<?php
session_start();
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
