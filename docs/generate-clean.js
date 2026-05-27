const fs = require("fs");
const path = require("path");
const {
  Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
  Header, Footer, AlignmentType, LevelFormat, PageNumber, PageBreak,
  TableOfContents, HeadingLevel, BorderStyle, WidthType, ShadingType,
  ImageRun
} = require("docx");

const border = { style: BorderStyle.SINGLE, size: 1, color: "CCCCCC" };
const borders = { top: border, bottom: border, left: border, right: border };
const cellMargins = { top: 60, bottom: 60, left: 100, right: 100 };
const pageWidth = 12240;
const margin = 1440;
const contentWidth = pageWidth - 2 * margin;

function h(level, text) {
  const m = { 1: HeadingLevel.HEADING_1, 2: HeadingLevel.HEADING_2, 3: HeadingLevel.HEADING_3, 4: HeadingLevel.HEADING_4 };
  return new Paragraph({ heading: m[level], children: [new TextRun({ text, bold: true, font: "Arial" })] });
}

function p(text) {
  return new Paragraph({ spacing: { after: 120 }, children: [new TextRun({ text, font: "Arial", size: 22 })] });
}

function pb(label, value) {
  return new Paragraph({
    spacing: { after: 80 },
    children: [
      new TextRun({ text: label, bold: true, font: "Arial", size: 22 }),
      new TextRun({ text: String(value), font: "Arial", size: 22 }),
    ],
  });
}

function bt(text) {
  return new Paragraph({
    numbering: { reference: "bullets", level: 0 },
    spacing: { after: 60 },
    children: [new TextRun({ text, font: "Arial", size: 22 })],
  });
}

function nm(text) {
  return new Paragraph({
    numbering: { reference: "numbers", level: 0 },
    spacing: { after: 60 },
    children: [new TextRun({ text, font: "Arial", size: 22 })],
  });
}

function sn(text) {
  return new Paragraph({
    numbering: { reference: "subnum", level: 0 },
    spacing: { after: 60 },
    children: [new TextRun({ text, font: "Arial", size: 22 })],
  });
}

function sp(pts = 200) {
  return new Paragraph({ spacing: { after: pts }, children: [] });
}

function mon(text) {
  return new Paragraph({
    spacing: { after: 60 },
    shading: { fill: "F5F5F5", type: ShadingType.CLEAR },
    indent: { left: 360 },
    children: [new TextRun({ text, font: "Courier New", size: 20 })],
  });
}

function hCell(text, w) {
  return new TableCell({
    borders, width: { size: w, type: WidthType.DXA },
    shading: { fill: "2E4057", type: ShadingType.CLEAR },
    margins: cellMargins, verticalAlign: "center",
    children: [new Paragraph({
      alignment: AlignmentType.CENTER,
      children: [new TextRun({ text, bold: true, font: "Arial", size: 20, color: "FFFFFF" })],
    })],
  });
}

function dCell(text, w, alt) {
  return new TableCell({
    borders, width: { size: w, type: WidthType.DXA },
    shading: alt ? { fill: "F9F9F9", type: ShadingType.CLEAR } : undefined,
    margins: cellMargins,
    children: [new Paragraph({ children: [new TextRun({ text: String(text), font: "Arial", size: 20 })] })],
  });
}

function cTable(rows, widths) {
  return new Table({
    width: { size: contentWidth, type: WidthType.DXA },
    columnWidths: widths,
    rows: [
      new TableRow({ children: rows[0].map((h, i) => hCell(h, widths[i])) }),
      ...rows.slice(1).map((r, ri) => new TableRow({
        children: r.map((c, i) => dCell(c, widths[i], ri % 2 === 1)),
      })),
    ],
  });
}

function img(filename, wPx = 550) {
  const fp = path.join(__dirname, "screenshots", filename);
  if (!fs.existsSync(fp)) return [];
  const data = fs.readFileSync(fp);
  const emuW = Math.round(wPx * 914400 / 96);
  const emuH = Math.round(emuW * 0.667);
  return [
    new Paragraph({
      alignment: AlignmentType.CENTER, spacing: { before: 200, after: 200 },
      children: [new ImageRun({ type: "png", data, transformation: { width: emuW, height: emuH } })],
    }),
  ];
}

const children = [];

// ===== TITLE PAGE =====
children.push(sp(3000));
children.push(new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 200 }, children: [new TextRun({ text: "Smart Mall", size: 72, bold: true, font: "Arial", color: "1A365D" })] }));
children.push(new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 100 }, children: [new TextRun({ text: "E-Commerce Platform", size: 48, font: "Arial", color: "2E4057" })] }));
children.push(new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 100 }, children: [new TextRun({ text: "Complete Technical Documentation", size: 36, font: "Arial", color: "4A6FA5" })] }));
children.push(new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 400 }, border: { bottom: { style: BorderStyle.SINGLE, size: 6, color: "2E4057", space: 1 } }, children: [] }));
children.push(sp(500));
children.push(new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ text: "Document Version 2.0", size: 24, font: "Arial", color: "888888" })] }));
children.push(new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ text: "May 2026", size: 24, font: "Arial", color: "888888" })] }));
children.push(new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 200 }, children: [new TextRun({ text: "smartmall.unaux.com", size: 24, font: "Arial", color: "888888" })] }));
children.push(sp(1000));
children.push(new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ text: "Prepared for academic submission", size: 22, font: "Arial", color: "999999", italics: true })] }));

children.push(new Paragraph({ children: [new PageBreak()] }));
children.push(h(1, "Table of Contents"));
children.push(new TableOfContents("Table of Contents", { hyperlink: true, headingStyleRange: "1-4" }));
children.push(new Paragraph({ children: [new PageBreak()] }));

// ===== PART I: PROJECT OVERVIEW =====
children.push(h(1, "Part I: Project Overview"));
children.push(sp());

children.push(h(2, "1. Introduction"));
children.push(p("Smart Mall is a full-stack e-commerce platform developed using PHP and MySQL, designed to serve the Ethiopian market with multi-currency support (USD and ETB). The platform supports the complete online retail lifecycle: product catalog management, customer registration and authentication, a server-side shopping cart, order processing with real-time stock validation, and payment processing through the Chapa payment gateway. A companion Capacitor mobile application wraps the web experience for Android deployment."));
children.push(p("This document provides a comprehensive technical reference for every component in the Smart Mall codebase. Each file is documented with its exact file path, purpose, dependencies, database interactions, and integration points with other components."));

children.push(h(2, "2. Technology Stack"));
children.push(cTable([
  ["Layer", "Technology", "Version / Notes"],
  ["Backend Language", "PHP", "8.0+, with PDO, cURL, MySQLi extensions"],
  ["Database", "MySQL / MariaDB", "MySQL 5.7+ / MariaDB 10.3+"],
  ["Database Access", "PDO", "Prepared statements with ERRMODE_EXCEPTION"],
  ["Web Server", "Apache", "mod_rewrite, XAMPP-compatible"],
  ["Frontend", "HTML5 + CSS3 + JavaScript", "Vanilla JS, CSS custom properties"],
  ["Payment Gateway", "Chapa API", "https://api.chapa.co/v1 (test mode)"],
  ["Exchange Rates", "ExchangeRate-API", "https://open.er-api.com/v6/latest/USD"],
  ["Mobile App", "Capacitor (WebView)", "v8, wraps the web app for Android"],
  ["PWA", "manifest.json + Service Worker", "Installable web app"],
], [2340, 4680, 2340]));

children.push(h(2, "3. Directory Structure"));
children.push(p("The project root is at /reference/. Every PHP file in the root directory serves as an independent page or handler. The includes directory holds shared components. The admin directory contains store management tools."));
children.push(sp());
["reference/", "  config.php", "  index.php", "  product.php", "  search.php", "  cart.php", "  checkout.php", "  order_confirmation.php", "  orders.php", "  login.php", "  register.php", "  logout.php", "  forgot_password.php", "  reset_password.php", "  about.php", "  contact.php", "  download.php", "  set_currency.php", "  add_to_cart.php", "  protect.php", "  manifest.json", "  includes/", "    db.php", "    currency.php", "    header.php", "    footer.php", "  admin/", "    dashboard.php", "    add_product.php", "    delete_product.php", "    manage_orders.php", "    manage_categories.php", "  api/search.php", "  chapa_pay/chapa-config.php", "  uploads/", "  assets/images/", "  docs/", "  smartmall-app/"].forEach(f => children.push(mon(f))));
children.push(sp());

children.push(h(2, "4. Request Lifecycle"));
children.push(p("Every page request follows a consistent bootstrap sequence:"));
nm("Apache routes HTTP request to target PHP file");
nm("Config.php starts ob_start, session, base URL detection, then requires db.php and currency.php");
nm("config.php emits security headers (X-Content-Type-Options, X-Frame-Options)");
nm("Page processes POST submissions with CSRF verification before including header.php");
nm("header.php sets cache headers, queries cart count, renders <head> and <body> with nav");
nm("Page renders main content between header.php and footer.php");
nm("footer.php closes HTML and includes JS assets");

children.push(new Paragraph({ children: [new PageBreak()] }));

// ===== PART II: DATABASE SCHEMA =====
children.push(h(1, "Part II: Database Schema"));
children.push(sp());

children.push(h(2, "5. Schema Overview"));
children.push(p("The Smart Mall database (smartmall_db) consists of eight InnoDB tables with UTF-8 MB4 encoding. Foreign keys enforce referential integrity with ON DELETE CASCADE on cart entries and ON DELETE RESTRICT on order-related tables."));

children.push(h(3, "5.1 Entity Relationship Summary"));
children.push(cTable([
  ["Table", "Foreign Keys", "References", "Purpose"],
  ["users", "—", "—", "Customer & admin accounts"],
  ["categories", "—", "—", "Product categories with slide images"],
  ["products", "category_id", "categories.id", "Product inventory"],
  ["cart", "user_id, product_id", "users.id, products.id (CASCADE)", "Shopping cart"],
  ["orders", "user_id", "users.id", "Placed orders with shipping"],
  ["order_items", "order_id, product_id", "orders.id, products.id", "Order line items"],
  ["payments", "order_id", "orders.id", "Payment transactions"],
  ["password_resets", "—", "—", "Password reset tokens"],
], [2000, 2000, 3000, 2960]));

children.push(sp(80));

const tw = [2340, 2000, 3000, 2620];
function schemaTable(data) {
  return cTable([["Column", "Type", "Constraints", "Notes"], ...data], tw);
}

children.push(h(3, "5.2 Table: users"));
children.push(schemaTable([
  ["id", "INT(11)", "PK, AUTO_INCREMENT", "Unique identifier"],
  ["name", "VARCHAR(100)", "NOT NULL", "Full name"],
  ["email", "VARCHAR(100)", "NOT NULL, UNIQUE", "Login identifier"],
  ["phone", "VARCHAR(20)", "DEFAULT NULL", "Contact number"],
  ["password", "VARCHAR(255)", "NOT NULL", "bcrypt hash"],
  ["role", "ENUM('customer','admin')", "DEFAULT 'customer'", "Authorization"],
  ["address", "TEXT", "DEFAULT NULL", "Shipping address"],
  ["created_at", "TIMESTAMP", "DEFAULT CURRENT_TIMESTAMP", "Creation time"],
]));
children.push(sp(80));

children.push(h(3, "5.3 Table: categories"));
children.push(schemaTable([
  ["id", "INT(11)", "PK, AUTO_INCREMENT", "Unique ID"],
  ["name", "VARCHAR(255)", "NOT NULL", "Display name"],
  ["slug", "VARCHAR(255)", "DEFAULT NULL", "URL-friendly"],
  ["image1", "VARCHAR(255)", "DEFAULT NULL", "Slide image 1"],
  ["image2", "VARCHAR(255)", "DEFAULT NULL", "Slide image 2"],
  ["image3", "VARCHAR(255)", "DEFAULT NULL", "Slide image 3"],
  ["created_at", "TIMESTAMP", "DEFAULT CURRENT_TIMESTAMP", "Creation time"],
]));
children.push(sp(80));

children.push(h(3, "5.4 Table: products"));
children.push(schemaTable([
  ["id", "INT(11)", "PK, AUTO_INCREMENT", "Unique ID"],
  ["category_id", "INT(11)", "FK -> categories(id)", "Category"],
  ["name", "VARCHAR(255)", "NOT NULL", "Product name"],
  ["description", "TEXT", "DEFAULT NULL", "Description"],
  ["price", "DECIMAL(10,2)", "NOT NULL", "Price in USD"],
  ["image", "VARCHAR(255)", "DEFAULT NULL", "Primary image"],
  ["images", "LONGTEXT", "DEFAULT NULL", "JSON additional images"],
  ["video", "VARCHAR(255)", "DEFAULT NULL", "Video filename"],
  ["stock", "INT(11)", "NOT NULL DEFAULT 0", "Quantity"],
  ["created_at", "TIMESTAMP", "DEFAULT CURRENT_TIMESTAMP", "Creation time"],
]));
children.push(sp(80));

children.push(h(3, "5.5 Table: cart"));
children.push(p("ON DELETE CASCADE on both foreign keys — deleting a user or product removes cart entries."));
children.push(schemaTable([
  ["id", "INT(11)", "PK, AUTO_INCREMENT", "Unique entry ID"],
  ["user_id", "INT(11)", "FK -> users(id) CASCADE", "Cart owner"],
  ["product_id", "INT(11)", "FK -> products(id) CASCADE", "Product"],
  ["quantity", "INT(11)", "NOT NULL DEFAULT 1", "Quantity"],
  ["created_at", "TIMESTAMP", "DEFAULT CURRENT_TIMESTAMP", "Added at"],
]));
children.push(sp(80));

children.push(h(3, "5.6 Table: orders"));
children.push(schemaTable([
  ["id", "INT(11)", "PK, AUTO_INCREMENT", "Unique ID"],
  ["user_id", "INT(11)", "FK -> users(id)", "Customer"],
  ["total_price", "DECIMAL(10,2)", "NOT NULL", "Subtotal + 10% VAT"],
  ["status", "ENUM('pending','processing','paid','shipped','delivered','cancelled')", "DEFAULT 'pending'", "Order state"],
  ["first_name", "VARCHAR(100)", "DEFAULT NULL", "Shipping first name"],
  ["last_name", "VARCHAR(100)", "DEFAULT NULL", "Shipping last name"],
  ["email", "VARCHAR(255)", "DEFAULT NULL", "Shipping email"],
  ["address", "TEXT", "DEFAULT NULL", "Street address"],
  ["city", "VARCHAR(100)", "DEFAULT NULL", "City"],
  ["state", "VARCHAR(100)", "DEFAULT NULL", "State"],
  ["zip", "VARCHAR(20)", "DEFAULT NULL", "Postal code"],
  ["country", "VARCHAR(100)", "DEFAULT 'Ethiopia'", "Country"],
  ["payment_method", "VARCHAR(50)", "DEFAULT 'chapa'", "chapa / bank_transfer / cod"],
  ["created_at", "TIMESTAMP", "DEFAULT CURRENT_TIMESTAMP", "Placed at"],
]));
children.push(sp(80));

children.push(h(3, "5.7 Table: order_items"));
children.push(p("Preserves price at purchase time — price changes do not affect historical orders."));
children.push(schemaTable([
  ["id", "INT(11)", "PK, AUTO_INCREMENT", "Unique ID"],
  ["order_id", "INT(11)", "FK -> orders(id)", "Parent order"],
  ["product_id", "INT(11)", "FK -> products(id)", "Product"],
  ["quantity", "INT(11)", "NOT NULL", "Quantity"],
  ["price", "DECIMAL(10,2)", "NOT NULL", "Price snapshot"],
]));
children.push(sp(80));

children.push(h(3, "5.8 Table: payments"));
children.push(schemaTable([
  ["id", "INT(11)", "PK, AUTO_INCREMENT", "Unique ID"],
  ["order_id", "INT(11)", "FK -> orders(id)", "Related order"],
  ["method", "VARCHAR(50)", "NOT NULL", "chapa / bank_transfer / cod"],
  ["status", "ENUM('pending','paid','failed')", "DEFAULT 'pending'", "Payment state"],
  ["amount", "DECIMAL(10,2)", "NOT NULL", "Amount charged"],
  ["currency", "VARCHAR(10)", "NOT NULL DEFAULT 'USD'", "USD or ETB"],
  ["tx_ref", "VARCHAR(100)", "DEFAULT NULL", "Chapa tx reference"],
  ["chapa_response", "LONGTEXT", "DEFAULT NULL", "Full API JSON response"],
  ["paid_at", "DATETIME", "DEFAULT NULL", "Confirmation time"],
  ["created_at", "DATETIME", "NOT NULL", "Creation time"],
]));
children.push(sp(80));

children.push(h(3, "5.9 Table: password_resets"));
children.push(schemaTable([
  ["id", "INT(11)", "PK, AUTO_INCREMENT", "Unique ID"],
  ["email", "VARCHAR(100)", "NOT NULL", "User email"],
  ["token", "VARCHAR(64)", "NOT NULL", "64-char hex token"],
  ["expires_at", "DATETIME", "NOT NULL", "Expiration (1 hour)"],
  ["created_at", "TIMESTAMP", "DEFAULT CURRENT_TIMESTAMP", "Creation time"],
]));

children.push(new Paragraph({ children: [new PageBreak()] }));

// ===== PART III: COMPONENT REFERENCE =====
children.push(h(1, "Part III: Component Reference"));
children.push(sp());
children.push(p("Every file in the Smart Mall codebase is documented with exact path, purpose, dependencies, database tables, and integration points."));
children.push(sp(80));

function comp(name, path, purpose, deps, tables, features, interactions, screenshot) {
  children.push(h(3, name));
  children.push(pb("File Path: ", path));
  children.push(pb("Purpose: ", purpose));
  if (deps) children.push(pb("Dependencies: ", deps));
  if (tables) children.push(pb("Database Tables: ", tables));
  if (features) {
    children.push(pb("Key Features:", ""));
    features.forEach(f => {
      if (f.startsWith("  ")) children.push(sn(f.trim()));
      else children.push(bt(f));
    });
  }
  if (interactions) children.push(pb("Interacts With: ", interactions));
  if (screenshot) img(screenshot).forEach(c => children.push(c));
  children.push(sp(80));
}

// --- Bootstrap ---
children.push(h(2, "6. Bootstrap & Configuration"));

comp("6.1 config.php", "/reference/config.php",
  "Application bootstrap executes before every page request",
  "includes/db.php, includes/currency.php", "None directly",
  [
    "Starts output buffering (ob_start) to catch BOM/whitespace",
    "Initializes PHP session if not already active",
    "Auto-detects base URL from HTTP_HOST + SCRIPT_NAME",
    "Defines global $base_url and redirect() helper",
    "Emits security headers: X-Content-Type-Options, X-Frame-Options",
    "No closing PHP tag to prevent accidental whitespace",
  ],
  "Every page file via require_once");

comp("6.2 includes/db.php", "/reference/includes/db.php",
  "PDO database connection and CSRF token helpers",
  "None (standalone, requires currency.php at end)", "All tables via $pdo",
  [
    "PDO with DSN charset=utf8mb4, ERRMODE_EXCEPTION, FETCH_ASSOC, real prepares",
    "getDB(): returns global $pdo connection",
    "CSRF token: bin2hex(random_bytes(32)) — 64-char hex, idempotent per session",
    "csrf_field(): hidden HTML input, csrf_verify(): hash_equals() check (403 on fail)",
    "get_product_image_url(path): resolves image paths",
    "get_product_video_url(path): same for videos",
  ],
  "Every page needing database access");

comp("6.3 includes/currency.php", "/reference/includes/currency.php",
  "Multi-currency engine — USD base, live ETB exchange rates",
  "None", "None",
  [
    "smartmall_supported_currencies(): returns ['USD', 'ETB']",
    "smartmall_selected_currency(): reads session, defaults to USD",
    "smartmall_exchange_cache_path(): sys_get_temp_dir() + '/smartmall_exchange_usd.json'",
    "smartmall_fetch_exchange_rates(): cURL call to open.er-api.com, caches with LOCK_EX",
    "smartmall_exchange_data(): returns cached rates or fresh; stale cache fallback on failure",
    "smartmall_exchange_rate(currency): returns rate (1.0 for USD, <rate> for ETB)",
    "smartmall_convert_money(amountUsd, currency): converts USD to target",
    "smartmall_format_money(amountUsd, currency): formats as $X.XX or ETB X.XX",
  ],
  "header.php, checkout.php, product.php, cart.php, order_confirmation.php, api/search.php");

comp("6.4 chapa_pay/chapa-config.php", "/reference/chapa_pay/chapa-config.php",
  "Chapa payment gateway configuration constants",
  "None", "None",
  [
    "CHAPA_SECRET_KEY: CHASECK_TEST-aF7ZWVLHJRP8rFpNG4V7rpDveopvXt2D (test)",
    "CHAPA_API_URL: https://api.chapa.co/v1",
  ],
  "checkout.php, order_confirmation.php");

children.push(new Paragraph({ children: [new PageBreak()] }));

// --- Auth ---
children.push(h(2, "7. Authentication System"));

comp("7.1 login.php", "/reference/login.php",
  "User login with email/password",
  "config.php, includes/db.php", "users",
  [
    "CSRF verification on POST before header.php output",
    "Prepared statement: SELECT id, name, email, password, role FROM users",
    "password_verify() for bcrypt comparison",
    "session_regenerate_id(true) to prevent fixation",
    "Session vars: user_id, user_name, user_email, user_role",
    "Redirect sanitized via preg_match('/^[a-zA-Z0-9_...]+\\.php$/', $redirect)",
  ],
  "header.php (displays login/logout link)");

comp("7.2 register.php", "/reference/register.php",
  "New user registration with password policy",
  "config.php", "users",
  [
    "CSRF verification on POST",
    "Name: min 2 chars; Email: FILTER_VALIDATE_EMAIL + duplicate check",
    "Password policy: 8+ chars, uppercase, lowercase, digit, special (4 preg_match)",
    "Hashing: password_hash($password, PASSWORD_BCRYPT)",
    "Role defaults to 'customer'",
    "Sets session success flash, redirects to login.php",
  ],
  "login.php, header.php");

comp("7.3 logout.php", "/reference/logout.php",
  "Session destruction and redirect",
  "None", "None",
  ["session_unset() + session_destroy()", "Redirects to index.php"]);

comp("7.4 forgot_password.php", "/reference/forgot_password.php",
  "Password reset request initiation",
  "config.php", "password_resets",
  [
    "CSRF verification on POST",
    "Token: bin2hex(random_bytes(32)) — 64-char hex",
    "Token stored with email and expires_at (1 hour)",
    "Reset link saved to session and sys_temp_dir/smartmall_reset_links.txt",
    "Attempts PHP mail() for delivery",
  ],
  "reset_password.php");

comp("7.5 reset_password.php", "/reference/reset_password.php",
  "Validate reset token and set new password",
  "config.php", "password_resets, users",
  [
    "Token validation via GET or POST parameter",
    "SELECT email, expires_at FROM password_resets WHERE token = :token",
    "Expiration check: strtotime(expires_at) > time()",
    "Same password policy as register.php",
    "Updates hash with password_hash, deletes used token",
  ],
  "forgot_password.php, login.php");

children.push(new Paragraph({ children: [new PageBreak()] }));

// --- Catalog ---
children.push(h(2, "8. Product Catalog"));

comp("8.1 index.php", "/reference/index.php",
  "Homepage — product grid with categories, search, slideshow",
  "config.php, header.php, footer.php", "products, categories",
  [
    "LEFT JOIN: products LEFT JOIN categories ON p.category_id = c.id",
    "Category filtering via $_GET['category']",
    "Search via $_GET['query'] with LIKE on name/description",
    "ORDER BY p.created_at DESC with LIMIT pagination",
    "INNER JOIN with order_items for best-sellers",
    "Category slideshow (up to 3 images/category)",
    "Price via smartmall_format_money() in USD and ETB",
  ],
  "product.php, cart.php, search.php", "01-homepage.png");

comp("8.2 product.php", "/reference/product.php",
  "Single product detail view with gallery and related products",
  "config.php, header.php, footer.php", "products, categories",
  [
    "Product loaded by ID from $_GET['id']",
    "Full details: name, description, price, stock, images, video",
    "Image gallery with prev/next navigation",
    "Related products: same category, exclude current, LIMIT 4",
    "Multi-image via images column (JSON array)",
    "Stock indicator",
  ],
  "add_to_cart.php, cart.php", "07-product-detail.png");

comp("8.3 api/search.php", "/reference/api/search.php",
  "JSON search autocomplete API",
  "config.php", "products",
  [
    "Content-Type: application/json",
    "GET ?q parameter, LIKE search, LIMIT 6",
    "Results with image_url and display_price via smartmall_format_money",
  ],
  "header.php search bar JavaScript");

children.push(new Paragraph({ children: [new PageBreak()] }));

// --- Cart ---
children.push(h(2, "9. Shopping Cart"));

comp("9.1 cart.php", "/reference/cart.php",
  "Full shopping cart management page",
  "config.php, header.php, footer.php", "cart, products",
  [
    "Requires login, redirects if not authenticated",
    "Remove item: POST with csrf_verify(), ownership check",
    "Update quantity: POST with stock limit enforcement",
    "JOIN: SELECT c.id, c.quantity, p.id, p.name, p.price, p.stock, p.image FROM cart c JOIN products p",
    "Subtotal, 10% VAT, grand total calculation",
    "Sticky cart notification bar in footer",
  ],
  "add_to_cart.php, checkout.php", "08-cart.png");

comp("9.2 add_to_cart.php", "/reference/add_to_cart.php",
  "AJAX JSON endpoint for adding products to cart",
  "config.php", "cart, products",
  [
    "Content-Type: application/json",
    "POST with product_id and quantity",
    "Requires auth (JSON error if not)",
    "Stock validation: products.stock >= quantity",
    "Quantity capping at stock max",
    "UPSERT: UPDATE if exists, INSERT if new",
    "Returns JSON: {success, message, cart_count, new_quantity}",
  ],
  "index.php, product.php (via JS fetch)");

children.push(new Paragraph({ children: [new PageBreak()] }));

// --- Checkout ---
children.push(h(2, "10. Checkout & Payment"));

comp("10.1 checkout.php", "/reference/checkout.php",
  "Order placement with Chapa payment initiation",
  "config.php, currency.php, chapa-config.php", "cart, products, orders, order_items, payments",
  [
    "POST handler before header.php for redirect capability",
    "Requires login",
    "Three methods: chapa, bank_transfer, cash_on_delivery",
    "PDO Transaction with beginTransaction()/commit()",
    "Row-level locking: SELECT ... FOR UPDATE for stock validation",
    "10% VAT: $tax = $total_price * 0.1",
    "Chapa ETB conversion via smartmall_convert_money()",
    "TxRef: 'ORD-' + order_id + '-' + YYYYMMDD",
    "Chapa: POST to /v1/transaction/initialize with Bearer auth",
    "Cart preserved for Chapa (cleared after verification)",
    "Cart cleared immediately for non-Chapa payments",
  ],
  "cart.php, order_confirmation.php, chapa_pay/chapa-config.php", "09-checkout.png");

comp("10.2 order_confirmation.php", "/reference/order_confirmation.php",
  "Post-payment verification, order display, payment state machine",
  "config.php, chapa-config.php", "orders, payments, order_items, products, cart",
  [
    "Three states: ALREADY PAID (skip), PENDING+CHAPA (verify), PENDING+NON-CHAPA",
    "Chapa verification: GET /v1/transaction/verify/{tx_ref}",
    "Requires both status='success' AND data.status='success'",
    "Test mode fallback: assumes success if key contains 'TEST'",
    "On success: status='paid', paid_at=NOW(), order='processing', clear cart",
    "On failure: status='failed', order='cancelled', restore stock",
    "Stock restoration: UPDATE SET stock = stock + qty for each item",
    "Ownership check against session user_id",
  ],
  "checkout.php, chapa_pay/chapa-config.php", "10-order-confirmation.png");

children.push(new Paragraph({ children: [new PageBreak()] }));

// --- Orders ---
children.push(h(2, "11. Order Management"));
comp("11.1 orders.php", "/reference/orders.php",
  "Customer order history page",
  "config.php, header.php, footer.php", "orders, payments, order_items",
  [
    "Requires login",
    "Fetches user's orders ORDER BY created_at DESC",
    "JOIN with payments for payment status",
    "Order cancellation: POST with csrf_verify, stock restoration",
    "Price via smartmall_format_money()",
  ],
  "order_confirmation.php, checkout.php", "11-orders.png");

children.push(new Paragraph({ children: [new PageBreak()] }));

// --- Admin ---
children.push(h(2, "12. Admin Panel"));

comp("12.1 admin/dashboard.php", "/reference/admin/dashboard.php",
  "Admin dashboard with store statistics",
  "config.php, header.php, footer.php", "products, categories, orders",
  [
    "Admin gate: $_SESSION['user_role'] !== 'admin' redirects",
    "Stats: COUNT products, SUM stock, COUNT orders, SUM total_price, COUNT categories",
    "Products table with LEFT JOIN categories, edit/delete actions",
  ],
  "add_product.php, delete_product.php, manage_orders.php, manage_categories.php", "12-admin-dashboard.png");

comp("12.2 admin/add_product.php", "/reference/admin/add_product.php",
  "Add and edit product form with image upload",
  "includes/db.php (manual require)", "products, categories",
  [
    "Dual-mode: add (no id) and edit (?id=N)",
    "Admin gate check",
    "File upload validation: type, size (5 MB max), error codes",
    "Supported: JPEG, PNG, GIF, WebP",
    "Multi-image via JSON array in images column",
    "Video upload support",
    "Media deletion via POST action=delete_media",
    "Category dropdown from categories table",
  ],
  "dashboard.php, uploads/", "15-admin-add-product.png");

comp("12.3 admin/delete_product.php", "/reference/admin/delete_product.php",
  "Product deletion handler (POST only)",
  "includes/db.php", "products",
  [
    "POST-only: rejects GET",
    "CSRF verification before deletion",
    "Deletes image file via unlink()",
    "DELETE FROM products WHERE id = :id",
  ],
  "dashboard.php");

comp("12.4 admin/manage_orders.php", "/reference/admin/manage_orders.php",
  "Order management with status updates",
  "config.php, header.php, footer.php", "orders, users, payments, order_items, products",
  [
    "Paid orders first: CASE WHEN pay.status='paid' THEN 1 ELSE 2 END",
    "Status update via POST with csrf_verify()",
    "Items via GROUP_CONCAT with product details",
    "JOIN: orders LEFT JOIN users, payments, order_items, products",
  ],
  "order_confirmation.php", "13-admin-orders.png");

comp("12.5 admin/manage_categories.php", "/reference/admin/manage_categories.php",
  "Category CRUD with up to 3 slide images",
  "config.php, header.php, footer.php", "categories",
  [
    "View all categories with slide image previews",
    "Add with name + up to 3 slide images",
    "Edit with image replacement",
    "Delete with image cleanup from uploads/",
    "Upload helper: cat_****.ext unique names",
  ],
  "index.php (homepage slideshow), uploads/", "14-admin-categories.png");

children.push(new Paragraph({ children: [new PageBreak()] }));

// --- Additional Pages ---
children.push(h(2, "13. Additional Pages"));

comp("13.1 about.php", "/reference/about.php",
  "About page with live store statistics",
  "config.php", "products, orders, users, categories",
  ["Stats: total products, orders, customers, categories"]);

comp("13.2 contact.php", "/reference/contact.php",
  "Contact form page (client-side, no backend handler)",
  "config.php");

comp("13.3 download.php", "/reference/download.php",
  "Mobile app download page with badges and QR code",
  "config.php",
  ["Google Play badge, App Store badge, QR code display"]);

comp("13.4 set_currency.php", "/reference/set_currency.php",
  "Currency switch handler",
  "config.php", "None",
  [
    "POST or GET with 'currency' parameter",
    "Calls smartmall_set_selected_currency()",
    "Validates redirect: parses HTTP_REFERER, rejects cross-origin",
  ],
  "Currency toggle in header.php");

comp("13.5 check_schema.php", "/reference/check_schema.php",
  "Dev utility — outputs product table schema as JSON",
  "config.php", "products");

comp("13.6 protect.php", "/reference/protect.php",
  "Password-gated page protection",
  "None", "None",
  ["Password is 'smartmalltest!'", "Session unlock: $_SESSION['unlocked'] = true"]);

children.push(new Paragraph({ children: [new PageBreak()] }));

// --- Shared Components ---
children.push(h(2, "14. Shared Layout Components"));

comp("14.1 includes/header.php", "/reference/includes/header.php",
  "Shared HTML head, navigation bar, opening body tags",
  "currency.php", "cart (for badge count)",
  [
    "Cache-control: no-store, no-cache",
    "Dark theme: localStorage 'smartmall-theme', respects prefers-color-scheme",
    "Cart badge: SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id = :uid",
    "Current page via basename($_SERVER['PHP_SELF'])",
    "Nav: Home, Shop, About, Contact, Cart, Orders, Download, Admin",
    "Currency dropdown, PWA links, hamburger menu",
  ]);

comp("14.2 includes/footer.php", "/reference/includes/footer.php",
  "Shared footer with JavaScript assets and closing tags",
  "None", "None",
  [
    "Sticky cart notification bar",
    "Toast notification system (showToast)",
    "Search autocomplete JS (calls api/search.php)",
    "Slideshow/carousel JS",
    "Dark theme toggle JS",
  ]);

// --- PWA ---
children.push(h(2, "15. Progressive Web App"));
comp("15.1 manifest.json", "/reference/manifest.json",
  "PWA manifest enabling installable web app",
  "None", "None",
  ["Display: standalone", "Theme: #007AFF", "Name: Smart Mall"]);

children.push(new Paragraph({ children: [new PageBreak()] }));

// --- Mobile ---
children.push(h(2, "16. Capacitor Mobile App"));
comp("16.1 Capacitor Project", "/reference/smartmall-app/",
  "Android mobile app wrapping the web platform",
  "Capacitor v8", "None",
  [
    "capacitor.config.json: server.url = https://smartmall.unaux.com",
    "appId: com.example.smartmall",
    "www/app.js: StatusBar.hide(), SplashScreen.hide()",
    "backButton: history.back() or App.exitApp()",
    "appUrlOpen: intercepts smartmall.unaux.com URLs",
  ]);

children.push(new Paragraph({ children: [new PageBreak()] }));

// ===== PART IV: DATA FLOWS =====
children.push(h(1, "Part IV: Data Flows"));
children.push(sp());

children.push(h(2, "17. Add to Cart Flow"));
children.push(p("AJAX-driven, instant feedback without page reload:"));
nm("User clicks Add to Cart");
nm("JavaScript POST to add_to_cart.php with product_id + quantity");
nm("Checks auth — returns JSON error if not logged in");
nm("Validates stock via SELECT stock FROM products WHERE id = :id");
nm("UPSERT: UPDATE if cart entry exists, INSERT if new");
nm("Recalculates cart count: SELECT COALESCE(SUM(quantity),0)");
nm("Returns JSON with success, message, cart_count, new_quantity");
nm("JavaScript updates badge and shows toast");

children.push(h(2, "18. Checkout & Payment Flow"));
nm("User reviews cart, clicks Proceed to Checkout");
nm("Cart items loaded via JOIN with products, subtotal + 10% VAT");
nm("Billing form and payment method selection");
nm("csrf_verify() called, PDO transaction begins");
nm("SELECT ... FOR UPDATE row-level stock check per item");
nm("Order INSERT, Payment INSERT, Order Items INSERT, Stock UPDATE");
nm("Chapa: tx_ref='ORD-{id}-{date}', POST to /v1/transaction/initialize");
nm("On success: commit, redirect to Chapa checkout page");
nm("Cart preserved until payment callback");

children.push(h(2, "19. Payment Verification Flow"));
nm("User redirected to order_confirmation.php?order_id=X");
nm("If already paid: display confirmation immediately");
nm("If pending + chapa: GET /v1/transaction/verify/{tx_ref}");
nm("Success = status='success' AND data.status='success'");
nm("Test mode: synthetic success if key contains 'TEST'");
nm("On success: UPDATE payments(status='paid'), orders(status='processing'), DELETE cart");
nm("On failure: UPDATE payments(status='failed'), orders(status='cancelled'), restore stock");

children.push(h(2, "20. Currency Conversion Flow"));
nm("User selects ETB in header currency dropdown");
nm("set_currency.php calls smartmall_set_selected_currency('ETB')");
nm("smartmall_exchange_data() checks file cache at sys_temp_dir");
nm("If valid cache: use immediately");
nm("If expired: cURL to open.er-api.com/v6/latest/USD");
nm("Rates cached with LOCK_EX and next_update timestamp");
nm("API failure: stale cache, or rates default to 0.0 (USD-only)");
nm("Display: ETB X.XX = $USD * rate");

children.push(h(2, "21. Password Reset Flow"));
nm("User enters email on forgot_password.php");
nm("Token: bin2hex(random_bytes(32)) — 64-char hex");
nm("INSERT into password_resets with 1-hour expiry");
nm("Link saved to sys_temp_dir/smartmall_reset_links.txt");
nm("reset_password.php validates: SELECT token, checks expires_at > time()");
nm("New password: same policy, PASSWORD_BCRYPT hash");
nm("Used token deleted to prevent reuse");

children.push(new Paragraph({ children: [new PageBreak()] }));

// ===== PART V: SECURITY =====
children.push(h(1, "Part V: Security"));
children.push(sp());

children.push(h(2, "22. Security Measures"));

children.push(h(3, "22.1 CSRF Protection"));
children.push(p("All state-changing operations require CSRF token verification. Tokens generated via bin2hex(random_bytes(32)) — 64-char hex stored in $_SESSION['csrf_token']. Verification uses hash_equals() to prevent timing attacks. Forms use csrf_field(), handlers call csrf_verify() which terminates with HTTP 403 on mismatch."));
children.push(pb("Files using CSRF: ", "login.php, register.php, checkout.php, cart.php, add_to_cart.php, orders.php, forgot_password.php, reset_password.php, admin/dashboard.php, admin/manage_orders.php, admin/manage_categories.php, admin/delete_product.php"));

children.push(h(3, "22.2 SQL Injection Prevention"));
children.push(p("All queries use PDO prepared statements with named parameters. PDO::ATTR_EMULATE_PREPARES = false for real prepared statements. Charset=utf8mb4 in DSN. No raw string interpolation in any SQL query."));

children.push(h(3, "22.3 XSS Prevention"));
children.push(p("All user-controlled data escaped with htmlspecialchars($value, ENT_QUOTES, 'UTF-8'). Applied to product names, descriptions, form values, error messages."));

children.push(h(3, "22.4 Password Security"));
children.push(p("PASSWORD_BCRYPT via password_hash() with cost factor 10. Verification via timing-safe password_verify(). Policy: 8+ chars, uppercase, lowercase, digit, special (4 preg_match checks)."));

children.push(h(3, "22.5 Session Security"));
bt("session_regenerate_id(true) on login — prevents fixation");
bt("session_unset() + session_destroy() on logout");
bt("Cache-control: no-store, no-cache on session pages");
bt("HTTP-only cookies, no session ID in URLs");

children.push(h(3, "22.6 File Upload Security"));
children.push(p("Admin uploads validate type, size (5 MB), and PHP error constants. Unique filenames (cat_****.ext). Deletion handlers clean up filesystem."));

children.push(h(3, "22.7 HTTP Security Headers"));
bt("X-Content-Type-Options: nosniff");
bt("X-Frame-Options: SAMEORIGIN");

children.push(new Paragraph({ children: [new PageBreak()] }));

// ===== PART VI: APPENDICES =====
children.push(h(1, "Part VI: Appendices"));
children.push(sp());

children.push(h(2, "23. Complete DDL"));
children.push(p("DDL for all eight Smart Mall tables:"));

const ddl = [
  "CREATE TABLE users (",
  "  user_id INT(11) PRIMARY KEY AUTO_INCREMENT,",
  "  name VARCHAR(100) NOT NULL,",
  "  email VARCHAR(100) NOT NULL UNIQUE,",
  "  phone VARCHAR(20) DEFAULT NULL,",
  "  password VARCHAR(255) NOT NULL,",
  "  role ENUM('customer','admin') DEFAULT 'customer',",
  "  address TEXT DEFAULT NULL,",
  "  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
  ");",
  "",
  "CREATE TABLE categories (",
  "  category_id INT(11) PRIMARY KEY AUTO_INCREMENT,",
  "  name VARCHAR(255) NOT NULL,",
  "  slug VARCHAR(255) DEFAULT NULL,",
  "  image1 VARCHAR(255) DEFAULT NULL,",
  "  image2 VARCHAR(255) DEFAULT NULL,",
  "  image3 VARCHAR(255) DEFAULT NULL,",
  "  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
  ");",
  "",
  "CREATE TABLE products (",
  "  product_id INT(11) PRIMARY KEY AUTO_INCREMENT,",
  "  category_id INT(11) DEFAULT NULL,",
  "  name VARCHAR(255) NOT NULL,",
  "  description TEXT DEFAULT NULL,",
  "  price DECIMAL(10,2) NOT NULL,",
  "  image VARCHAR(255) DEFAULT NULL,",
  "  images LONGTEXT DEFAULT NULL,",
  "  video VARCHAR(255) DEFAULT NULL,",
  "  stock INT(11) NOT NULL DEFAULT 0,",
  "  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,",
  "  FOREIGN KEY (category_id) REFERENCES categories(category_id)",
  ");",
  "",
  "CREATE TABLE cart (",
  "  cart_id INT(11) PRIMARY KEY AUTO_INCREMENT,",
  "  user_id INT(11) NOT NULL,",
  "  product_id INT(11) NOT NULL,",
  "  quantity INT(11) NOT NULL DEFAULT 1,",
  "  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,",
  "  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,",
  "  FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE",
  ");",
  "",
  "CREATE TABLE orders (",
  "  order_id INT(11) PRIMARY KEY AUTO_INCREMENT,",
  "  user_id INT(11) NOT NULL,",
  "  total_price DECIMAL(10,2) NOT NULL,",
  "  status ENUM('pending','processing','paid','shipped','delivered','cancelled') DEFAULT 'pending',",
  "  first_name VARCHAR(100) DEFAULT NULL,",
  "  last_name VARCHAR(100) DEFAULT NULL,",
  "  email VARCHAR(255) DEFAULT NULL,",
  "  address TEXT DEFAULT NULL,",
  "  city VARCHAR(100) DEFAULT NULL,",
  "  state VARCHAR(100) DEFAULT NULL,",
  "  zip VARCHAR(20) DEFAULT NULL,",
  "  country VARCHAR(100) DEFAULT 'Ethiopia',",
  "  payment_method VARCHAR(50) DEFAULT 'chapa',",
  "  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,",
  "  FOREIGN KEY (user_id) REFERENCES users(user_id)",
  ");",
  "",
  "CREATE TABLE order_items (",
  "  order_item_id INT(11) PRIMARY KEY AUTO_INCREMENT,",
  "  order_id INT(11) NOT NULL,",
  "  product_id INT(11) NOT NULL,",
  "  quantity INT(11) NOT NULL,",
  "  price DECIMAL(10,2) NOT NULL,",
  "  FOREIGN KEY (order_id) REFERENCES orders(order_id),",
  "  FOREIGN KEY (product_id) REFERENCES products(product_id)",
  ");",
  "",
  "CREATE TABLE payments (",
  "  id INT(11) PRIMARY KEY AUTO_INCREMENT,",
  "  order_id INT(11) NOT NULL,",
  "  method VARCHAR(50) NOT NULL,",
  "  status ENUM('pending','paid','failed') DEFAULT 'pending',",
  "  amount DECIMAL(10,2) NOT NULL,",
  "  currency VARCHAR(10) NOT NULL DEFAULT 'USD',",
  "  tx_ref VARCHAR(100) DEFAULT NULL,",
  "  chapa_response LONGTEXT DEFAULT NULL,",
  "  paid_at DATETIME DEFAULT NULL,",
  "  created_at DATETIME NOT NULL,",
  "  FOREIGN KEY (order_id) REFERENCES orders(order_id)",
  ");",
  "",
  "CREATE TABLE password_resets (",
  "  reset_id INT(11) PRIMARY KEY AUTO_INCREMENT,",
  "  email VARCHAR(100) NOT NULL,",
  "  token VARCHAR(64) NOT NULL,",
  "  expires_at DATETIME NOT NULL,",
  "  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
  ");",
];
ddl.forEach(line => children.push(mon(line)));

children.push(new Paragraph({ children: [new PageBreak()] }));

children.push(h(2, "24. Component Dependency Map"));
children.push(p("How every file depends on includes/db.php and includes/currency.php:"));
children.push(sp());

const dw = [3000, 1200, 1200, 1200, 2760];
children.push(cTable([
  ["File", "config.php", "db.php", "currency.php", "Tables Used"],
  ["config.php", "—", "Yes", "Yes", "None"],
  ["index.php", "Yes", "via config", "via config", "products, categories"],
  ["product.php", "Yes", "via config", "via config", "products, categories"],
  ["cart.php", "Yes", "via config", "via config", "cart, products"],
  ["checkout.php", "Yes", "via config", "via config", "cart, products, orders, order_items, payments"],
  ["order_confirmation.php", "Yes", "via config", "via config", "orders, payments, order_items, products, cart"],
  ["orders.php", "Yes", "via config", "via config", "orders, payments, order_items"],
  ["login.php", "No", "Yes", "No", "users"],
  ["register.php", "Yes", "via config", "via config", "users"],
  ["logout.php", "No", "No", "No", "None"],
  ["forgot_password.php", "Yes", "via config", "via config", "password_resets"],
  ["reset_password.php", "Yes", "via config", "via config", "password_resets, users"],
  ["add_to_cart.php", "Yes", "via config", "via config", "cart, products"],
  ["api/search.php", "Yes", "via config", "via config", "products"],
  ["about.php", "Yes", "via config", "via config", "products, orders, users, categories"],
  ["set_currency.php", "Yes", "via config", "via config", "None"],
  ["admin/dashboard.php", "Yes", "via config", "via config", "products, categories, orders"],
  ["admin/add_product.php", "No", "Yes", "No", "products, categories"],
  ["admin/delete_product.php", "No", "Yes", "No", "products"],
  ["admin/manage_orders.php", "Yes", "via config", "via config", "orders, users, payments, order_items, products"],
  ["admin/manage_categories.php", "Yes", "via config", "via config", "categories"],
], dw));

children.push(sp(120));

children.push(h(2, "25. Deployment Checklist"));
nm("PHP 8.0+ with PDO, cURL, MySQL extensions enabled");
nm("Create MySQL database, execute DDL for all 8 tables");
nm("Copy files preserving directory structure");
nm("Update DB credentials in includes/db.php");
nm("Configure Chapa production key in chapa_pay/chapa-config.php");
nm("uploads/ writable by web server (chmod 755)");
nm("Mobile app: update server.url in capacitor.config.json, npx cap sync android");
nm("Configure cron for abandoned order cleanup (30 min)");
nm("Enable HTTPS (required by Chapa API)");
nm("Test full payment flow");

// ========== BUILD DOCUMENT ==========
const doc = new Document({
  styles: {
    default: { document: { run: { font: "Arial", size: 22 } } },
    paragraphStyles: [
      {
        id: "Heading1", name: "Heading 1", basedOn: "Normal", next: "Normal", quickFormat: true,
        run: { size: 36, bold: true, font: "Arial", color: "1A365D" },
        paragraph: { spacing: { before: 360, after: 200 }, outlineLevel: 0 },
      },
      {
        id: "Heading2", name: "Heading 2", basedOn: "Normal", next: "Normal", quickFormat: true,
        run: { size: 30, bold: true, font: "Arial", color: "2E4057" },
        paragraph: { spacing: { before: 280, after: 160 }, outlineLevel: 1 },
      },
      {
        id: "Heading3", name: "Heading 3", basedOn: "Normal", next: "Normal", quickFormat: true,
        run: { size: 26, bold: true, font: "Arial", color: "4A6FA5" },
        paragraph: { spacing: { before: 200, after: 120 }, outlineLevel: 2 },
      },
      {
        id: "Heading4", name: "Heading 4", basedOn: "Normal", next: "Normal", quickFormat: true,
        run: { size: 24, bold: true, font: "Arial", color: "4A6FA5" },
        paragraph: { spacing: { before: 160, after: 100 }, outlineLevel: 3 },
      },
    ],
  },
  numbering: {
    config: [
      { reference: "bullets", levels: [{ level: 0, format: LevelFormat.BULLET, text: "\u2022", alignment: AlignmentType.LEFT, style: { paragraph: { indent: { left: 720, hanging: 360 } } } }] },
      { reference: "numbers", levels: [{ level: 0, format: LevelFormat.DECIMAL, text: "%1.", alignment: AlignmentType.LEFT, style: { paragraph: { indent: { left: 720, hanging: 360 } } } }] },
      { reference: "subnum", levels: [{ level: 0, format: LevelFormat.LOWER_LETTER, text: "  %a.", alignment: AlignmentType.LEFT, style: { paragraph: { indent: { left: 1080, hanging: 360 } } } }] },
    ],
  },
  sections: [{
    properties: {
      page: { size: { width: pageWidth, height: 15840 }, margin: { top: margin, right: margin, bottom: margin, left: margin } },
    },
    headers: {
      default: new Header({
        children: [new Paragraph({
          border: { bottom: { style: BorderStyle.SINGLE, size: 4, color: "2E4057", space: 4 } },
          children: [new TextRun({ text: "Smart Mall Technical Documentation", font: "Arial", size: 18, color: "888888" })],
        })],
      }),
    },
    footers: {
      default: new Footer({
        children: [new Paragraph({
          alignment: AlignmentType.CENTER,
          children: [new TextRun({ text: "Page ", font: "Arial", size: 18, color: "888888" }), PageNumber.CURRENT],
        })],
      }),
    },
    children,
  }],
});

Packer.toBuffer(doc).then(buf => {
  const out = "/opt/lampp/htdocs/reference/docs/Smart_Mall_Technical_Documentation.docx";
  fs.writeFileSync(out, buf);
  console.log("Generated successfully: " + (buf.length / 1024).toFixed(1) + " KB");
}).catch(err => {
  console.error("Error:", err.message);
  process.exit(1);
});
