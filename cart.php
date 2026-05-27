<?php
require_once __DIR__ . '/config.php';
// Shopping Cart Page
//Load config FIRST — before ANY output

// Handle logic/redirects HERE (before header.php)
// No generic POST redirect: let specific form handlers process the request.

//$page_title = 'Shopping Cart - Smart Mall';

// 3️⃣ Set page-specific vars
$page_title = 'Shopping Cart - Smart Mall';
$current_page = 'cart.php';
$cart_count = $_SESSION['cart_count'] ?? 0;


// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=cart.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error   = '';

// ── Handle remove from cart (POST + CSRF) ─────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    csrf_verify();
    try {
        $pdo     = getDB();
        $cart_id = (int)$_POST['remove_item'];

        // Verify ownership before deleting
        $stmt = $pdo->prepare("SELECT user_id FROM cart WHERE cart_id = :cart_id");
        $stmt->execute([':cart_id' => $cart_id]);
        $cart = $stmt->fetch();

        if ($cart && $cart['user_id'] == $user_id) {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = :cart_id");
            $stmt->execute([':cart_id' => $cart_id]);
            $_SESSION['success'] = 'Item removed from cart';
        }
    } catch (PDOException $e) {
        $_SESSION['cart_error'] = 'Could not remove item. Please try again.';
    }
    redirect('/cart.php');
    exit();
}
// ── Handle update quantity (POST + CSRF) ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    csrf_verify();
    try {
        $pdo     = getDB();
        $cart_id = (int)$_POST['cart_id'];
        $quantity = (int)$_POST['quantity'];

        // Verify ownership and stock
        $stmt = $pdo->prepare("
            SELECT c.user_id, p.stock
            FROM cart c
            JOIN products p ON c.product_id = p.product_id
            WHERE c.cart_id = :cart_id
        ");
        $stmt->execute([':cart_id' => $cart_id]);
        $cart = $stmt->fetch();

        if ($cart && $cart['user_id'] == $user_id && $quantity > 0 && $quantity <= $cart['stock']) {
            $stmt = $pdo->prepare("UPDATE cart SET quantity = :quantity WHERE cart_id = :cart_id");
            $stmt->execute([':quantity' => $quantity, ':cart_id' => $cart_id]);
        } elseif ($cart && $quantity > $cart['stock']) {
            $_SESSION['cart_error'] = 'Requested quantity exceeds available stock (' . $cart['stock'] . ' available).';
        }
    } catch (PDOException $e) {
        $_SESSION['cart_error'] = 'Could not update quantity. Please try again.';
    }
    redirect('/cart.php');
    exit();
}
// ── Load cart items ───────────────────────────────────────────
$cart_items  = [];
$total_price = 0;

try {
    $pdo  = getDB();
    $stmt = $pdo->prepare("
        SELECT c.cart_id, c.quantity, p.product_id, p.name, p.price, p.stock, p.image
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        WHERE c.user_id = :user_id
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([':user_id' => $user_id]);
    $cart_items = $stmt->fetchAll();

    foreach ($cart_items as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }
} catch (PDOException $e) {
    $error = 'Could not load cart. Please try again.';
}

$tax         = $total_price * 0.1;
$grand_total = $total_price + $tax;
include __DIR__ . '/includes/header.php';
?>

<style>
    .cart-page-title {
        margin-bottom: 2rem;
        color: var(--text-dark);
        font-size: clamp(1.4rem, 4vw, 2.2rem);
    }

    .cart-container {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 2rem;
        margin-top: 2rem;
    }

    .cart-items-section {
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
    }

    .cart-item {
        display: grid;
        grid-template-columns: 100px 1fr auto;
        gap: 1.5rem;
        padding: 1.5rem;
        border-bottom: 1px solid var(--border-color);
        align-items: center;
    }

    .cart-item:last-child {
        border-bottom: none;
    }

    .cart-item-image {
        width: 100px;
        height: 100px;
        background: var(--bg-light);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: var(--text-light);
        overflow: hidden;
        border-radius: 8px;
    }

    .cart-item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cart-item-details h3 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: var(--text-dark);
    }

    .cart-item-details p {
        color: var(--text-light);
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .cart-item-price {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }

    .cart-item-actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-end;
    }

    .quantity-form {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .quantity-form input {
        width: 60px;
        padding: 0.5rem;
        border: 1px solid var(--border-color);
        text-align: center;
        font-weight: 600;
        background: transparent;
        color: var(--text-dark);
    }

    .quantity-form button {
        padding: 0.5rem 1rem;
        background-color: var(--primary-color);
        color: white;
        border: none;
        cursor: pointer;
        font-size: 0.85rem;
        transition: background-color 0.3s ease;
    }

    .quantity-form button:hover {
        background-color: var(--primary-dark);
    }

    .btn-remove {
        color: var(--danger-color);
        background: none;
        border: none;
        cursor: pointer;
        font-size: 0.9rem;
        text-decoration: underline;
        padding: 0;
    }

    .btn-remove:hover {
        color: #991b1b;
    }

    .cart-summary {
        background: var(--surface);
        padding: 2rem;
        box-shadow: var(--shadow);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        height: fit-content;
        position: sticky;
        top: 100px;
    }

    .cart-summary h3 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.5rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        color: var(--text-dark);
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.8rem;
        font-size: 0.95rem;
        color: var(--text-light);
    }

    .summary-row.total {
        border-top: 2px solid var(--border-color);
        padding-top: 1rem;
        margin-top: 1rem;
        font-size: 1.3rem;
        font-weight: bold;
        color: var(--text-dark);
    }

    .summary-row.total span:last-child {
        color: var(--primary-color);
    }

    .btn-checkout {
        width: 100%;
        padding: 1rem;
        background-color: var(--primary-color);
        color: white;
        border: none;
        font-size: 1.05rem;
        font-weight: 700;
        cursor: pointer;
        margin-top: 1.5rem;
        transition: background-color 0.3s ease, transform 0.2s ease;
        text-decoration: none;
        display: block;
        text-align: center;
    }

    .btn-checkout:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
    }

    .btn-continue {
        width: 100%;
        padding: 0.7rem;
        background-color: var(--surface);
        color: var(--primary-color);
        border: 1px solid var(--primary-color);
        cursor: pointer;
        text-decoration: none;
        text-align: center;
        margin-bottom: 1rem;
        display: block;
        transition: background-color 0.3s ease;
        border-radius: 4px;
    }

    .btn-continue:hover {
        background-color: var(--primary-light);
    }

    .empty-cart {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-light);
    }

    .empty-cart h2 {
        font-size: 1.8rem;
        margin-bottom: 1rem;
        color: var(--text-dark);
    }

    .empty-cart p {
        margin-bottom: 2rem;
    }

    .empty-cart a {
        display: inline-block;
        padding: 0.8rem 2rem;
        background-color: var(--primary-color);
        color: white;
        text-decoration: none;
        font-weight: 700;
        transition: background-color 0.3s ease;
    }

    .empty-cart a:hover {
        background-color: var(--primary-dark);
    }

    @media (max-width: 768px) {
        .cart-container {
            grid-template-columns: 1fr;
        }

        .cart-summary {
            position: static;
        }

        .cart-item {
            grid-template-columns: 80px 1fr;
            gap: 1rem;
        }

        .cart-item-image {
            width: 80px;
            height: 80px;
        }

        .cart-item-actions {
            grid-column: 1 / -1;
            flex-direction: row;
            justify-content: space-between;
        }
    }

    @media (max-width: 480px) {
        .cart-container {
            margin-top: 1rem;
            gap: 1rem;
        }

        .cart-page-title {
            font-size: 1.4rem;
            margin-bottom: 1rem;
        }

        .cart-item {
            grid-template-columns: 60px 1fr;
            gap: 0.75rem;
            padding: 1rem;
        }

        .cart-item-image {
            width: 60px;
            height: 60px;
        }

        .cart-item-details h3 {
            font-size: 1rem;
        }

        .cart-item-details p {
            font-size: 0.8rem;
        }

        .cart-item-price {
            font-size: 0.85rem;
        }

        .cart-item-actions {
            padding-top: 0.5rem;
        }

        .cart-item-actions strong {
            font-size: 0.9rem;
        }

        .quantity-form {
            flex-direction: row;
            gap: 0.35rem;
        }

        .quantity-form input {
            width: 50px;
            padding: 0.35rem;
            font-size: 0.85rem;
        }

        .quantity-form button {
            padding: 0.35rem 0.75rem;
            font-size: 0.8rem;
        }

        .btn-remove {
            font-size: 0.8rem;
        }

        .cart-summary {
            padding: 1.25rem;
        }

        .cart-summary h3 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .summary-row {
            font-size: 0.85rem;
            margin-bottom: 0.6rem;
        }

        .summary-row.total {
            font-size: 1.1rem;
            padding-top: 0.75rem;
            margin-top: 0.75rem;
        }

        .btn-checkout {
            padding: 0.85rem;
            font-size: 0.95rem;
            margin-top: 1rem;
        }

        .btn-continue {
            padding: 0.6rem;
            font-size: 0.85rem;
        }

        .empty-cart {
            padding: 2.5rem 1.25rem;
        }

        .empty-cart h2 {
            font-size: 1.4rem;
        }

        .empty-cart p {
            font-size: 0.9rem;
        }

        .empty-cart a {
            padding: 0.7rem 1.5rem;
            font-size: 0.9rem;
        }
    }
</style>

<div class="container">
    <?php if (isset($_SESSION['success'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => showToast("<?php echo addslashes($_SESSION['success']); ?>", "success"));
        </script>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['cart_error'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => showToast("<?php echo addslashes($_SESSION['cart_error']); ?>", "error"));
        </script>
        <?php unset($_SESSION['cart_error']); ?>
    <?php endif; ?>

    <?php if ($error): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => showToast("<?php echo addslashes($error); ?>", "error"));
        </script>
    <?php endif; ?>

    <h1 class="cart-page-title">Shopping Cart</h1>

    <?php if (count($cart_items) > 0): ?>
        <div class="cart-container">
            <!-- Cart Items -->
            <div class="cart-items-section">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <!-- Image -->
                        <div class="cart-item-image">
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?php echo htmlspecialchars(get_product_image_url($item['image'])); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <?php else: ?>
                                📦
                            <?php endif; ?>
                        </div>

                        <!-- Details -->
                        <div class="cart-item-details">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p>SKU: #000<?php echo str_pad($item['product_id'], 4, '0', STR_PAD_LEFT); ?></p>
                            <p style="color: var(--success-color); font-weight: 600;">✓ In Stock (<?php echo $item['stock']; ?> available)</p>
                            <div class="cart-item-price"><?php echo smartmall_format_money($item['price']); ?> each</div>
                        </div>

                        <!-- Actions -->
                        <div class="cart-item-actions">
                            <div style="text-align: right; margin-bottom: 0.5rem;">
                                <strong><?php echo smartmall_format_money($item['price'] * $item['quantity']); ?></strong>
                            </div>

                            <!-- Quantity Update Form -->
                            <form action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="POST" class="quantity-form">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="update_quantity" value="1">
                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" required>
                                <button type="submit" name="update_quantity">Update</button>
                            </form>

                            <!-- Remove Form -->
                            <form action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="POST" style="margin-top: 0.5rem;">
                                <?php csrf_field(); ?>
                                <button type="submit" name="remove_item" value="<?php echo $item['cart_id']; ?>" class="btn-remove">Remove</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Order Summary -->
            <div class="cart-summary">
                <h3>Order Summary</h3>

                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span><?php echo smartmall_format_money($total_price); ?></span>
                </div>

                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>Free</span>
                </div>

                <div class="summary-row">
                    <span>Tax (10%):</span>
                    <span><?php echo smartmall_format_money($tax); ?></span>
                </div>

                <div class="summary-row total">
                    <span>Total:</span>
                    <span><?php echo smartmall_format_money($grand_total); ?></span>
                </div>

                <a href="checkout.php" class="btn-checkout">Proceed to Checkout</a>
                <a href="index.php" class="btn-continue" style="margin-top: 0.75rem;">Continue Shopping</a>

                <div style="text-align: center; margin-top: 1rem; color: var(--text-light); font-size: 0.9rem;">
                    <p>✓ Secure checkout</p>
                    <p>✓ Free returns</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-cart">
            <h2>Your Cart is Empty</h2>
            <p>Looks like you haven't added any items yet.</p>
            <a href="index.php">Start Shopping</a>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>