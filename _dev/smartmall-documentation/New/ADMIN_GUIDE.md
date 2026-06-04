# Smart Mall Admin Guide

> **Document ID:** ADMIN-GUIDE-001  
> **Target audience:** Store administrators and managers  
> **Access:** All admin pages require an admin-level login session  
> **Cross-reference:** MASTER_DOCUMENTATION.md §4.9 (Admin Features Implementation), §3.8 (Security Design)

---

## Table of Contents

1. [Dashboard Overview](#1-dashboard-overview)
2. [Managing Products](#2-managing-products)
3. [Managing Categories](#3-managing-categories)
4. [Managing Orders](#4-managing-orders)
5. [Managing Users](#5-managing-users)
6. [Reports & Analytics](#6-reports--analytics)
7. [Navigation & Admin Layout](#7-navigation--admin-layout)
8. [Troubleshooting](#8-troubleshooting)

---

## 1. Dashboard Overview

The dashboard is the first page you see after logging in as admin. It provides a high-level snapshot of your store.

### 1.1 Accessing the Dashboard

1. Log in with an admin account (email/password or Google Sign-In).
2. Navigate to the **Dashboard** from the admin navigation menu.
3. If your role is not admin, you will be redirected to the homepage.

[Screenshot: Admin dashboard showing 4 stat cards — Products, Categories, Orders, Users]

### 1.2 Statistics Cards

The dashboard shows 4 clickable stat cards in a responsive grid layout:

| Card | Description | Links To |
|------|-------------|----------|
| **Total Products** | Total count of all products | Manage Products |
| **Categories** | Total count of all categories | Manage Categories |
| **Total Orders** | Total count of all orders | Manage Orders |
| **Users** | Total count of all registered users | Manage Users |

An additional row shows **Total Stock** (sum of all product stock) and **Total Revenue** (sum of all order totals).

> [!NOTE]
> The dashboard does not support date range filtering. All counts are lifetime totals. For time-based analytics, see the [Reports & Analytics](#6-reports--analytics) section.

### 1.3 Quick Actions

From the dashboard you can:
- Click any stat card to jump to the full management page
- Use the browser back button or main nav to return

---

## 2. Managing Products

### 2.1 Product List

#### Viewing Products

The product list shows all products in a table with columns:

| Column | Details |
|--------|---------|
| **Image** | 48×48px product thumbnail |
| **Product Name** | Click product name to view raw text |
| **Category** | Shows assigned category or "Uncategorized" if none |
| **Price** | Formatted currency display |
| **Stock** | Quantity available |
| **Actions** | Edit + Delete buttons |

[Screenshot: Product list table showing Image, Name, Category, Price, Stock, and Action buttons]

#### Searching Products

A search box at the top filters results on the server:

1. Enter a keyword in the search input.
2. Press Enter or click Search.
3. Results match against both product name and category name.

#### Pagination

- **20 products per page** with numbered page navigation
- Previous/Next buttons when applicable
- Search query is preserved across pages

#### Editing a Product

Click the **Edit** button next to any product. This opens the same product form in edit mode, pre-populated with existing data.

#### Deleting a Product

1. Click the **Delete** button.
2. A confirmation dialog appears: *"Are you sure you want to delete this product?"*
3. On confirmation, the product is permanently deleted from the database.
4. The main product image file is cleaned up from the uploads directory.
5. An on-screen message confirms success.

> [!NOTE]
> Deleting a product does **not** cascade to past orders. Past orders retain their item records with the product name and price snapshot.

### 2.2 Adding / Editing Products

#### The Product Form

The same form handles both **Add** and **Edit** modes.

**Form Fields:**

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| **Category** | Select dropdown | No | Lists all available categories |
| **Product Name** | Text input | Yes | |
| **Description** | Textarea | Yes | Rich text (basic HTML supported) |
| **Price** | Number (step=0.01) | Yes | Must be greater than 0 |
| **Stock Quantity** | Number (min=0) | Yes | Cannot be negative |
| **Main Cover Image** | File (image/*) | Yes (new) | Max 5MB. Formats: JPEG, PNG, GIF, WebP |
| **Secondary Angle** | File (image/*) | No | Same format/limits as cover |
| **Detail Angle** | File (image/*) | No | Same format/limits as cover |
| **Extended Gallery** | File (multiple) | No | Up to 4 additional images |
| **Product Video** | File (video/mp4,webm) | No | Max 50MB. One video per product |

[Screenshot: Product add/edit form showing all fields with image previews]

#### Adding a New Product

1. Click **+ New Product** from the admin pages.
2. Fill in all required fields.
3. Upload images and/or video as needed.
4. Click **Save Product**.
5. On success: redirected to the dashboard with a green success message.

#### Editing an Existing Product

1. Click **Edit** next to any product in the product list.
2. The form loads with existing values pre-filled.
3. Image and video previews show current media.
4. To replace media: upload a new file (the old file is automatically deleted).
5. To remove media without replacing: click the overlay **X** button on the preview.
6. Click **Update Product**.

#### Validation Rules

All validation happens on the server when the form is submitted:

| Field | Rule | Error Message |
|-------|------|---------------|
| Name | Must not be empty | "Product name is required" |
| Description | Must not be empty | "Description is required" |
| Price | Must be greater than 0 | "Price must be greater than 0" |
| Stock | Must be 0 or more | "Stock cannot be negative" |
| Main Image (new only) | Must be a valid image file | From image upload validation |

#### Image Upload Details

- **Main image:** Validated for MIME type (JPEG, PNG, GIF, WebP only) with a 5MB size limit. The old image is automatically deleted when replaced.
- **Gallery images:** Same MIME validation. Up to 4 additional images can be uploaded at once. Stored as a set.
- **Video:** 50MB size limit. Accepted formats: MP4 and WebM.

#### Deleting Individual Media

You can remove specific media files without deleting the entire product:

1. While editing, click the **X** overlay on any image or video preview.
2. The system verifies the request and deletes the file from the uploads directory.
3. The corresponding database field is cleared.
4. The page reloads showing the updated media.

### 2.3 Deleting Products

> [!IMPORTANT] This action is irreversible.

1. The delete request is processed only through the Delete button (direct URL access is blocked).
2. The product record is permanently removed from the database.
3. The main product image is deleted from the filesystem.
4. The product cache is cleared.
5. A success message confirms: *"Product deleted successfully!"*

> [!NOTE]
> Additional images and the product video are **not** cleaned up when deleting — only the main image is removed.

---

## 3. Managing Categories

Categories group products for easier browsing. Each category can have a name, description, and up to 3 images.

### 3.1 Viewing Categories

The category management page shows all categories in a table with inline editing:

| Column | Details |
|--------|---------|
| **Name** | Editable text input |
| **Description** | Editable textarea |
| **Images** | Up to 3 slides with preview + file input + URL fallback |
| **Products** | Badge showing count of products in this category |
| **Actions** | Save (inline) / Remove (delete) |

### 3.2 Adding a Category

1. Fill in the **Name** field (required).
2. Optionally add a **Description**.
3. Upload images or paste image URLs (3 slots available).
4. Click **Add Category**.

On submit:
- A URL-friendly slug is auto-generated from the name.
- Images are validated (JPEG, PNG, WebP, GIF) and uploaded to the media directory.
- File uploads take precedence over text URL input.

### 3.3 Editing a Category

Each category row has inline editable fields:

1. Modify the **Name** or **Description** text fields.
2. Upload new images or change URL fields.
3. Click **Save**.

On save:
- Existing images are preserved from the database.
- New uploads replace old files.
- The category record is updated.

### 3.4 Deleting a Category

1. Click **Remove** on any category row.
2. The system checks for products in this category.
3. **If products exist:** deletion is blocked with an error — reassign or delete those products first.
4. **If no products:** the category is permanently deleted.

[Screenshot: Category list showing inline edit form with image previews and product count badge]

### 3.5 Deleting Individual Category Images

1. Click the **X** button on any image slide preview.
2. The file is deleted from the uploads directory and the database field is cleared.

---

## 4. Managing Orders

### 4.1 Viewing Orders

The orders page loads the **100 most recent orders**, sorted by payment status first (paid first) then by order date descending.

**Table Columns:**

| Column | Details |
|--------|---------|
| **Order ID** | Link to scroll to details |
| **Customer** | Name + Email |
| **Products Ordered** | Shown in expandable details |
| **Total** | Formatted price |
| **Date** | Timestamp |
| **Status** | Current order status badge |
| **Action** | Status update form |

[Screenshot: Orders table showing order rows with status badges]

### 4.2 Updating Order Status

1. Find the order in the list.
2. Use the status dropdown in the **Action** column.
3. Select a new status from: `pending`, `processing`, `shipped`, `delivered`, `cancelled`.
4. Click **Update**.

The server verifies the request and updates the order record. A success message confirms: *"Order #15 status updated to shipped"*.

### 4.3 Viewing Order Details

Click on any order row to expand its details:

- **Order Items:** Product image, name, unit price, quantity, subtotal
- **Shipping Details:** Address, city, state, zip, country
- **Payment Method:** As recorded during checkout

### 4.4 Searching Orders

The search box filters orders **client-side** only (all 100 orders are pre-loaded):

1. Type any keyword into the search box.
2. The list filters in real time as you type.
3. Non-matching rows are hidden.
4. A "No results found" message appears if nothing matches.

---

## 5. Managing Users

### 5.1 Viewing Users

The users page shows all registered users with:

| Column | Details |
|--------|---------|
| **ID** | User ID |
| **Name** | Full name |
| **Email** | Email address |
| **Role** | Badge: `admin` or `customer` |
| **Verified** | Badge: `Verified` or `Unverified` |
| **Orders** | Count of orders placed |
| **Spent** | Total amount spent (formatted) |
| **Joined** | Registration date |
| **Actions** | Verify/Unverify, Toggle Role, Delete |

[Screenshot: Users table showing user rows with role/verified badges]

### 5.2 Verifying / Unverifying Users

- **Verify:** Marks the user's email as verified.
- **Unverify:** Removes the verified status.

Useful for manually approving users who have trouble with the email verification process.

### 5.3 Toggling Admin Role

**Promoting a user to admin:**
1. Click **Make Admin**.
2. A confirmation email is sent to the target user with a link that expires in 30 minutes.
3. The user must click the confirmation link to complete the promotion.
4. If the email fails to send, the promotion is cancelled.

**Demoting an admin to customer:**
1. Click **Remove Admin**.
2. The role updates immediately.

> [!IMPORTANT]
> You cannot change your own role.

### 5.4 Deleting a User

1. Click **Delete**.
2. The request is verified and the user record is permanently deleted.

> [!IMPORTANT]
> User deletion does **not** cascade to orders, cart items, or wishlist items. Past orders become orphaned. You cannot delete your own account.

### 5.5 Searching Users

The search box filters **client-side** by ID, name, email, and role text content.

---

## 6. Reports & Analytics

The reports page provides 6 charts with a period selector for data visualization.

### 6.1 Period Filter

Select a time range from 10 options:

| Value | Range |
|-------|-------|
| `today` | Current day |
| `1h` | Last hour |
| `6h` | Last 6 hours |
| `12h` | Last 12 hours |
| `24h` | Last 24 hours |
| `7` | Last 7 days |
| `30` | Last 30 days (default) |
| `90` | Last 90 days |
| `365` | Last 365 days |
| `all` | All time (no filter) |

### 6.2 Summary Statistics

At the top of the page, 5 stat boxes show filtered totals:

- Total Revenue
- Total Orders
- Average Order Value
- Unique Customers
- Category Count

### 6.3 Charts

Six interactive charts:

| Chart | Type | What It Shows |
|-------|------|---------------|
| **Revenue Over Time** | Line | Daily revenue totals |
| **Revenue by Category** | Doughnut | Per-category item count and revenue |
| **Top Selling Products** | Horizontal Bar | Top 10 products by revenue |
| **Order Status** | Doughnut | Status breakdown (pending, processing, etc.) |
| **New User Registrations** | Line | Daily registration counts |
| **Payment Methods** | Doughnut | Method breakdown (Chapa, Bank Transfer, COD) |

[Screenshot: Reports page showing line chart, doughnut charts, and stat boxes]

### 6.4 Recent Orders List

Below the charts, a table shows the **20 most recent orders** with:

- Order ID (linked to order details)
- Customer name (first + last)
- Total and payment status
- Current order status and date

---

## 7. Navigation & Admin Layout

### 7.1 Admin Authentication

All admin pages verify the user's role at the top of each page. If the user is not logged in as admin, they are redirected to the homepage.

### 7.2 Navigation

Admin pages use the site's standard header for navigation. The header provides:

- Main site search bar
- Cart and wishlist links
- User dropdown menu
- **Admin-specific:** If the logged-in user has an admin role, the header displays unread notification count and the top 5 notifications

Admin pages also render contextual action buttons inline:
- Dashboard: stat card links
- Products: **+ New Product** button
- Reports: period filter tabs

### 7.3 Admin Page Structure

All admin pages follow this pattern:

1. Load system configuration.
2. Verify the user session and admin role.
3. Process any submitted form data.
4. Fetch and prepare data from the database.
5. Render the HTML page with header and footer.

Key files included on each page:
- System configuration (base path, currency helpers, database connection)
- Site header for navigation
- Site footer where applicable

---

## 8. Troubleshooting

### 8.1 "Access Denied" Redirect to Homepage

**Problem:** You're sent to the homepage when trying to access any admin page.

**Causes:**
- You're not logged in
- Your account role is `customer`, not `admin`
- Session expired

**Fix:**
- Ask an existing admin to promote your account (see [5.3 Toggling Admin Role](#53-toggling-admin-role))
- Log out and log back in

### 8.2 Product Image Not Showing

**Problem:** Product image shows a broken icon or "No img" placeholder.

**Causes:**
- The image file was deleted from the uploads directory but the database still references it
- The filename is stored as a full URL but the domain is unreachable

**Fix:**
- Edit the product and upload a new image
- Or use the Delete Media overlay to clear the broken reference

### 8.3 Cannot Delete a Category

**Problem:** Clicking Remove on a category shows an error.

**Cause:** One or more products are assigned to that category.

**Fix:**
- Go to Manage Products
- Reassign or delete all products in that category
- Try deleting the category again

### 8.4 Order Status Not Updating

**Problem:** The status dropdown doesn't save changes.

**Causes:**
- Invalid status value (must be one of: pending, processing, shipped, delivered, cancelled)
- Session timed out
- Form submission interrupted

**Fix:**
- Refresh the page and log in again
- Use only the provided dropdown options

### 8.5 Cannot Delete or Demote Own Account

**Problem:** You get an error when trying to delete your own user record or demote yourself from admin.

**Cause:** Self-protection logic prevents modifying your own account.

**Fix:** Ask another admin to perform this action.

### 8.6 Upload Failed: File Too Large

**Problem:** Image or video upload fails.

| Media Type | Max Size |
|-----------|----------|
| Product image | 5 MB |
| Product video | 50 MB |
| Category image | No explicit limit |

**Fix:** Compress the file to fit within limits, or adjust the server's `upload_max_filesize` / `post_max_size` settings.

### 8.7 Database Error on Admin Pages

**Problem:** A "database error" message appears instead of data.

**Causes:**
- Configuration file missing or misconfigured
- Database server not running
- Database credentials changed

**Fix:**
- Verify the configuration file has correct database credentials
- Check database service status
- See DEPLOYMENT_GUIDE.md for database setup instructions
