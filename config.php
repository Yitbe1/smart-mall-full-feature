<?php

if (ob_get_level() === 0) ob_start();

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

$app_env = $_ENV['APP_ENV'] ?? 'production';
define('APP_VERSION', $_ENV['APP_VERSION'] ?? '1.0.0');

/**
 * Production error handler — never expose details to the browser.
 * Logs the real error and shows a generic 500 page in production.
 */
function smartmall_error_handler($severity, $message, $file, $line): bool
{
    if (!(error_reporting() & $severity)) {
        return false;
    }
    error_log("Smart Mall Error: $message in $file:$line");
    if (($_ENV['APP_ENV'] ?? 'production') !== 'development') {
        http_response_code(500);
        require __DIR__ . '/errors/500.php';
        exit;
    }
    return false;
}

/**
 * Production exception handler.
 * Logs the full exception and shows a generic 500 page in production.
 */
function smartmall_exception_handler($e): void
{
    error_log("Smart Mall Exception: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
    if (($_ENV['APP_ENV'] ?? 'production') !== 'development') {
        http_response_code(500);
        require __DIR__ . '/errors/500.php';
        exit;
    }
}

set_error_handler('smartmall_error_handler');
set_exception_handler('smartmall_exception_handler');

// Session cookie hardening
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', '1');
}
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

// Ensure session is started before any $_SESSION access
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Idle timeout: 30 minutes
$idle_timeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $idle_timeout) {
    $_SESSION = [];
    session_destroy();
    session_start();
    session_regenerate_id(true);
}
$_SESSION['last_activity'] = time();

// Regenerate session ID periodically (every 30% of timeout) to prevent fixation
if (!isset($_SESSION['_regenerated_at']) || (time() - $_SESSION['_regenerated_at']) > ($idle_timeout * 0.3)) {
    session_regenerate_id(true);
    $_SESSION['_regenerated_at'] = time();
}

// Base URL with sanitized host
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
    || !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    ? 'https://' : 'http://';

// Use SERVER_NAME instead of HTTP_HOST to prevent host header injection
$host = $_SERVER['SERVER_NAME'] ?? 'localhost';

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$subfolder = rtrim(dirname($scriptName), '/\\');
if ($subfolder === '/') {
    $subfolder = '';
}
$base_url = $protocol . $host . $subfolder;

// BASE_PATH — derive from config.php's location, not the requesting script
$base_path = strtr(str_replace($_SERVER['DOCUMENT_ROOT'] ?? '', '', __DIR__), '\\', '/');
if ($base_path === '/') { $base_path = ''; }
$base_path_env = $_ENV['BASE_PATH'] ?? '';
if ($base_path_env !== '') { $base_path = $base_path_env; }
define('BASE_PATH', $base_path);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/currency.php';

/**
 * Generate a cache-busting URL for static assets.
 * Appends the APP_VERSION as a query parameter to force browser re-fetch on deployment.
 *
 * @param string $path Relative asset path (e.g. "/css/app.css")
 * @return string Path with version query string
 */
function asset_url(string $path): string
{
    $v = defined('APP_VERSION') ? APP_VERSION : '1';
    return $path . '?v=' . urlencode($v);
}

/**
 * Redirect to a given path.
 * Validates host to prevent open redirect attacks.
 */
function redirect($path)
{
    global $base_url;
    $allowed_base = parse_url($base_url, PHP_URL_HOST) ?: 'localhost';
    $parsed = parse_url($path);
    if (isset($parsed['host']) && $parsed['host'] !== $allowed_base) {
        $url = $base_url . '/index.php';
    } else {
        $url = (strpos($path, 'http') === 0) ? $path : $base_url . $path;
    }
    header("Location: $url");
    exit;
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

if ($app_env === 'production') {
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://checkout.chapa.co https://accounts.google.com https://www.google.com https://www.gstatic.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https://www.google.com https://www.gstatic.com; frame-src https://checkout.chapa.co https://www.google.com https://accounts.google.com https://www.gstatic.com; connect-src 'self' https://accounts.google.com https://www.google.com https://www.gstatic.com;");
}
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

/**
 * Output minifier — compresses HTML output in production.
 * Preserves newlines inside <script> and <style> so line comments don't break logic.
 */
function smartmall_minify_output(string $buffer): string
{
    // Remove HTML comments (except IE conditional comments)
    $buffer = preg_replace('/<!--[^\[].*?-->/s', '', $buffer);

    // Preserve newlines inside <script> and <style> tags (replace with placeholder)
    $buffer = preg_replace_callback('/<(script|style)\b[^>]*>.*?<\/\1>/is', function ($m) {
        return str_replace("\n", "\x00\x00\x01", $m[0]);
    }, $buffer);

    // Collapse multiple whitespace between tags
    $buffer = preg_replace('/\s+/', ' ', $buffer);

    // Restore newlines inside script/style content
    $buffer = str_replace("\x00\x00\x01", "\n", $buffer);

    // Restore single space between block elements
    $buffer = preg_replace('/> </', ">\n<", $buffer);

    return trim($buffer);
}

if ($app_env === 'production') {
    ob_start('smartmall_minify_output');
}
