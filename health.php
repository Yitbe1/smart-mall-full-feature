<?php
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');

session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$status = 'ok';
$checks = [];
$httpCode = 200;

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

try {
    $start = microtime(true);
    require_once __DIR__ . '/includes/db.php';
    $pdo = getDB();
    $pdo->query('SELECT 1');
    $queryTime = (microtime(true) - $start) * 1000;

    $stmt = $pdo->query('SELECT COUNT(*) as total FROM products');
    $productCount = (int)$stmt->fetch()['total'];

    $stmt = $pdo->query('SELECT COUNT(*) as total FROM orders');
    $orderCount = (int)$stmt->fetch()['total'];

    $stmt = $pdo->query('SELECT COUNT(*) as total FROM users');
    $userCount = (int)$stmt->fetch()['total'];

    $checks['database'] = [
        'status' => 'ok',
        'query_time_ms' => round($queryTime, 2),
        'product_count' => $productCount,
        'order_count' => $orderCount,
        'user_count' => $userCount,
    ];
} catch (Throwable $e) {
    error_log("Health check error: " . $e->getMessage());
    $status = 'degraded';
    $httpCode = 503;
    $checks['database'] = [
        'status' => 'error',
        'message' => 'Database connection failed: ' . $e->getMessage(),
    ];
}

$checks['php_version'] = PHP_VERSION;
$checks['server_time'] = date('c');
$checks['memory_mb'] = round(memory_get_usage(true) / 1024 / 1024, 2);

http_response_code($httpCode);
echo json_encode([
    'status' => $status,
    'app' => 'Smart Mall',
    'checks' => $checks,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
