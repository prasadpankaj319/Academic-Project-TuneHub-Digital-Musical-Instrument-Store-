<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/auth.php';

require_login();

$user_id = $_SESSION['user_id'];

// Remove item explicitly from the dashboard view
if(isset($_POST['remove_wishlist_id'])) {
    if(verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $del = $pdo->prepare("DELETE FROM Wishlist WHERE wishlist_id = ? AND user_id = ?");
        $del->execute([$_POST['remove_wishlist_id'], $user_id]);
    }
}

// Fetch aggregate wishlist catalog
$stmt = $pdo->prepare("
    SELECT w.wishlist_id, p.*, c.category_name
    FROM Wishlist w
    JOIN Products p ON w.product_id = p.product_id
    JOIN Categories c ON p.category_id = c.category_id
    WHERE w.user_id = ?
    ORDER BY w.added_at DESC
");
$stmt->execute([$user_id]);
$wishlist_items = $stmt->fetchAll();
?>

<div class="row pt-4 pb-5">
    <!-- User Side Navigation -->
    <div class="col-md-4 col-lg-3 mb-4">
        <div class="list-group shadow-sm">
            <a href="profile.php" class="list-group-item list-group-item-action">
                <i class="bi bi-person-circle me-2"></i> My Profile
            </a>
            <a href="../orders/index.php" class="list-group-item list-group-item-action">
                <i class="bi bi-box-seam me-2"></i> Order History
            </a>
            <a href="wishlist.php" class="list-group-item list-group-item-action active bg-primary-custom border-primary-custom">
                <i class="bi bi-heart-fill me-2"></i> My Wishlist
            </a>
            <a href="logout.php" class="list-group-item list-group-item-action text-danger mt-3 border-top">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </div>
    </div>

    <!-- Wishlist Viewport -->
    <div class="col-md-8 col-lg-9">
        <h2 class="text-primary-custom fw-bold mb-4"><i class="bi bi-heart me-2 text-secondary-custom"></i>Saved Instruments</h2>

        <?php if(count($wishlist_items) > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                <?php foreach($wishlist_items as $item): ?>
                    <div class="col stagger-item">
                        <div class="card h-100 border-0 shadow-sm glass-panel p-2">
                            <div class="position-relative">
                                <?php $img = !empty($item['image_url']) ? '../../' . $item['image_url'] : 'https://images.unsplash.com/photo-1511192336575-5a79af67a629?w=800&h=600&fit=crop'; ?>
                                <img src="<?php echo sanitize_html($img); ?>" class="card-img-top rounded" alt="<?php echo sanitize_html($item['product_name']); ?>" style="height: 200px; object-fit: cover;">

                                <form method="POST" action="" class="position-absolute top-0 end-0 p-2">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="remove_wishlist_id" value="<?php echo $item['wishlist_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger rounded-circle shadow-sm" style="width: 32px; height: 32px; padding: 0;">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            </div>

                            <div class="card-body p-3 d-flex flex-column">
                                <span class="badge badge-category mb-2 align-self-start border fw-normal"><?php echo sanitize_html($item['category_name']); ?></span>
                                <h5 class="card-title fw-bold text-white mb-1 fs-6 text-truncate" title="<?php echo sanitize_html($item['product_name']); ?>"><?php echo sanitize_html($item['product_name']); ?></h5>
                                <p class="small text-white opacity-50 mb-3"><?php echo sanitize_html($item['brand']); ?></p>

                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                    <span class="fs-5 fw-bold text-secondary-custom">&#8377;<?php echo number_format($item['price'], 2); ?></span>
                                </div>
                            </div>
                            <!-- Actions -->
                            <div class="card-footer bg-transparent border-0 p-3 pt-0">
                                <div class="d-flex gap-2">
                                    <a href="../products/view.php?id=<?php echo $item['product_id']; ?>" class="btn btn-outline-light btn-sm w-50">View</a>

                                    <?php if ($item['stock_quantity'] > 0): ?>
                                    <form method="POST" action="../cart/actions.php" class="w-50">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-secondary btn-sm w-100 fw-bold">To Cart</button>
                                    </form>
                                    <?php else: ?>
                                    <button class="btn btn-outline-danger btn-sm w-50" disabled>Out of Stock</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="glass-panel p-5 text-center mt-3 rounded-4">
                <i class="bi bi-heart fs-1 text-white opacity-25 mb-3 d-block"></i>
                <h4 class="text-white">Your wishlist is empty</h4>
                <p class="text-white opacity-50 mb-4">Start exploring our catalog and save the gear you love!</p>
                <a href="../products/index.php" class="btn btn-primary px-4">Browse Catalog</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
