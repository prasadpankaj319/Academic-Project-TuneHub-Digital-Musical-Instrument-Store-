<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

session_start();
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . base_url('modules/cart/index.php'));
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    die("Security validation failed during checkout.");
}

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    die("Cart is empty.");
}

$user_id = $_SESSION['user_id'];
$total_amount = (float) $_POST['total_amount'];
$shipping_address = trim($_POST['shipping_address'] ?? '');
$payment_method = $_POST['payment_method'] ?? 'Card';

if (empty($shipping_address)) {
    die("A valid shipping address is required to process the order.");
}

// Setup Razorpay simulation
$paise_amount = 0;
$payment_status = 'pending';

if ($payment_method !== 'COD') {
    // Razorpay API requirement: INR must be multiplied by 100 into "paise" format
    $paise_amount = $total_amount * 100;

    // Simulate Razorpay Gateway Delay
    sleep(1);

    // Simulate success
    $payment_success = true;
    $payment_status = 'completed';
} else {
    // COD Bypass
    $payment_success = true;
}

if ($payment_success) {
    try {
        // Begin Transaction to isolate DB changes across operations
        $pdo->beginTransaction();

        $ids = array_map('intval', array_keys($cart));
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';

        // SELECT FOR UPDATE acquires a pessimistic row-level lock on the given products
        // No other transaction can modify these rows until we commit/rollback
        $stmt = $pdo->prepare("SELECT product_id, stock_quantity, price FROM Products WHERE product_id IN ($placeholders) AND is_active = 1 FOR UPDATE");
        $stmt->execute($ids);

        $products = [];
        while ($row = $stmt->fetch()) {
            $products[$row['product_id']] = $row;
        }

        // Verify stock limits haven't been exceeded during the transaction gap
        foreach ($cart as $pid => $item) {
            if (!isset($products[$pid])) {
                throw new Exception("Product ID $pid no longer exists.");
            }
            if ($products[$pid]['stock_quantity'] < $item['quantity']) {
                throw new Exception("Not enough stock for '" . $item['name'] . "'. Available: " . $products[$pid]['stock_quantity']);
            }
        }

        // 1. Insert Order
        $stmt = $pdo->prepare("INSERT INTO Orders (user_id, total_amount, status, shipping_address) VALUES (?, ?, 'confirmed', ?)");
        $stmt->execute([$user_id, $total_amount, $shipping_address]);
        $order_id = $pdo->lastInsertId();

        // 2. Insert Order Items & Update Stock inside the same locked transaction
        $item_stmt = $pdo->prepare("INSERT INTO Order_Items (order_id, product_id, quantity, subtotal) VALUES (?, ?, ?, ?)");
        $stock_stmt = $pdo->prepare("UPDATE Products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");

        foreach ($cart as $pid => $item) {
            $subtotal = $item['price'] * $item['quantity'];
            $item_stmt->execute([$order_id, $pid, $item['quantity'], $subtotal]);
            $stock_stmt->execute([$item['quantity'], $pid]);
        }

        // 3. Insert Payment Tracking Record
        $payment_stmt = $pdo->prepare("INSERT INTO Payments (order_id, payment_method, payment_status, transaction_id) VALUES (?, ?, ?, ?)");
        $mock_txn = ($payment_method !== 'COD') ? 'TXN_' . strtoupper(uniqid()) : NULL;
        $payment_stmt->execute([$order_id, $payment_method, $payment_status, $mock_txn]);

        // 4. Commit the transaction (Releases row-level locks on Products table)
        $pdo->commit();

        // End logic: Clear Cart and redirect to the invoice
        unset($_SESSION['cart']);
        unset($_SESSION['promo_code']);
        unset($_SESSION['discount_percent']);

        header("Location: " . base_url("modules/orders/invoice.php?id=$order_id"));
        exit;

    } catch (Exception $e) {
        // 4. Rollback all SQL calls inside transaction if anything failed (e.g. out of stock check)
        $pdo->rollBack();
        $_SESSION['error'] = "Checkout Error: " . $e->getMessage();
        header("Location: " . base_url('modules/cart/index.php'));
        exit;
    }
} else {
    $_SESSION['error'] = "Payment failed. Please try again.";
    header("Location: " . base_url('modules/orders/checkout.php'));
    exit;
}
?>