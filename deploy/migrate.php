<?php

$usage = <<<USAGE
Smart Mall Migration Runner

Usage: php deploy/migrate.php [options]

Options:
  --down    Revert the last migration
  --help    Show this help

USAGE;

if (in_array('--help', $argv ?? [])) {
    echo $usage;
    exit(0);
}

$down = in_array('--down', $argv ?? []);

$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    $env_vars = parse_ini_file($env_file);
    if ($env_vars) {
        foreach ($env_vars as $key => $value) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

$host    = $_ENV['DB_HOST'] ?? '';
$db_name = $_ENV['DB_NAME'] ?? '';
$user    = $_ENV['DB_USER'] ?? '';
$pass    = $_ENV['DB_PASS'] ?? '';

if (empty($host) || empty($db_name) || empty($user)) {
    fwrite(STDERR, "Database credentials not configured. Check .env file.\n");
    exit(1);
}

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db_name;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    echo "\033[31mERROR\033[0m: Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$pdo->exec("CREATE TABLE IF NOT EXISTS schema_migrations (
    version VARCHAR(20) NOT NULL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    duration_ms INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$migrations_dir = __DIR__ . '/migrations';
if (!is_dir($migrations_dir)) {
    echo "\033[33mWARN\033[0m: Migrations directory not found: $migrations_dir\n";
    exit(0);
}

$files = glob($migrations_dir . '/*.sql');
$migrations = [];
foreach ($files as $file) {
    $basename = basename($file);
    if (str_ends_with($basename, '_down.sql')) {
        continue;
    }
    if (preg_match('/^(\d{8}_\d{6})_(.+)\.sql$/', $basename, $m)) {
        $migrations[$m[1]] = [
            'version' => $m[1],
            'name'    => $m[2],
            'file'    => $file,
            'down'    => $migrations_dir . '/' . $m[1] . '_' . $m[2] . '_down.sql',
        ];
    }
}
ksort($migrations);

if (empty($migrations)) {
    echo "\033[33mWARN\033[0m: No migration files found.\n";
    exit(0);
}

if ($down) {
    $last = $pdo->query("SELECT version, name FROM schema_migrations ORDER BY version DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if (!$last) {
        echo "\033[33mWARN\033[0m: No migrations have been run yet.\n";
        exit(0);
    }
    $key = $last['version'];
    if (!isset($migrations[$key])) {
        echo "\033[31mERROR\033[0m: Migration file for version $key not found.\n";
        exit(1);
    }
    $mig = $migrations[$key];
    $downFile = $mig['down'];
    if (!file_exists($downFile)) {
        echo "\033[31mERROR\033[0m: Down migration file not found: $downFile\n";
        exit(1);
    }
    $sql = file_get_contents($downFile);
    echo "Reverting: {$mig['version']}_{$mig['name']}... ";
    $start = microtime(true);
    try {
        $pdo->exec($sql);
        $pdo->prepare("DELETE FROM schema_migrations WHERE version = ?")->execute([$mig['version']]);
        $elapsed = round((microtime(true) - $start) * 1000);
        echo "\033[32mdone\033[0m ({$elapsed}ms)\n";
    } catch (PDOException $e) {
        echo "\033[31mFAILED\033[0m: " . $e->getMessage() . "\n";
        exit(1);
    }
    exit(0);
}

$stmt = $pdo->query("SELECT version FROM schema_migrations");
$ran = $stmt->fetchAll(PDO::FETCH_COLUMN);
$ranMap = array_flip($ran);

echo "Running migrations...\n\n";
$any = false;
foreach ($migrations as $mig) {
    if (isset($ranMap[$mig['version']])) {
        echo "  \033[33m[SKIP]\033[0m {$mig['version']}_{$mig['name']} (already ran)\n";
        continue;
    }
    $any = true;
    $sql = file_get_contents($mig['file']);
    echo "  \033[36m[RUN]\033[0m  {$mig['version']}_{$mig['name']}... ";
    $start = microtime(true);
    try {
        $pdo->exec($sql);
        $elapsed = round((microtime(true) - $start) * 1000);
        $pdo->prepare("INSERT INTO schema_migrations (version, name, duration_ms) VALUES (?, ?, ?)")
            ->execute([$mig['version'], $mig['name'], $elapsed]);
        echo "\033[32mdone\033[0m ({$elapsed}ms)\n";
    } catch (PDOException $e) {
        echo "\033[31mFAILED\033[0m: " . $e->getMessage() . "\n";
        exit(1);
    }
}

if (!$any) {
    echo "  No new migrations to run.\n";
}
echo "\nAll migrations processed.\n";
