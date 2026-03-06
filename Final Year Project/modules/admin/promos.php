<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

// Handle Actions (Add/Toggle/Delete) BEFORE standard HTML header injection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $code = strtoupper(trim($_POST['code']));
            $discount = (float) $_POST['discount_percent'];
            $valid_until = !empty($_POST['valid_until']) ? $_POST['valid_until'] : null;

            try {
                $stmt = $pdo->prepare("INSERT INTO Promotions (code, discount_percent, valid_until) VALUES (?, ?, ?)");
                $stmt->execute([$code, $discount, $valid_until]);
                $_SESSION['msg'] = "Promotion code '$code' created successfully.";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $_SESSION['err'] = "Error: Promo code '$code' already exists.";
                } else {
                    $_SESSION['err'] = "Error crafting promotion: " . $e->getMessage();
                }
            }
            header("Location: promos.php");
            exit;

        } elseif ($action === 'toggle') {
            $promo_id = (int) $_POST['promo_id'];
            $current_status = (int) $_POST['current_status'];
            $new_status = $current_status ? 0 : 1;

            $stmt = $pdo->prepare("UPDATE Promotions SET is_active = ? WHERE promo_id = ?");
            $stmt->execute([$new_status, $promo_id]);
            $_SESSION['msg'] = "Promotion status toggled.";
            header("Location: promos.php");
            exit;

        } elseif ($action === 'delete') {
            $promo_id = (int) $_POST['promo_id'];
            $stmt = $pdo->prepare("DELETE FROM Promotions WHERE promo_id = ?");
            $stmt->execute([$promo_id]);
            $_SESSION['msg'] = "Promotion permanently deleted.";
            header("Location: promos.php");
            exit;
        }
    } else {
        $_SESSION['err'] = "CSRF validation failed.";
    }
}

require_once __DIR__ . '/../../includes/header.php';

// Fetch all promos
$stmt = $pdo->query("SELECT * FROM Promotions ORDER BY created_at DESC");
$promos = $stmt->fetchAll();
?>

<div class="container mt-5 pt-5 pb-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="list-group shadow-sm border-0">
                <a href="index.php" class="list-group-item list-group-item-action"><i
                        class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                <a href="sales.php" class="list-group-item list-group-item-action"><i
                        class="bi bi-graph-up-arrow me-2"></i> Sales Report</a>
                <a href="products.php" class="list-group-item list-group-item-action"><i class="bi bi-box me-2"></i>
                    Products & Inventory</a>
                <a href="orders.php" class="list-group-item list-group-item-action"><i class="bi bi-box-seam me-2"></i>
                    Manage Orders</a>
                <a href="users.php" class="list-group-item list-group-item-action"><i class="bi bi-people me-2"></i>
                    User Management</a>
                <a href="tutorials.php" class="list-group-item list-group-item-action"><i
                        class="bi bi-youtube me-2"></i> Manage Tutorials</a>
                <a href="reviews.php" class="list-group-item list-group-item-action"><i
                        class="bi bi-star-fill me-2"></i> Manage Reviews</a>
                <a href="promos.php"
                    class="list-group-item list-group-item-action active bg-primary-custom border-primary-custom fw-bold"><i
                        class="bi bi-tags-fill me-2"></i> Promo Codes</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <?php if (isset($_SESSION['msg'])): ?>
                <div class="alert alert-success"><i
                        class="bi bi-check-circle-fill me-2"></i><?php echo $_SESSION['msg'];
                        unset($_SESSION['msg']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['err'])): ?>
                <div class="alert alert-danger"><i
                        class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $_SESSION['err'];
                        unset($_SESSION['err']); ?>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header border-bottom pt-3 pb-2 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-white"><i
                            class="bi bi-plus-circle-fill text-primary-custom me-2"></i>Create New Promo</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="promos.php" class="row gx-3 gy-2 align-items-end">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="add">
                        <div class="col-sm-4">
                            <label class="form-label fw-bold text-white opacity-75 small">Code String</label>
                            <input type="text" name="code" class="form-control text-uppercase"
                                placeholder="e.g. SUMMER20" required>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label fw-bold text-white opacity-75 small">Discount (%)</label>
                            <input type="number" name="discount_percent" class="form-control" min="1" max="100"
                                placeholder="20" required>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label fw-bold text-white opacity-75 small">Expiry (Optional)</label>
                            <input type="datetime-local" name="valid_until" class="form-control">
                        </div>
                        <div class="col-sm-2">
                            <button type="submit" class="btn btn-primary-custom w-100 fw-bold">Generate</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header border-bottom-0 pt-4 pb-0">
                    <h3 class="mb-0 text-primary-custom fw-bold">Active Promotions</h3>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($promos)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-tags text-white opacity-25 d-block mb-3" style="font-size: 4rem;"></i>
                            <p class="text-white opacity-50">No promotional codes found in the system.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light text-white opacity-75 small text-uppercase tracking-wider">
                                    <tr>
                                        <th>Code</th>
                                        <th>Discount</th>
                                        <th>Status</th>
                                        <th>Expires</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($promos as $p): ?>
                                        <?php
                                        $is_expired = !empty($p['valid_until']) && strtotime($p['valid_until']) < time();
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-primary-custom fs-5 tracking-widest"
                                                    style="letter-spacing: 2px;"><?php echo sanitize_html($p['code']); ?></div>
                                            </td>
                                            <td>
                                                <div class="text-white fw-bold fs-5">
                                                    <?php echo (float) $p['discount_percent']; ?>%</div>
                                            </td>
                                            <td>
                                                <?php if ($is_expired): ?>
                                                    <span class="badge bg-secondary">Expired</span>
                                                <?php elseif ($p['is_active']): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Disabled</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="text-white opacity-50 small">
                                                    <?php echo !empty($p['valid_until']) ? date('M j, Y g:i A', strtotime($p['valid_until'])) : 'Never'; ?>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <form method="POST" action="promos.php" class="d-inline-block">
                                                    <input type="hidden" name="csrf_token"
                                                        value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="promo_id" value="<?php echo $p['promo_id']; ?>">
                                                    <input type="hidden" name="current_status"
                                                        value="<?php echo $p['is_active']; ?>">
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-<?php echo $p['is_active'] ? 'warning' : 'success'; ?> me-2"
                                                        title="<?php echo $p['is_active'] ? 'Disable' : 'Enable'; ?>">
                                                        <i
                                                            class="bi bi-<?php echo $p['is_active'] ? 'slash-circle' : 'check-circle'; ?>"></i>
                                                    </button>
                                                </form>

                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deletePromoModal<?php echo $p['promo_id']; ?>"
                                                    title="Permanently Delete">
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
</div>

<!-- Dynamic Delete Promo Modals -->
<?php if (isset($promos) && count($promos) > 0): ?>
    <?php foreach ($promos as $p): ?>
        <div class="modal fade" id="deletePromoModal<?php echo $p['promo_id']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content glass-panel border-danger">
                    <div class="modal-header border-secondary border-opacity-50">
                        <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Delete
                            Promotional Code</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-white">
                        <p class="mb-4">Are you certain you want to permanently obliterate this discount string? This action
                            cannot be undone.</p>

                        <div class="bg-dark p-3 rounded-3 border border-secondary mb-3 text-center">
                            <h4 class="fw-bold tracking-widest text-primary-custom mb-1 highlight-pulse"
                                style="letter-spacing: 3px;"><?php echo sanitize_html($p['code']); ?></h4>
                            <span class="badge bg-secondary"><?php echo (float) $p['discount_percent']; ?>% Discount
                                Target</span>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary border-opacity-50">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                        <form method="POST" action="promos.php" class="m-0 p-0">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="promo_id" value="<?php echo $p['promo_id']; ?>">
                            <button type="submit" class="btn btn-danger fw-bold">
                                <i class="bi bi-trash-fill me-2"></i>Terminate
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>