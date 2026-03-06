<?php
require_once __DIR__ . '/../../includes/header.php';

$cart = $_SESSION['cart'] ?? [];
$total = 0;
$discount_percent = $_SESSION['discount_percent'] ?? 0;
$promo_code = $_SESSION['promo_code'] ?? '';

$cart_items = [];
if (!empty($cart)) {
    // Collect specific product info directly from DB based on ID
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $stmt = $pdo->query("SELECT product_id, product_name, price, stock_quantity, image_url, category_id, brand FROM Products WHERE product_id IN ($ids) AND is_active = 1");
    $db_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($db_products as $p) {
        $pid = $p['product_id'];
        $qty = $cart[$pid]['quantity'];

        // Final stock check boundary mapping to session
        if ($qty > $p['stock_quantity']) {
            $qty = $p['stock_quantity'];
            $_SESSION['cart'][$pid]['quantity'] = $qty;
            if ($qty == 0) {
                unset($_SESSION['cart'][$pid]);
                continue;
            }
            $_SESSION['error'] = "Some items had their quantities reduced due to limited stock availability.";
        }

        $subtotal = $p['price'] * $qty;
        $total += $subtotal;

        $p['cart_qty'] = $qty;
        $p['subtotal'] = $subtotal;
        $cart_items[] = $p;
    }
}
?>
<div class="row pt-4 pb-5">
    <div class="col-12 mb-4 d-flex justify-content-between align-items-center">
        <h2 class="text-primary-custom fw-bold m-0"><i class="bi bi-cart3 me-2"></i>Shopping Cart</h2>
        <?php if (!empty($cart_items)): ?>
            <span class="text-white opacity-75"><?php echo array_sum(array_column($cart_items, 'cart_qty')); ?> items</span>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="col-12 mb-3">
            <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center"><i
                    class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
                <div><?php echo sanitize_html($_SESSION['error']);
                unset($_SESSION['error']); ?></div>
            </div>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="col-12 mb-3">
            <div class="alert alert-success shadow-sm border-0 d-flex align-items-center"><i
                    class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div><?php echo sanitize_html($_SESSION['success']);
                unset($_SESSION['success']); ?></div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
        <div class="col-12 text-center py-5 rounded shadow-sm border glass-panel">
            <i class="bi bi-cart-x text-white opacity-75 opacity-50 d-block mb-3" style="font-size: 5rem;"></i>
            <h4 class="text-white opacity-75 fw-bold">Your cart is completely empty</h4>
            <p class="mb-4 text-white opacity-75">Looks like you haven't added any instruments yet. Explore our catalog.</p>
            <a href="../products/index.php" class="btn btn-secondary px-5 py-2 fw-bold shadow-sm">Start Shopping</a>
        </div>
    <?php else: ?>
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush rounded border-0">
                        <?php foreach ($cart_items as $item): ?>
                            <li class="list-group-item p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-2 col-4 mb-3 mb-md-0 text-center">
                                        <img src="https://images.unsplash.com/photo-1511192336575-5a79af67a629?w=150&h=150&fit=crop"
                                            class="img-fluid rounded shadow-sm"
                                            alt="<?php echo sanitize_html($item['product_name']); ?>">
                                    </div>
                                    <div class="col-md-4 col-8 mb-3 mb-md-0">
                                        <h5 class="mb-1 fw-bold">
                                            <a href="../products/view.php?id=<?php echo $item['product_id']; ?>"
                                                class="text-decoration-none text-primary-custom">
                                                <?php echo sanitize_html($item['product_name']); ?>
                                            </a>
                                        </h5>
                                        <div class="text-white opacity-75 small mb-2"><span
                                                class="badge badge-brand border me-1"><?php echo sanitize_html($item['brand']); ?></span>
                                        </div>
                                        <div class="fw-semibold text-dark">
                                            &#8377;<?php echo number_format($item['price'], 2); ?> <span
                                                class="text-white opacity-75 fw-normal small">each</span></div>

                                        <?php if ($item['stock_quantity'] <= 5): ?>
                                            <div class="text-danger small mt-2 fw-bold"><i
                                                    class="bi bi-exclamation-triangle-fill me-1"></i>Only
                                                <?php echo $item['stock_quantity']; ?> left</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-3 col-6 d-flex justify-content-center">
                                        <form method="POST" action="actions.php" class="d-flex align-items-center w-100">
                                            <input type="hidden" name="csrf_token"
                                                value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">

                                            <div class="input-group input-group-sm shadow-sm">
                                                <span class="input-group-text border-end-0 fw-bold">Qty</span>
                                                <input type="number" name="quantity"
                                                    class="form-control text-center border-start-0"
                                                    value="<?php echo $item['cart_qty']; ?>" min="1"
                                                    max="<?php echo $item['stock_quantity']; ?>" onchange="this.form.submit()">
                                            </div>
                                            <!-- Hidden submit button triggered on enter, JS could trigger onchange -->
                                            <button type="submit" class="d-none">Update</button>
                                        </form>
                                    </div>
                                    <div class="col-md-3 col-6 text-end">
                                        <div class="fw-bold fs-5 text-secondary-custom mb-2">
                                            &#8377;<?php echo number_format($item['subtotal'], 2); ?></div>
                                        <form method="POST" action="actions.php">
                                            <input type="hidden" name="csrf_token"
                                                value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                            <button type="submit"
                                                class="btn btn-sm text-danger p-0 text-decoration-none fw-bold px-2 py-1 rounded">
                                                <i class="bi bi-trash3-fill me-1"></i>Remove
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold border-bottom pb-3 mb-4 text-primary-custom"><i class="bi bi-receipt me-2"></i>Order
                        Summary</h5>
                    <div class="d-flex justify-content-between mb-3 text-white opacity-75">
                        <span>Items Subtotal (<?php echo array_sum(array_column($cart_items, 'cart_qty')); ?>)</span>
                        <span class="fw-bold text-white">&#8377;<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-4 border-bottom pb-3 text-white opacity-75">
                        <span>Estimated Tax (0%)</span>
                        <span class="fw-bold text-white">&#8377;0.00</span>
                    </div>

                    <!-- Promo Code UI -->
                    <div class="mb-4">
                        <label class="form-label small text-white opacity-75 fw-bold">Promo Code</label>
                        <div class="input-group">
                            <input type="text" id="promo-input" class="form-control text-uppercase" placeholder="Enter code"
                                value="<?php echo sanitize_html($promo_code); ?>">
                            <button class="btn btn-outline-secondary-custom fw-bold" type="button"
                                id="apply-promo-btn">Apply</button>
                        </div>
                        <div id="promo-message" class="small mt-2"></div>
                    </div>

                    <!-- Discount Row (Hidden if no discount) -->
                    <div id="discount-row"
                        class="d-flex justify-content-between mb-3 text-success <?php echo $discount_percent > 0 ? '' : 'd-none'; ?>">
                        <span>Discount (<span id="discount-label"><?php echo $discount_percent; ?></span>%)</span>
                        <span class="fw-bold">-&#8377;<span
                                id="discount-amount"><?php echo number_format($total * ($discount_percent / 100), 2); ?></span></span>
                    </div>

                    <div class="d-flex justify-content-between mb-4 align-items-center">
                        <span class="fs-5 fw-bold text-primary-custom">Total Order</span>
                        <span class="fs-3 fw-bold text-secondary-custom">&#8377;<span
                                id="final-total"><?php echo number_format($total - ($total * ($discount_percent / 100)), 2); ?></span></span>
                    </div>

                    <a href="../orders/checkout.php" class="btn btn-primary w-100 py-3 fs-5 fw-bold shadow-sm mb-3">
                        Secure Checkout <i class="bi bi-arrow-right-circle-fill ms-2"></i>
                    </a>

                    <div class="text-center">
                        <a href="../products/index.php"
                            class="text-decoration-none text-white opacity-75 small fw-bold hover-primary">
                            <i class="bi bi-arrow-left me-1"></i>Continue Shopping
                        </a>
                    </div>
                </div>
            </div>

            <div class="mt-4 p-3 rounded shadow-sm border text-center border-start border-4 border-success glass-panel">
                <p class="small text-white opacity-75 fw-bold m-0 d-flex align-items-center justify-content-center">
                    <i class="bi bi-shield-lock-fill text-success fs-4 me-2"></i> Your checkout is 100% secure.
                </p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rawTotal = <?php echo $total; ?>;
        const applyBtn = document.getElementById('apply-promo-btn');
        const input = document.getElementById('promo-input');
        const msg = document.getElementById('promo-message');

        // UI Elements
        const discountRow = document.getElementById('discount-row');
        const discountLabel = document.getElementById('discount-label');
        const discountAmt = document.getElementById('discount-amount');
        const finalTotal = document.getElementById('final-total');

        applyBtn.addEventListener('click', async function () {
            const code = input.value.trim();

            // Reset visuals
            msg.textContent = 'Verifying...';
            msg.className = 'small mt-2 text-warning';
            msg.classList.add('blink');

            try {
                const response = await fetch(`${BASE_URL}modules/cart/apply_promo.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        code: code,
                        csrf_token: '<?php echo $_SESSION["csrf_token"]; ?>'
                    })
                });
                const data = await response.json();

                msg.classList.remove('blink');

                if (data.status === 'success') {
                    msg.textContent = data.message;
                    msg.className = 'small mt-2 text-success fw-bold';

                    const pct = parseFloat(data.discount_percent);

                    if (pct > 0) {
                        const saved = rawTotal * (pct / 100);
                        const newTotal = rawTotal - saved;

                        discountLabel.textContent = pct;
                        discountAmt.textContent = saved.toFixed(2);
                        discountRow.classList.remove('d-none');
                        finalTotal.textContent = newTotal.toFixed(2);
                    } else {
                        // Removed
                        discountRow.classList.add('d-none');
                        finalTotal.textContent = rawTotal.toFixed(2);
                        input.value = '';
                    }

                } else {
                    msg.textContent = data.message;
                    msg.className = 'small mt-2 text-danger fw-bold';
                    // Remove active session elements on fail
                    discountRow.classList.add('d-none');
                    finalTotal.textContent = rawTotal.toFixed(2);
                }
            } catch (err) {
                console.error(err);
                msg.classList.remove('blink');
                msg.textContent = 'Connection error processing promo.';
                msg.className = 'small mt-2 text-danger fw-bold';
            }
        });
    });
</script>