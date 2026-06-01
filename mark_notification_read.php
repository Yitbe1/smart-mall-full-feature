<?php
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

if (($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

csrf_verify();

$pdo = getDB();

if (isset($_POST['all'])) {
    $pdo->exec("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
} elseif (isset($_POST['id'])) {
    $id = (int) $_POST['id'];
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?")->execute([$id]);
}

http_response_code(200);
