<?php
// Logout handler
session_start();

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

$user_id = $_SESSION['user_id'] ?? null;

// Clean up device tokens for this user (table may not exist)
if ($user_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM device_tokens WHERE user_id = ?");
        $stmt->execute([$user_id]);
    } catch (PDOException $e) {
        error_log('logout: device_tokens cleanup skipped: ' . $e->getMessage());
    }
}

// Regenerate and destroy to prevent session fixation
session_regenerate_id(true);
session_unset();
session_destroy();

// Redirect to home
header('Location: index.php');
exit();
