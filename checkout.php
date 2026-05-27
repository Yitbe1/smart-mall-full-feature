<?php
// Checkout Page — POST is processed BEFORE header.php to allow header() redirects
$page_title = 'Checkout - Smart Mall';

require_once 'includes/db.php';


if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout.php');
    exit();
}

$user_id     = $_SESSION['user_id'];
$cart_items  = [];
$total_price = 0;
$errors      = [];

// Load cart
try {
    $pdo  = getDB();
    $stmt = $pdo->prepare("
        SELECT c.cart_id, c.quantity, p.product_id, p.name, p.price, p.stock
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        WHERE c.user_id = :user_id
    ");
    $stmt->execute([':user_id' => $user_id]);
    $cart_items = $stmt->fetchAll();

    foreach ($cart_items as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }

    $tax         = $total_price * 0.1;
    $final_total = $total_price + $tax;
} catch (PDOException $e) {
    $errors[] = 'Could not load cart. Please try again.';
    $tax         = 0;
    $final_total = 0;
}

// Redirect if cart is empty (GET only)
if (count($cart_items) === 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit();
}

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    csrf_verify();

    if (count($cart_items) === 0) {
        $errors[] = 'Your cart is empty. Please add items before checkout.';
        // Don't process - just show error
    } else {
        try {
            $pdo = getDB();
            $pdo->beginTransaction();

            // Re-check stock for every item
            foreach ($cart_items as $item) {
                $stmt = $pdo->prepare("SELECT stock FROM products WHERE product_id = :product_id FOR UPDATE");
                $stmt->execute([':product_id' => $item['product_id']]);
                $current = $stmt->fetch();

                if (!$current || $current['stock'] < $item['quantity']) {
                    $pdo->rollBack();
                    $errors[] = '"' . htmlspecialchars($item['name']) . '" is out of stock. Please update your cart.';
                    goto render_page;
                }
            }

            $first_name     = trim($_POST['first_name'] ?? '');
            $last_name      = trim($_POST['last_name'] ?? '');
            $order_email    = trim($_POST['email'] ?? $_SESSION['user_email'] ?? '');
            $address        = trim($_POST['address'] ?? '');
            $city           = trim($_POST['city'] ?? '');
            $state          = trim($_POST['state'] ?? '');
            $zip            = trim($_POST['zip'] ?? '');
            $country        = trim($_POST['country'] ?? 'Ethiopia');
            $payment_method = $_POST['payment_method'] ?? 'chapa';

            $payment_amount = $final_total;
            $payment_currency = 'USD';

            if ($payment_method === 'chapa') {
                $etb_rate = smartmall_exchange_rate('ETB');
                if ($etb_rate <= 0) {
                    $pdo->rollBack();
                    $errors[] = 'Could not load the ETB exchange rate needed for Chapa payment. Please try again in a moment.';
                    goto render_page;
                }

                $payment_amount = round(smartmall_convert_money($final_total, 'ETB'), 2);
                $payment_currency = 'ETB';
            }

            // Create order with address
            $stmt = $pdo->prepare("
                INSERT INTO orders
                    (user_id, total_price, status, first_name, last_name, email,
                     address, city, state, zip, country, payment_method)
                VALUES
                    (:user_id, :total_price, 'pending', :fn, :ln, :email,
                     :address, :city, :state, :zip, :country, :pm)
            ");
            $stmt->execute([
                ':user_id'    => $user_id,
                ':total_price' => $final_total,
                ':fn'         => $first_name,
                ':ln'         => $last_name,
                ':email'      => $order_email,
                ':address'    => $address,
                ':city'       => $city,
                ':state'      => $state,
                ':zip'        => $zip,
                ':country'    => $country,
                ':pm'         => $payment_method,
            ]);

            $order_id = $pdo->lastInsertId();

            // 💳 Create payment record for ALL payment methods
            $paymentStatus = ($payment_method === 'cash_on_delivery') ? 'paid' : 'pending';
            $stmt = $pdo->prepare("
                INSERT INTO payments (order_id, method, status, amount, currency, chapa_response, created_at)
                VALUES (:order_id, :method, :status, :amount, :currency, '{}', NOW())
            ");
            $stmt->execute([
                ':order_id' => $order_id,
                ':method'   => $payment_method,
                ':status'   => $paymentStatus,
                ':amount'   => $payment_amount,
                ':currency' => $payment_currency
            ]);

            // Add order items and deduct stock
            foreach ($cart_items as $item) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price)
                    VALUES (:order_id, :product_id, :quantity, :price)
                ");
                $stmt->execute([
                    ':order_id'   => $order_id,
                    ':product_id' => $item['product_id'],
                    ':quantity'   => $item['quantity'],
                    ':price'      => $item['price'],
                ]);

                $stmt = $pdo->prepare("UPDATE products SET stock = stock - :qty WHERE product_id = :product_id");
                $stmt->execute([':qty' => $item['quantity'], ':product_id' => $item['product_id']]);
            }

            // 🟡 CHAPA PAYMENT: Do NOT clear cart here — wait for callback verification
            if ($payment_method === 'chapa') {
                // 1️⃣ Update payment record with tx_ref
                $txRef = 'ORD-' . $order_id . '-' . date('Ymd');
                $stmt = $pdo->prepare("UPDATE payments SET tx_ref = :tx_ref WHERE order_id = :order_id");
                $stmt->execute([':tx_ref' => $txRef, ':order_id' => $order_id]);

                // 2️⃣ Initialize Chapa
                require_once __DIR__ . '/chapa_pay/chapa-config.php';
                $CHAPA_KEY = CHAPA_SECRET_KEY;
                $DOMAIN    = 'http://localhost/reference/order_confirmation.php';  // For localhost testing

                if (empty($CHAPA_KEY) || $CHAPA_KEY === 'YOUR_CHAPA_SECRET_KEY_HERE') {
                    echo '<div style="padding:40px; font-family:Arial; background:#ffe3e3; border:1px solid #ffb3b3; border-radius:8px;">';
                    echo '<h3>⚠️ Chapa configuration error</h3>';
                    echo '<p>Please set a valid <strong>CHAPA_SECRET_KEY</strong> in <code>chapa-config.php</code> or environment variables.</p>';
                    echo '</div>';
                    exit;
                }

                $payload = [
                    'amount'       => number_format($payment_amount, 2, '.', ''),
                    'currency'     => 'ETB',
                    'email'        => $order_email,
                    'tx_ref'       => $txRef,
                    'callback_url' => $DOMAIN . '/chapa/callback.php',
                    'return_url'   => $DOMAIN . '/order_confirmation.php?order_id=' . $order_id,
                    'customization' => [
                        'title'       => 'Smart Mall Pay',
                        'description' => 'Order Payment'
                    ]
                ];

                // 3️ Call Chapa API with error handling
                $ch = curl_init('https://api.chapa.co/v1/transaction/initialize');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => json_encode($payload),
                    CURLOPT_HTTPHEADER     => [
                        'Authorization: Bearer ' . $CHAPA_KEY,
                        'Content-Type: application/json'
                    ],
                    CURLOPT_TIMEOUT        => 30,
                    CURLOPT_SSL_VERIFYPEER => true
                ]);
                $rawResponse = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);

                // 4️⃣ Parse & Validate Response
                $res = json_decode($rawResponse, true);

                if ($httpCode === 200 && isset($res['status']) && $res['status'] === 'success' && !empty($res['data']['checkout_url'])) {
                    // ✅ Valid response → Commit transaction and redirect to Chapa
                    $pdo->commit();
                    header('Location: ' . $res['data']['checkout_url']);
                    exit;
                } else {
                    // ❌ Failed → Show exact error instead of white page
                    echo '<div style="padding:40px; font-family:Arial; background:#fff3cd; border:1px solid #ffc107; border-radius:8px;">';
                    echo '<h3>⚠️ Chapa Initialization Failed</h3>';
                    echo '<p><strong>HTTP Code:</strong> ' . htmlspecialchars($httpCode) . '</p>';
                    echo '<p><strong>Curl Error:</strong> ' . htmlspecialchars($curlError ?: 'None') . '</p>';
                    echo '<pre style="background:#f8f9fa; padding:10px; border-radius:4px; overflow:auto;">' . htmlspecialchars($rawResponse) . '</pre>';
                    echo '<p><a href="/reference/checkout.php">← Back to Checkout</a></p>';
                    echo '</div>';
                    exit;
                }
            }

            // ✅ NON-CHAPA FLOW (cash_on_delivery, etc.) — keep existing behavior

            // 🔥 COMMIT TRANSACTION
            $pdo->commit();

            $_SESSION['success'] = 'Order placed! Order #' . str_pad($order_id, 6, '0', STR_PAD_LEFT);

            // 🟢 Clear cart for non-Chapa payments (Chapa clears after verification)
            if ($payment_method !== 'chapa') {
                $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id")->execute([':user_id' => $user_id]);
            }

            session_write_close();
            header('Location: order_confirmation.php?order_id=' . $order_id);
            exit();
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            $errors[] = 'Error placing order: ' . $e->getMessage();
        }
    }
}

render_page:
include 'includes/header.php';
?>

<style>
    .checkout-title {
        margin-bottom: 2rem;
        color: var(--text-dark);
        font-family: 'Outfit', sans-serif;
        font-size: clamp(1.4rem, 4vw, 2.2rem);
    }

    .checkout-container {
        display: grid;
        grid-template-columns: 1.3fr 1fr;
        gap: 2rem;
        align-items: start;
        margin-top: 2rem;
    }

    .checkout-form-section {
        background: var(--surface);
        padding: 2rem;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow);
        border-radius: var(--radius);
    }

    .form-section {
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid var(--border-color);
    }

    .form-section:last-of-type {
        border-bottom: none;
    }

    .form-section-title {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 1.25rem;
        font-family: 'Outfit', sans-serif;
        font-size: 1.15rem;
        color: var(--text-dark);
        font-weight: 700;
    }

    .form-section-title span {
        display: inline-grid;
        width: 28px;
        height: 28px;
        place-items: center;
        background: var(--primary-color);
        color: #fff;
        font-size: 0.78rem;
        font-weight: 900;
        font-family: inherit;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .form-row.full {
        grid-template-columns: 1fr;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        margin-bottom: 0.4rem;
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-light);
    }

    .form-group input,
    .form-group select {
        padding: 0.8rem 1rem;
        border: 1.5px solid var(--border-color);
        font-size: 1rem;
        background: var(--bg-light);
        color: var(--text-dark);
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--primary-color);
        background: var(--surface);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .payment-option {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        padding: 1rem 1.1rem;
        border: 1.5px solid var(--border-color);
        margin-bottom: 0.75rem;
        cursor: pointer;
        transition: border-color 0.2s, background 0.2s;
    }

    .payment-option input[type="radio"] {
        margin-top: 3px;
    }

    .payment-option:has(input:checked) {
        border-color: var(--primary-color);
        background: var(--primary-light);
    }

    .payment-option-label strong {
        display: block;
        color: var(--text-dark);
        font-size: 0.95rem;
        margin-bottom: 0.15rem;
    }

    .payment-option-label small {
        color: var(--text-light);
        font-size: 0.85rem;
    }

    .payment-notice {
        background: var(--primary-light);
        border-left: 3px solid var(--primary-color);
        padding: 0.75rem 1rem;
        font-size: 0.88rem;
        color: var(--primary-color);
        margin-top: 0.75rem;
    }

    /* Order Summary Sidebar */
    .checkout-summary {
        background: var(--surface);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow);
        border-radius: var(--radius);
        height: fit-content;
        position: sticky;
        top: 100px;
        overflow: hidden;
    }

    .summary-header {
        padding: 1.25rem 1.5rem;
        background: var(--primary-color);
        color: #fff;
        font-family: 'Outfit', sans-serif;
        font-size: 1.1rem;
        font-weight: 700;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .summary-body {
        padding: 1.25rem 1.5rem;
    }

    @media (max-width: 768px) {
        .checkout-summary {
            max-height: none;
        }
    }

    .checkout-summary.collapsed .summary-body {
        max-height: 0;
        padding: 0 1.5rem;
    }


    .order-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 0.7rem 0;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.9rem;
        gap: 0.5rem;
    }

    .order-item:last-child {
        border-bottom: none;
    }

    .order-item-name {
        color: var(--text-dark);
        flex: 1;
    }

    .order-item-qty {
        color: var(--text-light);
        font-size: 0.82rem;
    }

    .order-item-price {
        font-weight: 700;
        color: var(--text-dark);
        white-space: nowrap;
    }

    .summary-divider {
        border: none;
        border-top: 1px solid var(--border-color);
        margin: 1rem 0;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.6rem;
        font-size: 0.92rem;
        color: var(--text-light);
    }

    .summary-row.total {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--text-dark);
        margin-top: 0.5rem;
        padding-top: 0.75rem;
        border-top: 2px solid var(--border-color);
    }

    .summary-row.total span:last-child {
        color: var(--primary-color);
    }

    .btn-place-order {
        width: 100%;
        padding: 1.1rem;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        border: none;
        font-size: 1.05rem;
        font-weight: 800;
        cursor: pointer;
        margin-top: 1.25rem;
        letter-spacing: 0.03em;
        transition: opacity 0.2s, transform 0.2s;
    }

    .btn-place-order:hover {
        opacity: 0.92;
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        .checkout-container {
            grid-template-columns: 1fr;
        }

        .checkout-summary {
            position: static;
        }

        .form-row {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .checkout-title {
            font-size: 1.3rem;
            margin-bottom: 0.75rem;
        }

        .checkout-container {
            gap: 0.75rem;
            margin-top: 0;
        }

        .checkout-form-section {
            padding: 1rem;
        }

        .form-section {
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
        }

        .form-section:last-of-type {
            padding-bottom: 0;
        }

        .form-section-title {
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
            gap: 0.4rem;
        }

        .form-section-title span {
            width: 22px;
            height: 22px;
            font-size: 0.65rem;
        }

        .form-row {
            gap: 0.6rem;
            margin-bottom: 0;
        }

        .form-group label {
            font-size: 0.7rem;
            margin-bottom: 0.25rem;
        }

        .form-group input,
        .form-group select {
            padding: 0.55rem 0.75rem;
            font-size: 0.85rem;
        }

        .payment-option {
            padding: 0.6rem 0.75rem;
            gap: 0.6rem;
            margin-bottom: 0.4rem;
        }

        .payment-option-label strong {
            font-size: 0.82rem;
        }

        .payment-notice {
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
            margin-top: 0.4rem;
        }

        .btn-place-order {
            padding: 0.75rem;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        .summary-header {
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
        }

        .summary-body {
            padding: 0.75rem 1rem;
        }

        .order-item {
            font-size: 0.8rem;
            padding: 0.4rem 0;
        }

        .order-item-qty {
            font-size: 0.72rem;
        }

        .summary-row {
            font-size: 0.82rem;
            margin-bottom: 0.35rem;
        }

        .summary-row.total {
            font-size: 1rem;
            padding-top: 0.4rem;
            margin-top: 0.25rem;
        }
    }
</style>

<div class="container">
    <h1 class="checkout-title">Checkout</h1>

    <?php if (!empty($errors)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                <?php foreach ($errors as $e): ?>
                    showToast("<?php echo addslashes($e); ?>", "error");
                <?php endforeach; ?>
            });
        </script>
    <?php endif; ?>

    <div class="checkout-container">
        <!-- Form -->
        <div class="checkout-form-section">
            <form method="POST" action="">
                <?php csrf_field(); ?>

                <!-- Billing Address -->
                <div class="form-section">
                    <div class="form-section-title"><span>1</span> Billing Address</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="form-row full">
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? $_SESSION['user_email'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="form-row full">
                        <div class="form-group">
                            <label for="address">Street Address *</label>
                            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>" placeholder="e.g. 123 Bole Road" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City *</label>
                            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="state">State / Region *</label>
                            <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($_POST['state'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="zip">ZIP / Postal Code</label>
                            <input type="text" id="zip" name="zip" value="<?php echo htmlspecialchars($_POST['zip'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="country">Country *</label>
                            <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($_POST['country'] ?? 'Ethiopia'); ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Payment -->
                <div class="form-section">
                    <div class="form-section-title"><span>2</span> Payment Method</div>

                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="chapa"
                            <?= (!isset($_POST['payment_method']) || ($_POST['payment_method'] ?? '') === 'chapa') ? 'checked' : '' ?>>
                        <span class="payment-option-label"><strong>🟢 Chapa Pay</strong></span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="bank_transfer"
                            <?= (($_POST['payment_method'] ?? '') === 'bank_transfer') ? 'checked' : '' ?>>
                        <span class="payment-option-label"><strong>🏦 Bank Transfer</strong></span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="cash_on_delivery"
                            <?= (($_POST['payment_method'] ?? '') === 'cash_on_delivery') ? 'checked' : '' ?>>
                        <span class="payment-option-label"><strong>💵 Cash on Delivery</strong></span>
                    </label>

                    <div class="payment-notice">
                        ℹ️ This is a demo platform. No real payment is processed.
                    </div>
                </div>

                <input type="hidden" name="place_order" value="1">
                <button type="submit" class="btn-place-order">✓ Place Order</button>
            </form>
        </div>

        <!-- Summary Sidebar -->
        <div class="checkout-summary" id="checkout-summary">
            <div class="summary-header">
                <span>Your Order (<?php echo count($cart_items); ?> item<?php echo count($cart_items) !== 1 ? 's' : ''; ?>)</span>
            </div>
            <div class="summary-body" id="summary-body">
                <?php foreach ($cart_items as $item): ?>
                    <div class="order-item">
                        <div>
                            <div class="order-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="order-item-qty">Qty: <?php echo $item['quantity']; ?></div>
                        </div>
                        <div class="order-item-price"><?php echo smartmall_format_money($item['price'] * $item['quantity']); ?></div>
                    </div>
                <?php endforeach; ?>

                <hr class="summary-divider">

                <div class="summary-row"><span>Subtotal</span><span><?php echo smartmall_format_money($total_price); ?></span></div>
                <div class="summary-row"><span>Shipping</span><span>Free</span></div>
                <div class="summary-row"><span>Tax (10%)</span><span><?php echo smartmall_format_money($tax); ?></span></div>
                <div class="summary-row total"><span>Total</span><span><?php echo smartmall_format_money($final_total); ?></span></div>
                <?php if (smartmall_selected_currency() === 'USD' && smartmall_exchange_rate('ETB') > 0): ?>
                    <div style="margin-top: 0.85rem; color: var(--text-light); font-size: 0.84rem; line-height: 1.5;">
                        Chapa payments are charged in ETB: <?php echo smartmall_format_money($final_total, 'ETB'); ?>.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>
<?php require_once 'includes/footer.php'; ?>