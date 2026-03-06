<?php
require_once __DIR__ . '/../../includes/header.php';

// Fetch all tutorials with product info
$stmt = $pdo->query("SELECT t.*, p.product_name, p.brand FROM Tutorials t JOIN Products p ON t.product_id = p.product_id WHERE t.is_active = 1 ORDER BY t.tutorial_id DESC");
$tutorials = $stmt->fetchAll();
?>
<div class="row pt-4 pb-5">
    <div class="col-12 mb-5 text-center">
        <h2 class="text-primary-custom fw-bold display-5 mb-3"><i class="bi bi-youtube text-danger me-2"></i>Masterclass
            Tutorials</h2>
        <p class="text-white opacity-75 fs-5">Learn from the best. Curated lessons for your specific instruments.</p>
    </div>

    <div class="col-12">
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-5">
            <?php foreach ($tutorials as $t): ?>
                <div class="col">
                    <div class="card h-100 border-0 shadow hover-lift rounded-4 overflow-hidden">
                        <div class="ratio ratio-16x9 bg-dark">
                            <iframe src="<?php echo htmlspecialchars($t['video_url']); ?>"
                                title="<?php echo sanitize_html($t['title']); ?>" allowfullscreen></iframe>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <h5 class="card-title fw-bold text-white mb-3 lh-base"><?php echo sanitize_html($t['title']); ?>
                            </h5>
                            <div class="mt-auto d-flex align-items-center p-3 rounded-3"
                                style="background: rgba(0,0,0,0.2);">
                                <i class="bi bi-music-note-list fs-4 text-primary-custom me-3 border-end pe-3"></i>
                                <div>
                                    <span class="d-block small text-white opacity-75 text-uppercase tracking-wider fw-bold"
                                        style="font-size:0.7rem;">Featured Instrument</span>
                                    <a href="../products/view.php?id=<?php echo $t['product_id']; ?>"
                                        class="text-decoration-none fw-bold text-secondary-custom hover-primary">
                                        <?php echo sanitize_html($t['brand'] . ' ' . $t['product_name']); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($tutorials)): ?>
                <div class="col-12 py-5 text-center w-100">
                    <i class="bi bi-camera-video-off fs-1 text-white opacity-75 mb-3 d-block opacity-25"
                        style="font-size: 5rem;"></i>
                    <h4 class="text-white opacity-75 fw-bold">No tutorials available</h4>
                    <p class="text-white opacity-75">Check back later for new video lessons.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>