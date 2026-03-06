<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please log in.']);
    exit;
}

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';

// Validate CSRF token strictly for all deletion requests
$headers = getallheaders();
$csrfToken = $headers['X-CSRF-TOKEN'] ?? ($headers['X-Csrf-Token'] ?? '');

if (!verify_csrf_token($csrfToken)) {
    echo json_encode(['status' => 'error', 'message' => 'CSRF Security verification failed.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$review_id = $data['review_id'] ?? 0;

if (!$review_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid review signature.']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Stage 1: Verify exact ownership or Admin Level privileges before deleting
    $stmt = $pdo->prepare("SELECT user_id FROM Reviews WHERE review_id = ?");
    $stmt->execute([$review_id]);
    $review = $stmt->fetch();

    if (!$review) {
        echo json_encode(['status' => 'error', 'message' => 'Review not found.']);
        exit;
    }

    $is_admin = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Admin';
    if ($review['user_id'] !== $user_id && !$is_admin) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized. You cannot delete reviews belonging to other musicians.']);
        exit;
    }

    // Stage 2: Confirmed Ownership -> Execute Hard Deletion
    $del = $pdo->prepare("DELETE FROM Reviews WHERE review_id = ?");
    $del->execute([$review_id]);

    echo json_encode(['status' => 'success']);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database exception: ' . $e->getMessage()]);
}
?>
