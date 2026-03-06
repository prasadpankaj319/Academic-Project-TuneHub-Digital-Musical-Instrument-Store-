<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . base_url('index.php'));
    exit;
}

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

// Block access if no token exists in the URL
if (empty($token)) {
    $error = "Invalid or missing password reset token.";
} else {
    // Verify token exists and hasn't expired (using raw Unix boundaries)
    $stmt = $pdo->prepare("SELECT * FROM Password_Resets WHERE token = ? AND expires_at > UNIX_TIMESTAMP()");
    $stmt->execute([$token]);
    $reset_record = $stmt->fetch();

    if (!$reset_record) {
        $error = "This password reset token is invalid or has expired. Please request a new one.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token. Please refresh.";
    } else {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (strlen($password) < 8) {
            $error = "Password must be at least 8 characters long.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Token is valid and passwords match. Proceed with Hash Override.
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $email = $reset_record['email'];

            try {
                $pdo->beginTransaction();

                // Update User Password
                $update_stmt = $pdo->prepare("UPDATE Users SET password_hash = ? WHERE email = ?");
                $update_stmt->execute([$password_hash, $email]);

                // Cleanup: Delete used token to prevent replay attacks
                $del_stmt = $pdo->prepare("DELETE FROM Password_Resets WHERE email = ?");
                $del_stmt->execute([$email]);

                $pdo->commit();
                $success = "Your password has been successfully reset! You can now login.";

            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "A database error occurred. Please try again.";
            }
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center pt-5 pb-5 reveal-up">
    <div class="col-md-6 col-lg-5 col-xl-4 pb-5">
        <div class="card glass-panel shadow-lg border-0">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-key-fill text-primary-custom" style="font-size: 3rem;"></i>
                    <h3 class="mt-2 fw-bold">Set New Password</h3>
                    <p class="text-light opacity-75 small">Securely update your TuneHub credentials.</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="bi bi-x-circle-fill flex-shrink-0 me-2"></i>
                        <div class="small"><?php echo sanitize_html($error); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success d-flex align-items-center mb-0" role="alert">
                        <i class="bi bi-check-circle-fill flex-shrink-0 me-2"></i>
                        <div class="small"><?php echo sanitize_html($success); ?></div>
                    </div>
                    <a href="login.php" class="btn btn-primary w-100 py-2 mt-4 fs-6 fw-bold shadow-sm">Proceed to Login</a>
                <?php else: ?>

                    <?php if (!empty($token) && empty($error)): ?>
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                            <div class="input-group mb-3">
                                <div class="form-floating flex-grow-1">
                                    <input type="password" class="form-control" id="password" name="password" placeholder=" " required minlength="8">
                                    <label for="password">New Password (8+ chars)</label>
                                </div>
                                <button class="btn btn-outline-secondary password-toggle" type="button" style="border-color: rgba(255,255,255,0.2);"><i class="bi bi-eye"></i></button>
                            </div>

                            <div class="input-group mb-4">
                                <div class="form-floating flex-grow-1">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder=" " required minlength="8">
                                    <label for="confirm_password">Confirm New Password</label>
                                </div>
                                <button class="btn btn-outline-secondary password-toggle" type="button" style="border-color: rgba(255,255,255,0.2);"><i class="bi bi-eye"></i></button>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fs-6 fw-bold shadow-sm"><i class="bi bi-lock-fill me-2"></i>Save Final Password</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($error && strpos($error, 'invalid or has expired') !== false): ?>
                        <a href="forgot_password.php" class="btn btn-outline-primary w-100 py-2 mt-2 fs-6 fw-bold">Request New Link</a>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
