<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validate CSRF Protection
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['err'] = "Invalid verification token. Please try again.";
        header("Location: index.php");
        exit;
    }

    $order_id = intval($_POST['order_id'] ?? 0);
    $user_id = $_SESSION['user_id'];

    // 2. Verify Order Ownership and Eligibility
    // We only allow users to cancel orders that are still 'pending'
    $stmt = $pdo->prepare("SELECT status FROM Orders WHERE order_id = ? AND user_id = ?");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();

    if (!$order) {
        $_SESSION['err'] = "Order not found or permission denied.";
        header("Location: index.php");
        exit;
    }

    if ($order['status'] !== 'pending') {
        $_SESSION['err'] = "This order cannot be cancelled because it is already " . sanitize_html($order['status']) . ".";
        header("Location: index.php");
        exit;
    }

    try {
        // Begin Transaction for Data Integrity
        $pdo->beginTransaction();

        // 3. Mark the Order as Cancelled
        $update_stmt = $pdo->prepare("UPDATE Orders SET status = 'cancelled' WHERE order_id = ?");
        $update_stmt->execute([$order_id]);

        // 4. Retrieve all associated Order_Items to restore global inventory
        $items_stmt = $pdo->prepare("SELECT product_id, quantity FROM Order_Items WHERE order_id = ?");
        $items_stmt->execute([$order_id]);
        $items = $items_stmt->fetchAll();

        // 5. Restore Inventory Stock natively per item
        $restore_stmt = $pdo->prepare("UPDATE Products SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
        foreach ($items as $item) {
            $restore_stmt->execute([$item['quantity'], $item['product_id']]);
        }

        // Commit all changes
        $pdo->commit();
        $_SESSION['msg'] = "Order #{$order_id} has been successfully cancelled. Your payment method will not be charged.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['err'] = "A critical system error occurred during cancellation. Please contact support.";
    }

    header("Location: index.php");
    exit;
} else {
    // Prevent direct GET access
    header("Location: index.php");
    exit;
}
?>
