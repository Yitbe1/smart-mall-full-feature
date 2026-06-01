<?php
require_once __DIR__ . '/config.php';
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=orders.php');
    exit();
}
// User Orders Page
$page_title = 'My Orders - Smart Mall';
$current_page = 'orders.php';
$user_id = $_SESSION['user_id'];
$orders = [];

try {
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT order_id, total_price, status, created_at, payment_method
        FROM orders
        WHERE user_id = :user_id 
        ORDER BY created_at DESC
    ");
    $stmt->execute([':user_id' => $user_id]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Smart Mall orders.php: " . $e->getMessage());
    $error = "Could not load your orders. Please try again.";
}

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    csrf_verify();
    try {
        $pdo = getDB();
        $order_id = (int)$_POST['order_id'];

        // Verify ownership and status before cancelling
        $stmt = $pdo->prepare("SELECT status FROM orders WHERE order_id = :order_id AND user_id = :user_id");
        $stmt->execute([':order_id' => $order_id, ':user_id' => $user_id]);
        $order_to_cancel = $stmt->fetch();

        if ($order_to_cancel && $order_to_cancel['status'] === 'pending') {
            // Mark as cancelled
            $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE order_id = :order_id");
            $stmt->execute([':order_id' => $order_id]);

            // Mark payment as failed
            $stmt = $pdo->prepare("UPDATE payments SET status = 'failed' WHERE order_id = ?");
            $stmt->execute([$order_id]);

            // Restore stock
            $stmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $stmt->execute([$order_id]);
            $items = $stmt->fetchAll();

            foreach ($items as $item) {
                $stmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE product_id = ?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }

            $_SESSION['success'] = "Order #$order_id has been cancelled.";
        } else {
            $_SESSION['error'] = "Order cannot be cancelled at this stage.";
        }
    } catch (PDOException $e) {
        error_log("Orders cancel error: " . $e->getMessage());
        $_SESSION['error'] = "Error cancelling order.";
    }
    header('Location: orders.php');
    exit();
}
include __DIR__ . '/includes/header.php';
?>

<style>
    .orders-container {
        max-width: 1000px;
        margin: 2rem auto;
    }

    .orders-header {
        margin-bottom: 2rem;
    }

    .orders-header h1 {
        color: var(--text-dark);
        font-family: 'Outfit', sans-serif;
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
    }

    .order-card {
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow-md);
        transition: all 0.3s ease;
    }

    .order-card:hover {
        box-shadow: var(--shadow-lg);
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .order-id {
        font-family: 'Outfit', sans-serif;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-dark);
    }

    .order-id span {
        color: var(--primary-color);
        font-family: 'Courier New', monospace;
    }

    .order-status {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 5px;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: capitalize;
    }

    .status-pending {
        background-color: var(--warning-color);
        color: #fff;
        opacity: 0.9;
    }

    .status-processing {
        background-color: var(--primary-color);
        color: #fff;
        opacity: 0.9;
    }

    .status-shipped {
        background-color: var(--success-color);
        color: #fff;
        opacity: 0.9;
    }

    .status-delivered {
        background-color: var(--success-color);
        color: #fff;
        opacity: 0.9;
    }

    .status-cancelled {
        background-color: var(--danger-color);
        color: #fff;
        opacity: 0.9;
    }

    .btn-cancel {
        background: none;
        border: 1px solid var(--danger-color);
        color: var(--danger-color);
        padding: 0.4rem 0.8rem;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.2s;
    }

    .btn-cancel:hover {
        background: var(--danger-color);
        color: white;
    }

    .order-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
    }

    .detail-label {
        font-size: 0.85rem;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.3rem;
    }

    .detail-value {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-dark);
    }

    .detail-value.total {
        color: var(--primary-color);
        font-size: 1.3rem;
    }

    .order-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn-view-order {
        padding: 0.7rem 1.5rem;
        background-color: var(--surface);
        color: var(--primary-color);
        border: 1px solid var(--primary-color);
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-view-order:hover {
        background-color: var(--primary-light);
    }

    .btn-reorder {
        padding: 0.7rem 1.5rem;
        background-color: var(--primary-color);
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
        transition: background-color 0.3s ease;
    }

    .btn-reorder:hover {
        background-color: var(--primary-dark);
    }

    .empty-orders {
        text-align: center;
        padding: 3rem;
        color: var(--text-light);
    }

    .empty-orders h2 {
        font-size: 1.8rem;
        color: var(--text-dark);
        margin-bottom: 1rem;
    }

    .empty-orders a {
        display: inline-block;
        padding: 0.8rem 2rem;
        background-color: var(--primary-color);
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-weight: 600;
        margin-top: 1rem;
        transition: background-color 0.3s ease;
    }

    .empty-orders a:hover {
        background-color: var(--primary-dark);
    }

    @media (max-width: 768px) {
        .orders-header h1 {
            font-size: 1.9rem;
        }

        .order-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .order-details {
            grid-template-columns: 1fr 1fr;
        }

        .order-actions {
            flex-direction: column;
        }

        .btn-view-order,
        .btn-reorder,
        .btn-cancel {
            width: 100%;
            text-align: center;
        }
    }

    @media (max-width: 480px) {
        .container {
            padding-bottom: 0.5rem;
        }

        .orders-container {
            margin: 0.75rem auto;
        }

        .orders-header {
            margin-bottom: 1rem;
        }

        .orders-header h1 {
            font-size: 1.4rem;
            margin-bottom: 0.2rem;
        }

        .orders-header p {
            font-size: 0.8rem !important;
        }

        .order-card {
            padding: 1rem;
            margin-bottom: 0.75rem;
        }

        .order-header {
            margin-bottom: 0;
            gap: 0.5rem;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 0.65rem;
            border-bottom: 1px solid var(--border-color);
        }

        .order-id {
            font-size: 0.85rem;
        }

        .order-status {
            padding: 0.2rem 0.6rem;
            font-size: 0.7rem;
            white-space: nowrap;
            border-radius: 3px;
        }

        .order-details {
            grid-template-columns: 1fr 1fr;
            gap: 0;
            margin-bottom: 0;
            padding: 0.65rem 0;
        }

        .detail-item {
            padding: 0.25rem 0;
        }

        .detail-item:nth-child(1) {
            order: 1;
        }

        .detail-item:nth-child(2) {
            order: 4;
        }

        .detail-item:nth-child(3) {
            order: 2;
        }

        .detail-item:nth-child(4) {
            order: 3;
        }

        .detail-label {
            font-size: 0.6rem;
            letter-spacing: 0.5px;
            margin-bottom: 0;
            text-transform: uppercase;
        }

        .detail-value {
            font-size: 0.82rem;
            font-weight: 500;
        }

        .detail-value.total {
            font-size: 1rem;
            color: var(--primary-color);
            font-weight: 700;
        }

        .order-actions {
            flex-direction: row;
            gap: 0.5rem;
            flex-wrap: nowrap;
            padding-top: 0.65rem;
            border-top: 1px solid var(--border-color);
        }

        .order-actions a,
        .order-actions form {
            flex: 1;
            min-width: 0;
        }

        .btn-view-order {
            width: 100%;
            text-align: center;
            padding: 0.6rem 0.5rem;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .btn-reorder {
            width: 100%;
            text-align: center;
            padding: 0.6rem 0.5rem;
            font-size: 0.78rem;
        }

        .btn-cancel {
            width: 100%;
            text-align: center;
            padding: 0.55rem 0.5rem;
            font-size: 0.78rem;
        }

        .empty-orders {
            padding: 2.5rem 1rem;
        }

        .empty-orders h2 {
            font-size: 1.3rem;
        }

        .empty-orders p {
            font-size: 0.85rem;
        }

        .empty-orders a {
            padding: 0.65rem 1.5rem;
            font-size: 0.85rem;
        }
    }
</style>

<div class="container">
    <div class="orders-container">
        <div class="orders-header">
            <h1>My Orders</h1>
            <p style="color: var(--text-light);">View and manage your orders</p>
        </div>

        <?php if (isset($error)): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($error); ?>, "error"));
            </script>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($_SESSION['success']); ?>, "success"));
            </script>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($_SESSION['error']); ?>, "error"));
            </script>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (count($orders) > 0): ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-id">Order <span>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></span></div>
                        </div>
                        <span class="order-status status-<?php echo $order['status']; ?>">
                            <?php echo htmlspecialchars($order['status']); ?>
                        </span>
                    </div>

                    <div class="order-details">
                        <div class="detail-item">
                            <span class="detail-label">Placed on</span>
                            <span class="detail-value"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Total</span>
                            <span class="detail-value total"><?php echo smartmall_format_money($order['total_price']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Payment</span>
                            <span class="detail-value"><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Status</span>
                            <span class="detail-value" style="text-transform: capitalize;"><?php echo htmlspecialchars($order['status']); ?></span>
                        </div>
                    </div>

                    <div class="order-actions">
                        <a href="order_confirmation.php?order_id=<?php echo $order['order_id']; ?>" class="btn-view-order">View Details →</a>

                        <?php if ($order['status'] === 'pending'): ?>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?')">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                <button type="submit" name="cancel_order" class="btn-cancel">Cancel</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-orders">
                <h2>No Orders Yet</h2>
                <p>You haven't placed any orders yet. Start shopping now!</p>
                <a href="index.php">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>
</div>