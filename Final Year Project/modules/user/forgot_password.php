<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/mail.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . base_url('index.php'));
    exit;
}

$error = '';
$success = '';
$simulated_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token. Please refresh.";
    } else {
        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT user_id, username FROM Users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Determine existing token to prevent spam
                $clean_stmt = $pdo->prepare("DELETE FROM Password_Resets WHERE email = ?");
                $clean_stmt->execute([$email]);

                // Generate Cryptographic Token
                $token = bin2hex(random_bytes(32));

                // Set Expiration (1 Hour via Unix Timestamp)
                $expires_at = time() + 3600;

                // Insert into Database
                $insert_stmt = $pdo->prepare("INSERT INTO Password_Resets (email, token, expires_at) VALUES (?, ?, ?)");

                if ($insert_stmt->execute([$email, $token, $expires_at])) {
                    // Encode spaces in the URL to prevent the link from breaking in email clients
                    $reset_link = str_replace(' ', '%20', base_url('modules/user/reset_password.php?token=' . $token));

                    try {
                        $mail = get_mailer();
                        $mail->addAddress($email, $user['username']);
                        $mail->Subject = 'TuneHub Password Reset Request';
                        $mail->isHTML(true);
                        $mail->Body = "
                            <p>Hello " . sanitize_html($user['username']) . ",</p>
                            <p>We received a request to reset your TuneHub password. This link will expire in exactly 1 hour.</p>
                            <p><a href='{$reset_link}' style='display:inline-block;padding:10px 20px;background-color:#0d6efd;color:#fff;text-decoration:none;border-radius:5px;'>Reset My Password</a></p>
                            <p>Or copy and paste this URL into your browser:</p>
                            <p><a href='{$reset_link}' style='color:#0d6efd;word-break:break-all;'>{$reset_link}</a></p>
                            <p>If you did not request a password reset, please ignore this email.</p>
                        ";
                        $mail->send();
                        $success = "If an account exists for that email, a password reset link has been sent.";
                    } catch (Exception $e) {
                        $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                } else {
                    $error = "A system error occurred generating the token.";
                }
            } else {
                // Generic success message to prevent email enumeration (Security Best Practice)
                $success = "If an account exists for that email, a password reset link has been sent.";
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
                    <i class="bi bi-shield-lock text-primary-custom" style="font-size: 3rem;"></i>
                    <h3 class="mt-2 fw-bold">Recover Password</h3>
                    <p class="text-light opacity-75 small">Enter the email associated with your account.</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
                        <div class="small"><?php echo sanitize_html($error); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success d-flex align-items-center" role="alert">
                        <i class="bi bi-check-circle-fill flex-shrink-0 me-2"></i>
                        <div class="small"><?php echo sanitize_html($success); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($success && empty($error)): ?>
                    <p class="text-center text-white opacity-75 mt-3">Please check your inbox (and spam folder) for the
                        recovery email.</p>
                <?php else: ?>
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                        <div class="form-floating mb-4">
                            <input type="email" class="form-control" id="email" name="email" placeholder=" " required>
                            <label for="email">Email Address</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fs-6 fw-bold shadow-sm">Send Recovery
                            Link</button>
                    </form>
                <?php endif; ?>

                <div class="mt-4 text-center">
                    <a href="login.php"
                        class="text-light opacity-75 text-decoration-none hover-primary transition-all small"><i
                            class="bi bi-arrow-left me-1"></i>Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>