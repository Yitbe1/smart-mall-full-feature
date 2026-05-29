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
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

// Idle timeout: 30 minutes
$idle_timeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $idle_timeout) {
    $_SESSION = [];
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

// Regenerate session ID periodically (every 30% of timeout) to prevent fixation
if (!isset($_SESSION['_regenerated_at']) || (time() - $_SESSION['_regenerated_at']) > ($idle_timeout * 0.3)) {
    session_regenerate_id(true);
    $_SESSION['_regenerated_at'] = time();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
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

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/currency.php';

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
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://checkout.chapa.co; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; frame-src https://checkout.chapa.co");
}
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
