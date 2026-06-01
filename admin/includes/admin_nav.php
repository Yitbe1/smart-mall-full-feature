<style>
    .admin-topbar {
        background: var(--surface);
        border-bottom: 1px solid var(--border-color);
        padding: 0 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    .admin-topbar::-webkit-scrollbar {
        display: none;
    }

    .admin-topbar a {
        padding: 1rem 1.25rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-light);
        text-decoration: none;
        white-space: nowrap;
        border-bottom: 2px solid transparent;
        transition: all 0.2s;
        flex-shrink: 0;
    }

    .admin-topbar a:hover {
        color: var(--primary-color);
        background: var(--primary-light);
    }

    .admin-topbar a.active {
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
    }

    @media (max-width: 480px) {
        .admin-topbar {
            padding: 0 0.75rem;
            gap: 0;
        }

        .admin-topbar a {
            padding: 0.75rem 0.85rem;
            font-size: 0.75rem;
        }
    }
</style>

<nav class="admin-topbar">
    <a href="dashboard.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'dashboard.php') !== false ? 'active' : ''; ?>">Dashboard</a>
    <a href="reports.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'reports.php') !== false ? 'active' : ''; ?>">Reports</a>
    <a href="manage_categories.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'manage_categories.php') !== false ? 'active' : ''; ?>">Categories</a>
    <a href="manage_orders.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'manage_orders.php') !== false ? 'active' : ''; ?>">Orders</a>
    <a href="manage_users.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'manage_users.php') !== false ? 'active' : ''; ?>">Users</a>
    <a href="add_product.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'add_product.php') !== false ? 'active' : ''; ?>">Add Product</a>
    <a href="../index.php" style="margin-left: auto; color: var(--text-light);">View Store</a>
</nav>
