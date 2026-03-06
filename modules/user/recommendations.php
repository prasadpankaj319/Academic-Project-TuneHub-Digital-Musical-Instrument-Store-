<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/auth.php';

require_login();

$user_id = $_SESSION['user_id'];

// Recommendation Engine Algorithm
// Weighting: Category (40%), Brand (25%), Skill level (20%), Price similarity (15%)

// Step 1: Get user's preferred attributes from recent purchases
$stmt = $pdo->prepare("SELECT p.category_id, p.brand, p.skill_level, p.price FROM Orders o JOIN Order_Items oi ON o.order_id=oi.order_id JOIN Products p ON oi.product_id=p.product_id WHERE o.user_id = ? ORDER BY o.created_at DESC LIMIT 10");
$stmt->execute([$user_id]);
$purchases = $stmt->fetchAll();

$recommendations = [];
$is_fallback = false;

if (empty($purchases)) {
    // Fallback: Random popular products
    $stmt = $pdo->query("SELECT p.*, c.category_name FROM Products p JOIN Categories c ON p.category_id = c.category_id WHERE p.is_active = 1 ORDER BY p.stock_quantity DESC LIMIT 6");
    $recommendations = $stmt->fetchAll();
    $is_fallback = true;
} else {
    // Calculate simple profile averages/most common terms
    $categories = array_count_values(array_column($purchases, 'category_id'));
    arsort($categories);
    $pref_cat = array_key_first($categories);

    $brands = array_count_values(array_column($purchases, 'brand'));
    arsort($brands);
    $pref_brand = array_key_first($brands);

    $skills = array_count_values(array_column($purchases, 'skill_level'));
    arsort($skills);
    $pref_skill = array_key_first($skills);

    $prices = array_column($purchases, 'price');
    $pref_price = array_sum($prices) / count($prices);

    // Max allowable price diff for scoring
    $max_diff = $pref_price * 0.5;

    // Fetch products user hasn't bought yet
    $bought_stmt = $pdo->prepare("SELECT oi.product_id FROM Order_Items oi JOIN Orders o ON oi.order_id=o.order_id WHERE o.user_id=?");
    $bought_stmt->execute([$user_id]);
    $b_ids = $bought_stmt->fetchAll(PDO::FETCH_COLUMN);
    $b_ids[] = 0; // Prevent empty IN clause

    $placeholders = str_repeat('?,', count($b_ids)-1) . '?';
    $all_prods_stmt = $pdo->prepare("SELECT p.*, c.category_name FROM Products p JOIN Categories c ON p.category_id=c.category_id WHERE p.is_active = 1 AND p.product_id NOT IN ($placeholders)");
    $all_prods_stmt->execute($b_ids);
    $all_prods = $all_prods_stmt->fetchAll();

    foreach ($all_prods as $p) {
        $score = 0;

        // Category match: 40%
        if ($p['category_id'] == $pref_cat) $score += 0.40;

        // Brand match: 25%
        if ($p['brand'] == $pref_brand) $score += 0.25;

        // Skill level match: 20%
        if ($p['skill_level'] == $pref_skill) $score += 0.20;

        // Price similarity: 15%
        $diff = abs($p['price'] - $pref_price);
        if ($diff <= $max_diff) {
            $price_score = 0.15 * (1 - ($diff / $max_diff));
            $score += $price_score;
        }

        // Only recommend if there's some relevance
        if ($score > 0) {
            $p['rec_score'] = $score;
            $recommendations[] = $p;
        }
    }

    // Sort by score DESC
    usort($recommendations, function($a, $b) {
        return $b['rec_score'] <=> $a['rec_score'];
    });

    // Take top 6
    $recommendations = array_slice($recommendations, 0, 6);
}
?>
<div class="row pt-4 pb-5">
    <div class="col-md-3 mb-4">
        <div class="list-group shadow-sm border-0">
            <a href="profile.php" class="list-group-item list-group-item-action"><i class="bi bi-person-circle me-2"></i> My Profile</a>
            <a href="../orders/index.php" class="list-group-item list-group-item-action"><i class="bi bi-box-seam me-2"></i> Order History</a>
            <a href="recommendations.php" class="list-group-item list-group-item-action active bg-primary-custom border-primary-custom fw-bold"><i class="bi bi-magic me-2"></i> Recommendations</a>
            <a href="logout.php" class="list-group-item list-group-item-action text-danger mt-3 border-top"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom-0 pt-4 pb-0 d-flex align-items-center">
                <i class="bi bi-magic text-secondary-custom fs-2 me-3"></i>
                <div>
                    <h3 class="mb-0 text-primary-custom fw-bold">Personalized For You</h3>
                    <p class="text-white opacity-75 small mb-0">Powered by TuneHub's Recommendation Engine</p>
                </div>
            </div>
            <div class="card-body p-4">
                <?php if ($is_fallback): ?>
                    <div class="alert alert-info shadow-sm border-0 d-flex align-items-center mb-4 p-4 rounded-3 border-start border-info border-4">
                        <i class="bi bi-info-circle-fill me-3 fs-3 text-info"></i>
                        <div>We don't have enough purchase history yet. Start buying to get custom matches! Exploring our most popular instruments below to get started.</div>
                    </div>
                <?php else: ?>
                    <p class="text-white opacity-75 mb-4 p-3 rounded border-start border-secondary-custom border-4" style="background: rgba(0,0,0,0.2);">
                        <i class="bi bi-calculator-fill text-white opacity-75 me-2"></i>
                        <strong>Match Algorithm:</strong> Category affinity (40%), Brand affinity (25%), Skill level match (20%), and Price range similarity (15%). Matches below threshold are discarded.
                    </p>
                <?php endif; ?>

                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 mt-2">
                    <?php if (count($recommendations) > 0): ?>
                        <?php foreach ($recommendations as $p): ?>
                            <div class="col">
                                <div class="card h-100 border-0 shadow-sm hover-lift">
                                    <div class="position-relative">
                                        <?php $rec_img = !empty($p['image_url']) ? '../../' . $p['image_url'] : 'https://images.unsplash.com/photo-1511192336575-5a79af67a629?w=300&h=200&fit=crop'; ?>
                                        <img src="<?php echo sanitize_html($rec_img); ?>" class="card-img-top product-img" alt="<?php echo sanitize_html($p['product_name']); ?>" style="height: 180px; object-fit: cover;">
                                        <?php if(isset($p['rec_score'])): ?>
                                        <div class="position-absolute top-0 end-0 m-2 badge <?php echo $p['rec_score'] > 0.7 ? 'bg-success' : 'bg-primary-custom'; ?> fs-6 opacity-75 shadow-sm">
                                            <i class="bi bi-bullseye me-1"></i><?php echo round($p['rec_score'] * 100); ?>% Match
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body d-flex flex-column p-4">
                                        <div class="mb-2 d-flex justify-content-between align-items-center">
                                            <span class="badge badge-category border px-2 py-1"><?php echo sanitize_html($p['category_name']); ?></span>
                                            <span class="badge bg-secondary px-2 py-1"><?php echo sanitize_html($p['skill_level']); ?></span>
                                        </div>
                                        <h6 class="card-title text-primary-custom fw-bold mt-2 mb-1"><?php echo sanitize_html($p['product_name']); ?></h6>
                                        <div class="text-white opacity-75 small fw-semibold mb-3"><?php echo sanitize_html($p['brand']); ?></div>

                                        <div class="mt-auto d-flex justify-content-between align-items-center pt-3 border-top border-secondary opacity-75">
                                            <span class="fs-5 fw-bold text-secondary-custom">&#8377;<?php echo number_format($p['price'], 2); ?></span>
                                            <a href="../products/view.php?id=<?php echo $p['product_id']; ?>" class="btn btn-sm btn-outline-primary fw-bold px-3">View</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 py-5 text-center w-100">
                            <i class="bi bi-emoji-smile fs-1 text-white opacity-75 mb-3 d-block opacity-25"></i>
                            <h4 class="text-white opacity-75 fw-bold">You've explored it all!</h4>
                            <p class="text-white opacity-75">You seem to own all our currently cataloged instruments. Check back later for new arrivals.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
