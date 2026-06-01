<?php
// Database configuration and connection
// This file handles all database connections using PDO for security

// Database credentials — single source of truth is .env
$host    = $_ENV['DB_HOST'] ?? '';
$db_name = $_ENV['DB_NAME'] ?? '';
$db_user = $_ENV['DB_USER'] ?? '';
$db_pass = $_ENV['DB_PASS'] ?? '';

if (empty($host) || empty($db_name) || empty($db_user)) {
    error_log('Smart Mall: Database credentials not configured. Check .env file.');
    die('Database configuration error. Please contact the site administrator.');
}

// Create DSN (Data Source Name)
$dsn = 'mysql:host=' . $host . ';dbname=' . $db_name . ';charset=utf8mb4';

// PDO options for security
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // Log the real error to server log — never expose it to the browser
    error_log('Smart Mall DB Error: ' . $e->getMessage());
    die('A database connection error occurred. Please try again later or contact the site administrator.');
}

/**
 * Return the shared PDO database connection instance.
 *
 * @global PDO $pdo
 * @return PDO
 */
function getDB(): PDO
{
    global $pdo;
    return $pdo;
}

/**
 * Generate or retrieve the session CSRF token (idempotent).
 * Creates a 64-character hex token on first call and stores it in session.
 *
 * @return string The CSRF token
 */
function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output a hidden HTML input field containing the CSRF token.
 * Intended for use inside HTML forms.
 *
 * @return void
 */
function csrf_field(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Verify the submitted CSRF token matches the session token.
 * Uses hash_equals() for timing-safe comparison.
 * Terminates with a 403 response on failure.
 *
 * @return void
 */
function csrf_verify(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $submitted = $_POST['csrf_token'] ?? '';
    $expected  = $_SESSION['csrf_token'] ?? '';

    if (!$submitted || !hash_equals($expected, $submitted)) {
        http_response_code(403);
        die('Invalid or missing security token. Please go back and try again.');
    }
}

/**
 * Return the full URL for a product image asset.
 * Resolves local filenames to the uploads directory and handles admin subfolder prefixing.
 * Passes through external URLs unchanged.
 *
 * @param string $image_path Local filename or full URL
 * @return string Resolved image URL
 */
function get_product_image_url(string $image_path): string
{
    if (empty($image_path)) {
        return '';
    }

    // Check if it's already a full URL
    if (strpos($image_path, 'http://') === 0 || strpos($image_path, 'https://') === 0) {
        return $image_path;
    }

    // Otherwise, assume it's in the uploads folder
    $prefix = defined('BASE_PATH') ? BASE_PATH . '/' : '';
    return $prefix . 'uploads/' . $image_path;
}

/**
 * Return the full URL for a product video asset.
 * Resolves local filenames to the uploads directory with admin subfolder prefixing.
 * Passes through external URLs unchanged.
 *
 * @param string $video_path Local filename or full URL
 * @return string Resolved video URL
 */
function get_product_video_url(string $video_path): string
{
    if (empty($video_path)) {
        return '';
    }

    // Check if it's already a full URL
    if (strpos($video_path, 'http://') === 0 || strpos($video_path, 'https://') === 0) {
        return $video_path;
    }

    // Otherwise, assume it's in the uploads folder
    $prefix = defined('BASE_PATH') ? BASE_PATH . '/' : '';
    return $prefix . 'uploads/' . $video_path;
}

require_once __DIR__ . '/currency.php';
