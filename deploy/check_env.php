<?php

$result = [
    'status' => 'ok',
    'checks' => [],
];

$addCheck = function (string $name, string $status, string $message) use (&$result) {
    $result['checks'][] = [
        'name'    => $name,
        'status'  => $status,
        'message' => $message,
    ];
    if ($status === 'error' && $result['status'] === 'ok') {
        $result['status'] = 'error';
    } elseif ($status === 'warning' && $result['status'] === 'ok') {
        $result['status'] = 'warning';
    }
};

if (PHP_VERSION_ID < 80000) {
    $addCheck('PHP Version', 'error', 'PHP ' . PHP_VERSION . ' is below minimum 8.0');
} else {
    $addCheck('PHP Version', 'ok', 'PHP ' . PHP_VERSION . ' (>= 8.0)');
}

$required = ['pdo_mysql', 'mysqli', 'mbstring', 'curl', 'gd'];
$missing = [];
foreach ($required as $ext) {
    if (extension_loaded($ext)) {
        $addCheck("Extension: $ext", 'ok', "$ext loaded");
    } else {
        $missing[] = $ext;
        $addCheck("Extension: $ext", 'error', "$ext not loaded");
    }
}

$env_file = __DIR__ . '/../.env';
$env_vars = [];
if (file_exists($env_file)) {
    $parsed = parse_ini_file($env_file);
    if ($parsed) {
        $env_vars = $parsed;
        foreach ($parsed as $k => $v) {
            $_ENV[$k] = $v;
            putenv("$k=$v");
        }
    }
    $addCheck('Environment file', 'ok', '.env file found');
} else {
    $addCheck('Environment file', 'error', '.env file not found at ' . realpath(__DIR__ . '/..'));
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$name = $_ENV['DB_NAME'] ?? 'smartmall_db';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
    ]);
    $pdo->query('SELECT 1');
    $addCheck('Database connection', 'ok', "Connected to $name on $host");

    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $addCheck('Database tables', 'ok', count($tables) . ' tables found: ' . implode(', ', $tables));
} catch (PDOException $e) {
    $addCheck('Database connection', 'error', 'Connection failed: ' . $e->getMessage());
}

foreach (['assets/images', 'uploads'] as $dir) {
    $path = __DIR__ . '/../' . $dir;
    if (is_dir($path)) {
        if (is_writable($path)) {
            $addCheck("Directory: $dir", 'ok', "Writable");
        } else {
            $addCheck("Directory: $dir", 'error', "Not writable");
        }
    } else {
        $addCheck("Directory: $dir", 'warning', "Does not exist");
    }
}

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
exit($result['status'] === 'error' ? 1 : 0);
