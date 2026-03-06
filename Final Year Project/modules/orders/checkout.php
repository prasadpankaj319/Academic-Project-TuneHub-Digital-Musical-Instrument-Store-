<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['checkout_redirect'] = true;
    $_SESSION['login_message'] = "Please log in or register to complete your checkout.";
    header("Location: " . base_url('modules/user/login.php'));
    exit;
}

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header("Location: " . base_url('modules/cart/index.php'));
    exit;
}

$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

$discount_percent = $_SESSION['discount_percent'] ?? 0;
$promo_code = $_SESSION['promo_code'] ?? '';
$discount_amount = $total * ($discount_percent / 100);
$final_total = $total - $discount_amount;
?>
<div class="row pt-4 pb-5 justify-content-center">
    <div class="col-md-9 col-lg-7">
        <div class="card shadow border-0">
            <div class="card-header bg-primary-custom text-white p-4">
                <h3 class="mb-0 fw-bold"><i class="bi bi-lock-fill me-2"></i>Secure Checkout</h3>
            </div>
            <div class="card-body p-4 p-md-5">
                <h5 class="fw-bold mb-4 text-secondary-custom border-bottom pb-2">1. Order Summary</h5>
                <ul class="list-group mb-5 shadow-sm">
                    <?php foreach ($cart as $id => $item): ?>
                        <li class="list-group-item d-flex justify-content-between lh-sm p-3">
                            <div>
                                <h6 class="my-0 fw-bold text-white"><?php echo sanitize_html($item['name']); ?></h6>
                                <small class="text-white opacity-75">Quantity: <?php echo $item['quantity']; ?></small>
                            </div>
                            <span
                                class="text-white opacity-75 fw-semibold">&#8377;<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </li>
                    <?php endforeach; ?>
                    <?php if ($discount_percent > 0): ?>
                        <li
                            class="list-group-item d-flex justify-content-between p-3 border-top-0 bg-dark border-secondary">
                            <span class="text-success fw-bold"><i class="bi bi-tag-fill me-1"></i> Discount
                                (<?php echo sanitize_html($promo_code); ?>)</span>
                            <span
                                class="text-success fw-bold">-&#8377;<?php echo number_format($discount_amount, 2); ?></span>
                        </li>
                    <?php endif; ?>
                    <li class="list-group-item d-flex justify-content-between p-3 border-top-0">
                        <span class="fw-bold text-primary-custom fs-5">Total (INR)</span>
                        <strong
                            class="fs-4 text-secondary-custom">&#8377;<?php echo number_format($final_total, 2); ?></strong>
                    </li>
                </ul>

                <form method="POST" action="../payment/process.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="total_amount" value="<?php echo $final_total; ?>">

                    <h5 class="fw-bold mb-4 text-secondary-custom border-bottom pb-2">2. Delivery Address</h5>
                    <div class="mb-5 border rounded p-4 bg-dark">
                        <label class="form-label fw-bold text-white mb-2">Full Shipping Address <span
                                class="text-danger">*</span></label>
                        <textarea class="form-control" name="shipping_address" rows="3"
                            placeholder="123 Example Street, Apartment 4B, City, Country, ZIP 12345"
                            required></textarea>
                        <div class="form-text text-white opacity-50 mt-2 small"><i class="bi bi-geo-alt-fill me-1"></i>
                            Please double check your shipping location to prevent delivery delays.</div>
                    </div>

                    <h5 class="fw-bold mb-4 text-secondary-custom border-bottom pb-2">3. Payment Details</h5>

                    <div class="alert alert-info border-0 shadow-sm mb-4 d-flex align-items-center">
                        <i class="bi bi-info-circle-fill me-3 fs-3 text-info"></i>
                        <div class="small">This is a simulated checkout. No real cards will be charged. Clicking Place
                            Order will trigger a DB transaction to adjust inventory safely before creating your invoice.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-white">Select Payment Method</label>
                        <div class="d-flex flex-column flex-md-row gap-3">
                            <div class="form-check form-check-inline border rounded p-3 m-0 flex-fill">
                                <input class="form-check-input ms-1 me-2 payment-radio" type="radio"
                                    name="payment_method" id="pay_card" value="Card" checked>
                                <label class="form-check-label fw-bold d-flex align-items-center cursor-pointer"
                                    for="pay_card">
                                    <i class="bi bi-credit-card-fill text-primary-custom me-2 fs-5"></i> Credit/Debit
                                    Card
                                </label>
                            </div>
                            <div class="form-check form-check-inline border rounded p-3 m-0 flex-fill">
                                <input class="form-check-input ms-1 me-2 payment-radio" type="radio"
                                    name="payment_method" id="pay_upi" value="UPI">
                                <label class="form-check-label fw-bold d-flex align-items-center cursor-pointer"
                                    for="pay_upi">
                                    <i class="bi bi-phone-fill text-success me-2 fs-5"></i> UPI
                                </label>
                            </div>
                            <div class="form-check form-check-inline border rounded p-3 m-0 flex-fill">
                                <input class="form-check-input ms-1 me-2 payment-radio" type="radio"
                                    name="payment_method" id="pay_cod" value="COD">
                                <label class="form-check-label fw-bold d-flex align-items-center cursor-pointer"
                                    for="pay_cod">
                                    <i class="bi bi-cash-stack text-warning me-2 fs-5"></i> Cash on Delivery
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="card-fields" class="row gy-4 mb-5 p-4 border rounded">
                        <div class="col-12">
                            <label class="form-label fw-bold text-white opacity-75 small text-uppercase">Name on
                                card</label>
                            <input type="text" class="form-control" name="cc_name" id="cc_name" placeholder="John Doe"
                                required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-white opacity-75 small text-uppercase">Credit card
                                number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i
                                        class="bi bi-credit-card-fill text-white opacity-75"></i></span>
                                <input type="text" class="form-control" name="cc_number" id="cc_number"
                                    placeholder="0000 0000 0000 0000" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label
                                class="form-label fw-bold text-white opacity-75 small text-uppercase">Expiration</label>
                            <input type="text" class="form-control" name="cc_exp" id="cc_exp" placeholder="MM/YY"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-white opacity-75 small text-uppercase">CVV</label>
                            <input type="text" class="form-control" name="cc_cvv" id="cc_cvv" placeholder="123"
                                required>
                        </div>
                    </div>

                    <div id="upi-fields" class="row gy-4 mb-5 p-4 border rounded d-none">
                        <div class="col-12">
                            <label class="form-label fw-bold text-white opacity-75 small text-uppercase">Enter your UPI
                                ID</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-phone text-white opacity-75"></i></span>
                                <input type="text" class="form-control" name="upi_id" id="upi_id"
                                    placeholder="username@okbank">
                            </div>
                            <div class="form-text text-white opacity-75 small mt-2">A payment request will be sent to
                                this UPI App.</div>
                        </div>
                    </div>

                    <div id="cod-fields" class="row gy-4 mb-5 p-4 border rounded d-none">
                        <div class="col-12 text-center">
                            <i class="bi bi-box-seam fs-1 text-secondary-custom mb-2"></i>
                            <h5 class="fw-bold text-white">Pay at Doorstep</h5>
                            <p class="text-white opacity-75 small mb-0">Please keep exact change ready. You will pay the
                                delivery executive once your products arrive.</p>
                        </div>
                    </div>

                    <button class="btn btn-secondary w-100 py-3 fs-4 fw-bold text-uppercase tracking-wider shadow"
                        type="submit">
                        <i class="bi bi-shield-lock-fill me-2"></i>Place Order
                    </button>

                    <div class="text-center mt-4">
                        <a href="../cart/index.php"
                            class="text-decoration-none text-white opacity-75 fw-bold hover-primary">
                            <i class="bi bi-arrow-left me-1"></i>Return to Cart
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const radios = document.querySelectorAll('.payment-radio');
        const cardFields = document.getElementById('card-fields');
        const upiFields = document.getElementById('upi-fields');
        const codFields = document.getElementById('cod-fields');

        // Input fields for validation toggling
        const ccInputs = [
            document.getElementById('cc_name'),
            document.getElementById('cc_number'),
            document.getElementById('cc_exp'),
            document.getElementById('cc_cvv')
        ];
        const upiInput = document.getElementById('upi_id');

        radios.forEach(radio => {
            radio.addEventListener('change', function () {
                // Hide all standard
                cardFields.classList.add('d-none');
                upiFields.classList.add('d-none');
                codFields.classList.add('d-none');

                // Clear reqs
                ccInputs.forEach(el => el.required = false);
                upiInput.required = false;

                if (this.value === 'Card') {
                    cardFields.classList.remove('d-none');
                    ccInputs.forEach(el => el.required = true);
                } else if (this.value === 'UPI') {
                    upiFields.classList.remove('d-none');
                    upiInput.required = true;
                } else if (this.value === 'COD') {
                    codFields.classList.remove('d-none');
                    // COD needs no inputs
                }
            });
        });
    });
</script>