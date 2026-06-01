<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/currency.php';

$page_title = 'Reports - Smart Mall';
$selected_currency = smartmall_selected_currency();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$period = $_GET['period'] ?? '30';
$allowed_periods = ['today' => 0, '1h' => 1, '6h' => 6, '12h' => 12, '24h' => 24, '7' => 7, '30' => 30, '90' => 90, '365' => 365, 'all' => null];
if (!array_key_exists($period, $allowed_periods)) $period = '30';
$value = $allowed_periods[$period];

$dateCond = "";
$dateCondPrefix = "";
$dateCondJoin = "";

if ($value !== null) {
    if (str_ends_with($period, 'h')) {
        $dateCond = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL $value HOUR)";
        $dateCondPrefix = "WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL $value HOUR)";
        $dateCondJoin = "AND o.created_at >= DATE_SUB(NOW(), INTERVAL $value HOUR)";
    } elseif ($value === 0) {
        $dateCond = "WHERE DATE(created_at) = CURDATE()";
        $dateCondPrefix = "WHERE DATE(o.created_at) = CURDATE()";
        $dateCondJoin = "AND DATE(o.created_at) = CURDATE()";
    } else {
        $dateCond = "WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL $value DAY)";
        $dateCondPrefix = "WHERE o.created_at >= DATE_SUB(CURDATE(), INTERVAL $value DAY)";
        $dateCondJoin = "AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL $value DAY)";
    }
}

$pdo = getDB();
$error = '';

try {
    // Daily revenue
    $stmt = $pdo->query("
        SELECT DATE(created_at) as day, COUNT(*) as orders, SUM(total_price) as revenue
        FROM orders
        $dateCond
        GROUP BY DATE(created_at)
        ORDER BY day
    ");
    $dailyData = $stmt->fetchAll();

    // Category breakdown
    $stmt = $pdo->query("
        SELECT c.name,
               COUNT(CASE WHEN o.order_id IS NOT NULL THEN oi.order_item_id END) as items,
               COALESCE(SUM(CASE WHEN o.order_id IS NOT NULL THEN oi.price * oi.quantity ELSE 0 END), 0) as revenue
        FROM categories c
        LEFT JOIN products p ON c.category_id = p.category_id
        LEFT JOIN order_items oi ON p.product_id = oi.product_id
        LEFT JOIN orders o ON oi.order_id = o.order_id $dateCondJoin
        GROUP BY c.category_id
        ORDER BY revenue DESC
    ");
    $categoryData = $stmt->fetchAll();

    // Top products
    $stmt = $pdo->query("
        SELECT p.name, SUM(oi.quantity) as sold, SUM(oi.price * oi.quantity) as revenue
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        JOIN orders o ON oi.order_id = o.order_id
        $dateCondPrefix
        GROUP BY oi.product_id
        ORDER BY revenue DESC
        LIMIT 10
    ");
    $topProducts = $stmt->fetchAll();

    // Order status
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders $dateCond GROUP BY status ORDER BY count DESC");
    $orderStatus = $stmt->fetchAll();

    // Summary stats (orders)
    $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(total_price) as revenue FROM orders $dateCond");
    $summary = $stmt->fetch();

    $stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) as customers FROM orders $dateCond");
    $customers = $stmt->fetch()['customers'];

    $stmt = $pdo->query("SELECT AVG(total_price) as avg_order FROM orders $dateCond");
    $avgOrder = $stmt->fetch()['avg_order'] ?? 0;

    // New users over time
    $stmt = $pdo->query("
        SELECT DATE(created_at) as day, COUNT(*) as count
        FROM users
        $dateCond
        GROUP BY DATE(created_at)
        ORDER BY day
    ");
    $userRegistrations = $stmt->fetchAll();

    // Payment method breakdown
    $stmt = $pdo->query("
        SELECT COALESCE(pay.method, o.payment_method) as method,
               COUNT(*) as count,
               COALESCE(SUM(pay.amount), SUM(o.total_price)) as revenue
        FROM orders o
        LEFT JOIN payments pay ON o.order_id = pay.order_id
        $dateCondPrefix
        GROUP BY method
        ORDER BY revenue DESC
    ");
    $paymentMethods = $stmt->fetchAll();

    // Recent orders
    $stmt = $pdo->query("
        SELECT o.order_id, o.first_name, o.last_name, o.total_price, o.status, o.created_at,
               COALESCE(pay.status, 'paid') as payment_status
        FROM orders o
        LEFT JOIN payments pay ON o.order_id = pay.order_id
        $dateCondPrefix
        ORDER BY o.created_at DESC
        LIMIT 20
    ");
    $recentOrders = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Reports error: " . $e->getMessage());
    $error = "Failed to load report data.";
}

include __DIR__ . '/../includes/header.php';
?>

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
        padding: 1.5rem 1.5rem 1.25rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04);
        position: relative;
        overflow: hidden;
    }

    .rp-stat::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
    }

    .rp-stat:nth-child(1)::before {
        background: linear-gradient(90deg, #2563eb, #3b82f6);
    }

    .rp-stat:nth-child(2)::before {
        background: linear-gradient(90deg, #059669, #10b981);
    }

    .rp-stat:nth-child(3)::before {
        background: linear-gradient(90deg, #7c3aed, #8b5cf6);
    }

    .rp-stat:nth-child(4)::before {
        background: linear-gradient(90deg, #d97706, #f59e0b);
    }

    .rp-stat:nth-child(5)::before {
        background: linear-gradient(90deg, #0891b2, #06b6d4);
    }

    .rp-stat h3 {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-light);
        margin-bottom: 0.35rem;
        font-weight: 600;
    }

    .rp-stat .val {
        font-size: 1.65rem;
        font-weight: 800;
        color: var(--text-dark);
        font-family: 'Outfit', sans-serif;
        letter-spacing: -0.02em;
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
        box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    }

    .rp-card .chart-wrap {
        position: relative;
        height: 220px;
    }

    .rp-card .chart-wrap canvas {
        display: block;
        max-width: 100%;
        max-height: 100%;
        width: 100% !important;
        height: 100% !important;
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
        margin-bottom: auto;
        margin-left: 3rem;
        ;
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

    @media (max-width: 768px) {
        .rp-chart-row {
            grid-template-columns: 1fr;
        }

        .rp-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .rp-card .chart-wrap {
            height: 180px;
        }
    }

    @media (max-width: 480px) {
        .rp-top-table {
            border-collapse: separate;
            border-spacing: 0;
            background: transparent;
            border: none;
        }

        .rp-top-table thead {
            display: none;
        }

        .rp-top-table tbody tr {
            display: block;
            background: var(--surface);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            box-shadow: var(--shadow-sm);
        }

        .rp-top-table tbody tr td {
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

        .rp-top-table tbody tr td:before {
            content: attr(data-label);
            font-weight: 700;
            font-size: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-light);
            white-space: nowrap;
            flex-shrink: 0;
        }
    }
</style>

<div class="container" style="width: min(1300px, calc(100% - 32px)); padding-top: 4rem;">
    <div style="text-align:center; padding-bottom:1.5rem;">
        <h2 style="font-size:2.25rem; font-weight:700; font-family:'Outfit',sans-serif; margin:0; letter-spacing:-0.5px; background:linear-gradient(135deg, var(--text-dark) 0%, var(--primary-color) 100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; filter:drop-shadow(0 2px 4px rgba(0,0,0,0.1)); position:relative;">
            Reports & Analytics
        </h2>
        <span style="display:block; width:80px; height:4px; background:linear-gradient(90deg, var(--primary-color), transparent); border-radius:2px; margin:0.5rem auto 0;"></span>
    </div>
    <div class="admin-header" style="display:flex; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:2rem;">
        <div style="display:flex; align-items:center; gap:1.5rem; flex-wrap:wrap;">
            <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
            <div class="period-tabs" style="display:flex; gap:0.35rem; flex-wrap:wrap;">
                <a href="?period=1h" class="<?php echo $period === '1h' ? 'active' : ''; ?>">1 Hour</a>
                <a href="?period=6h" class="<?php echo $period === '6h' ? 'active' : ''; ?>">6 Hours</a>
                <a href="?period=12h" class="<?php echo $period === '12h' ? 'active' : ''; ?>">12 Hours</a>
                <a href="?period=24h" class="<?php echo $period === '24h' ? 'active' : ''; ?>">24 Hours</a>
                <a href="?period=today" class="<?php echo $period === 'today' ? 'active' : ''; ?>">Today</a>
                <a href="?period=7" class="<?php echo $period === '7' ? 'active' : ''; ?>">7 Days</a>
                <a href="?period=30" class="<?php echo $period === '30' ? 'active' : ''; ?>">30 Days</a>
                <a href="?period=90" class="<?php echo $period === '90' ? 'active' : ''; ?>">90 Days</a>
                <a href="?period=365" class="<?php echo $period === '365' ? 'active' : ''; ?>">This Year</a>
                <a href="?period=all" class="<?php echo $period === 'all' ? 'active' : ''; ?>">All Time</a>
            </div>
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
            <h3>Avg Order Value</h3>
            <div class="val"><?php echo smartmall_format_money($avgOrder); ?></div>
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
            <div class="chart-wrap"><canvas id="revenueChart"></canvas></div>
        </div>

        <!-- Category Donut -->
        <div class="rp-card">
            <h3>Revenue by Category</h3>
            <div class="chart-wrap"><canvas id="categoryChart"></canvas></div>
        </div>
    </div>

    <div class="rp-chart-row">
        <!-- Top Products -->
        <div class="rp-card">
            <h3>Top Selling Products</h3>
            <div class="chart-wrap"><canvas id="topProductsChart"></canvas></div>
        </div>

        <!-- Order Status -->
        <div class="rp-card">
            <h3>Order Status</h3>
            <div class="chart-wrap"><canvas id="statusChart"></canvas></div>
        </div>
    </div>

    <div class="rp-chart-row">
        <!-- New Users -->
        <div class="rp-card">
            <h3>New User Registrations</h3>
            <div class="chart-wrap"><canvas id="usersChart"></canvas></div>
        </div>

        <!-- Payment Methods -->
        <div class="rp-card">
            <h3>Payment Methods</h3>
            <div class="chart-wrap"><canvas id="paymentChart"></canvas></div>
        </div>
    </div>

    <!-- Recent Orders -->
    <?php if (count($recentOrders) > 0): ?>
        <div class="rp-card" style="margin-top:1.5rem;">
            <h3>Recent Orders</h3>
            <div style="overflow-x:auto;">
                <table class="rp-top-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $ro): ?>
                            <tr>
                                <td data-label="Order #"><a href="manage_orders.php?edit=<?php echo $ro['order_id']; ?>" style="color:var(--primary-color);font-weight:600;text-decoration:none;">#<?php echo $ro['order_id']; ?></a></td>
                                <td data-label="Customer"><?php echo htmlspecialchars($ro['first_name'] . ' ' . $ro['last_name']); ?></td>
                                <td data-label="Total"><?php echo smartmall_format_money($ro['total_price']); ?></td>
                                <td data-label="Payment"><span style="display:inline-block;padding:2px 8px;border-radius:6px;font-size:0.75rem;font-weight:600;background:<?php echo $ro['payment_status'] === 'paid' ? '#dcfce7' : '#fef3c7'; ?>;color:<?php echo $ro['payment_status'] === 'paid' ? '#16a34a' : '#d97706'; ?>;"><?php echo ucfirst($ro['payment_status']); ?></span></td>
                                <td data-label="Status"><span style="display:inline-block;padding:2px 8px;border-radius:6px;font-size:0.75rem;font-weight:600;background:<?php
                                                                                                                                                        $stColors = ['pending' => '#fef3c7', 'processing' => '#dbeafe', 'shipped' => '#ede9fe', 'delivered' => '#dcfce7', 'cancelled' => '#fee2e2'];
                                                                                                                                                        echo $stColors[$ro['status']] ?? '#f3f4f6';
                                                                                                                                                        ?>;color:<?php
                                                                                                                                                                    $stText = ['pending' => '#d97706', 'processing' => '#2563eb', 'shipped' => '#7c3aed', 'delivered' => '#16a34a', 'cancelled' => '#dc2626'];
                                                                                                                                                                    echo $stText[$ro['status']] ?? '#6b7280';
                                                                                                                                                                    ?>;"><?php echo ucfirst($ro['status']); ?></span></td>
                                <td data-label="Date" style="color:var(--text-light);font-size:0.8rem;"><?php echo date('M j, Y', strtotime($ro['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="../assets/js/chart.umd.min.js"></script>
<script>
    (function() {
        var currencySymbol = <?php echo json_encode($selected_currency === 'ETB' ? 'ETB ' : '$'); ?>;
        // Revenue chart
        var revCtx = document.getElementById('revenueChart');
        if (revCtx) {
            new Chart(revCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_column($dailyData, 'day')); ?>,
                    datasets: [{
                        label: 'Revenue',
                        data: <?php echo json_encode(array_map(function ($r) {
                                    return smartmall_convert_money((float)$r['revenue']);
                                }, $dailyData)); ?>,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37,99,235,0.08)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointHoverRadius: 9,
                        pointHoverBorderWidth: 3,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    hover: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            titleFont: {
                                size: 13,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 14
                            },
                            padding: 10,
                            cornerRadius: 6,
                            callbacks: {
                                label: function(ctx) {
                                    return currencySymbol + Number(ctx.parsed.y).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxTicksLimit: 10,
                                font: {
                                    size: 10
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                font: {
                                    size: 10
                                },
                                callback: function(v) {
                                    return currencySymbol + Number(v).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
                                }
                            }
                        }
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
                        data: <?php echo json_encode(array_map(function ($c) {
                                    return smartmall_convert_money((float)$c['revenue']);
                                }, $categoryData)); ?>,
                        backgroundColor: colors.slice(0, <?php echo count($categoryData); ?>),
                        hoverOffset: 12
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 11
                                },
                                padding: 12
                            }
                        },
                        tooltip: {
                            titleFont: { size: 13, weight: 'bold' },
                            bodyFont: { size: 14 },
                            padding: 10,
                            cornerRadius: 6,
                            callbacks: {
                                label: function(ctx) {
                                    return currencySymbol + Number(ctx.parsed).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
                                }
                            }
                        }
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
                        data: <?php echo json_encode(array_reverse(array_map(function ($p) {
                                    return smartmall_convert_money((float)$p['revenue']);
                                }, $topProducts))); ?>,
                        backgroundColor: '#2563eb',
                        hoverBackgroundColor: '#1d4ed8',
                        borderRadius: 4,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            titleFont: { size: 13, weight: 'bold' },
                            bodyFont: { size: 14 },
                            padding: 10,
                            cornerRadius: 6,
                            callbacks: {
                                label: function(ctx) {
                                    return currencySymbol + Number(ctx.parsed.x).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(v) {
                                    return currencySymbol + Number(v).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
                                },
                                font: {
                                    size: 9
                                }
                            }
                        },
                        y: {
                            ticks: {
                                font: {
                                    size: 9
                                }
                            }
                        }
                    }
                }
            });
        }

        // User registrations
        var usersCtx = document.getElementById('usersChart');
        if (usersCtx) {
            new Chart(usersCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_column($userRegistrations, 'day')); ?>,
                    datasets: [{
                        label: 'New Users',
                        data: <?php echo json_encode(array_map(function ($u) {
                                    return (int)$u['count'];
                                }, $userRegistrations)); ?>,
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5,150,105,0.08)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointHoverRadius: 9,
                        pointHoverBorderWidth: 3,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    hover: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxTicksLimit: 10,
                                font: {
                                    size: 10
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                font: {
                                    size: 10
                                },
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        // Payment methods
        var payCtx = document.getElementById('paymentChart');
        if (payCtx) {
            var payColors = ['#2563eb', '#059669', '#d97706', '#7c3aed', '#0891b2'];
            new Chart(payCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode(array_map(function ($p) {
                                return ucfirst($p['method'] ?: 'Unknown');
                            }, $paymentMethods)); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_map(function ($p) {
                                    return (int)$p['count'];
                                }, $paymentMethods)); ?>,
                        backgroundColor: payColors.slice(0, <?php echo count($paymentMethods) ?: 1; ?>),
                        hoverOffset: 12
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 11
                                },
                                padding: 12
                            }
                        },
                        tooltip: {
                            titleFont: { size: 13, weight: 'bold' },
                            bodyFont: { size: 14 },
                            padding: 10,
                            cornerRadius: 6,
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.label + ': ' + ctx.parsed + ' orders';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Order status
        var stCtx = document.getElementById('statusChart');
        if (stCtx) {
            var statusColors = {
                pending: '#f59e0b',
                processing: '#3b82f6',
                shipped: '#8b5cf6',
                delivered: '#10b981',
                cancelled: '#ef4444'
            };
            new Chart(stCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode(array_column($orderStatus, 'status')); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_column($orderStatus, 'count')); ?>,
                        backgroundColor: <?php
                                            $statusColors = ['pending' => '#f59e0b', 'processing' => '#3b82f6', 'shipped' => '#8b5cf6', 'delivered' => '#10b981', 'cancelled' => '#ef4444'];
                                            echo json_encode(array_map(function ($s) use ($statusColors) {
                                                return $statusColors[$s['status']] ?? '#6b7280';
                                            }, $orderStatus)); ?>,
                        hoverOffset: 12
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 11
                                },
                                padding: 12
                            }
                        },
                        tooltip: {
                            titleFont: { size: 13, weight: 'bold' },
                            bodyFont: { size: 14 },
                            padding: 10,
                            cornerRadius: 6,
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.label + ': ' + ctx.parsed + ' orders';
                                }
                            }
                        }
                    }
                }
            });
        }
    })();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>