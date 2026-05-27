<?php
require_once __DIR__ . '/config.php';

$file = __DIR__ . '/Apk/Android/smartmall.apk';

if (!file_exists($file)) {
    http_response_code(404);
    die('File not found.');
}

$filename = 'Smart_Mall.apk';
$filesize = filesize($file);

header('Content-Description: File Transfer');
header('Content-Type: application/vnd.android.package-archive');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . $filesize);
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

ob_clean();
flush();
readfile($file);
exit;
