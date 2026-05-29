<?php
require_once __DIR__ . '/../config.php';
// Admin Dashboard
$page_title = 'Admin Dashboard - Smart Mall';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$products = [];
$stats = [];

try {
    $pdo = getDB();

    // Get all products with categories
    $stmt = $pdo->query("
        SELECT p.product_id, p.name, p.price, p.stock, p.created_at, p.image, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        ORDER BY p.created_at DESC
    ");
    $products = $stmt->fetchAll();

    // Get statistics
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $stats['total_products'] = $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT SUM(stock) as total FROM products");
    $stats['total_stock'] = $stmt->fetch()['total'] ?? 0;

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $stats['total_orders'] = $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT SUM(total_price) as total FROM orders");
    $stats['total_revenue'] = $stmt->fetch()['total'] ?? 0;

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM categories");
    $stats['total_categories'] = $stmt->fetch()['total'] ?? 0;
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
        background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
        color: white;
        padding: 1.75rem 1.5rem;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        text-align: center;
        text-decoration: none;
        transition: transform 0.25s, box-shadow 0.25s;
        position: relative;
        overflow: hidden;
    }

    .stat-card::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.04);
        border-radius: 50%;
        pointer-events: none;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .stat-value {
        font-size: 2.25rem;
        font-weight: 800;
        margin-bottom: 0.25rem;
        font-family: 'Outfit', sans-serif;
        line-height: 1.2;
    }

    .stat-label {
        font-size: 0.85rem;
        opacity: 0.85;
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

    .products-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: var(--surface);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        border: 1px solid var(--border-color);
    }

    .products-table thead {
        background-color: var(--primary-color);
        color: white;
    }

    .products-table th {
        padding: 1rem 1rem;
        text-align: left;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.06em;
        color: rgba(255, 255, 255, 0.9);
    }

    .products-table th:first-child {
        width: 72px;
    }

    .products-table th:nth-child(3) {
        width: 140px;
    }

    .products-table th:nth-child(4),
    .products-table th:nth-child(5) {
        width: 100px;
    }

    .products-table th:last-child {
        width: 170px;
    }

    .products-table td {
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-dark);
        font-size: 0.9rem;
    }

    .products-table tbody tr:last-child td {
        border-bottom: none;
    }

    .products-table tbody tr:nth-child(even) {
        background: rgba(0, 0, 0, 0.015);
    }

    .products-table tbody tr:hover {
        background: var(--primary-light);
    }

    .action-buttons {
        display: flex;
        gap: 0.4rem;
    }

    .btn-edit,
    .btn-delete {
        padding: 0.45rem 1rem;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.82rem;
        transition: all 0.2s;
    }

    .btn-edit {
        background: var(--primary-color);
        color: white !important;
    }

    .btn-edit:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
    }

    .btn-delete {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger-color);
    }

    .btn-delete:hover {
        background: var(--danger-color);
        color: white;
        transform: translateY(-1px);
    }

    .products-section-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--secondary-color);
        margin-bottom: 1rem;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: var(--text-light);
    }

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 16px;
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

        .products-table {
            font-size: 0.85rem;
            min-width: 560px;
        }

        .products-table th,
        .products-table td {
            padding: 0.65rem 0.75rem;
        }

        .action-buttons {
            flex-direction: column;
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

        .table-wrapper {
            overflow: visible;
            border: none;
        }

        .products-table,
        .products-table thead,
        .products-table tbody,
        .products-table tr,
        .products-table th,
        .products-table td {
            display: block;
        }

        .products-table thead {
            display: none;
        }

        .products-table {
            min-width: 0;
            border-radius: 0;
            border: none;
            box-shadow: none;
        }

        .products-table tr {
            background: var(--surface);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            box-shadow: var(--shadow-sm);
        }

        .products-table td {
            padding: 0.25rem 0;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.78rem;
        }

        .products-table td::before {
            content: attr(data-label);
            font-weight: 700;
            font-size: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--text-light);
            min-width: 4.5rem;
            flex-shrink: 0;
        }

        .products-table td:first-child {
            padding-top: 0;
        }

        .products-table td:last-child {
            padding-bottom: 0;
        }

        .products-table td:not(:last-child) {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0.3rem;
            margin-bottom: 0.3rem;
        }

        .action-buttons {
            flex-direction: row;
            gap: 0.4rem;
            width: 100%;
        }

        .btn-edit,
        .btn-delete {
            font-size: 0.72rem;
            padding: 0.3rem 0.6rem;
            flex: 1;
            text-align: center;
        }

        .empty-state {
            padding: 1rem;
        }

        .products-section-title {
            font-size: 1.1rem;
        }
    }
</style>

<div class="container" style="width: min(1300px, calc(100% - 32px));">
    <!-- Admin Header -->
    <div class="admin-header">
        <h2>Admin Dashboard</h2>
        <a href="add_product.php" class="btn-add">+ New Product</a>
    </div>

    <!-- Statistics -->
    <div class="admin-container">
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['total_products']; ?></div>
            <div class="stat-label">Total Products</div>
        </div>
        <a href="manage_categories.php" class="stat-card" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
            <div class="stat-value"><?php echo $stats['total_categories']; ?></div>
            <div class="stat-label">Categories</div>
        </a>
        <a href="manage_orders.php" class="stat-card">
            <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
            <div class="stat-label">Total Orders</div>
        </a>
        <div class="stat-card" style="background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);">
            <div class="stat-value"><?php echo smartmall_format_money($stats['total_revenue']); ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>

    <!-- Products Table -->
    <h3 class="products-section-title">Products Management</h3>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']);
                                            unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']);
                                        unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (count($products) > 0): ?>
        <div class="table-wrapper">
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td data-label="Image">
                                <div style="width: 48px; height: 48px; border-radius: 10px; overflow: hidden; background: var(--bg-light); border: 1px solid var(--border-color); flex-shrink: 0;">
                                    <?php if (!empty($product['image'])): ?>
                                        <img loading="lazy" src="<?php echo htmlspecialchars(get_product_image_url($product['image'])); ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <div style="display: flex; align-items: center; justify-content: center; height: 100%; font-size: 0.65rem; color: #999;">No img</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td data-label="Name"><strong><?php echo htmlspecialchars($product['name']); ?></strong></td>
                            <td data-label="Category"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                            <td data-label="Price"><?php echo smartmall_format_money($product['price']); ?></td>
                            <td data-label="Stock"><?php echo $product['stock']; ?></td>
                            <td data-label="Actions">
                                <div class="action-buttons">
                                    <a href="add_product.php?product_id=<?php echo $product['product_id']; ?>" class="btn-edit">Edit</a>
                                    <form method="POST" action="delete_product.php" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                        <?php csrf_field(); ?>
                                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                        <button type="submit" class="btn-delete">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h3>No Products Yet</h3>
            <p>Start by adding your first product</p>
            <a href="add_product.php" class="btn-add" style="display: inline-block; margin-top: 1rem;">Add Product</a>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>