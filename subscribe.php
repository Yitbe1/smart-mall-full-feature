<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$email = trim($_POST['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare("INSERT IGNORE INTO newsletters (email) VALUES (:email)");
    $stmt->execute([':email' => $email]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Thanks for subscribing!']);
    } else {
        echo json_encode(['success' => true, 'message' => 'You\'re already subscribed!']);
    }
} catch (PDOException $e) {
    error_log("Newsletter subscribe error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
}
