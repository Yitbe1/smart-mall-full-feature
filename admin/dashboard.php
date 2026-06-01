<?php
require_once __DIR__ . '/../config.php';
// Admin Dashboard
$page_title = 'Admin Dashboard - Smart Mall';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$stats = [];
$error = '';

try {
    $pdo = getDB();

    // Get statistics
    $stats['total_products'] = $pdo->query("SELECT COUNT(*) as total FROM products")->fetch()['total'];
    $stats['total_stock'] = $pdo->query("SELECT SUM(stock) as total FROM products")->fetch()['total'] ?? 0;
    $stats['total_orders'] = $pdo->query("SELECT COUNT(*) as total FROM orders")->fetch()['total'];
    $stats['total_revenue'] = $pdo->query("SELECT SUM(total_price) as total FROM orders")->fetch()['total'] ?? 0;
    $stats['total_categories'] = $pdo->query("SELECT COUNT(*) as total FROM categories")->fetch()['total'] ?? 0;
    $stats['total_users'] = $pdo->query("SELECT COUNT(*) as total FROM users")->fetch()['total'] ?? 0;

} catch (PDOException $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    $error = "Database error occurred";
}
include __DIR__ . '/../includes/header.php';
?>

<style>
    .admin-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2.5rem;
    }

    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.5rem 1.5rem 1.25rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04);
        text-align: center;
        text-decoration: none;
        transition: transform 0.25s, box-shadow 0.25s;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
    }

    .stat-card:nth-child(1)::before {
        background: linear-gradient(90deg, #2563eb, #3b82f6);
    }

    .stat-card:nth-child(2)::before {
        background: linear-gradient(90deg, #059669, #10b981);
    }

    .stat-card:nth-child(3)::before {
        background: linear-gradient(90deg, #7c3aed, #8b5cf6);
    }

    .stat-card:nth-child(4)::before {
        background: linear-gradient(90deg, #d97706, #f59e0b);
    }

    a.stat-card {
        cursor: pointer;
        padding-bottom: 2.25rem;
    }

    a.stat-card::after {
        content: '↗';
        position: absolute;
        bottom: 0.65rem;
        right: 0.75rem;
        font-size: 1rem;
        font-weight: 700;
        opacity: 0.65;
        transition: opacity 0.25s, transform 0.25s;
        color: var(--text-light);
    }

    a.stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }

    a.stat-card:hover::after {
        opacity: 1;
        transform: translateX(3px) translateY(-2px);
    }

    .stat-value {
        font-size: 2.25rem;
        font-weight: 800;
        margin-bottom: 0.25rem;
        font-family: 'Outfit', sans-serif;
        line-height: 1.2;
        color: var(--text-dark);
    }

    .stat-label {
        font-size: 0.85rem;
        color: var(--text-light);
        font-weight: 500;
    }

    .admin-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .admin-header h2 {
        color: var(--secondary-color);
        font-size: 2rem;
        font-family: 'Outfit', sans-serif;
        margin: 0;
    }

    .btn-add {
        background-color: var(--primary-color);
        color: white;
        padding: 0.7rem 1.5rem;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.9rem;
        transition: background-color 0.25s, transform 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .btn-add:hover {
        background-color: var(--primary-dark);
        transform: translateY(-1px);
    }

    .header-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        align-self: flex-start;
        width: 100%;
        align-items: center;
    }

    .btn-primary-action {
        padding: 0.8rem 1.8rem;
        font-size: 0.95rem;
        font-weight: 800;
        border-radius: 14px;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }

    .btn-primary-action:hover {
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        transform: translateY(-2px);
    }

    .btn-primary-action.btn-reports {
        background: linear-gradient(135deg, #7c3aed, #6d28d9);
    }

    .btn-primary-action.btn-reports:hover {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    }

    .btn-primary-action.btn-new-product {
        background: linear-gradient(135deg, #059669, #047857);
    }

    .btn-primary-action.btn-new-product:hover {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .search-box {
        display: flex;
        gap: 0.5rem;
        margin-left: auto;
    }

    .search-box input {
        padding: 0.65rem 1.1rem;
        border-radius: 12px;
        border: 1.5px solid var(--border-color);
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

    .search-box button {
        padding: 0.65rem 1.2rem;
        border-radius: 12px;
        border: none;
        background: var(--primary-color);
        color: white;
        font-weight: 700;
        cursor: pointer;
        transition: background-color 0.25s, transform 0.2s;
    }

    .search-box button:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
    }

    .search-box .clear-btn {
        padding: 0.65rem 1.1rem;
        border-radius: 12px;
        border: 1.5px solid var(--border-color);
        color: var(--text-light);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
        transition: background-color 0.2s;
    }

    .search-box .clear-btn:hover {
        background: var(--border-color);
    }

    @media (max-width: 768px) {
        .admin-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .admin-header h2 {
            font-size: 1.5rem;
        }

        .stat-value {
            font-size: 1.75rem;
        }
    }

    @media (max-width: 480px) {
        .container {
            padding-bottom: 0.5rem;
        }

        .admin-container {
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .stat-card {
            padding: 0.6rem;
            border-radius: 14px;
        }

        .stat-value {
            font-size: 1rem;
            margin-bottom: 0.15rem;
        }

        .stat-label {
            font-size: 0.6rem;
        }

        .admin-header h2 {
            font-size: 1.1rem;
        }

        .admin-header {
            margin-bottom: 0.5rem;
        }

        .btn-add {
            padding: 0.4rem 0.7rem;
            font-size: 0.75rem;
            border-radius: 8px;
        }

        .btn-primary-action {
            padding: 0.5rem 1rem;
            font-size: 0.78rem;
            border-radius: 10px;
        }

        .search-box input {
            font-size: 0.75rem;
            padding: 0.45rem 0.7rem;
            min-width: 140px;
        }

        .search-box button {
            padding: 0.45rem 0.8rem;
            font-size: 0.75rem;
        }

        .search-box .clear-btn {
            padding: 0.45rem 0.7rem;
            font-size: 0.75rem;
        }
    }

</style>

<div class="container" style="width: min(1300px, calc(100% - 32px)); padding-top: 4rem;">
    <!-- Admin Header -->
    <div class="admin-header" style="flex-direction:column;align-items:center;gap:1rem;">
        <h2 style="font-size:2.25rem; font-weight:700; font-family:'Outfit',sans-serif; margin:0; letter-spacing:-0.5px; background:linear-gradient(135deg, var(--text-dark) 0%, var(--primary-color) 100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; filter:drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
            Admin Dashboard
        </h2>
        <span style="display:block; width:60px; height:4px; background:linear-gradient(90deg, var(--primary-color), transparent); border-radius:2px; margin:-0.25rem auto 0;"></span>
        <div class="header-actions">
            <a href="reports.php" class="btn-add btn-primary-action btn-reports">Reports</a>
            <a href="add_product.php" class="btn-add btn-primary-action btn-new-product">+ New Product</a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="admin-container">
        <a href="manage_products.php" class="stat-card">
            <div class="stat-value"><?php echo $stats['total_products']; ?></div>
            <div class="stat-label">Total Products</div>
        </a>
        <a href="manage_categories.php" class="stat-card">
            <div class="stat-value"><?php echo $stats['total_categories']; ?></div>
            <div class="stat-label">Categories</div>
        </a>
        <a href="manage_orders.php" class="stat-card">
            <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
            <div class="stat-label">Total Orders</div>
        </a>
        <a href="manage_users.php" class="stat-card">
            <div class="stat-value"><?php echo $stats['total_users']; ?></div>
            <div class="stat-label">Users</div>
        </a>
    </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>