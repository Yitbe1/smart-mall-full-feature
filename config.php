<?php
// config.php

// 🔒 Buffer output first (catches hidden BOM/whitespace)
if (ob_get_level() === 0) ob_start();

// 🔐 Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🌐 Base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ||
    !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];

// Detect app subfolder automatically (e.g. /reference)
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$subfolder = rtrim(dirname($scriptName), '/\\');
if ($subfolder === '/') {
    $subfolder = '';
}
$base_url = $protocol . $host . $subfolder;

// 🗃️ Load Database Connection
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/currency.php';

// 🧭 Redirect helper
function redirect($path)
{
    global $base_url;
    $url = (strpos($path, 'http') === 0) ? $path : $base_url . $path;
    header("Location: $url");
    exit();
}

// 🛡️ Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

// NO closing 
