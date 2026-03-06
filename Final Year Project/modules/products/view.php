<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';

$product_id = $_GET['id'] ?? 0;
$review_error = '';

// Check for POST review submission BEFORE including header (which prints HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    if (isset($_SESSION['user_id']) && verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $rating = (int) ($_POST['rating'] ?? 5);
        $comment = trim($_POST['comment'] ?? '');
        try {
            $ins_stmt = $pdo->prepare("INSERT INTO Reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
            $ins_stmt->execute([$product_id, $_SESSION['user_id'], $rating, $comment]);
            header("Location: view.php?id=" . $product_id);
            exit;
        } catch (PDOException $e) {
            $review_error = "You have already reviewed this product.";
        }
    }
}

// Now include header which outputs HTML
require_once __DIR__ . '/../../includes/header.php';

$stmt = $pdo->prepare("SELECT p.*, c.category_name FROM Products p JOIN Categories c ON p.category_id = c.category_id WHERE p.product_id = ? AND p.is_active = 1");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

$wishlist_ids = [];
if (isset($_SESSION['user_id'])) {
    $w_stmt = $pdo->prepare("SELECT product_id FROM Wishlist WHERE user_id = ?");
    $w_stmt->execute([$_SESSION['user_id']]);
    $wishlist_ids = $w_stmt->fetchAll(PDO::FETCH_COLUMN);
}

if (!$product) {
    echo "<div class='container my-5 text-center py-5'><div class='alert alert-warning py-4'><i class='bi bi-exclamation-triangle-fill me-2'></i>Product not found.</div></div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}

// Fetch Reviews data
$rev_stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(review_id) as total_reviews FROM Reviews WHERE product_id = ?");
$rev_stmt->execute([$product_id]);
$rev_data = $rev_stmt->fetch();
$avg_rating = $rev_data['avg_rating'] ? round($rev_data['avg_rating'], 1) : 0;
$total_reviews = $rev_data['total_reviews'];

$reviews_stmt = $pdo->prepare("SELECT r.*, u.username FROM Reviews r JOIN Users u ON r.user_id = u.user_id WHERE r.product_id = ? ORDER BY r.created_at DESC");
$reviews_stmt->execute([$product_id]);
$product_reviews = $reviews_stmt->fetchAll();

// Fetch Related Products (same category, max 4)
$rel_stmt = $pdo->prepare("SELECT product_id, product_name, price, image_url, brand FROM Products WHERE category_id = ? AND product_id != ? AND is_active = 1 ORDER BY RAND() LIMIT 4");
$rel_stmt->execute([$product['category_id'], $product_id]);
$related_products = $rel_stmt->fetchAll();

// Check for tutorials specific to this product
$tuto_stmt = $pdo->prepare("SELECT * FROM Tutorials WHERE product_id = ? AND is_active = 1");
$tuto_stmt->execute([$product_id]);
$tutorials = $tuto_stmt->fetchAll();
?>
<div class="row pt-4 pb-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb pb-3 border-bottom">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-primary-custom">Catalog</a>
            </li>
            <li class="breadcrumb-item"><a href="index.php?category=<?php echo $product['category_id']; ?>"
                    class="text-decoration-none text-primary-custom"><?php echo sanitize_html($product['category_name']); ?></a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?php echo sanitize_html($product['product_name']); ?></li>
        </ol>
    </nav>

    <div class="col-md-6 mb-4 mb-md-0">
        <?php $view_img = !empty($product['image_url']) ? '../../' . $product['image_url'] : 'https://images.unsplash.com/photo-1511192336575-5a79af67a629?w=800&h=600&fit=crop'; ?>
        <img src="<?php echo sanitize_html($view_img); ?>" class="img-fluid rounded shadow-sm w-100"
            alt="<?php echo sanitize_html($product['product_name']); ?>" style="max-height: 500px; object-fit: cover;">
    </div>

    <div class="col-md-6 ps-md-5">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <h1 class="text-primary-custom fw-bold display-5 mb-0 text-truncate"
                title="<?php echo sanitize_html($product['product_name']); ?>">
                <?php echo sanitize_html($product['product_name']); ?></h1>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php $is_wished = in_array($product['product_id'], $wishlist_ids); ?>
                <button class="btn btn-outline-danger rounded-circle shadow-sm toggle-wishlist-btn flex-shrink-0 ms-3"
                    data-id="<?php echo $product['product_id']; ?>" style="width: 50px; height: 50px; padding: 0;">
                    <i class="bi <?php echo $is_wished ? 'bi-heart-fill' : 'bi-heart'; ?> fs-4"
                        style="transition: transform 0.2s;"></i>
                </button>
            <?php endif; ?>
        </div>
        <h4 class="text-secondary mb-4 fw-normal"><?php echo sanitize_html($product['brand']); ?></h4>

        <!-- Star Rating Display -->
        <div class="d-flex align-items-center mb-4">
            <div class="text-warning me-2">
                <?php
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $avg_rating)
                        echo '<i class="bi bi-star-fill"></i>';
                    elseif ($i - 0.5 <= $avg_rating)
                        echo '<i class="bi bi-star-half"></i>';
                    else
                        echo '<i class="bi bi-star"></i>';
                }
                ?>
            </div>
            <span class="text-white opacity-75 small">(<?php echo $total_reviews; ?> reviews)</span>
        </div>

        <div class="d-flex gap-2 mb-4">
            <span
                class="badge badge-category border fs-6 px-3 py-2 fw-normal"><?php echo sanitize_html($product['category_name']); ?></span>
            <?php
            $skill_color = 'bg-success';
            if ($product['skill_level'] == 'Intermediate')
                $skill_color = 'bg-warning text-dark';
            if ($product['skill_level'] == 'Advanced')
                $skill_color = 'bg-danger';
            ?>
            <span class="badge <?php echo $skill_color; ?> fs-6 px-3 py-2 fw-normal"><i
                    class="bi bi-bar-chart-fill me-1"></i><?php echo sanitize_html($product['skill_level']); ?></span>
        </div>

        <h2 class="text-secondary-custom fw-bold mb-4 display-6">
            &#8377;<?php echo number_format($product['price'], 2); ?></h2>

        <div class="mb-4">
            <h5 class="fw-bold border-bottom pb-2">Description</h5>
            <p class="text-secondary lh-lg mt-3" style="font-size: 1.05rem;">
                <?php echo nl2br(sanitize_html($product['description'])); ?></p>
        </div>

        <div class="card border-0 shadow-sm mt-5">
            <div class="card-body p-4">
                <form method="POST" action="../cart/actions.php" class="d-flex align-items-end gap-3 flex-wrap">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">

                    <div style="width: 100px;">
                        <label class="form-label fw-bold text-white opacity-75 small">Qty</label>
                        <input type="number" name="quantity" class="form-control" value="1" min="1"
                            max="<?php echo $product['stock_quantity']; ?>" <?php echo $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                    </div>

                    <div class="flex-grow-1">
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <button type="submit"
                                class="btn btn-secondary w-100 fw-bold py-2 fs-5 shadow-sm text-uppercase tracking-wider">
                                <i class="bi bi-cart-plus-fill me-2"></i>Add to Cart
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-outline-danger w-100 fw-bold py-2 fs-5" disabled>Out of
                                Stock</button>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="mt-3">
                    <?php if ($product['stock_quantity'] > 0 && $product['stock_quantity'] <= 5): ?>
                        <p class="text-danger small mb-0 fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Only
                            <?php echo $product['stock_quantity']; ?> left in stock - order soon!</p>
                    <?php elseif ($product['stock_quantity'] > 5): ?>
                        <p class="text-success small mb-0 fw-bold"><i class="bi bi-check-circle-fill me-1"></i> In Stock
                            (<?php echo $product['stock_quantity']; ?> available)</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reviews Section -->
<div class="row mt-5 mb-5 px-3 mx-1">
    <div class="col-12">
        <h3 class="text-primary-custom fw-bold border-bottom border-dark pb-3 mb-4"><i
                class="bi bi-star-half me-2"></i>Customer Reviews</h3>

        <?php if ($review_error): ?>
            <div class="alert alert-danger"><i
                    class="bi bi-exclamation-triangle-fill me-2"></i><?php echo sanitize_html($review_error); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <?php if (count($product_reviews) > 0): ?>
                    <?php foreach ($product_reviews as $r): ?>
                        <div class="glass-panel p-4 rounded-3 mb-3 reveal-up" id="review-card-<?php echo $r['review_id']; ?>">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="fw-bold mb-1 text-white"><?php echo sanitize_html($r['username']); ?></h6>
                                    <small
                                        class="text-white opacity-50"><?php echo date('F j, Y', strtotime($r['created_at'])); ?></small>
                                </div>
                                <div class="text-warning small text-end">
                                    <div>
                                        <?php
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo ($i <= $r['rating']) ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
                                        }
                                        ?>
                                    </div>
                                    <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $r['user_id'] || (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Admin'))): ?>
                                        <button type="button" class="btn btn-sm btn-link text-danger p-0 mt-1 fw-bold"
                                            data-bs-toggle="modal" data-bs-target="#deleteReviewModal<?php echo $r['review_id']; ?>"
                                            style="text-decoration: none; font-size: 0.75rem;">
                                            <i class="bi bi-trash-fill me-1"></i>Delete
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <p class="text-white opacity-75 mb-0 small lh-lg"><?php echo nl2br(sanitize_html($r['comment'])); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="glass-panel p-5 text-center rounded-3">
                        <i class="bi bi-chat-left-dots fs-1 text-white opacity-25 mb-3 d-block"></i>
                        <p class="text-white opacity-50 mb-0">No reviews yet. Be the first to share your thoughts!</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="glass-panel p-4 rounded-3 sticky-lg-top" style="top: 100px;">
                    <h5 class="fw-bold mb-3 text-white">Write a Review</h5>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="action" value="submit_review">
                            <div class="mb-3">
                                <label class="form-label small text-white opacity-75 fw-bold">Rating</label>
                                <select name="rating" class="form-select bg-dark text-white border-secondary" required
                                    style="color: #ffffff !important; background-color: rgba(0,0,0,0.5) !important;">
                                    <option value="5" selected>5 Stars - Excellent</option>
                                    <option value="4">4 Stars - Good</option>
                                    <option value="3">3 Stars - Average</option>
                                    <option value="2">2 Stars - Poor</option>
                                    <option value="1">1 Star - Terrible</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-white opacity-75 fw-bold">Comment</label>
                                <textarea name="comment" class="form-control bg-dark text-white border-secondary" rows="4"
                                    placeholder="Share your experience with this instrument..."
                                    style="resize:none; color: #ffffff !important; background-color: rgba(0,0,0,0.5) !important;"
                                    required></textarea>
                            </div>
                            <button type="submit" class="btn btn-secondary w-100 fw-bold">Submit Review</button>
                        </form>
                    <?php else: ?>
                        <div class="text-center py-4 border border-secondary rounded p-3"
                            style="background-color: rgba(0,0,0,0.2);">
                            <p class="text-white opacity-75 small mb-3">You must be logged in to post a review.</p>
                            <a href="../user/login.php" class="btn btn-primary btn-sm px-4">Log In</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (count($tutorials) > 0): ?>
    <div class="row mt-5 mb-5 pt-5 pb-4 rounded shadow-sm px-3 mx-1">
        <div class="col-12 text-center mb-5">
            <h3 class="text-primary-custom fw-bold"><i class="bi bi-youtube text-danger me-2"></i>Related Tutorials</h3>
            <p class="text-secondary">Master your new instrument with these curated lessons</p>
        </div>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 justify-content-center">
            <?php foreach ($tutorials as $t): ?>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="ratio ratio-16x9">
                            <iframe src="<?php echo htmlspecialchars($t['video_url']); ?>"
                                title="<?php echo sanitize_html($t['title']); ?>" allowfullscreen
                                class="rounded-top shadow-sm"></iframe>
                        </div>
                        <div class="card-body text-center p-3">
                            <h6 class="card-title fw-bold mb-0 text-truncate"><?php echo sanitize_html($t['title']); ?></h6>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Related Products Engine -->
<?php if (isset($related_products) && count($related_products) > 0): ?>
    <div
        class="row mt-5 mb-5 pt-4 pb-4 bg-dark bg-opacity-50 rounded-3 shadow border border-secondary border-opacity-25 mx-1">
        <div class="col-12 text-center mb-5">
            <h3 class="text-primary-custom fw-bold"><i class="bi bi-box-seam text-secondary-custom me-2"></i>You Might Also
                Like</h3>
            <p class="text-secondary">Discover other premium instruments from similar categories.</p>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4 px-md-4">
            <?php foreach ($related_products as $rp): ?>
                <div class="col">
                    <div class="card h-100 bg-dark border-secondary shadow-sm hover-grow"
                        style="transition: transform 0.3s ease;">
                        <?php if ($rp['image_url']): ?>
                            <div class="p-3 text-center">
                                <img src="<?php echo sanitize_html($rp['image_url']); ?>" class="img-fluid rounded shadow-sm"
                                    alt="<?php echo sanitize_html($rp['product_name']); ?>"
                                    style="max-height: 180px; object-fit: contain;">
                            </div>
                        <?php else: ?>
                            <div class="card-img-top bg-secondary bg-opacity-25 text-white d-flex align-items-center justify-content-center"
                                style="height: 180px;">
                                <i class="bi bi-music-note-beamed fs-1 opacity-50"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column text-center border-top border-secondary border-opacity-50">
                            <span
                                class="badge bg-secondary mb-2 align-self-center"><?php echo sanitize_html($rp['brand']); ?></span>
                            <h6 class="card-title text-white fw-bold text-truncate mb-3"
                                title="<?php echo sanitize_html($rp['product_name']); ?>">
                                <?php echo sanitize_html($rp['product_name']); ?></h6>
                            <div class="mt-auto">
                                <span
                                    class="fs-5 fw-bold text-secondary-custom d-block mb-3">&#8377;<?php echo number_format($rp['price'], 2); ?></span>
                                <a href="view.php?id=<?php echo $rp['product_id']; ?>"
                                    class="btn btn-sm btn-outline-primary-custom w-100 fw-bold hover-glow">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- User Review Deletion Modals -->
<?php if (isset($product_reviews) && count($product_reviews) > 0): ?>
    <?php foreach ($product_reviews as $r): ?>
        <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $r['user_id'] || (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Admin'))): ?>
            <div class="modal fade" id="deleteReviewModal<?php echo $r['review_id']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content glass-panel border-danger">
                        <div class="modal-header border-secondary border-opacity-50">
                            <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Delete
                                Rating</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-white">
                            <p>Are you sure you want to delete this rating? This action cannot be undone.</p>
                            <div class="bg-dark p-3 rounded-3 border border-secondary mb-3">
                                <i class="bi bi-quote fs-4 text-secondary-custom me-2"></i>
                                <em class="opacity-75"><?php echo sanitize_html($r['comment']); ?></em>
                            </div>
                        </div>
                        <div class="modal-footer border-secondary border-opacity-50">
                            <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger fw-bold confirm-delete-review-btn"
                                data-id="<?php echo $r['review_id']; ?>" data-bs-dismiss="modal">
                                <i class="bi bi-trash-fill me-2"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>