<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . base_url('index.php'));
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token. Please refresh and try again.";
    } elseif (!isset($_POST['terms'])) {
        $error = "You must agree to the Terms and Conditions to register.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($username) || empty($email) || empty($password)) {
            $error = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Check if username or email exists
            $stmt = $pdo->prepare("SELECT user_id FROM Users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error = "Username or Email already exists.";
            } else {
                // Hash password using bcrypt and cost 12
                $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

                $stmt = $pdo->prepare("INSERT INTO Users (username, email, password_hash) VALUES (?, ?, ?)");
                if ($stmt->execute([$username, $email, $password_hash])) {
                    if (isset($_SESSION['checkout_redirect']) && $_SESSION['checkout_redirect'] === true) {
                        $success = "Registration successful! You can now <a href='login.php' class='alert-link fw-bold text-secondary-custom'>Login Here</a> to complete your checkout.";
                    } else {
                        $success = "Registration successful! You can now <a href='login.php' class='alert-link fw-bold text-secondary-custom'>Login Here</a>.";
                    }
                } else {
                    $error = "Registration failed. Try again please.";
                }
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
                    <i class="bi bi-person-plus-fill text-primary-custom" style="font-size: 3rem;"></i>
                    <h2 class="mt-2 fw-bold">Create Account</h2>
                    <p class="text-light opacity-75">Join TuneHub to personalize your gear</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
                        <div><?php echo sanitize_html($error); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success d-flex align-items-center bg-transparent border-success text-success"
                        style="backdrop-filter: blur(5px);" role="alert">
                        <i class="bi bi-check-circle-fill flex-shrink-0 me-2"></i>
                        <div><?php echo $success; ?></div>
                    </div>
                <?php else: ?>
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="username" name="username" placeholder=" " required>
                            <label for="username">Username</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="email" name="email" placeholder=" " required>
                            <label for="email">Email address</label>
                        </div>

                        <div class="input-group mb-3">
                            <div class="form-floating flex-grow-1">
                                <input type="password" class="form-control" id="password" name="password" placeholder=" "
                                    required>
                                <label for="password">Password</label>
                            </div>
                            <button class="btn btn-outline-secondary password-toggle" type="button"
                                style="border-color: rgba(255,255,255,0.2);"><i class="bi bi-eye"></i></button>
                        </div>

                        <div class="input-group mb-4">
                            <div class="form-floating flex-grow-1">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                    placeholder=" " required>
                                <label for="confirm_password">Confirm Password</label>
                            </div>
                            <button class="btn btn-outline-secondary password-toggle" type="button"
                                style="border-color: rgba(255,255,255,0.2);"><i class="bi bi-eye"></i></button>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" value="1" id="terms" name="terms" required>
                            <label class="form-check-label small text-light opacity-75" for="terms">
                                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal"
                                    class="text-secondary-custom text-decoration-none">Terms and Conditions</a> and <a
                                    href="#" data-bs-toggle="modal" data-bs-target="#privacyModal"
                                    class="text-secondary-custom text-decoration-none">Privacy Policy</a>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-secondary w-100 py-2 fs-5 fw-bold">Register</button>
                    </form>

                    <div class="mt-4 text-center">
                        <p class="mb-0 text-light opacity-75">Already have an account? <a href="login.php"
                                class="text-primary-custom fw-bold text-decoration-none">Log in</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/modals.php'; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>