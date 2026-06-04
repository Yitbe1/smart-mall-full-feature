# Smart Mall — Developer Guide

> **Document Version:** 1.0  
> **Applies to:** Smart Mall v1.0+  
> **Last Updated:** June 2026  
> **Document ID:** DEV-GUIDE-001  
> **Cross-Reference:** See *MASTER_DOCUMENTATION.md* §3.1 (System Architecture) and Chapter 6 (Deployment & Maintenance).

---

## Table of Contents

1. [Overview & Architecture](#1-overview--architecture)
2. [Project Structure](#2-project-structure)
3. [Bootstrap & Configuration Lifecycle](#3-bootstrap--configuration-lifecycle)
4. [Database Layer](#4-database-layer)
5. [Routing & Page Structure](#5-routing--page-structure)
6. [Form Handling & CSRF Protection](#6-form-handling--csrf-protection)
7. [Session Management & Authentication](#7-session-management--authentication)
8. [Template System](#8-template-system)
9. [E-Commerce Core Logic](#9-e-commerce-core-logic)
10. [Payment Integration](#10-payment-integration)
11. [API Endpoints](#11-api-endpoints)
12. [Admin Panel Architecture](#12-admin-panel-architecture)
13. [Security Patterns](#13-security-patterns)
14. [Error Handling & Logging](#14-error-handling--logging)
15. [Utilities & Helpers](#15-utilities--helpers)

---

## 1. Overview & Architecture

### 1.1 Technology Stack

| Layer | Technology | Rationale |
|-------|-----------|-----------|
| **Language** | PHP 8.0+ (procedural) | Simplicity, no framework overhead, direct execution |
| **Web Server** | Apache 2.4 with `mod_rewrite` | `.htaccess`-based URL restrictions |
| **Database** | MySQL 8.0 (InnoDB, `utf8mb4`) | ACID compliance for e-commerce transactions |
| **Payments** | Chapa (Ethiopian payment gateway) | Primary payment processor |
| **Email** | Brevo (Sendinblue) SMTP / API | Transactional emails |
| **CAPTCHA** | reCAPTCHA v3 (invisible) | Bot protection without user friction |
| **Auth** | Google Sign-In (GSI) + Email/Password | Dual authentication paths |
| **Push** | Firebase Cloud Messaging (Capacitor) | Native push notifications |
| **Frontend** | Vanilla JS + Inline CSS | No frontend framework dependency |

### 1.2 Architectural Decision Records (ADRs)

**ADR-001: Procedural PHP over Framework**
- *Context:* E-commerce application with well-defined CRUD operations and standard page flow.
- *Decision:* Use procedural PHP with `require_once` includes rather than Laravel/Symfony.
- *Consequence:* Each `.php` file is a standalone entry point. No routing layer. No autoloading for application code (Composer used only for third-party libraries).

**ADR-002: No MVC Separation**
- *Context:* Business logic, data access, and presentation are interleaved.
- *Decision:* POST handlers run before template output; database queries and HTML rendering coexist in the same file.
- *Consequence:* Each page file follows a strict sequencing pattern (see Section 5). Testing requires HTTP-level or database-level approaches.

**ADR-003: Global PDO Singleton**
- *Context:* Database connection reused across includes.
- *Decision:* A single global `$pdo` variable shared via `global $pdo` and returned by `getDB()`.
- *Consequence:* No connection pooling. `getDB()` must be called in every function that queries the database.

**ADR-004: `.env` as the Single Source of Truth**
- *Context:* Configuration values must not be hardcoded.
- *Decision:* Every file that needs DB credentials loads `.env` directly via `parse_ini_file()` before including `includes/db.php`.
- *Consequence:* Many API files duplicate `.env` parsing. `config.php` also parses `.env` — files loading `config.php` get it automatically.

### 1.3 Request Lifecycle

```
┌─────────────────────────────────────────────────────┐
│                     HTTP Request                      │
└──────────────────────┬──────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────┐
│  1. Apache receives request → .htaccess processed    │
│     • .env files denied (Lines 1-4)                   │
│     • composer files denied (Lines 6-9)               │
│     • sitemap.xml → PHP handler (Lines 11-14)         │
└──────────────────────┬──────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────┐
│  2. PHP File Execution (each .php is its own route)  │
│                                                       │
│  ┌─────────────────────────────────────────────────┐ │
│  │  2a. Set $page_title                             │ │
│  │  2b. require_once 'config.php'                     │ │
│  │      • Parses .env                                 │ │
│  │      • Sets error/exception handlers               │ │
│  │      • Starts session (hardened)                   │ │
│  │      • Checks idle timeout                         │ │
│  │      • Loads includes/db.php → PDO connection      │ │
│  │      • Loads includes/currency.php                 │ │
│  │      • Sets security headers                       │ │
│  │      • Activates output minification (prod)        │ │
│  │                                                    │ │
│  │  2c. Process POST handlers (form submissions)      │ │
│  │      • csrf_verify()                              │ │
│  │      • Validate input                              │ │
│  │      • Execute business logic (DB ops)             │ │
│  │      • Set flash messages                          │ │
│  │      • Redirect                                    │ │
│  │                                                    │ │
│  │  2d. include 'includes/header.php'                 │ │
│  │      • Cache-control headers                       │ │
│  │      • SEO meta tags                               │ │
│  │      • PWA manifest + service worker               │ │
│  │      • CSS (inline ~1750 lines)                    │ │
│  │      • Navigation HTML                             │ │
│  │                                                    │ │
│  │  2e. Page-specific HTML content                    │ │
│  │                                                    │ │
│  │  2f. include 'includes/footer.php'                 │ │
│  │      • Footer grid                                 │ │
│  │      • Newsletter form                             │ │
│  │      • Closing HTML tags                           │ │
│  └─────────────────────────────────────────────────┘ │
└──────────────────────┬──────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────┐
│  3. Output sent to browser                            │
│     • Production: minified via smartmall_minify_output│
│     • Development: raw output                         │
└─────────────────────────────────────────────────────┘
```

---

## 2. Project Structure

### 2.1 Root Directory

```
reference/                       # Document root
  .env                           # Configuration (excluded from web access)
  .htaccess                      # Security rules + sitemap handler
  config.php                     # Bootstrap: env, session, error handler, DB
  health.php                     # Admin-only health check endpoint
  index.php                      # Homepage

  includes/                      # Shared components
    db.php                       # PDO connection + CSRF + asset URL helpers
    header.php                   # <head>, nav, CSS, JS (~2651 lines)
    footer.php                   # Footer grid, newsletter, closing tags
    currency.php                 # Currency conversion + exchange rates
    seo.php                      # OG tags, canonical, JSON-LD breadcrumbs
  helpers/
    captcha.php                  # reCAPTCHA v3 verification
    mail.php                     # Brevo SMTP transactional email

  api/                           # REST-like endpoints
    products.php                 # GET product list/single
    categories.php               # GET category list/single
    orders.php                   # GET/POST orders
    search.php                   # GET search (uses config.php)
    index.php                    # API documentation
    .htaccess                    # Deny all except search.php

  admin/                         # Admin panel
    dashboard.php                # Dashboard with lifetime stats
    manage_products.php          # Product CRUD with pagination
    add_product.php              # Product creation form
    delete_product.php           # Product deletion
    manage_categories.php        # Category CRUD with inline editing
    manage_orders.php            # Order management
    manage_users.php             # User management (roles, verify)
    reports.php                  # Chart.js analytics
    includes/
      product_form.php           # Reusable product form renderer
      product_handler.php        # File upload + DB persistence

  chapa_pay/                     # Payment integration
    chapa-config.php             # Secret key + API URL constants
    callback.php                 # Chapa webhook handler

  deploy/                        # Deployment tooling
    deploy.sh                    # Pre-deployment validation
    init.sh                      # First-time setup
    migrate.php                  # Migration runner
    migrations/                  # 18 SQL migration files
    .htaccess                    # Deny all (web-inaccessible)

  assets/
    images/                      # Static images
    js/                          # Static JS files

  uploads/                       # Product images (writable)
  cache/                         # Exchange rate cache (writable)
  logs/                          # Error logs (writable)
  backups/                       # Database backups (writable)
  mail/                          # Email logs (development only)
  vendor/                        # Composer dependencies (gitignored)
```

### 2.2 File Naming Conventions

| Pattern | Example | Convention |
|---------|---------|-----------|
| `snake_case.php` | `manage_products.php` | All PHP files |
| `snake_case.sql` | `20260528_120000_initial_schema.sql` | Migration files |
| `snake_case` | `smartmall_db` | Database name |
| `camelCase` | `get_product_image_url()` | PHP functions |
| `UPPER_SNAKE_CASE` | `SMARTMALL_BASE_CURRENCY` | PHP constants |
| `PascalCase` | `SendTransacEmail` | Vendor classes (Brevo) |

### 2.3 Key Conventions

- **One page = one file:** Each URL path maps to a single `.php` file (no front controller).
- **POST before output:** All form processing happens before `include 'header.php'`.
- **`config.php` must come first:** Every page includes `config.php` before any other application code.
- **`.env` before `db.php`:** Files that don't use `config.php` must load `.env` manually before `includes/db.php`.
- **No hardcoded paths:** Use `get_product_image_url()`, `base_url_path()`, `asset_url()`.

---

## 3. Bootstrap & Configuration Lifecycle

### 3.1 `config.php` — The Application Kernel

Every page starts by including `config.php`. This file performs the entire bootstrap sequence (`config.php`).

#### Execution Order

| Step | Lines | Code | Effect |
|------|-------|------|--------|
| Output buffer | 3 | `ob_start()` | Captures all output for later minification |
| Load `.env` | 5-14 | `parse_ini_file()` | Populates `$_ENV` and `putenv()` |
| Define constants | 16-17 | `define('APP_ENV', ...)` | `APP_ENV` and `APP_VERSION` |
| Set error handler | 23-52 | `set_error_handler()` / `set_exception_handler()` | Custom handlers that show 500.php in production |
| Session config | 55-61 | `ini_set('session.*', ...)` | httponly, samesite, secure, strict_mode |
| Start session | 64-66 | `session_start()` | Only if not already active |
| Idle timeout | 69-76 | `$_SESSION['last_activity']` check | Destroys session after 1800s (30 min) |
| Session rotation | 79-82 | `session_regenerate_id()` | Every ~540s to prevent fixation |
| Base URL | 85-97 | Computed from `SERVER_NAME` | Protects against host header injection |
| Base path | 99-116 | `base_url_path()` | Auto-detected from `__DIR__` minus `DOCUMENT_ROOT` |
| Include DB | 118 | `require_once 'includes/db.php'` | Establishes PDO connection |
| Include currency | 119 | `require_once 'includes/currency.php'` | Loads exchange rate helpers |
| Security headers | 153-160 | `header('X-*')` | CSP, XFO, Referrer-Policy, etc. |
| Output minifier | 188-189 | `ob_start('smartmall_minify_output')` | Only in `APP_ENV === 'production'` |

### 3.2 Configuring Pages

Each page sets variables **before** including `config.php`:

```php
// Standard page header pattern
$page_title = 'Page Name - Smart Mall';       // Required for <title>
$page_description = 'Page description here.';  // Optional for meta description

require_once __DIR__ . '/config.php';          // Bootstrap everything

// POST handlers go here (before header.php)
// ...
include __DIR__ . '/includes/header.php';      // Output starts
// Page HTML here
include __DIR__ . '/includes/footer.php';      // Output ends
```

### 3.3 `.env` Parsing

```php
// config.php
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $_ENV = parse_ini_file($envFile) ?: [];
    foreach ($_ENV as $key => $value) {
        putenv("$key=$value");
    }
}
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
define('APP_VERSION', $_ENV['APP_VERSION'] ?? '1.0.0');
```

> [!IMPORTANT] `parse_ini_file()` returns `false` on failure (not empty array), so the `?: []` fallback is essential.

### 3.4 Idle Timeout Mechanism

```php
// config.php
$timeout = 1800; // 30 minutes
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    $_SESSION = [];
    session_destroy();
    session_start();
    session_regenerate_id(true);
    // No redirect — subsequent page logic handles the logged-out state
}
$_SESSION['last_activity'] = time();
```

### 3.5 Session Regeneration

```php
// config.php
$regenerateInterval = $timeout * 0.3; // ~540 seconds
if (!isset($_SESSION['_regenerated_at']) || (time() - $_SESSION['_regenerated_at'] > $regenerateInterval)) {
    session_regenerate_id(true);
    $_SESSION['_regenerated_at'] = time();
}
```

---

## 4. Database Layer

### 4.1 Database Connection (`includes/db.php`)

#### File `/opt/lampp/htdocs/reference/includes/db.php`

#### Connection Setup

```php
// includes/db.php — Credentials from $_ENV only, no fallback
$dbHost = $_ENV['DB_HOST'] ?? '';
$dbName = $_ENV['DB_NAME'] ?? '';
$dbUser = $_ENV['DB_USER'] ?? '';
$dbPass = $_ENV['DB_PASS'] ?? '';

// includes/db.php — Empty credential validation
if (empty($dbHost) || empty($dbName) || empty($dbUser)) {
    error_log("Database configuration missing in .env");
    die('Database configuration error.');
}

// includes/db.php — PDO instantiation
$dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, $dbUser, $dbPass, $options);
```

**Key points:**
- Uses `PDO::ERRMODE_EXCEPTION` — all DB errors throw exceptions.
- Uses `PDO::FETCH_ASSOC` — results returned as associative arrays.
- `PDO::ATTR_EMULATE_PREPARES = false` — real prepared statements from MySQL.
- `$pdo` is a **global variable**, not enclosed in a function or class.

#### `getDB()` Helper

```php
// includes/db.php
function getDB(): PDO
{
    global $pdo;
    if (!$pdo) {
        // Reconnection would go here (not currently implemented)
        throw new RuntimeException('Database not connected');
    }
    return $pdo;
}
```

### 4.2 Query Patterns

#### Standard SELECT

```php
$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = :category_id ORDER BY created_at DESC");
$stmt->execute([':category_id' => $categoryId]);
$products = $stmt->fetchAll();
```

#### Single Row

```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(); // Returns false if not found
```

#### INSERT with lastInsertId

```php
$stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, status) VALUES (:user_id, :total, :status)");
$stmt->execute([':user_id' => $userId, ':total' => $total, ':status' => 'pending']);
$orderId = $pdo->lastInsertId();
```

#### UPDATE with Row Count Check

```php
$stmt = $pdo->prepare("UPDATE products SET stock = stock - :qty WHERE product_id = :id AND stock >= :qty");
$stmt->execute([':qty' => $qty, ':id' => $productId]);
if ($stmt->rowCount() === 0) {
    // Insufficient stock — rollback transaction
}
```

### 4.3 Transaction Pattern

Used for order placement and payment processing (`checkout.php`, `api/orders.php`, `chapa_pay/callback.php`):

```php
$pdo = getDB();
try {
    $pdo->beginTransaction();

    // Row-level lock for stock check
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE product_id = :id FOR UPDATE");
    $stmt->execute([':id' => $productId]);
    $product = $stmt->fetch();

    if ($product['stock'] < $quantity) {
        throw new \Exception('Insufficient stock');
    }

    // Perform operations
    // ...

    $pdo->commit();
} catch (\Exception $e) {
    $pdo->rollBack();
    // Handle error
}
```

**Convention:** Always use `FOR UPDATE` when reading stock inside a transaction. Never read stock outside a transaction for write operations.

### 4.4 CSRF Protection System

Three functions in `includes/db.php` handle CSRF protection:

```php
// includes/db.php
function csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // 64-char hex
    }
    return $_SESSION['csrf_token'];
}

// includes/db.php
function csrf_field(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

// includes/db.php
function csrf_verify(): void
{
    if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token'])
        || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die('Invalid CSRF token');
    }
}
```

#### Usage Pattern
1. Token generated once per session (idempotent in `csrf_token()`)
2. `csrf_field()` outputs a hidden input in every `<form>`
3. `csrf_verify()` called first thing in every POST handler
4. Uses `hash_equals()` for timing-safe comparison

### 4.5 Asset URL Helpers

```php
// includes/db.php
function get_product_image_url(string $image_path): string
{
    if (empty($image_path)) {
        return base_url_path('assets/images/placeholder.png');
    }
    // Pass through external URLs
    if (str_starts_with($image_path, 'http://') || str_starts_with($image_path, 'https://')) {
        return $image_path;
    }
    return base_url_path('uploads/' . ltrim($image_path, '/'));
}

// includes/db.php (same pattern for video)
function get_product_video_url(string $video_path): string
{
    if (empty($video_path)) return '';
    if (str_starts_with($video_path, 'http://') || str_starts_with($video_path, 'https://')) {
        return $video_path;
    }
    return base_url_path('uploads/' . ltrim($video_path, '/'));
}
```

---

## 5. Routing & Page Structure

### 5.1 No Front Controller

Smart Mall does **not** use a front controller or routing layer. Each `.php` file in the document root corresponds to a URL path:

| URL | File |
|-----|------|
| `/reference/index.php` | `index.php` |
| `/reference/product.php?id=5` | `product.php` |
| `/reference/cart.php` | `cart.php` |
| `/reference/checkout.php` | `checkout.php` |
| `/reference/admin/manage_products.php` | `admin/manage_products.php` |
| `/reference/api/products.php` | `api/products.php` |

### 5.2 Page Lifecycle Pattern

Every page follows this strict sequence:

```php
<?php
// STEP 1: Page metadata
$page_title = 'Page Title - Smart Mall';
$page_description = 'SEO meta description.';

// STEP 2: Bootstrap
require_once __DIR__ . '/config.php';    // .env, session, DB, error handler

// STEP 3: Guards
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode(basename($_SERVER['PHP_SELF'])));
    exit;
}

// STEP 4: GET data loading
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = :id");
$stmt->execute([':id' => $_GET['id'] ?? 0]);
$product = $stmt->fetch();

// STEP 5: POST handlers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    // Validate, process, redirect
    $_SESSION['success'] = 'Done!';
    header('Location: ' . basename($_SERVER['PHP_SELF']));
    exit;
}

// STEP 6: Render
include __DIR__ . '/includes/header.php';
?>
<!-- HTML content -->
<?php include __DIR__ . '/includes/footer.php'; ?>
```

### 5.3 Redirect Pattern

```php
// config.php — Safe redirect with host validation
function redirect($path)
{
    $parsed = parse_url($path);
    $allowedHost = $_SERVER['SERVER_NAME'] ?? '';
    if (isset($parsed['host']) && $parsed['host'] !== $allowedHost) {
        $path = 'index.php'; // Open redirect prevention
    }
    header('Location: ' . $path);
    exit;
}

// Usage (cart.php, checkout.php):
redirect('/cart.php');
// or
header('Location: cart.php');
exit;
```

### 5.4 Query String vs Form Action

- **GET parameters** pass IDs: `product.php?id=5`, `manage_products.php?page=3`, `api/products.php?id=5`
- **POST actions** use hidden fields or submit button names:
  ```php
  // cart.php — detect action by submit button name
  if (isset($_POST['remove_item'])) { ... }
  if (isset($_POST['update_quantity'])) { ... }

  // checkout.php — detect action by hidden field
  $action = $_POST['action'] ?? '';
  if ($action === 'place_order') { ... }
  ```

---

## 6. Form Handling & CSRF Protection

### 6.1 Standard Form Handler

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    csrf_verify();  // Must be first — dies with 403 on failure

    // Validate input
    $errors = [];
    $name = trim($_POST['name'] ?? '');
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }

    if (empty($errors)) {
        // Process — insert/update/delete
        $stmt = $pdo->prepare("INSERT INTO table (...) VALUES (...)");
        $stmt->execute([...]);

        $_SESSION['success'] = 'Operation completed successfully.';
        redirect('current_page.php');
    }
    // If errors, fall through to template (errors displayed inline)
}
```

### 6.2 Form Template

```php
<form method="POST">
    <?php csrf_field(); ?>

    <label for="name">Name</label>
    <input type="text" name="name" id="name"
           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
    <?php if (isset($errors['name'])): ?>
        <span class="error"><?php echo htmlspecialchars($errors['name']); ?></span>
    <?php endif; ?>

    <button type="submit" name="action" value="submit">Submit</button>
</form>
```

### 6.3 Flash Message Display

Flash messages are stored in `$_SESSION` and displayed via JavaScript toast:

```php
// Set flash message (after successful operation)
$_SESSION['success'] = 'Product added to cart!';

// In template (cart.php):
<?php if (isset($_SESSION['success'])): ?>
<script>
document.addEventListener('DOMContentLoaded', () =>
    showToast(<?php echo json_encode($_SESSION['success']); ?>, "success")
);
</script>
<?php unset($_SESSION['success']); endif; ?>
```

#### Available Flash Session Keys

| Key | Type | Used By |
|-----|------|---------|
| `$_SESSION['success']` | String | cart.php, checkout.php, login.php, admin pages |
| `$_SESSION['cart_error']` | String | cart.php (displayed as "error" toast) |
| `$_SESSION['error']` | String | checkout.php |

### 6.4 Form Error Display (Inline)

```php
// Set errors in POST handler, display in template
// PHP (registration form style):
$errors = [];
if (empty($name)) $errors['name'] = 'Name is required';

// Template:
<?php if (!empty($errors)): ?>
<div class="form-errors">
    <?php foreach ($errors as $field => $msg): ?>
        <p class="error-msg"><?php echo htmlspecialchars($msg); ?></p>
    <?php endforeach; ?>
</div>
<?php endif; ?>
```

---

## 7. Session Management & Authentication

### 7.1 Session Variables Reference

| Variable | Type | Set By | Purpose |
|----------|------|--------|---------|
| `$_SESSION['user_id']` | int | login.php, register.php | Current user's primary key |
| `$_SESSION['user_name']` | string | login.php | Display name |
| `$_SESSION['user_email']` | string | login.php | User email address |
| `$_SESSION['user_role']` | string | login.php | `'admin'` or `'customer'` |
| `$_SESSION['currency']` | string | set_currency.php | `'USD'` or `'ETB'` |
| `$_SESSION['csrf_token']` | string | `csrf_token()` | 64-char hex CSRF token |
| `$_SESSION['success']` | string | Various | Flash message |
| `$_SESSION['cart_error']` | string | cart.php | Flash error message |
| `$_SESSION['last_activity']` | int | config.php | Unix timestamp for idle timeout |
| `$_SESSION['_regenerated_at']` | int | config.php | Timestamp for session ID rotation |

### 7.2 Login Flow

#### File `/opt/lampp/htdocs/reference/login.php`

```
1. GET request → show login form with:
   - Email/password fields
   - reCAPTCHA v3 (invisible)
   - Google Sign-In button (GSI on web, @capgo on Capacitor)

2. POST request → process login:
   - csrf_verify()
   - verify_recaptcha() (score >= 0.5)
   - SELECT * FROM users WHERE email = :email
   - password_verify($password, $user['password'])
   - Check email_verified_at is not NULL
   - Set session: user_id, user_name, user_email, user_role
   - Sanitize redirect parameter: preg_match('/^[a-zA-Z0-9_-]+\.php$/', $redirect)
   - Redirect to original page or index.php
```

#### Password Verification

```php
// login.php
if (!password_verify($password, $user['password'])) {
    $errors['login'] = 'Invalid email or password.';
}
```

### 7.3 Registration Flow

#### File `/opt/lampp/htdocs/reference/register.php`

```
1. Password rules (register.php):
   - Minimum 8 characters
   - At least 1 uppercase letter
   - At least 1 lowercase letter
   - At least 1 digit
   - At least 1 special character (!@#$%^&*()-_+=)

2. Password hashing:
   password_hash($password, PASSWORD_BCRYPT)  // register.php

3. User insertion:
   INSERT INTO users (name, email, password, role)
   VALUES (:name, :email, :password, 'customer')  // register.php

4. Verification token:
   - $token = sha256(random_bytes(32))
   - Stored as verification_token + verification_token_expires_at (5 minutes)
   - Email sent via send_mail() with verify_email.php?token=... link
```

#### Email Verification Link Pattern

```
http://localhost/reference/verify_email.php?token={64-char-hex}
```

The `verify_email.php` handler:
1. Looks up token in `users` table
2. Checks `verification_token_expires_at` is still in the future
3. Sets `email_verified_at = NOW()`, clears token fields

### 7.4 Authentication Guards

```php
// Check logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode(basename($_SERVER['PHP_SELF'])));
    exit;
}

// Check admin role
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}
```

**Files with login guards:** cart.php, checkout.php, orders.php, wishlist.php

**Files with admin guards:** All `admin/*.php`, health.php

### 7.5 Google Sign-In Integration

Dual-path architecture (`google_login.php`):

#### Website Path (GSI Declarative)

```html
<!-- login.php — GSI button -->
<div id="g_id_onload"
     data-client_id="CLIENT_ID"
     data-callback="handleGoogleCredential"
     data-auto_prompt="false">
</div>
<div class="g_id_signin" data-type="standard" data-theme="outline"></div>
```

```javascript
// login.php — Callback
function handleGoogleCredential(response) {
    fetch('google_login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'credential=' + encodeURIComponent(response.credential)
    })
    .then(r => r.json())
    .then(d => { if (d.success) window.location.href = d.redirect; });
}
```

#### Capacitor Path (`@capgo/capacitor-social-login`)

```javascript
// login.php
await SocialLogin.initialize({ google: { webClientId: '...' } });
const result = await SocialLogin.login({ provider: 'google', options: [...] });
fetch('google_login.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'credential=' + encodeURIComponent(result.credential.token)
});
```

#### Server-Side Verification (`google_login.php`)

```php
require_once 'vendor/autoload.php';
$client = new Google\Client(['client_id' => '1003727523085-ff6nuocamjk...']);
$payload = $client->verifyIdToken($credential);
if ($payload) {
    $googleId = $payload['sub'];
    $email = $payload['email'];
    $name = $payload['name'];
    // Find or create user by google_id
}
```

### 7.6 Logout

#### File `/opt/lampp/htdocs/reference/logout.php`

```php
// logout.php
require_once __DIR__ . '/config.php';

// Try to remove FCM device token (wrapped in try-catch — table may be missing)
try {
    if (isset($_SESSION['user_id'])) {
        $pdo = getDB();
        $stmt = $pdo->prepare("DELETE FROM device_tokens WHERE user_id = :uid");
        $stmt->execute([':uid' => $_SESSION['user_id']]);
    }
} catch (\Exception $e) {
    // Table might not exist — ignore
}

$_SESSION = [];
session_destroy();
header('Location: login.php');
exit;
```

---

## 8. Template System

### 8.1 `includes/header.php` — The Shell

#### File `/opt/lampp/htdocs/reference/includes/header.php` (2651 lines)

#### PHP Logic (Lines 1-65)

```php
// Line 15: Current page detection for nav highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Line 16: Default page title
$page_title = $page_title ?? 'Smart Mall - Your Online Marketplace';

// Lines 22-23: Relative path for admin pages
$rel = strpos($current_page, 'admin/') === 0 ? '../' : '';

// Lines 25-33: Cart count (wrapped in try/catch)
try {
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = :uid");
        $stmt->execute([':uid' => $_SESSION['user_id']]);
        $cart_count = (int)$stmt->fetchColumn();
    }
} catch (\Throwable $e) { $cart_count = 0; }

// Lines 36-51: Admin notifications
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    // Fetch unread count + top 5
}
```

#### Head Section (Lines 69-1916)
- PWA manifest (`manifest.json`) with cache-busting `?v=APP_VERSION`
- Service worker registration (`sw.js`)
- FCM push notification setup (Capacitor native)
- Dark mode detection from `localStorage` or `prefers-color-scheme`
- `<title>` with `htmlspecialchars()`
- SEO meta tags via `seo_og_tags()` and `seo_canonical()` (lines 141-142)
- Google Fonts preconnect + stylesheet (Outfit, Inter, Poppins)
- Inline CSS (~1750 lines): full design system with CSS variables for light/dark themes

#### Navigation (Lines 1919-2184)
- Preloader (logo animation for ~2.6s)
- `BASE_PATH` exposed to JavaScript via inline script
- Toast notification container (global `showToast()`)
- Nav links with `is-active` class for current page
- Admin notification bell (conditional on `user_role === 'admin'`)
- Live search bar (300ms debounce, fetches `api/search.php`)
- Currency selector (form to `set_currency.php`)
- Cart icon with badge count
- User dropdown profile/orders/admin-panel/logout
- Theme toggle (light/dark)

#### Mobile Drawer (Lines 2187-2338)
Backdrop overlay, slide-out drawer with search, currency, nav, user actions.

#### JavaScript (Lines 2341-2651)
- Preloader exit animation
- Scroll-based nav styling
- Notification toggle + mark-as-read via `navigator.sendBeacon()` and `fetch()`
- Live search with debounce
- Theme toggle
- Global `showToast()` function

### 8.2 `includes/footer.php`

#### File `/opt/lampp/htdocs/reference/includes/footer.php` (398 lines)

Footer grid with 4 columns:
- Brand description
- Shop links (categories, products)
- Company links (about, contact, privacy)
- Contact info

Newsletter signup form (POST to `subscribe.php`).

### 8.3 SEO System (`includes/seo.php`)

#### File `/opt/lampp/htdocs/reference/includes/seo.php` (98 lines)

```php
// seo.php — Open Graph + Twitter Card tags
function seo_og_tags(string $title = '', string $description = '', string $url = '', string $image = ''): void
{
    // Outputs:
    // <meta property="og:title" content="...">
    // <meta property="og:description" content="...">
    // <meta property="og:url" content="...">
    // <meta property="og:image" content="...">
    // <meta property="og:type" content="website">
    // <meta name="twitter:card" content="summary_large_image">
    // <meta name="twitter:title" content="...">
    // <meta name="twitter:description" content="...">
    // <meta name="twitter:image" content="...">
}

// seo.php — Canonical URL
function seo_canonical(string $url = ''): void
{
    // <link rel="canonical" href="...">
}

// seo.php — JSON-LD BreadcrumbList
function seo_jsonld_breadcrumb(array $crumbs): void
{
    // <script type="application/ld+json">
    // { "@context": "...", "@type": "BreadcrumbList", ... }
}
```

#### Calling Convention

```php
// In header.php (lines 141-142):
seo_og_tags($page_title, $page_description ?? '');
seo_canonical();

// In product.php:
$page_title = 'Product Name - Smart Mall';
$page_description = 'Short product description';
// seo.php functions called automatically by header.php
```

### 8.4 Currency Display (`includes/currency.php`)

#### File `/opt/lampp/htdocs/reference/includes/currency.php` (268 lines)

```php
// Constants
define('SMARTMALL_BASE_CURRENCY', 'USD');
define('SMARTMALL_EXCHANGE_API_URL', 'https://open.er-api.com/v6/latest/USD');

// Key functions:
smartmall_supported_currencies(): array       // ['USD', 'ETB']
smartmall_selected_currency(): string          // Session value or 'USD'
smartmall_convert_money(float $amountUsd, ?string $currency = null): float
smartmall_format_money(mixed $amountUsd, ?string $currency = null): string
smartmall_exchange_rate(?string $currency = null): float
```

#### Caching Strategy
1. Check disk cache file in `sys_get_temp_dir()` for cached rates with `expires_at` timestamp
2. If expired, fetch from `open.er-api.com` via cURL or `file_get_contents()`
3. If fetch fails, use stale cache (return with `stale => true`)
4. Ultimate fallback: zero rates

---

## 9. E-Commerce Core Logic

### 9.1 Cart System

#### File `/opt/lampp/htdocs/reference/cart.php`

#### Add to Cart (`add_to_cart.php`)

```php
// add_to_cart.php
if (isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
    $productId = (int)$_GET['product_id'];

    // Check if already in cart
    $stmt = $pdo->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = :uid AND product_id = :pid");
    $stmt->execute([':uid' => $_SESSION['user_id'], ':pid' => $productId]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Increment quantity
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE cart_id = :cid");
        $stmt->execute([':cid' => $existing['cart_id']]);
    } else {
        // Insert new row
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (:uid, :pid, 1)");
        $stmt->execute([':uid' => $_SESSION['user_id'], ':pid' => $productId]);
    }
}
```

#### Cart Operations (`cart.php`)

| Action | Detection | Verification | Operation |
|--------|-----------|-------------|-----------|
| Remove | `isset($_POST['remove_item'])` | `csrf_verify()` + ownership check (`SELECT user_id FROM cart WHERE cart_id = :cid`) | `DELETE FROM cart WHERE cart_id = :cid` |
| Update | `isset($_POST['update_quantity'])` | `csrf_verify()` + ownership + stock check | `UPDATE cart SET quantity = :qty WHERE cart_id = :cid` |

#### Ownership Verification Pattern

```php
// cart.php — Verify item belongs to current user
$stmt = $pdo->prepare("SELECT user_id FROM cart WHERE cart_id = :cart_id");
$stmt->execute([':cart_id' => $cartId]);
$item = $stmt->fetch();

if (!$item || $item['user_id'] != $_SESSION['user_id']) {
    $_SESSION['cart_error'] = 'Item not found.';
    redirect('/cart.php');
}
```

### 9.2 Checkout Flow

#### File `/opt/lampp/htdocs/reference/checkout.php` (760 lines)

```
1. GET request:
   - Load cart items with product details (JOIN)
   - Calculate subtotal + 10% tax
   - Display checkout form (address, payment method, order summary)

2. POST request (action = place_order):
   - csrf_verify()
   - beginTransaction()
   - FOR UPDATE on stock rows
   - Re-validate stock availability
   - Determine payment method: chapa / bank_transfer / cod
   - For Chapa:
     a. Convert total to ETB via smartmall_convert_money()
     b. INSERT INTO payments (tx_ref = 'ORD-{order_id}-{date}')
     c. cURL POST to https://api.chapa.co/v1/transaction/initialize
     d. Redirect user to Chapa checkout URL
   - For bank_transfer / cod:
     a. INSERT INTO orders (status = 'pending')
     b. INSERT INTO order_items
     c. commit()
     d. Clear cart
     e. Redirect to order_confirmation.php
```

#### Stock Lock Pattern (`checkout.php`)

```php
$stmt = $pdo->prepare("SELECT stock, price FROM products WHERE product_id = :id FOR UPDATE");
$stmt->execute([':id' => $item['product_id']]);
$product = $stmt->fetch();

if ($product['stock'] < $item['quantity']) {
    throw new \Exception("Insufficient stock for {$item['name']}");
}
```

### 9.3 Order System

#### Order States

| Status | Meaning | Set By |
|--------|---------|--------|
| `pending` | Order created, awaiting processing | checkout.php (non-Chapa), api/orders.php |
| `processing` | Payment confirmed | chapa_pay/callback.php |
| `completed` | Fulfilled | admin/manage_orders.php |
| `cancelled` | Cancelled | admin/manage_orders.php, chapa_pay/callback.php (payment failed) |

#### Order Cancellation Rule (`orders.php`)

```php
// Only "pending" orders can be cancelled by the user
if ($order['status'] !== 'pending') {
    $_SESSION['cart_error'] = 'This order can no longer be cancelled.';
    redirect('/orders.php');
}
```

### 9.4 Wishlist

#### Files `wishlist.php`, `toggle_wishlist.php`

```php
// toggle_wishlist.php — Called via AJAX
if (isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
    $pid = (int)$_GET['product_id'];
    $uid = $_SESSION['user_id'];

    // Check if already in wishlist
    $stmt = $pdo->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = :uid AND product_id = :pid");
    $stmt->execute([':uid' => $uid, ':pid' => $pid]);

    if ($stmt->fetch()) {
        // Remove
        $pdo->prepare("DELETE FROM wishlist WHERE user_id = :uid AND product_id = :pid")
            ->execute([':uid' => $uid, ':pid' => $pid]);
    } else {
        // Add
        $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (:uid, :pid)")
            ->execute([':uid' => $uid, ':pid' => $pid]);
    }
}
```

### 9.5 Product Reviews

#### File `submit_review.php`

```php
// POST handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $rating = (int)($_POST['rating'] ?? 0);
    $review = trim($_POST['review'] ?? '');

    if ($rating >= 1 && $rating <= 5 && !empty($review)) {
        $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, review)
                               VALUES (:pid, :uid, :rating, :review)");
        $stmt->execute([...]);
    }
}
```

---

## 10. Payment Integration

### 10.1 Chapa Configuration

#### File `/opt/lampp/htdocs/reference/chapa_pay/chapa-config.php`

```php
$key = $_ENV['CHAPA_SECRET_KEY'] ?? '';
define('CHAPA_SECRET_KEY', $key);
define('CHAPA_API_URL', 'https://api.chapa.co/v1');
```

### 10.2 Checkout Initialization

```php
// checkout.php — Initialize Chapa transaction
$chapaData = [
    'amount'        => $totalInEtb,
    'currency'      => 'ETB',
    'tx_ref'        => $txRef,
    'return_url'    => $base_url . 'order_confirmation.php?order_id=' . $orderId,
    'callback_url'  => $base_url . 'chapa_pay/callback.php',
    'first_name'    => $user['name'],
    'email'         => $user['email'],
];

$ch = curl_init(CHAPA_API_URL . '/transaction/initialize');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . CHAPA_SECRET_KEY,
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS     => json_encode($chapaData),
    CURLOPT_RETURNTRANSFER => true,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpCode === 200) {
    $result = json_decode($response, true);
    if ($result['status'] === 'success') {
        header('Location: ' . $result['data']['checkout_url']);
        exit;
    }
}
```

### 10.3 Chapa Callback (Webhook)

#### File `/opt/lampp/htdocs/reference/chapa_pay/callback.php` (80 lines)

```
1. Read POST body as JSON
2. Extract tx_ref and status
3. Verify HMAC signature:
   hash_hmac('sha256', $rawBody, CHAPA_SECRET_KEY) === $_SERVER['HTTP_X_CHAPA_SIGNATURE']
4. Look up payment record by tx_ref
5. beginTransaction()
6. SELECT ... FOR UPDATE on payment row
7. Update:
   - Success: payments.status = 'paid', orders.status = 'processing',
     decrement product stock via order_items JOIN
   - Failure: payments.status = 'failed', orders.status = 'cancelled'
8. commit()
9. Return HTTP 200 ("OK")
```

---

## 11. API Endpoints

### 11.1 API Structure

All API files live in `api/` and return JSON. The `.htaccess` denies direct access to everything except `search.php`.

#### Common Patterns
- JSON content type: `header('Content-Type: application/json')`
- CORS: `header('Access-Control-Allow-Origin: *')` (all except search.php)
- `.env` loaded manually (except search.php which uses `config.php`)
- Global try/catch returns `{"error": "Database error"}` with HTTP 500

### 11.2 Endpoint Reference

#### `GET /api/index.php`

Returns self-describing endpoint list. No parameters.

```json
{
    "name": "Smart Mall API",
    "version": "1.0.0",
    "base_url": "http://localhost/reference/api",
    "endpoints": [
        {"path": "/products", "methods": ["GET"], "description": "List products"},
        {"path": "/categories", "methods": ["GET"], "description": "List categories"},
        {"path": "/orders", "methods": ["GET", "POST"], "description": "Manage orders"},
        {"path": "/search", "methods": ["GET"], "description": "Search products"}
    ]
}
```

#### `GET /api/products.php`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | int | No | Single product ID |
| `category_id` | int | No | Filter by category |

**Response (single):** `{"product_id": 1, "name": "...", "price": 99.99, "image_url": "...", "category_name": "..."}`

**Response (list):** `[{"product_id": 1, ...}, {"product_id": 2, ...}]`

**Response (not found):** `{"error": "Product not found"}` (HTTP 404)

#### `GET /api/categories.php`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | int | No | Single category (includes products) |

**Response (with `id`):** Includes `products` array nested in category object.

#### `GET /api/orders.php`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | int | No | Single order with items |
| `user_id` | int | No (for list) | List orders for user |

#### `POST /api/orders.php`

Request body (JSON):

```json
{
    "user_id": 1,
    "items": [
        {"product_id": 5, "quantity": 2}
    ]
}
```

Response (201): `{"success": true, "order_id": 42, "total_price": 199.98, "status": "pending"}`

#### `GET /api/search.php`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `q` | string | Yes | Search query (min 1 char) |

**Response:** `[{"product_id": 1, "name": "...", "price": 99.99, "image_url": "...", "display_price": "$99.99"}]`

**Limit:** 6 results. Search uses `LIKE '%query%'` on `name` and `description`.

### 11.3 Error Response Format

All API errors follow this structure:

```json
{
    "error": "Human-readable error message"
}
```

#### Error Codes

| HTTP Status | Meaning | Source |
|-------------|---------|--------|
| 400 | Bad request (missing params) | api/orders.php |
| 404 | Resource not found | api/products.php |
| 405 | Method not allowed | api/orders.php |
| 500 | Database/server error | All API catch blocks |

### 11.4 Adding a New API Endpoint

1. Create `api/new_endpoint.php`
2. Load `.env` manually: `parse_ini_file()` + `putenv()`
3. Include `../includes/db.php`
4. Set JSON content type and CORS header
5. Handle GET/POST + parameters
6. Wrap in try/catch with 500 fallback
7. Update `api/index.php` endpoint list
8. (Optional) Update `api/.htaccess` if search.php-level access is needed

---

## 12. Admin Panel Architecture

### 12.1 Admin Page Pattern

All admin pages follow the same structure:

```php
// admin/dashboard.php (typical admin page bootstrap)
$page_title = 'Dashboard - Admin - Smart Mall';
require_once __DIR__ . '/../config.php';  // Adjust path from admin/ subdirectory

// Admin guard
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Optional notification creation
// ...

include __DIR__ . '/../includes/header.php';  // $rel = '../' in header.php
?>
<!-- Admin content -->
<?php include __DIR__ . '/../includes/footer.php'; ?>
```

### 12.2 Admin Pages Summary

| Page | File | Key Operations | Unique Patterns |
|------|------|---------------|-----------------|
| Dashboard | `admin/dashboard.php` (366 lines) | 5 lifetime totals | Raw aggregate queries, quick-action buttons |
| Manage Products | `admin/manage_products.php` (513 lines) | List, search, paginate (20/page) | Server-side search, pagination links |
| Add Product | `admin/add_product.php` + `admin/includes/product_form.php` (540 lines) + `admin/includes/product_handler.php` (203 lines) | Create product with images/video | File upload, `enctype="multipart/form-data"`, `finfo` MIME detection |
| Delete Product | `admin/delete_product.php` | Remove product + files | File cleanup from uploads/ |
| Categories | `admin/manage_categories.php` (762 lines) | CRUD with inline editing | JavaScript inline edit, product-count deletion guard |
| Orders | `admin/manage_orders.php` (749 lines) | List 100 most recent, update status | Client-side search, status whitelist (pending/processing/completed/cancelled) |
| Users | `admin/manage_users.php` (686 lines) | Verify, toggle admin, delete | Self-protection (can't delete self), verify/unverify |
| Reports | `admin/reports.php` (864 lines) | 6 Chart.js charts | 10 period options, Order/top product/revenue/user/category/review trends |

### 12.3 Product Form (`admin/includes/product_form.php`)

#### Function Signature

```php
function render_product_form(
    ?array $product,        // Existing product data (null for new)
    array $categories,      // All categories for dropdown
    array $errors,          // Validation errors from POST handler
    bool $is_edit,          // true = edit mode, false = create
    ?array $current_additional = null  // Existing additional images
): void
```

#### Form Fields

| Field | Type | Validation | Notes |
|-------|------|------------|-------|
| Category | `<select>` | Required, valid ID | From categories table |
| Name | `<input text>` | Required, max 255 | — |
| Description | `<textarea>` | Required | Rich text (plain) |
| Price | `<input number>` | Required, numeric, > 0 | USD |
| Stock | `<input number>` | Required, int, >= 0 | — |
| Cover Image | `<input file>` | Max 5MB, jpg/png/gif/webp | `finfo` MIME check |
| Slide 2, Slide 3 | `<input file>` | Same as cover | Secondary angles |
| Gallery Images | `<input file multiple>` | Max 4 per upload | Bulk upload |
| Video | `<input file>` | Max 50MB, mp4/webm | — |

### 12.4 Product Handler (`admin/includes/product_handler.php`)

#### Function Reference

| Function | Lines | Purpose |
|----------|-------|---------|
| `upload_error_message(int $code)` | 6-19 | Maps PHP `$_FILES` error codes to readable messages |
| `handle_main_image_upload(?array $file, ?string $existing_image, bool $is_edit, string $upload_dir)` | 21-81 | Validate, generate safe filename, move file, delete old on edit |
| `handle_additional_images_upload(?array $slide2, ?array $slide3, ?array $bulk, array $existing_names, string $upload_dir)` | 83-127 | Process slide 2/3 + bulk gallery uploads |
| `handle_video_upload(?array $file, ?string $existing_video, string $upload_dir)` | 129-150 | Validate video file, move to uploads |
| `delete_product_file(string $filename, string $upload_dir)` | 152-162 | Unlink file from filesystem |
| `save_product(PDO $pdo, array $data, bool $is_edit, ?int $product_id)` | 164-203 | INSERT or UPDATE products table |

#### Safe Filename Generation (`handle_main_image_upload`)

```php
// product_handler.php
$filename = 'product_' . date('YmdHis') . '_' . bin2hex(random_bytes(4))
    . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($origName, PATHINFO_FILENAME))
    . '.' . $ext;
```

#### File Upload Directory Resolution

```php
// product_handler.php
$uploadDir = __DIR__ . '/../../uploads/';
```

### 12.5 Admin Navigation

Admin navigation is **not** in a separate file. It's provided by `includes/header.php` with conditional rendering:

```php
// header.php (admin notification bell — conditional on role)
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    // Show notification bell with unread count
    // Show "Admin Panel" link in user dropdown
}
```

---

## 13. Security Patterns

### 13.1 SQL Injection Prevention

**All** database queries use PDO prepared statements with named parameters. No raw string interpolation in SQL:

```php
// ✅ Correct: prepared statement
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute([':email' => $email]);

// ❌ Never: string interpolation
$stmt = $pdo->query("SELECT * FROM users WHERE email = '$email'"); // NEVER
```

### 13.2 XSS Prevention

**All** user-controlled output is escaped with `htmlspecialchars()`:

```php
echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8');
```

**Exception:** The admin panel's notification system and JavaScript data injection use `json_encode()` which provides safe escaping:

```php
// Template: Safe JS injection
<script>
const data = <?php echo json_encode($products, JSON_HEX_TAG); ?>;
</script>

// Toast messages:
showToast(<?php echo json_encode($_SESSION['success']); ?>, "success");
```

### 13.3 CSRF Protection

Every POST form includes a CSRF token:

```php
// In form template:
<?php csrf_field(); ?>
// Output: <input type="hidden" name="csrf_token" value="...">

// In POST handler:
csrf_verify(); // Dies with 403 on mismatch
```

### 13.4 Password Security

- **Hashing:** `password_hash($password, PASSWORD_BCRYPT)` (register.php)
- **Verification:** `password_verify($password, $hash)` (login.php)
- **Validation:** Minimum rules enforced client-side and server-side (register.php):
  - 8+ characters, uppercase, lowercase, digit, special character

### 13.5 Open Redirect Prevention

```php
// config.php — redirect() validates host
function redirect($path) {
    $parsed = parse_url($path);
    $allowedHost = $_SERVER['SERVER_NAME'] ?? '';
    if (isset($parsed['host']) && $parsed['host'] !== $allowedHost) {
        $path = 'index.php';
    }
    header('Location: ' . $path);
    exit;
}

// login.php — Redirect parameter sanitization
if ($redirect && preg_match('/^[a-zA-Z0-9_-]+\.php$/', $redirect)) {
    // Only allows simple PHP filenames
}
```

### 13.6 Payment Webhook Verification

Chapa callbacks are verified with HMAC-SHA256:

```php
// chapa_pay/callback.php
$signature = $_SERVER['HTTP_X_CHAPA_SIGNATURE'] ?? '';
$expected = hash_hmac('sha256', $rawBody, CHAPA_SECRET_KEY);
if (!hash_equals($expected, $signature)) {
    http_response_code(403);
    exit('Invalid signature');
}
```

### 13.7 File Upload Security

```php
// admin/includes/product_handler.php
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    $errors['image'] = 'Invalid file type. Allowed: JPG, PNG, GIF, WebP.';
}
```

#### Additional Upload Protections
- Max 5MB for images, 50MB for videos
- Safe filename generation (timestamp + random hex)
- `move_uploaded_file()` instead of `copy()` or `rename()`

### 13.8 Session Hardening Summary

| Protection | Configuration | Location |
|-----------|--------------|----------|
| HTTPOnly cookies | `session.cookie_httponly = 1` | config.php |
| SameSite=Lax | `session.cookie_samesite = Lax` | config.php |
| Secure cookies | Conditional on HTTPS | config.php |
| Strict session mode | `session.use_strict_mode = 1` | config.php |
| Cookie-only sessions | `session.use_only_cookies = 1` | config.php |
| Idle timeout | 30 minutes (1800s) | config.php |
| Session ID rotation | Every ~540s | config.php |

---

## 14. Error Handling & Logging

### 14.1 Error Handler

```php
// config.php
function smartmall_error_handler($severity, $message, $file, $line): bool
{
    $logMessage = "[SmartMall Error] [$severity] $message in $file:$line";
    error_log($logMessage);

    if (APP_ENV === 'production') {
        http_response_code(500);
        include __DIR__ . '/errors/500.php';
        exit;
    }
    // In development, return false to let PHP's built-in handler display the error
    return false;
}
```

### 14.2 Exception Handler

```php
// config.php
function smartmall_exception_handler($e): void
{
    $logMessage = "[SmartMall Exception] {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}";
    error_log($logMessage);

    http_response_code(500);
    if (APP_ENV === 'production') {
        include __DIR__ . '/errors/500.php';
    } else {
        echo "<h1>Exception</h1><pre>" . htmlspecialchars($e) . "</pre>";
    }
    exit;
}
```

### 14.3 500 Error Page

#### File `/opt/lampp/htdocs/reference/errors/500.php` (24 lines)

Simple, safe error page with no sensitive information:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Something went wrong — Smart Mall</title>
</head>
<body>
    <div class="card">
        <h1>Something went wrong</h1>
        <p>We encountered an unexpected error. Our team has been notified.
           Please try again shortly.</p>
        <a href="../index.php" class="btn">Go to Homepage</a>
    </div>
</body>
</html>
```

### 14.4 Logging Locations

| Type | Destination | Trigger |
|------|-------------|---------|
| PHP errors | Apache error log / `error_log()` | `smartmall_error_handler()` |
| DB connection failures | Apache error log | `includes/db.php` |
| Email logs | `mail/*.eml` (development) | `_mail_log()` in `helpers/mail.php` |
| Email API failures | Apache error log | `helpers/mail.php` |
| Payment callback errors | Apache error log | `chapa_pay/callback.php` |
| Cart exceptions | Apache error log | `cart.php` try/catch blocks |

### 14.5 API Error Handling

All API files follow this pattern:

```php
try {
    // Business logic
    // ...
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    // In production: log the real error without exposing details
    error_log($e->getMessage());
}
```

---

## 15. Utilities & Helpers

### 15.1 `base_url_path()` — Base Path Resolution

```php
// config.php
function base_url_path(string $path = ''): string
{
    static $base = null;
    if ($base === null) {
        // Auto-detect from __DIR__ minus DOCUMENT_ROOT
        $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
        $scriptDir = dirname(__DIR__);
        $base = str_replace($docRoot, '', $scriptDir) . '/';
    }
    return $base . ltrim($path, '/');
}
```

**Usage:** `base_url_path('uploads/product.jpg')` → `/reference/uploads/product.jpg`

### 15.2 `asset_url()` — Cache-Busting Asset URLs

```php
// config.php
function asset_url(string $path): string
{
    return base_url_path($path) . '?v=' . APP_VERSION;
}
```

**Usage:** `asset_url('assets/images/logo.png')` → `/reference/assets/images/logo.png?v=1.0.0`

### 15.3 Email Functions (`helpers/mail.php`)

```php
// send_mail() supports two modes:
// 1. Brevo API (production) — uses brevo-php SDK
// 2. Log only (development) — writes .eml file to mail/ directory

send_mail(
    string $to,           // Recipient email
    string $subject,       // Email subject
    string $body,          // Plain text body
    ?string $from,         // Optional custom sender
    ?string $log_id,       // Optional reference ID for logging
    ?string $html_body     // Optional HTML body (preferred)
): bool
```

**Email template:** `email_html_template(string $body_html): string` returns a full responsive HTML email layout with gradient header.

### 15.4 reCAPTCHA v3 (`helpers/captcha.php`)

```php
function verify_recaptcha(): bool
{
    $token = $_POST['g-recaptcha-response'] ?? '';
    if (empty($token)) return false;

    $secret = $_ENV['RECAPTCHA_SECRET_KEY'] ?? '';
    $response = file_get_contents(
        'https://www.google.com/recaptcha/api/siteverify',
        false,
        stream_context_create(['http' => [
            'method' => 'POST',
            'content' => http_build_query([
                'secret' => $secret,
                'response' => $token
            ]),
            'timeout' => 5,
        ]])
    );
    $data = json_decode($response, true);
    return ($data['score'] ?? 0) >= 0.5;
}
```

### 15.5 HTML Minifier

```php
// config.php
function smartmall_minify_output(string $buffer): string
{
    // 1. Remove HTML comments (except IE conditional comments)
    // 2. Collapse whitespace (preserving newlines in <script>/<style>)
    // 3. Restore single spaces between tags
    return $minified;
}

// Activated only in production:
if (APP_ENV === 'production') {
    ob_start('smartmall_minify_output');
}
```

### 15.6 `time_elapsed()` — Relative Time

```php
// header.php
function time_elapsed(string $datetime): string
{
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', $timestamp);
}
```

### 15.7 `redirect()` — Safe Redirect

```php
// config.php
function redirect($path)
{
    $parsed = parse_url($path);
    $allowedHost = $_SERVER['SERVER_NAME'] ?? '';
    if (isset($parsed['host']) && $parsed['host'] !== $allowedHost) {
        $path = 'index.php'; // Open redirect prevention
    }
    header('Location: ' . $path);
    exit;
}
```

---

*End of Developer Guide*
