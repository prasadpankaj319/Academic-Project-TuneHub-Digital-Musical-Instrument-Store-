<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/auth.php';

require_login();

$order_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Secure fetch: Only allow users to view their own invoices, unless admin
$stmt = $pdo->prepare("SELECT * FROM Orders WHERE order_id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    if(isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Admin'){
        // Admins can see any invoice
        $stmt = $pdo->prepare("SELECT * FROM Orders WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();
    }
}

if (!$order) {
    echo "<div class='container my-5 text-center py-5'><div class='alert alert-warning py-4'><i class='bi bi-exclamation-triangle-fill me-2'></i>Order not found or access denied.</div></div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}

// Fetch order items with product details
$stmt = $pdo->prepare("SELECT oi.*, p.product_name, p.is_active FROM Order_Items oi JOIN Products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>
<div class="row pt-4 pb-5 justify-content-center">
    <div class="col-lg-8">

        <!-- Action bar -->
        <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
            <h2 class="text-primary-custom fw-bold m-0"><i class="bi bi-receipt me-2"></i>Invoice</h2>
            <div>
                <a href="index.php" class="btn btn-outline-secondary border-0 me-2"><i class="bi bi-arrow-left me-1"></i> Back</a>
                <button class="btn btn-secondary px-4 fw-bold shadow-sm" onclick="window.print()"><i class="bi bi-printer-fill me-2"></i>Print</button>
            </div>
        </div>

        <!-- Printable Invoice Box -->
        <div class="card shadow-sm border-0 invoice-box p-5 rounded-3 glass-panel" id="invoice">

            <!-- Header -->
            <div class="row mb-5 border-bottom pb-4 align-items-center">
                <div class="col-sm-6 text-center text-sm-start">
                    <h3 class="text-primary-custom fw-bold mb-1 d-flex align-items-center justify-content-center justify-content-sm-start">
                        <i class="bi bi-music-note-beamed me-2"></i> TuneHub
                    </h3>
                    <p class="text-white opacity-75 small mb-0 mt-2">421401, Mumbai, MH, INDIA</p>
                    <p class="text-white opacity-75 small mb-0">support@tunehub.com | +91 7709100274</p>
                </div>
                <div class="col-sm-6 text-center text-sm-end mt-4 mt-sm-0">
                    <h5 class="fw-bold text-white mb-1">INVOICE #<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></h5>
                    <p class="text-white opacity-75 small mb-3">Order Date: <?php echo date('M j, Y, h:i A', strtotime($order['created_at'])); ?></p>

                    <?php
                        $status_badge = 'bg-secondary';
                        if($order['status'] == 'confirmed') $status_badge = 'bg-success';
                        else if($order['status'] == 'shipped') $status_badge = 'bg-info text-dark';
                        else if($order['status'] == 'delivered') $status_badge = 'bg-primary';
                        else if($order['status'] == 'cancelled') $status_badge = 'bg-danger';
                    ?>
                    <span class="badge <?php echo $status_badge; ?> px-3 py-2 text-uppercase tracking-wider fw-bold">
                        <i class="bi bi-circle-fill me-1" style="font-size:0.5rem; vertical-align:middle;"></i>
                        <?php echo sanitize_html($order['status']); ?>
                    </span>
                </div>
            </div>

            <!-- Visual Order Tracking Timeline -->
            <?php if($order['status'] !== 'cancelled'): ?>
            <div class="row mb-5 px-sm-3 d-print-none">
                <div class="col-12">
                    <div class="position-relative m-4">
                        <!-- Progress Bar Background -->
                        <div class="progress" style="height: 4px; background-color: rgba(255,255,255,0.1);">
                            <?php
                                $progress_width = 0;
                                if ($order['status'] == 'pending') $progress_width = 0;
                                else if ($order['status'] == 'confirmed') $progress_width = 33;
                                else if ($order['status'] == 'shipped') $progress_width = 66;
                                else if ($order['status'] == 'delivered') $progress_width = 100;
                            ?>
                            <div class="progress-bar bg-primary-custom" role="progressbar" style="width: <?php echo $progress_width; ?>%; transition: width 1.5s ease-in-out;" aria-valuenow="<?php echo $progress_width; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>

                        <!-- Timeline Nodes -->
                        <div class="d-flex justify-content-between position-absolute top-50 start-0 translate-middle-y w-100 px-1 px-sm-3" style="z-index: 2;">
                            <!-- Node 1: Placed -->
                            <div class="text-center" style="width: 70px;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 <?php echo ($progress_width >= 0) ? 'bg-primary-custom' : 'bg-dark border border-secondary'; ?>" style="width: 32px; height: 32px; box-shadow: 0 4px 10px rgba(0,0,0,0.5); transition: background 0.5s;">
                                    <i class="bi bi-cart-check-fill text-white" style="font-size: 0.9rem;"></i>
                                </div>
                                <span class="d-block small fw-bold <?php echo ($progress_width >= 0) ? 'text-primary-custom' : 'text-white opacity-50'; ?>" style="font-size: 0.8rem; letter-spacing: 0.5px;">Placed</span>
                            </div>

                            <!-- Node 2: Confirmed -->
                            <div class="text-center" style="width: 70px;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 <?php echo ($progress_width >= 33) ? 'bg-primary-custom' : 'bg-dark border border-secondary'; ?>" style="width: 32px; height: 32px; box-shadow: 0 4px 10px rgba(0,0,0,0.5); transition: background 0.5s;">
                                    <i class="bi bi-box-seam-fill text-white" style="font-size: 0.9rem;"></i>
                                </div>
                                <span class="d-block small fw-bold <?php echo ($progress_width >= 33) ? 'text-primary-custom' : 'text-white opacity-50'; ?>" style="font-size: 0.8rem; letter-spacing: 0.5px;">Processed</span>
                            </div>

                            <!-- Node 3: Shipped -->
                            <div class="text-center" style="width: 70px;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 <?php echo ($progress_width >= 66) ? 'bg-primary-custom' : 'bg-dark border border-secondary'; ?>" style="width: 32px; height: 32px; box-shadow: 0 4px 10px rgba(0,0,0,0.5); transition: background 0.5s;">
                                    <i class="bi bi-truck text-white" style="font-size: 0.9rem;"></i>
                                </div>
                                <span class="d-block small fw-bold <?php echo ($progress_width >= 66) ? 'text-primary-custom' : 'text-white opacity-50'; ?>" style="font-size: 0.8rem; letter-spacing: 0.5px;">Shipped</span>
                            </div>

                            <!-- Node 4: Delivered -->
                            <div class="text-center" style="width: 70px;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 <?php echo ($progress_width >= 100) ? 'bg-primary-custom' : 'bg-dark border border-secondary'; ?>" style="width: 32px; height: 32px; box-shadow: 0 4px 10px rgba(0,0,0,0.5); transition: background 0.5s;">
                                    <i class="bi bi-house-door-fill text-white" style="font-size: 0.9rem;"></i>
                                </div>
                                <span class="d-block small fw-bold <?php echo ($progress_width >= 100) ? 'text-primary-custom' : 'text-white opacity-50'; ?>" style="font-size: 0.8rem; letter-spacing: 0.5px;">Delivered</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="row mb-5 d-print-none">
                <div class="col-12 text-center">
                    <div class="alert alert-danger d-inline-block px-4 py-2 border-danger border-2 shadow-sm">
                        <i class="bi bi-x-circle-fill me-2 bg-danger rounded-circle p-1"></i>
                        <span class="fw-bold tracking-wider">ORDER CANCELLED</span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Table -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-borderless table-striped align-middle">
                            <thead class="bg-primary-custom text-white border-bottom border-2 border-primary-custom">
                                <tr>
                                    <th class="py-3 px-4 rounded-start">Product Description</th>
                                    <th class="py-3 text-center">Quantity</th>
                                    <th class="py-3 text-end px-4 rounded-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($items as $item): ?>
                                <tr>
                                    <td class="py-3 px-4 fw-semibold text-white">
                                        <?php echo sanitize_html($item['product_name']); ?>
                                        <?php if ($item['is_active'] == 0): ?>
                                            <span class="badge bg-danger ms-2" style="font-size: 0.65rem;">No longer available</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-white opacity-75 text-center"><?php echo $item['quantity']; ?></td>
                                    <td class="py-3 text-end px-4 fw-bold text-white">&#8377;<?php echo number_format($item['subtotal'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="border-top border-2 mt-2">
                                <tr style="background: rgba(0,0,0,0.2);">
                                    <td colspan="2" class="text-end fw-bold py-4 fs-4 text-primary-custom px-4">Grand Total (INR):</td>
                                    <td class="text-end fw-bold py-4 fs-4 text-secondary-custom px-4">&#8377;<?php echo number_format($order['total_amount'], 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="row text-center mt-3 pt-4 border-top">
                <div class="col-12">
                    <i class="bi bi-heart-fill text-danger mb-3 fs-3 d-block"></i>
                    <h5 class="fw-bold text-white">Thank you for your purchase!</h5>
                    <p class="small text-white opacity-75 w-75 mx-auto">If you have any questions about this invoice or your new instrument, please contact our support team. Have a great time playing!</p>
                </div>
            </div>
        </div>

    </div>
</div>
<style>
@media print {
    body * { visibility: hidden; }
    #invoice, #invoice * { visibility: visible; }
    #invoice { position: absolute; left: 0; top: 0; width: 100%; }
    .card { box-shadow: none !important; border: none !important; }
}
</style>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
