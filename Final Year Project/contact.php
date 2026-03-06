<?php
require_once __DIR__ . '/includes/header.php';

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_query') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_msg = "Invalid security token. Please try again.";
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $error_msg = "Please fill out all required fields.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO Queries (name, email, subject, message) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $email, $subject, $message]);
                $success_msg = "Thank you, $name. Your inquiry has been dispatched to our engineering desk. We'll be in touch soon.";
            } catch (PDOException $e) {
                $error_msg = "A routing error occurred. Please try again later.";
            }
        }
    }
}
?>

<!-- Ambient Stage Lighting -->
<div class="ambient-glow ambient-glow-green" style="opacity: 0.15;"></div>

<div class="container my-5 pt-5 reveal-up">
    <div class="row mb-5 text-center">
        <div class="col-12">
            <h1 class="display-4 text-primary-custom fw-bold mb-3"><i class="bi bi-headset me-3 text-secondary-custom"></i>Contact Us</h1>
            <p class="lead text-white opacity-75">We're here to help you dial in your perfect tone.</p>
            
            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success border-success bg-dark text-success mt-4 d-inline-block text-start">
                    <i class="bi bi-check-circle-fill me-2"></i><?php echo sanitize_html($success_msg); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger border-danger bg-dark text-danger mt-4 d-inline-block text-start">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo sanitize_html($error_msg); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-5">
        <!-- Contact Info & Map Column -->
        <div class="col-lg-5">
            <div class="glass-panel p-5 rounded-4 h-100">
                <h3 class="fw-bold border-bottom border-dark pb-3 mb-4 text-primary-custom">Get in Touch</h3>

                <div class="d-flex align-items-start mb-4 text-white opacity-75">
                    <i class="bi bi-geo-alt-fill fs-4 me-3 text-secondary-custom mt-1"></i>
                    <div>
                        <h6 class="fw-bold text-white mb-1">Headquarters</h6>
                        <p class="mb-0 small">421401, Mumbai<br>Maharashtra, INDIA</p>
                    </div>
                </div>

                <div class="d-flex align-items-start mb-4 text-white opacity-75">
                    <i class="bi bi-envelope-fill fs-4 me-3 text-secondary-custom mt-1"></i>
                    <div>
                        <h6 class="fw-bold text-white mb-1">Email Support</h6>
                        <p class="mb-0 small">support@tunehub.com</p>
                    </div>
                </div>

                <div class="d-flex align-items-start mb-5 text-white opacity-75">
                    <i class="bi bi-telephone-fill fs-4 me-3 text-secondary-custom mt-1"></i>
                    <div>
                        <h6 class="fw-bold text-white mb-1">Phone Line</h6>
                        <p class="mb-0 small">+91 7709100274<br>(Mon-Fri, 9AM - 6PM IST)</p>
                    </div>
                </div>

                <!-- Google Maps Embed Replica -->
                <div class="rounded overflow-hidden border border-secondary" style="height: 200px; background-color: #111; position:relative;">
                    <div class="position-absolute top-50 start-50 translate-middle text-center w-100 opacity-50 px-3">
                        <i class="bi bi-geo me-2 fs-3 mb-2 d-block"></i>
                        <span class="small">[ Google Maps Iframe Projection Area ]</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Form Column -->
        <div class="col-lg-7">
            <div class="glass-panel p-5 rounded-4 h-100">
                <h3 class="fw-bold mb-4 text-primary-custom">Send a Message</h3>
                <p class="text-white opacity-75 small mb-4">Have a question about a specific guitar? Need help resetting your password? Drop us a line and our team will get back to you within 24 hours.</p>

                <form action="contact.php" method="POST" class="needs-validation">
                    <input type="hidden" name="action" value="submit_query">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 form-floating">
                            <input type="text" name="name" class="form-control bg-dark border-secondary text-white" id="contactName" placeholder=" " required style="color: #fff !important; background-color: rgba(0,0,0,0.3) !important;">
                            <label for="contactName" class="text-white opacity-75 ms-2">Full Name</label>
                        </div>
                        <div class="col-md-6 form-floating">
                            <input type="email" name="email" class="form-control bg-dark border-secondary text-white" id="contactEmail" placeholder=" " required style="color: #fff !important; background-color: rgba(0,0,0,0.3) !important;">
                            <label for="contactEmail" class="text-white opacity-75 ms-2">Email Address</label>
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <select name="subject" class="form-select bg-dark border-secondary text-white" id="contactSubject" aria-label="Subject" style="color: #fff !important; background-color: rgba(0,0,0,0.3) !important;">
                            <option value="General Inquiry" selected>General Inquiry</option>
                            <option value="Order Status">Order Status</option>
                            <option value="Technical Support">Technical Support</option>
                            <option value="Returns/Refunds">Returns / Refunds</option>
                        </select>
                        <label for="contactSubject" class="text-white opacity-75 ms-2">Subject</label>
                    </div>

                    <div class="form-floating mb-4">
                        <textarea name="message" class="form-control bg-dark border-secondary text-white" id="contactMessage" placeholder=" " style="height: 150px; resize: none; color: #fff !important; background-color: rgba(0,0,0,0.3) !important;" required></textarea>
                        <label for="contactMessage" class="text-white opacity-75 ms-2">Your Message</label>
                    </div>

                    <button type="submit" class="btn btn-secondary btn-lg w-100 fw-bold py-3"><i class="bi bi-send-fill me-2"></i>Send Message</button>
                    <p class="small text-center mt-3 text-white opacity-50 mb-0"><i class="bi bi-lock-fill me-1"></i> Your connection to TuneHub is secure.</p>
                </form>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
