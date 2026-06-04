# CHAPTER 3: SYSTEM DESIGN

This chapter presents the architectural design of the Smart Mall e-commerce platform, covering the system architecture, user interface design, navigation flow, database design, entity-relationship model, schema specification, API design, and security framework. Each section describes the design decisions and structural organization of the system components.

---

## 3.1 System Architecture

Smart Mall follows a three-tier architecture pattern that separates the presentation layer, application logic, and data storage into distinct tiers. The system runs on a LAMPP (Linux, Apache, MariaDB, PHP) stack with a Capacitor-based Android mobile application acting as a native WebView wrapper around the web frontend. The architecture is designed for a shared hosting production environment deployed at smartmall.unaux.com, with a development environment running on localhost.

### 3.1.1 Presentation Layer

The presentation layer consists of two client applications that share the same backend API and business logic. The primary client is a responsive web application built with HTML5, CSS3, Bootstrap 5, and vanilla JavaScript. The secondary client is a Capacitor Android application that renders the same web frontend inside a native WebView, augmented with native plugins for push notifications and Google Sign-In.

The web frontend follows a server-rendered page model where each PHP file produces a complete HTML page. Bootstrap 5 provides the responsive grid system and UI components, while custom CSS defines the brand styling and layout overrides. JavaScript handles client-side interactivity including live search, currency switching, form validation, and AJAX cart operations. The user experience is augmented by a Progressive Web App (PWA) with a service worker (sw.js) that caches static assets and provides an offline fallback page (offline.html). A web app manifest (manifest.json) enables installation on mobile home screens.

### 3.1.2 Application Layer

The application layer is implemented in PHP 8.2 using a procedural programming model organized by functional concern. There is no framework abstraction; instead, the codebase follows a flat file structure where each PHP script handles a specific page or operation. The entry point for every request begins with config.php, which loads environment configuration, sets error handlers, configures session parameters, and includes shared dependencies.

The shared logic is organized into the includes/ directory. The database connection is established once in includes/db.php using PDO with prepared statements exclusively, ensuring consistent query parameterisation across all pages. The same file provides CSRF token generation and verification functions (csrf_token, csrf_field, csrf_verify) used by all forms in the application. Currency conversion helpers in includes/currency.php manage exchange rate fetching, caching, and formatted price display. The includes/header.php and includes/footer.php files provide consistent page chrome rendered by every PHP page.

The helpers/ directory contains specialised utility modules. The reCAPTCHA verification function in helpers/captcha.php calls the Google reCAPTCHA API to validate user submissions with a score threshold of 0.5. The mail helper (helpers/mail.php) uses the Brevo SDK to send transactional emails via its REST API.

The includes/seo.php module generates dynamic meta tags, Open Graph protocol tags, and JSON-LD structured data for search engine optimisation. The health.php endpoint provides system health monitoring by checking database connectivity and returning server status in JSON format for uptime tracking. The receipt.php page generates printable order receipts for customers, displaying line items, pricing, and payment details in a printer-friendly format.

The admin/ directory contains a separate set of pages for administrative functions including product management, category management, order management, user management, and reporting. These pages are protected by an admin role check and share a dedicated navigation component (admin/includes/admin_nav.php).

### 3.1.3 Data Layer

The data layer uses MariaDB 10.4.32 with the InnoDB storage engine running under the database name smartmall_db. All database interactions use PDO with prepared statements and parameterised queries, preventing SQL injection. The connection is configured with PDO::ATTR_EMULATE_PREPARES set to false, ensuring real prepared statements are used.

Database migrations are managed by deploy/migrate.php, which reads migration SQL files from deploy/migrations/ in timestamp-prefixed order. Each migration has a corresponding down migration file for rollback. The migration system tracks applied migrations in a migrations table within the database. The initial schema (20260528_120000_initial_schema.sql) creates the core tables for products, categories, users, orders, cart, and reviews. Subsequent migrations add features incrementally: email verification (20260529), contact messages (20260529), admin promotion tokens (20260529), Google Sign-In support (20260530), notifications (20260530), luxury product seed data (20260531), and FCM device tokens (20260602).

### 3.1.4 Caching Strategy

The system implements caching at multiple levels to improve performance. The PHP output minifier in config.php compresses HTML output by removing comments, collapsing whitespace, and preserving script and style tag integrity. Currency exchange rates are cached to disk in the system temp directory using JSON files, with a refresh interval determined by the exchangerate-api.com provider. The exchange rate cache falls back to stale data if the external API is unreachable, ensuring uninterrupted price display.

### 3.1.5 External Service Integration

Smart Mall integrates with six external services. The Chapa payment gateway (chapa.co) processes transactions through a hosted checkout page, with verification handled via HMAC-signed callback requests. Google reCAPTCHA v3 provides invisible bot detection on forms without user interaction. The Brevo SDK sends transactional emails via its REST API for order confirmations, password resets, and email verification. Google Sign-In enables OAuth 2.0 authentication through both the web (Google Identity Services) and Capacitor native (Social Login plugin) paths. The exchangerate-api.com service provides live USD-to-ETB conversion rates cached locally. Cloudflare Web Analytics tracks site traffic without compromising user privacy.

[Figure 3.1: Three-tier system architecture diagram showing Presentation Layer (Web Browser + Capacitor Android App), Application Layer (PHP pages, includes/, admin/, helpers/), Data Layer (MariaDB InnoDB), and external service integrations (Chapa, reCAPTCHA, Brevo API, Google Sign-In, Exchange Rate API, Cloudflare Analytics)]

---

## 3.2 User Interface Design

The user interface is designed for a premium e-commerce experience with a focus on product discovery, seamless checkout, and administrative control. The design follows a consistent layout structure: a fixed top navigation bar with logo, search bar, currency selector, cart indicator, and user menu; a main content area that varies by page; and a footer with links, contact information, and social media handles. The colour palette uses a dark navy header with gold accents for the premium brand feel, white backgrounds for content areas, and subtle grey dividers for visual separation.

### 3.2.1 Homepage (index.php)

The homepage presents a full-width hero section with a promotional banner highlighting featured products or seasonal offers, followed by a category showcase grid that displays each category card with an image and name. Below the categories, a product carousel displays featured or newest products with thumbnail images, prices, and quick-add-to-cart buttons. The layout uses Bootstrap rows and columns to maintain responsiveness across screen sizes. The search bar is prominently placed in the header for immediate access.

[Figure 3.2: Homepage layout showing hero banner, category grid, and featured product carousel]

### 3.2.2 Product Listing (product.php with category filter)

The product listing page presents products in a responsive grid layout with four columns on desktop screens, collapsing to two columns on tablets and a single column on mobile devices. Each product card displays the product image, name, price in the selected currency, stock status indicator, and an Add to Cart button. A sidebar filter panel allows users to narrow results by category. The page supports pagination when the product count exceeds the configured per-page limit. Empty state messaging is shown when no products match the selected filters.

[Figure 3.3: Product listing grid with sidebar filter and pagination]

### 3.2.3 Product Detail (product.php?id=X)

The product detail page features a large product image gallery with thumbnail navigation for products that have multiple images. Below the gallery, the product name, description, price, stock availability, and a quantity selector with Add to Cart button are displayed. Additional sections include product specification details, a customer reviews section with rating stars and written reviews, and a related products carousel at the bottom. Product videos are displayed in an embedded player when available.

[Figure 3.4: Product detail page with image gallery, pricing, and reviews section]

### 3.2.4 Shopping Cart (cart.php)

The cart page displays a table of added products with thumbnail images, product names, unit prices, quantity increment/decrement controls, and line totals. Each row has a remove button. Below the item list, the cart summary shows the subtotal, any applicable shipping estimate, and the total in the selected currency. A Proceed to Checkout button leads to the checkout page. An empty cart state displays a friendly message with a link back to the product listing. The cart data is stored in the cart database table and associated with the logged-in user session.

[Figure 3.5: Shopping cart page with item list, quantity controls, and order summary]

### 3.2.5 User Authentication: Login (login.php)

The login page presents a centred card with email and password fields, a Remember Me checkbox, a Log In button, and links to the registration page and forgot password page. Below the login form, a Google Sign-In button is displayed, which triggers OAuth authentication. Error messages are shown inline for invalid credentials or account issues. On the Capacitor mobile app, the Google Sign-In uses the native Social Login plugin with GSI iframe removal to prevent tap-blocking overlay issues.

[Figure 3.6: Login page with email/password form and Google Sign-In button]

### 3.2.6 Checkout (checkout.php)

The checkout page presents a two-column layout. The left column contains a shipping information form with fields for first name, last name, email, address, city, state, postal code, and country. The right column displays the order summary with itemised products, quantities, and totals. A payment method selector allows the user to choose between Cash on Delivery and Chapa online payment. The Place Order button triggers order creation and, for Chapa payments, redirects to the Chapa hosted checkout page.

[Figure 3.7: Checkout page with shipping form and order summary]

### 3.2.7 User Registration (register.php)

The registration page presents a form with name, email, password, and confirm password fields, plus a hidden reCAPTCHA v3 token. Client-side JavaScript validates password matching and email format before submission. Successful registration triggers an email verification workflow before the user can log in. The page also includes the GSI iframe overlay fix for the Capacitor build.

[Figure 3.8: Registration page with form fields and validation]

### 3.2.8 User Orders (orders.php)

The orders page displays a table of the user's past orders with order ID, date, status badge (colour-coded for pending, processing, shipped, delivered, cancelled), total price, and a View Details link. Each order row expands to show the individual line items with product images, names, quantities, and prices. The page includes pagination for users with many orders.

[Figure 3.9: Orders page with order history table and expandable line items]

### 3.2.9 Order Confirmation (order_confirmation.php)

After a successful order placement, the order confirmation page displays a success message with the order reference number, a summary of purchased items, the shipping address, the payment method, and the total charged. A Continue Shopping button returns the user to the homepage. For Chapa payments, the page displays the transaction reference for tracking.

[Figure 3.10: Order confirmation page with order summary and success message]

### 3.2.10 Wishlist (wishlist.php)

The wishlist page displays saved products in a grid layout similar to the product listing page, with each card showing the product image, name, price, and an Add to Cart button. A Remove from Wishlist button allows users to delete items. The page displays an empty state message when no items have been saved. Toggle wishlist functionality is handled by toggle_wishlist.php via AJAX.

[Figure 3.11: Wishlist page with saved products grid and remove controls]

### 3.2.11 About Us (about.php)

The About page presents the company story, mission statement, and team information in a narrative layout. It includes a timeline section showing company milestones and a values section describing the brand promise. The layout uses a centred text block with supporting imagery.

[Figure 3.12: About Us page with company story and timeline]

### 3.2.12 Contact Us (contact.php)

The contact page displays a contact form with name, email, subject, and message fields, protected by reCAPTCHA v3. Below the form, the store address, phone number, email address, and social media links are displayed. Form submissions are stored in the contact_messages table for admin review.

[Figure 3.13: Contact page with message form and contact details]

### 3.2.13 Admin Dashboard (admin/dashboard.php)

The admin dashboard presents an overview of store performance with key metrics displayed in stat cards: total products, total orders, total users, and revenue. Below the stat cards, Chart.js renders visual charts showing order trends over time, product category distribution, and revenue by month. Recent orders and low-stock product alerts are shown in summary tables.

[Figure 3.14: Admin dashboard with metric cards, charts, and summary tables]

### 3.2.14 Admin Product Management (admin/manage_products.php)

The product management page displays a searchable, paginated table of all products with columns for image thumbnail, name, category, price, stock, and action buttons (Edit, Delete). The table uses responsive design that collapses to a card layout on small screens. The Add Product button navigates to the product form page. Row-level actions include edit, delete with confirmation, and stock adjustment.

[Figure 3.15: Admin product management table with search, filters, and action buttons]

### 3.2.15 Admin Product Form (admin/add_product.php)

The product form provides fields for product name, category selection from a dropdown, description in a textarea, price, stock quantity, main image upload, additional images upload (multiple), and video upload. The form uses client-side validation and includes a CSRF token for security. Editing an existing product pre-populates all fields.

[Figure 3.16: Admin product add/edit form with all input fields]

### 3.2.16 Admin Order Management (admin/manage_orders.php)

The order management page displays all orders in a table with columns for order ID, customer name, total, status, date, and action buttons. Admin users can update order status through a dropdown selector (pending, processing, shipped, delivered, cancelled). Each row provides a View link that shows the full order details including line items and shipping address.

[Figure 3.17: Admin order management with status update controls and detail view]

### 3.2.17 Admin Reports (admin/reports.php)

The reports page presents sales analytics through Chart.js visualisations including a revenue line chart for the selected period, a product category distribution pie chart, and a top-selling products bar chart. Date range selectors allow filtering by custom periods. Summary metrics show total revenue, total orders, average order value, and conversion rate.

[Figure 3.18: Admin reports page with charts, date filters, and summary metrics]

---

## 3.3 Navigation Flow Diagram

The navigation flow describes the path a user takes through the Smart Mall application, categorised by authentication state and user role. The flow branches at three critical decision points: authentication status (guest versus authenticated), user role (customer versus admin), and payment method selection (cash on delivery versus Chapa online payment).

### 3.3.1 Guest User Flow

An unauthenticated user enters the system through the homepage (index.php) and can browse products by category, view product details, and use the search feature. Adding items to the cart or accessing the wishlist redirects the user to the login page (login.php) with a prompt to authenticate. Guest users can register (register.php), use the password reset flow (forgot_password.php and reset_password.php), submit a contact message (contact.php), or view informational pages (about.php). The guest flow terminates at login or registration, at which point the user transitions to the authenticated flow.

### 3.3.2 Authenticated Customer Flow

An authenticated customer can browse products, manage the shopping cart (add, update quantities, remove items), proceed to checkout, and place orders. The checkout flow (checkout.php) presents two payment paths: Cash on Delivery, which creates the order immediately with a pending payment status, and Chapa Payment, which redirects the customer to the Chapa hosted checkout page (checkout.chapa.co) and returns to the callback URL on completion. After order placement, the customer lands on the order confirmation page (order_confirmation.php). Customers can view their order history (orders.php), manage their wishlist (wishlist.php), and toggle currency display between USD and ETB (set_currency.php). Logout returns the user to the guest state.

### 3.3.3 Admin Flow

Admin users access the same customer pages plus the admin dashboard (admin/dashboard.php) and management pages for products (admin/manage_products.php, admin/add_product.php, admin/delete_product.php), categories (admin/manage_categories.php), orders (admin/manage_orders.php), users (admin/manage_users.php), and reports (admin/reports.php). Admin role is determined by the role column in the users table; pages in the admin/ directory check this role before rendering.

### 3.3.4 Authentication Branch Points

Three critical decision nodes control the navigation flow:

Node 1 (Login gate): The login page handles three authentication types — email/password against the users table, Google Sign-In via Google Identity Services (web) or Social Login plugin (Capacitor), and the case of unverified email, which redirects to the email verification page.

Node 2 (Checkout payment selection): At checkout, the payment method selection branches between cash_on_delivery (creates order directly) and chapa (redirects to Chapa checkout, returns via callback).

Node 3 (Admin role check): Accessing any admin/ page triggers a role verification against the session user ID. Non-admin users are redirected to the homepage with an error message.

```
[Figure 3.19: Navigation flow diagram]

                         ┌─────────────────┐
                         │   index.php      │
                         │   (Homepage)     │
                         └────────┬────────┘
                                  │
                    ┌─────────────┼─────────────┐
                    │             │             │
              ┌─────┴─────┐ ┌────┴────┐ ┌──────┴──────┐
              │ Browse     │ │ Search  │ │ View        │
              │ Categories │ │ (ajax)  │ │ Product     │
              └─────┬─────┘ └─────────┘ │ Detail      │
                    │                   └──────┬──────┘
                    │                          │
              ┌─────┴─────┐            ┌──────┴──────┐
              │ Product   │            │ Add to Cart │
              │ Listing   │            │ (auth gate) │
              └─────┬─────┘            └──────┬──────┘
                    │                         │
                    └──────────┬──────────────┘
                               │
                    ┌──────────┴──────────┐
                    │     login.php        │
                    │  (Auth Decision)     │
                    └──────────┬──────────┘
                               │
              ┌────────────────┼────────────────┐
              │                │                │
        ┌─────┴─────┐   ┌─────┴─────┐   ┌──────┴──────┐
        │ Register  │   │ Google    │   │ Email/Pass  │
        │ (new user)│   │ Sign-In   │   │ Login       │
        └─────┬─────┘   └─────┬─────┘   └──────┬──────┘
              │               │                │
              └───────────────┼────────────────┘
                              │
                    ┌─────────┴─────────┐
                    │   Authenticated   │
                    │   (Session)       │
                    └─────────┬─────────┘
                              │
              ┌───────────────┼───────────────────┐
              │               │                   │
        ┌─────┴─────┐   ┌────┴────┐        ┌─────┴──────┐
        │ Customer  │   │ Cart    │        │  Admin     │
        │ Pages     │   │ Mgmt    │        │  Pages     │
        └─────┬─────┘   └────┬────┘        └─────┬──────┘
              │              │                    │
        ┌─────┴─────┐  ┌────┴────┐        ┌──────┴──────┐
        │ Wishlist  │  │ Checkout│        │ Dashboard   │
        │ Orders    │  │ (Pay    │        │ Products    │
        │ About     │  │ Method) │        │ Categories  │
        │ Contact   │  └────┬────┘        │ Orders      │
        └───────────┘       │             │ Users       │
                            │             │ Reports     │
                    ┌───────┴───────┐     └─────────────┘
                    │               │
              ┌─────┴─────┐   ┌────┴─────┐
              │ Cash on   │   │ Chapa    │
              │ Delivery  │   │ Redirect │
              └─────┬─────┘   └────┬─────┘
                    │              │
                    └──────┬───────┘
                           │
                    ┌──────┴──────┐
                    │ Order Conf. │
                    │ (success)   │
                    └─────────────┘
```

---

## 3.4 Database Design

The database schema consists of 15 tables (11 core + 4 feature) designed for an e-commerce workflow, covering products, users, orders, cart, reviews, wishlist, messaging, notifications, and push tokens. The schema uses InnoDB for all tables, providing foreign key support and transaction capability. Character encoding is utf8mb4 throughout to support Unicode characters including emoji in product descriptions and user data.

### 3.4.1 Categories Table

The categories table stores product taxonomy with a self-referential hierarchy. Each category has an auto-increment primary key (category_id), a human-readable name, a URL-friendly unique slug, an optional text description, and up to three image URLs (image1, image2, image3) for category-level merchandising. The slug column has a unique index to enforce clean URL generation and prevent duplicate category paths. A timestamp tracks creation time.

### 3.4.2 Users Table

The users table stores customer and administrator accounts. Each user has an auto-increment primary key (user_id), an optional google_id column (added via migration) for Google Sign-In linkage with a unique constraint, a display name, a unique email address with an index, a bcrypt-hashed password (nullable for Google-only accounts), and a role field restricted to customer or admin via an ENUM with a default of customer. The email and role columns have secondary indexes for lookup queries. Timestamps track creation and last update via ON UPDATE CURRENT_TIMESTAMP.

### 3.4.3 Products Table

The products table is the largest and most heavily indexed table in the schema. Each product has an auto-increment primary key (product_id), a name (up to 200 characters), a foreign key reference to the categories table (category_id, nullable for uncategorised items), a detailed text description, a decimal price with two decimal places, a main image filename, an integer stock count defaulting to zero, additional_images stored as a JSON array in a LONGTEXT column, an optional video filename, and timestamps for creation and update.

The indexing strategy for products is designed for common query patterns. The category_id index supports filtered product listings by category. A composite index on (category_id, created_at) supports sorted category views. A second composite index on (category_id, price) supports price-sorted browsing within a category. Standalone indexes on price and created_at support global sorting. A FULLTEXT index on (name, description) enables MariaDB's native full-text search capability, used by the search API endpoint.

### 3.4.4 Cart Table

The cart table manages the shopping cart for authenticated users. Each record has an auto-increment primary key (cart_id), a foreign key to users (user_id) and products (product_id), and a quantity integer defaulting to one. A unique composite constraint on (user_id, product_id) prevents duplicate entries for the same user-product pair, enforcing that quantity updates occur in-place rather than creating duplicate rows. Indexes on product_id, user_id, and (user_id, created_at) support cart queries and session management.

### 3.4.5 Orders Table

The orders table records completed purchases with shipping and payment information. Each order has an auto-increment primary key (order_id), a unique transaction reference (tx_ref) for payment tracking, a foreign key to users (user_id), a decimal total_price, and a status ENUM with five states: pending, processing, shipped, delivered, and cancelled, defaulting to pending. Shipping address fields include first_name, last_name, email, address, city, state, zip, and country. The payment_method column defaults to cash_on_delivery. Indexes cover user_id for order history queries, status for admin filtering, and a composite (user_id, created_at) index for chronological order listing.

### 3.4.6 Order Items Table

The order_items table records the individual line items within each order. Each record has an auto-increment primary key (order_item_id), foreign keys to orders (order_id) and products (product_id), the quantity purchased, and the price at time of purchase (preserved independently from the product table to maintain historical accuracy). The composite index on (order_id, product_id) supports efficient order detail retrieval.

### 3.4.7 Payments Table

The payments table tracks payment transactions with a one-to-one relationship to orders. Each payment record has an auto-increment primary key (payment_id), a unique foreign key to orders (order_id) enforced by a unique constraint, a payment method ENUM (cash_on_delivery, bank_transfer, credit_card, chapa), a status ENUM (pending, paid, failed, refunded), the amount, a three-character currency code (ETB for local payments), an optional transaction reference, a timestamp for payment completion, and a chapa_response TEXT field that stores the full callback response JSON for audit purposes.

### 3.4.8 Password Resets Table

The password_resets table supports the forgot password workflow. Each record has an auto-increment primary key (reset_id), the user's email for lookup, a 64-character unique token generated by random_bytes and hashed for storage, and an expiration timestamp. The unique token index prevents replay attacks, and the email index supports lookup queries during the reset flow.

### 3.4.9 Reviews Table

The reviews table stores product ratings and comments from authenticated customers. Each review has an auto-increment primary key (review_id), foreign keys to products and users, a TINYINT rating constrained between 1 and 5 via a CHECK constraint, an optional text comment, and a creation timestamp. The composite index on (product_id, created_at) supports chronological review display on product detail pages.

### 3.4.10 Wishlist Table

The wishlist table allows users to save products for future reference. Each record has an auto-increment primary key (wishlist_id), foreign keys to users and products, and a creation timestamp. A unique composite constraint on (user_id, product_id) prevents duplicate saves.

### 3.4.11 Additional Support Tables

Four additional tables support specialised features. The contact_messages table stores submissions from the contact form with name, email, subject, and message fields. The notifications table stores system-wide notifications with type, message text, an optional link, and a boolean is_read flag with an index for unread queries. The admin_promotion_tokens table manages the admin-to-customer promotion workflow with foreign-key constrained references to both the promoting admin and the target user, a 64-character token, an expiration timestamp, and a completed flag. The newsletters table stores subscriber email addresses with a unique constraint to prevent duplicates. The device_tokens table stores Firebase Cloud Messaging tokens for push notifications with a unique constraint on fcm_token, user_id foreign key, and platform identifier.

---

## 3.5 Entity-Relationship Diagram

The entity-relationship diagram illustrates the logical data model of Smart Mall, showing the relationships between core entities. The database schema is organised around four entity clusters: the product cluster (categories, products, reviews), the user cluster (users, password_resets, admin_promotion_tokens, device_tokens), the order cluster (orders, order_items, payments), and the interaction cluster (cart, wishlist, contact_messages, notifications, newsletters).

```
[Figure 3.20: Entity-Relationship Diagram]

                      ┌───────────────┐
                      │   categories  │
                      └───────┬───────┘
                              │ 1
                              │
                              │ N
                      ┌───────┴───────┐
              ┌───────│   products    │───────┐
              │       └───────┬───────┘       │
              │ N             │ 1             │ N
              │               │               │
        ┌─────┴──────┐  ┌────┴────┐    ┌─────┴──────┐
        │   cart     │  │ reviews │    │ order_items │
        └─────┬──────┘  └─────────┘    └─────┬──────┘
              │ N                             │ N
              │                               │
              │ 1                     ┌───────┴───────┐
              │                       │    orders     │────┐
              │                       └───────┬───────┘    │
              │                               │ 1          │ 1
              │                               │            │
              │ 1                     ┌───────┴───────┐    │
              │                       │   payments    │────┘
              │                       └───────────────┘
              │
        ┌─────┴─────────────────────┐
        │         users             │
        └─────┬──────────┬──────────┘
              │ 1         │ 1
              │           │
      ┌───────┴───┐ ┌────┴──────┐
      │ wishlist  │ │ password  │
      └───────┬───┘ │ _resets   │
              │ N    └───────────┘
              │
              │ 1
      ┌───────┴──────────┐
      │ admin_promotion  │
      │ _tokens          │
      └──────────────────┘

    Independent entities (no FK relationships):
    ┌──────────────────┐  ┌────────────────┐  ┌───────────────┐
    │ contact_messages │  │ notifications  │  │  newsletters  │
    └──────────────────┘  └────────────────┘  └───────────────┘
```

### 3.5.1 Entity Relationships

The categories table has a one-to-many relationship with products, as each category can contain multiple products but each product belongs to exactly one category (or zero if unassigned). The products table has a one-to-many relationship with cart entries, reviews, and order items, as each product can appear in multiple carts, receive multiple reviews, and be part of multiple orders. The users table has a one-to-many relationship with cart, orders, wishlist, reviews, password_resets, and device_tokens entries, as each user can have multiple records across these tables. The orders table has a one-to-many relationship with order_items and a one-to-one relationship with payments, enforced by a unique constraint on the order_id foreign key in the payments table.

### 3.5.2 Independent Entities

The contact_messages, notifications, and newsletters tables operate as independent entities without foreign key relationships. Contact messages are stored for admin review without linking to a specific user account, allowing unauthenticated form submissions. Notifications are system-level broadcasts viewable by all users. Newsletters store subscriber emails independently of user accounts.

---

## 3.6 Database Schema Diagram

The detailed schema diagram presents the complete column specification for each table, including data types, constraints, defaults, and index definitions.

```
[Figure 3.21: Complete Database Schema Diagram]

┌─────────────────────────────────────────────────────────────┐
│                        categories                           │
├──────────────┬───────────────────┬──────────┬───────────────┤
│ Column       │ Type              │ Nullable │ Extra         │
├──────────────┼───────────────────┼──────────┼───────────────┤
│ category_id  │ INT AUTO_INCREMENT│ NOT NULL │ PK            │
│ name         │ VARCHAR(100)      │ NOT NULL │               │
│ slug         │ VARCHAR(100)      │ NOT NULL │ UNIQUE KEY    │
│ description  │ TEXT              │ YES      │               │
│ created_at   │ TIMESTAMP         │ YES      │ DEFAULT CURRENT│
│ image1       │ VARCHAR(255)      │ YES      │               │
│ image2       │ VARCHAR(255)      │ YES      │               │
│ image3       │ VARCHAR(255)      │ YES      │               │
└──────────────┴───────────────────┴──────────┴───────────────┘
  Indexes: PRIMARY (category_id), UNIQUE (slug)
  Engine: InnoDB  Charset: utf8mb4

┌─────────────────────────────────────────────────────────────────────────────┐
│                                users                                       │
├──────────────┬──────────────────────┬──────────┬───────────────────────────┤
│ Column       │ Type                 │ Nullable │ Extra                     │
├──────────────┼──────────────────────┼──────────┼───────────────────────────┤
│ user_id      │ INT AUTO_INCREMENT   │ NOT NULL │ PK                        │
│ google_id    │ VARCHAR(255)         │ YES      │ UNIQUE (added by migr.)   │
│ name         │ VARCHAR(100)         │ NOT NULL │                           │
│ email        │ VARCHAR(100)         │ NOT NULL │ KEY (idx_email)           │
│ password     │ VARCHAR(255)         │ YES      │ (nullable for Google SSO) │
│ role         │ ENUM('customer',     │ YES      │ DEFAULT 'customer'        │
│              │       'admin')       │          │ KEY (idx_role)            │
│ created_at   │ TIMESTAMP            │ YES      │ DEFAULT CURRENT_TIMESTAMP │
│ updated_at   │ TIMESTAMP            │ YES      │ ON UPDATE CURRENT_TS      │
└──────────────┴──────────────────────┴──────────┴───────────────────────────┘
  Indexes: PRIMARY (user_id), KEY (email), KEY (role), UNIQUE (google_id)
  Engine: InnoDB  Charset: utf8mb4

┌─────────────────────────────────────────────────────────────────────────────────┐
│                               products                                         │
├──────────────────┬──────────────────────┬──────────┬───────────────────────────┤
│ Column           │ Type                 │ Nullable │ Extra                     │
├──────────────────┼──────────────────────┼──────────┼───────────────────────────┤
│ product_id       │ INT AUTO_INCREMENT   │ NOT NULL │ PK                        │
│ name             │ VARCHAR(200)         │ NOT NULL │                           │
│ category_id      │ INT                  │ YES      │ KEY (idx_category_id)    │
│ description      │ TEXT                 │ YES      │                           │
│ price            │ DECIMAL(10,2)        │ NOT NULL │                           │
│ image            │ VARCHAR(255)         │ YES      │                           │
│ stock            │ INT                  │ YES      │ DEFAULT 0                 │
│ created_at       │ TIMESTAMP            │ YES      │ DEFAULT CURRENT_TIMESTAMP │
│ updated_at       │ TIMESTAMP            │ YES      │ ON UPDATE CURRENT_TS      │
│ additional_images│ LONGTEXT             │ YES      │ (JSON array of filenames)│
│ video            │ VARCHAR(255)         │ YES      │                           │
└──────────────────┴──────────────────────┴──────────┴───────────────────────────┘
  Indexes: PRIMARY (product_id),
           KEY (category_id),
           KEY (category_id, created_at),
           KEY (category_id, price),
           KEY (price), KEY (created_at),
           FULLTEXT (name, description)
  Engine: InnoDB  Charset: utf8mb4

┌───────────────────────────────────────────────────────────────────────┐
│                                  cart                                │
├──────────────┬──────────────────────┬──────────┬─────────────────────┤
│ Column       │ Type                 │ Nullable │ Extra               │
├──────────────┼──────────────────────┼──────────┼─────────────────────┤
│ cart_id      │ INT AUTO_INCREMENT   │ NOT NULL │ PK                  │
│ user_id      │ INT                  │ NOT NULL │ UNIQUE (composite)  │
│ product_id   │ INT                  │ NOT NULL │                     │
│ quantity     │ INT                  │ YES      │ DEFAULT 1           │
│ created_at   │ TIMESTAMP            │ YES      │ DEFAULT CURRENT_TS  │
│ updated_at   │ TIMESTAMP            │ YES      │ ON UPDATE CURRENT_TS│
└──────────────┴──────────────────────┴──────────┴─────────────────────┘
  Indexes: PRIMARY (cart_id),
           UNIQUE (user_id, product_id),
           KEY (product_id), KEY (user_id),
           KEY (user_id, created_at)
  Engine: InnoDB  Charset: utf8mb4

┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                                      orders                                            │
├──────────────────┬──────────────────────────┬──────────┬───────────────────────────────┤
│ Column           │ Type                     │ Nullable │ Extra                         │
├──────────────────┼──────────────────────────┼──────────┼───────────────────────────────┤
│ order_id         │ INT AUTO_INCREMENT       │ NOT NULL │ PK                            │
│ tx_ref           │ VARCHAR(50)              │ YES      │ UNIQUE KEY                    │
│ user_id          │ INT                      │ NOT NULL │ KEY (idx_user_id)             │
│ total_price      │ DECIMAL(10,2)            │ NOT NULL │                               │
│ status           │ ENUM('pending','process- │ YES      │ DEFAULT 'pending'             │
│                  │ ing','shipped','deliver- │          │ KEY (idx_status)              │
│                  │ ed','cancelled')         │          │                               │
│ first_name       │ VARCHAR(100)             │ YES      │                               │
│ last_name        │ VARCHAR(100)             │ YES      │                               │
│ email            │ VARCHAR(150)             │ YES      │                               │
│ address          │ VARCHAR(255)             │ YES      │                               │
│ city             │ VARCHAR(100)             │ YES      │                               │
│ state            │ VARCHAR(100)             │ YES      │                               │
│ zip              │ VARCHAR(20)              │ YES      │                               │
│ country          │ VARCHAR(100)             │ YES      │                               │
│ payment_method   │ VARCHAR(50)              │ YES      │ DEFAULT 'cash_on_delivery'    │
│ created_at       │ TIMESTAMP                │ YES      │ DEFAULT CURRENT_TIMESTAMP     │
│ updated_at       │ TIMESTAMP                │ YES      │ ON UPDATE CURRENT_TS          │
└──────────────────┴──────────────────────────┴──────────┴───────────────────────────────┘
  Indexes: PRIMARY (order_id), UNIQUE (tx_ref),
           KEY (user_id), KEY (status),
           KEY (user_id, created_at)
  Engine: InnoDB  Charset: utf8mb4

┌──────────────────────────────────────────────────────────────────────┐
│                             order_items                             │
├────────────────┬──────────────────────┬──────────┬──────────────────┤
│ Column         │ Type                 │ Nullable │ Extra            │
├────────────────┼──────────────────────┼──────────┼──────────────────┤
│ order_item_id  │ INT AUTO_INCREMENT   │ NOT NULL │ PK               │
│ order_id       │ INT                  │ NOT NULL │ KEY (idx_order)  │
│ product_id     │ INT                  │ NOT NULL │ KEY (product_id) │
│ quantity       │ INT                  │ NOT NULL │                  │
│ price          │ DECIMAL(10,2)        │ NOT NULL │                  │
│ created_at     │ TIMESTAMP            │ YES      │ DEFAULT CURRENT  │
└────────────────┴──────────────────────┴──────────┴──────────────────┘
  Indexes: PRIMARY (order_item_id),
           KEY (order_id), KEY (product_id),
           KEY (order_id, product_id)
  Engine: InnoDB  Charset: utf8mb4

┌───────────────────────────────────────────────────────────────────────────────────────┐
│                                  payments                                            │
├──────────────┬────────────────────────────┬──────────┬───────────────────────────────┤
│ Column       │ Type                       │ Nullable │ Extra                         │
├──────────────┼────────────────────────────┼──────────┼───────────────────────────────┤
│ payment_id   │ INT AUTO_INCREMENT         │ NOT NULL │ PK                            │
│ order_id     │ INT                        │ NOT NULL │ UNIQUE KEY                    │
│ method       │ ENUM('cash_on_delivery',   │ YES      │ DEFAULT 'cash_on_delivery'   │
│              │ 'bank_transfer','credit_   │          │                               │
│              │ card','chapa')             │          │                               │
│ status       │ ENUM('pending','paid',     │ YES      │ DEFAULT 'pending'             │
│              │ 'failed','refunded')       │          │                               │
│ amount       │ DECIMAL(10,2)              │ NOT NULL │                               │
│ currency     │ VARCHAR(3)                 │ NOT NULL │ DEFAULT 'ETB'                 │
│ tx_ref       │ VARCHAR(100)               │ YES      │ KEY (idx_tx_ref)              │
│ paid_at      │ TIMESTAMP                  │ YES      │ NULL                          │
│ chapa_response│ TEXT                      │ NOT NULL │ (full JSON from Chapa)        │
│ created_at   │ TIMESTAMP                  │ YES      │ DEFAULT CURRENT_TIMESTAMP     │
└──────────────┴────────────────────────────┴──────────┴───────────────────────────────┘
  Indexes: PRIMARY (payment_id), UNIQUE (order_id), KEY (tx_ref)
  Engine: InnoDB  Charset: utf8mb4

┌──────────────────────────────────────────────────────────────────────────┐
│                            password_resets                               │
├──────────────┬──────────────────────┬──────────┬────────────────────────┤
│ Column       │ Type                 │ Nullable │ Extra                  │
├──────────────┼──────────────────────┼──────────┼────────────────────────┤
│ reset_id     │ INT AUTO_INCREMENT   │ NOT NULL │ PK                     │
│ email        │ VARCHAR(100)         │ NOT NULL │ KEY (idx_email)        │
│ token        │ VARCHAR(64)          │ NOT NULL │ UNIQUE KEY             │
│ expires_at   │ DATETIME             │ NOT NULL │                        │
│ created_at   │ TIMESTAMP            │ YES      │ DEFAULT CURRENT_TS     │
└──────────────┴──────────────────────┴──────────┴────────────────────────┘
  Indexes: PRIMARY (reset_id), UNIQUE (token), KEY (email)
  Engine: InnoDB  Charset: utf8mb4

┌──────────────────────────────────────────────────────────────────────┐
│                               reviews                                │
├──────────────┬──────────────────────┬──────────┬─────────────────────┤
│ Column       │ Type                 │ Nullable │ Extra               │
├──────────────┼──────────────────────┼──────────┼─────────────────────┤
│ review_id    │ INT AUTO_INCREMENT   │ NOT NULL │ PK                  │
│ product_id   │ INT                  │ NOT NULL │ KEY (composite)     │
│ user_id      │ INT                  │ NOT NULL │ KEY (user_id)       │
│ rating       │ TINYINT              │ NOT NULL │ CHECK (1-5)         │
│ comment      │ TEXT                 │ YES      │                     │
│ created_at   │ TIMESTAMP            │ YES      │ DEFAULT CURRENT_TS  │
└──────────────┴──────────────────────┴──────────┴─────────────────────┘
  Indexes: PRIMARY (review_id),
           KEY (user_id),
           KEY (product_id, created_at)
  Engine: InnoDB  Charset: utf8mb4

┌──────────────────────────────────────────────────────────────────────┐
│                              wishlist                                │
├──────────────┬──────────────────────┬──────────┬─────────────────────┤
│ Column       │ Type                 │ Nullable │ Extra               │
├──────────────┼──────────────────────┼──────────┼─────────────────────┤
│ wishlist_id  │ INT AUTO_INCREMENT   │ NOT NULL │ PK                  │
│ user_id      │ INT                  │ NOT NULL │ UNIQUE (composite)  │
│ product_id   │ INT                  │ NOT NULL │                     │
│ created_at   │ TIMESTAMP            │ YES      │ DEFAULT CURRENT_TS  │
└──────────────┴──────────────────────┴──────────┴─────────────────────┘
  Indexes: PRIMARY (wishlist_id),
           UNIQUE (user_id, product_id),
           KEY (product_id)
  Engine: InnoDB  Charset: utf8mb4

Additional tables (contact_messages, newsletters, notifications,
admin_promotion_tokens, device_tokens) follow identical conventions
with appropriate column types and indexes as specified in sections
3.4.12 and 3.5.2.
```

---

## 3.7 API/Backend Design

The REST API provides programmatic access to products, categories, orders, and search functionality through JSON endpoints. All API endpoints are served from the api/ subdirectory and return JSON responses with appropriate HTTP status codes. CORS headers are set to allow cross-origin requests from the Capacitor mobile application.

### 3.7.1 API Discovery Endpoint (api/index.php)

The API root endpoint returns a JSON document listing all available endpoints with their HTTP methods, URLs, and descriptions. This serves as a self-documenting API explorer that reflects the current deployment base URL, allowing API consumers to discover available operations programmatically.

### 3.7.2 Products Endpoint (api/products.php)

The products endpoint supports two operations. A GET request without parameters returns a JSON array of all products with fields including product_id, name, description, price, stock, image URL, category_id, and category_name. The results are ordered by creation date descending. A GET request with an id parameter returns a single product object with the same fields, or a 404 error if the product is not found. An optional category_id query parameter filters the product list to a specific category. Image URLs are resolved to full paths relative to the uploads directory for local files, or passed through unchanged for external URLs.

### 3.7.3 Categories Endpoint (api/categories.php)

The categories endpoint supports two operations. A GET request without parameters returns a JSON array of all categories ordered alphabetically by name. A GET request with an id parameter returns a single category object with a nested products array containing all products belonging to that category, ordered by creation date descending. Each product within the nested array includes image URL resolution. A 404 response is returned if the category does not exist.

### 3.7.4 Orders Endpoint (api/orders.php)

The orders endpoint supports both GET and POST methods. A GET request with an id parameter returns a single order with its nested order_items array, including product names and images from a LEFT JOIN with the products table. A GET request with a user_id parameter returns all orders for that user ordered by creation date descending. The user_id parameter is required for list requests; omitting it returns a 400 Bad Request response.

A POST request creates a new order by accepting a JSON body with user_id and items (an array of objects containing product_id and quantity). The endpoint performs validation in sequence: it verifies the user exists, begins a database transaction, iterates through each item to validate product existence and stock sufficiency, decrements stock quantities, inserts the order record, inserts order_items records, and commits the transaction. If any validation fails, the transaction is rolled back and a 400 error is returned with the specific failure message. On success, a 201 Created response is returned with the order_id, total_price, and status.

### 3.7.5 Search Endpoint (api/search.php)

The search endpoint accepts a GET request with a q parameter containing the search keyword. It executes a LIKE query against both the name and description columns of the products table, using wildcard patterns on both sides of the search term. Results are limited to six products, ordered by creation date descending. Each result includes the product_id, name, description, price, image, resolved image URL, and a formatted display price using the smartmall_format_money helper. An empty query returns an empty array.

---

## 3.8 Security Design

The security architecture of Smart Mall addresses six threat categories: authentication bypass, SQL injection, cross-site request forgery, cross-site scripting, payment fraud, and session hijacking.

### 3.8.1 Authentication and Password Security

User passwords are hashed using PHP's password_hash function with the bcrypt algorithm, which includes a built-in salt and configurable cost factor. The password field in the users table is nullable to support Google Sign-In accounts that authenticate through OAuth 2.0 without a stored password. Server-side session identifiers are regenerated after login, logout, and periodically (every 30% of the idle timeout) using session_regenerate_id to prevent session fixation attacks.

### 3.8.2 Input Validation and SQL Injection Prevention

All database queries use PDO prepared statements with parameterised queries, eliminating SQL injection vectors entirely. PDO::ATTR_EMULATE_PREPARES is set to false to ensure real prepared statements rather than emulated ones. Input values are cast to appropriate types (integer casting for IDs, string validation for text fields) before being bound to query parameters. The CSRF token system in includes/db.php generates a 64-character hex token using random_bytes on first session access and verifies submissions using hash_equals for timing-safe comparison.

### 3.8.3 Payment Security (Chapa Integration)

Payment processing relies on the Chapa payment gateway, which handles credit card data externally and returns only a transaction reference to the application. The Chapa integration uses HMAC (Hash-Based Message Authentication Code) with SHA-256 to verify callback authenticity. On payment completion, Chapa sends a callback request containing the transaction reference and status; the application computes an HMAC hash using the shared secret key and compares it against the signature in the callback header, accepting only verified callbacks. The full Chapa callback response JSON is stored in the chapa_response column of the payments table for audit and dispute resolution.

### 3.8.4 Session and Cookie Security

Session cookies are configured with HttpOnly (inaccessible to JavaScript), SameSite=Lax (prevented from being sent with cross-site requests), and Secure (only over HTTPS in production). Session idle timeout is set to 30 minutes in config.php. Session ID regeneration occurs periodically to prevent fixation. The custom redirect function validates destination hosts against the allowed base URL to prevent open redirect attacks. Output is sanitised through config.php's Content-Security-Policy header, which restricts script sources to self, Chapa checkout, and Google domains while blocking inline event handlers.

### 3.8.5 reCAPTCHA v3 Integration

Google reCAPTCHA v3 provides invisible bot protection on the login, registration, and contact forms. The verify_recaptcha function in helpers/captcha.php sends the response token to Google's siteverify API with a 5-second timeout and validates both the success flag and a minimum score threshold of 0.5. Forms lacking a reCAPTCHA response or scoring below the threshold are rejected, and the event is logged for security monitoring.

---

# CHAPTER 4: SYSTEM IMPLEMENTATION (35 pages)

This chapter presents the implementation details of the Smart Mall e-commerce platform, covering the technology stack, frontend and backend code architecture, mobile app implementation with Capacitor, Progressive Web App features, database implementation, payment gateway integration with Chapa, email system integration, admin dashboard features, and overall system integration. Each section includes relevant code samples, configuration details, and implementation decisions.

---

## 4.1 Technology Stack (3 pages)

**Table 4.1:** Complete technology stack with versions and justifications.

| Layer | Technology | Version | Justification |
|-------|-----------|---------|---------------|
| Frontend | HTML5, CSS3, JavaScript | — | Universal browser support, no build step |
| CSS Framework | Bootstrap 5 | 5.3.x | Responsive grid, prebuilt components, mobile-first |
| Charts | Chart.js | 4.x | Lightweight, canvas-based, no dependencies |
| Backend | PHP | 8.2 | Shared hosting compatible, procedural model |
| Database | MariaDB | 10.4.32 | InnoDB for transactions and foreign keys |
| Web Server | Apache | 2.4+ | mod_rewrite, .htaccess, shared hosting standard |
| Mobile | Capacitor | 6.x | Web-to-native wrapper, single codebase |
| Push | Firebase Cloud Messaging | — | Android push standard |
| Social Auth | Google OAuth 2.0 / @capgo/capacitor-social-login | — | Web + native sign-in |
| Payment | Chapa API | v1 | Ethiopian payment gateway, mobile money support |
| Email | Brevo SDK | — | Transactional email via REST API |
| Analytics | Cloudflare | — | Free tier, Web Analytics only |
| CAPTCHA | Google reCAPTCHA v3 | — | Invisible bot detection |
| SEO | Custom (includes/seo.php) | — | OG tags, JSON-LD breadcrumbs, canonical URLs |
| Cache | Custom (includes/cache.php) | — | Flat-file JSON cache with TTL |

**Key decision points to document:**
- Why procedural PHP over Laravel/Symfony (shared hosting constraints, simpler deployment)
- Why Capacitor over Flutter/React Native (leverage existing web frontend, single codebase)
- Why Chapa over Stripe/PayPal (Ethiopian market support, mobile money, telebirr)
- Why USD base currency with ETB display (stable store of value, local display preference)
- Why flat-file cache over Redis/Memcached (shared hosting limitations)

[Figure 4.1: High-level system architecture — Browser/mobile app → Apache (mod_rewrite) → PHP application → MariaDB database. External services: Cloudflare Web Analytics, Chapa API, Brevo API, Google OAuth, FCM. Internal: flat-file cache, session store]

---

## 4.2 Frontend Implementation (5 pages)

### 4.2.1 Responsive Design (1 page)

**Content:**
- Bootstrap 5 grid system usage (`container`, `row`, `col-*`, `col-md-*`, `col-lg-*`)
- Custom responsive overrides in `<style>` blocks
- Breakpoints: mobile-first, `576px`/`768px`/`992px`/`1200px`
- Viewport meta tag configuration

**Code sample:** `includes/header.php` — viewport meta, Bootstrap CSS link, custom style block.

### 4.2.2 Navigation Bar (1 page)

**Content:**
- Bootstrap navbar with brand logo, search form, cart badge, currency selector, user dropdown
- Responsive collapse behavior on mobile
- Active state highlighting
- Cart badge count updates via AJAX

**Code sample:** `includes/header.php` — full navbar HTML structure (30–40 lines).

[Figure 4.2: Navigation bar — brand logo (left) → search input → cart badge with count → currency selector dropdown → user menu (login/register or profile/logout). Mobile: hamburger collapse icon]

### 4.2.3 Product Cards (1 page)

**Content:**
- Card structure from `index.php` and `product.php`
- Image with hover zoom effect
- Title, price (formatted via `smartmall_format_money`), rating stars
- Add-to-cart button, wishlist toggle
- Responsive grid layout (3 columns desktop, 2 tablet, 1 mobile)

**Code sample:** Product card HTML/CSS (15–20 lines).

[Figure 4.3: Product card — image (hover zoom) → title → formatted price → star rating → Add to Cart button → Wishlist toggle heart icon]

### 4.2.4 Cart UI (1 page)

**Content:**
- Cart page layout (`cart.php`)
- Table with product image, name, quantity stepper, unit price, line total, remove button
- Quantity update via AJAX (`add_to_cart.php`)
- Order summary sidebar with subtotal, tax (10%), shipping, total
- Empty cart state with call-to-action

**Edge case:** Empty cart causes session cart items query to return empty set — display "Your cart is empty" with link to shop. Quantity stepper has client-side min=1 enforcement; server also clamps to 1 and validates product existence before update.

**Code sample:** Cart table HTML (20–25 lines). Quantity update JavaScript.

[Figure 4.4: Cart interaction — product card "Add to Cart" → add_to_cart.php (AJAX) → session cart updated → header badge count refreshes → cart.php displays table with quantity steppers → AJAX update row total → checkout button]

### 4.2.5 Checkout UI (0.5 page)

**Content:**
- Two-column layout: shipping form (left) + order summary (right)
- Form fields: first name, last name, email, address, city, state, postal code, country
- Payment method selector: Cash on Delivery / Chapa
- Place Order button with loading state

**Table 4.2:** Checkout form fields with validation rules.

**Code sample:** Checkout form HTML skeleton (15 lines).

[Figure 4.5: Checkout process — cart.php → checkout.php → shipping form → payment selection → Chapa redirect / COD → order_confirmation.php]

### 4.2.6 Currency Selector (0.5 page)

**Content:**
- Dropdown in header nav
- Triggers `set_currency.php` via GET/POST
- Session-persistent selection
- Flag emoji display (`smartmall_currency_flag()`)

**Code sample:** Currency selector HTML + `set_currency.php` (complete, 26 lines).

---

## 4.3 Backend Implementation (10 pages)

### 4.3.1 Session Management (1 page)

**Content:**
- Session configuration in `config.php`
- `session_start()` after `.env` loading
- `session_regenerate_id()` on login/logout/timeout
- Session idle timeout (30 minutes)
- Session cookie configuration (`HttpOnly`, `SameSite=Lax`, `Secure`)
- Custom `base_url_path()` helper

**Code sample:** `config.php` — session setup, cookie params, error handler registration (25–30 lines).

### 4.3.2 Authentication System (1.5 pages)

**Content:**
- Email/password login flow (`login.php`)
- `password_hash()` with bcrypt for registration
- `password_verify()` for login
- CSRF token verification (`csrf_verify()`)
- "Remember Me" checkbox
- Forgot password flow (`forgot_password.php`, `reset_password.php`)
- Email verification flow (`verify_email.php`)
- Admin role check (`$_SESSION['user_role'] === 'admin'`)

**Edge case:** Session expiry mid-operation — login required redirect with `?redirect=` parameter returning user to original page after re-authentication.

**Code sample:** `login.php` — login validation block (25–30 lines).

[Figure 4.6: Authentication flow diagram — login.php form submit → password_verify() → session_regenerate_id() → $_SESSION variables → redirect. Forgot password: forgot_password.php → reset token email → reset_password.php → new hash]

### 4.3.3 Google OAuth Integration (1 page)

**Content:**
- Web: Google Identity Services (`google.accounts.id`) declarative button
- Backend: `google_login.php` — ID token verification via Google tokeninfo endpoint
- `aud` claim validation against Web OAuth client ID
- Auto-create user if new (by `google_id` or matching `email`)
- Capacitor: native `@capgo/capacitor-social-login` with `SocialLogin.initialize()`
- GSI iframe overlay fix (remove `credential_picker_container` on native)

**Code sample:** `google_login.php` (complete, 75 lines). `login.php` — `g_id_signin` render block (10 lines).

[Figure 4.7: Google OAuth flow — Web: google.accounts.id prompt → ID token → google_login.php → verify aud + iss → login/create user → session. Native: SocialLogin.initialize() → login() → native dialog → ID token → same backend]

### 4.3.4 CRUD Operations (1 page)

**Content:**
- Admin product CRUD: `admin/includes/product_handler.php`
- Image upload handling: `handle_main_image_upload()`, gallery uploads, video uploads
- Input validation and sanitization
- PDO prepared statements for all queries
- Admin category management (`admin/manage_categories.php`)

**Code sample:** `admin/includes/product_handler.php` — `handle_main_image_upload()` function (20–25 lines).

### 4.3.5 Order Processing (1.5 pages)

**Content:**
- Checkout flow: `checkout.php`
- Cart items → order creation within DB transaction
- Order status lifecycle: `pending` → `processing` → `shipped` → `delivered` / `cancelled`
- Tax calculation (10% VAT)
- Order confirmation page (`order_confirmation.php`)
- Receipt generation (`receipt.php`) — printable format

**Edge case:** Concurrent checkout — `SELECT ... FOR UPDATE` on cart items inside transaction prevents double-purchase. If payment fails, order stays `pending` and cart is preserved for retry.

**Cross-ref:** The order status lifecycle follows the state machine designed in §3.4.5

**Code sample:** `checkout.php` — order creation transaction block (25–30 lines).

### 4.3.6 Multi-Currency System (1.5 pages)

**Content:**
- Architecture: USD base storage, ETB display conversion
- Exchange rate fetching: `smartmall_fetch_exchange_rates()` — cURL to exchangerate-api.com
- Caching: `smartmall_read_exchange_cache()`, `smartmall_exchange_cache_path()`
- Zero-rate fallback when API unavailable

**Edge case:** Exchange rate API unavailable — `smartmall_fetch_exchange_rates()` returns 0, `smartmall_convert_money()` falls back to 1:1 (display USD as-is) rather than failing. Cache TTL of 5 minutes prevents thundering herd on API.
- Conversion: `smartmall_convert_money()`, `smartmall_format_money()`
- Session persistence: `smartmall_set_selected_currency()`, `smartmall_selected_currency()`

**Table 4.3:** Currency functions reference.

**Code sample:** `includes/currency.php` — complete file (268 lines) with inline annotations.

[Figure 4.8: Currency conversion flow — exchangerate-api.com → cURL fetch → flat-file cache → smartmall_convert_money() → smartmall_format_money() → display on page. USD stored in DB, ETB displayed to user]

### 4.3.7 Caching Implementation (1 page)

**Content:**
- Flat-file JSON cache in `cache/queries/` directory
- Functions: `cache_get()`, `cache_set()`, `invalidate_cache_pattern()`
- Default TTL: 5 minutes
- Pattern-based invalidation (deletes all `*.cache` files)
- Usage: exchange rates, category listings, product queries

**Code sample:** `includes/cache.php` — complete file (41 lines).

### 4.3.8 SEO Implementation (1 page)

**Content:**
- Dynamic meta tags: `seo_og_tags()` — OG, Twitter Card
- Canonical URLs: `seo_canonical()`
- JSON-LD breadcrumbs: `seo_jsonld_breadcrumb()`
- `$GLOBALS['page_title']` / `$GLOBALS['page_description']` convention
- `seo_current_url()` — reconstructs URL from `$_SERVER`

**Code sample:** `includes/seo.php` — complete file (98 lines).

---

## 4.4 Mobile App Implementation — Capacitor (8 pages)

The mobile app is implemented with Capacitor 6.x as a native WebView wrapper around the web frontend.

### 4.4.1 Capacitor Project Structure (1 page)

**Content:**
- Root: `capacitor/` directory
- `capacitor.config.json` — appId `com.smartmall.app`, webDir `www`, server URL `https://smartmall.unaux.com`
- `android/` — Android platform files (auto-generated by `npx cap add android`)
- No iOS support (targeting Android only for Ethiopian market)

**Code sample:** `capacitor/capacitor.config.json` (complete, 17 lines). Directory tree.

### 4.4.2 Android Configuration (1 page)

**Content:**
- `build.gradle` — compileSdk, minSdk, targetSdk versions
- `google-services.json` — Firebase project configuration for FCM
- `strings.xml` — `google_web_client_id` for Google Sign-In
- `AndroidManifest.xml` — permissions (INTERNET, ACCESS_NETWORK_STATE)

**Code sample:** `strings.xml` — Google client ID entry. Key `build.gradle` lines.

### 4.4.3 Native Plugins Integration (1.5 pages)

**Content:**
- `@capacitor/app` — app lifecycle events (appUrlOpen, appStateChange)
- `@capacitor/push-notifications` — FCM push notification registration
- `@capgo/capacitor-social-login` — native Google Sign-In
- Plugin initialization in Capacitor IIFE (guarded by `Capacitor.isNativePlatform()`)
- Plugin import and registration pattern

**Code sample:** Capacitor IIFE with plugin initialization (15–20 lines).

### 4.4.4 Google Sign-In (Native) (1.5 pages)

**Content:**
- Web OAuth client ID (type 1) vs Android client ID (type 3) — critical distinction
- `SocialLogin.initialize()` called before every `login()`
- Token returned from native dialog
- `google_login.php` — verifies ID token server-side
- GSI iframe overlay removal: `document.getElementById('credential_picker_container')?.remove()`

**Code sample:** Native Google Sign-In flow with `SocialLogin` (15 lines).

**Edge case:** User denies Google Sign-In permission on native dialog — `SocialLogin.login()` rejects with error; UI falls through to manual email/password login. No modal stuck state.

### 4.4.5 Push Notifications (FCM) (1.5 pages)

**Content:**
- `PushNotifications.requestPermissions()` — runtime permission request
- `PushNotifications.register()` — FCM token registration
- `addListener('registration', ...)` — must fire **before** `register()` to catch token
- Token stored in `localStorage` → sent to server via fetch
- `capacitor_push_token.php` — backend endpoint storing token in `device_tokens` table
- `PushNotifications.addListener('pushNotificationReceived', ...)` — foreground handler

**Code sample:** FCM registration and token storage (15–20 lines).

[Figure 4.11: FCM push notification registration flow — PushNotifications.requestPermissions() → user grants → PushNotifications.register() → registration event fires → token stored in localStorage → fetch POST to capacitor_push_token.php → INSERT INTO device_tokens]

### 4.4.6 API Communication (1 page)

**Content:**
- Standard `fetch()` calls to backend API endpoints (`/api/`)
- Session-based authentication (cookies sent automatically)
- No special auth headers needed (same origin via WebView)
- Error handling and loading states

**Code sample:** Fetch call to `/api/products.php` with JSON response handling (10 lines).

### 4.4.7 Offline Capabilities (0.5 page)

**Content:**
- `localStorage` for caching product data and user preferences
- Queued operations stored locally when offline
- Sync on reconnect
- Offline fallback page via service worker

[Figure 4.13: Mobile app architecture — Capacitor WebView loads https://smartmall.unaux.com → JavaScript communicates with native plugins via Capacitor bridge → FCM push, Google Sign-In, App lifecycle plugins]

---

## 4.5 Progressive Web App Implementation (3 pages)

### 4.5.1 Service Worker (1 page)

**Content:**
- `sw.js` — install, activate, fetch event handlers
- Cache-first strategy for static assets
- Network-first strategy for HTML pages (fallback to cache)
- `self.addEventListener('install')` — pre-cache offline.html, logo
- `self.addEventListener('activate')` — clean old caches
- `self.addEventListener('fetch')` — intercept and respond

**Code sample:** `sw.js` (complete, ~45 lines).

[Figure 4.9: Service worker lifecycle — install event pre-caches offline.html and logo, activate event cleans old caches, fetch event intercepts requests with cache-first (assets) or network-first (HTML) strategy]

### 4.5.2 Web App Manifest (0.5 page)

**Content:**
- `manifest.json` — `name`, `short_name`, `start_url`, `display: standalone`
- Icons: 192x192 and 512x512 (both `logo-icon.png`, purpose `any maskable`)
- Theme color `#007AFF`, background `#ffffff`
- `prefer_related_applications: false`

**Code sample:** `manifest.json` (complete, 28 lines).

### 4.5.3 Offline Page (0.5 page)

**Content:**
- `offline.html` — centered card with emoji, heading, description, retry link
- Inline CSS only (no external dependencies)
- Retry button links to `index.php`

**Code sample:** `offline.html` (complete, ~35 lines).

### 4.5.4 Cache Strategies (1 page)

**Content:**
- Network-first for HTML (fresh content preferred, cache fallback)
- Cache-first for assets (CSS, JS, images) — fast load, never stale
- Static asset pre-caching on install
- Cache versioning (`CACHE_NAME`) for updates

---
**Session boundary — end of Session A (§§4.1–4.5).**
**Estimated: 29 pages, ~5–6 hours.**
**Resume Session B (§§4.6–4.10) after break.**
---

## 4.6 Database Implementation (2.5 pages)

**Cross-ref:** This section covers runtime query patterns against the schema designed in §3.5 (Database Schema Specification). Refer to §3.4 for the ER model and §3.5 for full column definitions.

### 4.6.1 Key SQL Queries (0.5 page)

**Content:**
- Product listing with pagination
- Cart retrieval with product joins
- Order creation within transaction
- Search with LIKE on name + description
- Category-based product filtering
- User order history with status filtering

**Code sample:** 3–4 representative queries (product listing, cart join, order creation).

**EXPLAIN plan annotation:** For the product listing query (`SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.category_id WHERE p.status = 'active' ORDER BY p.created_at DESC LIMIT 12 OFFSET 0`), document the EXPLAIN output showing `type: ref`, `key: idx_status`, `rows: N`, `Extra: Using where; Using index`. Explain that the index on `status` + `created_at` covers the filter and sort.

**Cross-ref:** as designed in §3.5.2 (products table schema)

### 4.6.2 Insert/Update/Delete Operations (0.5 page)

**Content:**
- Parameterised INSERT for registration, product add, cart add
- UPDATE for profile, cart quantity, order status
- DELETE for cart items, wishlist items, products
- `ON DUPLICATE KEY UPDATE` pattern in `add_to_cart.php`

**Code sample:** Insert/update examples with PDO prepared statements (10 lines).

### 4.6.3 Relationships and Constraints (0.5 page)

**Content:**
- Foreign key constraints: `cart.user_id → users.user_id`, `cart.product_id → products.product_id`
- `ON DELETE CASCADE` for dependent records
- Composite unique constraints: `(user_id, product_id)` on wishlist and reviews
- Indexes on foreign keys, `is_read`, `email`, `status`, `slug`

**Code sample:** CREATE TABLE with constraints — representative example.

### 4.6.4 Migration System (0.5 page)

**Content:**
- `deploy/migrations/` directory structure
- Timestamp-prefixed SQL files (e.g., `20260602_100000_device_tokens.sql`)
- `deploy/migrate.php` — reads `.env`, applies pending migrations
- Idempotent execution (`IF NOT EXISTS` / `IF EXISTS`)

**Code sample:** `deploy/migrate.php` — migration execution loop (15 lines).

### 4.6.5 Execution Patterns (0.5 page)

**Content:**
- N+1 query prevention: product list page fetches all products in 1 query, then fetches reviews in a batch query using `WHERE product_id IN (...)` rather than individual queries per product
- Lazy loading: review count and average rating computed via subquery in product listing query (not separate queries per card)
- Pagination pattern: `LIMIT 12 OFFSET :offset` with total count via `SQL_CALC_FOUND_ROWS` or separate `SELECT COUNT(*)`
- Transaction pattern in order creation: `BEGIN` → INSERT order → INSERT order_items → UPDATE inventory → DELETE cart → `COMMIT` with rollback on any failure
- Connection reuse: single PDO instance from `includes/db.php` reused across all queries in a request

**Cross-ref:** The ER relationships driving these queries were designed in §3.4

---

## 4.7 Payment Gateway Integration (2 pages)

### 4.7.1 Chapa Integration Setup (0.5 page)

**Content:**
- Chapa merchant account registration
- API keys: `CHAPA_SECRET_KEY`, `CHAPA_API_URL` (in `.env`)
- `chapa_pay/chapa-config.php` — config loading
- Test environment vs production

### 4.7.2 Payment Request Flow (0.5 page)

**Content:**
- Order creation → generate `tx_ref` (unique transaction reference)
- POST to Chapa API `/transaction/initialize` with amount, currency, callback URL
- Redirect user to Chapa hosted checkout page
- User completes payment on Chapa's secure page

**Code sample:** Chapa API call with cURL (15 lines).

### 4.7.3 Transaction Verification (0.5 page)

**Content:**
- Chapa callback to `chapa_pay/callback.php`
- HMAC-SHA256 signature verification via `hash_equals()`
- `SELECT ... FOR UPDATE` row lock to prevent race conditions
- Status check: `success`/`complete` vs `failed`/`cancelled`

**Code sample:** `chapa_pay/callback.php` — HMAC verification and status handling (25–30 lines).

**Edge case:** Chapa callback timeout — user pays but callback delayed or lost. Order remains `pending` in payments table. Admin can manually verify via Chapa dashboard and update status. `chapa_response` column stores full callback JSON for audit reconciliation.

### 4.7.4 Payment Confirmation (0.25 page)

**Content:**
- On success: update `payments` table to `paid`
- Insert order, clear cart within DB transaction
- User redirected to `order_confirmation.php`

### 4.7.5 Order Update After Payment (0.25 page)

**Content:**
- `chapa_response` column stores full callback JSON for audit
- Order status updated from `pending` to `processing`
- Error handling: failed payments remain `pending` for retry or manual intervention

[Figure 4.10: Chapa payment flow — checkout.php creates order → POST /transaction/initialize with tx_ref → redirect to Chapa checkout → user pays → Chapa POSTs to callback.php → HMAC verification → SELECT ... FOR UPDATE → UPDATE payments + INSERT order_items + DELETE cart → redirect to order_confirmation.php]

---

## 4.8 Email System Implementation (1.5 pages)

### 4.8.1 Brevo SDK Setup (0.5 page)

**Content:**
- Composer dependency: `composer require getbrevo/brevo-php`
- REST API via Brevo SDK: `Api\TransactionalEmailsApi` with `SendSmtpEmail` model
- Credential in `.env`: `BREVO_API_KEY`
- `send_mail(string $to, string $subject, string $body, ?string $altBody = null): bool` — returns true on success
- Dev fallback: if `APP_ENV=development`, writes `.eml` files to `mail/` directory instead of sending

**Code sample:** `helpers/mail.php` — API key loading and `send_mail()` function (20 lines).

### 4.8.2 Email Template (0.5 page)

**Content:**
- `email_html_template(string $title, string $content): string` — wraps content in branded HTML
- Styled with inline CSS: logo header, content area, footer with social links
- Mobile-responsive: max-width 600px, fluid images
- Plain-text alternative via `$altBody` parameter

**Code sample:** `helpers/mail.php` — `email_html_template()` wrapper (15 lines).

### 4.8.3 Transactional Events (0.5 page)

**Table 4.4:** Email types and trigger events.

| Email Type | Trigger | Recipient | Template |
|------------|---------|-----------|----------|
| Email Verification | User registers | New user | `verify_email.php` link |
| Password Reset | User requests reset | Requesting user | `reset_password.php` token |
| Order Confirmation | Order placed | Customer | Order summary |
| Order Status Update | Admin changes status | Customer | New status + tracking |
| Contact Form Notification | User submits contact | Admin | Sender message + details |

**Cross-ref:** The email verification flow ties into the authentication system described in §4.3.2

---

## 4.9 Admin Features Implementation (2 pages)

### 4.9.1 Dashboard Analytics (0.75 page)

**Content:**
- `admin/dashboard.php` — admin role guard
- Summary cards: total products, total orders, total users, total revenue
- Recent orders table
- Quick action buttons
- Data pulled via aggregate SQL queries

### 4.9.2 Reports System (0.75 page)

**Content:**
- `admin/reports.php` — period filtering (today, 1h, 6h, 12h, 24h, 7d, 30d, 90d, 365d, all)
- Dynamic SQL WHERE conditions based on period
- Revenue reports, order counts, user registrations, product stats
- Export-ready data format

**Code sample:** Period filter SQL construction (15 lines).

### 4.9.3 Chart.js Integration (0.5 page)

**Content:**
- Chart.js CDN loaded in admin pages
- Line charts for revenue over time
- Bar charts for order volume
- Pie charts for order status distribution
- Canvas elements populated from PHP-generated JSON data

**Code sample:** Chart.js initialization with PHP data (15 lines).

[Figure 4.12: Admin dashboard with four metric summary cards (total products, orders, users, revenue), Chart.js line chart for revenue trend, bar chart for order volume, pie chart for order status distribution, recent orders table below]

---

## 4.10 System Integration (4 pages)

### 4.10.1 Frontend-Backend Integration (1 page)

**Content:**
- Server-rendered HTML pages with embedded PHP
- AJAX endpoints for cart, wishlist, search, currency
- JavaScript `fetch()` calls to `/api/` endpoints
- CSRF token passed in AJAX requests
- Session-based authentication flow across pages
- Form submissions with POST → process → redirect pattern

### 4.10.2 Mobile-Backend Integration (0.75 page)

**Content:**
- Capacitor WebView loads same web frontend from production URL
- Native ↔ Web communication via `Capacitor.isNativePlatform()` guard
- FCM tokens sent to dedicated endpoint
- Google Sign-In token passed to `google_login.php`
- Session cookies shared via WebView (same origin)
- No separate API layer for mobile — same PHP backend

### 4.10.3 Database Integration (0.5 page)

**Content:**
- PDO connection from `includes/db.php` — single entry point
- All queries use prepared statements
- Transaction support for order creation
- Connection configured for UTF-8, exception mode, associative fetch

### 4.10.4 Payment Gateway Integration (0.5 page)

**Content:**
- Checkout → Chapa initialize → redirect → payment → callback → verify → update
- HMAC signature verification secures callback
- `SELECT ... FOR UPDATE` row lock prevents double-spend
- Full callback JSON stored for audit

### 4.10.5 Third-Party Services Integration (0.25 page)

**Table 4.5:** Third-party services summary.

| Service | Purpose | Integration Point | Credentials In |
|---------|---------|-------------------|----------------|
| Google reCAPTCHA v3 | Bot protection | `helpers/captcha.php` | `.env` |
| Google OAuth 2.0 | Social login | `login.php`, `google_login.php` | `.env`, `strings.xml` |
| Chapa | Payment gateway | `chapa_pay/` | `chapa-config.php` |
| Brevo API | Transactional email | `helpers/mail.php` | `.env` |
| Firebase Cloud Messaging | Push notifications | Capacitor plugin | `google-services.json` |
| Cloudflare | Web Analytics | JS beacon | cloudflareinsights.com |

**reCAPTCHA v3 implementation detail:**
- Site key and secret key loaded from `.env` (`RECAPTCHA_SITE_KEY`, `RECAPTCHA_SECRET_KEY`)
- `helpers/captcha.php` — `verify_recaptcha(string $token): float` calls Google's `/siteverify` endpoint
- Returns score 0.0–1.0; threshold at 0.5
- If score < 0.5, form submission rejected with "Suspicious activity detected"
- Integrated in: `login.php`, `register.php`, `contact.php`
- reCAPTCHA v3 is invisible (no checkbox) — token generated on form submit via `grecaptcha.execute()`

**Cross-ref:** reCAPTCHA integration was specified in the security framework §3.8

**Health check endpoint:**
- `health.php` returns JSON: `{"status":"ok","db":"connected","php_version":"8.2","timestamp":"..."}`
- Database connectivity test via PDO `query("SELECT 1")`
- Used by external uptime monitors (e.g., Better Uptime)
- Requires admin session — returns 403 Forbidden if not authenticated as admin

[Figure 4.14: Third-party service integration diagram — concentric circles: Smart Mall core (PHP + MariaDB) → integration layer (helpers/, includes/, chapa_pay/) → external services (Google, Chapa, Brevo, Firebase, Cloudflare Web Analytics, reCAPTCHA)]

### 4.10.6 API Routing (0.5 page)

**Content:**
- `api/.htaccess` rewrites all requests to `api/index.php?endpoint={path}`
- `api/index.php` dispatches to handler based on endpoint name
- Endpoint list: `products` → `api/products.php`, `categories` → `api/categories.php`, `orders` → `api/orders.php`, `search` → `api/search.php`
- JSON response envelope: `{"success": true/false, "data": {...}, "error": "..."}`
- CORS headers: `Access-Control-Allow-Origin: *` (relaxed for development)
- All endpoints load `.env` before `includes/db.php` (critical ordering)

**Code sample:** `api/index.php` dispatcher (15 lines). `api/.htaccess` (4 lines).

**Cross-ref:** The API serves as the data layer for the AJAX-driven frontend described in §4.10.1

[Figure 4.15: Complete system integration data flow — end-to-end: Browser/App → index.php → includes/header.php → session → db.php → MariaDB → includes/functions → page render. AJAX → api/index.php → api/products.php → JSON → fetch(). Native → Capacitor → same backend. Payment → checkout.php → chapa_pay/ → redirect → callback. Email → helpers/mail.php → Brevo API]

---

## Code Samples Checklist

| File | Section | Lines |
|------|---------|-------|
| `includes/header.php` | 4.2.1, 4.2.2 | Navbar HTML |
| `includes/currency.php` | 4.3.6 | All 268 lines |
| `includes/cache.php` | 4.3.7 | All 41 lines |
| `includes/seo.php` | 4.3.8 | All 98 lines |
| `includes/db.php` | 4.10.3 | PDO setup, CSRF functions |
| `config.php` | 4.3.1 | Session config, error handler |
| `login.php` | 4.3.2, 4.3.3 | Login validation, GSI render |
| `google_login.php` | 4.3.3 | All 75 lines |
| `checkout.php` | 4.3.5 | Order creation block |
| `add_to_cart.php` | 4.2.4 | All 73 lines |
| `set_currency.php` | 4.2.6 | All 26 lines |
| `receipt.php` | 4.3.5 | Printable receipt HTML |
| `sw.js` | 4.5.1 | All ~45 lines |
| `manifest.json` | 4.5.2 | All 28 lines |
| `offline.html` | 4.5.3 | All ~35 lines |
| `chapa_pay/callback.php` | 4.7.3 | All 80 lines |
| `helpers/mail.php` | 4.8 | Key functions |
| `admin/reports.php` | 4.9.2 | Period filter, chart data |
| `admin/includes/product_handler.php` | 4.3.4 | Upload functions |
| `capacitor/capacitor.config.json` | 4.4.1 | All 17 lines |
| `toggle_wishlist.php` | 4.2.3 | All 49 lines |
| `submit_review.php` | 4.3.4 | Review creation |
| `api/index.php` | 4.10.6 | Dispatcher (15 lines) |
| `api/.htaccess` | 4.10.6 | Rewrite rule (4 lines) |
| `helpers/captcha.php` | 4.10.5 | verify_recaptcha() |
| `health.php` | 4.10.5 | Health check endpoint |

---

## Tables to Create

| Table | Section | Content |
|-------|---------|---------|
| 4.1 | 4.1 | Complete technology stack with versions and justifications |
| 4.2 | 4.2.5 | Checkout form fields with validation rules |
| 4.3 | 4.3.6 | Currency functions reference |
| 4.4 | 4.8 | Email types and trigger events |
| 4.5 | 4.10.5 | Third-party services summary |

---

## Key Warnings / Notes

- **Section 4.4** must be a complete rewrite — original documentation referenced Flutter; actual implementation uses Capacitor 6.x
- Code samples should be **short but functional** (15–30 lines each) — enough to demonstrate the pattern, not the entire file
- All prices in code should reference `smartmall_format_money()` — document that USD is base storage, ETB is display
- Emphasize the **`.env` loading order** pattern: `.env` must be loaded before `require_once 'includes/db.php'`
- Document the **POST-before-header** pattern used in `login.php`, `checkout.php` for redirect-based flows
- For Capacitor sections, emphasize the **Web OAuth client ID (type 1)** requirement (not Android type 3)

---

**Page Breakdown:**
  4.1:  3    4.2:  5    4.3:  10   4.4:  8    4.5:  3
  4.6:  2.5  4.7:  2    4.8:  1.5  4.9:  2    4.10: 4
  Total: ~41 pages

**Session Plan:**
  Session A (§§4.1–4.5): 29 pages — Tech Stack, Frontend, Backend, Mobile, PWA
  Session B (§§4.6–4.10): 12 pages — Database, Payment, Email, Admin, Integration

**Implementation effort:** ~8–10 hours (2 sessions)

**Closing:**
This chapter has presented a comprehensive implementation of the Smart Mall
e-commerce system, covering all layers from HTML/CSS frontend to payment gateway
integration and Capacitor mobile deployment. Each section implements the
architecture and design defined in Chapter 3, with standalone PHP files that
integrate into a unified system through shared includes, session state, and a
single MariaDB database. The result is a fully functional e-commerce platform
deployed at https://smartmall.unaux.com with a companion Capacitor Android
application, serving the Ethiopian market with multi-currency support, mobile
money payments (Chapa/telebirr), and push notification capabilities.
