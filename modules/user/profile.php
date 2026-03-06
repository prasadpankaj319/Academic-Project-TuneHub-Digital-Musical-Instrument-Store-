<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/auth.php';

require_login();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $new_password = $_POST['new_password'] ?? '';

        if (empty($email)) {
            $error = "Email cannot be empty.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } else {
            // Check if email belongs to someone else
            $stmt = $pdo->prepare("SELECT user_id FROM Users WHERE email = ? AND user_id != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetch()) {
                $error = "Email is already in use by another account.";
            } else {
                if (!empty($new_password)) {
                    // Update email and password
                    $new_hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
                    $update_stmt = $pdo->prepare("UPDATE Users SET email = ?, password_hash = ? WHERE user_id = ?");
                    $update_stmt->execute([$email, $new_hash, $user_id]);
                } else {
                    // Update email only
                    $update_stmt = $pdo->prepare("UPDATE Users SET email = ? WHERE user_id = ?");
                    $update_stmt->execute([$email, $user_id]);
                }
                $success = "Profile updated successfully.";
            }
        }
    }
}

// Fetch current user details
$stmt = $pdo->prepare("SELECT username, email, user_type, created_at FROM Users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

?>
<div class="row pt-4 pb-5">
    <div class="col-md-4 col-lg-3 mb-4">
        <div class="list-group shadow-sm">
            <a href="profile.php" class="list-group-item list-group-item-action active bg-primary-custom border-primary-custom">
                <i class="bi bi-person-circle me-2"></i> My Profile
            </a>
            <a href="wishlist.php" class="list-group-item list-group-item-action">
                <i class="bi bi-heart-fill me-2"></i> My Wishlist
            </a>
            <a href="../orders/index.php" class="list-group-item list-group-item-action">
                <i class="bi bi-box-seam me-2"></i> Order History
            </a>
            <a href="logout.php" class="list-group-item list-group-item-action text-danger mt-3 border-top">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </div>
    </div>
    <div class="col-md-8 col-lg-9">
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom-0 pt-4 pb-0">
                <h3 class="mb-0 text-primary-custom fw-bold">Profile Information</h3>
                <p class="text-white opacity-75 small">Member since <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
            </div>
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center"><i class="bi bi-exclamation-circle-fill me-2"></i> <?php echo sanitize_html($error); ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success d-flex align-items-center"><i class="bi bi-check-circle-fill me-2"></i> <?php echo sanitize_html($success); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label text-white opacity-75 fw-bold">Username</label>
                            <input type="text" class="form-control" value="<?php echo sanitize_html($user['username']); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-white opacity-75 fw-bold">Account Type</label>
                            <input type="text" class="form-control" value="<?php echo sanitize_html($user['user_type']); ?>" disabled>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="email" class="form-label fw-bold">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo sanitize_html($user['email']); ?>" required>
                    </div>

                    <hr class="my-4 text-white opacity-75">

                    <h5 class="mb-3 text-secondary-custom fw-bold"><i class="bi bi-shield-lock me-2"></i> Security</h5>
                    <div class="mb-4">
                        <label for="new_password" class="form-label text-white opacity-75">New Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢">
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
