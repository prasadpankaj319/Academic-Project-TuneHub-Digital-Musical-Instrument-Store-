<?php
require_once __DIR__ . '/includes/header.php';
?>

<!-- Ambient Stage Lighting -->
<div class="ambient-glow ambient-glow-primary" style="opacity: 0.15; top: 10%;"></div>

<div class="container my-5 pt-5 reveal-up">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="display-5 text-primary-custom fw-bold mb-3 text-center"><i class="bi bi-question-circle me-3"></i>Frequently Asked Questions</h1>
            <p class="lead text-white opacity-75 text-center mb-5">Everything you need to know about shopping, shipping, and supporting your sound at TuneHub.</p>

            <div class="accordion accordion-flush rounded-4 overflow-hidden border border-secondary shadow-sm" id="faqAccordion">

                <!-- FAQ 1 -->
                <div class="accordion-item bg-dark border-bottom border-secondary">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button collapsed bg-dark text-white fw-bold py-4 px-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                            <i class="bi bi-truck text-primary-custom me-3 fs-5"></i> What are your standard shipping times?
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-white opacity-75 px-5 pb-4 lh-lg">
                            Standard physical instruments are securely packed in acoustic-grade shock-absorbent materials and shipped within <strong>3-5 business days</strong>. Digital software, virtual instruments, and tutorials are delivered instantly to your TuneHub account upon successful checkout.
                        </div>
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="accordion-item bg-dark border-bottom border-secondary">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed bg-dark text-white fw-bold py-4 px-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            <i class="bi bi-arrow-return-left text-secondary-custom me-3 fs-5"></i> What is your return policy?
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-white opacity-75 px-5 pb-4 lh-lg">
                            We offer a strict <strong>30-day return window</strong> for physical goods. Non-defective returns must be in originally sealed, unblemished condition to avoid a 15% restocking fee. Due to licensing constraints, digital software purchases are non-refundable once the activation key is generated.
                        </div>
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="accordion-item bg-dark border-bottom border-secondary">
                    <h2 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed bg-dark text-white fw-bold py-4 px-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            <i class="bi bi-shield-check text-success me-3 fs-5"></i> Do your instruments carry a warranty?
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-white opacity-75 px-5 pb-4 lh-lg">
                            Absolutely. Every new piece of hardware comes standard with the manufacturer's original 1-year limited electronic/structural warranty. We handle the warranty claim processing entirely on our end so you focus solely on playing.
                        </div>
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="accordion-item bg-dark border-bottom border-secondary">
                    <h2 class="accordion-header" id="headingFour">
                        <button class="accordion-button collapsed bg-dark text-white fw-bold py-4 px-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                            <i class="bi bi-credit-card text-info me-3 fs-5"></i> How secure are my payments?
                        </button>
                    </h2>
                    <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-white opacity-75 px-5 pb-4 lh-lg">
                            TuneHub utilizes AES-256 equivalent server-side hashing mapped strictly against PCI-DSS Level 1 compliant external tokenizers. This means your raw credit card data never touches our internal MySQL databases.
                        </div>
                    </div>
                </div>

                <!-- FAQ 5 -->
                <div class="accordion-item bg-dark">
                    <h2 class="accordion-header" id="headingFive">
                        <button class="accordion-button collapsed bg-dark text-white fw-bold py-4 px-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                            <i class="bi bi-person-lines-fill text-danger me-3 fs-5"></i> I need technical support. How do I contact you?
                        </button>
                    </h2>
                    <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-white opacity-75 px-5 pb-4 lh-lg">
                            Our team of professional musicians and technicians is available Monday through Friday (9 AM - 6 PM IST). You can submit a direct request via our <a href="contact.php" class="text-primary-custom">Contact Us</a> page, or reach us directly at <strong>+91 7709100274</strong>.
                        </div>
                    </div>
                </div>

            </div>

            <div class="text-center mt-5">
                <p class="text-white opacity-50 small mb-2">Still have questions?</p>
                <a href="<?php echo base_url('contact.php'); ?>" class="btn btn-outline-secondary px-4 fw-bold">Contact Support</a>
            </div>

        </div>
    </div>
</div>

<style>
/* Accordion custom styling for dark mode */
.accordion-button::after {
    filter: invert(1) grayscale(100%) brightness(200%);
}
.accordion-button:not(.collapsed) {
    background-color: rgba(0, 0, 0, 0.4) !important;
    color: var(--primary) !important;
    box-shadow: inset 0 -1px 0 rgba(0,0,0,.125);
}
.accordion-button:focus {
    box-shadow: none;
    border-color: rgba(255,255,255,0.1);
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
