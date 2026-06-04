<?php
$page_title = 'Manage Products - Smart Mall';
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$products = [];
$error = '';
$success = '';
$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

try {
    $pdo = getDB();

    $where = '';
    $params = [];
    if ($search !== '') {
        $where = 'WHERE p.name LIKE :search OR c.name LIKE :search2';
        $params[':search'] = "%$search%";
        $params[':search2'] = "%$search%";
    }

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.category_id $where");
    $countStmt->execute($params);
    $totalProducts = (int)$countStmt->fetchColumn();
    $totalPages = max(1, (int)ceil($totalProducts / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;

    $stmt = $pdo->prepare("
        SELECT p.product_id, p.name, p.price, p.stock, p.created_at, p.image, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        $where
        ORDER BY p.created_at DESC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Admin manage_products error: " . $e->getMessage());
    $error = "Database error occurred";
}
include __DIR__ . '/../includes/header.php';
?>

<style>
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
        vertical-align: middle;
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

    .search-box {
        display: flex;
        gap: 0.5rem;
    }

    .search-box input {
        padding: 0.65rem 1.1rem;
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

    .btn-add {
        background: linear-gradient(135deg, #059669, #047857);
        color: white;
        padding: 0.8rem 1.8rem;
        border: none;
        border-radius: 14px;
        cursor: pointer;
        text-decoration: none;
        font-weight: 800;
        font-size: 0.95rem;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3);
        transition: box-shadow 0.25s, transform 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .btn-add:hover {
        background: linear-gradient(135deg, #10b981, #059669);
        box-shadow: 0 6px 20px rgba(5, 150, 105, 0.4);
        transform: translateY(-2px);
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

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 16px;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 0.35rem;
        margin-top: 1.5rem;
        flex-wrap: wrap;
    }

    .page-link {
        padding: 0.5rem 0.9rem;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        color: var(--text-dark);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.2s;
    }

    .page-link:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
        background: var(--primary-light);
    }

    .page-link.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .header-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        align-items: center;
    }

    @media (max-width: 768px) {
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

        .products-table {
            min-width: unset;
            border-collapse: separate;
            border-spacing: 0;
            box-shadow: none;
            background: transparent;
            border: none;
        }

        .products-table thead {
            display: none;
        }

        .products-table tbody tr {
            display: block;
            background: var(--surface);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            box-shadow: var(--shadow-sm);
        }

        .products-table tbody tr td {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.25rem 0 !important;
            border: none;
            font-size: 0.78rem;
        }

        .products-table tbody tr td::before {
            content: attr(data-label);
            font-weight: 700;
            font-size: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--text-light);
            min-width: 4.5rem;
            flex-shrink: 0;
        }

        .products-table tbody tr td:first-child {
            padding-top: 0 !important;
        }

        .products-table tbody tr td:last-child {
            padding-bottom: 0 !important;
        }

        .products-table tbody tr td:not(:last-child) {
            border-bottom: 1px solid var(--border-color) !important;
            padding-bottom: 0.3rem !important;
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

        .page-link {
            padding: 0.35rem 0.6rem;
            font-size: 0.75rem;
        }
    }
</style>

<div class="container" style="width: min(1300px, calc(100% - 32px)); padding-top: 4rem;">
    <div style="text-align:center; padding-bottom:1.5rem;">
        <h2 style="font-size:2.25rem; font-weight:700; font-family:'Outfit',sans-serif; margin:0; letter-spacing:-0.5px; background:linear-gradient(135deg, var(--text-dark) 0%, var(--primary-color) 100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; filter:drop-shadow(0 2px 4px rgba(0,0,0,0.1)); position:relative;">
            Manage Products
        </h2>
        <span style="display:block; width:80px; height:4px; background:linear-gradient(90deg, var(--primary-color), transparent); border-radius:2px; margin:0.5rem auto 0;"></span>
    </div>
    <div style="display:flex; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:2rem;">
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
        <div style="margin-left:auto;" class="header-actions">
            <a href="add_product.php" class="btn-add">+ New Product</a>
            <form method="GET" class="search-box">
                <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
                <?php if ($search !== ''): ?>
                    <a href="manage_products.php" class="clear-btn">Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
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

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" class="page-link">Previous</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="page-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" class="page-link">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div style="text-align: center; padding: 4rem; background: var(--surface); border-radius: var(--radius); box-shadow: var(--shadow-md); border: 1px solid var(--border-color);">
            <h3 style="color: var(--text-dark);">No Products Yet</h3>
            <p style="color: var(--text-light);">Start by adding your first product</p>
            <a href="add_product.php" style="display:inline-block; background:var(--primary-color); color:white; padding:0.7rem 1.5rem; border-radius:12px; text-decoration:none; font-weight:700; margin-top:1rem;">Add Product</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
