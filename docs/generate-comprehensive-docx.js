const fs = require("fs");
const path = require("path");
const {
  Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
  Header, Footer, AlignmentType, LevelFormat,
  ExternalHyperlink, InternalHyperlink, Bookmark,
  TableOfContents, HeadingLevel, BorderStyle, WidthType, ShadingType,
  PageNumber, PageBreak, ImageRun
} = require("docx");

const border = { style: BorderStyle.SINGLE, size: 1, color: "CCCCCC" };
const borders = { top: border, bottom: border, left: border, right: border };
const cellMargins = { top: 60, bottom: 60, left: 100, right: 100 };
const pageWidth = 12240;
const pageHeight = 15840;
const margin = 1440;
const contentWidth = pageWidth - 2 * margin;

function heading(level, text, id) {
  const headingMap = { 1: HeadingLevel.HEADING_1, 2: HeadingLevel.HEADING_2, 3: HeadingLevel.HEADING_3, 4: HeadingLevel.HEADING_4 };
  const children = id
    ? [new Bookmark({ id, children: [new TextRun({ text, bold: true, font: "Arial" })] })]
    : [new TextRun({ text, bold: true, font: "Arial" })];
  return new Paragraph({ heading: headingMap[level], children });
}

function para(text, opts = {}) {
  const runs = [];
  if (typeof text === "string") {
    runs.push(new TextRun({ text, font: "Arial", size: 22, ...opts }));
  } else if (Array.isArray(text)) {
    text.forEach(t => {
      if (typeof t === "string") runs.push(new TextRun({ text: t, font: "Arial", size: 22 }));
      else runs.push(new TextRun({ font: "Arial", size: 22, ...t }));
    });
  }
  return new Paragraph({ spacing: { after: 120 }, children: runs });
}

function monopara(text) {
  return new Paragraph({
    spacing: { after: 60 },
    shading: { fill: "F5F5F5", type: ShadingType.CLEAR },
    indent: { left: 360 },
    children: [new TextRun({ text, font: "Courier New", size: 20 })],
  });
}

function bullet(text) {
  const runs = typeof text === "string"
    ? [new TextRun({ text, font: "Arial", size: 22 })]
    : text.map(t => new TextRun({ font: "Arial", size: 22, ...t }));
  return new Paragraph({
    numbering: { reference: "bullets", level: 0 },
    spacing: { after: 60 },
    children: runs,
  });
}

function numberItem(text) {
  return new Paragraph({
    numbering: { reference: "numbers", level: 0 },
    spacing: { after: 60 },
    children: [new TextRun({ text, font: "Arial", size: 22 })],
  });
}

function subnumberItem(text) {
  return new Paragraph({
    numbering: { reference: "numbers2", level: 0 },
    spacing: { after: 60 },
    children: [new TextRun({ text, font: "Arial", size: 22 })],
  });
}

function headerCell(text, width) {
  return new TableCell({
    borders,
    width: { size: width, type: WidthType.DXA },
    shading: { fill: "2E4057", type: ShadingType.CLEAR },
    margins: cellMargins,
    verticalAlign: "center",
    children: [new Paragraph({
      alignment: AlignmentType.CENTER,
      children: [new TextRun({ text, bold: true, font: "Arial", size: 20, color: "FFFFFF" })],
    })],
  });
}

function cell(text, width, opts = {}) {
  return new TableCell({
    borders,
    width: { size: width, type: WidthType.DXA },
    shading: opts.shading ? { fill: opts.shading, type: ShadingType.CLEAR } : undefined,
    margins: cellMargins,
    children: [new Paragraph({
      children: [new TextRun({ text: String(text), font: "Arial", size: 20, ...opts })],
    })],
  });
}

function spacer(pts = 200) {
  return new Paragraph({ spacing: { after: pts }, children: [] });
}

function pageBreak() {
  return new Paragraph({ children: [new PageBreak()] });
}

// ========== SCREENSHOT HELPER ==========
const screenshotDir = path.join(__dirname, "screenshots");

function screenshotImage(filename, widthPx = 500) {
  const filepath = path.join(screenshotDir, filename);
  if (!fs.existsSync(filepath)) return null;
  const img = fs.readFileSync(filepath);
  const ext = filename.split(".").pop().toLowerCase();
  const typeMap = { png: "png", jpg: "jpg", jpeg: "jpg", gif: "gif" };
  const type = typeMap[ext] || "png";
  // Convert px to EMU: 1px = 914400/96 EMU at 96dpi
  const emuWidth = Math.round(widthPx * 914400 / 96);
  // Assume 3:2 aspect ratio for screenshots
  const emuHeight = Math.round(emuWidth * 0.667);
  return new Paragraph({
    alignment: AlignmentType.CENTER,
    spacing: { before: 200, after: 200 },
    children: [new ImageRun({
      type,
      data: img,
      transformation: { width: emuWidth, height: emuHeight },
      altText: { title: filename, description: filename, name: filename },
    })],
  });
}

// ========== COMPONENT TABLE BUILDER ==========
function componentTable(rows, widths) {
  const headerRow = new TableRow({
    children: rows[0].map((h, i) => headerCell(h, widths[i])),
  });
  const dataRows = rows.slice(1).map((row, ri) => new TableRow({
    children: row.map((c, i) => cell(c, widths[i], { shading: ri % 2 === 0 ? "F9F9F9" : undefined })),
  }));
  return new Table({
    width: { size: contentWidth, type: WidthType.DXA },
    columnWidths: widths,
    rows: [headerRow, ...dataRows],
  });
}

// ========== COMPONENT DOC BLOCKS ==========
function componentSection(title, filePath, purpose, details) {
  const blocks = [heading(3, title)];
  blocks.push(boldPara("File Path: ", filePath));
  if (purpose) blocks.push(boldPara("Purpose: ", purpose));
  for (const [label, value] of Object.entries(details)) {
    blocks.push(boldPara(label + ": ", value));
  }
  return blocks;
}

function boldPara(label, value) {
  return new Paragraph({
    spacing: { after: 80 },
    children: [
      new TextRun({ text: label, bold: true, font: "Arial", size: 22 }),
      new TextRun({ text: String(value), font: "Arial", size: 22 }),
    ],
  });
}

// ========== DOCUMENT SECTIONS ==========

// Screenshot helper returning null if not found
function tryScreenshot(filename, widthPx) {
  const img = screenshotImage(filename, widthPx);
  return img ? [img, spacer(60)] : [];
}

// ---- SECTION 1: TITLE PAGE ----
function titlePage() {
  return {
    properties: {
      page: { size: { width: pageWidth, height: pageHeight }, margin: { top: margin, right: margin, bottom: margin, left: margin } },
    },
    children: [
      spacer(3000),
      new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { after: 200 },
        children: [new TextRun({ text: "Smart Mall", size: 72, bold: true, font: "Arial", color: "1A365D" })],
      }),
      new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { after: 100 },
        children: [new TextRun({ text: "E-Commerce Platform", size: 48, font: "Arial", color: "2E4057" })],
      }),
      new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { after: 100 },
        children: [new TextRun({ text: "Complete Technical Documentation", size: 36, font: "Arial", color: "4A6FA5" })],
      }),
      new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { after: 400 },
        border: { bottom: { style: BorderStyle.SINGLE, size: 6, color: "2E4057", space: 1 } },
        children: [],
      }),
      spacer(500),
      new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ text: "Document Version 2.0", size: 24, font: "Arial", color: "888888" })] }),
      new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ text: "May 2026", size: 24, font: "Arial", color: "888888" })] }),
      new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 200 }, children: [new TextRun({ text: "smartmall.unaux.com", size: 24, font: "Arial", color: "888888" })] }),
      spacer(1000),
      new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ text: "Prepared for academic submission", size: 22, font: "Arial", color: "999999", italics: true })] }),
    ],
  };
}

// ---- SECTION 2: TOC ----
function tocSection() {
  return {
    properties: {
      page: { size: { width: pageWidth, height: pageHeight }, margin: { top: margin, right: margin, bottom: margin, left: margin } },
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
          children: [new TextRun({ text: "Page ", font: "Arial", size: 18, color: "888888" }), new TextRun({ children: [PageNumber.CURRENT], size: 18, color: "888888" })],
        })],
      }),
    },
    children: [
      heading(1, "Table of Contents"),
      new TableOfContents("Table of Contents", { hyperlink: true, headingStyleRange: "1-4" }),
      pageBreak(),
    ],
  };
}

// ---- COMMON PAGE SETUP FOR ALL BODY SECTIONS ----
function bodyPageSetup() {
  return {
    page: { size: { width: pageWidth, height: pageHeight }, margin: { top: margin, right: margin, bottom: margin, left: margin } },
  };
}

function bodyHeaderFooter() {
  return {
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
          children: [new TextRun({ text: "Page ", font: "Arial", size: 18, color: "888888" }), new TextRun({ children: [PageNumber.CURRENT], size: 18, color: "888888" })],
        })],
      }),
    },
  };
}

// ========== FULL CONTENT ==========

// ---- PART I: OVERVIEW ----
function part1Overview() {
  return {
    properties: bodyPageSetup(),
    ...bodyHeaderFooter(),
    children: [
      heading(1, "Part I: Project Overview"),
      spacer(120),

      heading(2, "1. Introduction", "intro"),
      para("Smart Mall is a full-stack e-commerce platform developed using PHP and MySQL, designed to serve the Ethiopian market with multi-currency support (USD and ETB). The platform supports the complete online retail lifecycle: product catalog management, customer registration and authentication, a server-side shopping cart, order processing with real-time stock validation, and payment processing through the Chapa payment gateway. A companion Capacitor mobile application wraps the web experience for Android deployment."),
      para("This document provides a comprehensive technical reference for every component in the Smart Mall codebase. Each file is documented with its exact file path, purpose, dependencies, database interactions, and integration points with other components."),

      heading(2, "2. Technology Stack", "stack"),
      componentTable([
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
        ["Doc Generation", "docx (npm)", "Node.js 18+ for .docx output"],
      ], [2340, 4680, 2340]),

      heading(2, "3. Directory Structure", "structure"),
      para("The project root is at /reference/ (under the web server document root at /opt/lampp/htdocs/). Every PHP file in the root directory serves as an independent page or handler. The includes directory holds shared components. The admin directory contains store management tools."),
      spacer(60),
      monopara("reference/"),
      monopara("  ├── config.php              # Application bootstrap"),
      monopara("  ├── index.php               # Homepage (product grid, categories)"),
      monopara("  ├── product.php             # Product detail page"),
      monopara("  ├── search.php              # Search results page"),
      monopara("  ├── cart.php                # Shopping cart management"),
      monopara("  ├── checkout.php            # Checkout + Chapa payment initiation"),
      monopara("  ├── order_confirmation.php  # Payment verification + confirmation"),
      monopara("  ├── orders.php              # Customer order history"),
      monopara("  ├── login.php               # User login"),
      monopara("  ├── register.php            # User registration"),
      monopara("  ├── logout.php              # Session destruction"),
      monopara("  ├── forgot_password.php     # Password reset request"),
      monopara("  ├── reset_password.php      # Password reset token validation"),
      monopara("  ├── about.php               # About page with store statistics"),
      monopara("  ├── contact.php             # Contact form"),
      monopara("  ├── download.php            # Mobile app download page"),
      monopara("  ├── set_currency.php        # Currency switch handler"),
      monopara("  ├── add_to_cart.php         # AJAX add-to-cart endpoint"),
      monopara("  ├── check_schema.php        # Schema introspection tool"),
      monopara("  ├── protect.php             # Password-gated access"),
      monopara("  ├── manifest.json           # PWA manifest"),
      monopara("  ├── includes/"),
      monopara("  │   ├── db.php              # PDO connection + CSRF helpers"),
      monopara("  │   ├── currency.php        # Multi-currency engine"),
      monopara("  │   ├── header.php          # Shared HTML header + nav"),
      monopara("  │   └── footer.php          # Shared HTML footer + scripts"),
      monopara("  ├── admin/"),
      monopara("  │   ├── dashboard.php       # Admin dashboard with stats"),
      monopara("  │   ├── add_product.php     # Add/Edit product form"),
      monopara("  │   ├── delete_product.php  # Product deletion handler"),
      monopara("  │   ├── manage_orders.php   # Order management + status updates"),
      monopara("  │   └── manage_categories.php # Category CRUD + slide images"),
      monopara("  ├── api/"),
      monopara("  │   └── search.php          # JSON search API"),
      monopara("  ├── chapa_pay/"),
      monopara("  │   └── chapa-config.php    # Chapa API credentials"),
      monopara("  ├── uploads/                # Product images (auto-created)"),
      monopara("  ├── assets/images/          # Logo and static images"),
      monopara("  ├── docs/                   # Documentation hub"),
      monopara("  └── smartmall-app/          # Capacitor mobile app"),
      spacer(60),

      pageBreak(),

      heading(2, "4. Request Lifecycle", "lifecycle"),
      para("Every page request follows a consistent bootstrap sequence:"),
      numberItem("The web server (Apache) routes the HTTP request to the target PHP file (e.g., index.php, product.php)."),
      numberItem("The target file requires config.php, which starts the output buffer (ob_start), initializes the session, detects the base URL from the server environment, then requires includes/db.php (PDO connection) and includes/currency.php (exchange rate engine)."),
      numberItem("config.php emits security headers (X-Content-Type-Options: nosniff, X-Frame-Options: SAMEORIGIN)."),
      numberItem("The page file processes any POST form submissions (with CSRF verification) before including header.php, ensuring header() redirects remain available."),
      numberItem("header.php sets cache-control headers, determines the current page for navigation highlighting, queries the cart count for the badge, and renders the <head> and <body> opening tags with navigation."),
      numberItem("The page renders its main content between header.php and footer.php."),
      numberItem("footer.php closes the HTML structure and includes JavaScript assets."),
      para("For AJAX requests (add_to_cart.php, api/search.php), the bootstrap is minimal: the output buffer is still started, but no header or footer is rendered. These endpoints return JSON."),
    ],
  };
}

// ---- PART II: DATABASE SCHEMA ----
function part2Database() {
  return {
    properties: bodyPageSetup(),
    ...bodyHeaderFooter(),
    children: [
      heading(1, "Part II: Database Schema", "database"),
      spacer(120),

      heading(2, "5. Schema Overview", "schema-overview"),
      para("The Smart Mall database (smartmall_db) consists of eight tables. The engine is InnoDB (enforced by PDO's MySQL driver) with UTF-8 MB4 character encoding. Foreign keys enforce referential integrity with ON DELETE CASCADE on cart entries and ON DELETE RESTRICT on order-related tables."),
      spacer(80),

      heading(3, "5.1 Entity Relationship Summary"),
      componentTable([
        ["Table", "Rows Ref'd", "Foreign Keys", "Purpose"],
        ["users", "—", "—", "Customer & admin accounts"],
        ["categories", "—", "—", "Product categories with slide images"],
        ["products", "categories", "category_id", "Product inventory"],
        ["cart", "users, products", "user_id, product_id", "Shopping cart items"],
        ["orders", "users", "user_id", "Placed orders with shipping"],
        ["order_items", "orders, products", "order_id, product_id", "Order line items"],
        ["payments", "orders", "order_id", "Payment transactions"],
        ["password_resets", "—", "—", "Password reset tokens"],
      ], [2000, 1500, 3000, 2860]),

      pageBreak(),

      heading(3, "5.2 Table: users"),
      componentTable([
        ["Column", "Type", "Constraints", "Notes"],
        ["id", "INT(11)", "PRIMARY KEY, AUTO_INCREMENT", "Unique user identifier"],
        ["name", "VARCHAR(100)", "NOT NULL", "User's full name"],
        ["email", "VARCHAR(100)", "NOT NULL, UNIQUE", "Login identifier"],
        ["phone", "VARCHAR(20)", "DEFAULT NULL", "Contact number"],
        ["password", "VARCHAR(255)", "NOT NULL", "bcrypt hash (PASSWORD_BCRYPT)"],
        ["role", "ENUM('customer','admin')", "DEFAULT 'customer'", "Authorization level"],
        ["address", "TEXT", "DEFAULT NULL", "Default shipping address"],
        ["created_at", "TIMESTAMP", "DEFAULT CURRENT_TIMESTAMP", "Account creation time"],
      ], [2340, 2000, 3000, 2620]),
      spacer(80),

      heading(3, "5.3 Table: categories"),
      componentTable([
        ["Column", "Type", "Constraints", "Notes"],
        ["id", "INT(11)", "PRIMARY KEY, AUTO_INCREMENT", "Unique category ID"],
        ["name", "VARCHAR(255)", "NOT NULL", "Display name"],
        ["slug", "VARCHAR(255)", "DEFAULT NULL", "URL-friendly name"],
        ["image1", "VARCHAR(255)", "DEFAULT NULL", "Slide image 1"],
        ["image2", "VARCHAR(255)", "DEFAULT NULL", "Slide image 2"],
        ["image3", "VARCHAR(255)", "DEFAULT NULL", "Slide image 3"],
        ["created_at", "TIMESTAMP", "DEFAULT CURRENT_TIMESTAMP", "Creation time"],
      ], [2340, 2000, 3000, 2620]),
      spacer(80),

      heading(3, "5.4 Table: products"),
      componentTable([
        ["Column", "Type", "Constraints", "Notes"],
        ["id", "INT(11)", "PRIMARY KEY, AUTO_INCREMENT", "Unique product ID"],
        ["category_id", "INT(11)", "FOREIGN KEY → categories(id)", "Product category"],
        ["name", "VARCHAR(255)", "NOT NULL", "Product name"],
        ["description", "TEXT", "DEFAULT NULL", "Product description"],
        ["price", "DECIMAL(10,2)", "NOT NULL", "Price in USD"],
        ["image", "VARCHAR(255)", "DEFAULT NULL", "Primary product image"],
        ["images", "LONGTEXT", "DEFAULT NULL", "JSON array of additional images"],
        ["video", "VARCHAR(255)", "DEFAULT NULL", "Product video filename"],
        ["stock", "INT(11)", "NOT NULL DEFAULT 0", "Available quantity"],
        ["created_at", "TIMESTAMP", "DEFAULT CURRENT_TIMESTAMP", "Creation time"],
      ], [2340, 2000, 3000, 2620]),
      spacer(80),

      heading(3, "5.5 Table: cart"),
      para("The cart table uses ON DELETE CASCADE on both foreign keys, so deleting a user or a product automatically removes their cart entries."),
      componentTable([
        ["Column", "Type", "Constraints", "Notes"],
        ["id", "INT(11)", "PRIMARY KEY, AUTO_INCREMENT", "Unique cart entry ID"],
        ["user_id", "INT(11)", "FOREIGN KEY → users(id) ON DELETE CASCADE", "Cart owner"],
        ["product_id", "INT(11)", "FOREIGN KEY → products(id) ON DELETE CASCADE", "Added product"],
        ["quantity", "INT(11)", "NOT NULL DEFAULT 1", "Quantity selected"],
        ["created_at", "TIMESTAMP", "DEFAULT CURRENT_TIMESTAMP", "When added"],
      ], [2340, 2000, 3000, 2620]),
      spacer(80),

      heading(3, "5.6 Table: orders"),
      componentTable([
        ["Column", "Type", "Constraints", "Notes"],
        ["id", "INT(11)", "PRIMARY KEY, AUTO_INCREMENT", "Unique order ID"],
        ["user_id", "INT(11)", "FOREIGN KEY → users(id)", "Ordering customer"],
        ["total_price", "DECIMAL(10,2)", "NOT NULL", "Total (subtotal + 10% VAT)"],
        ["status", "ENUM('pending','processing','paid','shipped','delivered','cancelled')", "DEFAULT 'pending'", "Order lifecycle state"],
        ["first_name", "VARCHAR(100)", "DEFAULT NULL", "Shipping first name"],
        ["last_name", "VARCHAR(100)", "DEFAULT NULL", "Shipping last name"],
        ["email", "VARCHAR(255)", "DEFAULT NULL", "Shipping email"],
        ["address", "TEXT", "DEFAULT NULL", "Street address"],
        ["city", "VARCHAR(100)", "DEFAULT NULL", "City"],
        ["state", "VARCHAR(100)", "DEFAULT NULL", "State/region"],
        ["zip", "VARCHAR(20)", "DEFAULT NULL", "Postal code"],
        ["country", "VARCHAR(100)", "DEFAULT 'Ethiopia'", "Country"],
        ["payment_method", "VARCHAR(50)", "DEFAULT 'chapa'", "chapa / bank_transfer / cash_on_delivery"],
        ["created_at", "TIMESTAMP", "DEFAULT CURRENT_TIMESTAMP", "Order placement time"],
      ], [2340, 2000, 3000, 2620]),
      spacer(80),

      heading(3, "5.7 Table: order_items"),
      para("Order items preserve a snapshot of the price at the time of purchase, so that price changes to products do not affect historical orders."),
      componentTable([
        ["Column", "Type", "Constraints", "Notes"],
        ["id", "INT(11)", "PRIMARY KEY, AUTO_INCREMENT", "Unique item ID"],
        ["order_id", "INT(11)", "FOREIGN KEY → orders(id)", "Parent order"],
        ["product_id", "INT(11)", "FOREIGN KEY → products(id)", "Purchased product"],
        ["quantity", "INT(11)", "NOT NULL", "Quantity purchased"],
        ["price", "DECIMAL(10,2)", "NOT NULL", "Price snapshot at purchase time"],
      ], [2340, 2000, 3000, 2620]),
      spacer(80),

      heading(3, "5.8 Table: payments"),
      componentTable([
        ["Column", "Type", "Constraints", "Notes"],
        ["id", "INT(11)", "PRIMARY KEY, AUTO_INCREMENT", "Unique payment ID"],
        ["order_id", "INT(11)", "FOREIGN KEY → orders(id)", "Related order"],
        ["method", "VARCHAR(50)", "NOT NULL", "Payment method used"],
        ["status", "ENUM('pending','paid','failed')", "DEFAULT 'pending'", "Payment state"],
        ["amount", "DECIMAL(10,2)", "NOT NULL", "Amount charged"],
        ["currency", "VARCHAR(10)", "NOT NULL DEFAULT 'USD'", "Currency (USD or ETB)"],
        ["tx_ref", "VARCHAR(100)", "DEFAULT NULL", "Chapa transaction reference"],
        ["chapa_response", "LONGTEXT", "DEFAULT NULL", "Full Chapa API JSON response"],
        ["paid_at", "DATETIME", "DEFAULT NULL", "When payment confirmed"],
        ["created_at", "DATETIME", "NOT NULL", "Record creation time"],
      ], [2340, 2000, 3000, 2620]),
      spacer(80),

      heading(3, "5.9 Table: password_resets"),
      componentTable([
        ["Column", "Type", "Constraints", "Notes"],
        ["id", "INT(11)", "PRIMARY KEY, AUTO_INCREMENT", "Unique record ID"],
        ["email", "VARCHAR(100)", "NOT NULL", "User's email address"],
        ["token", "VARCHAR(64)", "NOT NULL", "64-char hex token (32 random bytes)"],
        ["expires_at", "DATETIME", "NOT NULL", "Token expiration (1 hour)"],
        ["created_at", "TIMESTAMP", "DEFAULT CURRENT_TIMESTAMP", "Record creation"],
      ], [2340, 2000, 3000, 2620]),
    ],
  };
}

// ---- PART III: COMPONENT REFERENCE ----
function part3Components() {
  const w = [1800, 1500, 2000, 2000, 2060];
  return {
    properties: bodyPageSetup(),
    ...bodyHeaderFooter(),
    children: [
      heading(1, "Part III: Component Reference", "components"),
      spacer(120),
      para("This section documents every file in the Smart Mall codebase. Each entry lists the exact file path, the component's purpose, its dependencies (files it includes or requires), the database tables it interacts with, its key functions or features, and the other components it integrates with."),
      spacer(80),

      // ---- 6. Bootstrap & Configuration ----
      heading(2, "6. Bootstrap & Configuration", "bootstrap"),

      heading(3, "6.1 config.php"),
      boldPara("File Path: ", "/reference/config.php"),
      boldPara("Purpose: ", "Application bootstrap — executes before every page request"),
      boldPara("Dependencies: ", "includes/db.php, includes/currency.php"),
      boldPara("Database Tables: ", "None directly (includes db.php which connects)"),
      boldPara("Key Features:", ""),
      bullet("Starts output buffering (ob_start) to catch accidental BOM/whitespace"),
      bullet("Initializes the PHP session if not already active"),
      bullet("Auto-detects the base URL: parses HTTP_HOST and SCRIPT_NAME to determine the subfolder (e.g., /reference) and constructs absolute URLs"),
      bullet("Defines the global variable $base_url used by all pages"),
      bullet("Defines the redirect() helper: takes a path, prepends $base_url if not already absolute, and issues a Location header"),
      bullet("Emits security headers: X-Content-Type-Options: nosniff, X-Frame-Options: SAMEORIGIN"),
      bullet("NO closing PHP tag (?>) to prevent accidental whitespace output"),
      boldPara("Called By: ", "Every page file (index.php, product.php, cart.php, etc.) via require_once"),

      spacer(120),

      heading(3, "6.2 includes/db.php"),
      boldPara("File Path: ", "/reference/includes/db.php"),
      boldPara("Purpose: ", "PDO database connection and CSRF token helpers"),
      boldPara("Dependencies: ", "None (standalone; currency.php is required at the end)"),
      boldPara("Database Tables: ", "All (provides the $pdo connection object)"),
      boldPara("Key Features:", ""),
      bullet("PDO connection with DSN mysql:host=localhost;dbname=smartmall_db;charset=utf8mb4"),
      bullet("PDO options: ERRMODE_EXCEPTION for error handling, FETCH_ASSOC for fetch style, emulated prepares OFF (real prepared statements)"),
      bullet("getDB(): returns the global $pdo connection for use in all page files"),
      bullet("CSRF token generation: bin2hex(random_bytes(32)) produces a 64-character hex token, idempotent per session"),
      bullet("csrf_field(): outputs a hidden HTML input with the token"),
      bullet("csrf_verify(): compares submitted token to session token using hash_equals(), terminates with HTTP 403 on mismatch"),
      bullet("get_product_image_url($path): resolves image paths — returns external URLs as-is, prepends uploads/ for local files, adjusts for admin subfolder context"),
      bullet("get_product_video_url($path): same logic for video files"),
      bullet("Requires currency.php at the end of the file"),
      boldPara("Called By: ", "Every page that needs database access"),

      spacer(120),

      heading(3, "6.3 includes/currency.php"),
      boldPara("File Path: ", "/reference/includes/currency.php"),
      boldPara("Purpose: ", "Multi-currency engine — USD base, live ETB exchange rates"),
      boldPara("Dependencies: ", "None (standalone helper library)"),
      boldPara("Database Tables: ", "None"),
      boldPara("Key Functions:", ""),
      bullet("smartmall_supported_currencies(): returns ['USD', 'ETB']"),
      bullet("smartmall_selected_currency(): reads $_SESSION['currency'], defaults to 'USD'"),
      bullet("smartmall_set_selected_currency($currency): validates and sets the session currency"),
      bullet("smartmall_exchange_cache_path(): returns sys_get_temp_dir() + '/smartmall_exchange_usd.json'"),
      bullet("smartmall_fetch_exchange_rates(): calls open.er-api.com/v6/latest/USD via cURL (with file_get_contents fallback), validates result and base_code, writes rates to cache file with LOCK_EX"),
      bullet("smartmall_exchange_data(): returns cached rates if not expired, otherwise fetches fresh; falls back to stale cache or empty rates on API failure"),
      bullet("smartmall_exchange_rate($currency): returns the rate for USD or ETB (1.0 for USD)"),
      bullet("smartmall_convert_money($amountUsd, $currency): converts USD to target currency"),
      bullet("smartmall_format_money($amountUsd, $currency): formats as '$X.XX' for USD or 'ETB X.XX' for ETB"),
      bullet("smartmall_currency_is_converted(): boolean check if displaying in non-USD"),
      boldPara("Constants: ", "SMARTMALL_BASE_CURRENCY='USD', SMARTMALL_EXCHANGE_API_URL='https://open.er-api.com/v6/latest/USD'"),
      boldPara("Called By: ", "header.php (display), checkout.php (price display + ETB conversion for Chapa), product.php, cart.php, order_confirmation.php, api/search.php"),

      spacer(120),

      heading(3, "6.4 chapa_pay/chapa-config.php"),
      boldPara("File Path: ", "/reference/chapa_pay/chapa-config.php"),
      boldPara("Purpose: ", "Chapa payment gateway configuration constants"),
      boldPara("Dependencies: ", "None"),
      boldPara("Key Features:", ""),
      bullet("CHAPA_SECRET_KEY: 'CHASECK_TEST-aF7ZWVLHJRP8rFpNG4V7rpDveopvXt2D' (test mode)"),
      bullet("CHAPA_API_URL: 'https://api.chapa.co/v1'"),
      boldPara("Called By: ", "checkout.php, order_confirmation.php"),
      spacer(80),

      pageBreak(),

      // ---- 7. Authentication ----
      heading(2, "7. Authentication System", "auth"),

      heading(3, "7.1 login.php"),
      boldPara("File Path: ", "/reference/login.php"),
      boldPara("Purpose: ", "User login with email/password"),
      boldPara("Dependencies: ", "includes/db.php, config.php"),
      boldPara("Database Tables: ", "users"),
      boldPara("Key Features:", ""),
      bullet("POST handler processes login before header.php is included (to allow redirects)"),
      bullet("CSRF verification via csrf_verify()"),
      bullet("Prepared statement: SELECT id, name, email, password, role FROM users WHERE email = :email"),
      bullet("Password verification: password_verify($password, $user['password'])"),
      bullet("Session fixation prevention: session_regenerate_id(true) on successful login"),
      bullet("Session variables set: user_id, user_name, user_email, user_role"),
      bullet("Redirect parameter sanitization: preg_match('/^[a-zA-Z0-9_...]+\\.php$/', $redirect)"),
      bullet("session_write_close() before redirect to force write"),
      boldPara("Called By: ", "User navigation from any page"),
      boldPara("Interacts With: ", "header.php (displays login/logout link based on session)"),

      spacer(80),

      heading(3, "7.2 register.php"),
      boldPara("File Path: ", "/reference/register.php"),
      boldPara("Purpose: ", "New user registration with password policy enforcement"),
      boldPara("Dependencies: ", "config.php"),
      boldPara("Database Tables: ", "users"),
      boldPara("Key Features:", ""),
      bullet("CSRF verification on POST"),
      bullet("Name validation: min 2 characters"),
      bullet("Email validation: filter_var with FILTER_VALIDATE_EMAIL + duplicate email check"),
      bullet("Password policy — four separate preg_match checks:"),
      subnumberItem("strlen($password) >= 8"),
      subnumberItem("preg_match('/[A-Z]/', $password) — at least one uppercase"),
      subnumberItem("preg_match('/[a-z]/', $password) — at least one lowercase"),
      subnumberItem("preg_match('/[0-9]/', $password) — at least one digit"),
      subnumberItem("preg_match('/[^A-Za-z0-9]/', $password) — at least one special character"),
      bullet("Password confirmation matching"),
      bullet("Hashing: password_hash($password, PASSWORD_BCRYPT)"),
      bullet("Role defaults to 'customer'"),
      bullet("On success: sets session success flash, redirects to login.php"),
      boldPara("Interacts With: ", "login.php (redirect after success), header.php"),

      spacer(80),

      heading(3, "7.3 logout.php"),
      boldPara("File Path: ", "/reference/logout.php"),
      boldPara("Purpose: ", "Session destruction and redirect to homepage"),
      boldPara("Key Features:", ""),
      bullet("session_unset() followed by session_destroy() for complete cleanup"),
      bullet("Redirects to index.php"),
      boldPara("Called By: ", "User clicking Logout in navigation"),

      spacer(80),

      heading(3, "7.4 forgot_password.php"),
      boldPara("File Path: ", "/reference/forgot_password.php"),
      boldPara("Purpose: ", "Password reset request initiation"),
      boldPara("Dependencies: ", "config.php"),
      boldPara("Database Tables: ", "password_resets"),
      boldPara("Key Features:", ""),
      bullet("CSRF verification on POST"),
      bullet("Email validation + checks if user exists (without revealing existence)"),
      bullet("Token generation: bin2hex(random_bytes(32)) — 64-character hex string"),
      bullet("Token stored in password_resets table with email and expires_at (1 hour)"),
      bullet("Reset link stored in session as $_SESSION['reset_link'] for display"),
      bullet("Reset link logged to file: sys_get_temp_dir() . '/smartmall_reset_links.txt'"),
      bullet("Attempts email via PHP mail() function"),
      boldPara("Interacts With: ", "reset_password.php"),

      spacer(80),

      heading(3, "7.5 reset_password.php"),
      boldPara("File Path: ", "/reference/reset_password.php"),
      boldPara("Purpose: ", "Validate reset token and set new password"),
      boldPara("Dependencies: ", "config.php"),
      boldPara("Database Tables: ", "password_resets, users"),
      boldPara("Key Features:", ""),
      bullet("Validates token from GET or POST parameter"),
      bullet("Prepared statement: SELECT email, expires_at FROM password_resets WHERE token = :token"),
      bullet("Checks strtotime($reset['expires_at']) > time() for expiration"),
      bullet("Re-applies the same password policy as register.php"),
      bullet("Updates user password with password_hash($password, PASSWORD_BCRYPT)"),
      bullet("Deletes used token from password_resets table"),
      boldPara("Interacts With: ", "forgot_password.php, login.php (redirect after success)"),

      pageBreak(),

      // ---- 8. Product Catalog ----
      heading(2, "8. Product Catalog", "catalog"),

      heading(3, "8.1 index.php"),
      boldPara("File Path: ", "/reference/index.php"),
      boldPara("Purpose: ", "Homepage — product grid with categories, search, and slideshow"),
      boldPara("Dependencies: ", "config.php, header.php, footer.php"),
      boldPara("Database Tables: ", "products, categories"),
      boldPara("Key Features:", ""),
      bullet("LEFT JOIN query: products LEFT JOIN categories ON p.category_id = c.id"),
      bullet("Optional category filtering via $_GET['category'] parameter"),
      bullet("Search query support via $_GET['query'] parameter (LIKE search on name and description)"),
      bullet("ORDER BY p.created_at DESC with LIMIT for pagination"),
      bullet("INNER JOIN with order_items for best-selling products display"),
      bullet("Category slideshow with up to 3 images per category"),
      bullet("Price display via smartmall_format_money() in both USD and ETB"),
      boldPara("Interacts With: ", "product.php (links to detail pages), cart.php (add-to-cart links), search.php (search form POST)"),
      ...tryScreenshot("01-homepage.png", 550),

      spacer(80),

      heading(3, "8.2 product.php"),
      boldPara("File Path: ", "/reference/product.php"),
      boldPara("Purpose: ", "Single product detail view with image gallery and related products"),
      boldPara("Dependencies: ", "config.php, header.php, footer.php"),
      boldPara("Database Tables: ", "products, categories"),
      boldPara("Key Features:", ""),
      bullet("Product loaded by ID from $_GET['id'] parameter"),
      bullet("Full product details: name, description, price, stock, images, video"),
      bullet("Image gallery with previous/next navigation arrows"),
      bullet("Related products query: same category, excluding current product, LIMIT 4"),
      bullet("Multi-image support via images column (JSON array)"),
      bullet("Video display support"),
      bullet("Stock indicator (in stock / out of stock)"),
      boldPara("Interacts With: ", "add_to_cart.php (AJAX add-to-cart button), cart.php"),
      ...tryScreenshot("07-product-detail.png", 550),

      spacer(80),

      heading(3, "8.3 api/search.php"),
      boldPara("File Path: ", "/reference/api/search.php"),
      boldPara("Purpose: ", "JSON search autocomplete API"),
      boldPara("Dependencies: ", "config.php (for DB and currency)"),
      boldPara("Database Tables: ", "products"),
      boldPara("Key Features:", ""),
      bullet("Content-Type: application/json"),
      bullet("Parameter: $_GET['q'] — search query string"),
      bullet("LIKE search on name and description, LIMIT 6 results"),
      bullet("Results formatted with image_url and display_price (via smartmall_format_money)"),
      bullet("External URLs kept as-is; local paths prefixed with /uploads/"),
      boldPara("Called By: ", "header.php search bar via JavaScript fetch/AJAX"),

      pageBreak(),

      // ---- 9. Shopping Cart ----
      heading(2, "9. Shopping Cart", "cart"),

      heading(3, "9.1 cart.php"),
      boldPara("File Path: ", "/reference/cart.php"),
      boldPara("Purpose: ", "Full shopping cart management page"),
      boldPara("Dependencies: ", "config.php, header.php, footer.php"),
      boldPara("Database Tables: ", "cart, products"),
      boldPara("Key Features:", ""),
      bullet("Requires login: redirects to login.php?redirect=cart.php if not authenticated"),
      bullet("Remove item: POST handler with csrf_verify(), verifies cart ownership before DELETE"),
      bullet("Update quantity: POST handler verifies ownership AND checks stock limit"),
      bullet("Stock enforcement: joins cart with products to compare c.quantity against p.stock"),
      bullet("Cart items loaded with JOIN: SELECT c.id, c.quantity, p.id, p.name, p.price, p.stock, p.image FROM cart c JOIN products p"),
      bullet("Price totals calculated in PHP: subtotal, 10% VAT, grand total"),
      bullet("Sticky cart notification bar in footer showing item count"),
      bullet("Cache-control: no-store headers via header.php"),
      boldPara("Interacts With: ", "add_to_cart.php (add items), checkout.php (proceed to checkout)"),
      ...tryScreenshot("08-cart.png", 550),

      spacer(80),

      heading(3, "9.2 add_to_cart.php"),
      boldPara("File Path: ", "/reference/add_to_cart.php"),
      boldPara("Purpose: ", "AJAX JSON endpoint for adding products to cart"),
      boldPara("Dependencies: ", "config.php"),
      boldPara("Database Tables: ", "cart, products"),
      boldPara("Key Features:", ""),
      bullet("Content-Type: application/json response"),
      bullet("Accepts POST with product_id and quantity"),
      bullet("Requires authentication (returns JSON error if not logged in)"),
      bullet("Stock validation: checks products.stock >= requested quantity"),
      bullet("Quantity capping: if quantity > stock, sets to stock max"),
      bullet("UPSERT logic: if product already in user's cart, UPDATE quantity; otherwise INSERT"),
      bullet("Returns JSON: {success, message, cart_count, new_quantity}"),
      bullet("cart_count is summed via SELECT COALESCE(SUM(quantity), 0) FROM cart"),
      boldPara("Called By: ", "index.php, product.php (via JavaScript fetch on button click)"),

      pageBreak(),

      // ---- 10. Checkout & Payment ----
      heading(2, "10. Checkout & Payment", "checkout"),

      heading(3, "10.1 checkout.php"),
      boldPara("File Path: ", "/reference/checkout.php"),
      boldPara("Purpose: ", "Order placement with Chapa payment initiation"),
      boldPara("Dependencies: ", "config.php, includes/currency.php, chapa_pay/chapa-config.php"),
      boldPara("Database Tables: ", "cart, products, orders, order_items, payments"),
      boldPara("Key Features:", ""),
      bullet("POST handler processes order before header.php for redirect capability"),
      bullet("Requires login: redirects to login.php?redirect=checkout.php"),
      bullet("Three payment methods: chapa (online), bank_transfer, cash_on_delivery"),
      bullet("PDO Transaction: beginTransaction(), commit() for atomicity"),
      bullet("Row-level locking: SELECT stock FROM products WHERE id = :id FOR UPDATE"),
      bullet("Stock validation per item — rolls back entire transaction on shortage"),
      bullet("10% VAT calculation: $tax = $total_price * 0.1"),
      bullet("Chapa ETB conversion: uses smartmall_exchange_rate('ETB') and smartmall_convert_money()"),
      bullet("Order inserted with shipping details: first_name, last_name, email, address, city, state, zip, country"),
      bullet("Payment record inserted regardless of method (cash_on_delivery → 'paid'; Chapa → 'pending')"),
      bullet("Order items inserted with price snapshot"),
      bullet("Stock deducted: UPDATE products SET stock = stock - :qty"),
      bullet("Chapa flow:"),
      subnumberItem("TxRef generated: 'ORD-' + order_id + '-' + YYYYMMDD"),
      subnumberItem("Updates payments.tx_ref"),
      subnumberItem("POST to https://api.chapa.co/v1/transaction/initialize with JSON payload"),
      subnumberItem("Authorization: Bearer CHAPA_SECRET_KEY header"),
      subnumberItem("On success (200 + status=success): commits transaction, redirects to Chapa checkout_url"),
      subnumberItem("On failure: displays HTTP code, cURL error, and raw response for debugging"),
      bullet("Cart NOT cleared for Chapa payments (cleared after verification callback)"),
      bullet("Cart cleared immediately for non-Chapa payments"),
      boldPara("Interacts With: ", "cart.php (source of items), order_confirmation.php (confirmation page), chapa_pay/chapa-config.php (API config)"),
      ...tryScreenshot("09-checkout.png", 550),

      spacer(80),

      heading(3, "10.2 order_confirmation.php"),
      boldPara("File Path: ", "/reference/order_confirmation.php"),
      boldPara("Purpose: ", "Post-payment verification, order display, and payment state machine"),
      boldPara("Dependencies: ", "config.php, chapa_pay/chapa-config.php"),
      boldPara("Database Tables: ", "orders, payments, order_items, products, cart"),
      boldPara("Key Features:", ""),
      bullet("Payment state machine with three branches:"),
      subnumberItem("ALREADY PAID: skips verification, shows confirmation page directly"),
      subnumberItem("PENDING + CHAPA: triggers immediate Chapa verification API call"),
      subnumberItem("PENDING + NON-CHAPA: handled as per payment method"),
      bullet("Chapa verification: GET https://api.chapa.co/v1/transaction/verify/{tx_ref}"),
      bullet("Verification success requires both top-level status='success' AND data.status='success'"),
      bullet("Test mode fallback: if CHAPA_SECRET_KEY contains 'TEST' and verification fails, assumes success"),
      bullet("Test mode writes synthetic success response to chapa_response column"),
      bullet("On success: updates payments.status='paid', payments.paid_at=NOW(), orders.status='processing', clears cart"),
      bullet("On failure: updates payments.status='failed', orders.status='cancelled', restores product stock"),
      bullet("Stock restoration: loops through order_items and UPDATE products SET stock = stock + :qty"),
      bullet("Loading page with 3-second auto-countdown while payment is pending"),
      bullet("Ownership check: verifies $_SESSION['user_id'] matches order's user_id"),
      bullet("Displays order items with product names, images, quantities, and prices"),
      boldPara("Interacts With: ", "checkout.php (receives order), chapa_pay/chapa-config.php (API credentials)"),
      ...tryScreenshot("10-order-confirmation.png", 550),

      pageBreak(),

      // ---- 11. Order Management ----
      heading(2, "11. Order Management", "orders"),

      heading(3, "11.1 orders.php"),
      boldPara("File Path: ", "/reference/orders.php"),
      boldPara("Purpose: ", "Customer order history page"),
      boldPara("Dependencies: ", "config.php, header.php, footer.php"),
      boldPara("Database Tables: ", "orders, payments, order_items"),
      boldPara("Key Features:", ""),
      bullet("Requires login (redirects if not authenticated)"),
      bullet("Fetches orders for current user: SELECT FROM orders WHERE user_id = :user_id ORDER BY created_at DESC"),
      bullet("Joins with payments for payment status"),
      bullet("Displays order items with quantities via order_items table"),
      bullet("Order cancelation: POST handler with csrf_verify(), updates status to 'cancelled'"),
      bullet("Cancelation restores stock: UPDATE products SET stock = stock + :qty"),
      bullet("Price display via smartmall_format_money()"),
      boldPara("Interacts With: ", "order_confirmation.php (linked from order rows), checkout.php"),
      ...tryScreenshot("11-orders.png", 550),

      pageBreak(),

      // ---- 12. Admin Panel ----
      heading(2, "12. Admin Panel", "admin"),

      heading(3, "12.1 admin/dashboard.php"),
      boldPara("File Path: ", "/reference/admin/dashboard.php"),
      boldPara("Purpose: ", "Admin dashboard with store statistics and product management"),
      boldPara("Dependencies: ", "config.php, header.php, footer.php"),
      boldPara("Database Tables: ", "products, categories, orders"),
      boldPara("Key Features:", ""),
      bullet("Admin gate: checks $_SESSION['user_role'] !== 'admin', redirects to ../index.php"),
      bullet("Statistics queries:"),
      subnumberItem("COUNT(*) FROM products"),
      subnumberItem("SUM(stock) FROM products"),
      subnumberItem("COUNT(*) FROM orders"),
      subnumberItem("SUM(total_price) FROM orders"),
      subnumberItem("COUNT(*) FROM categories"),
      bullet("Products table with edit/delete actions, LEFT JOIN with categories"),
      bullet("Links to manage orders, manage categories, add product pages"),
      boldPara("Interacts With: ", "add_product.php (edit links), delete_product.php (delete links), manage_orders.php, manage_categories.php"),
      ...tryScreenshot("12-admin-dashboard.png", 550),

      spacer(80),

      heading(3, "12.2 admin/add_product.php"),
      boldPara("File Path: ", "/reference/admin/add_product.php"),
      boldPara("Purpose: ", "Add and edit product form with image upload"),
      boldPara("Dependencies: ", "includes/db.php (manually required, not config.php)"),
      boldPara("Database Tables: ", "products, categories"),
      boldPara("Key Features:", ""),
      bullet("Dual-mode: add (no ?id) or edit (?id=N)"),
      bullet("Admin gate: checks $_SESSION['user_role']"),
      bullet("File upload validation: checks file type, size (max 5 MB), and upload errors"),
      bullet("Supported formats: JPEG, PNG, GIF, WebP"),
      bullet("Multi-image support with JSON array in images column"),
      bullet("Video upload support"),
      bullet("Media deletion: individual image/video deletion via POST action=delete_media"),
      bullet("Category dropdown loaded from categories table"),
      bullet("Price, stock, name, description fields"),
      boldPara("Interacts With: ", "dashboard.php (navigates back), uploads/ directory"),
      ...tryScreenshot("15-admin-add-product.png", 550),

      spacer(80),

      heading(3, "12.3 admin/delete_product.php"),
      boldPara("File Path: ", "/reference/admin/delete_product.php"),
      boldPara("Purpose: ", "Product deletion handler (POST only)"),
      boldPara("Dependencies: ", "includes/db.php"),
      boldPara("Database Tables: ", "products"),
      boldPara("Key Features:", ""),
      bullet("POST-only: rejects GET requests"),
      bullet("CSRF verification before deletion"),
      bullet("Fetches product image path, deletes image file from uploads/ using unlink()"),
      bullet("DELETE FROM products WHERE id = :id"),
      bullet("Redirects to dashboard.php with success/error flash"),
      boldPara("Interacts With: ", "dashboard.php (product delete button)"),

      spacer(80),

      heading(3, "12.4 admin/manage_orders.php"),
      boldPara("File Path: ", "/reference/admin/manage_orders.php"),
      boldPara("Purpose: ", "Order management with status updates"),
      boldPara("Dependencies: ", "config.php, header.php, footer.php"),
      boldPara("Database Tables: ", "orders, users, payments, order_items, products"),
      boldPara("Key Features:", ""),
      bullet("Paid orders displayed first: CASE WHEN pay.status='paid' THEN 1 ELSE 2 END ORDER"),
      bullet("Status update via POST: csrf_verify(), UPDATE orders SET status = :status"),
      bullet("Items data aggregated via GROUP_CONCAT with product name, image, quantity, price"),
      bullet("JOIN: orders LEFT JOIN users, LEFT JOIN payments, LEFT JOIN order_items, LEFT JOIN products"),
      bullet("Order details include: ID, customer name, items, total, status, date, payment method"),
      boldPara("Interacts With: ", "order_confirmation.php (view order details)"),
      ...tryScreenshot("13-admin-orders.png", 550),

      spacer(80),

      heading(3, "12.5 admin/manage_categories.php"),
      boldPara("File Path: ", "/reference/admin/manage_categories.php"),
      boldPara("Purpose: ", "Category CRUD with up to 3 slide images per category"),
      boldPara("Dependencies: ", "config.php, header.php, footer.php"),
      boldPara("Database Tables: ", "categories"),
      boldPara("Key Features:", ""),
      bullet("View all categories with slide image previews"),
      bullet("Add new category with name and up to 3 slide images"),
      bullet("Edit existing categories with image replacement"),
      bullet("Delete categories (with slide image cleanup from uploads/)"),
      bullet("Image upload via handle_category_upload() helper: generates unique names (cat_****.ext)"),
      bullet("Individual slide deletion via POST action=delete_category_slide"),
      boldPara("Interacts With: ", "index.php (category slideshow on homepage), uploads/ directory"),
      ...tryScreenshot("14-admin-categories.png", 550),

      pageBreak(),

      // ---- 13. Additional Pages ----
      heading(2, "13. Additional Pages", "additional"),

      heading(3, "13.1 about.php"),
      boldPara("File Path: ", "/reference/about.php"),
      boldPara("Purpose: ", "About page with live store statistics"),
      boldPara("Database Tables: ", "products, orders, users, categories"),
      boldPara("Key Stats Displayed: ", "Total products, total orders, registered customers, categories count"),

      spacer(60),

      heading(3, "13.2 contact.php"),
      boldPara("File Path: ", "/reference/contact.php"),
      boldPara("Purpose: ", "Contact form page"),
      boldPara("Note: ", "Client-side form; no backend email handler (static contact form)"),

      spacer(60),

      heading(3, "13.3 download.php"),
      boldPara("File Path: ", "/reference/download.php"),
      boldPara("Purpose: ", "Mobile app download page with app store badges and QR code"),
      boldPara("Features: ", "Google Play badge, App Store badge, QR code display"),

      spacer(60),

      heading(3, "13.4 set_currency.php"),
      boldPara("File Path: ", "/reference/set_currency.php"),
      boldPara("Purpose: ", "Currency switch handler (called when user toggles USD/ETB)"),
      boldPara("Key Features:", ""),
      bullet("Accepts POST or GET with 'currency' parameter"),
      bullet("Calls smartmall_set_selected_currency() to persist choice in session"),
      bullet("Validates redirect URL: parses HTTP_REFERER, rejects cross-origin URLs"),
      bullet("Sanitizes redirect: strips newlines, ensures relative path starts with /"),
      boldPara("Called By: ", "Currency toggle in header.php navigation"),

      spacer(60),

      heading(3, "13.5 check_schema.php"),
      boldPara("File Path: ", "/reference/check_schema.php"),
      boldPara("Purpose: ", "Development utility — outputs product table column names as JSON"),
      boldPara("Output: ", "JSON array of column names from DESCRIBE products"),

      spacer(60),

      heading(3, "13.6 protect.php"),
      boldPara("File Path: ", "/reference/protect.php"),
      boldPara("Purpose: ", "Password-gated page protection"),
      boldPara("Credentials: ", "Password is 'smartmalltest!' (defined as $SECRET)"),
      boldPara("Key Features:", ""),
      bullet("Session-based unlock: sets $_SESSION['unlocked'] = true on correct password"),
      bullet("Mobile-responsive password form with gradient design"),
      boldPara("Called By: ", "Included at top of pages that need protection"),

      pageBreak(),

      // ---- 14. Shared Components ----
      heading(2, "14. Shared Layout Components", "shared"),

      heading(3, "14.1 includes/header.php"),
      boldPara("File Path: ", "/reference/includes/header.php"),
      boldPara("Purpose: ", "Shared HTML <head>, navigation bar, and opening <body> tags"),
      boldPara("Dependencies: ", "includes/currency.php"),
      boldPara("Key Features:", ""),
      bullet("Cache-control: no-store, no-cache headers for session pages"),
      bullet("Dark theme applied via JavaScript before page renders (localStorage 'smartmall-theme', respects prefers-color-scheme)"),
      bullet("Cart count badge: SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = :user_id"),
      bullet("Current page detection via basename($_SERVER['PHP_SELF'])"),
      bullet("Navigation: Home, Shop, About, Contact, Cart (with badge), My Orders, Download App, Admin (if role=admin)"),
      bullet("Currency dropdown: USD/ETB toggle via set_currency.php"),
      bullet("PWA: manifest.json link, apple-touch-icon, theme-color meta"),
      bullet("Mobile hamburger menu drawer"),

      spacer(80),

      heading(3, "14.2 includes/footer.php"),
      boldPara("File Path: ", "/reference/includes/footer.php"),
      boldPara("Purpose: ", "Shared footer with JavaScript assets and closing tags"),
      bullet("Sticky cart notification bar at bottom of page"),
      bullet("Toast notification system (showToast function)"),
      bullet("Search autocomplete JavaScript (calls api/search.php)"),
      bullet("Slideshow/carousel JavaScript for homepage"),
      bullet("Dark theme toggle JavaScript"),

      spacer(80),

      // ---- 15. PWA ----
      heading(2, "15. Progressive Web App", "pwa"),
      heading(3, "15.1 manifest.json"),
      boldPara("File Path: ", "/reference/manifest.json"),
      boldPara("Purpose: ", "PWA manifest enabling installable web app"),
      bullet("Display: standalone (full-screen, no browser chrome)"),
      bullet("Theme color: #007AFF"),
      bullet("Background color: #FFFFFF"),
      bullet("Name: 'Smart Mall'"),
      bullet("Icons: logo-icon.png"),

      pageBreak(),

      // ---- 16. Mobile App ----
      heading(2, "16. Capacitor Mobile App", "mobile"),
      heading(3, "16.1 Capacitor Project (smartmall-app/)"),
      boldPara("Project Root: ", "/reference/smartmall-app/"),
      boldPara("Framework: ", "Capacitor v8 (NOT Flutter)"),

      spacer(60),
      heading(4, "capacitor.config.json"),
      boldPara("Key Configuration:", ""),
      bullet("webDir: 'www' (the web app root)"),
      bullet("server.url: 'https://smartmall.unaux.com'"),
      bullet("appId: 'com.example.smartmall'"),
      bullet("appName: 'Smart Mall'"),

      spacer(60),
      heading(4, "www/app.js"),
      boldPara("File Path: ", "/reference/smartmall-app/www/app.js"),
      boldPara("Purpose: ", "Capacitor app entry point — native back button and deep link handling"),
      bullet("StatusBar.hide() — hides status bar on app launch"),
      bullet("SplashScreen.hide() — hides splash screen on app launch"),
      bullet("backButton listener: if canGoBack, calls window.history.back(); otherwise App.exitApp()"),
      bullet("appUrlOpen listener: intercepts URLs containing 'smartmall.unaux.com/' and navigates in-app"),
    ],
  };
}

// ---- PART IV: DATA FLOWS ----
function part4Flows() {
  return {
    properties: bodyPageSetup(),
    ...bodyHeaderFooter(),
    children: [
      heading(1, "Part IV: Data Flows", "flows"),
      spacer(120),

      heading(2, "17. Add to Cart Flow", "flow-cart"),
      para("The add-to-cart flow is entirely AJAX-driven, providing instant feedback without page reload:"),
      numberItem("User clicks 'Add to Cart' on a product card or product detail page."),
      numberItem("JavaScript in the page sends a POST request to add_to_cart.php with product_id and quantity as FormData."),
      numberItem("add_to_cart.php checks authentication: if no user_id in session, returns JSON {error: 'Please login first'}."),
      numberItem("If authenticated, validates stock: SELECT stock FROM products WHERE id = :id. Caps quantity at stock if exceeded."),
      numberItem("Checks for existing cart entry: SELECT id, quantity FROM cart WHERE user_id = :uid AND product_id = :pid."),
      numberItem("If exists: UPDATE cart SET quantity = :qty WHERE id = :cid. If not: INSERT INTO cart (user_id, product_id, quantity)."),
      numberItem("Recalculates cart count: SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = :uid."),
      numberItem("Returns JSON: {success: true, message, cart_count, new_quantity}."),
      numberItem("JavaScript updates the cart count badge and shows a success toast."),
      para("This entire flow takes under 500ms on a local development environment."),

      spacer(120),

      heading(2, "18. Checkout & Payment Flow", "flow-checkout"),
      para("The checkout process involves a PDO transaction, a Chapa API call for online payments, and a post-return verification step:"),
      numberItem("User reviews cart on cart.php and clicks 'Proceed to Checkout'."),
      numberItem("checkout.php loads cart items via JOIN with products and calculates subtotal + 10% VAT."),
      numberItem("User fills in billing/shipping form and selects payment method (Chapa / Bank Transfer / Cash on Delivery)."),
      numberItem("On form POST: csrf_verify() is called first. Then a PDO transaction begins."),
      numberItem("Row-level stock check: SELECT stock FROM products WHERE id = :id FOR UPDATE for every item."),
      numberItem("Order INSERT with shipping details. Payment INSERT with method and status."),
      numberItem("Order items INSERT for each product. Stock UPDATE: stock = stock - quantity."),
      numberItem("For Chapa payments:"),
      subnumberItem("Generate tx_ref = 'ORD-' + order_id + '-' + date('Ymd'). Update payments row."),
      subnumberItem("POST JSON to https://api.chapa.co/v1/transaction/initialize with amount (ETB), currency, tx_ref, callback_url, return_url."),
      subnumberItem("On success: commit transaction, redirect user to Chapa checkout page."),
      subnumberItem("Cart is NOT deleted yet (preserved until payment callback)."),
      numberItem("For non-Chapa: commit transaction, clear cart immediately, redirect to order_confirmation.php."),

      spacer(120),

      heading(2, "19. Payment Verification Flow", "flow-verify"),
      para("After a Chapa payment, the user returns to order_confirmation.php which handles verification:"),
      numberItem("User arrives at order_confirmation.php?order_id=X after Chapa redirect."),
      numberItem("System checks payment status: if 'paid', display confirmation immediately."),
      numberItem("If 'pending' and method is 'chapa':"),
      subnumberItem("Fetch tx_ref from payments table."),
      subnumberItem("GET https://api.chapa.co/v1/transaction/verify/{tx_ref} with Bearer token auth."),
      subnumberItem("Parse response. Success requires: status='success' AND data.status='success'."),
      bullet("If verification succeeds: UPDATE payments SET status='paid', paid_at=NOW() — UPDATE orders SET status='processing'. DELETE FROM cart WHERE user_id = :uid."),
      bullet("If verification fails AND key contains 'TEST': assume success (test mode fallback). Writes synthetic response."),
      bullet("If verification fails and NOT test mode: UPDATE payments SET status='failed'. UPDATE orders SET status='cancelled'. Restore stock for each order_item."),
      numberItem("Redirect to self with ?verified=1 to prevent form resubmission."),

      spacer(120),

      heading(2, "20. Currency Conversion Flow", "flow-currency"),
      para("The currency system converts USD prices (stored in the database) to ETB for display and Chapa payments:"),
      numberItem("User selects ETB from the currency dropdown in the header."),
      numberItem("set_currency.php receives the request, calls smartmall_set_selected_currency('ETB'), persists to $_SESSION['currency'], and redirects back."),
      numberItem("On subsequent page loads, header.php reads smartmall_selected_currency() which returns 'ETB' from session."),
      numberItem("Formatting functions (smartmall_format_money) detect the active currency and exchange rate."),
      numberItem("smartmall_exchange_data() checks file cache at {sys_temp_dir}/smartmall_exchange_usd.json."),
      numberItem("If cached rates are valid (within expires_at), they are used immediately."),
      numberItem("If expired or missing, smartmall_fetch_exchange_rates() calls open.er-api.com/v6/latest/USD via cURL."),
      numberItem("The API response is validated (result=success, base_code=USD, rates.ETB exists)."),
      numberItem("Rates are cached to disk with LOCK_EX and a next_update timestamp."),
      numberItem("If the API call fails, stale cache is used. If no cache exists, rates default to 0.0 (USD-only fallback)."),
      numberItem("smartmall_format_money applies the rate: ETB X.XX = $USD * rate."),

      spacer(120),

      heading(2, "21. Password Reset Flow", "flow-reset"),
      para("The password reset process uses a token-based approach with one-hour expiration:"),
      numberItem("User navigates to forgot_password.php and enters their email."),
      numberItem("A 64-character hex token is generated: bin2hex(random_bytes(32))."),
      numberItem("The token, email, and expires_at (now + 1 hour) are INSERTed into password_resets."),
      numberItem("A reset link is constructed: {base_url}/reset_password.php?token={token}."),
      numberItem("The link is saved to {sys_temp_dir}/smartmall_reset_links.txt for dev access, and stored in $_SESSION['reset_link']."),
      numberItem("PHP mail() is called with the reset link (delivery depends on server mail configuration)."),
      numberItem("User navigates to the reset link. reset_password.php validates the token:"),
      subnumberItem("SELECT email, expires_at FROM password_resets WHERE token = :token."),
      subnumberItem("Checks strtotime(expires_at) > time()."),
      numberItem("If valid, user sets a new password (same policy as registration: 8+ chars, upper, lower, digit, special)."),
      numberItem("Password is hashed with PASSWORD_BCRYPT and UPDATE users SET password = :hash WHERE email = :email."),
      numberItem("The reset token is DELETEd from password_resets to prevent reuse."),
    ],
  };
}

// ---- PART V: SECURITY ----
function part5Security() {
  return {
    properties: bodyPageSetup(),
    ...bodyHeaderFooter(),
    children: [
      heading(1, "Part V: Security", "security"),
      spacer(120),

      heading(2, "22. Security Measures", "security-measures"),

      heading(3, "22.1 CSRF Protection"),
      para("All state-changing operations require CSRF token verification. Tokens are generated in includes/db.php using bin2hex(random_bytes(32)), producing a 64-character hexadecimal string stored in $_SESSION['csrf_token']. Verification uses PHP's hash_equals() function to prevent timing attacks. Forms include the token via csrf_field() and handlers call csrf_verify(), which terminates with HTTP 403 on mismatch."),
      boldPara("Files using CSRF: ", "login.php, register.php, checkout.php, cart.php, add_to_cart.php, orders.php, forgot_password.php, reset_password.php, admin/dashboard.php (product delete), admin/manage_orders.php, admin/manage_categories.php, admin/delete_product.php"),

      spacer(60),

      heading(3, "22.2 SQL Injection Prevention"),
      para("All database queries use PDO prepared statements with named parameters (:param) or positional placeholders (?). PDO::ATTR_EMULATE_PREPARES is set to false, ensuring real prepared statements are sent to MySQL. The charset is explicitly set to utf8mb4 in the DSN. No raw string interpolation or concatenation is used in any SQL query across the entire codebase."),

      spacer(60),

      heading(3, "22.3 XSS Prevention"),
      para("All user-controlled data rendered in HTML is escaped with htmlspecialchars($value, ENT_QUOTES, 'UTF-8'). This applies to product names, descriptions, form field values, error messages, and any other dynamic content displayed on pages. Session success/error messages are also escaped before display."),

      spacer(60),

      heading(3, "22.4 Password Security"),
      para("Passwords are hashed using PASSWORD_BCRYPT (PHP's bcrypt implementation) via password_hash(). Verification uses password_verify(), which is timing-safe. The minimum cost factor is 10 (PHP default). The password policy enforced at registration and password reset requires: minimum 8 characters, at least one uppercase letter, at least one lowercase letter, at least one digit, and at least one special character. This is enforced server-side through four separate preg_match() calls."),

      spacer(60),

      heading(3, "22.5 Session Security"),
      bullet("Session fixation prevention: session_regenerate_id(true) on login"),
      bullet("Session cleanup: session_unset() + session_destroy() on logout"),
      bullet("Cache-control: no-store, no-cache headers on session pages"),
      bullet("HTTP-only cookies (PHP default for sessions)"),
      bullet("No session ID in URLs"),

      spacer(60),

      heading(3, "22.6 File Upload Security"),
      para("The admin product form (admin/add_product.php) validates file uploads using PHP's upload error constants (UPLOAD_ERR_OK, etc.). Files are checked for size (server upload limits), and extensions are validated. Images are stored in the uploads/ directory with original filenames prefixed with unique identifiers for categories (cat_****.ext). Deletion handlers clean up associated files from the filesystem."),

      spacer(60),

      heading(3, "22.7 HTTP Security Headers"),
      para("config.php emits two security headers on every request:"),
      bullet("X-Content-Type-Options: nosniff — prevents MIME-type sniffing"),
      bullet("X-Frame-Options: SAMEORIGIN — prevents clickjacking by restricting framing to same origin"),
    ],
  };
}

// ---- PART VI: APPENDICES ----
function part6Appendices() {
  return {
    properties: bodyPageSetup(),
    ...bodyHeaderFooter(),
    children: [
      heading(1, "Part VI: Appendices", "appendices"),
      spacer(120),

      heading(2, "23. Complete DDL", "ddl"),
      para("The following DDL statements define all eight tables in the Smart Mall database schema:"),
      spacer(60),
      monopara("CREATE TABLE users ("),
      monopara("  user_id INT(11) PRIMARY KEY AUTO_INCREMENT,"),
      monopara("  name VARCHAR(100) NOT NULL,"),
      monopara("  email VARCHAR(100) NOT NULL UNIQUE,"),
      monopara("  phone VARCHAR(20) DEFAULT NULL,"),
      monopara("  password VARCHAR(255) NOT NULL,"),
      monopara("  role ENUM('customer','admin') DEFAULT 'customer',"),
      monopara("  address TEXT DEFAULT NULL,"),
      monopara("  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"),
      monopara(");"),
      spacer(60),
      monopara("CREATE TABLE categories ("),
      monopara("  category_id INT(11) PRIMARY KEY AUTO_INCREMENT,"),
      monopara("  name VARCHAR(255) NOT NULL,"),
      monopara("  slug VARCHAR(255) DEFAULT NULL,"),
      monopara("  image1 VARCHAR(255) DEFAULT NULL,"),
      monopara("  image2 VARCHAR(255) DEFAULT NULL,"),
      monopara("  image3 VARCHAR(255) DEFAULT NULL,"),
      monopara("  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"),
      monopara(");"),
      spacer(60),
      monopara("CREATE TABLE products ("),
      monopara("  product_id INT(11) PRIMARY KEY AUTO_INCREMENT,"),
      monopara("  category_id INT(11) DEFAULT NULL,"),
      monopara("  name VARCHAR(255) NOT NULL,"),
      monopara("  description TEXT DEFAULT NULL,"),
      monopara("  price DECIMAL(10,2) NOT NULL,"),
      monopara("  image VARCHAR(255) DEFAULT NULL,"),
      monopara("  images LONGTEXT DEFAULT NULL,"),
      monopara("  video VARCHAR(255) DEFAULT NULL,"),
      monopara("  stock INT(11) NOT NULL DEFAULT 0,"),
      monopara("  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,"),
      monopara("  FOREIGN KEY (category_id) REFERENCES categories(category_id)"),
      monopara(");"),
      spacer(60),
      monopara("CREATE TABLE cart ("),
      monopara("  cart_id INT(11) PRIMARY KEY AUTO_INCREMENT,"),
      monopara("  user_id INT(11) NOT NULL,"),
      monopara("  product_id INT(11) NOT NULL,"),
      monopara("  quantity INT(11) NOT NULL DEFAULT 1,"),
      monopara("  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,"),
      monopara("  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,"),
      monopara("  FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE"),
      monopara(");"),
      spacer(60),
      monopara("CREATE TABLE orders ("),
      monopara("  order_id INT(11) PRIMARY KEY AUTO_INCREMENT,"),
      monopara("  user_id INT(11) NOT NULL,"),
      monopara("  total_price DECIMAL(10,2) NOT NULL,"),
      monopara("  status ENUM('pending','processing','paid','shipped','delivered','cancelled') DEFAULT 'pending',"),
      monopara("  first_name VARCHAR(100) DEFAULT NULL,"),
      monopara("  last_name VARCHAR(100) DEFAULT NULL,"),
      monopara("  email VARCHAR(255) DEFAULT NULL,"),
      monopara("  address TEXT DEFAULT NULL,"),
      monopara("  city VARCHAR(100) DEFAULT NULL,"),
      monopara("  state VARCHAR(100) DEFAULT NULL,"),
      monopara("  zip VARCHAR(20) DEFAULT NULL,"),
      monopara("  country VARCHAR(100) DEFAULT 'Ethiopia',"),
      monopara("  payment_method VARCHAR(50) DEFAULT 'chapa',"),
      monopara("  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,"),
      monopara("  FOREIGN KEY (user_id) REFERENCES users(user_id)"),
      monopara(");"),
      spacer(60),
      monopara("CREATE TABLE order_items ("),
      monopara("  order_item_id INT(11) PRIMARY KEY AUTO_INCREMENT,"),
      monopara("  order_id INT(11) NOT NULL,"),
      monopara("  product_id INT(11) NOT NULL,"),
      monopara("  quantity INT(11) NOT NULL,"),
      monopara("  price DECIMAL(10,2) NOT NULL,"),
      monopara("  FOREIGN KEY (order_id) REFERENCES orders(order_id),"),
      monopara("  FOREIGN KEY (product_id) REFERENCES products(product_id)"),
      monopara(");"),
      spacer(60),
      monopara("CREATE TABLE payments ("),
      monopara("  id INT(11) PRIMARY KEY AUTO_INCREMENT,"),
      monopara("  order_id INT(11) NOT NULL,"),
      monopara("  method VARCHAR(50) NOT NULL,"),
      monopara("  status ENUM('pending','paid','failed') DEFAULT 'pending',"),
      monopara("  amount DECIMAL(10,2) NOT NULL,"),
      monopara("  currency VARCHAR(10) NOT NULL DEFAULT 'USD',"),
      monopara("  tx_ref VARCHAR(100) DEFAULT NULL,"),
      monopara("  chapa_response LONGTEXT DEFAULT NULL,"),
      monopara("  paid_at DATETIME DEFAULT NULL,"),
      monopara("  created_at DATETIME NOT NULL,"),
      monopara("  FOREIGN KEY (order_id) REFERENCES orders(order_id)"),
      monopara(");"),
      spacer(60),
      monopara("CREATE TABLE password_resets ("),
      monopara("  reset_id INT(11) PRIMARY KEY AUTO_INCREMENT,"),
      monopara("  email VARCHAR(100) NOT NULL,"),
      monopara("  token VARCHAR(64) NOT NULL,"),
      monopara("  expires_at DATETIME NOT NULL,"),
      monopara("  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"),
      monopara(");"),

      pageBreak(),

      heading(2, "24. Component Dependency Map", "deps"),
      para("The following table summarizes how every file depends on includes/db.php (for the PDO connection and CSRF helpers) and includes/currency.php (for the multi-currency engine):"),
      spacer(60),

      (() => {
        const dw = [3000, 1200, 1200, 1200, 2760];
        return componentTable([
          ["File", "config.php", "db.php", "currency.php", "Database Tables Used"],
          ["config.php", "—", "Yes (require)", "Yes (require)", "None directly"],
          ["index.php", "Yes", "via config", "via config", "products, categories"],
          ["product.php", "Yes", "via config", "via config", "products, categories"],
          ["cart.php", "Yes", "via config", "via config", "cart, products"],
          ["checkout.php", "Yes", "via config", "via config", "cart, products, orders, order_items, payments"],
          ["order_confirmation.php", "Yes", "via config", "via config", "orders, payments, order_items, products, cart"],
          ["orders.php", "Yes", "via config", "via config", "orders, payments, order_items"],
          ["login.php", "No", "Yes (require)", "No", "users"],
          ["register.php", "Yes", "via config", "via config", "users"],
          ["logout.php", "No", "No", "No", "None"],
          ["forgot_password.php", "Yes", "via config", "via config", "password_resets"],
          ["reset_password.php", "Yes", "via config", "via config", "password_resets, users"],
          ["add_to_cart.php", "Yes", "via config", "via config", "cart, products"],
          ["api/search.php", "Yes", "via config", "via config", "products"],
          ["about.php", "Yes", "via config", "via config", "products, orders, users, categories"],
          ["set_currency.php", "Yes", "via config", "via config", "None"],
          ["admin/dashboard.php", "Yes", "via config", "via config", "products, categories, orders"],
          ["admin/add_product.php", "No", "Yes (require)", "No", "products, categories"],
          ["admin/delete_product.php", "No", "Yes (require)", "No", "products"],
          ["admin/manage_orders.php", "Yes", "via config", "via config", "orders, users, payments, order_items, products"],
          ["admin/manage_categories.php", "Yes", "via config", "via config", "categories"],
        ], dw);
      })(),

      pageBreak(),

      heading(2, "25. Deployment Checklist", "deployment"),
      para("To deploy the Smart Mall platform to a production server:"),
      spacer(60),
      numberItem("Ensure PHP 8.0+ with PDO, cURL, and MySQL extensions enabled."),
      numberItem("Create a MySQL database (e.g., smartmall_db) and execute the DDL statements in Appendix 23 to create all eight tables."),
      numberItem("Copy all files to the web server's document root, preserving the directory structure (uploads/, includes/, admin/, chapa_pay/, etc.)."),
      numberItem("Update database credentials in includes/db.php: set $host, $db_user, $db_pass, and $db_name for the production environment."),
      numberItem("Configure the Chapa production secret key in chapa_pay/chapa-config.php (replace CHASECK_TEST-* with the live key)."),
      numberItem("Ensure the uploads/ directory is writable by the web server process (chmod 755 or 775)."),
      numberItem("For the mobile app: update server.url in smartmall-app/capacitor.config.json to the production domain, then build with 'npx cap sync android'."),
      numberItem("Configure cron for abandoned order cleanup if desired (30-minute interval recommended)."),
      numberItem("Enable HTTPS (required by Chapa API for production callbacks)."),
      numberItem("Test the full payment flow: registration, product browsing, add to cart, checkout with Chapa (sandbox), and order confirmation."),
    ],
  };
}


// ========== ASSEMBLE DOCUMENT ==========

function numberConfig(reference, text) {
  return {
    reference,
    levels: [{
      level: 0, format: LevelFormat.DECIMAL, text, alignment: AlignmentType.LEFT,
      style: { paragraph: { indent: { left: 720, hanging: 360 } } },
    }],
  };
}

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
      numberConfig("numbers", "%1."),
      numberConfig("numbers2", "  %a."),
    ],
  },
  sections: [
    titlePage(),
    tocSection(),
    part1Overview(),
    part2Database(),
    part3Components(),
    part4Flows(),
    part5Security(),
    part6Appendices(),
  ],
});

const outputPath = "/opt/lampp/htdocs/reference/docs/Smart_Mall_Technical_Documentation.docx";
Packer.toBuffer(doc).then(buffer => {
  fs.writeFileSync(outputPath, buffer);
  console.log("Documentation generated successfully!");
  console.log("File: " + outputPath);
  console.log("Size: " + (buffer.length / 1024).toFixed(1) + " KB");
}).catch(err => {
  console.error("Error generating documentation:", err);
  process.exit(1);
});
