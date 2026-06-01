<?php
// Admin Order Management
$page_title = 'Manage Orders - Smart Mall';

require_once __DIR__ . '/../config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$orders = [];
$error = '';
$success = '';
$order_search = htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES, 'UTF-8');

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    csrf_verify();
    try {
        $pdo = getDB();
        $order_id = (int) $_POST['order_id'];
        $new_status = $_POST['status'];
        $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($new_status, $allowed_statuses, true)) {
            $error = "Invalid order status.";
        } else {
            $stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE order_id = :order_id");
            $stmt->execute([':status' => $new_status, ':order_id' => $order_id]);
            $success = "Order #$order_id status updated to $new_status";
        }
    } catch (PDOException $e) {
        error_log("Admin manage_orders error (status update): " . $e->getMessage());
        $error = "Error updating order status. Please try again.";
    }
}

try {
    $pdo = getDB();
    // Get all orders with payment status for admin - paid orders first
    $stmt = $pdo->query("
        SELECT o.*, u.name as user_name, u.email as user_email,
               COALESCE(pay.status, 'paid') as payment_status,
               GROUP_CONCAT(CONCAT(p.name, '::', IFNULL(p.image, ''), '::', oi.quantity, '::', oi.price) SEPARATOR '||') as items_data
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id
        LEFT JOIN payments pay ON o.order_id = pay.order_id
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.product_id
        GROUP BY o.order_id
        ORDER BY 
            CASE COALESCE(pay.status, 'paid')
                WHEN 'paid' THEN 1
                WHEN 'pending' THEN 2
                WHEN 'failed' THEN 3
                ELSE 4
            END,
            o.created_at DESC
        LIMIT 100
    ");
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Admin manage_orders error: " . $e->getMessage());
    $error = "Database error. Please try again.";
}
include __DIR__ . '/../includes/header.php';
?>

<style>
    .back-btn {
        padding: 0.6rem 1.2rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.85rem;
        text-decoration: none;
        border: 1.5px solid var(--input-border);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        background: var(--surface);
        color: var(--text-dark);
        transition: all 0.25s;
    }

    .back-btn:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
        background: var(--primary-light);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
    }

    .search-box {
        position: relative;
    }

    .search-box .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.9rem;
        opacity: 0.45;
        pointer-events: none;
        transition: opacity 0.2s;
    }

    .search-box:focus-within .search-icon {
        opacity: 0.8;
    }

    .search-box input {
        padding: 0.65rem 2.2rem 0.65rem 2.1rem;
        border-radius: 12px;
        border: 1.5px solid var(--primary-color);
        font-size: 0.85rem;
        background: var(--input-bg);
        color: var(--text-dark);
        min-width: 220px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .search-box input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .search-box .clear-search {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%) scale(0);
        background: var(--border-color);
        border: none;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 0.65rem;
        line-height: 20px;
        text-align: center;
        cursor: pointer;
        color: var(--text-light);
        transition: transform 0.2s, opacity 0.2s;
        opacity: 0;
        padding: 0;
    }

    .search-box .clear-search.visible {
        transform: translateY(-50%) scale(1);
        opacity: 1;
    }

    .search-box .clear-search:hover {
        background: var(--text-light);
        color: var(--surface);
    }

    .no-results {
        display: none;
        text-align: center;
        padding: 4rem 2rem;
        background: var(--surface);
        border-radius: var(--radius);
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-color);
        margin-top: 1.5rem;
    }

    .no-results.show {
        display: block;
        animation: fadeIn 0.2s ease-out;
    }

    .no-results h3 {
        color: var(--text-dark);
        margin: 0 0 0.5rem;
    }

    .no-results p {
        color: var(--text-light);
        margin: 0;
    }

    .orders-table tbody tr.main-order-row {
        transition: opacity 0.15s;
    }

    .orders-table tbody tr.hidden-row {
        display: none;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .orders-table {
        width: 100%;
        border-collapse: collapse;
        background: var(--surface);
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: var(--shadow-md);
        margin-top: 2rem;
        border: 1px solid var(--border-color);
    }

    .orders-table thead {
        background-color: var(--primary-color);
        color: white;
    }

    .orders-table th {
        padding: 1.25rem;
        text-align: left;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.05em;
    }

    .orders-table td {
        padding: 1.25rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-dark);
    }

    .status-select {
        padding: 0.5rem;
        border-radius: 8px;
        border: 1.5px solid var(--border-color);
        background: var(--bg-light);
        color: var(--text-dark);
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .btn-update {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
    }

    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-processing {
        background: #e0f2fe;
        color: #075985;
    }

    .status-shipped {
        background: #dcfce7;
        color: #166534;
    }

    .status-delivered {
        background: #f0fdf4;
        color: #15803d;
    }

    .status-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    [data-theme='dark'] .status-pending {
        background: #451a03;
        color: #fbbf24;
    }

    [data-theme='dark'] .status-processing {
        background: #082f49;
        color: #38bdf8;
    }

    [data-theme='dark'] .status-shipped {
        background: #064e3b;
        color: #34d399;
    }

    [data-theme='dark'] .status-delivered {
        background: #064e3b;
        color: #34d399;
    }

    [data-theme='dark'] .status-cancelled {
        background: #450a0a;
        color: #f87171;
    }

    .details-row {
        display: none;
        background: var(--bg-light);
        animation: slide-down 0.3s ease;
    }

    .details-content {
        padding: 2rem;
        border-top: 1px solid var(--border-color);
    }

    .order-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
    }

    .order-items-list {
        background: var(--surface);
        border-radius: 12px;
        border: 1px solid var(--border-color);
        padding: 1rem;
    }

    .order-item-detail {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-color);
    }

    .order-item-detail:last-child {
        border-bottom: none;
    }

    .order-item-img {
        width: 50px;
        height: 50px;
        border-radius: 6px;
        object-fit: cover;
    }

    .shipping-info {
        background: var(--surface);
        padding: 1.5rem;
        border-radius: 12px;
        border: 1px solid var(--border-color);
    }

    .shipping-info h4 {
        margin-bottom: 1rem;
        font-family: 'Outfit', sans-serif;
        color: var(--primary-color);
    }

    .btn-view {
        background: var(--bg-light);
        color: var(--primary-color);
        border: 1px solid var(--primary-color);
        padding: 0.5rem 0.8rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-view:hover {
        background: var(--primary-color);
        color: #fff;
    }

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: var(--radius);
    }

    @media (max-width: 768px) {
        .orders-table {
            min-width: 720px;
            font-size: 0.85rem;
        }

        .orders-table th,
        .orders-table td {
            padding: 0.75rem 0.6rem;
        }

        .order-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .container {
            padding-bottom: 0.5rem;
        }

        .orders-table {
            min-width: unset;
            border-collapse: separate;
            border-spacing: 0;
            box-shadow: none;
            background: transparent;
            border: none;
        }

        .orders-table thead {
            display: none;
        }

        .orders-table tbody tr:not(.details-row) {
            display: block;
            background: var(--surface);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            box-shadow: var(--shadow-sm);
        }

        .orders-table tbody tr:not(.details-row) td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            padding: 0.35rem 0 !important;
            border: none;
            font-size: 0.8rem;
            gap: 0.5rem;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        .orders-table tbody tr:not(.details-row) td:before {
            content: attr(data-label);
            font-weight: 700;
            font-size: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-light);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .orders-table tbody tr:not(.details-row) td:not(:last-child) {
            border-bottom: 1px solid var(--border-color) !important;
            padding-bottom: 0.5rem !important;
            margin-bottom: 0.35rem;
        }

        .orders-table tbody tr.details-row td {
            display: block;
            padding: 0 !important;
            border: none;
        }

        .details-content {
            padding: 0.75rem;
        }

        .order-grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .shipping-info {
            padding: 0.75rem;
        }

        .shipping-info div {
            font-size: 0.8rem !important;
        }

        .order-items-list {
            padding: 0.75rem;
        }

        .order-item-detail {
            gap: 0.5rem;
            padding: 0.4rem 0;
        }

        .order-item-img {
            width: 36px;
            height: 36px;
        }

        .status-select {
            font-size: 0.75rem;
            padding: 0.35rem;
        }

        .btn-update {
            font-size: 0.75rem;
            padding: 0.35rem 0.6rem;
        }

        .btn-view {
            font-size: 0.65rem;
            padding: 0.2rem 0.4rem;
            margin-top: 0 !important;
        }
    }

    @keyframes slide-down {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="container" style="width: min(1300px, calc(100% - 32px)); padding-top: 4rem;">
    <div style="text-align:center; padding-bottom:1.5rem;">
        <h2 style="font-size:2.25rem; font-weight:700; font-family:'Outfit',sans-serif; margin:0; letter-spacing:-0.5px; background:linear-gradient(135deg, var(--text-dark) 0%, var(--primary-color) 100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; filter:drop-shadow(0 2px 4px rgba(0,0,0,0.1)); position:relative;">
            Manage Orders
        </h2>
        <span style="display:block; width:80px; height:4px; background:linear-gradient(90deg, var(--primary-color), transparent); border-radius:2px; margin:0.5rem auto 0;"></span>
    </div>
    <div style="display:flex; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:2rem;">
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
        <div style="margin-left:auto;" class="search-box">
            <span class="search-icon">&#x1F50D;</span>
            <input type="text" id="orderSearch" placeholder="Search by ID, customer, or status..." value="<?php echo $order_search; ?>" oninput="filterOrders()">
            <button class="clear-search" id="clearSearch" onclick="clearOrderSearch()" aria-label="Clear search">&times;</button>
        </div>
    </div>

    <div class="no-results" id="noOrderResults">
        <h3>No orders match your search</h3>
        <p>Try a different order ID, customer name, or status.</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (count($orders) > 0): ?>
        <div class="table-wrapper">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Products Ordered</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr class="main-order-row" style="cursor: pointer;" onclick="toggleDetails(<?php echo $order['order_id']; ?>)">
                            <td data-label="Order ID">
                                <strong>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></strong>
                                <button class="btn-view" style="margin-top: 0.4rem;">View Details</button>
                            </td>
                            <td data-label="Customer">
                                <strong
                                    style="font-size: 1rem;"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></strong><br>
                                <small
                                    style="color: var(--text-light);"><?php echo htmlspecialchars($order['user_name']); ?></small>
                                <small
                                    style="display:block; color: var(--text-light); font-size:0.7rem;"><?php echo htmlspecialchars($order['user_email']); ?></small>
                            </td>
                            <td data-label="Products">
                                <div style="font-weight: 600; color: var(--primary-color);">
                                    <?php
                                    $item_count = !empty($order['items_data']) ? count(explode('||', $order['items_data'])) : 0;
                                    echo $item_count . ($item_count == 1 ? ' Item' : ' Items');
                                    ?>
                                </div>
                            </td>
                            <td data-label="Total"><strong
                                    style="font-size: 1.1rem;"><?php echo smartmall_format_money($order['total_price']); ?></strong>
                            </td>
                            <td data-label="Date"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td data-label="Status">
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo $order['status']; ?>
                                </span>
                                <br>
                                <small
                                    style="color: <?php echo $order['payment_status'] === 'paid' ? '#16a34a' : ($order['payment_status'] === 'pending' ? '#f59e0b' : '#dc2626'); ?>">
                                    💳 <?php echo ucfirst($order['payment_status']); ?>
                                </small>
                            </td>
                            <td data-label="Action" onclick="event.stopPropagation()">
                                <form method="POST" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <select name="status" class="status-select">
                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>
                                            Pending</option>
                                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>
                                            Shipped</option>
                                        <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn-update">Update</button>
                                </form>
                            </td>
                        </tr>
                        <tr id="details-<?php echo $order['order_id']; ?>" class="details-row">
                            <td colspan="7">
                                <div class="details-content">
                                    <div class="order-grid">
                                        <div>
                                            <h4 style="margin-bottom: 1rem; font-family: 'Outfit', sans-serif;">Order Items</h4>
                                            <div class="order-items-list">
                                                <?php
                                                if (!empty($order['items_data'])) {
                                                    $items = explode('||', $order['items_data']);
                                                    foreach ($items as $item_raw) {
                                                        $item = explode('::', $item_raw);
                                                ?>
                                                        <div class="order-item-detail">
                                                            <img src="<?php echo !empty($item[1]) ? htmlspecialchars(get_product_image_url($item[1])) : '../assets/no-image.png'; ?>"
                                                                class="order-item-img" alt="">
                                                            <div style="flex: 1;">
                                                                <div style="font-weight: 700;"><?php echo htmlspecialchars($item[0]); ?>
                                                                </div>
                                                                <div style="font-size: 0.8rem; color: var(--text-light);">Unit Price:
                                                                    <?php echo smartmall_format_money((float) $item[3]); ?></div>
                                                            </div>
                                                            <div style="text-align: right;">
                                                                <div style="font-weight: 700;">x<?php echo $item[2]; ?></div>
                                                                <div style="color: var(--primary-color); font-weight: 700;">
                                                                    <?php echo smartmall_format_money((float) $item[3] * (int) $item[2]); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="shipping-info">
                                                <h4>Shipping Details</h4>
                                                <div style="line-height: 1.6; font-size: 0.9rem;">
                                                    <strong>Address:</strong><br>
                                                    <?php echo htmlspecialchars($order['address']); ?><br>
                                                    <?php echo htmlspecialchars($order['city'] . ', ' . $order['state'] . ' ' . $order['zip']); ?><br>
                                                    <?php echo htmlspecialchars($order['country']); ?>
                                                </div>
                                                <div style="margin-top: 1.5rem;">
                                                    <strong>Payment:</strong><br>
                                                    <span
                                                        style="text-transform: capitalize;"><?php echo str_replace('_', ' ', $order['payment_method']); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div
            style="text-align: center; padding: 4rem; background: var(--surface); border-radius: var(--radius); box-shadow: var(--shadow-md); border: 1px solid var(--border-color);">
            <h3 style="color: var(--text-dark);">No orders found</h3>
            <p style="color: var(--text-light);">Orders will appear here once customers place them.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>

<script>
    function toggleDetails(orderId) {
        const row = document.getElementById('details-' + orderId);
        if (row.style.display === 'table-row') {
            row.style.display = 'none';
        } else {
            row.style.display = 'table-row';
        }
    }

    function filterOrders() {
        const input = document.getElementById('orderSearch');
        const clearBtn = document.getElementById('clearSearch');
        const filter = input.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.orders-table tbody tr.main-order-row');
        const noResults = document.getElementById('noOrderResults');
        let visibleCount = 0;

        clearBtn.classList.toggle('visible', filter.length > 0);

        rows.forEach(row => {
            const detailId = row.getAttribute('onclick')?.match(/\d+/)?.[0];
            const detailRow = detailId ? document.getElementById('details-' + detailId) : null;
            const fullText = row.textContent.toLowerCase() + ' ' + (detailRow ? detailRow.textContent.toLowerCase() : '');

            const match = !filter || fullText.includes(filter);
            row.classList.toggle('hidden-row', !match);
            if (detailRow) {
                detailRow.classList.toggle('hidden-row', !match);
                if (match && detailRow.style.display === 'table-row') {
                    detailRow.style.display = 'table-row';
                } else if (!match) {
                    detailRow.style.display = 'none';
                }
            }
            if (match) visibleCount++;
        });

        noResults.classList.toggle('show', visibleCount === 0 && filter.length > 0);
    }

    function clearOrderSearch() {
        const input = document.getElementById('orderSearch');
        input.value = '';
        filterOrders();
        input.focus();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('orderSearch');
        if (input && input.value.trim()) {
            filterOrders();
        }
    });
</script>