<?php
require_once __DIR__ . '/../config.php';

$page_title = 'Reports - Smart Mall';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$period = $_GET['period'] ?? '30';
$days = in_array((int)$period, [7, 30, 90]) ? (int)$period : 30;

$pdo = getDB();
$error = '';

try {
    // Daily revenue
    $stmt = $pdo->prepare("
        SELECT DATE(created_at) as day, COUNT(*) as orders, SUM(total_price) as revenue
        FROM orders
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
        GROUP BY DATE(created_at)
        ORDER BY day
    ");
    $stmt->execute([':days' => $days]);
    $dailyData = $stmt->fetchAll();

    // Category breakdown
    $stmt = $pdo->query("
        SELECT c.name, COUNT(oi.order_item_id) as items, COALESCE(SUM(oi.price * oi.quantity), 0) as revenue
        FROM categories c
        LEFT JOIN products p ON c.category_id = p.category_id
        LEFT JOIN order_items oi ON p.product_id = oi.product_id
        GROUP BY c.category_id
        ORDER BY revenue DESC
    ");
    $categoryData = $stmt->fetchAll();

    // Top products
    $stmt = $pdo->query("
        SELECT p.name, SUM(oi.quantity) as sold, SUM(oi.price * oi.quantity) as revenue
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        GROUP BY oi.product_id
        ORDER BY revenue DESC
        LIMIT 10
    ");
    $topProducts = $stmt->fetchAll();

    // Order status
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status ORDER BY count DESC");
    $orderStatus = $stmt->fetchAll();

    // Summary stats
    $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(total_price) as revenue FROM orders");
    $summary = $stmt->fetch();
    $stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) as customers FROM orders");
    $customers = $stmt->fetch()['customers'];
} catch (PDOException $e) {
    error_log("Reports error: " . $e->getMessage());
    $error = "Failed to load report data.";
}

include __DIR__ . '/../includes/header.php';
?>
<?php include __DIR__ . '/includes/admin_nav.php'; ?>

<style>
    .rp-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
        margin: 1.5rem 0;
    }

    .rp-stat {
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.5rem;
    }

    .rp-stat h3 {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-light);
        margin-bottom: 0.5rem;
    }

    .rp-stat .val {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-dark);
    }

    .rp-chart-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-top: 1.5rem;
    }

    .rp-card {
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.5rem;
    }

    .rp-card h3 {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 1rem;
    }

    .rp-top-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
    }

    .rp-top-table th {
        text-align: left;
        padding: 0.6rem 0.5rem;
        color: var(--text-light);
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1px solid var(--border-color);
    }

    .rp-top-table td {
        padding: 0.6rem 0.5rem;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-dark);
    }

    .rp-top-table tr:last-child td {
        border-bottom: none;
    }

    .rp-top-table .rank {
        width: 28px;
        color: var(--text-light);
        font-weight: 700;
        text-align: center;
    }

    .period-tabs {
        display: flex;
        gap: 0.35rem;
        margin-bottom: 1rem;
    }

    .period-tabs a {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        text-decoration: none;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-light);
        transition: all 0.2s;
    }

    .period-tabs a:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    .period-tabs a.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    @media (max-width: 768px) {
        .rp-chart-row {
            grid-template-columns: 1fr;
        }
        .rp-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<div class="container" style="width: min(1300px, calc(100% - 32px));">
    <div class="admin-header">
        <h2>Reports & Analytics</h2>
        <div class="period-tabs">
            <a href="?period=7" class="<?php echo $days === 7 ? 'active' : ''; ?>">7 Days</a>
            <a href="?period=30" class="<?php echo $days === 30 ? 'active' : ''; ?>">30 Days</a>
            <a href="?period=90" class="<?php echo $days === 90 ? 'active' : ''; ?>">90 Days</a>
            <a href="?period=all" class="<?php echo $period === 'all' ? 'active' : ''; ?>">All Time</a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Summary Stats -->
    <div class="rp-grid">
        <div class="rp-stat">
            <h3>Total Revenue</h3>
            <div class="val"><?php echo smartmall_format_money($summary['revenue'] ?? 0); ?></div>
        </div>
        <div class="rp-stat">
            <h3>Total Orders</h3>
            <div class="val"><?php echo (int)($summary['total'] ?? 0); ?></div>
        </div>
        <div class="rp-stat">
            <h3>Customers</h3>
            <div class="val"><?php echo (int)$customers; ?></div>
        </div>
        <div class="rp-stat">
            <h3>Categories</h3>
            <div class="val"><?php echo count($categoryData); ?></div>
        </div>
    </div>

    <div class="rp-chart-row">
        <!-- Revenue Line Chart -->
        <div class="rp-card">
            <h3>Revenue Over Time</h3>
            <canvas id="revenueChart" height="240"></canvas>
        </div>

        <!-- Category Donut -->
        <div class="rp-card">
            <h3>Revenue by Category</h3>
            <canvas id="categoryChart" height="240"></canvas>
        </div>
    </div>

    <div class="rp-chart-row">
        <!-- Top Products -->
        <div class="rp-card">
            <h3>Top Selling Products</h3>
            <canvas id="topProductsChart" height="200"></canvas>
        </div>

        <!-- Order Status -->
        <div class="rp-card">
            <h3>Order Status</h3>
            <canvas id="statusChart" height="200"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
(function () {
    // Revenue chart
    var revCtx = document.getElementById('revenueChart');
    if (revCtx) {
        new Chart(revCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($dailyData, 'day')); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode(array_map(function ($r) { return (float)$r['revenue']; }, $dailyData)); ?>,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37,99,235,0.08)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 10, font: { size: 10 } } },
                    y: { beginAtZero: true, ticks: { font: { size: 10 }, callback: function (v) { return '$' + v; } } }
                }
            }
        });
    }

    // Category donut
    var catCtx = document.getElementById('categoryChart');
    if (catCtx) {
        var colors = ['#2563eb', '#059669', '#d97706', '#dc2626', '#7c3aed', '#0891b2'];
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($categoryData, 'name')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_map(function ($c) { return (float)$c['revenue']; }, $categoryData)); ?>,
                    backgroundColor: colors.slice(0, <?php echo count($categoryData); ?>)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 12 } }
                }
            }
        });
    }

    // Top products horizontal bar
    var topCtx = document.getElementById('topProductsChart');
    if (topCtx) {
        new Chart(topCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_reverse(array_column($topProducts, 'name'))); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode(array_reverse(array_map(function ($p) { return (float)$p['revenue']; }, $topProducts))); ?>,
                    backgroundColor: '#2563eb',
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { callback: function (v) { return '$' + v; }, font: { size: 9 } } },
                    y: { ticks: { font: { size: 9 } } }
                }
            }
        });
    }

    // Order status
    var stCtx = document.getElementById('statusChart');
    if (stCtx) {
        var statusColors = { pending: '#f59e0b', processing: '#3b82f6', shipped: '#8b5cf6', delivered: '#10b981', cancelled: '#ef4444' };
        new Chart(stCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($orderStatus, 'status')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($orderStatus, 'count')); ?>,
                    backgroundColor: <?php echo json_encode(array_map(function ($s) use ($statusColors) { return $statusColors[$s['status']] ?? '#6b7280'; }, $orderStatus)); ?>
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 12 } }
                }
            }
        });
    }
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
