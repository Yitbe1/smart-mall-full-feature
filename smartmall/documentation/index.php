<?php
session_start();
$page_title = "SmartMall Documentation";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>SmartMall Documentation</title>
<style>
body{font-family:Georgia,'Times New Roman',serif;color:#1a1a1a;line-height:1.8;font-size:12pt;padding:60px 80px;max-width:1100px;margin:0 auto;}
h1{font-size:28pt;font-weight:700;margin:0 0 4px 0;}
h2{font-size:18pt;font-weight:700;margin:36px 0 12px 0;border-bottom:2px solid #1a1a1a;padding-bottom:6px;}
h3{font-size:14pt;font-weight:600;margin:28px 0 10px 0;}
h4{font-size:12pt;font-weight:600;margin:20px 0 8px 0;font-style:italic;}
p{margin-bottom:12px;text-align:justify;}
table{width:100%;border-collapse:collapse;margin:16px 0;font-size:11pt;}
table th{background:#1a1a1a;color:#fff;padding:8px 12px;text-align:left;}
table td{padding:8px 12px;border-bottom:1px solid #ddd;}
table tr:nth-child(even){background:#f9f9f9;}
code{background:#f0f0f0;padding:1px 5px;border-radius:2px;font-family:'Courier New',monospace;font-size:11pt;}
pre{background:#1a1a1a;color:#e0e0e0;padding:14px 18px;overflow-x:auto;margin:14px 0;font-family:'Courier New',monospace;font-size:10pt;line-height:1.5;}
ul,ol{margin:10px 0 10px 28px;}
li{margin-bottom:6px;}
.cover{text-align:center;padding:100px 40px 60px;margin-bottom:40px;border-bottom:3px solid #1a1a1a;}
.cover .subtitle{font-size:16pt;color:#555;margin-top:10px;}
.cover .meta{margin-top:30px;font-size:11pt;color:#666;}
.cover .meta span{display:inline-block;margin:0 16px;}
.signature{margin:40px 0;}
.placeholder{border:1px dashed #ccc;padding:30px;text-align:center;margin:16px 0;color:#888;font-style:italic;}
@media(max-width:768px){body{padding:20px;}}
@media print{a{color:#1a1a1a;text-decoration:none;}}
</style>
</head>
<body>

<div class="cover">
<h1>SmartMall</h1>
<p class="subtitle">E-Commerce Platform &mdash; Complete Documentation</p>
<div class="meta">
<span>Version 2.0</span>
<span>May 2026</span>
<span>smartmall.unaux.com</span>
</div>
</div>

<h2 id="declaration">Declaration</h2>
<p>I hereby declare that this project documentation titled <strong>"SmartMall &mdash; E-Commerce Platform"</strong> is my original work and has not been submitted for any academic award or examination in any other institution. All sources of information used in this documentation have been duly acknowledged.</p>
<div class="signature">
<p><strong>Name:</strong> _________________________________</p>
<p><strong>ID Number:</strong> ___________________________</p>
<p><strong>Signature:</strong> ___________________________</p>
<p><strong>Date:</strong> _______________________________</p>
<p><strong>Supervisor:</strong> ______________________</p>
<p><strong>Signature:</strong> ___________________________</p>
<p><strong>Date:</strong> _______________________________</p>
</div>

<h2 id="acknowledgment">Acknowledgment</h2>
<p>We extend our sincere gratitude to everyone who contributed to the successful completion of the SmartMall e-commerce platform. We thank our supervisor for the invaluable guidance, constructive feedback, and continuous support throughout the development process. We also appreciate the contributions of our colleagues and peers who participated in user testing and provided valuable insights that helped refine the platform's usability and functionality. Finally, we acknowledge the open-source community for the tools and frameworks that made this development possible &mdash; PHP, MySQL, Apache, and the Capacitor framework for mobile integration.</p>

<h2 id="abstract">Abstract</h2>
<p>SmartMall is a web-based e-commerce platform developed to provide a centralized digital marketplace where customers can browse products by category, manage a shopping cart, place orders, and track purchase history. The platform is built using HTML, CSS, JavaScript, PHP, and MySQL, following a modular architecture with clear separation of concerns. The system supports two primary user roles: Customers and Administrators. Customers can register, log in, browse products across categories including Fashion, Electronics, Home and Living, Beauty and Health, and Pharmacy, add items to a cart, and complete orders through either Chapa online payment or cash on delivery. Administrators have access to a dashboard for managing products, categories, inventory, and orders, as well as viewing sales statistics. Key technical features include a dual-currency system supporting USD and ETB with live exchange rates fetched from the exchangerate-api.com service; integration with the Chapa payment gateway for secure online transactions; CSRF-protected forms using 32-byte cryptographic tokens; PDO prepared statements for database security; bcrypt password hashing; and a Capacitor-based mobile application that wraps the web platform for Android deployment. The live demo is accessible at smartmall.unaux.com.</p>

<h2 id="introduction">1. Introduction</h2>

<h3>1.1 Background of the Study</h3>
<p>The rapid evolution of the internet has fundamentally transformed retail commerce. E-commerce platforms have become essential tools for businesses to reach customers beyond physical store locations. In Ethiopia and many developing markets, the adoption of online shopping is accelerating, yet many local businesses still lack an effective digital presence. Traditional shopping requires customers to visit physical stores, compare prices manually, and invest significant time finding desired products. This process is inefficient and limits both customer choice and merchant reach. A centralized e-commerce platform addresses these challenges by providing a single digital marketplace where multiple product categories are available for browsing, comparison, and purchase from any internet-connected device.</p>

<h3>1.2 Purpose of the System</h3>
<p>The primary purpose of SmartMall is to provide a centralized online marketplace that addresses the limitations of traditional retail. The platform enables product browsing and comparison across multiple categories; secure user registration and authentication with bcrypt password hashing; a persistent shopping cart stored in the database; multi-step order processing with stock validation and database transactions; an administrative dashboard for product, category, and order management; dual-currency support for both USD and ETB pricing; integration with the Chapa payment gateway for secure online transactions; and a Capacitor-based mobile application for Android deployment.</p>

<h3>1.3 Project Overview</h3>
<p>SmartMall is developed using a LAMP-stack architecture (Linux, Apache, MySQL, PHP) with a responsive frontend built in HTML5, CSS3, and vanilla JavaScript. The system follows a modular directory structure with separate areas for assets, admin functionality, API endpoints, configuration files, and includes for reusable components. The platform supports five product categories out of the box: Fashion and Apparel, Electronics and Gadgets, Home and Living, Beauty and Health, and Pharmacy. Each category can contain an unlimited number of products with images, descriptions, prices stored in USD, and stock quantities. The entire platform is deployed and publicly accessible at smartmall.unaux.com.</p>

<h2 id="problem">2. Problem Statement</h2>

<h3>2.1 The Current Situation</h3>
<p>In many local markets, shopping remains predominantly offline. Customers visit physical stores to browse products, compare prices, and make purchases. This traditional approach presents several challenges that limit both customer satisfaction and business growth. Customers face limited product visibility, as they can only see products available in stores they physically visit, missing out on better options elsewhere. Price comparison across multiple stores requires significant time and travel, and there is no centralized catalog where customers can view products from multiple categories and sellers in one place. Store owners rely on manual or disconnected methods to track inventory, leading to errors, overselling, and lost revenue. Shopping is also restricted to store operating hours with no after-hours purchase capability, and customers have no digital record of their purchases for reference, returns, or warranty claims.</p>

<h3>2.2 Identified Gaps in Existing Solutions</h3>
<p>A survey of existing solutions reveals several critical gaps in the local market. No central marketplace exists that aggregates multiple stores and categories in a single platform. Few local platforms support online payment integration, limiting most transactions to cash on delivery with its attendant risks and limitations. Most existing solutions lack mobile app access, resulting in poor mobile user experience on phones, which constitute the majority of local internet access points. Prices are typically shown in one currency only, creating confusion for international customers or diaspora users who think in foreign currencies. These gaps collectively create a fragmented shopping experience that discourages online purchasing and limits the growth of digital commerce in the region.</p>

<h3>2.3 How SmartMall Addresses These Gaps</h3>
<p>SmartMall addresses these challenges by providing a complete, centralized e-commerce solution. The platform is web-based and accessible from any device with a browser, eliminating the need for software installation. It supports multiple product categories within a single platform, providing a unified shopping experience. The Chapa payment gateway enables secure online transactions, while the dual-currency system (USD and ETB) with live exchange rates accommodates both local and international users. The Capacitor mobile wrapper provides a native app experience on Android devices, and the comprehensive admin dashboard gives administrators full control over products, categories, orders, and platform configuration.</p>

<h2 id="objectives">3. Project Objectives</h2>

<h3>3.1 General Objective</h3>
<p>To develop a fully functional, web-based e-commerce platform that enables customers to browse products, manage a shopping cart, and place orders, while allowing administrators to manage the platform through an intuitive dashboard &mdash; with dual-currency support, Chapa payment gateway integration, and a companion Capacitor mobile application.</p>

<h3>3.2 Specific Objectives</h3>
<p>The following specific objectives guided the development of SmartMall. A secure user registration and login system with bcrypt password hashing was implemented to protect user accounts. A category-based product browsing interface with search and filtering was developed to organize products and facilitate discovery. A fully functional shopping cart with AJAX-based add-to-cart operations and persistent database storage was built to support the complete purchasing workflow. Order placement within database transactions, including stock validation with row-level locking (SELECT ... FOR UPDATE), was implemented to ensure data integrity. An admin dashboard for product and category CRUD operations, order management, and sales statistics was created to enable efficient platform administration. The Chapa payment gateway was integrated for secure online transactions in test mode, with callback-based verification. A dual-currency display system supporting both USD (base) and ETB (display/payment), with live exchange rate fetching from exchangerate-api.com, was built. A Capacitor mobile app wrapper was created for Android deployment. CSRF token protection, PDO prepared statements, and bcrypt password hashing were implemented throughout the platform.</p>

<h2 id="scope">4. Scope and Limitations</h2>

<h3>4.1 Features Within Scope</h3>
<p>Product browsing with category-based filtering, keyword search using SQL LIKE queries, and image display. User accounts with registration, login, session management, and role-based access control. Shopping cart with add, remove, and quantity update operations, all stored persistently in the database. Order processing with stock validation, database transactions, and order history viewing. Admin dashboard enabling product CRUD, category management, order status management, and sales statistics. Dual-currency support for USD (base) and ETB (payment). Chapa payment gateway integration in test mode with callback verification. Capacitor mobile application for Android. Security measures including CSRF tokens, prepared statements, bcrypt hashing, and session regeneration on login.</p>

<h3>4.2 Features Outside Scope</h3>
<p>Multi-vendor or seller portal functionality is not included. Product reviews and ratings are not implemented. Real payment processing uses Chapa test endpoints; production API keys are required for live transactions. Email notifications use PHP's built-in mail() function and are limited; no SMTP integration is configured. An invoicing or PDF generation system is not included. Automated exchange rate updates rely on a free third-party API with rate limits. The mobile application is a Capacitor web wrapper, not a native build with platform-specific features such as push notifications or offline support.</p>

<h2 id="analysis">5. System Analysis</h2>

<h3>5.1 Actors</h3>
<p>The system recognizes two primary actors. The Customer is an end-user who registers, browses products, manages a shopping cart, places orders through Chapa or cash on delivery, views order history, and can cancel pending orders. Customers can browse products without logging in but must be authenticated to add items to the cart, proceed to checkout, or view order history. The Administrator manages the platform through the admin dashboard and is responsible for product and category CRUD operations, order status updates (pending to processing, shipped, delivered, or cancelled), viewing sales statistics, and managing currency settings. Administrator access is controlled by a role check on the session variable user_role.</p>

<h3>5.2 Data Flow</h3>
<p>Data flows through the system in a structured, layered manner. The customer interacts with the frontend interface, which issues HTTP requests to the PHP backend. The backend processes each request by executing the appropriate business logic: validating input, checking authentication and CSRF tokens, interacting with the MySQL database through PDO prepared statements, and returning responses either as rendered HTML or JSON for AJAX operations. The database stores all persistent data, including user accounts with bcrypt-hashed passwords, product and category records, cart items linked to user sessions, orders with shipping addresses, order items preserving purchase-time prices, and payment records with Chapa transaction references. Session data is managed server-side to maintain user state across requests. The Chapa payment gateway is called via cURL from the server side when customers initiate payment, and the callback mechanism updates payment and order status upon the customer's return from the Chapa hosted payment page.</p>

<div class="placeholder">[PLACEHOLDER: Data Flow Diagram showing interactions between Customer, Frontend, Backend, Database, and Chapa Gateway]</div>

<h2 id="requirements">6. Functional and Non-Functional Requirements</h2>

<h3>6.1 Functional Requirements</h3>
<p>The system provides a comprehensive set of functions organized by subsystem. The authentication subsystem allows users to register with their name, email, and password, enforcing a password policy requiring a minimum of eight characters with at least one uppercase letter, one lowercase letter, one digit, and one special character. Login verifies credentials against stored bcrypt hashes and regenerates the session ID to prevent session fixation. The browsing subsystem displays products in a responsive grid with images, names, and prices in the selected currency. Customers can filter products by category using a dropdown or button-based selector or by keyword through a search bar that queries both product names and descriptions using SQL LIKE operators. A sorting control allows ordering by newest first, price ascending, or price descending.</p>
<p>The shopping cart subsystem enables authenticated users to add products by sending a JSON payload to the add_to_cart.php AJAX endpoint, which validates stock availability and prevents overselling. The same product can be added multiple times, and the system increments the existing quantity rather than creating duplicate entries, capping the total at the available stock level. Users can view their cart at any time, update quantities through increment and decrement controls, and remove items. The cart total is recalculated automatically, applying a 10% VAT tax to produce the final total displayed in the user's selected currency.</p>
<p>The order processing subsystem creates orders within explicit database transactions. When a customer confirms checkout, the system re-verifies stock for each item using SELECT ... FOR UPDATE row-level locking to prevent race conditions, inserts the order record with the customer's shipping address, creates order_item records preserving the price at time of purchase, deducts stock quantities, and handles the cart cleanup differently depending on the payment method. For Chapa payments, the cart is preserved until the payment callback is verified; for cash on delivery, the cart is cleared immediately upon order creation. A payment record is created for every order regardless of payment method, storing the method, amount, currency, and transaction reference. If the Chapa initialization fails, the database transaction is rolled back entirely.</p>
<p>The administrative subsystem provides a dashboard displaying aggregate statistics including total product count, total stock units, total orders, total revenue (formatted in USD), and category count. Administrators can add products with a name, category selection, price in USD, image upload (restricted to jpg, jpeg, png, gif, and webp formats with a 5 MB maximum), description, and initial stock quantity. Products can be edited or deleted, with deletion cascading to clear associated images from the filesystem. Category management supports creation, renaming, and deletion (which cascades to delete all products within the category). Order management displays all orders sorted by payment status priority (paid first) and supports status transitions through the workflow of pending, processing, shipped, delivered, and cancelled.</p>

<h3>6.2 Non-Functional Requirements</h3>
<p>The security requirements mandate that all passwords must be hashed using bcrypt before storage. All database queries must use PDO prepared statements with emulated prepares disabled, ensuring true parameterized query execution. CSRF tokens generated from 32 bytes of cryptographic randomness via random_bytes() and bin2hex() must protect all form submissions. The session must be regenerated on login to prevent fixation attacks. Usability requirements specify that the interface must render correctly on screens from 320 pixels to 1920 pixels wide, and navigation should be intuitive with consistent placement of search, currency selector, and cart badge across all pages. Performance requirements specify that page load times should not exceed three seconds under normal load and database queries should complete within 500 milliseconds. The system should maintain 99% uptime during hosted operation. The frontend must function correctly on the two most recent major versions of Chrome, Firefox, Safari, and Edge browsers.</p>

<h2 id="users">7. Target Users and Personas</h2>

<h3>7.1 User Categories</h3>
<p>SmartMall serves two distinct user groups. Customers are individual consumers who visit the platform to browse products and make purchases. They range from tech-savvy young adults comfortable with online transactions to older users who may be new to e-commerce but are drawn by the convenience of home delivery. Administrators are platform managers responsible for maintaining the product catalog, processing orders, managing user accounts, and configuring platform settings. They require comprehensive tools for efficient administration but are not assumed to have advanced technical skills beyond basic computer literacy.</p>

<h3>7.2 Representative Personas</h3>
<p><strong>Hanna, aged 24, is a university student.</strong> She shops for clothing and consumer electronics online. Hanna uses her smartphone for most internet activities and expects a smooth, responsive mobile experience. She is price-sensitive and frequently compares prices across products and categories. Her primary goals are to find desired products quickly, compare prices easily, and complete purchases without encountering errors or confusing interfaces.</p>
<p><strong>Dawit, aged 38, is a small business owner.</strong> He manages inventory for his retail store and uses SmartMall to purchase wholesale supplies. Dawit uses a laptop for most online activities and values efficiency and reliability. His primary goals are to place orders confidently, track shipping status, and maintain records of his purchase history for accounting purposes.</p>
<p><strong>Genet, aged 45, is an administrative officer.</strong> She manages products and orders on the SmartMall platform for her organization. Genet is comfortable with basic computer operations but prefers simple, uncluttered interfaces. Her primary goals are to add and update product listings efficiently, process incoming customer orders promptly, and view accurate sales reports to track platform performance.</p>

<h2 id="architecture">8. System Architecture</h2>

<h3>8.1 Architecture Overview</h3>
<p>SmartMall follows a three-tier client-server architecture. The presentation tier consists of HTML, CSS, and vanilla JavaScript delivered to the client browser. The application tier comprises PHP scripts running on the Apache web server that handle business logic, request processing, session management, and server-side rendering. The data tier is the MySQL database, accessed exclusively through PDO prepared statements. The architecture is page-based, meaning each URL path corresponds directly to a PHP file that handles both logic and presentation, with shared components extracted into the includes/ directory.</p>

<h3>8.2 Directory Structure</h3>
<p>The actual project codebase is organized as follows. The root directory at /opt/lampp/htdocs/reference/ contains the primary entry points: config.php (the bootstrap file that starts the session, sets security headers, detects the base URL, and loads the database connection and currency helpers), index.php (the homepage with the product grid, category filter, and search), cart.php, checkout.php, login.php, register.php, logout.php, orders.php, order_confirmation.php, product.php, search.php, about.php, contact.php, add_to_cart.php (AJAX cart endpoint), set_currency.php (currency switch handler), and forgot_password.php and reset_password.php for password recovery. The admin/ directory contains dashboard.php, add_product.php, delete_product.php, manage_categories.php, and manage_orders.php. The api/ directory contains only a single endpoint, search.php, for live search autocomplete. The includes/ directory contains the shared components: db.php (PDO connection and CSRF helper functions), currency.php (the complete dual-currency system with live rate fetching), header.php (HTML head, nav bar, dark theme toggle, cart badge, search, currency selector), and footer.php (newsletter form, four-column footer, store stats). The assets/ directory holds CSS stylesheets, JavaScript files, and uploaded product images. The chapa_pay/ directory contains the Chapa configuration file with the API key and base URL. The smartmall-app/ directory contains the Capacitor mobile application source. The smartmall/documentation/ directory contains this documentation.</p>

<pre>
reference/
  config.php            # Bootstrap: session, URL detection, security headers
  index.php             # Homepage with product grid and category filter
  cart.php              # Shopping cart with quantity controls
  checkout.php          # Multi-step checkout with Chapa integration
  login.php             # Email/password login with bcrypt verification
  register.php          # Registration with password policy enforcement
  orders.php            # Customer order history and cancellation
  order_confirmation.php # Post-payment verification and confirmation
  add_to_cart.php       # AJAX JSON endpoint for cart operations
  set_currency.php      # Currency preference handler
  product.php           # Product detail page
  search.php            # Search results page
  forgot_password.php   # Token-based password reset request
  reset_password.php    # Password reset with token validation
  admin/
    dashboard.php       # Stats dashboard and product table
    add_product.php     # Product create and edit form
    delete_product.php  # POST-only product deletion
    manage_categories.php  # Category CRUD
    manage_orders.php   # Order listing and status management
  includes/
    db.php              # PDO connection + CSRF token helpers
    currency.php        # Dual-currency system with live API rates
    header.php          # HTML head, nav, dark theme, search, cart badge
    footer.php          # Footer with newsletter and store stats
  api/
    search.php          # Live search autocomplete (JSON)
  assets/
    css/                # Stylesheets
    js/                 # JavaScript files
    images/             # Uploaded product images
  chapa_pay/
    chapa-config.php    # Chapa API key and base URL
  smartmall-app/        # Capacitor mobile app
  documentation/        # This document
</pre>

<div class="placeholder">[PLACEHOLDER: System Architecture Diagram showing Client, Server, Database layers with technology labels]</div>

<h2 id="techstack">9. Technology Stack</h2>

<h3>9.1 Frontend Technologies</h3>
<p>The frontend is built with HTML5 for semantic page structure, CSS3 for responsive layout and visual styling with CSS custom properties (variables) for theming, and vanilla JavaScript for all client-side interactivity. No frontend frameworks such as React, Vue, or Angular are used, keeping the stack lightweight and eliminating unnecessary dependencies. The dark theme is implemented entirely through CSS custom properties stored in :root and [data-theme="dark"] selectors, toggled via JavaScript that persists the preference in localStorage under the key smartmall-theme. The system respects the user's operating system preference via the prefers-color-scheme media query as the initial default. Font Awesome provides iconography throughout the interface. Google Fonts supplies the Outfit and Inter typefaces.</p>

<h3>9.2 Backend Technologies</h3>
<p>PHP serves as the server-side scripting language, handling all business logic, session management, database interaction, API request processing, and page rendering. The system runs on Apache HTTP Server, which handles URL routing and serves static assets. The backend follows a page-based architecture in which each URL corresponds to a single PHP file that handles both request processing (validating input, running business logic, calling the database) and presentation (rendering HTML with PHP's include system for shared components). The bootstrap file, config.php, is included at the top of every page and handles session initialization, base URL auto-detection (supporting subfolder deployments such as /reference/), security header emission, and loading of the database connection and currency helpers.</p>

<h3>9.3 Database</h3>
<p>MySQL is the relational database management system, accessed exclusively through PHP Data Objects (PDO) with prepared statements. PDO is configured with emulated prepares disabled, ensuring that queries and parameter data are sent to the database server separately, providing genuine protection against SQL injection. The PDO connection is configured to throw exceptions on errors, use associative arrays as the default fetch mode, and target the utf8mb4 character set. The application uses a single shared PDO instance obtained through a global getDB() function.</p>

<h3>9.4 Mobile Integration</h3>
<p>The mobile application is built with Apache Capacitor, an open-source framework for building native mobile applications using standard web technologies. The Capacitor app consists of a minimal index.html that imports a JavaScript entry point (app.js). App.js imports the App, StatusBar, and SplashScreen plugins from @capacitor. On load, it hides the status bar and splash screen, then registers a back-button listener that calls window.history.back() if the WebView can navigate back, or App.exitApp() if at the root. It also listens for appUrlOpen events and navigates to smartmall.unaux.com URLs when deep links are received. The web application is loaded in a full-screen WebView, providing an app-like experience on Android without requiring a separate mobile codebase.</p>

<pre>
// smartmall-app/www/app.js
import { App } from '@capacitor/app';
import { StatusBar } from '@capacitor/status-bar';
import { SplashScreen } from '@capacitor/splash-screen';

StatusBar.hide();
SplashScreen.hide();

App.addListener('backButton', ({ canGoBack }) => {
    canGoBack ? window.history.back() : App.exitApp();
});

App.addListener('appUrlOpen', ({ url }) => {
    if (url.includes('smartmall.unaux.com/')) window.location.href = url;
});
</pre>

<h2 id="database">10. Database Design</h2>

<h3>10.1 Entity-Relationship Model</h3>
<p>The SmartMall database, named smartmall_db, consists of eight related tables that store all platform data. The users table stores customer and administrator accounts with bcrypt-hashed passwords, email addresses (unique), names, and role assignments. The categories table defines product categories with names, URL-friendly slugs, and up to three slide images per category. The products table stores product information including name, description, price in USD, stock quantity, image filename, and a foreign key linking to categories with cascading delete. The cart table stores temporary shopping cart entries, each linking a user to a product with a quantity, with cascading deletes on both foreign keys. The orders table stores placed orders with customer shipping details, totals, status tracking, and payment method. The order_items table stores individual line items for each order, preserving the product name and price at the time of purchase. The payments table tracks payment transactions for each order with method, amount, currency, Chapa transaction reference, status, and timestamp. The password_resets table stores password reset tokens with email, 32-byte random token, and one-hour expiration timestamp.</p>

<div class="placeholder">[PLACEHOLDER: Entity-Relationship Diagram showing all 8 tables, their columns, and foreign key relationships]</div>

<h3>10.2 Complete Schema</h3>
<p>The following SQL statements define the database schema extracted from the actual codebase analysis.</p>

<pre>
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `image1` varchar(255) DEFAULT NULL,
  `image2` varchar(255) DEFAULT NULL,
  `image3` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`product_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`category_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`cart_id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`)
    REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `zip` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Ethiopia',
  `status` enum('pending','processing','shipped','delivered','cancelled')
    NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`)
    REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`)
    REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `method` varchar(50) DEFAULT NULL,
  `status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `tx_ref` varchar(100) DEFAULT NULL,
  `chapa_response` longtext DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`)
    REFERENCES `orders` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `password_resets` (
  `reset_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`reset_id`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
</pre>

<h2 id="features">11. Platform Features</h2>

<h3>11.1 User Authentication System</h3>
<p>The authentication system is built on bcrypt password hashing and server-side sessions. During registration, the system validates that the name is at least two characters, the email passes PHP's filter_var(FILTER_VALIDATE_EMAIL) check, and the password satisfies a four-part policy: at least eight characters, at least one uppercase letter (preg_match('/[A-Z]/')), at least one lowercase letter (preg_match('/[a-z]/')), at least one digit (preg_match('/[0-9]/')), and at least one special character (preg_match('/[^A-Za-z0-9]/')). The password confirmation must match exactly. If validation passes, the system checks for duplicate emails and, if none exist, hashes the password with PHP's password_hash() using the PASSWORD_BCRYPT algorithm, which automatically generates a unique 22-character random salt and produces a 60-character hash. The user record is created with the default role of "customer".</p>
<p>During login, the submitted email is looked up and the password is verified against the stored bcrypt hash using password_verify(), which is timing-safe. On successful authentication, the session ID is regenerated with session_regenerate_id(true) to prevent session fixation attacks. The user ID, name, email, and role are stored in the session. A redirect parameter (redirect) is supported, allowing authenticated users to be sent back to the page they were trying to access before login. The redirect URL is validated against a whitelist regex to prevent open redirect vulnerabilities. A flash message ("Welcome back, ...") is stored in the session to display on the redirected page.</p>

<h3>11.2 Product Browsing and Search</h3>
<p>The homepage displays all available products in a responsive grid with product images, names, and prices formatted in the user's selected currency. Products are organized into categories, and the page provides both a dropdown menu and visual category buttons for filtering. When a category is selected, the query appends a WHERE clause filtering by category_id. A search bar enables keyword-based lookup against both product names and descriptions using SQL LIKE operators with wildcards. A sort control allows ordering by newest first, price ascending, or price descending. Each product card links to a detailed product page (product.php) that displays the full description, price in both USD and ETB, stock status indicator (with a visual badge for low stock), and an add-to-cart form. The homepage also features a hero section, category thumbnail cards with accent colors mapped per slug (fashion: pink, electronics: blue, home: green, beauty: amber), and a product spotlight section.</p>

<h3>11.3 Shopping Cart</h3>
<p>The shopping cart uses a server-centric architecture. When an authenticated user clicks "Add to Cart," the frontend sends a JSON payload via fetch() to add_to_cart.php. The payload contains the product_id and quantity. The server validates that the user is logged in, that the product exists, and that the requested quantity does not exceed available stock. If the product already exists in the user's cart, the quantity is incremented but capped at the stock level. If it is a new entry, a cart row is inserted. The response is a JSON object with success, message, and cart_count fields. The cart count badge in the navigation header is updated after each successful add by re-fetching from the server.</p>
<p>The cart page (cart.php) displays all cart items with product images, names, unit prices, quantity controls (increment and decrement buttons), and line totals. Each item shows the current stock level for reference. Below the item list, the subtotal is computed at the server level, a 10% VAT tax is applied, and the order total is displayed in both the selected display currency and, if applicable, the ETB equivalent for Chapa payment. A sticky notification banner confirms cart actions. If the cart becomes empty, a message is shown with a link to continue shopping.</p>

<pre>
// add_to_cart.php &mdash; AJAX cart endpoint
$data       = json_decode(file_get_contents('php://input'), true);
$product_id = (int)($data['product_id'] ?? 0);
$quantity   = (int)($data['quantity'] ?? 1);

// Stock validation
$stmt = $pdo->prepare("SELECT stock FROM products WHERE product_id = :product_id");
$stmt->execute([':product_id' => $product_id]);
$product = $stmt->fetch();
if ($product['stock'] < $quantity) {
    echo json_encode(['success' => false,
        'message' => 'Not enough stock available']);
    exit();
}

// Increment existing or insert new
$stmt = $pdo->prepare("SELECT cart_id, quantity FROM cart
    WHERE user_id = :uid AND product_id = :pid");
$stmt->execute([':uid' => $user_id, ':pid' => $product_id]);
$cart_item = $stmt->fetch();
if ($cart_item) {
    $new_qty = min($cart_item['quantity'] + $quantity, $product['stock']);
    // UPDATE cart SET quantity = :qty WHERE cart_id = :cart_id
} else {
    // INSERT INTO cart (user_id, product_id, quantity) VALUES (...)
}
</pre>

<h3>11.4 Order Processing and Checkout</h3>
<p>Checkout (checkout.php) begins by loading the user's cart items via a JOIN between cart and products. If the cart is empty, the user is redirected back to the cart page. The checkout form collects the customer's billing address in a structured layout: first name, last name, email (pre-filled from the session), street address, city, state or region, ZIP or postal code, and country (defaulting to Ethiopia). Three payment methods are offered: Chapa Pay (online payment via the Chapa gateway), bank transfer, and cash on delivery.</p>
<p>When the order is placed, the system enters an explicit database transaction. First, stock is re-validated for every cart item using SELECT ... FOR UPDATE, which acquires row-level locks and prevents race conditions. If any item has insufficient stock, the transaction is rolled back and an error message is shown. A payment record is created for all payment methods. For Chapa payments, an ETB exchange rate is fetched; if unavailable, the transaction is rolled back. The system generates a transaction reference in the format ORD-{order_id}-YYYYMMDD. The Chapa API is then called via cURL with the payment payload including amount in ETB, currency, customer email, transaction reference, callback URL, and return URL. The cURL request uses SSL peer verification and a 30-second timeout. If the Chapa API responds with HTTP 200 and a success status containing a checkout URL, the database transaction is committed and the customer is redirected to Chapa's hosted payment page. If the API call fails, the raw HTTP code, cURL error, and response body are displayed for debugging, and the database transaction is automatically rolled back. For cash on delivery, the cart is cleared and the order is immediately confirmed.</p>

<pre>
// Checkout stock re-validation with row-level locking
$pdo->beginTransaction();
foreach ($cart_items as $item) {
    $stmt = $pdo->prepare("SELECT stock FROM products
        WHERE product_id = :product_id FOR UPDATE");
    $stmt->execute([':product_id' => $item['product_id']]);
    $current = $stmt->fetch();
    if (!$current || $current['stock'] < $item['quantity']) {
        $pdo->rollBack();
        // Show error: item out of stock
    }
}
// ... create order, payment record, order_items, deduct stock ...
// For Chapa: don't clear cart yet, redirect to payment page
// For COD: clear cart, show confirmation
</pre>

<h3>11.5 Payment Verification</h3>
<p>The order confirmation page (order_confirmation.php) is the most complex page in the system, handling multiple payment states. When a customer arrives at this page after a Chapa payment, the system checks the payment status. If the status is already "paid", the confirmation page is displayed directly. If the status is "pending" and the payment method is Chapa, the system immediately calls the Chapa verification API at https://api.chapa.co/v1/transaction/verify/{tx_ref} with the secret key as a Bearer token. If the verification confirms success (both top-level status and data.status are "success"), the payment and order records are updated: payments.status becomes "paid", paid_at is set to the current timestamp, the full Chapa response JSON is stored in chapa_response, the order status advances to "processing", and the user's cart is cleared. If the verification fails but the Chapa secret key contains "TEST" (indicating test mode), the system applies a fallback: it assumes the payment succeeded since test transactions complete immediately on the Chapa sandbox. This test-mode override writes a synthetic success response into the payment record and proceeds with the order confirmation. If verification genuinely fails and the key is not a test key, the payment status is set to "failed", the order status is set to "cancelled", and the product stock is restored by iterating over the order_items records.</p>
<p>If the payment is still pending after the verification attempt (which should not normally occur), a polling page is displayed that auto-refreshes every three seconds with a countdown timer. This page provides a manual refresh button and a link back to checkout. If the payment has failed, a failure message is displayed with a "Try Again" button that links back to checkout. For successful orders, the full confirmation page shows the order ID zero-padded to six digits, the shipping address, order details (payment method, status, total), itemized list with product images from get_product_image_url() helper, subtotal (computed by dividing total by 1.1), 10% VAT, grand total, and a "What's Next" collapsible section with the steps: payment processing, order preparation, delivery, and enjoyment.</p>

<h3>11.6 Dual-Currency System</h3>
<p>The currency system is defined in includes/currency.php and is fundamentally different from the typical hardcoded-rate approach. All product prices in the database are stored in US Dollars (USD), which serves as the base currency. The system defines a constant SMARTMALL_BASE_CURRENCY set to 'USD'. Two currencies are supported: USD and ETB. A smartmall_selected_currency() function reads the user's preference from the session key currency, defaulting to USD if not set, and validates it against the supported list. The set_currency handler (set_currency.php) updates this session value.</p>
<p>Exchange rates are sourced live from the open exchange rate API at https://open.er-api.com/v6/latest/USD. The system implements a caching layer using a JSON file stored in the system temporary directory. The flow is as follows: smartmall_exchange_data() first checks the cache file. If a valid cache exists (the expires_at timestamp is in the future), the cached rates are returned immediately. If the cache is expired or missing, smartmall_fetch_exchange_rates() is called, which uses cURL with an 8-second timeout and SSL peer verification to fetch fresh rates from the API. If cURL is unavailable, it falls back to file_get_contents() with a stream context. The API response is validated by checking that result equals "success", base_code equals "USD", and rates.ETB is present. On success, the data is written to the cache file with a file lock (LOCK_EX) and returned. If the API call fails, the function returns null, and smartmall_exchange_data() falls back to any existing stale cache. If no cache exists at all, a fallback array is returned with an ETB rate of 0.0, which the calling code must handle gracefully.</p>
<p>The smartmall_convert_money() function multiplies a USD amount by the ETB rate to produce an ETB amount. The smartmall_format_money() function returns the formatted price: for ETB, the output is "ETB {amount}" with two decimal places; for USD, the output is "${amount}". The checkout system calls smartmall_exchange_rate('ETB') to obtain the current rate for Chapa payment initialization. If the rate is zero or unavailable, the checkout transaction is rolled back with an explanatory error message. This design ensures that Chapa payments in ETB are always based on current exchange rate data.</p>

<pre>
// Currency system core functions
function smartmall_fetch_exchange_rates(): ?array {
    $ch = curl_init('https://open.er-api.com/v6/latest/USD');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $raw = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode !== 200) return null;

    $payload = json_decode($raw, true);
    if ($payload['result'] !== 'success' || !$payload['rates']['ETB'])
        return null;

    return [
        'base' => 'USD',
        'rates' => ['USD' => 1.0, 'ETB' => (float)$payload['rates']['ETB']],
        'fetched_at' => time(),
        'expires_at' => $payload['time_next_update_unix'] ?? (time() + 86400),
    ];
}

function smartmall_convert_money(float $amountUsd, ?string $currency = null): float {
    if ($currency === 'USD') return $amountUsd;
    $rate = smartmall_exchange_rate('ETB');
    return $rate > 0 ? $amountUsd * $rate : $amountUsd;
}
</pre>

<h3>11.7 Administrator Dashboard</h3>
<p>The admin dashboard (admin/dashboard.php) provides a comprehensive view of the platform's operational state. Access is controlled by a session role check: if the user is not logged in or has a role other than "admin", they are redirected to the homepage. The dashboard displays four statistics cards in a responsive grid: total products (SELECT COUNT(*) FROM products), total categories (SELECT COUNT(*) FROM categories, linked to the category management page), total orders (SELECT COUNT(*) FROM orders), and total revenue (SELECT SUM(total_price) FROM orders, formatted in USD via smartmall_format_money()). Each card has a distinct gradient color scheme.</p>
<p>Below the statistics, a product management table displays all products with their images (rendered through the get_product_image_url() helper, which handles both local uploads/paths and external URLs), names, category names (via LEFT JOIN with categories), prices (formatted in the selected currency), stock quantities, and action buttons for editing and deletion. The edit button links to add_product.php?product_id=X with the product ID pre-populated. The delete button submits a POST form to delete_product.php with a CSRF token and a JavaScript confirm() dialog. Deletion removes the product image from the filesystem and cascades to remove cart entries. If no products exist, an empty state is shown with a prompt to add the first product.</p>
<p>The category management page (admin/manage_categories.php) allows creating, renaming, and deleting categories. Each category can have up to three slide images for display on the homepage hero section. The order management page (admin/manage_orders.php) lists all orders sorted by payment status priority: paid orders appear first, followed by pending, then failed. The query uses a GROUP BY with GROUP_CONCAT to aggregate order items data into a pipe-delimited string containing product names, images, quantities, and prices. Administrators can update order statuses through a dropdown and submit button, with each update logged and confirmed via flash messages.</p>

<h3>11.8 Security Architecture</h3>
<p>SmartMall's security implementation follows a defense-in-depth approach across multiple layers. SQL injection is prevented through the exclusive use of PDO prepared statements with emulated prepares disabled (PDO::ATTR_EMULATE_PREPARES => false). This configuration ensures that the database driver uses native server-side prepared statements, where the query structure and parameter data are transmitted separately, making it structurally impossible for user input to alter SQL syntax. All user-generated content displayed in HTML is encoded with htmlspecialchars() using the ENT_QUOTES flag and UTF-8 character set before output.</p>
<p>Cross-site request forgery is mitigated through a CSRF token system defined in includes/db.php. On each session, a 64-character hexadecimal token is generated from 32 random bytes via bin2hex(random_bytes(32)). This token is stored in the session and embedded in every form as a hidden input field through the csrf_field() helper function. On POST submission, the csrf_verify() function compares the submitted token against the session token using hash_equals() for timing-attack-safe comparison. Mismatches result in an immediate 403 response. Session security is reinforced by regenerating the session ID with session_regenerate_id(true) on every successful login, preventing session fixation. Security headers are set in config.php: X-Content-Type-Options: nosniff prevents MIME-type sniffing, and X-Frame-Options: SAMEORIGIN prevents clickjacking by restricting framing to the same origin. Cache control headers (no-store, no-cache) are applied to session-dependent pages to prevent sensitive data from being cached in the browser. The login form also implements a password visibility toggle for usability, but the input type defaults to "password", maintaining security unless the user explicitly toggles it.</p>

<h3>11.9 Mobile Application</h3>
<p>The mobile application is a Capacitor web wrapper located in the smartmall-app/ directory. It consists of three files: index.html (a minimal HTML shell with viewport meta tags and a script module import), app.js (the Capacitor integration code), and manifest.json (the PWA manifest). The app.js file imports the App, StatusBar, and SplashScreen plugins from @capacitor. When the app launches, it hides the native status bar and splash screen. A back-button listener is registered that calls window.history.back() if the WebView has navigation history, or App.exitApp() if the user is at the root page. An appUrlOpen listener intercepts deep links and navigates to smartmall.unaux.com URLs, enabling push notification deep linking in future iterations. The Capacitor app is built and deployed as an APK for Android, using the standard Capacitor build pipeline (npx cap build android).</p>

<h3>11.10 Password Reset</h3>
<p>The password reset flow (forgot_password.php and reset_password.php) implements a token-based approach. The user submits their email address. If the email exists in the users table, a 64-character hexadecimal token is generated from 32 random bytes via bin2hex(random_bytes(32)). The token, along with the email and a one-hour expiration timestamp, is stored in the password_resets table. A password reset link is constructed as {base_url}/reset_password.php?token={token}. Since the system does not have SMTP configured for production email delivery, the reset link is written to a temporary file at {sys_temp_dir}/smartmall_reset_links.txt for development access, and the link is also stored in the session under reset_link for display on the confirmation page. PHP's mail() function is called with the reset link, but its delivery depends on the server's mail configuration being active. The reset_password.php page validates the token by checking that it exists in the password_resets table and that the expires_at timestamp has not passed. If valid, the user can set a new password, which is hashed with bcrypt and stored, after which the reset token is deleted from the database.</p>

<h2 id="implementation">12. Implementation Details</h2>

<h3>12.1 Database Connection and PDO Configuration</h3>
<p>The database connection is established in includes/db.php using PHP Data Objects. The connection targets the smartmall_db database on localhost with the root user (default for XAMPP development). The Data Source Name (DSN) format is "mysql:host=localhost;dbname=smartmall_db;charset=utf8mb4". PDO options configure exception error mode, associative array fetch mode, and disabled emulated prepares. A getDB() function returns the global PDO instance for use throughout the application. In production, the connection credentials must be updated to match the hosting environment.</p>

<pre>
$host    = 'localhost';
$db_name = 'smartmall_db';
$db_user = 'root';
$db_pass = '';
$dsn = 'mysql:host=' . $host . ';dbname=' . $db_name . ';charset=utf8mb4';
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, $db_user, $db_pass, $options);
</pre>

<h3>12.2 CSRF Token Implementation</h3>
<p>Cross-site request forgery protection is implemented through three functions. The csrf_token() function generates or retrieves the session token: it first checks if a csrf_token exists in the session; if not, it creates one by calling bin2hex(random_bytes(32)), producing a 64-character hexadecimal string. The csrf_field() function outputs a hidden HTML input element containing the CSRF token, properly encoded with htmlspecialchars(). The csrf_verify() function checks POST submissions by retrieving the submitted token from $_POST['csrf_token'] and comparing it against the stored session token using hash_equals(), which performs a constant-time comparison to prevent timing attacks. If the tokens do not match or the submitted token is empty, the script terminates with HTTP 403 and an error message.</p>

<pre>
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function csrf_verify(): void {
    $submitted = $_POST['csrf_token'] ?? '';
    $expected  = $_SESSION['csrf_token'] ?? '';
    if (!$submitted || !hash_equals($expected, $submitted)) {
        http_response_code(403);
        die('Invalid or missing security token.');
    }
}
</pre>

<h3>12.3 Image Upload Handling</h3>
<p>Product image uploads in the admin panel are validated for file type (extensions permitted: jpg, jpeg, png, gif, webp) and file size (maximum 5 MB). The filename is generated using uniqid() with the original extension extracted via pathinfo(). The file is moved from the temporary upload directory to the uploads/ folder. The get_product_image_url() helper function in includes/db.php handles display: if the image path is an external URL (starting with http:// or https://), it is returned as-is. Otherwise, the function prepends the appropriate path prefix, accounting for whether the current script is in the admin/ subdirectory (using ../) or the root directory.</p>

<h3>12.4 Dark Theme Implementation</h3>
<p>The dark theme is implemented through CSS custom properties. The <html> element's data-theme attribute is set either by a synchronous script in the <head> (to prevent flash-of-unstyled-content) that reads from localStorage key smartmall-theme, or by default using the prefers-color-scheme media query. A toggle button in the header calls a JavaScript function that sets document.documentElement.dataset.theme to "dark" or "light" and persists the choice to localStorage. All color references throughout the CSS use var(--primary-color), var(--surface), var(--bg-light), and similar custom properties that change their values based on the theme attribute.</p>

<h3>12.5 Chapa Payment Configuration</h3>
<p>The Chapa payment configuration is stored in chapa_pay/chapa-config.php with two constants. CHAPA_SECRET_KEY is set to the test key "CHASECK_TEST-aF7ZWVLHJRP8rFpNG4V7rpDveopvXt2D", which uses the "TEST" prefix to identify test mode. CHAPA_API_URL is set to "https://api.chapa.co/v1". The checkout process uses this key for both payment initialization and subsequent verification. The test mode is detected by checking if the key contains "TEST", which triggers the fallback assumption of payment success when the user returns from the Chapa sandbox, since test transactions complete immediately without real payment processing.</p>

<h3>12.6 Bootstrap and Configuration Flow</h3>
<p>The config.php bootstrap file is the first file included by most pages. It starts the output buffer to catch any stray whitespace or BOM characters that might prevent header redirects from working. It initializes the session if none exists. It auto-detects the base URL by combining the protocol (checking HTTPS and X-Forwarded-Proto headers), the HTTP host, and the script's directory path, which allows the application to function correctly whether deployed at the root of a domain or in a subdirectory such as /reference/. It then loads includes/db.php (which in turn loads includes/currency.php) and defines a redirect() helper function. Finally, it emits security headers: X-Content-Type-Options: nosniff and X-Frame-Options: SAMEORIGIN. There is no closing PHP tag to prevent accidental whitespace output.</p>

<h2 id="methodology">13. Development Methodology</h2>
<p>SmartMall was developed following an iterative approach with structured phases. The requirements phase involved analyzing the limitations of existing local e-commerce solutions and defining the functional and non-functional requirements documented in this report. The design phase produced the database schema, system architecture, and user interface layout. The implementation phase proceeded in iterative cycles, beginning with the database layer and authentication system, followed by the product catalog and browsing interface, then the cart and checkout system with payment integration, and finally the admin dashboard and mobile wrapper. Each cycle included integration testing to verify that new code worked correctly with existing components. The testing phase covered unit testing of individual functions such as currency conversion and CSRF verification, integration testing of database transactions and payment flows, security testing of authentication and input validation, and user acceptance testing with representative users. The deployment phase configured the live environment at smartmall.unaux.com with Apache and MySQL.</p>

<h2 id="testing">14. Testing and Quality Assurance</h2>

<h3>14.1 Authentication Testing</h3>
<p>Registration testing verified that the system accepts valid data and creates accounts with properly hashed passwords, rejects duplicate emails with an appropriate error, enforces all four password policy requirements (minimum eight characters, uppercase, lowercase, digit, special character), and handles missing required fields. Login testing confirmed that valid credentials create a properly initialized session with regenerated ID, invalid credentials show a generic "Invalid email or password" message without revealing whether the email exists, and session logout properly destroys the session data. Role-based access control was verified by confirming that unauthenticated requests to admin pages are redirected to the homepage and that customer-role sessions cannot access admin functionality.</p>

<h3>14.2 Cart and Checkout Testing</h3>
<p>Cart testing verified that adding a product increments the cart count, adding a duplicate product increments quantity without creating duplicate entries, the quantity is capped at the available stock level, quantity decrement and removal work correctly, the cart persists across browser sessions for logged-in users, and the empty cart state displays correctly. Checkout testing confirmed that an empty cart cannot proceed to order placement, that all shipping address fields are collected and stored correctly, that stock is re-validated at the time of order placement, that the database transaction correctly rolls back all changes if any step fails (such as a Chapa API error), and that the 10% VAT is calculated accurately.</p>

<h3>14.3 Payment Flow Testing</h3>
<p>Chapa payment testing verified that the API initialization call is made with the correct payload including transaction reference, amount in ETB, callback URL, and return URL. The verification callback was tested by simulating Chapa sandbox responses. The test-mode fallback (auto-assuming success when the key contains "TEST") was verified to handle the case where the user returns from the Chapa sandbox but the verification API does not immediately reflect the payment. Failure scenarios tested included Chapa API timeout, non-200 HTTP response, missing checkout_url in the response, and invalid API key. In all failure cases, the database transaction was confirmed to roll back completely, leaving no orphan records.</p>

<h3>14.4 Currency System Testing</h3>
<p>The currency conversion system was tested for correct behavior in all caching states: fresh cache available (returns cached rates), expired cache with API available (fetches new rates and updates cache), expired cache with API unavailable and stale cache present (returns stale cache with stale flag), and no cache with API unavailable (returns fallback with zero rate, which causes the checkout to block Chapa payment with an explanatory message). The smartmall_format_money() function was tested for both USD and ETB formatting, confirming that USD amounts are prefixed with "$" and ETB amounts with "ETB".</p>

<h3>14.5 Admin Functionality Testing</h3>
<p>Admin testing confirmed that the dashboard statistics accurately reflect the current database state, including product count, order count, and total revenue. Product creation, editing, and deletion were verified end-to-end, including image upload with format and size validation. The order management page was confirmed to sort paid orders before pending orders, and status updates persisted correctly. Category management operations, including creation, renaming, and cascading deletion, all functioned as expected.</p>

<h2 id="security">15. Security Architecture</h2>

<h3>15.1 Defensive Layers</h3>
<p>SmartMall's security architecture implements protection against the most common web application threats. SQL injection is prevented by PDO prepared statements with emulated prepares disabled, meaning SQL query structures and parameter data are never concatenated. The database connection file explicitly states that the real error is logged server-side and never exposed to the browser, with a generic error message shown to users in case of database failure.</p>
<p>Password security is handled by PHP's password_hash() with the bcrypt algorithm, which automatically generates unique salts per password. Passwords are never stored in plain text and never logged. The verification uses password_verify() which is timing-safe. Session security features include ID regeneration on login, HttpOnly cookies (enabled by default in modern PHP), and SameSite attribute configuration.</p>
<p>XSS prevention is handled through consistent use of htmlspecialchars() with ENT_QUOTES on all dynamic output, encoding both single and double quotes. CSRF protection is implemented via the token system described in section 12.2. File upload security restricts uploads to image formats only (jpg, jpeg, png, gif, webp) with a 5 MB maximum, and uses uniqid() based filenames to prevent path traversal and overwrite attacks.</p>

<h2 id="deployment">16. Deployment Guide</h2>

<h3>16.1 Local Development Setup</h3>
<p>To run SmartMall in a local development environment, install XAMPP (or equivalent LAMP stack such as WAMP on Windows or MAMP on macOS). Clone or copy the project files to the web server's document root, preserving the entire directory structure including the reference/ parent folder. Create a MySQL database named smartmall_db and execute the SQL schema from section 10.2 to create all eight tables. No modification to database credentials is needed for XAMPP default configuration (localhost, root, empty password). Start the Apache and MySQL services and access the application at http://localhost/reference/. The application will auto-detect the base URL and configure itself.</p>

<h3>16.2 Production Deployment</h3>
<p>For production deployment, upload all project files to a PHP-capable web host (PHP 7.4 or higher, MySQL 5.7 or higher). Create a MySQL database through the hosting control panel and import the SQL schema. Update the database credentials in includes/db.php (change host, db_user, and db_pass to match the production environment). Configure the document root to point to the project directory, or deploy to a subdirectory and let the auto-detection in config.php handle URL resolution. For the Chapa payment integration, obtain live API keys from the Chapa dashboard and replace the test key in chapa_pay/chapa-config.php. Update the callback and return URLs in checkout.php from http://localhost/reference/ to the production domain. For the Capacitor mobile app, build the APK using the Capacitor CLI (npx cap build android) and distribute through standard channels.</p>

<h3>16.3 Default Credentials and Initial Data</h3>
<p>After importing the database schema, the system has no default user accounts. An administrator account must be created through the registration form, and then the role must be manually updated to "admin" in the database through phpMyAdmin or a SQL query: UPDATE users SET role = 'admin' WHERE email = 'admin@example.com'. Sample categories and products can be added through the admin dashboard after logging in as an administrator. The initial currency exchange rate will be fetched automatically from the open exchange rate API on the first request that requires a conversion.</p>

<h2 id="maintenance">17. Maintenance Plan</h2>

<h3>17.1 Routine Maintenance Tasks</h3>
<p>Daily maintenance includes monitoring PHP and Apache error logs for database connection errors, failed Chapa API calls, and any unusual error patterns. Weekly tasks include verifying that the exchange rate caching mechanism is functioning (checking the cache file in the system temp directory) and reviewing the product catalog for accuracy. Monthly tasks include checking for PHP and MySQL security patches, reviewing the Chapa API for any endpoint changes, and running a manual end-to-end test of the complete order flow including registration, product browsing, cart operations, checkout, and payment verification. Quarterly tasks include performing a full security review, updating the password policy if needed, and reviewing inactive user accounts for cleanup.</p>

<h3>17.2 Backup Strategy</h3>
<p>The MySQL database should be backed up daily using mysqldump or the hosting provider's backup tools. A retention period of at least 30 days should be maintained. Application code is version-controlled externally and can be restored from the repository. The Chapa configuration file (chapa-config.php) containing the secret key should be excluded from version control in production and stored securely.</p>

<pre>
#!/bin/bash
mysqldump -u root smartmall_db > /backups/smartmall_$(date +%Y%m%d).sql
gzip /backups/smartmall_*.sql
find /backups/ -name "*.sql.gz" -mtime +30 -delete
</pre>

<h2 id="conclusion">18. Conclusion</h2>
<p>The SmartMall e-commerce platform successfully demonstrates a complete, functional online shopping system built on the LAMP stack. The platform delivers all core e-commerce capabilities: user authentication with bcrypt password hashing and CSRF protection, product browsing with category-based filtering and keyword search, persistent shopping cart with AJAX operations and server-side stock validation, order processing within database transactions with row-level locking, dual-currency display with live exchange rate fetching from exchangerate-api.com, Chapa payment gateway integration with callback-based verification in test mode, a comprehensive admin dashboard with sales statistics and full product/order management, a debug protection gate for development access, and a Capacitor mobile wrapper for Android deployment.</p>
<p>Throughout the development process, emphasis was placed on security through bcrypt password hashing, PDO prepared statements with disabled emulated prepares, CSRF tokens from cryptographic randomness, session regeneration on login, and security headers. The modular directory structure with separate includes for database access, currency management, and shared page components supports maintainability and future extension.</p>
<p>The project achieved its primary objectives of creating a centralized digital marketplace that addresses the limitations of traditional retail in the target market. Among the more distinctive technical accomplishments is the currency system, which uses a live API with file-based caching and graceful fallback to stale data when the API is unreachable, ensuring that price conversions remain available even during network outages. The platform is deployed and accessible at smartmall.unaux.com, demonstrating its viability as a real-world e-commerce solution.</p>

<h2 id="references">19. References and Appendices</h2>

<h3>19.1 Chapa Payment Configuration</h3>
<table>
<tr><th>Parameter</th><th>Value</th></tr>
<tr><td>Secret Key (Test)</td><td>CHASECK_TEST-aF7ZWVLHJRP8rFpNG4V7rpDveopvXt2D</td></tr>
<tr><td>API Base URL</td><td>https://api.chapa.co/v1</td></tr>
<tr><td>Currency</td><td>ETB</td></tr>
<tr><td>Mode</td><td>Test (sandbox)</td></tr>
</table>

<h3>19.2 Exchange Rate API Configuration</h3>
<table>
<tr><th>Parameter</th><th>Value</th></tr>
<tr><td>API URL</td><td>https://open.er-api.com/v6/latest/USD</td></tr>
<tr><td>Base Currency</td><td>USD</td></tr>
<tr><td>Cache Location</td><td>{sys_temp_dir}/smartmall_exchange_usd.json</td></tr>
<tr><td>Cache Duration</td><td>Until time_next_update_unix (typically 24h)</td></tr>
<tr><td>Fallback Rate</td><td>1 USD = 55 ETB (emergent fallback only)</td></tr>
</table>

<h3>19.3 Password Policy</h3>
<table>
<tr><th>Rule</th><th>Validation</th></tr>
<tr><td>Minimum length</td><td>8 characters</td></tr>
<tr><td>Uppercase letter</td><td>preg_match('/[A-Z]/')</td></tr>
<tr><td>Lowercase letter</td><td>preg_match('/[a-z]/')</td></tr>
<tr><td>Digit</td><td>preg_match('/[0-9]/')</td></tr>
<tr><td>Special character</td><td>preg_match('/[^A-Za-z0-9]/')</td></tr>
</table>

<h3>19.4 References</h3>
<p>PHP Documentation. PHP Manual. php.net. 2024.</p>
<p>MySQL Documentation. MySQL Reference Manual. dev.mysql.com. 2024.</p>
<p>Apache HTTP Server Documentation. httpd.apache.org. 2024.</p>
<p>Chapa Payment Gateway API. chapa.co/developer. 2025.</p>
<p>Capacitor Framework Documentation. capacitorjs.com/docs. 2025.</p>
<p>OWASP Top Ten Web Application Security Risks. owasp.org. 2024.</p>
<p>Exchangerate API. open.er-api.com. 2025.</p>

<p style="text-align:center;margin-top:60px;color:#888;font-size:11pt;">&mdash; End of Documentation &mdash;</p>

</body>
</html>
