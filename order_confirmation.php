<?php
// Order Confirmation Page
require_once __DIR__ . '/config.php';

$page_title = 'Order Confirmation - Smart Mall';
//require_once 'includes/db.php';
$order_id = $_GET['order_id'] ?? 0;
if (!$order_id) {
    header('Location: checkout.php');
    exit();
}

$pdo = getDB();

$stmt = $pdo->prepare("SELECT o.*, COALESCE(p.status, 'paid') as payment_status FROM orders o LEFT JOIN payments p ON o.order_id = p.order_id WHERE o.order_id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: checkout.php');
    exit();
}

// 🟡 Handle payment states before showing confirmation
// Skip verification if already paid
if ($order['payment_status'] === 'paid') {
    // Already paid - skip to confirmation page
    error_log("[Order Confirmation] Order $order_id already paid - skipping verification");
}
// If payment is 'pending' AND it's a Chapa payment, verify immediately
elseif ($order['payment_status'] === 'pending' && $order['payment_method'] === 'chapa') {
    error_log("[Order Confirmation] Verifying payment for order $order_id");

    // Get payment record
    $stmt = $pdo->prepare("SELECT tx_ref, amount, currency FROM payments WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $payment = $stmt->fetch();

    if ($payment && !empty($payment['tx_ref'])) {
        require_once __DIR__ . '/chapa_pay/chapa-config.php';

        // Verify with Chapa API immediately
        $ch = curl_init(CHAPA_API_URL . '/transaction/verify/' . urlencode($payment['tx_ref']));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . CHAPA_SECRET_KEY],
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $verify = json_decode($response, true);

        // Check payment status
        $isSuccess = false;
        if ($httpCode === 200 && is_array($verify)) {
            $status = strtolower($verify['status'] ?? '');
            $dataStatus = is_array($verify['data']) ? strtolower($verify['data']['status'] ?? '') : '';
            $isSuccess = ($status === 'success' && $dataStatus === 'success');
        }

        // 🧪 TEST MODE: If using test key and user reached this page, assume success
        $isTestMode = strpos(CHAPA_SECRET_KEY, 'TEST') !== false;
        if ($isTestMode && !$isSuccess) {
            // User completed Chapa flow and returned - assume test payment succeeded
            $isSuccess = true;
            $verify = [
                'status' => 'success',
                'message' => 'Test mode - assumed successful',
                'data' => [
                    'status' => 'success',
                    'reference' => $payment['tx_ref'],
                    'amount' => $payment['amount'] ?? $order['total_price'],
                    'currency' => $payment['currency'] ?? 'ETB'
                ]
            ];
            error_log("[Order Confirmation] Test mode - assuming success for order $order_id");
        }

        error_log("[Order Confirmation] Verification result for order $order_id: " . ($isSuccess ? 'SUCCESS' : 'FAILED'));

        if ($isSuccess) {
            // ✅ Check if already paid (prevent duplicate processing)
            $stmt = $pdo->prepare("SELECT status FROM payments WHERE order_id = ?");
            $stmt->execute([$order_id]);
            $currentStatus = $stmt->fetchColumn();

            if ($currentStatus === 'paid') {
                error_log("[Order Confirmation] Order $order_id already paid - skipping");
                $order['payment_status'] = 'paid';
            } else {
                // Update payment with response and mark as paid
                $stmt = $pdo->prepare("UPDATE payments SET status = 'paid', paid_at = NOW(), chapa_response = ? WHERE order_id = ?");
                $stmt->execute([json_encode($verify), $order_id]);

                $stmt = $pdo->prepare("UPDATE orders SET status = 'processing' WHERE order_id = ?");
                $stmt->execute([$order_id]);

                // Clear cart
                $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
                $stmt->execute([$order['user_id']]);

                // Redirect to reload page with updated status
                header("Location: /reference/order_confirmation.php?order_id=$order_id&verified=1");
                exit();
            }
        } else {
            // Payment failed - mark as failed (don't delete)
            $stmt = $pdo->prepare("UPDATE payments SET status = 'failed', chapa_response = ? WHERE order_id = ?");
            $stmt->execute([json_encode($verify ?? ['error' => 'Verification failed']), $order_id]);

            $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE order_id = ?");
            $stmt->execute([$order_id]);

            // Restore stock
            $stmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $stmt->execute([$order_id]);
            $items = $stmt->fetchAll();

            foreach ($items as $item) {
                $stmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE product_id = ?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }

            // Refresh order data
            $order['payment_status'] = 'failed';
            $order['status'] = 'cancelled';
        }
    }
}

// If still pending after verification attempt, show waiting message
if ($order['payment_status'] === 'pending' && $order['payment_method'] === 'chapa') {
    include __DIR__ . '/includes/header.php';
?>
    <div class='payment-status-box pending'>
        <h2 style='color:#1976d2;'>⏳ Processing Your Payment</h2>
        <p>We're confirming your Chapa payment. This usually takes a few seconds.</p>
        <p id='countdown'>Refreshing in 3 seconds...</p>
        <script>
            let count = 3;

            function countdown() {
                document.getElementById('countdown').textContent = 'Refreshing in ' + count + ' seconds...';
                if (count-- <= 0) {
                    location.reload();
                } else {
                    setTimeout(countdown, 1000);
                }
            }
            countdown();
        </script>
        <p style='margin-top:20px;'>
            <a href='/reference/order_confirmation.php?order_id=<?php echo $order_id; ?>' style='margin-right:10px; padding:10px 20px; background:#1976d2; color:white; text-decoration:none; border-radius:4px; display:inline-block;'>Refresh Now</a>
            <a href='/reference/checkout.php' style='padding:10px 20px; background:#666; color:white; text-decoration:none; border-radius:4px; display:inline-block;'>Back to Checkout</a>
        </p>
    </div>
<?php
    include __DIR__ . '/includes/footer.php';
    exit();
}

// ❌ Payment failed
if ($order['payment_status'] === 'failed') {
    include __DIR__ . '/includes/header.php';
?>
    <div class='payment-status-box failed'>
        <h2 style='color:#d32f2f;'>❌ Payment Failed</h2>
        <p>Your payment was not completed. Please try again.</p>
        <p style='margin-top:20px;'>
            <a href='/reference/checkout.php' style='padding:10px 20px; background:#d32f2f; color:white; text-decoration:none; border-radius:4px; display:inline-block;'>Try Again</a>
        </p>
    </div>
<?php
    include __DIR__ . '/includes/footer.php';
    exit();
}
// ✅ If 'paid' or other status (COD), continue to show confirmation HTML below

// Verify user owns this order
if (!isset($_SESSION['user_id']) || $order['user_id'] != $_SESSION['user_id']) {
    header('Location: login.php');
    exit();
}

$order_id = (int)$order['order_id'];
$user_id = $_SESSION['user_id'];
$order_items = [];

try {
    // Get order items
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name, p.image
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = :order_id
    ");
    $stmt->execute([':order_id' => $order_id]);
    $order_items = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}

include __DIR__ . '/includes/header.php';
?>

<style>
    .confirmation-container {
        max-width: 900px;
        margin: 3rem auto;
        background: var(--surface);
        padding: 3rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow-xl);
    }

    .confirmation-header {
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 2px solid var(--primary-color);
    }

    .success-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    .confirmation-header h1 {
        color: var(--success-color);
        font-family: 'Outfit', sans-serif;
        font-size: 2.8rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
    }

    .order-number {
        font-family: 'Outfit', sans-serif;
        font-size: 1.5rem;
        color: var(--text-light);
        margin-bottom: 0.5rem;
    }

    .order-number strong {
        color: var(--secondary-color);
        font-weight: 700;
    }

    .confirmation-message {
        color: var(--text-light);
        font-size: 1rem;
        margin-bottom: 1rem;
    }

    .order-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin: 2rem 0;
    }

    .info-box {
        padding: 1.5rem;
        background-color: var(--bg-light);
        border-radius: 8px;
        border-left: 4px solid var(--primary-color);
    }

    .info-box h4 {
        color: var(--text-dark);
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-box p {
        color: var(--secondary-color);
        font-size: 1.1rem;
        font-weight: 600;
    }

    .order-items-section {
        margin: 2rem 0;
    }

    .order-items-section h3 {
        color: var(--secondary-color);
        font-family: 'Outfit', sans-serif;
        margin-bottom: 1rem;
        font-size: 1.5rem;
        font-weight: 700;
    }

    .info-box h4 {
        color: var(--text-light);
        font-family: 'Outfit', sans-serif;
        margin-bottom: 0.5rem;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        font-weight: 800;
    }

    .order-item-row {
        display: grid;
        grid-template-columns: 80px 1fr auto;
        gap: 1.5rem;
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
        align-items: center;
    }

    .order-item-row:last-child {
        border-bottom: none;
    }

    .order-item-image {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: white;
        overflow: hidden;
    }

    .order-item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .order-item-details h4 {
        color: var(--text-dark);
        margin-bottom: 0.3rem;
    }

    .order-item-details p {
        color: var(--text-light);
        font-size: 0.9rem;
    }

    .order-item-price {
        text-align: right;
        font-weight: 600;
        color: var(--primary-color);
    }

    .order-total {
        display: flex;
        justify-content: flex-end;
        gap: 3rem;
        padding: 1.5rem;
        background-color: var(--bg-light);
        border-radius: 8px;
        margin-top: 1rem;
    }

    .total-row {
        display: flex;
        gap: 2rem;
        align-items: center;
    }

    .total-row.grand {
        border-left: 2px solid var(--primary-color);
        padding-left: 2rem;
    }

    .total-row span:first-child {
        color: var(--text-light);
        min-width: 80px;
    }

    .total-row span:last-child {
        font-size: 1.3rem;
        font-weight: bold;
        color: var(--primary-color);
    }

    .next-steps {
        background-color: var(--surface);
        border-left: 4px solid var(--primary-color);
        padding: 1.5rem;
        border-radius: 8px;
        margin: 2rem 0;
        transition: padding 0.3s ease;
    }

    .next-steps-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: default;
    }

    .next-steps-header .steps-toggle {
        font-size: 0.8rem;
        opacity: 0.5;
        transition: transform 0.3s ease;
    }

    .next-steps-body {
        overflow: hidden;
        transition: max-height 0.35s ease, padding 0.35s ease;
    }

    @media (max-width: 480px) {
        .container {
            padding-bottom: 0.5rem;
        }
    }

    @media (max-width: 768px) {
        .next-steps-header {
            cursor: pointer;
        }

        .next-steps-body {
            max-height: 500px;
        }

        .next-steps.collapsed .next-steps-body {
            max-height: 0;
            padding: 0;
        }

        .next-steps.collapsed .steps-toggle {
            transform: rotate(-90deg);
        }
    }

    .next-steps h4 {
        color: var(--secondary-color);
        margin-bottom: 1rem;
    }

    .next-steps ol {
        list-style-position: inside;
        color: var(--text-light);
        line-height: 1.8;
    }

    .next-steps li {
        margin-bottom: 0.5rem;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin: 2rem 0;
        justify-content: center;
    }

    @media (max-width: 480px) {
        .action-buttons {
            flex-direction: column;
        }

        .btn-action {
            width: 100%;
            text-align: center;
        }
    }

    .btn-action {
        padding: 0.9rem 2rem;
        border: none;
        border-radius: 5px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        text-align: center;
        transition: all 0.3s ease;
        flex: 1;
        min-width: 200px;
    }

    .btn-action.primary {
        background-color: var(--primary-color);
        color: white;
    }

    .btn-action.primary:hover {
        background-color: var(--primary-dark);
        transform: translateY(-5px);
    }

    .btn-action.secondary {
        background-color: var(--primary-dark);
        color: white;
    }

    .btn-action.secondary:hover {
        background-color: var(--primary-dark);
        transform: translateY(-5px);
    }

    @media (max-width: 768px) {
        .confirmation-container {
            padding: 1.5rem;
        }

        .confirmation-header h1 {
            font-size: 1.8rem;
        }

        .order-item-row {
            grid-template-columns: 70px 1fr;
            gap: 1rem;
        }

        .order-item-price {
            grid-column: 1 / -1;
            text-align: left;
            margin-left: 70px;
        }

        .order-total {
            flex-direction: column;
            gap: 1rem;
            text-align: right;
        }

        .total-row {
            width: 100%;
            justify-content: space-between;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn-action {
            min-width: unset;
        }
    }

    @media (max-width: 480px) {
        .confirmation-container {
            padding: 1rem;
            margin: 1rem auto 0;
        }

        .confirmation-header {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
        }

        .success-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .confirmation-header h1 {
            font-size: 1.25rem;
        }

        .order-number {
            font-size: 0.85rem;
        }

        .confirmation-message {
            font-size: 0.8rem;
        }

        .order-info-grid {
            grid-template-columns: 1fr;
            gap: 0.5rem;
            margin: 0.75rem 0;
        }

        .info-box {
            padding: 0.75rem;
            border-left-width: 3px;
        }

        .info-box h4 {
            font-size: 0.7rem;
            margin-bottom: 0.25rem;
        }

        .info-box p,
        .info-box div {
            font-size: 0.8rem !important;
            line-height: 1.4;
        }

        .order-items-section {
            margin: 0.75rem 0;
        }

        .order-items-section h3 {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .order-item-row {
            grid-template-columns: 40px 1fr;
            gap: 0.5rem;
            padding: 0.5rem;
        }

        .order-item-image {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .order-item-details h4 {
            font-size: 0.8rem;
            margin-bottom: 0.15rem;
        }

        .order-item-details p {
            font-size: 0.75rem;
        }

        .order-item-price {
            font-size: 0.8rem;
            margin-left: 40px;
            padding-top: 0.15rem;
        }

        .order-total {
            padding: 0.75rem;
            gap: 0.35rem;
        }

        .total-row {
            gap: 0.75rem;
        }

        .total-row span:first-child {
            font-size: 0.75rem;
            min-width: 50px;
        }

        .total-row span:last-child {
            font-size: 0.85rem;
        }

        .total-row.grand {
            padding-left: 0.75rem;
        }

        .next-steps {
            padding: 0.75rem;
            margin: 0.75rem 0;
        }

        .next-steps h4 {
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }

        .next-steps ol {
            font-size: 0.78rem;
            line-height: 1.5;
        }

        .next-steps li {
            margin-bottom: 0.3rem;
        }

        .action-buttons {
            gap: 0.5rem;
            margin: 1rem 0;
        }

        .btn-action {
            padding: 0.6rem 1rem;
            font-size: 0.82rem;
        }
    }

    .payment-status-box {
        text-align: center;
        padding: 50px;
        font-family: Arial;
        border-radius: 8px;
        max-width: 600px;
        margin: 50px auto;
    }

    .payment-status-box.pending {
        background: #e3f2fd;
    }

    .payment-status-box.failed {
        background: #ffebee;
    }

    @media (max-width: 480px) {
        .payment-status-box {
            padding: 2rem 1.25rem;
            margin: 2rem auto;
            max-width: none;
        }

        .payment-status-box h2 {
            font-size: 1.3rem;
        }

        .payment-status-box p {
            font-size: 0.9rem;
        }

        .payment-status-box a {
            font-size: 0.9rem;
            padding: 0.65rem 1.25rem;
        }
    }
</style>

<div class="container">
    <div class="confirmation-container">
        <!-- Success Header -->
        <div class="confirmation-header">
            <div class="success-icon">✓</div>
            <h1>Order Confirmed!</h1>
            <div class="order-number">Order ID: <strong>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></strong></div>
            <p class="confirmation-message">Thank you for your purchase! We're preparing your order.</p>
        </div>

        <!-- Order Information -->
        <div class="order-info-grid">
            <div class="info-box">
                <h4>Shipping Address</h4>
                <div style="font-size: 0.95rem; color: var(--secondary-color);">
                    <strong><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></strong><br>
                    <?php echo htmlspecialchars($order['address']); ?><br>
                    <?php echo htmlspecialchars($order['city'] . ', ' . $order['state'] . ' ' . $order['zip']); ?><br>
                    <?php echo htmlspecialchars($order['country']); ?>
                </div>
            </div>
            <div class="info-box">
                <h4>Order Details</h4>
                <p style="font-size: 0.95rem;">
                    Method: <strong><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></strong><br>
                    Status: <strong style="text-transform: capitalize; color: var(--primary-color);"><?php echo htmlspecialchars($order['status']); ?></strong><br>
                    Total: <strong><?php echo smartmall_format_money($order['total_price']); ?></strong>
                </p>
            </div>
            <div class="info-box">
                <h4>Contact Info</h4>
                <p style="font-size: 0.95rem;">
                    <?php echo htmlspecialchars($order['email']); ?><br>
                    Date: <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                </p>
            </div>
        </div>

        <!-- Order Items -->
        <div class="order-items-section">
            <h3>Order Items</h3>

            <?php foreach ($order_items as $item): ?>
                <div class="order-item-row">
                    <div class="order-item-image">
                        <?php if (!empty($item['image'])): ?>
                            <img src="<?php echo htmlspecialchars(get_product_image_url($item['image'])); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <?php else: ?>
                            📦
                        <?php endif; ?>
                    </div>
                    <div class="order-item-details">
                        <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                        <p>Quantity: <?php echo $item['quantity']; ?> × <?php echo smartmall_format_money($item['price']); ?></p>
                    </div>
                    <div class="order-item-price">
                        <?php echo smartmall_format_money($item['price'] * $item['quantity']); ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Order Total -->
            <div class="order-total">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <?php $subtotal = $order['total_price'] / 1.1; ?>
                    <span><?php echo smartmall_format_money($subtotal); ?></span>
                </div>
                <div class="total-row">
                    <span>Tax (10%):</span>
                    <span><?php echo smartmall_format_money($order['total_price'] - $subtotal); ?></span>
                </div>
                <div class="total-row grand">
                    <span>Total:</span>
                    <span><?php echo smartmall_format_money($order['total_price']); ?></span>
                </div>
            </div>
        </div>

        <!-- Next Steps -->
        <div class="next-steps" id="nextSteps">
            <div class="next-steps-header" onclick="toggleNextSteps(this)">
                <h4>📋 What's Next?</h4>
                <span class="steps-toggle">▼</span>
            </div>
            <div class="next-steps-body">
                <ol>
                    <li><strong>Payment Processing</strong> — Your payment via <strong><?php echo htmlspecialchars($order['payment_method']); ?></strong> is being processed. We'll confirm once it's complete.</li>
                    <li><strong>Order Preparation</strong> — Once payment is confirmed, we'll begin preparing your items for delivery.</li>
                    <li><strong>Delivery</strong> — Your order will be delivered to <strong><?php echo htmlspecialchars($order['address']); ?></strong>. We'll notify you when it's on its way.</li>
                    <li><strong>Enjoy</strong> — We hope you love your purchase! If you have any issues, our support team is here to help.</li>
                </ol>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="/reference/index.php" class="btn-action secondary">Continue Shopping</a>
            <a href="/reference/orders.php" class="btn-action primary">View My Orders</a>
        </div>
    </div>
</div>

<script>
    function toggleNextSteps(header) {
        const section = header.closest('.next-steps');
        if (window.innerWidth <= 768) {
            section.classList.toggle('collapsed');
        }
    }
    if (window.innerWidth <= 768) {
        document.getElementById('nextSteps').classList.add('collapsed');
    }
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>