<?php
/**
 * Smart Mall Test Runner
 *
 * Usage: /opt/lampp/bin/php tests/run.php
 *        /opt/lampp/bin/php tests/run.php --filter=Database
 *        /opt/lampp/bin/php tests/run.php --help
 */

$filter = '';
foreach ($argv as $i => $arg) {
    if ($arg === '--filter' && isset($argv[$i + 1])) {
        $filter = $argv[$i + 1];
    }
    if ($arg === '--help' || $arg === '-h') {
        echo "Smart Mall Test Runner\n";
        echo "Usage: /opt/lampp/bin/php tests/run.php [--filter=Pattern]\n";
        echo "\n";
        exit(0);
    }
}

require_once __DIR__ . '/TestRunner.php';

$testFiles = glob(__DIR__ . '/*Test.php');
$runner = new TestRunner();

echo "\033[1mSmart Mall Test Suite\033[0m\n";
echo "===================\n\n";

$anyFailed = false;
foreach ($testFiles as $file) {
    $class = basename($file, '.php');
    if ($filter && !str_contains($class, $filter)) {
        continue;
    }
    require_once $file;
    $runner->run($class);
}

$runner->summary();
exit($runner->failed > 0 ? 1 : 0);
