<?php
// Capacitor FCM push token registration (JSON API)
header('Content-Type: application/json');

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    $env_vars = parse_ini_file($env_file);
    if ($env_vars) {
        foreach ($env_vars as $key => $value) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

require_once __DIR__ . '/includes/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$fcm_token = $input['fcm_token'] ?? '';

if (!$fcm_token) {
    echo json_encode(['success' => false, 'error' => 'Missing fcm_token']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$stmt = $pdo->prepare(
    "INSERT INTO device_tokens (user_id, fcm_token, platform)
     VALUES (?, ?, 'android')
     ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), updated_at = NOW()"
);
$stmt->execute([$user_id, $fcm_token]);

echo json_encode(['success' => true]);
