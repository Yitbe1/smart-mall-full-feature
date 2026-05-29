<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/chapa-config.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$tx_ref   = $data['tx_ref']         ?? ($_GET['tx_ref']          ?? '');
$status   = $data['status']         ?? ($_GET['status']          ?? '');
$chapa_id = $data['id']             ?? ($_GET['id']              ?? '');
$amount   = $data['amount']         ?? ($_GET['amount']          ?? 0);
$currency = $data['currency']       ?? ($_GET['currency']        ?? 'ETB');

$header_signature = $_SERVER['HTTP_X_CHAPA_SIGNATURE'] ?? '';

$expected_signature = hash_hmac('sha256', $raw ?: '', CHAPA_SECRET_KEY);

if ($header_signature && !hash_equals($expected_signature, $header_signature)) {
    error_log("Chapa callback: HMAC mismatch for tx_ref=$tx_ref");
    http_response_code(403);
    echo 'HMAC verification failed';
    exit;
}

if (empty($tx_ref)) {
    http_response_code(400);
    echo 'Missing tx_ref';
    exit;
}

$pdo = getDB();

$stmt = $pdo->prepare("SELECT * FROM payments WHERE tx_ref = :tx_ref LIMIT 1");
$stmt->execute([':tx_ref' => $tx_ref]);
$payment = $stmt->fetch();

if (!$payment) {
    error_log("Chapa callback: unknown tx_ref=$tx_ref");
    http_response_code(404);
    echo 'Transaction not found';
    exit;
}

if ($status === 'success' || ($data['status'] ?? '') === 'complete') {
    if ($payment['payment_status'] !== 'completed') {
        $pdo->prepare("UPDATE payments SET payment_status = 'completed', chapa_tx_id = :chapa_id, updated_at = NOW() WHERE tx_ref = :tx_ref")
           ->execute([':chapa_id' => $chapa_id, ':tx_ref' => $tx_ref]);

        $pdo->prepare("UPDATE orders SET status = 'paid' WHERE order_id = :oid")
           ->execute([':oid' => $payment['order_id']]);

        $pdo->prepare("UPDATE products p
            JOIN order_items oi ON oi.product_id = p.product_id
            SET p.stock = p.stock - oi.quantity
            WHERE oi.order_id = :oid")
           ->execute([':oid' => $payment['order_id']]);
    }
    http_response_code(200);
    echo 'OK';
} else {
    $pdo->prepare("UPDATE payments SET payment_status = 'failed', updated_at = NOW() WHERE tx_ref = :tx_ref")
       ->execute([':tx_ref' => $tx_ref]);
    $pdo->prepare("UPDATE orders SET status = 'failed' WHERE order_id = :oid")
       ->execute([':oid' => $payment['order_id']]);
    http_response_code(200);
    echo 'Payment not successful';
}
