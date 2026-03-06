<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . base_url('index.php'));
    exit;
}

$error = '';
$message = '';
if (isset($_SESSION['login_message'])) {
    $message = $_SESSION['login_message'];
    unset($_SESSION['login_message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token. Please refresh.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = "Please enter both username and password.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM Users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Success: Regenerate session ID to prevent fixation
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type'];

                if (isset($_SESSION['checkout_redirect']) && $_SESSION['checkout_redirect'] === true) {
                    unset($_SESSION['checkout_redirect']);
                    header("Location: " . base_url('modules/orders/checkout.php'));
                } else {
                    header("Location: " . base_url('index.php'));
                }
                exit;
            } else {
                $error = "Invalid username or password.";
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
                    <i class="bi bi-box-arrow-in-right text-secondary-custom" style="font-size: 3rem;"></i>
                    <h2 class="mt-2 fw-bold">Welcome Back</h2>
                    <p class="text-light opacity-75">Login to TuneHub</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
                        <div><?php echo sanitize_html($error); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($message): ?>
                    <div class="alert alert-info d-flex align-items-center shadow-sm" role="alert">
                        <i class="bi bi-info-circle-fill flex-shrink-0 me-2 fs-5"></i>
                        <div><?php echo sanitize_html($message); ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="username" name="username" placeholder=" " required>
                        <label for="username">Username</label>
                    </div>

                    <div class="input-group mb-2">
                        <div class="form-floating flex-grow-1">
                            <input type="password" class="form-control" id="password" name="password" placeholder=" "
                                required>
                            <label for="password">Password</label>
                        </div>
                        <button class="btn btn-outline-secondary password-toggle" type="button"
                            style="border-color: rgba(255,255,255,0.2);"><i class="bi bi-eye"></i></button>
                    </div>

                    <div class="text-end mb-4">
                        <a href="forgot_password.php"
                            class="text-secondary-custom text-decoration-none small hover-primary transition-all">Forgot
                            password?</a>
                    </div>

                    <button type="submit" class="btn btn-secondary w-100 py-2 fs-5 fw-bold">Login</button>
                </form>

                <div class="mt-4 text-center">
                    <p class="mb-0 text-light opacity-75">Don't have an account? <a href="register.php"
                            class="text-secondary-custom fw-bold text-decoration-none">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>