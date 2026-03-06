<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Security validation failed. Please try again.";
        header("Location: " . base_url('modules/cart/index.php'));
        exit;
    }

    $action = $_POST['action'] ?? '';
    $product_id = (int) ($_POST['product_id'] ?? 0);
    $quantity = (int) ($_POST['quantity'] ?? 1);

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if ($action === 'add' || $action === 'update') {
        // Verify product stock in DB
        $stmt = $pdo->prepare("SELECT stock_quantity, product_name, price FROM Products WHERE product_id = ? AND is_active = 1");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if ($product) {
            $current_qty = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id]['quantity'] : 0;
            $new_qty = $action === 'add' ? $current_qty + $quantity : $quantity;

            if ($new_qty > $product['stock_quantity']) {
                $_SESSION['error'] = "Cannot add quantity. Only {$product['stock_quantity']} {$product['product_name']}(s) are currently in stock.";
                if ($action === 'update') {
                    $new_qty = $product['stock_quantity'];
                    $_SESSION['cart'][$product_id]['quantity'] = $new_qty;
                }
            } else {
                if ($new_qty <= 0) {
                    unset($_SESSION['cart'][$product_id]);
                } else {
                    $_SESSION['cart'][$product_id] = [
                        'quantity' => $new_qty,
                        'name' => $product['product_name'],
                        'price' => $product['price']
                    ];
                }
                $_SESSION['success'] = "Cart updated successfully.";
            }
        } else {
            $_SESSION['error'] = "Product no longer exists.";
        }
    } elseif ($action === 'remove') {
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            $_SESSION['success'] = "Item removed from cart.";
        }
    }

    // Redirect to cart
    header("Location: " . base_url('modules/cart/index.php'));
    exit;
}

header("Location: " . base_url('index.php'));
exit;
?>