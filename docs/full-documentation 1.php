<?php
require_once __DIR__ . '/../config.php';
$page_title = 'Full Documentation - Smart Mall';
include __DIR__ . '/../includes/header.php';
?>
<style>
    .doc-container {
        max-width: 860px;
        margin: 0 auto;
        padding: 2rem 1.5rem 4rem;
        line-height: 1.8;
    }
    .doc-container h1 {
        font-family: 'Outfit', sans-serif;
        font-size: 2.6rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--secondary-color);
        margin: 2.5rem 0 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 3px solid var(--primary-color);
    }
    .doc-container h2 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--secondary-color);
        margin: 2rem 0 0.8rem;
    }
    .doc-container h3 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--primary-color);
        margin: 1.5rem 0 0.5rem;
    }
    .doc-container p {
        margin-bottom: 0.8rem;
        color: var(--text-dark);
    }
    .doc-container ul, .doc-container ol {
        margin: 0.5rem 0 1rem 1.5rem;
    }
    .doc-container li {
        margin-bottom: 0.3rem;
        color: var(--text-dark);
    }
    .doc-container .section-num {
        display: inline-block;
        background: var(--primary-color);
        color: #fff;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        text-align: center;
        line-height: 32px;
        font-size: 0.85rem;
        font-weight: 700;
        margin-right: 0.5rem;
    }
    .doc-container .sub-num {
        display: inline-block;
        background: var(--accent-color, #4A6FA5);
        color: #fff;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        text-align: center;
        line-height: 26px;
        font-size: 0.75rem;
        font-weight: 700;
        margin-right: 0.4rem;
    }
    .doc-container blockquote {
        border-left: 4px solid var(--primary-color);
        padding: 0.8rem 1.2rem;
        margin: 1rem 0;
        background: var(--bg-light);
        border-radius: 0 8px 8px 0;
        color: var(--text-dark);
    }
    .doc-container table {
        width: 100%;
        border-collapse: collapse;
        margin: 1rem 0;
    }
    .doc-container th, .doc-container td {
        border: 1px solid var(--border-color);
        padding: 0.6rem 0.8rem;
        text-align: left;
    }
    .doc-container th {
        background: var(--primary-color);
        color: #fff;
        font-weight: 600;
    }
    .doc-container tr:nth-child(even) {
        background: var(--bg-light);
    }
    .doc-container code {
        background: var(--bg-light);
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
        font-size: 0.85rem;
        color: var(--text-dark);
    }
    .doc-container pre {
        background: var(--secondary-color);
        color: var(--bg-light);
        padding: 1rem 1.2rem;
        border-radius: 10px;
        overflow-x: auto;
        font-size: 0.85rem;
        line-height: 1.6;
        margin: 1rem 0;
    }
    .doc-title {
        text-align: center;
        padding: 3rem 0 1rem;
    }
    .doc-title h1 {
        border-bottom: none;
        font-size: 3rem;
        margin-bottom: 0.3rem;
    }
    .doc-title p {
        color: var(--text-light);
        font-size: 1.1rem;
    }
    .doc-version {
        text-align: center;
        color: var(--text-light);
        font-size: 0.9rem;
        margin-bottom: 2rem;
    }
    .toc {
        background: var(--bg-light);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem 2rem;
        margin: 2rem 0;
    }
    .toc h2 {
        margin-top: 0;
        border-bottom: none;
    }
    .toc ol {
        margin: 0.5rem 0 0 1.2rem;
    }
    .toc a {
        color: var(--primary-color);
        text-decoration: none;
    }
    .toc a:hover {
        text-decoration: underline;
    }
    @media (max-width: 640px) {
        .doc-container { padding: 1rem; }
        .doc-title h1 { font-size: 2rem; }
    }
</style>

<div class="container">
    <div class="doc-container">

        <div class="doc-title">
            <h1>Smart Mall</h1>
            <p>Complete Documentation</p>
        </div>
        <div class="doc-version">Version 1.0 &mdash; Full System Documentation</div>

        <!-- ==================== TABLE OF CONTENTS ==================== -->
        <div class="toc">
            <h2>Table of Contents</h2>
            <ol>
                <li><a href="#abstract">Abstract</a></li>
                <li><a href="#introduction">Introduction</a></li>
                <li><a href="#problem">Problem Statement</a></li>
                <li><a href="#objectives">Project Objectives</a></li>
                <li><a href="#overview">System Overview</a></li>
                <li><a href="#features">System Features</a></li>
                <li><a href="#architecture">System Architecture</a></li>
                <li><a href="#tech">Technology Stack</a></li>
                <li><a href="#database">Database Design</a></li>
                <li><a href="#er">ER Diagram</a></li>
                <li><a href="#development">Development Process</a></li>
                <li><a href="#testing">Testing</a></li>
                <li><a href="#security">Security Measures</a></li>
                <li><a href="#deployment">Deployment</a></li>
                <li><a href="#usage">How to Use the Platform</a></li>
                <li><a href="#future">Future Enhancements</a></li>
                <li><a href="#conclusion">Conclusion</a></li>
            </ol>
        </div>

        <!-- ==================== 1. ABSTRACT ==================== -->
        <h1 id="abstract"><span class="section-num">1</span> Abstract</h1>
        <p>Smart Mall is a full-stack e-commerce platform designed to bridge the gap between local businesses and online consumers. Built with PHP, MySQL, and modern frontend technologies, the platform provides a secure and intuitive digital marketplace where customers can browse products, manage shopping carts, make payments, and track orders. Administrators benefit from a comprehensive dashboard for managing products, categories, orders, and store analytics. The platform supports multi-currency pricing (USD/ETB), integrates with the Chapa payment gateway for Ethiopian online transactions, and includes a Progressive Web App implementation for mobile access. Smart Mall demonstrates a complete, production-ready e-commerce solution suitable for small to medium businesses seeking to establish an online presence.</p>

        <!-- ==================== 2. INTRODUCTION ==================== -->
        <h1 id="introduction"><span class="section-num">2</span> Introduction</h1>

        <h2>Background</h2>
        <p>The global shift toward digital commerce has accelerated dramatically, with e-commerce transactions accounting for an increasing share of retail spending worldwide. In emerging markets, this transition presents both opportunities and challenges. While consumers increasingly expect online shopping convenience, many local businesses lack the technical infrastructure or resources to establish digital storefronts. The gap between growing consumer demand and limited business capability creates a need for accessible, affordable e-commerce platforms tailored to local market conditions.</p>

        <h2>Overview of Smart Mall</h2>
        <p>Smart Mall is a web-based e-commerce platform that enables businesses to showcase and sell products online. The platform provides a complete shopping experience including product browsing with category filtering, a search system, shopping cart management, and secure checkout. The admin panel gives store operators full control over product inventory, category organization, order processing, and store statistics. The system incorporates multi-currency support, a payment gateway integration for Ethiopian Birr transactions, and mobile-responsive design for cross-device accessibility.</p>

        <h2>Importance of E-Commerce</h2>
        <p>E-commerce platforms offer significant advantages over traditional brick-and-mortar retail:</p>
        <ul>
            <li><strong>24/7 Accessibility:</strong> Customers can shop at any time from any location with internet access.</li>
            <li><strong>Wider Reach:</strong> Businesses can reach customers beyond their immediate geographic area.</li>
            <li><strong>Reduced Overhead:</strong> Digital storefronts eliminate or reduce costs associated with physical retail space.</li>
            <li><strong>Data-Driven Insights:</strong> Online platforms provide analytics on customer behavior, popular products, and sales trends.</li>
            <li><strong>Scalability:</strong> Digital infrastructure can grow with the business more easily than physical locations.</li>
        </ul>

        <!-- ==================== 3. PROBLEM STATEMENT ==================== -->
        <h1 id="problem"><span class="section-num">3</span> Problem Statement</h1>
        <p>Traditional retail shopping presents several challenges that Smart Mall aims to address:</p>
        <ul>
            <li><strong>Limited Product Accessibility:</strong> Customers can only browse products available in their immediate geographic area, limiting choice and price comparison.</li>
            <li><strong>Lack of Centralized Shopping:</strong> Without a unified platform, customers must visit multiple stores or websites to compare products and prices.</li>
            <li><strong>Difficulty Comparing Products:</strong> Physical shopping makes it hard to compare features, prices, and specifications across different products and vendors.</li>
            <li><strong>Time-Consuming Shopping:</strong> Traditional shopping requires physical travel, store navigation, and waiting in checkout lines.</li>
            <li><strong>Limited Business Reach:</strong> Local businesses are constrained by their physical location and cannot easily attract customers from outside their immediate area.</li>
            <li><strong>Payment Barriers:</strong> Many international e-commerce platforms do not support local payment methods common in emerging markets such as Ethiopia.</li>
        </ul>

        <!-- ==================== 4. PROJECT OBJECTIVES ==================== -->
        <h1 id="objectives"><span class="section-num">4</span> Project Objectives</h1>

        <h2>General Objective</h2>
        <p>Develop a secure, scalable, and user-friendly e-commerce platform that enables businesses to sell products online and customers to shop conveniently from any device.</p>

        <h2>Specific Objectives</h2>
        <ol>
            <li>Provide an intuitive product browsing experience with category filtering and search functionality.</li>
            <li>Implement a complete shopping cart and order management system.</li>
            <li>Provide administrators with full product, category, and order management capabilities.</li>
            <li>Maintain secure user authentication with password hashing, session management, and CSRF protection.</li>
            <li>Integrate multi-currency support (USD and ETB) with live exchange rate conversion.</li>
            <li>Implement online payment processing through the Chapa payment gateway.</li>
            <li>Ensure responsive design for mobile and desktop devices.</li>
            <li>Create a Progressive Web App for enhanced mobile experience.</li>
        </ol>

        <!-- ==================== 5. SYSTEM OVERVIEW ==================== -->
        <h1 id="overview"><span class="section-num">5</span> System Overview</h1>

        <h2>5.1 What Problem Does Smart Mall Solve?</h2>
        <p>Smart Mall addresses the following challenges in traditional shopping:</p>
        <ul>
            <li><strong>Limited product accessibility</strong> &mdash; Products are available online 24/7 from any location.</li>
            <li><strong>Lack of centralized shopping platforms</strong> &mdash; Products are aggregated in a single digital marketplace.</li>
            <li><strong>Difficulty comparing products</strong> &mdash; Customers can browse side-by-side with search and category filters.</li>
            <li><strong>Time-consuming physical shopping</strong> &mdash; Online ordering eliminates travel and wait times.</li>
            <li><strong>Local payment limitations</strong> &mdash; Chapa integration enables payments in Ethiopian Birr through local methods.</li>
        </ul>

        <h2>5.2 Proposed Solution</h2>
        <p>Smart Mall provides a complete digital commerce ecosystem:</p>
        <ul>
            <li>A centralized digital marketplace accessible via web browser and mobile app.</li>
            <li>Product browsing and searching with real-time price display in multiple currencies.</li>
            <li>Online ordering system with cart management and secure checkout.</li>
            <li>Admin management dashboard for store operators.</li>
            <li>Multi-currency support with live exchange rate updates.</li>
            <li>Payment gateway integration supporting Ethiopian Birr transactions.</li>
            <li>Progressive Web App for mobile installation and offline capabilities.</li>
        </ul>

        <h2>5.3 Target Users</h2>
        <p>The platform is designed for two primary user roles:</p>

        <h3>Customers</h3>
        <ul>
            <li>Browse products by category or search.</li>
            <li>View product details and pricing in multiple currencies.</li>
            <li>Add products to cart and manage quantities.</li>
            <li>Place orders via Chapa online payment or cash on delivery.</li>
            <li>Track order history and status.</li>
        </ul>

        <h3>Administrators</h3>
        <ul>
            <li>Add, edit, and delete products with image uploads.</li>
            <li>Manage product categories with promotional slide images.</li>
            <li>View and update order statuses.</li>
            <li>Monitor store statistics (products, orders, customers, revenue).</li>
        </ul>

        <h3>Future Users</h3>
        <ul>
            <li><strong>Independent Sellers:</strong> Multi-vendor marketplace support allowing third-party sellers to register and list products.</li>
        </ul>

        <!-- ==================== 6. SYSTEM FEATURES ==================== -->
        <h1 id="features"><span class="section-num">6</span> System Features</h1>

        <h2>Customer Features</h2>
        <ul>
            <li><strong>Account Registration:</strong> Create a new account with name, email, and password. Password policy enforces minimum 8 characters with uppercase, lowercase, digit, and special character requirements.</li>
            <li><strong>Login Authentication:</strong> Secure login with session regeneration, remember-me functionality, and redirect support.</li>
            <li><strong>Browse Products by Category:</strong> Filter products using category dropdown or filter buttons. Each category can display promotional slide images.</li>
            <li><strong>Product Search:</strong> Real-time search results as you type, matching product names and descriptions. Returns up to 6 results with live price display.</li>
            <li><strong>Product Details:</strong> Full product page with image gallery navigation, description, stock indicator, and pricing in USD and ETB.</li>
            <li><strong>Shopping Cart:</strong> Server-side persistent cart with quantity controls, item removal, subtotal/VAT/total calculation, and sticky cart notification bar.</li>
            <li><strong>Checkout and Order Placement:</strong> Multi-step checkout with address collection, payment method selection (Chapa or COD), stock verification, and database transactions.</li>
            <li><strong>Order History:</strong> View past orders with status badges, item details, and cancel-pending-order functionality with stock restoration.</li>
            <li><strong>Password Reset:</strong> Forgot password flow with email-based reset link (dev mode writes to temp file).</li>
            <li><strong>Multi-Currency Toggle:</strong> Switch between USD and ETB display with cached exchange rates.</li>
            <li><strong>Contact Form:</strong> Submit inquiries with validation and success feedback.</li>
        </ul>

        <h2>Admin Features</h2>
        <ul>
            <li><strong>Dashboard:</strong> Overview with product, order, customer, and category counts plus low-stock alerts.</li>
            <li><strong>Add New Products:</strong> Form with name, description, category selection, price, stock, and multiple image uploads.</li>
            <li><strong>Edit Products:</strong> Update product details and replace images.</li>
            <li><strong>Delete Products:</strong> Remove products with server-side image cleanup.</li>
            <li><strong>Upload Images:</strong> Supports JPG, PNG, GIF, WebP with uniqid()-based naming to prevent collisions.</li>
            <li><strong>View Statistics:</strong> Dashboard shows product count, order total, customer count, and category count.</li>
            <li><strong>Manage Categories:</strong> CRUD operations with up to 3 promotional slide images per category.</li>
            <li><strong>Manage Orders:</strong> View all orders sorted by status, update status workflow (Pending &rarr; Paid &rarr; Shipped &rarr; Delivered), cancel orders.</li>
        </ul>

        <!-- ==================== 7. SYSTEM ARCHITECTURE ==================== -->
        <h1 id="architecture"><span class="section-num">7</span> System Architecture</h1>

        <h2>Architecture Type: Client-Server Architecture</h2>
        <p>The system follows a standard three-tier client-server architecture:</p>

        <pre>
User Browser (HTML, CSS, JavaScript)
        &darr; HTTP Requests (GET/POST)
Web Server (Apache)
        &darr;
PHP Application Layer (Server-Side Rendering)
        &darr; PDO Queries
MySQL Database
        </pre>

        <h3>Layer Explanation</h3>
        <ul>
            <li><strong>Frontend (Presentation Layer):</strong> HTML5, CSS3, and JavaScript handle the user interface, responsive layout, and client-side interactions such as search autocomplete, cart quantity updates, and theme switching.</li>
            <li><strong>Backend (Application Layer):</strong> PHP processes all business logic including authentication, cart operations, order management, payment verification, and currency conversion.</li>
            <li><strong>Database (Data Layer):</strong> MySQL stores all persistent data including users, products, categories, cart items, orders, and payment transactions.</li>
        </ul>

        <h3>Request Flow</h3>
        <ol>
            <li>User visits a page (e.g., <code>index.php</code>, <code>product.php</code>).</li>
            <li><code>config.php</code> bootstraps the application: starts the session, establishes database connection, loads currency data, and defines helper functions.</li>
            <li>The page includes <code>header.php</code> and <code>footer.php</code> for consistent layout (navigation, search bar, currency selector, cart notification, and footer).</li>
            <li>Database queries execute via PDO prepared statements to render dynamic content.</li>
            <li>Forms and actions (add to cart, checkout, login) post back to PHP handlers that process data and redirect.</li>
            <li>Chapa payment redirects the user to the Chapa payment page and handles the return callback for verification.</li>
        </ol>

        <!-- ==================== 8. TECHNOLOGY STACK ==================== -->
        <h1 id="tech"><span class="section-num">8</span> Technology Stack</h1>

        <table>
            <tr><th>Layer</th><th>Technology</th><th>Purpose</th></tr>
            <tr><td>Frontend</td><td>HTML5, CSS3, JavaScript</td><td>User interface, styling, client-side interactivity</td></tr>
            <tr><td>Backend</td><td>PHP 8.0+</td><td>Server-side logic, authentication, data processing</td></tr>
            <tr><td>Database</td><td>MySQL / MariaDB</td><td>Data persistence and retrieval</td></tr>
            <tr><td>Web Server</td><td>Apache (XAMPP)</td><td>HTTP server and request handling</td></tr>
            <tr><td>Payment Gateway</td><td>Chapa API</td><td>Online payment processing in ETB</td></tr>
            <tr><td>Exchange Rates</td><td>Open Exchange Rates API</td><td>Live USD/ETB conversion rates</td></tr>
            <tr><td>Mobile Wrapper</td><td>Capacitor</td><td>Native mobile app shell for Android/iOS</td></tr>
            <tr><td>PWA</td><td>Manifest.json</td><td>Progressive Web App installation support</td></tr>
        </table>

        <p><strong>Hosting:</strong> The live demo is hosted at <a href="https://smartmall.unaux.com/?i=1" target="_blank">smartmall.unaux.com</a>.</p>

        <!-- ==================== 9. DATABASE DESIGN ==================== -->
        <h1 id="database"><span class="section-num">9</span> Database Design</h1>

        <p>The system uses a relational MySQL database named <code>smartmall_db</code> with seven core tables:</p>

        <table>
            <tr><th>Table</th><th>Purpose</th></tr>
            <tr><td><code>users</code></td><td>Store customer and admin accounts (name, email, password hash, role)</td></tr>
            <tr><td><code>products</code></td><td>Store product information (name, description, price, stock, images, category)</td></tr>
            <tr><td><code>categories</code></td><td>Product grouping with promotional slide images</td></tr>
            <tr><td><code>cart</code></td><td>User cart items with product references and quantities</td></tr>
            <tr><td><code>orders</code></td><td>Order records with total, status, user reference, and timestamps</td></tr>
            <tr><td><code>order_items</code></td><td>Individual products within each order with quantity and price snapshot</td></tr>
            <tr><td><code>payments</code></td><td>Chapa payment transaction records with status and transaction references</td></tr>
        </table>

        <h3>Table Details</h3>

        <p><strong>users &mdash;</strong> Customer and administrator accounts. Contains: id, name, email, password (bcrypt hashed), role (customer/admin), created_at.</p>

        <p><strong>products &mdash;</strong> Product catalog. Contains: id, name, description, price (USD), stock, category_id (FK to categories), image (primary image path), images (JSON array of additional images), created_at.</p>

        <p><strong>categories &mdash;</strong> Product categories with promotional images. Contains: id, name, slide_image_1, slide_image_2, slide_image_3.</p>

        <p><strong>cart &mdash;</strong> Shopping cart items tied to authenticated users. Contains: id, user_id (FK to users), product_id (FK to products), quantity, created_at. Cart is server-side and persists across sessions.</p>

        <p><strong>orders &mdash;</strong> Customer order records. Contains: id, user_id (FK to users), total (order amount), status (pending/paid/shipped/delivered/cancelled), created_at.</p>

        <p><strong>order_items &mdash;</strong> Line items within each order. Contains: id, order_id (FK to orders), product_id (FK to products), quantity, price (snapshot of price at time of order).</p>

        <p><strong>payments &mdash;</strong> Chapa payment transaction records. Contains: id, order_id (FK to orders), user_id (FK to users), amount, currency, status, chapa_tx_ref (Chapa transaction reference), chapa_response (full JSON response from Chapa), created_at.</p>

        <!-- ==================== 10. ER DIAGRAM ==================== -->
        <h1 id="er"><span class="section-num">10</span> ER Diagram</h1>

        <p>The entity-relationship diagram illustrates the relationships between database tables:</p>

        <pre>
+-----------------+       +-------------------+       +-----------------------+
|     users       |       |     orders        |       |     order_items       |
+-----------------+       +-------------------+       +-----------------------+
| user_id (PK)    |&lt;------| user_id           |       | order_item_id (PK)    |
| name            |   1:N | order_id (PK)     |&lt;------| order_id              |
| email           |       | total             |   1:N  | product_id            |
| password        |       | status            |       | quantity              |
| role            |       | created           |       | price                 |
| created         |       +-------------------+       +-----------------------+ 
+-----------+-----+                                       |
            |                                             |
            | 1:N                                         | N:1
            |                                             |
+-----------+----------+                  +---------------+----------+
|        cart          |                  |         products         |
+----------------------+                  +--------------------------+
| cart_id (PK)         |                  | product_id (PK)          |
| user_id              |       N:1        | name                     |
| product_id           |&lt;-----------------| description              |
| quantity             |                  | price                    |
| created              |                  | stock                    |
+----------------------+                  | category_id--------------|&lt;--+
                                          | image                    |    |
                                          | images (JSON)            |    |
                                          +--------------------------+    |
                                                                           |
                                                +--------------------------+------+
                                                |       categories               |
                                                +---------------------------------+
                                                | category_id (PK)                |
                                                | name                            |
                                                | slide_image_1..3                |
                                                +---------------------------------+

                        +----------------------+
                        |      payments        |
                        +----------------------+
                        | payment_id (PK)      |
                        | order_id (FK)        |------&gt; orders.order_id
                        | user_id (FK)         |------&gt; users.user_id
                        | amount               |
                        | currency             |
                        | status               |
                        | chapa_tx_ref         |
                        | created              |
                        +----------------------+
        </pre>

        <h3>Relationships</h3>
        <ul>
            <li>One user can have many orders (1:N)</li>
            <li>One user can have many cart items (1:N)</li>
            <li>One order contains many order items (1:N)</li>
            <li>One product appears in many order items (1:N)</li>
            <li>One category contains many products (1:N)</li>
            <li>One order has one payment record (1:1)</li>
        </ul>

        <!-- ==================== 11. DEVELOPMENT PROCESS ==================== -->
        <h1 id="development"><span class="section-num">11</span> Development Process</h1>

        <h2>Technology Selection</h2>
        <p>The following technologies were chosen for these reasons:</p>
        <ul>
            <li><strong>PHP:</strong> Open source, widely supported by hosting providers, large community, easy to deploy, extensive documentation.</li>
            <li><strong>MySQL:</strong> Free, reliable, well-integrated with PHP, supports complex queries and transactions.</li>
            <li><strong>Apache/XAMPP:</strong> Free, cross-platform development environment, mirrors production server configurations.</li>
            <li><strong>HTML5/CSS3/JS:</strong> Universal web standards, no build tools required, works on all devices.</li>
            <li><strong>Capacitor:</strong> Wraps the web app in a native shell without requiring a separate mobile codebase.</li>
        </ul>

        <h2>System Design</h2>

        <h3>Frontend Design</h3>
        <ul>
            <li>Responsive layout with CSS Grid and Flexbox for cross-device compatibility.</li>
            <li>Card-based product grid adapting to screen width (auto-fill columns).</li>
            <li>Category browsing via dropdown menu and filter buttons.</li>
            <li>Sticky navigation header and cart notification bar.</li>
            <li>Mobile hamburger menu drawer for small screens.</li>
            <li>Dark/light theme toggle with localStorage persistence.</li>
        </ul>

        <h3>Backend Design</h3>
        <ul>
            <li>PHP sessions for user authentication and state management.</li>
            <li>PDO prepared statements for all database queries (SQL injection prevention).</li>
            <li>Password hashing via PHP's built-in <code>password_hash()</code> with bcrypt.</li>
            <li>CSRF token generation and validation on all forms.</li>
            <li>Role-based access control for admin pages.</li>
            <li>Database transactions for order placement (atomicity guarantee).</li>
        </ul>

        <h3>Database Design</h3>
        <ul>
            <li>Fully relational with foreign key constraints.</li>
            <li>Normalized structure to minimize data redundancy.</li>
            <li>Indexed columns for query performance.</li>
        </ul>

        <!-- ==================== 12. TESTING ==================== -->
        <h1 id="testing"><span class="section-num">12</span> Testing</h1>

        <p>The following testing types were performed during development:</p>

        <h3>Unit Testing</h3>
        <ul>
            <li><strong>Login:</strong> Verified authentication flow with valid credentials, invalid password, non-existent account, and session persistence.</li>
            <li><strong>Product Upload:</strong> Tested image upload with various formats (JPG, PNG, WebP), file size limits, and error handling.</li>
            <li><strong>Cart Functions:</strong> Verified add, update quantity, remove items, and cart persistence across sessions.</li>
        </ul>

        <h3>Integration Testing</h3>
        <ul>
            <li><strong>Database Queries:</strong> Tested all PDO prepared statements for correctness and parameter binding.</li>
            <li><strong>Frontend-Backend Communication:</strong> Verified search autocomplete, cart AJAX operations, and form submissions.</li>
            <li><strong>Order Flow:</strong> End-to-end testing of cart &rarr; checkout &rarr; payment &rarr; order confirmation &rarr; admin order management.</li>
        </ul>

        <h3>Security Testing</h3>
        <ul>
            <li><strong>SQL Injection:</strong> Verified PDO prepared statements prevent injection attacks.</li>
            <li><strong>Password Hashing:</strong> Confirmed bcrypt hashing with cost factor 10.</li>
            <li><strong>CSRF Tokens:</strong> Validated token generation, storage, and verification on all form submissions.</li>
            <li><strong>Input Sanitization:</strong> Checked htmlspecialchars() usage on output and input validation on forms.</li>
        </ul>

        <!-- ==================== 13. SECURITY MEASURES ==================== -->
        <h1 id="security"><span class="section-num">13</span> Security Measures</h1>

        <p>Smart Mall implements the following security features:</p>

        <table>
            <tr><th>Measure</th><th>Implementation</th></tr>
            <tr><td>Password Hashing</td><td>bcrypt via PHP's <code>password_hash()</code> with cost factor 10</td></tr>
            <tr><td>SQL Injection Prevention</td><td>PDO prepared statements with parameterized queries throughout</td></tr>
            <tr><td>CSRF Protection</td><td>Per-session CSRF token generated in <code>includes/db.php</code>, validated on all POST submissions</td></tr>
            <tr><td>Input Sanitization</td><td><code>htmlspecialchars()</code> on output, <code>trim()</code> and validation on input</td></tr>
            <tr><td>Role-Based Access</td><td>Admin pages check <code>$_SESSION['user_role']</code> before rendering, unauthorized users redirected</td></tr>
            <tr><td>Session Security</td><td>Session regeneration on login, HTTP-only cookies, session timeout</td></tr>
            <tr><td>Password Policy</td><td>Minimum 8 characters with uppercase, lowercase, digit, and special character requirements</td></tr>
            <tr><td>File Upload Security</td><td>Extension whitelist (JPG, PNG, GIF, WebP), <code>uniqid()</code>-based naming to prevent path traversal</td></tr>
        </table>

        <!-- ==================== 14. DEPLOYMENT ==================== -->
        <h1 id="deployment"><span class="section-num">14</span> Deployment</h1>

        <p>Steps to deploy the Smart Mall platform to a production server:</p>

        <ol>
            <li><strong>Upload Project Files:</strong> Copy all PHP files, assets, and configuration to the web server's document root using FTP or a file manager.</li>
            <li><strong>Create Database:</strong> Create a MySQL database (e.g., <code>smartmall_db</code>) and import the database schema. The schema includes all tables: users, products, categories, cart, orders, order_items, and payments.</li>
            <li><strong>Configure Database Connection:</strong> Update the connection settings in <code>includes/db.php</code> with the production database host, name, username, and password.</li>
            <li><strong>Configure Payment Gateway:</strong> Update the Chapa secret key in <code>chapa_pay/chapa-config.php</code> with the production API key.</li>
            <li><strong>Set Permissions:</strong> Ensure the <code>uploads/</code> directory is writable by the web server for product image uploads.</li>
            <li><strong>Create Admin Account:</strong> Register a user through the site, then set the role to 'admin' in the database, or create an admin user via SQL using <code>password_hash()</code> for the bcrypt hash.</li>
            <li><strong>Configure Domain:</strong> Point a domain name to the server and update the <code>BASE_URL</code> in <code>config.php</code> if defined.</li>
            <li><strong>Test:</strong> Verify homepage loads, registration works, products display, cart functions, and checkout processes correctly.</li>
        </ol>

        <p><strong>Live Demo:</strong> <a href="https://smartmall.unaux.com/?i=1" target="_blank">smartmall.unaux.com</a></p>

        <!-- ==================== 15. HOW TO USE ==================== -->
        <h1 id="usage"><span class="section-num">15</span> How to Use the Platform</h1>

        <h2>Customer Guide</h2>

        <h3>Step 1: Register an Account</h3>
        <ol>
            <li>Click "Register" in the top navigation bar.</li>
            <li>Enter your full name, email address, and a secure password (minimum 8 characters with uppercase, lowercase, digit, and special character).</li>
            <li>Click "Create Account" to complete registration. You will be automatically logged in.</li>
        </ol>

        <h3>Step 2: Browse Products</h3>
        <ol>
            <li>Use the homepage grid to view all available products.</li>
            <li>Filter by category using the dropdown menu or filter buttons.</li>
            <li>Use the search bar to find products by name or description (results appear as you type).</li>
            <li>Click a product card to view full details including image gallery and description.</li>
        </ol>

        <h3>Step 3: Add Items to Cart</h3>
        <ol>
            <li>Click "Add to Cart" on any product card or the product detail page.</li>
            <li>A sticky cart notification bar at the bottom of the page shows your current item count.</li>
            <li>Click the cart icon in the navigation bar to view your cart.</li>
            <li>Adjust quantities with +/- controls or remove items as needed.</li>
        </ol>

        <h3>Step 4: Checkout</h3>
        <ol>
            <li>Click "Proceed to Checkout" from your cart page.</li>
            <li>Enter your shipping address details.</li>
            <li>Select a payment method: Chapa (online) or Cash on Delivery.</li>
            <li>Review your order summary and place the order.</li>
            <li>For Chapa payments, you will be redirected to complete payment securely.</li>
        </ol>

        <h3>Step 5: Track Orders</h3>
        <ol>
            <li>Click "My Orders" in the navigation bar to view order history.</li>
            <li>Each order shows its status: Pending, Paid, Shipped, Delivered, or Cancelled.</li>
            <li>Pending orders can be cancelled directly from the order history page.</li>
        </ol>

        <h2>Administrator Guide</h2>

        <h3>Login to Admin Dashboard</h3>
        <ol>
            <li>Log in with an account that has admin role.</li>
            <li>Click "Admin" in the navigation bar to access the dashboard.</li>
        </ol>

        <h3>Manage Products</h3>
        <ol>
            <li>From the dashboard, click "Add New Product" to add a new item.</li>
            <li>Fill in product name, description, category, price, and stock quantity.</li>
            <li>Upload product images (supports multiple images).</li>
            <li>Use the Edit and Delete buttons in the product table to modify existing products.</li>
        </ol>

        <h3>Manage Categories</h3>
        <ol>
            <li>Navigate to the Categories management page.</li>
            <li>Add new categories with a name and up to 3 promotional slide images.</li>
            <li>Edit or delete existing categories as needed.</li>
        </ol>

        <h3>Monitor Orders</h3>
        <ol>
            <li>Navigate to Orders management from the admin panel.</li>
            <li>View all customer orders sorted by status.</li>
            <li>Update order status through the workflow: Pending &rarr; Paid &rarr; Shipped &rarr; Delivered.</li>
            <li>Cancel orders when necessary.</li>
        </ol>

        <!-- ==================== 16. FUTURE ENHANCEMENTS ==================== -->
        <h1 id="future"><span class="section-num">16</span> Future Enhancements</h1>

        <p>The following improvements are planned for future releases:</p>

        <ul>
            <li><strong>Local Language Support:</strong> Add Amharic and other local language translations for a broader audience.</li>
            <li><strong>AI Shopping Assistant:</strong> Integrate an AI-powered chatbot to help customers find products, answer questions, and provide personalized recommendations.</li>
            <li><strong>Product Reviews and Ratings:</strong> Allow customers to leave reviews and ratings on products to build trust and inform other shoppers.</li>
            <li><strong>Seller Marketplace System:</strong> Expand the platform to support multiple independent sellers with individual storefronts, inventory management, and revenue tracking.</li>
            <li><strong>Guest Cart:</strong> Allow users to add items to cart without requiring login, with cart merging on registration.</li>
            <li><strong>Email Notifications:</strong> Wire the contact form and order confirmation to send real email notifications via SMTP.</li>
            <li><strong>Product Pagination:</strong> Implement pagination on product listings for better performance with large catalogs.</li>
            <li><strong>External CSS:</strong> Move inline styles to external stylesheets for easier maintenance and theming.</li>
            <li><strong>Full PWA:</strong> Add a service worker for offline support and true app installation experience.</li>
            <li><strong>Wishlist:</strong> Allow customers to save products to a wishlist for future purchase.</li>
            <li><strong>Discount Codes:</strong> Implement promotional coupon/discount code functionality.</li>
            <li><strong>Social Login:</strong> Enable login via Google, Facebook, or other social platforms.</li>
        </ul>

        <!-- ==================== 17. CONCLUSION ==================== -->
        <h1 id="conclusion"><span class="section-num">17</span> Conclusion</h1>

        <p>Smart Mall demonstrates a complete, production-ready e-commerce platform built using modern web technologies. The system successfully addresses key challenges in traditional retail by providing a centralized digital marketplace accessible from any device.</p>

        <p>The platform delivers a comprehensive feature set including product browsing, category filtering, real-time search, multi-currency pricing, server-side shopping cart, secure checkout with Chapa payment integration, and a full-featured admin dashboard. Security measures including bcrypt password hashing, PDO prepared statements, CSRF protection, and role-based access control ensure the platform is safe for production use.</p>

        <p>The architecture&rsquo;s modular design and use of widely-supported open-source technologies (PHP, MySQL, Apache) make it easy to deploy, maintain, and extend. The planned future enhancements &mdash; including multi-language support, AI integration, product reviews, and a seller marketplace &mdash; provide a clear roadmap for evolving Smart Mall from a single-store platform into a full marketplace ecosystem.</p>

        <p>Smart Mall represents a practical, scalable solution for small to medium businesses seeking to establish or expand their online presence, particularly in markets where local payment methods and multi-currency support are essential.</p>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
