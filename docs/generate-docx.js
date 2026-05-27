const fs = require("fs");
const {
  Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
  Header, Footer, AlignmentType, LevelFormat,
  ExternalHyperlink, Bookmark, InternalHyperlink,
  TableOfContents, HeadingLevel, BorderStyle, WidthType, ShadingType,
  PageNumber, PageBreak
} = require("docx");

const border = { style: BorderStyle.SINGLE, size: 1, color: "CCCCCC" };
const borders = { top: border, bottom: border, left: border, right: border };
const cellMargins = { top: 60, bottom: 60, left: 100, right: 100 };

function heading(level, text, id) {
  const headingMap = {
    1: HeadingLevel.HEADING_1,
    2: HeadingLevel.HEADING_2,
    3: HeadingLevel.HEADING_3,
  };
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

function boldPara(label, value) {
  return new Paragraph({
    spacing: { after: 80 },
    children: [
      new TextRun({ text: label, bold: true, font: "Arial", size: 22 }),
      new TextRun({ text: value, font: "Arial", size: 22 }),
    ],
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
      children: [new TextRun({ text, font: "Arial", size: 20, ...opts })],
    })],
  });
}

function spacer(pts = 200) {
  return new Paragraph({ spacing: { after: pts }, children: [] });
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
    ],
  },
  numbering: {
    config: [
      {
        reference: "bullets",
        levels: [{
          level: 0, format: LevelFormat.BULLET, text: "\u2022", alignment: AlignmentType.LEFT,
          style: { paragraph: { indent: { left: 720, hanging: 360 } } },
        }],
      },
      {
        reference: "numbers",
        levels: [{
          level: 0, format: LevelFormat.DECIMAL, text: "%1.", alignment: AlignmentType.LEFT,
          style: { paragraph: { indent: { left: 720, hanging: 360 } } },
        }],
      },
    ],
  },
  sections: [
    // ===================== TITLE PAGE =====================
    {
      properties: {
        page: {
          size: { width: 12240, height: 15840 },
          margin: { top: 1440, right: 1440, bottom: 1440, left: 1440 },
        },
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
          children: [new TextRun({ text: "Complete Documentation", size: 48, font: "Arial", color: "2E4057" })],
        }),
        new Paragraph({
          alignment: AlignmentType.CENTER,
          spacing: { after: 400 },
          border: { bottom: { style: BorderStyle.SINGLE, size: 6, color: "2E4057", space: 1 } },
          children: [],
        }),
        new Paragraph({
          alignment: AlignmentType.CENTER,
          spacing: { after: 80 },
          children: [new TextRun({ text: "User Guide", size: 28, font: "Arial", color: "4A6FA5" })],
        }),
        new Paragraph({
          alignment: AlignmentType.CENTER,
          spacing: { after: 80 },
          children: [new TextRun({ text: "Admin Guide", size: 28, font: "Arial", color: "4A6FA5" })],
        }),
        new Paragraph({
          alignment: AlignmentType.CENTER,
          spacing: { after: 80 },
          children: [new TextRun({ text: "Developer Guide", size: 28, font: "Arial", color: "4A6FA5" })],
        }),
        spacer(2000),
        new Paragraph({
          alignment: AlignmentType.CENTER,
          children: [new TextRun({ text: "Version 1.0", size: 24, font: "Arial", color: "888888" })],
        }),
      ],
    },

    // ===================== TABLE OF CONTENTS =====================
    {
      properties: {
        page: {
          size: { width: 12240, height: 15840 },
          margin: { top: 1440, right: 1440, bottom: 1440, left: 1440 },
        },
      },
      headers: {
        default: new Header({
          children: [new Paragraph({
            border: { bottom: { style: BorderStyle.SINGLE, size: 4, color: "2E4057", space: 4 } },
            children: [new TextRun({ text: "Smart Mall Documentation", font: "Arial", size: 18, color: "888888" })],
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
        new TableOfContents("Table of Contents", { hyperlink: true, headingStyleRange: "1-3" }),
        new Paragraph({ children: [new PageBreak()] }),

        // ===================== PART I: USER GUIDE =====================
        heading(1, "Part I: User Guide"),

        // --- Chapter 1 ---
        heading(2, "1. Getting Started", "user-getting-started"),
        heading(3, "1.1 Registration"),
        para("To create a new Smart Mall account:"),
        numberItem("Navigate to the Smart Mall website and click the \"Register\" link in the top navigation bar."),
        numberItem("Fill in your full name, email address, phone number, and a secure password."),
        numberItem("Click \"Create Account\" to complete registration."),
        numberItem("You will be automatically logged in after successful registration."),
        spacer(60),
        para([{ text: "Note: ", bold: true }, { text: "Password must be at least 8 characters and include a mix of letters and numbers." }]),

        heading(3, "1.2 Login"),
        numberItem("Click the \"Login\" link in the top navigation bar."),
        numberItem("Enter your registered email address and password."),
        numberItem("Click \"Sign In\" to access your account."),
        bullet("Check \"Remember Me\" to stay logged in across sessions."),

        // --- Chapter 2 ---
        heading(2, "2. Browsing Products", "user-browsing"),
        heading(3, "2.1 Homepage"),
        para("The homepage displays a grid of available products with their images, names, prices, and stock quantities. Products show the current price in both USD and ETB (Ethiopian Birr)."),
        bullet("Each product card shows: thumbnail image, product name, price, stock status."),
        bullet("Use the \"Buy Now\" button for immediate purchase."),
        bullet("Use \"Add to Cart\" to shop for multiple items."),

        heading(3, "2.2 Search"),
        para("The search bar at the top of every page allows you to find products by name or description. Results update dynamically as you type, showing up to 6 matching products with live price display."),
        para("Advanced tips:"),
        bullet("Search matches partial words (e.g., \"phone\" finds \"Smartphone\")."),
        bullet("Search includes product descriptions, not just titles."),

        heading(3, "2.3 Categories"),
        para("Use the category dropdown or filter buttons on the homepage to narrow products by category. Each category can feature up to 3 slide images for promotional display."),

        heading(3, "2.4 Product Details"),
        para("Click any product card or image to view the full product details page, which includes:"),
        bullet("Multiple product images with gallery navigation (previous/next arrows)."),
        bullet("Full product description and specifications."),
        bullet("Price displayed in both USD and ETB."),
        bullet("Stock availability indicator."),
        bullet("\"Add to Cart\" and \"Buy Now\" action buttons."),

        // --- Chapter 3 ---
        heading(2, "3. Shopping Cart", "user-cart"),
        heading(3, "3.1 Adding Items"),
        para("Add products to your cart using the \"Add to Cart\" button on product cards or the product details page. A sticky cart bar appears at the bottom of every page showing your current item count and total."),

        heading(3, "3.2 Managing Your Cart"),
        para("Click the cart icon in the navigation bar to open your shopping cart. From here you can:"),
        bullet("View all items with their individual prices."),
        bullet("Update quantities using the +/- controls."),
        bullet("Remove items individually."),
        bullet("See the subtotal, 10% VAT, and grand total."),
        bullet("Proceed to checkout."),

        heading(3, "3.3 Cart Persistence"),
        para("Your cart is stored server-side and persists across browser sessions when logged in. A sticky cart notification bar at the bottom of the page displays your current cart status."),

        // --- Chapter 4 ---
        heading(2, "4. Checkout & Payment", "user-checkout"),
        heading(3, "4.1 Checkout Process"),
        numberItem("Review your cart items and totals."),
        numberItem("Click \"Proceed to Checkout\"."),
        numberItem("Confirm your shipping details (name, phone, address)."),
        numberItem("Select a payment method."),
        numberItem("Place your order."),

        heading(3, "4.2 Chapa Payment (Online)"),
        para("Chapa is the integrated online payment gateway supporting:"),
        bullet("Ethiopian Birr (ETB) payments."),
        bullet("Bank transfers, mobile banking, and telebirr."),
        bullet("Debit/credit cards."),
        para("After placing an order with Chapa, you will be redirected to the Chapa payment page to complete the transaction securely."),

        heading(3, "4.3 Cash on Delivery"),
        para("Select \"Cash on Delivery\" to pay in cash when your order arrives. No online payment is required at checkout."),

        heading(3, "4.4 Currency Switching"),
        para("Toggle between USD and ETB using the currency selector in the header:"),
        bullet("Prices update instantly across all pages."),
        bullet("Exchange rates are cached for performance."),
        bullet("Checkout processes in the selected currency."),

        // --- Chapter 5 ---
        heading(2, "5. Order Tracking", "user-orders"),
        heading(3, "5.1 Viewing Orders"),
        para("Access your order history from the \"My Orders\" link in the navigation bar. The orders page shows:"),
        bullet("Order ID and date placed."),
        bullet("Current status (Pending / Paid / Shipped / Delivered / Cancelled)."),
        bullet("Total amount in your chosen currency."),
        bullet("Item details and quantities."),

        heading(3, "5.2 Order Statuses"),
        para([{ text: "Pending: ", bold: true }, { text: "Order received, awaiting payment confirmation." }]),
        para([{ text: "Paid: ", bold: true }, { text: "Payment confirmed, order is being processed." }]),
        para([{ text: "Shipped: ", bold: true }, { text: "Order has been dispatched." }]),
        para([{ text: "Delivered: ", bold: true }, { text: "Order completed successfully." }]),
        para([{ text: "Cancelled: ", bold: true }, { text: "Order was cancelled by you or the admin." }]),

        heading(3, "5.3 Cancelling Orders"),
        para("Pending orders can be cancelled from the order history page. Click the \"Cancel\" button next to a pending order to cancel it."),

        // --- Chapter 6 ---
        heading(2, "6. Mobile App", "user-mobile"),
        para("Smart Mall offers a Flutter-based mobile application for Android and iOS:"),
        bullet("Browse products, search, and filter by category."),
        bullet("View product details with images."),
        bullet("Manage your shopping cart locally."),
        bullet("User registration and login."),
        bullet("Place orders with Chapa payment."),
        bullet("View order history."),
        bullet("Admin dashboard for store management."),
        para("Download the app from the \"Download App\" page on the website, or scan the QR code."),

        // --- Chapter 7 ---
        heading(2, "7. Contact & Support", "user-support"),
        para("Use the Contact page to reach the Smart Mall team:"),
        bullet("Send inquiries through the contact form."),
        bullet("Find store information on the About page."),
        para("The About page displays live store statistics:"),
        bullet("Total number of products listed."),
        bullet("Total orders processed."),
        bullet("Registered customers."),
        bullet("Product categories available."),

        new Paragraph({ children: [new PageBreak()] }),

        // ===================== PART II: ADMIN GUIDE =====================
        heading(1, "Part II: Admin Guide"),

        // --- Chapter 1 ---
        heading(2, "1. Admin Dashboard", "admin-dashboard"),
        para("The admin dashboard provides a centralized overview of your store:"),
        bullet("Total products count and low-stock alerts."),
        bullet("Total orders and revenue statistics."),
        bullet("Number of registered customers and categories."),
        bullet("Quick-action buttons for managing products, categories, and orders."),
        bullet("Product management table with edit/delete actions."),
        para([{ text: "Access: ", bold: true }, { text: "Navigate to /admin/dashboard.php or click \"Admin\" in the navigation (requires admin credentials)." }]),

        // --- Chapter 2 ---
        heading(2, "2. Product Management", "admin-products"),
        heading(3, "2.1 Viewing Products"),
        para("The product management page lists all products with:"),
        bullet("Product ID, name, category, price (USD)."),
        bullet("Stock quantity."),
        bullet("Edit and Delete action buttons."),

        heading(3, "2.2 Adding a Product"),
        numberItem("Click \"Add New Product\" on the products page."),
        numberItem("Fill in product details: name, description, category, price, stock."),
        numberItem("Upload product images (supports multiple image uploads)."),
        numberItem("Click \"Save\" to add the product."),

        heading(3, "2.3 Editing a Product"),
        numberItem("Click \"Edit\" next to the product you want to modify."),
        numberItem("Update any fields as needed."),
        numberItem("Replace or add new images if required."),
        numberItem("Click \"Save\" to apply changes."),

        heading(3, "2.4 Deleting a Product"),
        para("Click \"Delete\" next to a product to remove it. This action:"),
        bullet("Removes the product from the database."),
        bullet("Deletes associated product images from the server."),
        bullet("Cannot be undone."),

        // --- Chapter 3 ---
        heading(2, "3. Category Management", "admin-categories"),
        para("Manage product categories to organize your store:"),
        bullet("View all categories with their associated slide images."),
        bullet("Add new categories with a name and up to 3 promotional slide images."),
        bullet("Edit existing categories and update images."),
        bullet("Delete categories (ensure no products are assigned first)."),

        // --- Chapter 4 ---
        heading(2, "4. Order Management", "admin-orders"),
        para("The order management page provides full control over customer orders:"),
        bullet("View all orders sorted by status (paid orders first)."),
        bullet("Order details include: ID, customer name, items, total, status, date."),
        bullet("Update order status through the workflow: Pending -> Paid -> Shipped -> Delivered."),
        bullet("Orders can be marked as cancelled when needed."),

        // --- Chapter 5 ---
        heading(2, "5. Payment Debugging", "admin-payment"),
        para("Smart Mall includes comprehensive payment debugging tools:"),
        heading(3, "5.1 Payment Debug Dashboard"),
        para("Located at /admin/chapa-debug.php, this shows:"),
        bullet("Last 20 payment transactions with status details."),
        bullet("Cart status for each transaction."),
        bullet("Order references and amounts."),

        heading(3, "5.2 Configuration Verification"),
        para("Located at /admin/verify-chapa.php, this runs 7 verification tests:"),
        bullet("Chapa secret key presence."),
        bullet("Payment table schema integrity."),
        bullet("Database connection status."),
        bullet("Order and payment relationship checks."),

        heading(3, "5.3 Manual Payment Completion"),
        para("For testing or recovery, use /complete-payment.php to manually finalize a payment."),

        // --- Chapter 6 ---
        heading(2, "6. System Cleanup", "admin-cleanup"),
        para("Two cleanup mechanisms help maintain database health:"),
        heading(3, "6.1 Manual Cleanup (Admin UI)"),
        para("Located at /admin/cleanup-orders.php. Provides an interface to trigger abandoned order cleanup."),

        heading(3, "6.2 Automated Cleanup (CLI/Cron)"),
        para("The script /cleanup-abandoned-orders.php can be run via CLI or cron job:"),
        bullet("Deletes abandoned Chapa orders pending for more than 30 minutes."),
        bullet("Deletes cancelled orders older than 7 days."),
        bullet("Recommended cron schedule: every 15 minutes."),

        new Paragraph({ children: [new PageBreak()] }),

        // ===================== PART III: DEVELOPER GUIDE =====================
        heading(1, "Part III: Developer Guide"),

        // --- Chapter 1 ---
        heading(2, "1. Project Overview", "dev-overview"),
        para("Smart Mall is a full-stack e-commerce platform consisting of:"),
        bullet([{ text: "PHP Backend: ", bold: true }, { text: "Server-side rendering with MySQL database." }]),
        bullet([{ text: "Flutter Mobile App: ", bold: true }, { text: "Cross-platform mobile application for Android and iOS." }]),
        bullet([{ text: "REST API: ", bold: true }, { text: "JSON endpoints for mobile app integration." }]),
        bullet([{ text: "Chapa Payment Gateway: ", bold: true }, { text: "ETB online payment processing." }]),

        // --- Chapter 2 ---
        heading(2, "2. Architecture", "dev-architecture"),
        heading(3, "2.1 Directory Structure"),
        para("The project follows this directory layout:"),
        para([{ text: "/reference/", bold: true }, { text: " - Web root containing all PHP files." }]),
        para([{ text: "/reference/includes/", bold: true }, { text: " - Shared components (header, footer, database, helpers)." }]),
        para([{ text: "/reference/admin/", bold: true }, { text: " - Admin panel pages." }]),
        para([{ text: "/reference/api/", bold: true }, { text: " - JSON API endpoints." }]),
        para([{ text: "/reference/chapa_pay/", bold: true }, { text: " - Chapa payment integration." }]),
        para([{ text: "/reference/assets/", bold: true }, { text: " - Static assets (images)." }]),
        para([{ text: "/reference/flutter_app/", bold: true }, { text: " - Flutter mobile application." }]),

        heading(3, "2.2 Request Flow"),
        para("1. User visits a page (index.php, product.php, etc.)"),
        para("2. config.php bootstraps the application (session, DB, auth, currency)."),
        para("3. The page includes header.php and footer.php for the shared layout."),
        para("4. Database queries render dynamic content (products, cart, orders)."),
        para("5. Chapa payment gateway handles online transactions via redirect."),

        // --- Chapter 3 ---
        heading(2, "3. Setup & Installation", "dev-setup"),
        heading(3, "3.1 Requirements"),
        bullet("PHP 8.0+ with PDO and MySQL extensions."),
        bullet("MySQL 5.7+ or MariaDB 10.3+."),
        bullet("Apache or Nginx web server."),
        bullet("Flutter SDK (for mobile app development)."),
        bullet("Node.js 18+ (for documentation tools)."),

        heading(3, "3.2 Installation Steps"),
        numberItem("Clone the repository to your web server's document root."),
        numberItem("Create a MySQL database and import the schema (see Database Schema section)."),
        numberItem("Configure database credentials in includes/db.php."),
        numberItem("Set the Chapa secret key in chapa_pay/chapa-config.php."),
        numberItem("Ensure the uploads directory is writable by the web server."),
        numberItem("Access the site via your configured domain or localhost."),

        heading(3, "3.3 Mobile App Setup"),
        numberItem("Navigate to flutter_app/smart_mall_app/."),
        numberItem("Update the API base URL in lib/services/api_service.dart."),
        numberItem("Run \"flutter pub get\" to install dependencies."),
        numberItem("Run \"flutter run\" to launch on a connected device or emulator."),

        // --- Chapter 4 ---
        heading(2, "4. Database Schema", "dev-database"),
        para("The database uses the following core tables:"),

        heading(3, "4.1 products"),
        para("Stores all product information:"),
        para([{ text: "id, name, description, price, stock, category_id, image, images (JSON array), created_at." }]),

        heading(3, "4.2 categories"),
        para("Product categorization:"),
        para([{ text: "id, name, slide_image_1, slide_image_2, slide_image_3." }]),

        heading(3, "4.3 users"),
        para("Customer accounts:"),
        para([{ text: "id, name, email, phone, password (hashed), role, created_at." }]),

        heading(3, "4.4 cart"),
        para("Shopping cart items:"),
        para([{ text: "id, user_id, product_id, quantity, created_at." }]),

        heading(3, "4.5 orders"),
        para("Customer orders:"),
        para([{ text: "id, user_id, total, status (pending/paid/shipped/delivered/cancelled), created_at." }]),

        heading(3, "4.6 order_items"),
        para("Individual items within each order:"),
        para([{ text: "id, order_id, product_id, quantity, price." }]),

        heading(3, "4.7 payments"),
        para("Chapa payment transactions:"),
        para([{ text: "id, order_id, user_id, amount, currency, status, chapa_tx_ref, cart_id, created_at." }]),

        // --- Chapter 5 ---
        heading(2, "5. Configuration", "dev-config"),
        heading(3, "5.1 config.php"),
        para("The bootstrap file handles:"),
        bullet("Session management and security headers."),
        bullet("Database connection via includes/db.php."),
        bullet("Currency conversion via includes/currency.php."),
        bullet("Base URL definition for link generation."),
        bullet("Redirect helper function."),

        heading(3, "5.2 Chapa Payment Config"),
        para("Located in chapa_pay/chapa-config.php:"),
        bullet("CHASECK_TEST secret key (test mode)."),
        bullet("Chapa API base URL constant."),
        bullet("Replace with production key when going live."),

        heading(3, "5.3 Currency Configuration"),
        para("Exchange rate is defined in includes/currency.php:"),
        bullet("USD to ETB conversion rate (configurable)."),
        bullet("Rates cached to sys_get_temp_dir() for performance."),

        // --- Chapter 6 ---
        heading(2, "6. API Reference", "dev-api"),
        para("Smart Mall provides RESTful JSON API endpoints for the mobile app:"),

        heading(3, "6.1 Authentication"),
        para([{ text: "POST /api/auth.php", bold: true }, { text: " - Register or login. Returns a token on success." }]),

        heading(3, "6.2 Products"),
        para([{ text: "GET /api/search.php?q={query}", bold: true }, { text: " - Search products. Returns up to 6 results with display_price." }]),

        heading(3, "6.3 Orders"),
        para([{ text: "GET /api/orders.php", bold: true }, { text: " - List user orders. Requires Bearer token." }]),
        para([{ text: "POST /api/orders.php", bold: true }, { text: " - Create a new order. Requires Bearer token." }]),
        para([{ text: "DELETE /api/orders.php?id={id}", bold: true }, { text: " - Cancel an order. Requires Bearer token." }]),

        heading(3, "6.4 Profile"),
        para([{ text: "GET /api/profile.php", bold: true }, { text: " - Get user profile. Requires Bearer token." }]),
        para([{ text: "PUT /api/profile.php", bold: true }, { text: " - Update user profile. Requires Bearer token." }]),

        heading(3, "6.5 Authentication"),
        para("API requests use Bearer token authentication:"),
        para("Authorization: Bearer {your_token_here}"),
        bullet("Tokens are returned from the auth endpoint."),
        bullet("Include the token in all authenticated requests."),

        // --- Chapter 7 ---
        heading(2, "7. Flutter Mobile App", "dev-flutter"),
        heading(3, "7.1 Architecture"),
        para("The Flutter app uses:"),
        bullet([{ text: "Provider: ", bold: true }, { text: "State management for auth, cart, and data." }]),
        bullet([{ text: "API Service: ", bold: true }, { text: "HTTP client communicating with the PHP backend." }]),
        bullet([{ text: "Material 3: ", bold: true }, { text: "Modern Material Design components." }]),
        bullet([{ text: "Google Fonts: ", bold: true }, { text: "Custom typography." }]),

        heading(3, "7.2 Project Structure"),
        para("The Flutter app follows a standard structure:"),
        para([{ text: "lib/main.dart", bold: true }, { text: " - App entry point with routing and Provider setup." }]),
        para([{ text: "lib/services/", bold: true }, { text: " - API, Auth, and Cart service classes." }]),
        para([{ text: "lib/models/", bold: true }, { text: " - Product and Category data models." }]),
        para([{ text: "lib/screens/", bold: true }, { text: " - All UI screens (home, product, cart, checkout, auth, orders, profile, admin)." }]),

        heading(3, "7.3 Key Dependencies"),
        para("Defined in pubspec.yaml:"),
        bullet("provider - State management."),
        bullet("http - REST API communication."),
        bullet("shared_preferences - Local token storage."),
        bullet("google_fonts - Typography."),
        bullet("intl - Date and number formatting."),

        heading(3, "7.4 API Base URL Configuration"),
        para("Before running the app, update the base URL in:"),
        para([{ text: "lib/services/api_service.dart: ", bold: true }, { text: "Set _baseUrl to your server's address (e.g., http://192.168.1.100/reference/)." }]),

        // --- Chapter 8 ---
        heading(2, "8. Design System", "dev-design"),
        heading(3, "8.1 Color Palette"),
        para("The project uses a cohesive blue-based color scheme:"),

        (() => {
          const w = 2340;
          return new Table({
            width: { size: 9360, type: WidthType.DXA },
            columnWidths: [w, w, w, w],
            rows: [
              new TableRow({
                children: [
                  headerCell("Token", w),
                  headerCell("Color", w),
                  headerCell("Hex", w),
                  headerCell("Usage", w),
                ],
              }),
              new TableRow({
                children: [
                  cell("Primary", w, { bold: true }),
                  cell("", w, { shading: "1A365D" }),
                  cell("#1A365D", w),
                  cell("Headings, nav", w),
                ],
              }),
              new TableRow({
                children: [
                  cell("Secondary", w, { bold: true, shading: "F0F4F8" }),
                  cell("", w, { shading: "2E4057" }),
                  cell("#2E4057", w),
                  cell("Subheadings", w),
                ],
              }),
              new TableRow({
                children: [
                  cell("Accent", w, { bold: true }),
                  cell("", w, { shading: "4A6FA5" }),
                  cell("#4A6FA5", w),
                  cell("Links, buttons", w),
                ],
              }),
              new TableRow({
                children: [
                  cell("Background", w, { bold: true, shading: "F0F4F8" }),
                  cell("", w, { shading: "F0F4F8" }),
                  cell("#F0F4F8", w),
                  cell("Page bg", w),
                ],
              }),
            ],
          });
        })(),

        spacer(120),

        heading(3, "8.2 Typography"),
        bullet("Headings: Bold, various sizes (36pt H1, 30pt H2, 26pt H3)."),
        bullet("Body: Regular weight, 12pt (24 half-points)."),
        bullet("Font Family: System fonts (fallback stack)."),

        heading(3, "8.3 Layout"),
        bullet("Responsive design with CSS Grid and Flexbox."),
        bullet("Max-width container with centered content."),
        bullet("Card-based product grid layout."),
        bullet("Sticky navigation header and cart bar."),
        bullet("Mobile-responsive with hamburger menu drawer."),

        heading(3, "8.4 Components"),
        para("Reusable UI patterns across the site:"),
        bullet("Navigation bar with search, currency toggle, cart badge."),
        bullet("Product cards with image, name, price, actions."),
        bullet("Modal/drawer for mobile navigation."),
        bullet("Footer with quick links and newsletter signup."),
        bullet("Sticky cart bar (persistent bottom notification)."),
        bullet("Form inputs with consistent styling and validation."),
      ],
    },
  ],
});

Packer.toBuffer(doc).then(buffer => {
  fs.writeFileSync("/opt/lampp/htdocs/reference/docs/Smart_Mall_Documentation.docx", buffer);
  console.log("Documentation generated successfully!");
  console.log("File: /opt/lampp/htdocs/reference/docs/Smart_Mall_Documentation.docx");
});
