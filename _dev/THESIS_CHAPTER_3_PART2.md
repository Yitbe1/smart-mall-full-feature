# CHAPTER 3: SYSTEM DESIGN (Part 2)

## 3.2.9 Mobile Home Screen

### Figure 3.11: Mobile Home Screen

**Figure 3.11 Description:** The mobile home screen provides optimized shopping experience for Android devices. The screen features:

**Top App Bar:**
- Smart Mall logo (32px height) with icon
- App title "Smart Mall"
- Profile icon button
- Shopping cart icon with badge showing item count

**Search and Filter Section:**
- Full-width search bar with search icon
- Placeholder text "Search products..."
- Clear button (X) when text is entered
- Filter button (tune icon) with blue background

**Hero Section:**
- Gradient background (blue to dark blue)
- "Welcome to Smart Mall" heading (36px, white, bold)
- Subtitle text about product discovery
- "Shop Now" button (white background, blue text)
- Rounded corners (24px radius)
- Shadow effect for depth

**Category Section:**
- Horizontal scrollable category cards
- Each card: 100px width, icon + text
- Icons: checkroom (Fashion), devices (Electronics), home (Home), spa (Beauty)
- Selected category: gradient background + shadow
- Unselected: white background with border

**Product Grid:**
- 2-column grid layout
- Product cards with:
  - Product image (covers full width)
  - Category badge (top-left, blue background)
  - Wishlist icon (top-right, white circle)
  - Product name (2 lines max, truncated)
  - Price (18px, bold, blue color)
  - Arrow icon (bottom-right)
- Card animations: scale on tap, shadow on hover
- Spacing: 12px between cards

**Bottom Navigation (Future):**
- Home, Categories, Cart, Profile tabs

## 3.2.10 Mobile Product Screen

### Figure 3.12: Mobile Product Detail Screen

**Figure 3.12 Description:** The product detail screen shows comprehensive product information:

**Image Section:**
- Full-width product image
- Swipeable image gallery for multiple images
- Image indicators (dots) showing current image
- Pinch-to-zoom capability
- Back button (top-left)
- Share button (top-right)

**Product Information:**
- Product name (24px, bold)
- Category badge (blue, rounded)
- Star rating display (if available)
- Price (28px, bold, blue)
- Stock status indicator:
  - Green "In Stock" if available
  - Red "Out of Stock" if unavailable

**Quantity Selector:**
- Minus button (disabled if quantity = 1)
- Quantity display (center, bold)
- Plus button (disabled if quantity = stock)
- Rounded border, touch-friendly size (48px)

**Action Buttons:**
- "Add to Cart" button (full width, blue, 56px height)
- "Buy Now" button (optional, outlined)
- Loading state with spinner during API call

**Description Section:**
- "Description" heading
- Expandable text content
- "Read more" / "Read less" toggle
- Formatted text with proper spacing

**Specifications (if available):**
- Table format with key-value pairs
- Alternating row colors for readability

**Related Products:**
- Horizontal scrollable list
- Smaller product cards
- "View All" button

## 3.2.11 Mobile Cart Screen

### Figure 3.13: Mobile Shopping Cart Screen

**Figure 3.13 Description:** The cart screen manages shopping cart items:

**App Bar:**
- "Shopping Cart" title
- Back button
- Item count in subtitle

**Cart Items List:**
Each item card shows:
- Product thumbnail (80x80px, rounded corners)
- Product name and category
- Unit price
- Quantity controls:
  - Minus button (circle, border)
  - Quantity display
  - Plus button (circle, border)
- Item total (quantity × price)
- Remove button (trash icon, red)

**Item Card Layout:**
- White background
- 16px padding
- 12px margin between items
- Rounded corners (12px)
- Subtle shadow

**Empty Cart State:**
- Shopping bag icon (80px, gray)
- "Your cart is empty" message
- "Start shopping to add items" subtitle
- "Browse Products" button (blue)

**Cart Summary (Bottom Sheet):**
- Subtotal row
- Shipping row (if applicable)
- Tax row (if applicable)
- Divider line
- Total row (bold, larger font)
- "Proceed to Checkout" button (full width, blue, 56px)

**Pull-to-Refresh:**
- Swipe down to refresh cart from server
- Loading indicator during refresh

## 3.2.12 Mobile Checkout Screen

### Figure 3.14: Mobile Checkout Screen

**Figure 3.14 Description:** The checkout screen collects shipping information:

**Progress Indicator:**
- Step 1: Cart (completed, green check)
- Step 2: Shipping (current, blue)
- Step 3: Payment (pending, gray)
- Step 4: Confirmation (pending, gray)

**Shipping Form:**
- Full Name field (text input, required)
- Email field (email input, validation)
- Phone Number field (tel input, format validation)
- Address field (textarea, 3 rows)
- City field (text input)
- Postal Code field (text input)
- All fields with proper labels and icons

**Form Validation:**
- Real-time validation on blur
- Error messages below fields (red text)
- Required field indicators (asterisk)
- Valid field indicators (green check)

**Order Summary Card:**
- Collapsible section
- Item count and total
- "View Details" to expand
- Shows all cart items when expanded

**Saved Addresses (if logged in):**
- List of saved addresses
- Radio button selection
- "Use this address" quick select
- "Add new address" option

**Action Buttons:**
- "Back to Cart" (outlined, gray)
- "Continue to Payment" (filled, blue)
- Buttons fixed at bottom for easy access

## 3.2.13 Mobile Payment Screen

### Figure 3.15: Mobile Payment Screen

**Figure 3.15 Description:** The payment screen handles transaction processing:

**Payment Method Selection:**
- Chapa Payment (default, selected)
- Payment method logo
- "Secure Payment" badge
- SSL encryption indicator

**Order Summary:**
- Order number (generated)
- Order date and time
- Shipping address (read-only)
- Item list with quantities
- Subtotal, shipping, tax
- Grand total (bold, large)

**Payment Processing States:**

**1. Ready State:**
- "Review and Pay" button
- Payment amount displayed
- Security badges visible

**2. Processing State:**
- Loading spinner
- "Processing your payment..." message
- "Please wait" subtitle
- Disabled back button

**3. Success State:**
- Green checkmark icon (large)
- "Payment Successful!" message
- Order number display
- "Order placed successfully" subtitle
- "View Order" button
- "Continue Shopping" button

**4. Failed State:**
- Red error icon
- "Payment Failed" message
- Error description
- "Try Again" button
- "Contact Support" link
- "Back to Cart" option

**Security Indicators:**
- Lock icon
- "Secure Payment" text
- SSL certificate badge
- Chapa logo and trust badges

## 3.3 Navigation Flow Diagram

### Figure 3.16: Complete Navigation Flow

```
                    ┌─────────────┐
                    │  App Start  │
                    └──────┬──────┘
                           │
                           ▼
                    ┌─────────────┐
                    │ Home Screen │
                    └──────┬──────┘
                           │
            ┌──────────────┼──────────────┐
            │              │              │
            ▼              ▼              ▼
    ┌──────────────┐ ┌──────────┐ ┌──────────┐
    │   Search     │ │ Category │ │ Profile  │
    │   Products   │ │  Filter  │ │  Menu    │
    └──────┬───────┘ └─────┬────┘ └────┬─────┘
           │               │            │
           └───────┬───────┘            │
                   │                    │
                   ▼                    ▼
            ┌─────────────┐      ┌──────────┐
            │   Product   │      │  Login/  │
            │   Detail    │      │ Register │
            └──────┬──────┘      └────┬─────┘
                   │                  │
                   ▼                  │
            ┌─────────────┐           │
            │ Add to Cart │           │
            └──────┬──────┘           │
                   │                  │
                   ▼                  │
            ┌─────────────┐           │
            │ Cart Screen │◄──────────┘
            └──────┬──────┘
                   │
                   ▼
            ┌─────────────┐
            │  Checkout   │
            └──────┬──────┘
                   │
                   ▼
            ┌─────────────┐
            │   Payment   │
            └──────┬──────┘
                   │
            ┌──────┴──────┐
            │             │
            ▼             ▼
    ┌──────────┐   ┌──────────┐
    │ Success  │   │  Failed  │
    │  Order   │   │  Retry   │
    └────┬─────┘   └────┬─────┘
         │              │
         ▼              │
    ┌──────────┐        │
    │  Order   │        │
    │ History  │        │
    └──────────┘        │
         │              │
         └──────┬───────┘
                │
                ▼
         ┌─────────────┐
         │ Home Screen │
         └─────────────┘
```

**Figure 3.16 Description:** The navigation flow diagram illustrates the complete user journey through the mobile application. Users start at the home screen and can navigate to product browsing, search, or profile. The shopping flow proceeds from product selection through cart, checkout, and payment to order confirmation. Failed payments allow retry, while successful orders lead to order history. All paths eventually return to the home screen for continued shopping.

## 3.4 Database Design

The Smart Mall system uses MySQL relational database with 8 tables managing all system data.

### 3.4.1 Database Tables

### Table 3.1: Database Tables Overview

| Table Name | Purpose | Record Count |
|------------|---------|--------------|
| users | Store customer and admin accounts | Variable |
| products | Store product catalog | 131 |
| categories | Store product categories | 4 |
| orders | Store customer orders | Variable |
| order_items | Store order line items | Variable |
| payments | Store payment transactions | Variable |
| cart | Store shopping cart items | Variable |
| password_resets | Store password reset tokens | Variable |

### Table 3.2: users Table Schema

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique user identifier |
| name | VARCHAR(255) | NOT NULL | User full name |
| email | VARCHAR(255) | UNIQUE, NOT NULL | User email address |
| password | VARCHAR(255) | NOT NULL | Hashed password (bcrypt) |
| phone | VARCHAR(50) | NULL | User phone number |
| address | TEXT | NULL | User shipping address |
| token | VARCHAR(255) | NULL | Authentication token for API |
| role | ENUM('user','admin') | DEFAULT 'user' | User role |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Account creation date |

### Table 3.3: products Table Schema

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique product identifier |
| name | VARCHAR(255) | NOT NULL | Product name |
| slug | VARCHAR(255) | UNIQUE, NOT NULL | URL-friendly product name |
| description | TEXT | NULL | Product description |
| price | DECIMAL(10,2) | NOT NULL | Product price |
| stock | INT | DEFAULT 0 | Available quantity |
| category_id | INT | FOREIGN KEY | References categories(id) |
| image | VARCHAR(500) | NULL | Product image URL |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Product creation date |

**Sample Products:**
- Blue & Black Check Shirt - $29.99
- Gigabyte Aorus Men Tshirt - $24.99
- Man Plaid Shirt - $34.99
- Man Short Sleeve Shirt - $19.99
- Men Check Shirt - $27.99

### Table 3.4: categories Table Schema

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique category identifier |
| name | VARCHAR(100) | NOT NULL | Category name |
| slug | VARCHAR(100) | UNIQUE, NOT NULL | URL-friendly category name |
| description | TEXT | NULL | Category description |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Category creation date |

**Actual Categories:**
1. Fashion & Apparel (slug: fashion)
2. Electronics & Gadgets (slug: electronics)
3. Home & Living (slug: home)
4. Beauty & Health (slug: beauty)

### Table 3.5: orders Table Schema

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique order identifier |
| user_id | INT | FOREIGN KEY, NOT NULL | References users(id) |
| order_number | VARCHAR(50) | UNIQUE, NOT NULL | Order tracking number |
| total | DECIMAL(10,2) | NOT NULL | Order total amount |
| status | ENUM | DEFAULT 'pending' | Order status |
| shipping_address | TEXT | NULL | Delivery address |
| payment_method | VARCHAR(50) | NULL | Payment method used |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Order creation date |

**Status Values:** pending, processing, completed, cancelled

### Table 3.6: order_items Table Schema

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique item identifier |
| order_id | INT | FOREIGN KEY, NOT NULL | References orders(id) |
| product_id | INT | NOT NULL | Product identifier |
| product_name | VARCHAR(255) | NOT NULL | Product name snapshot |
| quantity | INT | NOT NULL | Quantity ordered |
| price | DECIMAL(10,2) | NOT NULL | Price at time of order |

### Table 3.7: payments Table Schema

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique payment identifier |
| order_id | INT | FOREIGN KEY, NOT NULL | References orders(id) |
| transaction_id | VARCHAR(255) | UNIQUE | Payment gateway transaction ID |
| amount | DECIMAL(10,2) | NOT NULL | Payment amount |
| status | VARCHAR(50) | NOT NULL | Payment status |
| payment_method | VARCHAR(50) | NULL | Payment method |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Payment date |

### Table 3.8: cart Table Schema

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique cart item identifier |
| user_id | INT | FOREIGN KEY | References users(id) |
| product_id | INT | FOREIGN KEY, NOT NULL | References products(id) |
| quantity | INT | NOT NULL | Quantity in cart |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Added to cart date |

### Table 3.9: password_resets Table Schema

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique reset identifier |
| email | VARCHAR(255) | NOT NULL | User email |
| token | VARCHAR(255) | NOT NULL | Reset token |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Token creation date |

### 3.4.2 Table Relationships

**One-to-Many Relationships:**
- users → orders (One user can have many orders)
- users → cart (One user can have many cart items)
- categories → products (One category contains many products)
- orders → order_items (One order contains many items)
- orders → payments (One order can have multiple payment attempts)

**Foreign Key Constraints:**
- products.category_id → categories.id (ON DELETE RESTRICT)
- orders.user_id → users.id (ON DELETE RESTRICT)
- order_items.order_id → orders.id (ON DELETE CASCADE)
- cart.user_id → users.id (ON DELETE CASCADE)
- cart.product_id → products.id (ON DELETE CASCADE)

**Indexes:**
- PRIMARY KEY indexes on all id columns
- UNIQUE indexes on email, slug, order_number, transaction_id
- INDEX on category_id, user_id, product_id for faster queries
- INDEX on created_at for date-based queries

---

**Continue to Chapter 3 Part 3...**
