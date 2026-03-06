<?php
require_once __DIR__ . '/../../includes/header.php';

// Pagination and Filtering Logic
$search = trim($_GET['search'] ?? '');
$category = $_GET['category'] ?? '';
$brand = $_GET['brand'] ?? '';
$skill_level = $_GET['skill_level'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

$sql = "SELECT p.*, c.category_name FROM Products p JOIN Categories c ON p.category_id = c.category_id WHERE p.is_active = 1";
$params = [];

if ($search !== '') {
    // Advanced FULLTEXT search
    $sql .= " AND MATCH(p.product_name, p.description, p.brand) AGAINST(? IN NATURAL LANGUAGE MODE)";
    $params[] = $search;
}

if ($category !== '') {
    $sql .= " AND p.category_id = ?";
    $params[] = $category;
}

if ($brand !== '') {
    $sql .= " AND p.brand = ?";
    $params[] = $brand;
}

if ($skill_level !== '') {
    $sql .= " AND p.skill_level = ?";
    $params[] = $skill_level;
}

if ($min_price !== '') {
    $sql .= " AND p.price >= ?";
    $params[] = $min_price;
}

if ($max_price !== '') {
    $sql .= " AND p.price <= ?";
    $params[] = $max_price;
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$wishlist_ids = [];
if (isset($_SESSION['user_id'])) {
    $w_stmt = $pdo->prepare("SELECT product_id FROM Wishlist WHERE user_id = ?");
    $w_stmt->execute([$_SESSION['user_id']]);
    $wishlist_ids = $w_stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Fetch Categories for filter
$cat_stmt = $pdo->query("SELECT * FROM Categories ORDER BY category_name");
$categories = $cat_stmt->fetchAll();

// Fetch Brands for filter
$brand_stmt = $pdo->query("SELECT DISTINCT brand FROM Products WHERE brand IS NOT NULL AND brand != '' AND is_active = 1 ORDER BY brand");
$brands = $brand_stmt->fetchAll();
?>
<div class="row pt-4 pb-5">
    <div class="col-lg-3 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary-custom text-white">
                <h5 class="mb-0"><i class="bi bi-funnel"></i> Filters</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="index.php">
                    <?php if ($search): ?>
                        <input type="hidden" name="search" value="<?php echo sanitize_html($search); ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Category</label>
                        <select name="category" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>" <?php echo ($category == $cat['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo sanitize_html($cat['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Brand</label>
                        <select name="brand" class="form-select form-select-sm">
                            <option value="">All Brands</option>
                            <?php foreach ($brands as $b): ?>
                                <option value="<?php echo sanitize_html($b['brand']); ?>" <?php echo ($brand === $b['brand']) ? 'selected' : ''; ?>>
                                    <?php echo sanitize_html($b['brand']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Skill Level</label>
                        <select name="skill_level" class="form-select form-select-sm">
                            <option value="">Any Skill Level</option>
                            <option value="Beginner" <?php echo ($skill_level === 'Beginner') ? 'selected' : ''; ?>>
                                Beginner</option>
                            <option value="Intermediate" <?php echo ($skill_level === 'Intermediate') ? 'selected' : ''; ?>>Intermediate</option>
                            <option value="Advanced" <?php echo ($skill_level === 'Advanced') ? 'selected' : ''; ?>>
                                Advanced</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Price Range ($)</label>
                        <div class="d-flex align-items-center">
                            <input type="number" name="min_price" class="form-control form-control-sm" placeholder="Min"
                                value="<?php echo sanitize_html($min_price); ?>">
                            <span class="mx-2 text-white opacity-75">-</span>
                            <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Max"
                                value="<?php echo sanitize_html($max_price); ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-secondary btn-sm w-100 fw-bold">Apply Filters</button>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm w-100 mt-2">Clear</a>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <h2 class="text-primary-custom mb-3 mb-md-0 fw-bold">Our Catalog</h2>

            <form method="GET" action="index.php" class="d-flex">
                <!-- Keep other filters in search -->
                <?php if ($category): ?><input type="hidden" name="category"
                        value="<?php echo htmlspecialchars($category); ?>"><?php endif; ?>
                <?php if ($brand): ?><input type="hidden" name="brand"
                        value="<?php echo htmlspecialchars($brand); ?>"><?php endif; ?>
                <?php if ($skill_level): ?><input type="hidden" name="skill_level"
                        value="<?php echo htmlspecialchars($skill_level); ?>"><?php endif; ?>
                <?php if ($min_price): ?><input type="hidden" name="min_price"
                        value="<?php echo htmlspecialchars($min_price); ?>"><?php endif; ?>
                <?php if ($max_price): ?><input type="hidden" name="max_price"
                        value="<?php echo htmlspecialchars($max_price); ?>"><?php endif; ?>

                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search instruments..."
                        value="<?php echo sanitize_html($search); ?>">
                    <button class="btn btn-primary px-3" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>
        </div>

        <?php if ($search): ?>
            <p class="text-white opacity-75"><i class="bi bi-info-circle me-1"></i> Showing results for:
                <strong><?php echo sanitize_html($search); ?></strong></p>
        <?php endif; ?>

        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $p): ?>
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="position-relative">
                                <?php $img_src = !empty($p['image_url']) ? '../../' . $p['image_url'] : 'https://images.unsplash.com/photo-1511192336575-5a79af67a629?w=400&h=300&fit=crop'; ?>
                                <img src="<?php echo sanitize_html($img_src); ?>" class="card-img-top product-img"
                                    alt="<?php echo sanitize_html($p['product_name']); ?>"
                                    style="height: 200px; object-fit: cover;">
                                <?php if ($p['stock_quantity'] <= 0): ?>
                                    <div class="position-absolute top-0 start-0 m-2 badge bg-danger fs-6 opacity-75">Out of Stock
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <?php $is_wished = in_array($p['product_id'], $wishlist_ids); ?>
                                    <button
                                        class="btn btn-dark position-absolute top-0 end-0 m-2 rounded-circle shadow-sm toggle-wishlist-btn"
                                        data-id="<?php echo $p['product_id']; ?>" style="width: 35px; height: 35px; padding: 0;">
                                        <i
                                            class="bi <?php echo $is_wished ? 'bi-heart-fill text-danger' : 'bi-heart text-white'; ?>"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="mb-2 d-flex justify-content-between align-items-center">
                                    <span
                                        class="badge badge-category border px-2 py-1"><?php echo sanitize_html($p['category_name']); ?></span>
                                    <?php
                                    $skill_color = 'bg-success';
                                    if ($p['skill_level'] == 'Intermediate')
                                        $skill_color = 'bg-warning text-dark';
                                    if ($p['skill_level'] == 'Advanced')
                                        $skill_color = 'bg-danger';
                                    ?>
                                    <span
                                        class="badge <?php echo $skill_color; ?> px-2 py-1"><?php echo sanitize_html($p['skill_level']); ?></span>
                                </div>
                                <h5 class="card-title text-primary-custom fw-bold mt-1 mb-1">
                                    <?php echo sanitize_html($p['product_name']); ?></h5>
                                <h6 class="card-subtitle mb-2 text-white opacity-75 fw-semibold">
                                    <?php echo sanitize_html($p['brand']); ?></h6>
                                <p class="card-text small text-white opacity-75 text-truncate" style="max-height: 4.5em;">
                                    <?php echo sanitize_html($p['description']); ?></p>

                                <div class="mt-auto d-flex justify-content-between align-items-center pt-3 border-top">
                                    <span
                                        class="fs-4 fw-bold text-secondary-custom">&#8377;<?php echo number_format($p['price'], 2); ?></span>
                                    <a href="view.php?id=<?php echo $p['product_id']; ?>"
                                        class="btn btn-sm btn-outline-primary fw-bold px-3">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 py-5 text-center w-100">
                    <i class="bi bi-music-note-list fs-1 text-white opacity-75 mb-3 d-block opacity-50"></i>
                    <h4 class="text-white opacity-75 fw-bold">No products found</h4>
                    <p class="text-white opacity-75">Try adjusting your filters or search query.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>