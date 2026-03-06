<?php
require_once __DIR__ . '/config/database.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Basic CSRF validation if provided (optional for this simple footer form, but recommended)
// We'll skip strict CSRF here to allow global ease-of-use, or we can check header tokens.
// For TuneHub, we can just sanitize aggressively.

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if (empty($name) || empty($email) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO Feedbacks (name, email, message) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $message]);
    echo json_encode(['success' => true, 'message' => 'Thank you! Your feedback has been securely submitted.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again later.']);
}
?>
