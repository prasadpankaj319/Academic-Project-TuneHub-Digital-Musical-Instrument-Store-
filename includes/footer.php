    </main>
    <footer class="bg-primary-custom text-white pt-5 pb-3 mt-auto">
        <div class="container">
            <div class="row mb-4">
                <!-- Brand & Address -->
                <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                    <h5 class="fs-4 fw-bold mb-3 d-flex align-items-center"><i class="bi bi-music-note-beamed me-2 text-secondary-custom"></i> TuneHub</h5>
                    <p class="small opacity-75 mb-4">Your ultimate destination for premium digital musical instruments. We provide top-tier gear, verified reviews, and expert tutorials to elevate your sound.</p>
                    <ul class="list-unstyled opacity-75 small">
                        <li class="mb-2"><i class="bi bi-geo-alt-fill me-2 text-secondary-custom"></i> 421401, Mumbai, MH, INDIA</li>
                        <li class="mb-2"><i class="bi bi-envelope-fill me-2 text-secondary-custom"></i> support@tunehub.com</li>
                        <li><i class="bi bi-telephone-fill me-2 text-secondary-custom"></i> +91 7709100274</li>
                    </ul>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6 mb-4 mb-lg-0 py-lg-1">
                    <h6 class="text-uppercase fw-bold mb-3 tracking-wider text-secondary-custom">Explore</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?php echo base_url('index.php'); ?>" class="text-white text-decoration-none opacity-75 hover-primary transition-all">Home</a></li>
                        <li class="mb-2"><a href="<?php echo base_url('modules/products/index.php'); ?>" class="text-white text-decoration-none opacity-75 hover-primary transition-all">Catalog</a></li>
                        <li class="mb-2"><a href="<?php echo base_url('modules/tutorials/index.php'); ?>" class="text-white text-decoration-none opacity-75 hover-primary transition-all">Tutorials</a></li>
                        <li class="mb-2"><a href="<?php echo base_url('modules/cart/index.php'); ?>" class="text-white text-decoration-none opacity-75 hover-primary transition-all">Shopping Cart</a></li>
                        <li class="mb-0"><a href="#" data-bs-toggle="modal" data-bs-target="#newsModal" class="text-white text-decoration-none opacity-75 hover-primary transition-all">Latest News</a></li>
                    </ul>
                </div>

                <!-- Customer Service -->
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0 py-lg-1">
                    <h6 class="text-uppercase fw-bold mb-3 tracking-wider text-secondary-custom">Support</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?php echo base_url('modules/user/login.php'); ?>" class="text-white text-decoration-none opacity-75 hover-primary transition-all">My Account</a></li>
                        <li class="mb-2"><a href="<?php echo base_url('modules/orders/index.php'); ?>" class="text-white text-decoration-none opacity-75 hover-primary transition-all">Order Status</a></li>
                        <li class="mb-2"><a href="<?php echo base_url('modules/user/profile.php'); ?>" class="text-white text-decoration-none opacity-75 hover-primary transition-all">My Profile</a></li>
                        <li class="mb-2"><a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal" class="text-white text-decoration-none opacity-75 hover-primary transition-all">Privacy Policy</a></li>
                        <li class="mb-0"><a href="#" data-bs-toggle="modal" data-bs-target="#termsModal" class="text-white text-decoration-none opacity-75 hover-primary transition-all">Terms of Service</a></li>
                    </ul>
                </div>

                <!-- Feedback & Social -->
                <div class="col-lg-3 col-md-6 py-lg-1">
                    <h6 class="text-uppercase fw-bold mb-3 tracking-wider text-secondary-custom">Ideas & Feedback</h6>
                    <p class="small opacity-75 mb-2">Have a suggestion to improve TuneHub? We'd love to hear it!</p>
                    <form id="footerFeedbackForm" class="mb-3">
                        <div class="mb-2">
                            <input type="text" name="name" class="form-control form-control-sm border-secondary mb-2" placeholder="Your Name" style="background-color: rgba(0,0,0,0.2) !important; color: #ffffff !important;" required>
                            <input type="email" name="email" class="form-control form-control-sm border-secondary mb-2" placeholder="Your Email" style="background-color: rgba(0,0,0,0.2) !important; color: #ffffff !important;" required>
                            <textarea id="suggestionMessage" name="message" class="form-control form-control-sm border-secondary" rows="2" placeholder="Share your suggestion..." style="resize: none; background-color: rgba(0,0,0,0.2) !important; color: #ffffff !important;" required></textarea>
                        </div>
                        <style>
                            /* Override Bootstrap's native form-control placeholder opacity to ensure readability */
                            footer form .form-control::placeholder {
                                color: rgba(255,255,255,0.6) !important;
                                opacity: 1 !important;
                            }
                        </style>
                        <button class="btn btn-secondary btn-sm fw-bold w-100" type="submit">Submit Suggestion</button>
                    </form>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const sfForm = document.getElementById('footerFeedbackForm');
                            if (sfForm) {
                                sfForm.addEventListener('submit', async function(e) {
                                    e.preventDefault();
                                    const btn = this.querySelector('button[type="submit"]');
                                    const origText = btn.innerHTML;
                                    btn.innerHTML = '<span class="spinner-border spinner-border-sm align-middle me-2"></span>Sending...';
                                    btn.disabled = true;

                                    try {
                                        const res = await fetch('<?php echo base_url("submit_feedback.php"); ?>', {
                                            method: 'POST',
                                            body: new FormData(this)
                                        });
                                        const data = await res.json();
                                        if (data.success) {
                                            btn.innerHTML = '<i class="bi bi-check2-circle text-success me-2 align-middle"></i>Sent!';
                                            this.reset();
                                            setTimeout(() => { btn.innerHTML = origText; btn.disabled = false; }, 3000);
                                        } else {
                                            alert(data.message);
                                            btn.innerHTML = origText; btn.disabled = false;
                                        }
                                    } catch (err) {
                                        alert('Connection error. Try again.');
                                        btn.innerHTML = origText; btn.disabled = false;
                                    }
                                });
                            }
                        });
                    </script>
                    <div>
                        <a href="#" class="text-white me-3 text-decoration-none opacity-75 hover-primary transition-all"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="#" class="text-white me-3 text-decoration-none opacity-75 hover-primary transition-all"><i class="bi bi-instagram fs-5"></i></a>
                        <a href="#" class="text-white me-3 text-decoration-none opacity-75 hover-primary transition-all"><i class="bi bi-twitter fs-5"></i></a>
                        <a href="#" class="text-white text-decoration-none opacity-75 hover-primary transition-all"><i class="bi bi-youtube fs-5"></i></a>
                    </div>
                </div>
            </div>

            <!-- Copyright Banner -->
            <div class="border-top border-secondary pt-4 mt-2 d-flex flex-column flex-md-row justify-content-between align-items-center">
                <p class="mb-0 small text-white opacity-50">&copy; <?php echo date('Y'); ?> TuneHub. All rights reserved.</p>
                <div class="mt-2 mt-md-0 d-flex gap-2 opacity-50">
                    <i class="bi bi-credit-card-2-front fs-4"></i>
                    <i class="bi bi-paypal fs-4"></i>
                    <i class="bi bi-stripe fs-4"></i>
                </div>
            </div>
        </div>

        <?php require_once __DIR__ . '/modals.php'; ?>
    </footer>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Main JS -->
    <script src="<?php echo base_url('assets/js/main.js'); ?>"></script>
    <!-- Advanced Animations JS -->
    <script src="<?php echo base_url('assets/js/animations.js'); ?>"></script>
</body>
</html>
