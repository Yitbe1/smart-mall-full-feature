<?php

require_once __DIR__ . '/TestRunner.php';

class SmokeTest
{
    public function testConfigFileIsValid(): void
    {
        $output = shell_exec('/opt/lampp/bin/php -l ' . escapeshellarg(__DIR__ . '/../config.php') . ' 2>&1');
        TestRunner::assertTrue(str_contains($output ?? '', 'No syntax errors'), 'config.php should parse without errors');
    }

    public function testHeaderFileIsValid(): void
    {
        $output = shell_exec('/opt/lampp/bin/php -l ' . escapeshellarg(__DIR__ . '/../includes/header.php') . ' 2>&1');
        TestRunner::assertTrue(str_contains($output ?? '', 'No syntax errors'), 'header.php should parse without errors');
    }

    public function testFooterFileIsValid(): void
    {
        $output = shell_exec('/opt/lampp/bin/php -l ' . escapeshellarg(__DIR__ . '/../includes/footer.php') . ' 2>&1');
        TestRunner::assertTrue(str_contains($output ?? '', 'No syntax errors'), 'footer.php should parse without errors');
    }

    public function testProductPageIsValid(): void
    {
        $output = shell_exec('/opt/lampp/bin/php -l ' . escapeshellarg(__DIR__ . '/../product.php') . ' 2>&1');
        TestRunner::assertTrue(str_contains($output ?? '', 'No syntax errors'), 'product.php should parse without errors');
    }

    public function testCartPageIsValid(): void
    {
        $output = shell_exec('/opt/lampp/bin/php -l ' . escapeshellarg(__DIR__ . '/../cart.php') . ' 2>&1');
        TestRunner::assertTrue(str_contains($output ?? '', 'No syntax errors'), 'cart.php should parse without errors');
    }

    public function testIndexPageIsValid(): void
    {
        $output = shell_exec('/opt/lampp/bin/php -l ' . escapeshellarg(__DIR__ . '/../index.php') . ' 2>&1');
        TestRunner::assertTrue(str_contains($output ?? '', 'No syntax errors'), 'index.php should parse without errors');
    }

    public function testManifestIsValidJson(): void
    {
        $json = file_get_contents(__DIR__ . '/../manifest.json');
        $data = json_decode($json, true);
        TestRunner::assertNotNull($data, 'manifest.json should be valid JSON');
        TestRunner::assertTrue(isset($data['name']), 'manifest.json should have "name" field');
        TestRunner::assertTrue(isset($data['icons']), 'manifest.json should have "icons" field');
    }

    public function testOfflinePageIsValid(): void
    {
        $html = file_get_contents(__DIR__ . '/../offline.html');
        TestRunner::assertTrue(str_contains($html, '<!DOCTYPE html>'), 'offline.html should be valid HTML');
    }

    public function testEnvFileExists(): void
    {
        TestRunner::assertTrue(file_exists(__DIR__ . '/../.env'), '.env file should exist');
    }

    public function testCriticalDirectoriesExist(): void
    {
        $dirs = ['uploads', 'cache/queries'];
        foreach ($dirs as $d) {
            TestRunner::assertTrue(is_dir(__DIR__ . '/../' . $d), "Directory '$d' should exist");
        }
    }
}
