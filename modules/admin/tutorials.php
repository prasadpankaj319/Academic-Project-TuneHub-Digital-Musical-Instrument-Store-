<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

$action = $_POST['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        if ($action === 'create' || $action === 'update') {
            $product_id = $_POST['product_id'];
            $title = trim($_POST['title']);
            $video_url = trim($_POST['video_url']);

            if ($action === 'create') {
                $stmt = $pdo->prepare("INSERT INTO Tutorials (product_id, title, video_url) VALUES (?, ?, ?)");
                $stmt->execute([$product_id, $title, $video_url]);
                $_SESSION['msg'] = "Tutorial successfully added.";
            } else {
                $tid = $_POST['tutorial_id'];
                $stmt = $pdo->prepare("UPDATE Tutorials SET product_id=?, title=?, video_url=? WHERE tutorial_id=?");
                $stmt->execute([$product_id, $title, $video_url, $tid]);
                $_SESSION['msg'] = "Tutorial successfully updated.";
            }
        } elseif ($action === 'archive') {
            $tid = $_POST['tutorial_id'];
            $stmt = $pdo->prepare("UPDATE Tutorials SET is_active = 0 WHERE tutorial_id=?");
            $stmt->execute([$tid]);
            $_SESSION['msg'] = "Tutorial completely archived.";
        } elseif ($action === 'unarchive') {
            $tid = $_POST['tutorial_id'];
            $stmt = $pdo->prepare("UPDATE Tutorials SET is_active = 1 WHERE tutorial_id=?");
            $stmt->execute([$tid]);
            $_SESSION['msg'] = "Tutorial unarchived. It is now live on the public storefront again.";
        }
        header("Location: tutorials.php");
        exit;
    }
}

// Fetch lists
$tutorials = $pdo->query("SELECT t.*, p.product_name, p.brand FROM Tutorials t JOIN Products p ON t.product_id=p.product_id ORDER BY t.tutorial_id DESC")->fetchAll();
$products = $pdo->query("SELECT product_id, product_name, brand FROM Products ORDER BY product_name ASC")->fetchAll();

$edit_id = $_GET['edit'] ?? null;
$edit_tuto = null;
if ($edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM Tutorials WHERE tutorial_id=?");
    $stmt->execute([$edit_id]);
    $edit_tuto = $stmt->fetch();
}

// All backend logic complete. Render DOM.
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="row pt-4 pb-5">
    <div class="col-md-3 mb-4">
        <div class="list-group shadow-sm border-0">
            <a href="index.php" class="list-group-item list-group-item-action"><i class="bi bi-speedometer2 me-2"></i>
                Dashboard</a>
            <a href="sales.php" class="list-group-item list-group-item-action"><i class="bi bi-graph-up-arrow me-2"></i>
                Sales Report</a>
            <a href="products.php" class="list-group-item list-group-item-action"><i class="bi bi-box me-2"></i>
                Products & Inventory</a>
            <a href="orders.php" class="list-group-item list-group-item-action"><i class="bi bi-box-seam me-2"></i>
                Manage Orders</a>
            <a href="users.php" class="list-group-item list-group-item-action"><i class="bi bi-people me-2"></i> User
                Management</a>
            <a href="tutorials.php"
                class="list-group-item list-group-item-action active bg-primary-custom border-primary-custom fw-bold"><i
                    class="bi bi-youtube me-2"></i> Manage Tutorials</a>
            <a href="reviews.php" class="list-group-item list-group-item-action"><i class="bi bi-star-fill me-2"></i>
                Manage Reviews</a>
            <a href="promos.php" class="list-group-item list-group-item-action"><i class="bi bi-tags-fill me-2"></i>
                Promo Codes</a>
        </div>
    </div>

    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
            <h2 class="text-primary-custom fw-bold m-0"><i class="bi bi-camera-video me-2"></i>Tutorial Management</h2>
            <?php if ($edit_tuto): ?>
                <a href="tutorials.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add
                    New</a>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['msg'])): ?>
            <div class="alert alert-success d-flex align-items-center shadow-sm"><i
                    class="bi bi-check-circle-fill me-2 fs-5"></i><?php echo $_SESSION['msg'];
                    unset($_SESSION['msg']); ?>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <div class="card shadow-sm border-0 mb-5">
            <div class="card-header border-bottom pt-3 pb-2 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-white">
                    <?php echo $edit_tuto ? "<i class='bi bi-pencil-square text-primary-custom me-2'></i>Edit Tutorial Lesson" : "<i class='bi bi-plus-circle-fill text-primary-custom me-2'></i>Publish New Tutorial"; ?>
                </h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="tutorials.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="action" value="<?php echo $edit_tuto ? 'update' : 'create'; ?>">
                    <?php if ($edit_tuto): ?>
                        <input type="hidden" name="tutorial_id" value="<?php echo $edit_tuto['tutorial_id']; ?>">
                    <?php endif; ?>

                    <div class="row gy-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-white opacity-75 small">Tutorial Title</label>
                            <input type="text" name="title" class="form-control"
                                value="<?php echo $edit_tuto ? sanitize_html($edit_tuto['title']) : ''; ?>"
                                placeholder="e.g. Mastering the Minor Pentatonic" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-white opacity-75 small">Target Instrument
                                Match</label>
                            <select name="product_id" class="form-select" required>
                                <option value="" disabled <?php echo !$edit_tuto ? 'selected' : ''; ?>>Select a paired
                                    product...</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?php echo $p['product_id'] ?>" <?php echo ($edit_tuto && $edit_tuto['product_id'] == $p['product_id']) ? 'selected' : ''; ?>>
                                        <?php echo sanitize_html($p['brand'] . ' - ' . $p['product_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-white opacity-75 small"><i
                                    class="bi bi-youtube text-danger me-1"></i> Video Embed URL</label>
                            <input type="url" name="video_url" class="form-control"
                                value="<?php echo $edit_tuto ? sanitize_html($edit_tuto['video_url']) : ''; ?>"
                                placeholder="e.g. https://www.youtube.com/embed/dQw4w9WgXcQ" required>
                            <div class="form-text text-white opacity-50 mt-2 small"><i
                                    class="bi bi-info-circle me-1"></i> Paste the explicit embed URL (not the standard
                                watch link) to ensure valid iframe rendering.</div>
                        </div>

                        <div class="col-12 mt-4 pt-4 border-top d-flex gap-2">
                            <button class="btn btn-primary px-5 fw-bold" type="submit">
                                <i
                                    class="bi <?php echo $edit_tuto ? 'bi-save' : 'bi-plus-lg'; ?> me-2"></i><?php echo $edit_tuto ? 'Update Video' : 'Publish Tutorial'; ?>
                            </button>
                            <?php if ($edit_tuto): ?>
                                <a href="tutorials.php" class="btn btn-outline-secondary px-4 fw-bold">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Catalog List -->
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom pt-4 pb-3">
                <h5 class="fw-bold mb-0 text-white"><i class="bi bi-collection-play me-2"></i>Active Tutorial Library
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-white opacity-75 small text-uppercase tracking-wider">
                            <tr>
                                <th class="ps-4">Preview</th>
                                <th>Lesson Title</th>
                                <th>Target Instrument</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tutorials as $t): ?>
                                <tr class="<?php echo $t['is_active'] == 0 ? 'opacity-50' : ''; ?>">
                                    <td class="ps-4" style="width: 140px;">
                                        <div
                                            class="ratio ratio-16x9 bg-dark rounded shadow-sm overflow-hidden border border-secondary">
                                            <iframe src="<?php echo htmlspecialchars($t['video_url']); ?>"
                                                title="<?php echo sanitize_html($t['title']); ?>"
                                                style="pointer-events: none;" tabindex="-1"></iframe>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-white fs-6">
                                            <?php echo sanitize_html($t['title']); ?>
                                            <?php if ($t['is_active'] == 0): ?>
                                                <span class="badge bg-danger ms-2" style="font-size: 0.70rem;">ARCHIVED</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="products.php?edit=<?php echo $t['product_id']; ?>"
                                            class="badge badge-category border px-2 py-1 text-decoration-none hover-primary">
                                            <i
                                                class="bi bi-link-45deg me-1"></i><?php echo sanitize_html($t['brand'] . ' ' . $t['product_name']); ?>
                                        </a>
                                    </td>
                                    <td class="text-end pe-4" style="width: 120px;">
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="tutorials.php?edit=<?php echo $t['tutorial_id']; ?>"
                                                class="btn btn-sm btn-outline-primary shadow-sm"><i
                                                    class="bi bi-pencil-fill"></i></a>
                                            <?php if ($t['is_active'] == 1): ?>
                                                <button type="button" class="btn btn-sm btn-outline-warning shadow-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#archiveTutorialModal<?php echo $t['tutorial_id']; ?>"
                                                    title="Archive">
                                                    <i class="bi bi-archive-fill"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-outline-success shadow-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#unarchiveTutorialModal<?php echo $t['tutorial_id']; ?>"
                                                    title="Unarchive">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($tutorials)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <i class="bi bi-film fs-1 text-white opacity-25 d-block mb-3"></i>
                                        <p class="text-white opacity-75 mb-0 fw-bold">Your tutorial library is completely
                                            empty.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Custom TuneHub Delete Tutorial Modals -->
<?php if (!empty($tutorials)): ?>
    <?php foreach ($tutorials as $t): ?>
        <div class="modal fade" id="archiveTutorialModal<?php echo $t['tutorial_id']; ?>" tabindex="-1"
            aria-labelledby="archiveTutorialModalLabel<?php echo $t['tutorial_id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content glass-panel" style="text-align: left;">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title fw-bold text-warning"
                            id="archiveTutorialModalLabel<?php echo $t['tutorial_id']; ?>">
                            <i class="bi bi-archive-fill me-2"></i>Archive Tutorial Video
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-white opacity-75 mb-3" style="font-size: 1.05rem;">Are you absolutely sure you want to
                            archive the tutorial:<br><strong><?php echo sanitize_html($t['title']); ?></strong>?</p>
                        <div class="p-3 bg-warning bg-opacity-25 rounded border border-warning text-white small">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i><strong>Notice</strong><br>
                            This tutorial will be archived and hidden from the public Tutorials page, but will be preserved in
                            your records.
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                        <form method="POST" action="tutorials.php" class="m-0">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="action" value="archive">
                            <input type="hidden" name="tutorial_id" value="<?php echo $t['tutorial_id']; ?>">
                            <button type="submit" class="btn btn-warning fw-bold text-dark">Yes, Archive</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="unarchiveTutorialModal<?php echo $t['tutorial_id']; ?>" tabindex="-1"
            aria-labelledby="unarchiveTutorialModalLabel<?php echo $t['tutorial_id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content glass-panel" style="text-align: left;">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title fw-bold text-success"
                            id="unarchiveTutorialModalLabel<?php echo $t['tutorial_id']; ?>">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Restore Tutorial Video
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-white opacity-75 mb-3" style="font-size: 1.05rem;">Are you absolutely sure you want to
                            restore the tutorial:<br><strong><?php echo sanitize_html($t['title']); ?></strong>?</p>
                        <div class="p-3 bg-success bg-opacity-25 rounded border border-success text-white small">
                            <i class="bi bi-check-circle-fill text-success me-2"></i><strong>Notice</strong><br>
                            This tutorial will immediately become visible to users again.
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                        <form method="POST" action="tutorials.php" class="m-0">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="action" value="unarchive">
                            <input type="hidden" name="tutorial_id" value="<?php echo $t['tutorial_id']; ?>">
                            <button type="submit" class="btn btn-success fw-bold px-4">Yes, Unarchive Live</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>