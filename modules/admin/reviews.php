<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

// Handle Admin Review Deletion BEFORE requiring header.php (which outputs HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_review') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $review_id = (int)$_POST['review_id'];
        try {
            $del = $pdo->prepare("DELETE FROM Reviews WHERE review_id = ?");
            $del->execute([$review_id]);
            header("Location: reviews.php?success=deleted");
            exit;
        } catch (PDOException $e) {
            $error = "Error deleting review: " . $e->getMessage();
        }
    } else {
        $error = "CSRF verification failed.";
    }
}

require_once __DIR__ . '/../../includes/header.php';

// Fetch all reviews joined with User and Product data
$stmt = $pdo->prepare("
    SELECT r.*, u.username, u.email, p.product_name, p.image_url
    FROM Reviews r
    JOIN Users u ON r.user_id = u.user_id
    JOIN Products p ON r.product_id = p.product_id
    ORDER BY r.created_at DESC
");
$stmt->execute();
$reviews = $stmt->fetchAll();
?>

<div class="row pt-4 pb-5">
    <!-- Sidebar -->
    <div class="col-md-3 mb-4">
        <div class="list-group shadow-sm border-0">
            <a href="index.php" class="list-group-item list-group-item-action">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
            <a href="sales.php" class="list-group-item list-group-item-action">
                <i class="bi bi-graph-up-arrow me-2"></i> Sales Report
            </a>
            <a href="products.php" class="list-group-item list-group-item-action">
                <i class="bi bi-box-seam me-2"></i> Manage Products
            </a>
            <a href="orders.php" class="list-group-item list-group-item-action">
                <i class="bi bi-receipt me-2"></i> Manage Orders
            </a>
            <a href="users.php" class="list-group-item list-group-item-action">
                <i class="bi bi-people me-2"></i> Manage Users
            </a>
            <a href="tutorials.php" class="list-group-item list-group-item-action">
                <i class="bi bi-youtube me-2"></i> Manage Tutorials
            </a>
            <a href="reviews.php" class="list-group-item list-group-item-action active bg-primary-custom border-primary-custom fw-bold">
                <i class="bi bi-star-fill me-2"></i> Manage Reviews
            </a>
            <a href="promos.php" class="list-group-item list-group-item-action">
                <i class="bi bi-tags-fill me-2"></i> Promo Codes
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-md-9">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                <h3 class="mb-0 text-primary-custom fw-bold">Review Moderation</h3>
            </div>
            <div class="card-body p-4">

                <?php if(isset($_GET['success']) && $_GET['success'] === 'deleted'): ?>
                    <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i>Review permanently deleted.</div>
                <?php endif; ?>
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo sanitize_html($error); ?></div>
                <?php endif; ?>

                <?php if(empty($reviews)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-chat-left-dots text-white opacity-25 d-block mb-3" style="font-size: 5rem;"></i>
                        <h4 class="text-white opacity-75 fw-bold">No Reviews Found</h4>
                        <p class="text-white opacity-50">There are currently no product reviews in the system.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light text-white opacity-75 small text-uppercase tracking-wider">
                                <tr>
                                    <th>User</th>
                                    <th>Product</th>
                                    <th>Rating</th>
                                    <th>Comment</th>
                                    <th>Date</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($reviews as $r): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-white"><?php echo sanitize_html($r['username']); ?></div>
                                        <div class="small text-white opacity-50"><?php echo sanitize_html($r['email']); ?></div>
                                    </td>
                                    <td>
                                        <div class="text-white text-truncate" style="max-width: 150px;" title="<?php echo sanitize_html($r['product_name']); ?>">
                                            <a href="../products/view.php?id=<?php echo $r['product_id']; ?>" class="text-primary-custom text-decoration-none fw-bold" target="_blank">
                                                <?php echo sanitize_html($r['product_name']); ?>
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-warning small text-nowrap">
                                            <?php
                                            for($i=1; $i<=5; $i++) echo ($i <= $r['rating']) ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-white opacity-75 text-truncate" style="max-width: 250px; font-size: 0.9rem;" title="<?php echo sanitize_html($r['comment']); ?>">
                                            <?php echo sanitize_html($r['comment']); ?>
                                        </div>
                                    </td>
                                    <td class="text-white opacity-50 small text-nowrap">
                                        <?php echo date('M j, Y', strtotime($r['created_at'])); ?>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger fw-bold" data-bs-toggle="modal" data-bs-target="#deleteReviewModal<?php echo $r['review_id']; ?>">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
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

<!-- Admin Review Deletion Modals -->
<?php if(!empty($reviews)): ?>
    <?php foreach($reviews as $r): ?>
        <div class="modal fade" id="deleteReviewModal<?php echo $r['review_id']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content glass-panel border-danger">
                    <div class="modal-header border-secondary border-opacity-50">
                        <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Delete Review</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-white">
                        <p>Are you sure you want to permanently delete this review from exactly <strong><?php echo sanitize_html($r['username']); ?></strong>?</p>
                        <div class="bg-dark p-3 rounded-3 border border-secondary mb-3">
                            <i class="bi bi-quote fs-4 text-secondary-custom me-2"></i>
                            <em class="opacity-75"><?php echo sanitize_html($r['comment']); ?></em>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary border-opacity-50">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="action" value="delete_review">
                            <input type="hidden" name="review_id" value="<?php echo $r['review_id']; ?>">
                            <button type="submit" class="btn btn-danger fw-bold"><i class="bi bi-trash-fill me-2"></i>Delete Review</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
