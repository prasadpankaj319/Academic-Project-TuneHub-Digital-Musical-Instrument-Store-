<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';

session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!verify_csrf_token($data['csrf_token'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'Security validation failed.']);
    exit;
}

$code = $data['code'] ?? '';

if (empty($code)) {
    // If empty code is sent, remove the promo
    unset($_SESSION['promo_code']);
    unset($_SESSION['discount_percent']);
    echo json_encode(['status' => 'success', 'message' => 'Promo code removed.', 'discount_percent' => 0]);
    exit;
}

$code = strtoupper(trim($code));

$stmt = $pdo->prepare("SELECT discount_percent, valid_until, is_active FROM Promotions WHERE code = ?");
$stmt->execute([$code]);
$promo = $stmt->fetch();

if (!$promo) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid promo code.']);
    exit;
}

if (!$promo['is_active']) {
    echo json_encode(['status' => 'error', 'message' => 'This promo code is currently disabled.']);
    exit;
}

if (!empty($promo['valid_until']) && strtotime($promo['valid_until']) < time()) {
    echo json_encode(['status' => 'error', 'message' => 'This promo code has expired.']);
    exit;
}

// Success
$_SESSION['promo_code'] = $code;
$_SESSION['discount_percent'] = (float)$promo['discount_percent'];

echo json_encode([
    'status' => 'success',
    'message' => 'Promo code applied!',
    'discount_percent' => $_SESSION['discount_percent'],
    'code' => $code
]);
exit;
?>
