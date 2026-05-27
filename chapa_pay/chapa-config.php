<?php
// Chapa Payment Configuration — loaded from .env
$key = $_ENV['CHAPA_SECRET_KEY'] ?? '';
if (empty($key)) {
    error_log('Smart Mall: CHAPA_SECRET_KEY not configured in .env');
    $key = '';
}
define('CHAPA_SECRET_KEY', $key);
define('CHAPA_API_URL', 'https://api.chapa.co/v1');
