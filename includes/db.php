<?php
// Database configuration and connection
// This file handles all database connections using PDO for security

// Database credentials loaded from .env
$host    = $_ENV['DB_HOST'] ?? 'localhost';
$db_name = $_ENV['DB_NAME'] ?? 'smartmall_db';
$db_user = $_ENV['DB_USER'] ?? 'root';
$db_pass = $_ENV['DB_PASS'] ?? '';

if (empty($db_name) || $db_user === false) {
    error_log('Smart Mall: Database credentials not configured. Check .env file.');
    die('A configuration error occurred. Please contact the site administrator.');
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
 * Return the shared PDO database connection.
 * Uses a global variable set at the bottom of this file.
 */
function getDB()
{
    global $pdo;
    return $pdo;
}

// ============================================================
// CSRF Token Helpers
// Used by all forms to prevent Cross-Site Request Forgery
// ============================================================

/**
 * Generate a CSRF token for the current session (idempotent).
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
 * Output a hidden CSRF input field for use in forms.
 */
function csrf_field(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Verify that the submitted CSRF token matches the session token.
 * Terminates with a 403 response on failure.
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
 * Returns the full URL for a product image.
 * Handles both local filenames and external URLs.
 */
function get_product_image_url($image_path): string
{
    if (empty($image_path)) {
        return '';
    }

    // Check if it's already a full URL
    if (strpos($image_path, 'http://') === 0 || strpos($image_path, 'https://') === 0) {
        return $image_path;
    }

    // Otherwise, assume it's in the uploads folder
    // Adjust path based on whether we are in admin subfolder
    $prefix = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../' : '';
    return $prefix . 'uploads/' . $image_path;
}

/**
 * Returns the full URL for a product video.
 * Handles both local filenames and external URLs.
 */
function get_product_video_url($video_path): string
{
    if (empty($video_path)) {
        return '';
    }

    // Check if it's already a full URL
    if (strpos($video_path, 'http://') === 0 || strpos($video_path, 'https://') === 0) {
        return $video_path;
    }

    // Otherwise, assume it's in the uploads folder
    $prefix = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../' : '';
    return $prefix . 'uploads/' . $video_path;
}

require_once __DIR__ . '/currency.php';
