# APPENDICES

## APPENDIX A: DATABASE SCHEMA (SQL)

### A.1 Complete Database Creation Script

```sql
-- Create Database
CREATE DATABASE IF NOT EXISTS smartmall_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE smartmall_db;

-- Table 1: users
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 2: categories
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 3: products
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category_id INT NOT NULL,
    image VARCHAR(255),
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_price (price),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 4: cart
CREATE TABLE cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 5: orders
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    shipping_name VARCHAR(100) NOT NULL,
    shipping_email VARCHAR(100) NOT NULL,
    shipping_phone VARCHAR(20) NOT NULL,
    shipping_address TEXT NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 6: order_items
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    INDEX idx_order (order_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 7: payments
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    transaction_id VARCHAR(100) UNIQUE NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'chapa',
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    payment_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    INDEX idx_order (order_id),
    INDEX idx_transaction (transaction_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 8: password_resets
CREATE TABLE password_resets (
    reset_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### A.2 Sample Data Insertion

```sql
-- Insert Categories
INSERT INTO categories (name, description, image) VALUES
('Fashion & Apparel', 'Clothing, shoes, and accessories', 'fashion.jpg'),
('Electronics & Gadgets', 'Phones, laptops, and tech accessories', 'electronics.jpg'),
('Home & Living', 'Furniture, decor, and home essentials', 'home.jpg'),
('Beauty & Health', 'Cosmetics, skincare, and wellness products', 'beauty.jpg');

-- Insert Sample Products (showing first 10)
INSERT INTO products (name, description, price, category_id, image, stock) VALUES
('Blue & Black Check Shirt', 'Comfortable cotton check shirt', 29.99, 1, 'shirt1.jpg', 50),
('Gigabyte Aorus Men Tshirt', 'Gaming-themed t-shirt', 24.99, 1, 'tshirt1.jpg', 100),
('Man Plaid Shirt', 'Classic plaid pattern shirt', 34.99, 1, 'shirt2.jpg', 75),
('Colorful Stylish Shirt', 'Vibrant multi-color shirt', 39.99, 1, 'shirt3.jpg', 60),
('Plain Polo Shirt', 'Simple and elegant polo', 27.99, 1, 'polo1.jpg', 80),
('Wireless Bluetooth Headphones', 'High-quality sound headphones', 79.99, 2, 'headphones1.jpg', 40),
('Smart Watch', 'Fitness tracking smartwatch', 149.99, 2, 'watch1.jpg', 30),
('Laptop Stand', 'Ergonomic laptop stand', 45.99, 3, 'stand1.jpg', 50),
('LED Desk Lamp', 'Adjustable LED lamp', 35.99, 3, 'lamp1.jpg', 70),
('Face Cream', 'Moisturizing face cream', 19.99, 4, 'cream1.jpg', 100);

-- Insert Admin User (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@smartmall.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
```

## APPENDIX B: API DOCUMENTATION

### B.1 Authentication API

**Endpoint:** `/api/auth.php`

**1. User Registration**
```
POST /api/auth.php?action=register

Request Body:
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "phone": "0912345678"
}

Response (Success):
{
    "success": true,
    "message": "Registration successful",
    "user_id": 1
}

Response (Error):
{
    "success": false,
    "message": "Email already exists"
}
```

**2. User Login**
```
POST /api/auth.php?action=login

Request Body:
{
    "email": "john@example.com",
    "password": "password123"
}

Response (Success):
{
    "success": true,
    "message": "Login successful",
    "token": "abc123xyz789",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    }
}

Response (Error):
{
    "success": false,
    "message": "Invalid credentials"
}
```

### B.2 Products API

**Endpoint:** `/api/products.php`

**1. Get All Products**
```
GET /api/products.php

Response:
{
    "success": true,
    "products": [
        {
            "id": 1,
            "name": "Blue & Black Check Shirt",
            "description": "Comfortable cotton check shirt",
            "price": "29.99",
            "category_id": 1,
            "category_name": "Fashion & Apparel",
            "image": "shirt1.jpg",
            "stock": 50
        },
        ...
    ]
}
```

**2. Get Product by ID**
```
GET /api/products.php?id=1

Response:
{
    "success": true,
    "product": {
        "id": 1,
        "name": "Blue & Black Check Shirt",
        "description": "Comfortable cotton check shirt",
        "price": "29.99",
        "category_id": 1,
        "category_name": "Fashion & Apparel",
        "image": "shirt1.jpg",
        "stock": 50
    }
}
```

**3. Filter by Category**
```
GET /api/products.php?category_id=1

Response:
{
    "success": true,
    "products": [...]
}
```

### B.3 Orders API

**Endpoint:** `/api/orders.php`

**1. Create Order**
```
POST /api/orders.php?action=create
Headers: Authorization: Bearer {token}

Request Body:
{
    "shipping_name": "John Doe",
    "shipping_email": "john@example.com",
    "shipping_phone": "0912345678",
    "shipping_address": "123 Main St, Addis Ababa",
    "items": [
        {
            "product_id": 1,
            "quantity": 2,
            "price": 29.99
        }
    ],
    "total_amount": 59.98
}

Response:
{
    "success": true,
    "message": "Order created successfully",
    "order_id": 1,
    "order_number": "ORD-20240525-001"
}
```

**2. Get User Orders**
```
GET /api/orders.php
Headers: Authorization: Bearer {token}

Response:
{
    "success": true,
    "orders": [
        {
            "id": 1,
            "order_number": "ORD-20240525-001",
            "total_amount": "59.98",
            "status": "completed",
            "payment_status": "completed",
            "created_at": "2024-05-25 10:30:00",
            "items": [
                {
                    "product_name": "Blue & Black Check Shirt",
                    "quantity": 2,
                    "price": "29.99",
                    "subtotal": "59.98"
                }
            ]
        }
    ]
}
```

### B.4 Categories API

**Endpoint:** `/api/categories.php`

**Get All Categories**
```
GET /api/categories.php

Response:
{
    "success": true,
    "categories": [
        {
            "id": 1,
            "name": "Fashion & Apparel",
            "description": "Clothing, shoes, and accessories",
            "image": "fashion.jpg"
        },
        ...
    ]
}
```

### B.5 Profile API

**Endpoint:** `/api/profile.php`

**Get User Profile**
```
GET /api/profile.php
Headers: Authorization: Bearer {token}

Response:
{
    "success": true,
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "0912345678",
        "address": "123 Main St"
    }
}
```

## APPENDIX C: TESTING EVIDENCE

### C.1 Unit Test Results

**Test Suite:** Backend Functions
**Date:** May 25, 2024
**Total Tests:** 15
**Passed:** 15
**Failed:** 0
**Pass Rate:** 100%

**Test Cases:**
1. ✅ Database connection successful
2. ✅ Password hashing working
3. ✅ Password verification working
4. ✅ Email validation working
5. ✅ Input sanitization working
6. ✅ Session creation working
7. ✅ Session destruction working
8. ✅ SQL prepared statement working
9. ✅ CSRF token generation working
10. ✅ CSRF token validation working
11. ✅ Cart total calculation correct
12. ✅ Order number generation unique
13. ✅ Price formatting correct
14. ✅ Stock validation working
15. ✅ API token validation working

### C.2 Integration Test Results

**Test Suite:** End-to-End Workflows
**Date:** May 25, 2024
**Total Tests:** 10
**Passed:** 10
**Failed:** 0
**Pass Rate:** 100%

**Test Cases:**
1. ✅ Complete registration → login flow
2. ✅ Browse → add to cart → checkout flow
3. ✅ Order creation → payment → confirmation flow
4. ✅ Admin login → product management flow
5. ✅ Admin order management flow
6. ✅ Mobile app authentication flow
7. ✅ Mobile app shopping flow
8. ✅ API authentication flow
9. ✅ Payment webhook handling
10. ✅ Session persistence across pages

### C.3 Security Test Results

**Test Suite:** Security Vulnerabilities
**Date:** May 25, 2024
**Total Tests:** 8
**Passed:** 8
**Failed:** 0
**Pass Rate:** 100%

**Test Cases:**
1. ✅ SQL injection blocked (login form)
2. ✅ SQL injection blocked (search box)
3. ✅ XSS attack blocked (product name)
4. ✅ XSS attack blocked (user input)
5. ✅ CSRF attack blocked (form submission)
6. ✅ Session hijacking prevented
7. ✅ Password stored securely (hashed)
8. ✅ API authentication enforced

### C.4 Performance Test Results

**Test Configuration:**
- Tool: Apache JMeter
- Concurrent Users: 50
- Test Duration: 10 minutes
- Total Requests: 5,000

**Results:**
- Average Response Time: 320ms ✅
- 95th Percentile: 580ms ✅
- 99th Percentile: 890ms ✅
- Error Rate: 0% ✅
- Throughput: 8.3 requests/second ✅

**Page Load Times:**
- Home Page: 1.8s ✅
- Product Listing: 2.1s ✅
- Product Details: 1.5s ✅
- Cart Page: 1.3s ✅
- Checkout Page: 1.7s ✅

## APPENDIX D: USER MANUAL

### D.1 Customer User Manual

**1. Registration**
- Navigate to http://localhost/reference/register.php
- Fill in name, email, password, phone
- Click "Register" button
- You will be redirected to login page

**2. Login**
- Navigate to http://localhost/reference/login.php
- Enter email and password
- Click "Login" button
- You will be redirected to home page

**3. Browse Products**
- Click "Products" in navigation menu
- View all 131 products
- Use category filter to narrow results
- Use search box to find specific products

**4. Add to Cart**
- Click on product to view details
- Click "Add to Cart" button
- Cart icon will show item count
- Click cart icon to view cart

**5. Checkout**
- Review items in cart
- Click "Proceed to Checkout"
- Fill in shipping information
- Click "Place Order"
- You will be redirected to payment

**6. Payment**
- Review order summary
- Click "Pay with Chapa"
- Complete payment on Chapa page
- Return to confirmation page

**7. View Orders**
- Click "Orders" in navigation menu
- View all your orders
- Check order status and payment status

### D.2 Admin User Manual

**1. Admin Login**
- Navigate to http://localhost/reference/admin/
- Enter admin email and password
- Click "Login" button
- You will see admin dashboard

**2. Manage Products**
- Click "Manage Products" in sidebar
- View all products in table
- Click "Add Product" to create new product
- Click "Edit" to modify existing product
- Click "Delete" to remove product

**3. Add Product**
- Click "Add Product" button
- Fill in product details (name, description, price, category, stock)
- Upload product image
- Click "Add Product" button
- Product will appear in catalog

**4. Edit Product**
- Click "Edit" button next to product
- Modify product details
- Upload new image (optional)
- Click "Update Product" button
- Changes will be saved

**5. Manage Orders**
- Click "Manage Orders" in sidebar
- View all customer orders
- Check order details and items
- Update order status (pending, processing, completed, cancelled)
- View payment status

**6. Update Order Status**
- Find order in list
- Select new status from dropdown
- Click "Update Status" button
- Customer will see updated status

### D.3 Mobile App User Manual

**1. Install App**
- Download APK file
- Enable "Install from Unknown Sources"
- Tap APK file to install
- Open Smart Mall app

**2. Login/Register**
- Open app
- Tap "Login" or "Register"
- Enter credentials
- Tap "Submit" button

**3. Browse Products**
- Scroll through home screen
- Tap category to filter
- Use search bar to find products
- Tap product to view details

**4. Add to Cart**
- View product details
- Tap "Add to Cart" button
- Cart icon shows item count
- Tap cart icon to view cart

**5. Checkout**
- Review cart items
- Tap "Checkout" button
- Fill in shipping information
- Tap "Place Order" button

**6. Payment**
- Review order summary
- Tap "Pay Now" button
- Complete payment
- View confirmation screen

**7. View Orders**
- Tap "Orders" in bottom navigation
- View order history
- Tap order to view details
- Check order and payment status

---

**End of Appendices**
