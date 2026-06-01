<?php
require_once __DIR__ . '/config.php';

$order_id = (int)($_GET['order_id'] ?? 0);
if (!$order_id) {
    header('Location: index.php');
    exit();
}

$pdo = getDB();

$stmt = $pdo->prepare("SELECT o.*, p.status as payment_status, p.tx_ref, p.amount as pay_amount, p.currency, p.paid_at, p.method as pay_method
    FROM orders o
    LEFT JOIN payments p ON o.order_id = p.order_id
    WHERE o.order_id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order || !isset($_SESSION['user_id']) || $order['user_id'] != $_SESSION['user_id']) {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Receipt #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?> - Smart Mall</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            color: #333;
            background: #f5f5f5;
            padding: 20px;
        }
        .receipt {
            max-width: 420px;
            margin: 0 auto;
            background: #fff;
            padding: 30px 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #333;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .header p { color: #666; font-size: 11px; margin-top: 4px; }
        .divider { border-top: 1px dashed #ccc; margin: 10px 0; }
        .row { display: flex; justify-content: space-between; padding: 3px 0; }
        .row.label { color: #888; font-size: 11px; text-transform: uppercase; }
        .item { padding: 6px 0; border-bottom: 1px dotted #eee; }
        .item:last-child { border-bottom: none; }
        .item-name { font-weight: bold; }
        .item-meta { font-size: 11px; color: #666; }
        .total { font-size: 16px; font-weight: bold; border-top: 2px solid #333; padding-top: 8px; margin-top: 8px; }
        .total .row { font-size: 14px; }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px dashed #333;
            font-size: 11px;
            color: #666;
        }
        .paid-stamp {
            text-align: center;
            margin: 10px 0;
            font-size: 18px;
            font-weight: bold;
            color: #15803d;
            border: 2px solid #15803d;
            display: inline-block;
            padding: 4px 16px;
            transform: rotate(-5deg);
        }
        .actions { text-align: center; margin-top: 20px; }
        .actions button {
            padding: 10px 30px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
        }
        .actions button:hover { background: #1d4ed8; }
        @media print {
            body { background: #fff; padding: 0; }
            .receipt { box-shadow: none; padding: 20px; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    <div class="receipt" id="receipt">
        <div class="header">
            <h1 style="font-family:'Poppins',sans-serif;font-weight:700;letter-spacing:0.04em">Smart Mall</h1>
            <p>Receipt</p>
            <p>Order #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></p>
            <p><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></p>
        </div>

        <div class="row label"><span>Customer</span><span></span></div>
        <div class="row"><span><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></span></div>
        <div class="row"><span><?php echo htmlspecialchars($order['email']); ?></span></div>

        <div class="divider"></div>

        <div class="row label"><span>Items</span><span>Total</span></div>
        <?php foreach ($items as $item): ?>
        <div class="item">
            <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
            <div class="row item-meta">
                <span><?php echo $item['quantity']; ?> x <?php echo smartmall_format_money($item['price']); ?></span>
                <span><?php echo smartmall_format_money($item['price'] * $item['quantity']); ?></span>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="divider"></div>

        <div class="row label"><span>Payment</span><span></span></div>
        <div class="row"><span>Method</span><span><?php echo ucwords(str_replace('_', ' ', $order['pay_method'] ?? $order['payment_method'])); ?></span></div>
        <?php if ($order['tx_ref']): ?>
        <div class="row"><span>Tx Ref</span><span><?php echo htmlspecialchars($order['tx_ref']); ?></span></div>
        <?php endif; ?>
        <?php if ($order['paid_at']): ?>
        <div class="row"><span>Paid At</span><span><?php echo date('M d, Y h:i A', strtotime($order['paid_at'])); ?></span></div>
        <?php endif; ?>

        <div class="divider"></div>

        <?php $subtotal = $order['total_price'] / 1.1; ?>
        <div class="row"><span>Subtotal</span><span><?php echo smartmall_format_money($subtotal); ?></span></div>
        <div class="row"><span>Tax (10%)</span><span><?php echo smartmall_format_money($order['total_price'] - $subtotal); ?></span></div>
        <div class="total row"><span>Total</span><span><?php echo smartmall_format_money($order['total_price']); ?></span></div>

        <?php if ($order['payment_status'] === 'paid' || $order['status'] === 'processing' || $order['status'] === 'shipped' || $order['status'] === 'delivered'): ?>
        <div style="text-align:center;">
            <div class="paid-stamp">PAID</div>
        </div>
        <?php endif; ?>

        <div class="footer">
            <p>Thank you for your purchase!</p>
            <p>Smart Mall &mdash; Addis Ababa, Ethiopia</p>
        </div>
    </div>

    <div class="actions">
        <button onclick="window.print()">Print / Save as PDF</button>
        <p style="margin-top:8px;font-size:12px;color:#888;">or use Ctrl+P (Cmd+P on Mac)</p>
    </div>
</body>
</html>
