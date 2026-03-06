<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/auth.php';

require_login();

$user_id = $_SESSION['user_id'];

// Fetch all orders for current user ordered by most recent
$stmt = $pdo->prepare("SELECT * FROM Orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

?>
<div class="row pt-4 pb-5">
    <!-- Sidebar -->
    <div class="col-md-4 col-lg-3 mb-4">
        <div class="list-group shadow-sm border-0">
            <a href="../user/profile.php" class="list-group-item list-group-item-action">
                <i class="bi bi-person-circle me-2"></i> My Profile
            </a>
            <a href="../user/wishlist.php" class="list-group-item list-group-item-action">
                <i class="bi bi-heart-fill me-2"></i> My Wishlist
            </a>
            <a href="index.php" class="list-group-item list-group-item-action active bg-primary-custom border-primary-custom fw-bold">
                <i class="bi bi-box-seam-fill me-2"></i> Order History
            </a>
            <a href="../user/logout.php" class="list-group-item list-group-item-action text-danger mt-3 border-top">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-md-8 col-lg-9">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header border-bottom-0 pt-4 pb-0">
                <h3 class="mb-0 text-primary-custom fw-bold">My Orders</h3>
            </div>
            <div class="card-body p-4">
                <?php if (empty($orders)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-receipt text-white opacity-75 opacity-25 d-block mb-3" style="font-size: 5rem;"></i>
                        <h4 class="text-white opacity-75 fw-bold">No orders found</h4>
                        <p class="text-white opacity-75">You haven't placed any orders with us yet. Let's fix that!</p>
                        <a href="../products/index.php" class="btn btn-secondary mt-3 px-4 py-2 fw-bold shadow-sm">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light text-white opacity-75 small text-uppercase tracking-wider">
                                <tr>
                                    <th class="py-3">Order #</th>
                                    <th class="py-3">Date Placed</th>
                                    <th class="py-3">Total Amount</th>
                                    <th class="py-3">Status</th>
                                    <th class="text-end py-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orders as $order): ?>
                                <tr>
                                    <td class="fw-bold text-white">#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td class="text-white opacity-75"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                    <td class="fw-bold text-secondary-custom">&#8377;<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <?php
                                            $b = 'bg-secondary';
                                            if($order['status'] == 'confirmed') $b = 'bg-success';
                                            elseif($order['status'] == 'shipped') $b = 'bg-info text-dark';
                                            elseif($order['status'] == 'delivered') $b = 'bg-primary';
                                            elseif($order['status'] == 'cancelled') $b = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $b; ?> px-2 py-1"><i class="bi bi-circle-fill me-1" style="font-size:0.4rem; vertical-align:middle;"></i><?php echo ucfirst($order['status']); ?></span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2 text-nowrap">
                                            <a href="invoice.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-outline-primary fw-bold px-3">
                                                <i class="bi bi-file-earmark-text-fill me-1"></i>View
                                            </a>
                                            <?php if ($order['status'] === 'pending'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger fw-bold px-3" data-bs-toggle="modal" data-bs-target="#cancelModal<?php echo $order['order_id']; ?>">
                                                    <i class="bi bi-x-circle-fill me-1"></i>Cancel
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Custom TuneHub Cancel Modals (Generated outside table scope to prevent z-index/overlay issues) -->
<?php if (!empty($orders)): ?>
    <?php foreach($orders as $order): ?>
        <?php if ($order['status'] === 'pending'): ?>
        <div class="modal fade" id="cancelModal<?php echo $order['order_id']; ?>" tabindex="-1" aria-labelledby="cancelModalLabel<?php echo $order['order_id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content glass-panel" style="text-align: left;">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title fw-bold text-danger" id="cancelModalLabel<?php echo $order['order_id']; ?>">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>Cancel Order #<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-white opacity-75 mb-3" style="font-size: 1.05rem;">Are you sure you want to officially cancel this order?</p>
                        <p class="text-white opacity-50 mb-4 small">This action securely aborts your purchase and immediately returns the reserved instruments to the public TuneHub catalog.</p>
                        <div class="p-3 bg-black bg-opacity-25 rounded border border-secondary text-white small">
                            <i class="bi bi-shield-lock-fill text-success me-2"></i><strong>Secure Refund Assured</strong><br>
                            Your original payment method will not be charged. This action cannot be undone once confirmed.
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Keep My Order</button>
                        <form method="POST" action="cancel.php" class="m-0">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                            <button type="submit" class="btn btn-danger fw-bold">Yes, Cancel Purchase</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
