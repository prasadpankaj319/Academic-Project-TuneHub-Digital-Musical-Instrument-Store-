<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

$action = $_POST['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        if ($action === 'update_role') {
            $uid = $_POST['user_id'];
            $role = $_POST['user_type'];

            // Prevent Admin from demoting themselves or changing own rank
            if ($uid != $_SESSION['user_id']) {
                $stmt = $pdo->prepare("UPDATE Users SET user_type=? WHERE user_id=?");
                $stmt->execute([$role, $uid]);
                $_SESSION['msg'] = "User role updated successfully.";
            } else {
                $_SESSION['err'] = "Action denied. You cannot modify your own administrative privileges.";
            }
        }
        header("Location: users.php");
        exit;
    }
}

$users = $pdo->query("SELECT user_id, username, email, user_type, created_at FROM Users ORDER BY created_at DESC")->fetchAll();

// All backend logic complete. Render DOM.
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="row pt-4 pb-5">
    <div class="col-md-3 mb-4">
        <div class="list-group shadow-sm border-0">
            <a href="index.php" class="list-group-item list-group-item-action"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            <a href="sales.php" class="list-group-item list-group-item-action"><i class="bi bi-graph-up-arrow me-2"></i> Sales Report</a>
            <a href="products.php" class="list-group-item list-group-item-action"><i class="bi bi-box me-2"></i> Products & Inventory</a>
            <a href="orders.php" class="list-group-item list-group-item-action"><i class="bi bi-box-seam me-2"></i> Manage Orders</a>
            <a href="users.php" class="list-group-item list-group-item-action active bg-primary-custom border-primary-custom fw-bold"><i class="bi bi-people me-2"></i> User Management</a>
            <a href="tutorials.php" class="list-group-item list-group-item-action"><i class="bi bi-youtube me-2"></i> Manage Tutorials</a>
            <a href="reviews.php" class="list-group-item list-group-item-action"><i class="bi bi-star-fill me-2"></i> Manage Reviews</a>
            <a href="promos.php" class="list-group-item list-group-item-action"><i class="bi bi-tags-fill me-2"></i> Promo Codes</a>
        </div>
    </div>
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
            <h2 class="text-primary-custom fw-bold m-0"><i class="bi bi-people-fill me-2"></i>User Management</h2>
        </div>

        <?php if(isset($_SESSION['msg'])): ?>
            <div class="alert alert-success d-flex align-items-center shadow-sm"><i class="bi bi-check-circle-fill me-2 fs-5"></i><?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['err'])): ?>
            <div class="alert alert-danger d-flex align-items-center shadow-sm"><i class="bi bi-shield-x me-2 fs-5"></i><?php echo $_SESSION['err']; unset($_SESSION['err']); ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom pt-4 pb-3">
                <h5 class="fw-bold mb-0 text-white">Registered Accounts</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-white opacity-75 small text-uppercase tracking-wider">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Account Info</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th class="pe-4 text-end">Action / Change Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $u): ?>
                            <tr>
                                <td class="ps-4 text-white opacity-75 fw-bold">#<?php echo $u['user_id']; ?></td>
                                <td>
                                    <div class="fw-bold text-white d-flex align-items-center">
                                        <?php if($u['user_type'] == 'Admin'): ?>
                                            <i class="bi bi-shield-check text-primary-custom me-2" title="Admin"></i>
                                        <?php else: ?>
                                            <i class="bi bi-person text-secondary me-2"></i>
                                        <?php endif; ?>
                                        <?php echo sanitize_html($u['username']); ?>
                                        <?php if($u['user_id'] == $_SESSION['user_id']): ?>
                                            <span class="badge text-white opacity-75 border ms-2">You</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small text-white opacity-75 ms-4"><a href="mailto:<?php echo sanitize_html($u['email']); ?>" class="text-decoration-none text-white opacity-75"><i class="bi bi-envelope me-1"></i><?php echo sanitize_html($u['email']); ?></a></div>
                                </td>
                                <td>
                                    <?php if($u['user_type'] == 'Admin'): ?>
                                        <span class="badge bg-primary-custom px-2 py-1"><i class="bi bi-star-fill text-warning me-1 small"></i>Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary px-2 py-1">Customer</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-white opacity-75 small"><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                                <td class="pe-4 text-end">
                                    <form method="POST" class="d-flex justify-content-end align-items-center">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="action" value="update_role">
                                        <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                                        <select name="user_type" class="form-select form-select-sm d-inline-block w-auto me-2 shadow-sm" <?php echo ($u['user_id']==$_SESSION['user_id']) ? 'disabled' : ''; ?> onchange="this.form.submit()">
                                            <option value="Customer" <?php echo $u['user_type']=='Customer' ? 'selected' : ''; ?>>Customer</option>
                                            <option value="Admin" <?php echo $u['user_type']=='Admin' ? 'selected' : ''; ?>>Admin</option>
                                        </select>
                                        <noscript>
                                            <button type="submit" class="btn btn-sm btn-secondary" <?php echo ($u['user_id']==$_SESSION['user_id']) ? 'disabled' : ''; ?>>Save</button>
                                        </noscript>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
