<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_status') {
            $order_id = $_POST['order_id'];
            $status = $_POST['status'];

            // Validate allowed enum values
            $allowed_statuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
            if (in_array($status, $allowed_statuses)) {
                // Get current status to avoid double-processing or incorrect stock logic
                $cur_stmt = $pdo->prepare("SELECT status FROM Orders WHERE order_id = ?");
                $cur_stmt->execute([$order_id]);
                $current_order = $cur_stmt->fetch();

                if ($current_order && $current_order['status'] !== $status) {
                    try {
                        $pdo->beginTransaction();

                        $stmt = $pdo->prepare("UPDATE Orders SET status = ? WHERE order_id = ?");
                        $stmt->execute([$status, $order_id]);

                        // If definitively moving into cancelled, restore global stock
                        if ($status === 'cancelled') {
                            $items_stmt = $pdo->prepare("SELECT product_id, quantity FROM Order_Items WHERE order_id = ?");
                            $items_stmt->execute([$order_id]);
                            $items = $items_stmt->fetchAll();

                            $restore_stmt = $pdo->prepare("UPDATE Products SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
                            foreach ($items as $item) {
                                $restore_stmt->execute([$item['quantity'], $item['product_id']]);
                            }
                        }

                        $pdo->commit();
                        $_SESSION['msg'] = "Order #{$order_id} status updated to " . ucfirst($status) . ".";
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $_SESSION['err'] = "Failed to update order status safely. Database error.";
                    }
                }
            } else {
                $_SESSION['err'] = "Invalid status provided.";
            }
            header("Location: orders.php");
            exit;
        }
    }
}

// Fetch all orders
$stmt = $pdo->query("SELECT o.*, u.username, u.email FROM Orders o JOIN Users u ON o.user_id = u.user_id ORDER BY o.created_at DESC");
$orders = $stmt->fetchAll();

// Dynamic Order Item fetching if a specific order is clicked for 'View Specs'
$view_id = $_GET['view'] ?? null;
$view_order = null;
$order_items = [];
if ($view_id) {
    // Fetch parent order info
    $stmt = $pdo->prepare("SELECT o.*, u.username, u.email FROM Orders o JOIN Users u ON o.user_id = u.user_id WHERE o.order_id = ?");
    $stmt->execute([$view_id]);
    $view_order = $stmt->fetch();

    // Fetch associated items
    if ($view_order) {
        $stmt = $pdo->prepare("SELECT oi.*, p.product_name, p.brand, p.image_url, p.is_active FROM Order_Items oi JOIN Products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
        $stmt->execute([$view_id]);
        $order_items = $stmt->fetchAll();
    }
}

// All backend logic complete. Render DOM.
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="row pt-4 pb-5">
    <!-- Sidebar -->
    <div class="col-md-3 mb-4">
        <div class="list-group shadow-sm border-0">
            <a href="index.php" class="list-group-item list-group-item-action"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            <a href="sales.php" class="list-group-item list-group-item-action"><i class="bi bi-graph-up-arrow me-2"></i> Sales Report</a>
            <a href="products.php" class="list-group-item list-group-item-action"><i class="bi bi-box me-2"></i> Products & Inventory</a>
            <a href="orders.php" class="list-group-item list-group-item-action active bg-primary-custom border-primary-custom fw-bold"><i class="bi bi-box-seam me-2"></i> Manage Orders</a>
            <a href="users.php" class="list-group-item list-group-item-action"><i class="bi bi-people me-2"></i> User Management</a>
            <a href="tutorials.php" class="list-group-item list-group-item-action"><i class="bi bi-youtube me-2"></i> Manage Tutorials</a>
            <a href="reviews.php" class="list-group-item list-group-item-action"><i class="bi bi-star-fill me-2"></i> Manage Reviews</a>
            <a href="promos.php" class="list-group-item list-group-item-action"><i class="bi bi-tags-fill me-2"></i> Promo Codes</a>
        </div>
    </div>

    <!-- Main Content Panel -->
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
            <h2 class="text-primary-custom fw-bold m-0"><i class="bi bi-card-checklist me-2"></i>Order Fulfillment</h2>
            <?php if ($view_order): ?>
                <a href="orders.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Global List</a>
            <?php endif; ?>
        </div>

        <?php if(isset($_SESSION['msg'])): ?>
            <div class="alert alert-success d-flex align-items-center shadow-sm"><i class="bi bi-check-circle-fill me-2 fs-5"></i><?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['err'])): ?>
            <div class="alert alert-danger d-flex align-items-center shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i><?php echo $_SESSION['err']; unset($_SESSION['err']); ?></div>
        <?php endif; ?>

        <?php if ($view_order): ?>
            <!-- Detail View Mode -->
            <div class="card shadow-sm border-0 mb-4 bg-dark">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 border-end border-secondary">
                            <h5 class="fw-bold text-white mb-3">Order Details #<?php echo $view_order['order_id']; ?></h5>
                            <p class="text-white opacity-75 mb-1"><strong>Status:</strong>
                                <?php
                                    $b = 'bg-secondary';
                                    if($view_order['status'] == 'confirmed') $b = 'bg-success';
                                    elseif($view_order['status'] == 'shipped') $b = 'bg-info text-dark';
                                    elseif($view_order['status'] == 'delivered') $b = 'bg-primary';
                                    elseif($view_order['status'] == 'cancelled') $b = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $b; ?> ms-1" style="font-size: 0.85rem;"><?php echo ucfirst($view_order['status']); ?></span>
                            </p>
                            <p class="text-white opacity-75 mb-1"><strong>Total Amount:</strong> <span class="text-secondary-custom fw-bold">&#8377;<?php echo number_format($view_order['total_amount'], 2); ?></span></p>
                            <p class="text-white opacity-75 mb-0"><strong>Ordered On:</strong> <?php echo date('M j, Y g:i A', strtotime($view_order['created_at'])); ?></p>
                        </div>
                        <div class="col-md-6 ps-md-4 mt-4 mt-md-0">
                            <h5 class="fw-bold text-white mb-3">Customer & Delivery Information</h5>
                            <p class="text-white opacity-75 mb-1"><i class="bi bi-person-fill me-2"></i><?php echo sanitize_html($view_order['username']); ?></p>
                            <p class="text-white opacity-75 mb-2"><i class="bi bi-envelope-fill me-2"></i><?php echo sanitize_html($view_order['email']); ?></p>

                            <h6 class="fw-bold text-white mt-3 mb-1 opacity-75 small text-uppercase">Shipping Destination:</h6>
                            <div class="p-3 bg-black bg-opacity-25 rounded border border-secondary text-white small" style="white-space: pre-wrap;"><?php echo !empty($view_order['shipping_address']) ? sanitize_html($view_order['shipping_address']) : '<span class="text-muted fst-italic">No physical address provided.</span>'; ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Attached to this specific order -->
            <h5 class="fw-bold text-primary-custom mb-3 mt-5"><i class="bi bi-box2-fill me-2"></i>Attached Instruments</h5>
            <div class="row row-cols-1 row-cols-md-2 g-3 mb-5">
                <?php foreach($order_items as $item): ?>
                    <div class="col">
                        <div class="card shadow-sm border-0 h-100 bg-dark hover-lift">
                            <div class="row g-0">
                                <div class="col-4">
                                    <?php $img = !empty($item['image_url']) ? '../../' . $item['image_url'] : 'https://images.unsplash.com/photo-1511192336575-5a79af67a629?w=300&h=300&fit=crop'; ?>
                                    <img src="<?php echo sanitize_html($img); ?>" class="img-fluid rounded-start h-100" style="object-fit: cover;" alt="<?php echo sanitize_html($item['product_name']); ?>">
                                </div>
                                <div class="col-8">
                                    <div class="card-body p-3">
                                        <h6 class="card-title text-white fw-bold mb-1">
                                            <?php echo sanitize_html($item['product_name']); ?>
                                            <?php if ($item['is_active'] == 0): ?>
                                                <span class="badge bg-danger ms-2" style="font-size: 0.65rem;">No longer available</span>
                                            <?php endif; ?>
                                        </h6>
                                        <div class="small text-white opacity-50 mb-2"><?php echo sanitize_html($item['brand']); ?></div>
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <span class="badge bg-secondary">Qty: <?php echo $item['quantity']; ?></span>
                                            <span class="fw-bold text-secondary-custom">&#8377;<?php echo number_format($item['price_at_time'], 2); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <!-- Global List View Mode -->
            <div class="card shadow-sm border-0 mb-5">
                <div class="card-header border-bottom pt-4 pb-3">
                    <h5 class="fw-bold mb-0 text-white"><i class="bi bi-ui-checks-grid me-2"></i>Global Order Directory</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-white opacity-75 small text-uppercase tracking-wider">
                                <tr>
                                    <th class="ps-4">Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Current Status</th>
                                    <th class="text-end pe-4">Fulfillment Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orders as $o): ?>
                                <tr>
                                    <td class="fw-bold text-white ps-4">
                                        <a href="orders.php?view=<?php echo $o['order_id']; ?>" class="text-decoration-none text-primary-custom hover-secondary">#<?php echo $o['order_id']; ?></a>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-white"><?php echo sanitize_html($o['username']); ?></div>
                                        <div class="small opacity-50 text-white"><?php echo date('M j, Y g:i A', strtotime($o['created_at'])); ?></div>
                                    </td>
                                    <td class="fw-bold text-secondary-custom">&#8377;<?php echo number_format($o['total_amount'], 2); ?></td>
                                    <td>
                                        <?php
                                            $b = 'bg-secondary';
                                            if($o['status'] == 'confirmed') $b = 'bg-success';
                                            elseif($o['status'] == 'shipped') $b = 'bg-info text-dark';
                                            elseif($o['status'] == 'delivered') $b = 'bg-primary';
                                            elseif($o['status'] == 'cancelled') $b = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $b; ?>"><?php echo ucfirst($o['status']); ?></span>
                                    </td>
                                    <td class="text-end pe-4" style="width: 250px;">
                                        <form method="POST" action="orders.php" class="d-flex justify-content-end align-items-center gap-2">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">

                                            <select name="status" class="form-select form-select-sm shadow-sm" style="width: 140px; cursor: pointer;" onchange="this.form.submit()">
                                                <option value="pending" <?php echo $o['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="confirmed" <?php echo $o['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                <option value="shipped" <?php echo $o['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="delivered" <?php echo $o['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="cancelled" <?php echo $o['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>

                                            <a href="orders.php?view=<?php echo $o['order_id']; ?>" class="btn btn-sm btn-outline-secondary" title="View Specs"><i class="bi bi-eye"></i></a>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>

                                <?php if(empty($orders)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <i class="bi bi-basket fs-1 text-white opacity-25 d-block mb-3"></i>
                                            <p class="text-white opacity-75 mb-0 fw-bold">No customer orders have been placed yet.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
