<?php
require_once __DIR__ . '/../config.php';
$page_title = 'Admin Guide - Smart Mall';
include __DIR__ . '/../includes/header.php';
?>
<style>
    .docs-layout { display: grid; grid-template-columns: 220px 1fr; gap: 3rem; align-items: start; }
    .docs-sidebar { position: sticky; top: 6rem; }
    .docs-sidebar h3 { font-family: 'Outfit', sans-serif; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-light); margin-bottom: 1rem; }
    .docs-sidebar nav { display: flex; flex-direction: column; gap: 0.4rem; }
    .docs-sidebar a { font-size: 0.9rem; font-weight: 600; color: var(--text-light); padding: 0.4rem 0.75rem; border-radius: 8px; transition: all 0.2s; text-decoration: none; }
    .docs-sidebar a:hover, .docs-sidebar a.is-active { background: var(--primary-light); color: var(--primary-color); }
    .docs-content { max-width: 800px; }
    .docs-content h1 { font-family: 'Outfit', sans-serif; font-size: 2.5rem; font-weight: 800; letter-spacing: -0.02em; color: var(--secondary-color); margin-bottom: 2rem; }
    .docs-content h2 { font-family: 'Outfit', sans-serif; font-size: 1.6rem; font-weight: 700; color: var(--secondary-color); margin: 3rem 0 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--border-color); }
    .docs-content h3 { font-family: 'Outfit', sans-serif; font-size: 1.2rem; font-weight: 700; color: var(--secondary-color); margin: 2rem 0 0.75rem; }
    .docs-content p { color: var(--text-light); line-height: 1.8; margin-bottom: 1rem; }
    .docs-content ul, .docs-content ol { margin: 0.5rem 0 1.5rem 1.5rem; color: var(--text-light); line-height: 1.8; }
    .docs-content li { margin-bottom: 0.3rem; }
    .docs-content strong { color: var(--text-dark); }
    .docs-content .note { background: var(--primary-light); border-left: 4px solid var(--primary-color); padding: 1rem 1.5rem; border-radius: 0 12px 12px 0; margin: 1.5rem 0; }
    .docs-content .note p { margin-bottom: 0; }
    .docs-content .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 1rem 1.5rem; border-radius: 0 12px 12px 0; margin: 1.5rem 0; }
    .docs-content .warning p { margin-bottom: 0; color: #92400e; }
    .docs-step { display: flex; gap: 1rem; align-items: flex-start; margin-bottom: 0.5rem; }
    .docs-step-num { width: 28px; height: 28px; background: var(--primary-color); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 800; flex-shrink: 0; margin-top: 0.2rem; }
    .docs-step p { margin-bottom: 0; }
    .back-link { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.9rem; font-weight: 600; color: var(--primary-color); text-decoration: none; margin-bottom: 2rem; }
    .back-link:hover { text-decoration: underline; }
    @media (max-width: 768px) { .docs-layout { grid-template-columns: 1fr; } .docs-sidebar { display: none; } }
</style>

<div class="container">
    <div class="docs-layout">
        <aside class="docs-sidebar">
            <h3>Admin Guide</h3>
            <nav>
                <a href="#dashboard">Dashboard</a>
                <a href="#products">Products</a>
                <a href="#categories">Categories</a>
                <a href="#orders">Orders</a>
                <a href="#payments">Payments</a>
                <a href="#cleanup">System Cleanup</a>
            </nav>
        </aside>
        <div class="docs-content">
            <a href="index.php" class="back-link">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5m0 0l6 6m-6-6l6-6"/></svg>
                Back to Documentation
            </a>

            <h1>Admin Guide</h1>

            <h2 id="dashboard">1. Admin Dashboard</h2>
            <p>The admin dashboard provides a centralized overview of your store:</p>
            <ul>
                <li>Total products count and low-stock alerts.</li>
                <li>Total orders and revenue statistics.</li>
                <li>Number of registered customers and categories.</li>
                <li>Quick-action buttons for managing products, categories, and orders.</li>
                <li>Product management table with edit/delete actions.</li>
            </ul>
            <p><strong>Access:</strong> Admins see an <strong>Admin Panel</strong> link in the account dropdown menu. Admin pages check for the <code>admin</code> user role directly.</p>

            <h2 id="products">2. Product Management</h2>

            <h3>2.1 Viewing Products</h3>
            <p>The product management page lists all products with ID, name, category, price (USD), stock quantity, and edit/delete action buttons.</p>

            <h3>2.2 Adding a Product</h3>
            <div class="docs-step"><span class="docs-step-num">1</span><p>Click <strong>Add New Product</strong> on the products page.</p></div>
            <div class="docs-step"><span class="docs-step-num">2</span><p>Fill in product details: name, description, category, price, stock.</p></div>
            <div class="docs-step"><span class="docs-step-num">3</span><p>Upload product images (supports multiple image uploads).</p></div>
            <div class="docs-step"><span class="docs-step-num">4</span><p>Click <strong>Save</strong> to add the product.</p></div>

            <h3>2.3 Editing a Product</h3>
            <div class="docs-step"><span class="docs-step-num">1</span><p>Click <strong>Edit</strong> next to the product.</p></div>
            <div class="docs-step"><span class="docs-step-num">2</span><p>Update any fields as needed.</p></div>
            <div class="docs-step"><span class="docs-step-num">3</span><p>Replace or add new images if required.</p></div>
            <div class="docs-step"><span class="docs-step-num">4</span><p>Click <strong>Save</strong> to apply changes.</p></div>

            <h3>2.4 Deleting a Product</h3>
            <p>Click <strong>Delete</strong> next to a product to remove it. This action:</p>
            <ul>
                <li>Removes the product from the database.</li>
                <li>Deletes associated product images from the server.</li>
                <li>Cannot be undone.</li>
            </ul>
            <div class="warning"><p><strong>Warning:</strong> Product deletion is permanent and cannot be reversed.</p></div>

            <h2 id="categories">3. Category Management</h2>
            <p>Manage product categories to organize your store:</p>
            <ul>
                <li>View all categories with their associated slide images.</li>
                <li>Add new categories with a name and up to 3 promotional slide images.</li>
                <li>Edit existing categories and update images.</li>
                <li>Delete categories (ensure no products are assigned first).</li>
            </ul>

            <h2 id="orders">4. Order Management</h2>
            <p>The order management page provides full control over customer orders:</p>
            <ul>
                <li>View all orders sorted by status (paid orders first).</li>
                <li>Order details include: ID, customer name, items, total, status, date.</li>
                <li>Update order status through the workflow: <strong>Pending</strong> &rarr; <strong>Paid</strong> &rarr; <strong>Shipped</strong> &rarr; <strong>Delivered</strong>.</li>
                <li>Orders can be marked as <strong>Cancelled</strong> when needed.</li>
            </ul>

            <h2 id="payments">5. Payment</h2>
            <p>Smart Mall integrates with <strong>Chapa</strong> for online payments. Chapa configuration is in <code>chapa_pay/chapa-config.php</code>.</p>
            <ul>
                <li>Orders placed with Chapa redirect users to the Chapa-hosted payment page.</li>
                <li>On return, <code>order_confirmation.php</code> verifies the transaction via the Chapa API.</li>
                <li><strong>Cash on Delivery</strong> is available as an alternative payment method.</li>
                <li>Payment and order status can be updated directly from the <strong>Manage Orders</strong> page.</li>
            </ul>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
