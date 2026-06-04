<?php
require_once __DIR__ . '/config.php';

$currency = $_POST['currency'] ?? $_GET['currency'] ?? SMARTMALL_BASE_CURRENCY;
smartmall_set_selected_currency((string)$currency);

$fallback = base_url_path('/index.php');
$redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? ($_SERVER['HTTP_REFERER'] ?? $fallback);
$redirect = str_replace(["\r", "\n"], '', (string)$redirect);

if (strpos($redirect, 'http://') === 0 || strpos($redirect, 'https://') === 0) {
    $parts = parse_url($redirect);
    if (($parts['host'] ?? '') !== ($_SERVER['HTTP_HOST'] ?? '')) {
        $redirect = $fallback;
    } else {
        $path = $parts['path'] ?? $fallback;
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';
        $redirect = $path . $query . $fragment;
    }
} elseif ($redirect === '' || $redirect[0] !== '/') {
    $redirect = $fallback;
}

header('Location: ' . $redirect);
exit();
