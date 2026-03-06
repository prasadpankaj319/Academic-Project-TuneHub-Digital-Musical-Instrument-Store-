<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Ensure function library is present for token validation
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';

$headers = getallheaders();
$csrfToken = $headers['X-CSRF-TOKEN'] ?? ($headers['X-Csrf-Token'] ?? '');

if (!verify_csrf_token($csrfToken)) {
    echo json_encode(['status' => 'error', 'message' => 'CSRF verification failed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'] ?? 0;

if (!$product_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Check if the item is already wishlisted
    $stmt = $pdo->prepare("SELECT wishlist_id FROM Wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $exists = $stmt->fetch();

    if ($exists) {
        // Remove from wishlist
        $del = $pdo->prepare("DELETE FROM Wishlist WHERE wishlist_id = ?");
        $del->execute([$exists['wishlist_id']]);
        echo json_encode(['status' => 'success', 'action' => 'removed']);
    } else {
        // Add to wishlist
        $add = $pdo->prepare("INSERT INTO Wishlist (user_id, product_id) VALUES (?, ?)");
        $add->execute([$user_id, $product_id]);
        echo json_encode(['status' => 'success', 'action' => 'added']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database exception: ' . $e->getMessage()]);
}
?>
