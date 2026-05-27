<?php
require_once __DIR__ . '/../config.php';
$page_title = 'User Guide - Smart Mall';
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
            <h3>User Guide</h3>
            <nav>
                <a href="#getting-started">Getting Started</a>
                <a href="#browsing">Browsing Products</a>
                <a href="#cart">Shopping Cart</a>
                <a href="#checkout">Checkout &amp; Payment</a>
                <a href="#orders">Order Tracking</a>
                <a href="#mobile">Mobile App</a>
                <a href="#support">Contact &amp; Support</a>
            </nav>
        </aside>
        <div class="docs-content">
            <a href="index.php" class="back-link">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5m0 0l6 6m-6-6l6-6"/></svg>
                Back to Documentation
            </a>

            <h1>User Guide</h1>

            <h2 id="getting-started">1. Getting Started</h2>

            <h3>1.1 Registration</h3>
            <p>To create a new Smart Mall account:</p>
            <div class="docs-step"><span class="docs-step-num">1</span><p>Navigate to the Smart Mall website and click <strong>Register</strong> in the top navigation bar.</p></div>
            <div class="docs-step"><span class="docs-step-num">2</span><p>Fill in your full name, email address, and a secure password.</p></div>
            <div class="docs-step"><span class="docs-step-num">3</span><p>Click <strong>Create Account</strong> to complete registration.</p></div>
            <div class="docs-step"><span class="docs-step-num">4</span><p>You will be automatically logged in after successful registration.</p></div>
            <div class="note"><p><strong>Note:</strong> Password must be at least 8 characters and include a mix of letters and numbers.</p></div>

            <h3>1.2 Login</h3>
            <div class="docs-step"><span class="docs-step-num">1</span><p>Click <strong>Login</strong> in the top navigation bar.</p></div>
            <div class="docs-step"><span class="docs-step-num">2</span><p>Enter your registered email address and password.</p></div>
            <div class="docs-step"><span class="docs-step-num">3</span><p>Click <strong>Sign In</strong> to access your account.</p></div>
            <ul><li>Check <strong>Remember Me</strong> to stay logged in across sessions.</li></ul>

            <h2 id="browsing">2. Browsing Products</h2>

            <h3>2.1 Homepage</h3>
            <p>The homepage displays a grid of available products with their images, names, prices, and stock quantities. Prices show in both <strong>USD</strong> and <strong>ETB (Ethiopian Birr)</strong>.</p>
            <ul>
                <li>Each product card shows: thumbnail image, product name, price, and stock status.</li>
                <li>Use <strong>Buy Now</strong> for immediate purchase.</li>
                <li>Use <strong>Add to Cart</strong> to shop for multiple items.</li>
            </ul>

            <h3>2.2 Search</h3>
            <p>The search bar at the top of every page lets you find products by name or description. Results update dynamically as you type, showing up to 6 matching products with live price display.</p>
            <ul>
                <li>Search matches partial words (e.g., "phone" finds "Smartphone").</li>
                <li>Search includes product descriptions, not just titles.</li>
            </ul>

            <h3>2.3 Categories</h3>
            <p>Use the category cards on the homepage to browse products by category: <strong>Fashion</strong>, <strong>Electronics</strong>, <strong>Home</strong>, and <strong>Beauty</strong>. Each category features up to 3 promotional slide images.</p>

            <h3>2.4 Product Details</h3>
            <p>Click any product card to view the full product details page with:</p>
            <ul>
                <li>Multiple product images with gallery navigation.</li>
                <li>Full product description and specifications.</li>
                <li>Price in both USD and ETB.</li>
                <li>Stock availability indicator.</li>
                <li><strong>Add to Cart</strong> and <strong>Buy Now</strong> action buttons.</li>
            </ul>

            <h2 id="cart">3. Shopping Cart</h2>

            <h3>3.1 Adding Items</h3>
            <p>Add products to your cart using the <strong>Add to Cart</strong> button on product cards or the product details page. A sticky cart bar at the bottom of every page shows your current item count and total.</p>

            <h3>3.2 Managing Your Cart</h3>
            <p>Click the cart icon in the navigation bar to open your shopping cart:</p>
            <ul>
                <li>View all items with individual prices.</li>
                <li>Update quantities using +/- controls.</li>
                <li>Remove items individually.</li>
                <li>See subtotal, 10% VAT, and grand total.</li>
                <li>Proceed to checkout.</li>
            </ul>

            <h3>3.3 Cart Persistence</h3>
            <p>Your cart is stored server-side and persists across browser sessions when logged in. A sticky cart notification bar at the bottom of the page displays your current status.</p>

            <h2 id="checkout">4. Checkout &amp; Payment</h2>

            <h3>4.1 Checkout Process</h3>
            <div class="docs-step"><span class="docs-step-num">1</span><p>Review your cart items and totals.</p></div>
            <div class="docs-step"><span class="docs-step-num">2</span><p>Click <strong>Proceed to Checkout</strong>.</p></div>
            <div class="docs-step"><span class="docs-step-num">3</span><p>Confirm your shipping details (name, address, city, state, country).</p></div>
            <div class="docs-step"><span class="docs-step-num">4</span><p>Select a payment method.</p></div>
            <div class="docs-step"><span class="docs-step-num">5</span><p>Place your order.</p></div>

            <h3>4.2 Chapa Payment (Online)</h3>
            <p>Chapa is the integrated online payment gateway supporting Ethiopian Birr (ETB) payments via:</p>
            <ul>
                <li>Bank transfers and mobile banking.</li>
                <li>Telebirr.</li>
                <li>Debit/credit cards.</li>
            </ul>
            <p>After placing an order with Chapa, you will be redirected to the Chapa payment page to complete the transaction securely.</p>

            <h3>4.3 Cash on Delivery</h3>
            <p>Select <strong>Cash on Delivery</strong> to pay in cash when your order arrives. No online payment is required at checkout.</p>

            <h3>4.4 Currency Switching</h3>
            <p>Toggle between <strong>USD</strong> and <strong>ETB</strong> using the currency selector in the header. Prices update instantly across all pages. Exchange rates are cached for performance.</p>

            <h2 id="orders">5. Order Tracking</h2>

            <h3>5.1 Viewing Orders</h3>
            <p>Access your order history from the <strong>My Orders</strong> link in the navigation. The orders page shows:</p>
            <ul>
                <li>Order ID and date placed.</li>
                <li>Current status (Pending / Paid / Shipped / Delivered / Cancelled).</li>
                <li>Total amount in your chosen currency.</li>
                <li>Item details and quantities.</li>
            </ul>

            <h3>5.2 Order Statuses</h3>
            <ul>
                <li><strong>Pending</strong> &mdash; Order received, awaiting payment confirmation.</li>
                <li><strong>Paid</strong> &mdash; Payment confirmed, order is being processed.</li>
                <li><strong>Shipped</strong> &mdash; Order has been dispatched.</li>
                <li><strong>Delivered</strong> &mdash; Order completed successfully.</li>
                <li><strong>Cancelled</strong> &mdash; Order was cancelled.</li>
            </ul>

            <h3>5.3 Cancelling Orders</h3>
            <p>Pending orders can be cancelled from the order history page. Click <strong>Cancel</strong> next to a pending order to cancel it.</p>

            <h2 id="mobile">6. Mobile App</h2>
            <p>Smart Mall offers a Flutter-based mobile application for <strong>Android</strong> and <strong>iOS</strong>:</p>
            <ul>
                <li>Browse products, search, and filter by category.</li>
                <li>View product details with images.</li>
                <li>Manage your shopping cart locally.</li>
                <li>User registration and login.</li>
                <li>Place orders with Chapa payment.</li>
                <li>View order history.</li>
            </ul>
            <p>Download the app from the website's download page, or scan the QR code.</p>

            <h2 id="support">7. Contact &amp; Support</h2>
            <p>Use the <strong>Contact</strong> page to reach the Smart Mall team for inquiries. The <strong>About</strong> page displays live store statistics:</p>
            <ul>
                <li>Total products listed.</li>
                <li>Total orders processed.</li>
                <li>Registered customers.</li>
                <li>Product categories.</li>
            </ul>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
