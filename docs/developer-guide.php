<?php
require_once __DIR__ . '/../config.php';
$page_title = 'Developer Guide - Smart Mall';
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
    .docs-content h4 { font-family: 'Outfit', sans-serif; font-size: 1rem; font-weight: 700; color: var(--secondary-color); margin: 1.5rem 0 0.5rem; }
    .docs-content p { color: var(--text-light); line-height: 1.8; margin-bottom: 1rem; }
    .docs-content ul, .docs-content ol { margin: 0.5rem 0 1.5rem 1.5rem; color: var(--text-light); line-height: 1.8; }
    .docs-content li { margin-bottom: 0.3rem; }
    .docs-content strong { color: var(--text-dark); }
    .docs-content code { background: var(--bg-light); padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; color: #e11d48; }
    .docs-content pre { background: #1e293b; color: #e2e8f0; padding: 1.25rem; border-radius: 12px; overflow-x: auto; font-size: 0.85rem; line-height: 1.7; margin: 1rem 0; }
    .docs-content pre code { background: none; color: inherit; padding: 0; font-size: inherit; }
    .docs-content table { width: 100%; border-collapse: collapse; margin: 1rem 0 1.5rem; font-size: 0.9rem; }
    .docs-content th, .docs-content td { padding: 0.6rem 0.75rem; text-align: left; border-bottom: 1px solid var(--border-color); }
    .docs-content th { background: var(--bg-light); font-weight: 700; color: var(--secondary-color); font-family: 'Outfit', sans-serif; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; }
    .docs-content .note { background: var(--primary-light); border-left: 4px solid var(--primary-color); padding: 1rem 1.5rem; border-radius: 0 12px 12px 0; margin: 1.5rem 0; }
    .docs-content .note p { margin-bottom: 0; }
    .docs-content .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 1rem 1.5rem; border-radius: 0 12px 12px 0; margin: 1.5rem 0; }
    .docs-content .warning p { margin-bottom: 0; color: #92400e; }
    .docs-content .tip { background: #f0fdf4; border-left: 4px solid #22c55e; padding: 1rem 1.5rem; border-radius: 0 12px 12px 0; margin: 1.5rem 0; }
    .docs-content .tip p { margin-bottom: 0; color: #166534; }
    .docs-step { display: flex; gap: 1rem; align-items: flex-start; margin-bottom: 0.5rem; }
    .docs-step-num { width: 28px; height: 28px; background: var(--primary-color); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 800; flex-shrink: 0; margin-top: 0.2rem; }
    .docs-step p { margin-bottom: 0; }
    .back-link { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.9rem; font-weight: 600; color: var(--primary-color); text-decoration: none; margin-bottom: 2rem; }
    .back-link:hover { text-decoration: underline; }
    .file-badge { display: inline-block; background: #1e293b; color: #e2e8f0; padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.8rem; font-family: monospace; margin: 0.2rem; }
    .tag { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; margin: 0.15rem; }
    .tag-php { background: #eff6ff; color: #2563eb; }
    .tag-js { background: #fefce8; color: #ca8a04; }
    .tag-sql { background: #f0fdf4; color: #16a34a; }
    .tag-json { background: #f5f3ff; color: #7c3aed; }
    .tag-css { background: #fef2f2; color: #dc2626; }
    .tag-dart { background: #ecfdf5; color: #059669; }
    @media (max-width: 768px) { .docs-layout { grid-template-columns: 1fr; } .docs-sidebar { display: none; } }
</style>

<div class="container">
    <div class="docs-layout">
        <aside class="docs-sidebar">
            <h3>Developer Guide</h3>
            <nav>
                <a href="#architecture">Architecture</a>
                <a href="#setup">Setup</a>
                <a href="#database">Database Schema</a>
                <a href="#files">File Reference</a>
                <a href="#core">Core System</a>
                <a href="#auth">Authentication</a>
                <a href="#api">API Reference</a>
                <a href="#payment">Payment</a>
                <a href="#currency">Multi-Currency</a>
                <a href="#theme">Theme &amp; PWA</a>
                <a href="#mobile">Mobile App</a>
                <a href="#security">Security</a>
                <a href="#deployment">Deployment</a>
            </nav>
        </aside>
        <div class="docs-content">
            <a href="index.php" class="back-link">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5m0 0l6 6m-6-6l6-6"/></svg>
                Back to Documentation
            </a>

            <h1>Developer Guide</h1>

            <h2 id="architecture">1. Architecture Overview</h2>
            <p>Smart Mall is a <strong>PHP 8+</strong> e-commerce application following a straightforward <strong>page-based architecture</strong> (not MVC framework). Each PHP file handles its own presentation logic, database queries, and form processing. The application uses:</p>
            <ul>
                <li><strong>MySQL/MariaDB</strong> via PDO with prepared statements</li>
                <li><strong>Session-based authentication</strong> with CSRF protection</li>
                <li><strong>Progressive Web App (PWA)</strong> capabilities via service worker + manifest</li>
                <li><strong>Chapa payment gateway</strong> for Ethiopian Birr (ETB) transactions</li>
                <li><strong>Capacitor/Flutter</strong> wrapper for mobile app distribution</li>
            </ul>

            <h3>Directory Structure</h3>
            <pre><code>reference/
├── index.php              # Homepage (product grid, categories, search)
├── config.php             # App-level config (base URL, auto-include db+currency)
├── checkout.php           # Checkout flow + payment processing
├── cart.php               # Shopping cart management
├── add_to_cart.php        # AJAX add-to-cart endpoint
├── product.php            # Single product detail page
├── orders.php             # User order history
├── order_confirmation.php # Post-payment verification + confirmation
├── login.php              # User login
├── register.php           # User registration
├── logout.php             # Session destroy
├── contact.php            # Contact form
├── about.php              # About page with live stats
├── download.php           # Mobile app download page
├── protect.php            # Standalone password gate
├── set_currency.php       # Currency switch handler
├── check_schema.php       # Quick DB schema inspection
├── manifest.json          # PWA manifest
├── includes/
│   ├── header.php         # Shared HTML header + navbar + theme
│   ├── footer.php         # Shared footer + newsletter
│   ├── db.php             # PDO connection + CSRF helpers
│   └── currency.php       # Multi-currency + exchange rate system
├── admin/
│   ├── dashboard.php      # Admin overview + product table
│   ├── add_product.php    # Add/Edit product form
│   ├── delete_product.php # Delete product handler
│   ├── manage_categories.php # CRUD categories
│   └── manage_orders.php  # Order status management
├── api/
│   └── search.php         # JSON search endpoint
├── chapa_pay/
│   └── chapa-config.php   # Chapa API key + URL
├── docs/                  # Documentation
├── uploads/               # Product + category images
├── assets/                # Static assets (CSS, JS, images)
├── smartmall-app/         # Capacitor mobile wrapper
│   ├── capacitor.config.json
│   ├── www/index.html
│   └── www/app.js
└── INSTALLATION_GUIDE.md  # Full setup instructions</code></pre>

            <h2 id="setup">2. Installation &amp; Setup</h2>

            <h3>2.1 Requirements</h3>
            <ul>
                <li>PHP 8.0+ with PDO and MySQL extensions</li>
                <li>MySQL 5.7+ or MariaDB 10.3+</li>
                <li>Apache with mod_rewrite (or nginx)</li>
                <li>cURL extension (for Chapa API calls and exchange rate fetching)</li>
            </ul>

            <h3>2.2 Quick Start (XAMPP/LAMP)</h3>
            <div class="docs-step"><span class="docs-step-num">1</span><p>Clone the project into your web server's document root.</p></div>
            <div class="docs-step"><span class="docs-step-num">2</span><p>Create the database:</p></div>
            <pre><code>CREATE DATABASE smartmall_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</code></pre>
            <div class="docs-step"><span class="docs-step-num">3</span><p>Create tables using the SQL below (Database Schema section).</p></div>
            <div class="docs-step"><span class="docs-step-num">4</span><p>Seed initial data: categories, an admin user, and sample products.</p></div>
            <div class="docs-step"><span class="docs-step-num">5</span><p>Access the site through your web server.</p></div>

            <div class="note"><p><strong>Database Config:</strong> Connection settings are in <code>includes/db.php</code>. Default: host=<code>localhost</code>, db=<code>smartmall_db</code>, user=<code>root</code>, password=<code>(empty)</code>.</p></div>

            <h3>2.3 Admin Access</h3>
            <p>Admin pages check for the <code>admin</code> user role. To create an admin user, insert directly into the database with a bcrypt-hashed password and role=<code>'admin'</code>. Once logged in as an admin, the <strong>Admin Panel</strong> link appears in the account dropdown menu.</p>

            <h2 id="database">3. Database Schema</h2>
            <p>Database name: <code>smartmall_db</code> (character set: utf8mb4).</p>

            <h3>3.1 Table: <code>users</code></h3>
            <pre><code>CREATE TABLE users (
    user_id    INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('customer','admin') DEFAULT 'customer',
    phone      VARCHAR(20),
    address    TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;</code></pre>
            <p>Passwords are hashed with <code>PASSWORD_BCRYPT</code> via <code>password_hash()</code>. Role determines admin access (<code>admin</code>) vs standard customer.</p>

            <h3>3.2 Table: <code>categories</code></h3>
            <pre><code>CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name    VARCHAR(100) NOT NULL,
    image1  VARCHAR(255) DEFAULT '',
    image2  VARCHAR(255) DEFAULT '',
    image3  VARCHAR(255) DEFAULT '',
    accent  VARCHAR(7) DEFAULT '#007AFF'
) ENGINE=InnoDB;</code></pre>
            <p>Each category supports up to 3 promotional slide images. The <code>accent</code> column stores a hex color for UI theming (e.g., <code>#FF6B6B</code> for Fashion, <code>#4ECDC4</code> for Electronics).</p>

            <h3>3.3 Table: <code>products</code></h3>
            <pre><code>CREATE TABLE products (
    product_id        INT AUTO_INCREMENT PRIMARY KEY,
    category_id       INT,
    name              VARCHAR(200) NOT NULL,
    description       TEXT,
    price             DECIMAL(10,2) NOT NULL,
    image             VARCHAR(255) DEFAULT '',
    additional_images TEXT DEFAULT '[]',
    stock             INT DEFAULT 0,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
) ENGINE=InnoDB;</code></pre>
            <p>All prices are stored in <strong>USD</strong> (base currency). <code>additional_images</code> stores a JSON array of up to 2 extra image filenames for the product gallery. On category delete, products have <code>category_id</code> set to NULL.</p>

            <h3>3.4 Table: <code>cart</code></h3>
            <pre><code>CREATE TABLE cart (
    cart_id    INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    product_id INT NOT NULL,
    quantity   INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id)
) ENGINE=InnoDB;</code></pre>
            <p>Cart is server-side per user. The unique key prevents duplicate product entries — when re-adding, the quantity is updated instead. Quantity is capped at available stock.</p>

            <h3>3.5 Table: <code>orders</code></h3>
            <pre><code>CREATE TABLE orders (
    order_id       INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT NOT NULL,
    total_price    DECIMAL(10,2) NOT NULL,
    status         ENUM('pending','paid','shipped','delivered','cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT 'cod',
    phone          VARCHAR(20),
    address        TEXT,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;</code></pre>
            <p>Order status workflow: <code>pending</code> &rarr; <code>paid</code> &rarr; <code>shipped</code> &rarr; <code>delivered</code> (or <code>cancelled</code> from pending). <code>payment_method</code> is <code>'cod'</code> or <code>'chapa'</code>.</p>

            <h3>3.6 Table: <code>order_items</code></h3>
            <pre><code>CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id   INT NOT NULL,
    product_id INT,
    quantity   INT NOT NULL,
    price      DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE SET NULL
) ENGINE=InnoDB;</code></pre>
            <p>Line items for each order. <code>price</code> is the unit price at time of purchase (snapshot, not live). If a product is later deleted, the order item retains history via <code>ON DELETE SET NULL</code>.</p>

            <h3>3.7 Table: <code>payments</code></h3>
            <pre><code>CREATE TABLE payments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    order_id        INT NOT NULL,
    tx_ref          VARCHAR(100),
    amount          DECIMAL(10,2),
    currency        VARCHAR(3) DEFAULT 'ETB',
    status          ENUM('pending','paid','failed') DEFAULT 'pending',
    chapa_response  TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
) ENGINE=InnoDB;</code></pre>
            <p>Tracks Chapa payment transactions. <code>tx_ref</code> is the Chapa transaction reference (unique per payment). <code>chapa_response</code> stores the full JSON response from Chapa for debugging. <code>status</code> is updated by the callback or verification flow.</p>

            <h3>3.8 Entity Relationships</h3>
            <pre><code>users 1---N cart          (a user has many cart items)
users 1---N orders        (a user places many orders)
orders 1---N order_items  (an order contains many line items)
orders 1---1 payments     (an order has one payment record)
products 1---N cart       (a product can be in many carts)
products 1---N order_items (a product appears in many orders)
categories 1---N products (a category contains many products)</code></pre>

            <h2 id="files">4. File Reference</h2>

            <h3>4.1 Root Pages</h3>
            <table>
                <tr><th>File</th><th>Purpose</th></tr>
                <tr><td><code>index.php</code></td><td>Homepage with product grid, category slides, and featured products. Handles sorting by price/name/newest.</td></tr>
                <tr><td><code>product.php</code></td><td>Single product detail with gallery, description, stock indicator, and add-to-cart action.</td></tr>
                <tr><td><code>cart.php</code></td><td>Full cart view with quantity controls, item removal, subtotal/VAT/total calculation, and checkout button.</td></tr>
                <tr><td><code>checkout.php</code></td><td>Multi-step checkout: address collection, payment method selection (Chapa/COD), order placement with stock verification and transaction.</td></tr>
                <tr><td><code>order_confirmation.php</code></td><td>Post-payment page that verifies Chapa status, handles test mode, shows confirmation with order summary.</td></tr>
                <tr><td><code>orders.php</code></td><td>User order history with status badges, item details, and cancel-pending-order with stock restoration.</td></tr>
                <tr><td><code>login.php</code></td><td>Email/password login with session regeneration, remember-me, and redirect support.</td></tr>
                <tr><td><code>register.php</code></td><td>Registration with strict password policy (min 8 chars, upper+lower+digit+special).</td></tr>
                <tr><td><code>logout.php</code></td><td>Session cleanup: <code>session_unset()</code> + <code>session_destroy()</code> then redirect to home.</td></tr>
                <tr><td><code>contact.php</code></td><td>Contact form with client-side validation and success feedback. Email delivery requires production wiring.</td></tr>
                <tr><td><code>about.php</code></td><td>About page with live DB stats: product count, orders, customers, categories.</td></tr>
                <tr><td><code>protect.php</code></td><td>Standalone password gate (secret: <code>smartmalltest!</code>). Not used by admin pages (they check user role instead).</td></tr>
                <tr><td><code>set_currency.php</code></td><td>Currency switch endpoint. Accepts POST/GET, validates redirect URL (same-origin only), updates session.</td></tr>
                <tr><td><code>add_to_cart.php</code></td><td>AJAX endpoint: validates product + stock, inserts or updates cart entry, returns JSON.</td></tr>
                <tr><td><code>check_schema.php</code></td><td>Quick debug tool: runs <code>DESCRIBE products</code> and returns JSON of column names.</td></tr>
                <tr><td><code>download.php</code></td><td>Mobile app download page with QR code and platform links.</td></tr>
            </table>

            <h3>4.2 Admin Pages</h3>
            <table>
                <tr><th>File</th><th>Purpose</th></tr>
                <tr><td><code>admin/dashboard.php</code></td><td>Admin homepage: stat cards (products, stock, orders, revenue, categories) + product management table with edit/delete actions.</td></tr>
                <tr><td><code>admin/add_product.php</code></td><td>Add/Edit product form. Handles image upload (cover + 2 additional slides), media deletion via AJAX, and gallery preview with drag-to-reorder.</td></tr>
                <tr><td><code>admin/delete_product.php</code></td><td>POST-only handler: deletes product from DB and removes image files from uploads.</td></tr>
                <tr><td><code>admin/manage_categories.php</code></td><td>Full category CRUD with up to 3 slide images per category, image deletion, and inline editing.</td></tr>
                <tr><td><code>admin/manage_orders.php</code></td><td>Order list sorted by payment status (paid first), status update controls, and item detail expansion.</td></tr>
            </table>

            <h3>4.3 Core Includes</h3>
            <table>
                <tr><th>File</th><th>Purpose</th></tr>
                <tr><td><code>includes/db.php</code></td><td>PDO singleton via <code>getDB()</code>. Defines <code>csrf_token()</code> and <code>csrf_verify()</code> helpers. Defines <code>bcrypt_hash()</code> alias.</td></tr>
                <tr><td><code>includes/header.php</code></td><td>HTML head, navbar (with cart count badge), theme toggle (light/dark), currency selector, PWA service worker registration.</td></tr>
                <tr><td><code>includes/footer.php</code></td><td>Footer with newsletter form, admin link, mobile app download, and copyright.</td></tr>
                <tr><td><code>includes/currency.php</code></td><td>Full multi-currency system: exchange rate fetching from <code>exchangerate-api.com</code>, file-based caching, format conversion (USD/ETB).</td></tr>
                <tr><td><code>config.php</code></td><td>Top-level config: auto-detects base URL (<code>BASE_URL</code> constant) from protocol/host/subfolder. Includes db.php and currency.php.</td></tr>
            </table>

            <h3>4.4 API &amp; Payment</h3>
            <table>
                <tr><th>File</th><th>Purpose</th></tr>
                <tr><td><code>api/search.php</code></td><td>JSON search: <code>?q=term</code> returns max 6 matching products with image URL and formatted price.</td></tr>
                <tr><td><code>chapa_pay/chapa-config.php</code></td><td>Defines <code>CHAPA_SECRET_KEY</code> and <code>CHAPA_API_URL</code> constants.</td></tr>
            </table>

            <h2 id="core">5. Core System</h2>

            <h3>5.1 Database Connection (<code>includes/db.php</code>)</h3>
            <p>The <code>getDB()</code> function provides a singleton PDO connection:</p>
            <pre><code>function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $host = 'localhost';
        $db   = 'smartmall_db';
        $user = 'root';
        $pass = '';
        $pdo  = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_SILENT,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}</code></pre>
            <div class="note"><p><strong>Note:</strong> Error mode is <code>SILENT</code>. Queries use manual error checking via <code>try/catch</code> blocks on each page. All queries use prepared statements with named or positional parameters.</p></div>

            <h3>5.2 CSRF Protection</h3>
            <p>Two helper functions in <code>includes/db.php</code> protect against Cross-Site Request Forgery:</p>
            <pre><code>function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        die('Invalid CSRF token');
    }
}</code></pre>
            <p>Usage pattern: generate the token in the form via <code>&lt;?= csrf_token() ?&gt;</code> in a hidden field. Verify on POST with <code>csrf_verify()</code> before any state-changing operation.</p>

            <h3>5.3 Session Auth Flow</h3>
            <p>Authentication uses PHP native sessions. Key session variables after login:</p>
            <ul>
                <li><code>$_SESSION['user_id']</code> &mdash; User's database ID</li>
                <li><code>$_SESSION['user_name']</code> &mdash; Display name</li>
                <li><code>$_SESSION['user_email']</code> &mdash; Email address</li>
                <li><code>$_SESSION['user_role']</code> &mdash; <code>'customer'</code> or <code>'admin'</code></li>
                <li><code>$_SESSION['csrf_token']</code> &mdash; Auto-generated on first request</li>
                <li><code>$_SESSION['currency']</code> &mdash; Selected display currency (<code>'USD'</code> or <code>'ETB'</code>)</li>
            </ul>
            <p>On login, <code>session_regenerate_id(true)</code> prevents session fixation. <code>session_write_close()</code> ensures data is written before redirect. On logout, <code>session_unset()</code> + <code>session_destroy()</code> fully clears the session.</p>

            <h3>5.4 Request Flow Pattern</h3>
            <p>Pages follow a <strong>POST-before-output</strong> pattern. Form processing happens <em>before</em> the header include, allowing <code>header('Location: ...')</code> redirects:</p>
            <pre><code>&lt;?php
// 1. Process POST (before any HTML output)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    // ... handle form, redirect on success
    header('Location: success.php');
    exit();
}

// 2. Include header (HTML output begins)
include 'includes/header.php';
?&gt;
&lt;!-- 3. Page HTML --&gt;</code></pre>

            <h2 id="auth">6. Authentication &amp; Authorization</h2>

            <h3>6.1 Registration (<code>register.php</code>)</h3>
            <p>Password policy enforced server-side:</p>
            <ul>
                <li>Minimum 8 characters</li>
                <li>At least 1 uppercase letter (<code>[A-Z]</code>)</li>
                <li>At least 1 lowercase letter (<code>[a-z]</code>)</li>
                <li>At least 1 digit (<code>[0-9]</code>)</li>
                <li>At least 1 special character (<code>[^A-Za-z0-9]</code>)</li>
            </ul>
            <p>Passwords are hashed with <code>password_hash($password, PASSWORD_BCRYPT)</code>. Default role is <code>'customer'</code>. Duplicate email check prevents re-registration.</p>

            <h3>6.2 Login (<code>login.php</code>)</h3>
            <p>Verifies against <code>users</code> table using <code>password_verify()</code>. On success: regenerates session ID, stores user data in session, supports <code>?redirect=</code> parameter for post-login navigation (sanitized against path traversal via regex).</p>

            <h3>6.3 Admin Authorization</h3>
            <p>Admin pages check the user role at the top of each file:</p>
            <pre><code>if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}</code></pre>
            <p>Accessing admin pages without the <code>admin</code> role results in redirect to homepage.</p>

            <h2 id="api">7. API Reference</h2>

            <h3>7.1 Search API</h3>
            <table>
                <tr><th>Endpoint</th><td><code>api/search.php?q=term</code></td></tr>
                <tr><th>Method</th><td>GET</td></tr>
                <tr><th>Response</th><td>JSON array of product objects</td></tr>
                <tr><th>Max Results</th><td>6</td></tr>
            </table>
            <p><strong>Response format:</strong></p>
            <pre><code>[
    {
        "id": 1,
        "name": "Smartphone X",
        "description": "Latest model...",
        "price": "599.99",
        "image": "smartphone.jpg",
        "image_url": "/uploads/smartphone.jpg",
        "display_price": "$599.99"
    }
]</code></pre>
            <p>Searches against both <code>name</code> and <code>description</code> columns using <code>LIKE %term%</code>. Image URLs are resolved to absolute paths. Prices are formatted with the <code>smartmall_format_money()</code> function.</p>

            <h3>7.2 Add to Cart API</h3>
            <table>
                <tr><th>Endpoint</th><td><code>add_to_cart.php</code></td></tr>
                <tr><th>Method</th><td>POST (JSON body)</td></tr>
                <tr><th>Auth</th><td>Session required</td></tr>
                <tr><th>Response</th><td>JSON</td></tr>
            </table>
            <p><strong>Request body:</strong></p>
            <pre><code>{ "product_id": 1, "quantity": 1 }</code></pre>
            <p><strong>Validation:</strong></p>
            <ul>
                <li>User must be logged in</li>
                <li>Product must exist</li>
                <li>Quantity must not exceed available stock</li>
                <li>If product already in cart, quantity is summed (capped at stock)</li>
            </ul>

            <h3>7.3 Cart Display Helpers (Inline)</h3>
            <p>The header includes inline JavaScript that reads from the DOM for the sticky cart bar. Cart total and count are rendered directly in the footer via PHP queries on each page load (not a separate API endpoint).</p>

            <h2 id="payment">8. Payment Integration (Chapa)</h2>

            <h3>8.1 Configuration</h3>
            <pre><code>// chapa_pay/chapa-config.php
define('CHAPA_SECRET_KEY', 'CHASECK_TEST-...');
define('CHAPA_API_URL', 'https://api.chapa.co/v1');</code></pre>
            <div class="warning"><p><strong>Security:</strong> The API key is hardcoded inline. For production, move to environment variables or a .env file. The test key ends in test mode where payments auto-succeed on callback.</p></div>

            <h3>8.2 Payment Flow</h3>
            <div class="docs-step"><span class="docs-step-num">1</span><p>User checks out with <code>payment_method = 'chapa'</code>. Order is created with <code>status = 'pending'</code>.</p></div>
            <div class="docs-step"><span class="docs-step-num">2</span><p>Checkout script calls Chapa API to initialize payment: sends amount, currency (ETB), tx_ref, callback URL, and customer info.</p></div>
            <div class="docs-step"><span class="docs-step-num">3</span><p>User is redirected to Chapa-hosted payment page. After payment, Chapa redirects back to <code>order_confirmation.php</code>.</p></div>
            <div class="docs-step"><span class="docs-step-num">4</span><p>Confirmation page verifies the transaction by calling <code>GET /transaction/verify/{tx_ref}</code> with the secret key in the Authorization header.</p></div>
            <div class="docs-step"><span class="docs-step-num">5</span><p>If verified successfully (or test mode), payment and order status are updated to <code>'paid'</code>. Stock is decremented.</p></div>
            <div class="docs-step"><span class="docs-step-num">6</span><p>Chapa callback (<code>chapa_pay/callback.php</code>) provides a server-to-server webhook as backup verification.</p></div>

            <h3>8.3 Test Mode</h3>
            <p>When using a Chapa test secret key (starts with <code>CHASECK_TEST</code>), if the verification API does not confirm success but the user returns from the Chapa flow, the system <strong>assumes success</strong> for development purposes. This is noted in the error logs.</p>

            <h3>8.4 Payment Verification Endpoint</h3>
            <pre><code>GET https://api.chapa.co/v1/transaction/verify/{tx_ref}
Headers: Authorization: Bearer CHAPA_SECRET_KEY</code></pre>
            <p>Verification checks both <code>response.status === 'success'</code> and <code>response.data.status === 'success'</code>. The full response is stored in <code>payments.chapa_response</code> for debugging.</p>

            <h2 id="currency">9. Multi-Currency System</h2>

            <h3>9.1 Architecture</h3>
            <p>All product prices are stored in <strong>USD</strong> in the database. The currency system converts for display only. The system is defined in <code>includes/currency.php</code>.</p>

            <h3>9.2 Exchange Rate Fetching</h3>
            <p>Rates are fetched from <code>https://open.er-api.com/v6/latest/USD</code>. The response is cached to a JSON file in the system temp directory (<code>smartmall_exchange_usd.json</code>). Cache respects the API's <code>time_next_update_unix</code> field. If the API is unreachable and a cache exists, stale rates are used with a <code>stale</code> flag.</p>

            <h3>9.3 Key Functions</h3>
            <table>
                <tr><th>Function</th><th>Purpose</th></tr>
                <tr><td><code>smartmall_selected_currency()</code></td><td>Returns current currency from session (<code>'USD'</code> or <code>'ETB'</code>)</td></tr>
                <tr><td><code>smartmall_set_selected_currency($currency)</code></td><td>Sets currency in session</td></tr>
                <tr><td><code>smartmall_exchange_rate($currency)</code></td><td>Returns exchange rate for given currency (1.0 for USD)</td></tr>
                <tr><td><code>smartmall_convert_money($amountUsd, $currency)</code></td><td>Converts USD amount to target currency</td></tr>
                <tr><td><code>smartmall_format_money($amountUsd, $currency)</code></td><td>Formats money: <code>$599.99</code> or <code>ETB 34,500.00</code></td></tr>
                <tr><td><code>smartmall_currency_is_converted()</code></td><td>Returns true if displaying non-USD</td></tr>
            </table>

            <h3>9.4 Currency Switching</h3>
            <p>The <code>set_currency.php</code> endpoint handles currency changes via POST or GET. It validates redirect URLs are same-origin (prevents open redirect) and falls back to the referring page or homepage.</p>

            <h2 id="theme">10. Theme System &amp; PWA</h2>

            <h3>10.1 Light/Dark Mode</h3>
            <p>The theme toggle is in <code>includes/header.php</code>. It uses CSS custom properties and a <code>data-theme</code> attribute on <code>&lt;html&gt;</code>:</p>
            <ul>
                <li>User preference stored in <code>localStorage['theme']</code></li>
                <li>Defaults to system preference via <code>prefers-color-scheme</code></li>
                <li>Toggle button switches between <code>'light'</code> and <code>'dark'</code></li>
                <li>CSS variables define colors, shadows, border radii for each mode</li>
            </ul>

            <h3>10.2 PWA (Progressive Web App)</h3>
            <p><code>manifest.json</code> enables "Add to Home Screen" on mobile devices:</p>
            <ul>
                <li><code>display: standalone</code> &mdash; App runs without browser chrome</li>
                <li>Icon at <code>/assets/logo-icon.png</code> (512x512)</li>
                <li>Theme color: <code>#007AFF</code></li>
            </ul>

            <h3>10.3 Sticky Cart Bar</h3>
            <p>The footer includes a fixed-position cart status bar. It queries <code>cart</code> table for the logged-in user's item count and total, rendered as a sticky bottom bar with a link to the cart page. Auto-hides if cart is empty.</p>

            <h3>10.4 Image Management</h3>
            <p>Product images are uploaded to <code>/uploads/</code>. File naming uses <code>uniqid()</code> to prevent collisions. Supported formats: JPG, PNG, GIF, WebP. The upload system handles:</p>
            <ul>
                <li>Cover image (single, required)</li>
                <li>Additional slide images (2 extra, optional, stored as JSON array in <code>additional_images</code>)</li>
                <li>AJAX media deletion per image type (cover, slide2, slide3)</li>
                <li>Category slide images (3 per category, named <code>cat_xxxxx.ext</code>)</li>
            </ul>

            <h2 id="mobile">11. Mobile App (Capacitor)</h2>

            <h3>11.1 Architecture</h3>
            <p>The mobile app at <code>smartmall-app/</code> is a <strong>Capacitor</strong> web wrapper that loads the Smart Mall website in a native WebView. Configuration:</p>

            <pre><code>// capacitor.config.json
{
    "appId": "com.smartmall.app",
    "appName": "Smart Mall",
    "webDir": "www",
    "server": {
        "url": "https://your-domain.com",
        "allowNavigation": ["*.chapa.co"]
    }
}</code></pre>

            <h3>11.2 Key Features</h3>
            <ul>
                <li><strong>Back button handling:</strong> <code>window.history.back()</code> if possible, otherwise <code>App.exitApp()</code></li>
                <li><strong>Deep linking:</strong> Listens for <code>appUrlOpen</code> to handle Chapa payment redirects</li>
                <li><strong>Splash screen:</strong> Hidden after app loads</li>
                <li><strong>Status bar:</strong> Hidden for immersive experience</li>
                <li><strong>Platforms:</strong> Android (APK) and iOS (IPA) via Capacitor build system</li>
            </ul>

            <h3>11.3 Building the APK/IPA</h3>
            <pre><code>cd smartmall-app
npx cap sync
npx cap open android   # Opens Android Studio for APK generation
npx cap open ios       # Opens Xcode for IPA generation</code></pre>

            <h2 id="security">12. Security Model</h2>

            <h3>12.1 CSRF Protection</h3>
            <p>Every state-changing POST request includes a hidden <code>csrf_token</code> field. The server validates it against the session token using <code>hash_equals()</code> to prevent timing attacks.</p>
            <p><strong>Covered forms:</strong> login, register, add/edit product, delete product, manage categories, manage orders, checkout, order cancellation, contact.</p>

            <h3>12.2 SQL Injection Prevention</h3>
            <p>All database queries use <strong>PDO prepared statements</strong> with either named (<code>:param</code>) or positional (<code>?</code>) parameters. No raw string interpolation in SQL.</p>

            <h3>12.3 Password Security</h3>
            <ul>
                <li>Hashing: <code>password_hash()</code> with <code>PASSWORD_BCRYPT</code> (cost 10 by default)</li>
                <li>Verification: <code>password_verify()</code> &mdash; constant-time comparison</li>
                <li>Minimum length: 8 characters</li>
                <li>Complexity: uppercase, lowercase, digit, special character required</li>
            </ul>

            <h3>12.4 Session Security</h3>
            <ul>
                <li><code>session_regenerate_id(true)</code> on login to prevent fixation</li>
                <li><code>session_write_close()</code> before redirect to ensure persistence</li>
                <li>CSRF token stored in session, compared with <code>hash_equals()</code></li>
            </ul>

            <h3>12.5 URL Validation</h3>
            <ul>
                <li>Redirect URLs in <code>set_currency.php</code> are validated as same-origin only</li>
                <li>Login redirect parameter is sanitized via regex: <code>/^[a-zA-Z0-9_\-\/\.]+\.php$/</code></li>
            </ul>

            <h3>12.6 File Upload Security</h3>
            <ul>
                <li>Files are renamed with <code>uniqid()</code> to prevent path traversal</li>
                <li>File extensions are lowercased (no case-based bypass)</li>
                <li>Upload directory is a dedicated <code>/uploads/</code> folder (not executable by default)</li>
            </ul>

            <h2 id="deployment">13. Deployment</h2>

            <h3>13.1 Production Checklist</h3>
            <ul>
                <li>Change <strong>Chapa secret key</strong> to a production key (not test)</li>
                <li>Move the API key to an environment variable (<code>$_ENV</code> or <code>.env</code>)</li>
                <li>Set <code>PDO::ERRMODE_EXCEPTION</code> for better error handling</li>
                <li>Configure HTTPS (required for PWA and Chapa callbacks)</li>
                <li>Update <code>smartmall-app/capacitor.config.json</code> server URL to production domain</li>
                <li>Set up a cron job for abandoned cart cleanup if needed</li>
                <li>Enable OPcache for PHP performance</li>
                <li>Set <code>session.cookie_secure = true</code> and <code>session.cookie_httponly = true</code></li>
            </ul>

            <h3>13.2 Performance Notes</h3>
            <ul>
                <li>Exchange rate API calls are cached locally; cache respects API expiry</li>
                <li>Product images are stored locally; consider CDN for scaling</li>
                <li>Cart queries run on every page load (via footer) — consider memoization for high traffic</li>
                <li>No ORM overhead — raw PDO is fast but requires manual query maintenance</li>
            </ul>

            <h3>13.3 Common Issues</h3>
            <table>
                <tr><th>Issue</th><th>Solution</th></tr>
                <tr><td>404 on admin pages</td><td>Ensure correct URL path to <code>admin/dashboard.php</code></td></tr>
                <tr><td>Chapa payment not returning</td><td>Check callback URL in Chapa dashboard; verify <code>CHAPA_API_URL</code> is correct</td></tr>
                <tr><td>Images not displaying</td><td>Check <code>/uploads/</code> directory permissions (755) and file existence</td></tr>
                <tr><td>Currency showing 0.00</td><td>Exchange rate API may be blocked; check outbound cURL connectivity</td></tr>
                <tr><td>"Invalid CSRF token"</td><td>Session may have expired; clear cookies and retry</td></tr>
                <tr><td>Session not persisting</td><td>Check <code>session.save_path</code> is writable; verify <code>session_start()</code> order</td></tr>
            </table>

            <div class="tip"><p><strong>Tip:</strong> For development, the password gate at <code>protect.php</code> uses the password <code>smartmalltest!</code>. Admin access requires a database user with <code>role='admin'</code> — create one using <code>password_hash()</code> for the bcrypt hash.</p></div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
