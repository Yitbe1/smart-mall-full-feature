<?php
// Google Sign-In callback (JSON API)
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['credential'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$credential = $_POST['credential'];

$tokeninfo = @file_get_contents("https://oauth2.googleapis.com/tokeninfo?id_token=" . urlencode($credential));
if (!$tokeninfo) {
    echo json_encode(['success' => false, 'error' => 'Failed to verify Google token']);
    exit;
}

$payload = json_decode($tokeninfo, true);
if (!$payload || !isset($payload['sub']) || !isset($payload['email'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid Google token']);
    exit;
}

$expected_aud = '1003727523085-vk311f184eqrt95a3ggdq17h2fnqe5bl.apps.googleusercontent.com';
if ($payload['aud'] !== $expected_aud) {
    echo json_encode(['success' => false, 'error' => 'Token audience mismatch']);
    exit;
}

$google_id   = $payload['sub'];
$email       = $payload['email'];
$name        = $payload['name'] ?? explode('@', $email)[0];
$email_verified = !empty($payload['email_verified']);

$stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = ? OR email = ? LIMIT 1");
$stmt->execute([$google_id, $email]);
$user = $stmt->fetch();

if ($user) {
    if ($user['google_id'] === null) {
        $update = $pdo->prepare("UPDATE users SET google_id = ?, email_verified_at = COALESCE(email_verified_at, NOW()) WHERE user_id = ?");
        $update->execute([$google_id, $user['user_id']]);
    }
    $_SESSION['user_id']    = $user['user_id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = $user['role'];
    echo json_encode(['success' => true, 'redirect' => 'index.php']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, email_verified_at, google_id, created_at) VALUES (?, ?, NULL, 'customer', NOW(), ?, NOW())");
$stmt->execute([$name, $email, $google_id]);

$_SESSION['user_id']    = $pdo->lastInsertId();
$_SESSION['user_name']  = $name;
$_SESSION['user_email'] = $email;
$_SESSION['user_role']  = 'customer';

echo json_encode(['success' => true, 'redirect' => 'index.php']);
