<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

// Fetch summary stats
$stats = [];
$stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM Users WHERE user_type='Customer'")->fetchColumn();
$stats['total_products'] = $pdo->query("SELECT COUNT(*) FROM Products")->fetchColumn();
$stats['live_products'] = $pdo->query("SELECT COUNT(*) FROM Products WHERE is_active = 1")->fetchColumn();
$stats['total_orders'] = $pdo->query("SELECT COUNT(*) FROM Orders")->fetchColumn();
$stats['total_revenue'] = $pdo->query("SELECT SUM(total_amount) FROM Orders WHERE status != 'cancelled'")->fetchColumn() ?? 0;

// Fetch recent orders
$recent_orders = $pdo->query("SELECT o.*, u.username FROM Orders o JOIN Users u ON o.user_id = u.user_id ORDER BY o.created_at DESC LIMIT 5")->fetchAll();

// Low stock products
$low_stock = $pdo->query("SELECT product_id, product_name, stock_quantity FROM Products WHERE stock_quantity <= 5 AND is_active = 1 ORDER BY stock_quantity ASC LIMIT 5")->fetchAll();

// Handle Export
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="admin_dashboard_' . date('Y-m-d') . '.csv"');
    $output = fopen('php://output', 'w');

    // Summary
    fputcsv($output, ['Summary Metrics']);
    fputcsv($output, ['Total Revenue', 'Orders', 'Total Products', 'Live Products', 'Customers']);
    fputcsv($output, [$stats['total_revenue'], $stats['total_orders'], $stats['total_products'], $stats['live_products'], $stats['total_users']]);
    fputcsv($output, []);

    // Recent Orders
    fputcsv($output, ['Recent Orders (Last 5)']);
    fputcsv($output, ['Order ID', 'Customer', 'Amount', 'Status', 'Date']);
    foreach ($recent_orders as $o) {
        fputcsv($output, [$o['order_id'], $o['username'], $o['total_amount'], $o['status'], $o['created_at']]);
    }
    fputcsv($output, []);

    // Low Stock Alert
    fputcsv($output, ['Low Stock Alerts']);
    fputcsv($output, ['Product ID', 'Product Name', 'Quantity']);
    foreach ($low_stock as $ls) {
        fputcsv($output, [$ls['product_id'], $ls['product_name'], $ls['stock_quantity']]);
    }

    fclose($output);
    exit();
}

// All backend logic complete. Render DOM.
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="row pt-4 pb-5">
    <div class="col-md-3 mb-4">
        <div class="list-group shadow-sm border-0">
            <a href="index.php"
                class="list-group-item list-group-item-action active bg-primary-custom border-primary-custom fw-bold"><i
                    class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            <a href="sales.php" class="list-group-item list-group-item-action"><i class="bi bi-graph-up-arrow me-2"></i>
                Sales Report</a>
            <a href="products.php" class="list-group-item list-group-item-action"><i class="bi bi-box me-2"></i>
                Products & Inventory</a>
            <a href="orders.php" class="list-group-item list-group-item-action"><i class="bi bi-box-seam me-2"></i>
                Manage Orders</a>
            <a href="users.php" class="list-group-item list-group-item-action"><i class="bi bi-people me-2"></i> User
                Management</a>
            <a href="tutorials.php" class="list-group-item list-group-item-action"><i class="bi bi-youtube me-2"></i>
                Manage Tutorials</a>
            <a href="reviews.php" class="list-group-item list-group-item-action"><i class="bi bi-star-fill me-2"></i>
                Manage Reviews</a>
            <a href="promos.php" class="list-group-item list-group-item-action"><i class="bi bi-tags-fill me-2"></i>
                Promo Codes</a>
            <a href="queries.php" class="list-group-item list-group-item-action"><i class="bi bi-headset me-2"></i>
                Customer Queries</a>
            <a href="feedbacks.php" class="list-group-item list-group-item-action"><i class="bi bi-lightbulb me-2"></i>
                Feedbacks</a>
        </div>
    </div>
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
            <div>
                <h2 class="text-primary-custom fw-bold m-0"><i class="bi bi-shield-lock-fill me-2"></i>Admin Dashboard
                </h2>
                <div class="text-white opacity-75 small">Overview & Reports</div>
            </div>
            <a href="index.php?export=csv" class="btn btn-outline-success btn-sm"><i
                    class="bi bi-file-earmark-excel me-1"></i> Export Dashboard Data</a>
        </div>

        <!-- Summary Cards -->
        <div class="row g-4 mb-5">
            <div class="col-sm-6 col-lg-4 col-xl">
                <div class="card bg-success text-white shadow-sm border-0 h-100 rounded-3 text-center py-4">
                    <div class="card-body p-2">
                        <i class="bi bi-currency-dollar fs-1 opacity-50 mb-2 d-block"></i>
                        <h6 class="card-title fw-bold text-uppercase tracking-wider opacity-75">Total Revenue</h6>
                        <h3 class="mb-0 fw-bold">&#8377;<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4 col-xl">
                <div class="card bg-primary-custom text-white shadow-sm border-0 h-100 rounded-3 text-center py-4">
                    <div class="card-body p-2">
                        <i class="bi bi-bag-check fs-1 opacity-50 mb-2 d-block"></i>
                        <h6 class="card-title fw-bold text-uppercase tracking-wider opacity-75">Orders</h6>
                        <h3 class="mb-0 fw-bold"><?php echo $stats['total_orders']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4 col-xl">
                <div class="card bg-secondary-custom text-white shadow-sm border-0 h-100 rounded-3 text-center py-4">
                    <div class="card-body p-2">
                        <i class="bi bi-box-seam fs-1 opacity-50 mb-2 d-block"></i>
                        <h6 class="card-title fw-bold text-uppercase tracking-wider opacity-75">Total Products</h6>
                        <h3 class="mb-0 fw-bold"><?php echo $stats['total_products']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4 col-xl">
                <div class="card bg-info text-white shadow-sm border-0 h-100 rounded-3 text-center py-4"
                    style="background-color: #17a2b8 !important;">
                    <div class="card-body p-2">
                        <i class="bi bi-eye fs-1 opacity-50 mb-2 d-block"></i>
                        <h6 class="card-title fw-bold text-uppercase tracking-wider opacity-75">Live Products</h6>
                        <h3 class="mb-0 fw-bold"><?php echo $stats['live_products']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4 col-xl">
                <div class="card bg-dark text-white shadow-sm border-0 h-100 rounded-3 text-center py-4">
                    <div class="card-body p-2">
                        <i class="bi bi-people fs-1 opacity-50 mb-2 d-block"></i>
                        <h6 class="card-title fw-bold text-uppercase tracking-wider opacity-75">Customers</h6>
                        <h3 class="mb-0 fw-bold"><?php echo $stats['total_users']; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Recent Orders -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header border-bottom-0 pt-4 pb-3">
                        <h5 class="fw-bold mb-0 text-primary-custom"><i class="bi bi-clock-history me-2"></i>Recent
                            Orders</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-white opacity-75 small text-uppercase tracking-wider">
                                    <tr>
                                        <th class="ps-4">ID</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th class="pe-4">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_orders)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-white opacity-75">No orders found.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_orders as $o): ?>
                                            <tr>
                                                <td class="fw-bold text-white ps-4">#<?php echo $o['order_id']; ?></td>
                                                <td><?php echo sanitize_html($o['username']); ?></td>
                                                <td class="fw-bold text-secondary-custom">
                                                    &#8377;<?php echo number_format($o['total_amount'], 2); ?></td>
                                                <td>
                                                    <?php
                                                    $b = 'bg-secondary';
                                                    if ($o['status'] == 'confirmed')
                                                        $b = 'bg-success';
                                                    elseif ($o['status'] == 'shipped')
                                                        $b = 'bg-info text-dark';
                                                    elseif ($o['status'] == 'delivered')
                                                        $b = 'bg-primary';
                                                    ?>
                                                    <span
                                                        class="badge <?php echo $b; ?>"><?php echo ucfirst($o['status']); ?></span>
                                                </td>
                                                <td class="text-white opacity-75 small pe-4">
                                                    <?php echo date('M j, Y', strtotime($o['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Low Stock -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 h-100 border-top border-4 border-danger">
                    <div class="card-header border-bottom-0 pt-4 pb-2">
                        <h5 class="fw-bold mb-0 text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Low
                            Stock Alert</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($low_stock)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-check-circle-fill text-success fs-1 mb-2 d-block"></i>
                                <p class="text-white opacity-75 fw-bold">Inventory levels look good!</p>
                            </div>
                        <?php else: ?>
                            <ul class="list-group list-group-flush mt-2">
                                <?php foreach ($low_stock as $ls): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4">
                                        <div class="text-truncate me-2" style="max-width: 170px;">
                                            <a href="products.php?edit=<?php echo $ls['product_id']; ?>"
                                                class="text-decoration-none fw-bold text-white hover-primary"><?php echo sanitize_html($ls['product_name']); ?></a>
                                        </div>
                                        <span class="badge bg-danger rounded-pill px-2 py-1"><i
                                                class="bi bi-arrow-down-circle-fill me-1"></i><?php echo $ls['stock_quantity']; ?>
                                            left</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="p-3 border-top text-center mt-auto" style="background: rgba(0,0,0,0.2);">
                                <a href="products.php" class="btn btn-sm btn-outline-danger w-100 fw-bold">Manage
                                    Inventory</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>