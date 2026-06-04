# CHAPTER 3: SYSTEM DESIGN (Part 3)

## 3.5 ER Diagram

### Figure 3.17: Entity Relationship Diagram

```
┌──────────────────┐
│     USERS        │
│                  │
│ PK: id           │
│    name          │
│    email (UK)    │
│    password      │
│    phone         │
│    address       │
│    token         │
│    role          │
│    created_at    │
└────────┬─────────┘
         │ 1
         │
         │ N
         ▼
┌──────────────────┐         ┌──────────────────┐
│     ORDERS       │         │   CATEGORIES     │
│                  │         │                  │
│ PK: id           │         │ PK: id           │
│ FK: user_id      │         │    name          │
│    order_number  │         │    slug (UK)     │
│    total         │         │    description   │
│    status        │         │    created_at    │
│    shipping_addr │         └────────┬─────────┘
│    payment_method│                  │ 1
│    created_at    │                  │
└────────┬─────────┘                  │ N
         │ 1                          │
         │                            ▼
         │ N                 ┌──────────────────┐
         ▼                   │    PRODUCTS      │
┌──────────────────┐         │                  │
│  ORDER_ITEMS     │         │ PK: id           │
│                  │         │ FK: category_id  │
│ PK: id           │         │    name          │
│ FK: order_id     │         │    slug (UK)     │
│    product_id    │         │    description   │
│    product_name  │         │    price         │
│    quantity      │         │    stock         │
│    price         │         │    image         │
└──────────────────┘         │    created_at    │
                             └────────┬─────────┘
┌──────────────────┐                 │
│    PAYMENTS      │                 │
│                  │                 │
│ PK: id           │         ┌───────┴─────────┐
│ FK: order_id     │         │                 │
│    transaction_id│         │ 1               │ N
│    amount        │         │                 │
│    status        │         ▼                 ▼
│    payment_method│  ┌──────────────┐  ┌──────────────┐
│    created_at    │  │    CART      │  │ USERS (FK)   │
└──────────────────┘  │              │  └──────────────┘
                      │ PK: id       │
┌──────────────────┐  │ FK: user_id  │
│ PASSWORD_RESETS  │  │ FK: product_id│
│                  │  │    quantity  │
│ PK: id           │  │    created_at│
│    email         │  └──────────────┘
│    token         │
│    created_at    │
└──────────────────┘

LEGEND:
PK = Primary Key
FK = Foreign Key
UK = Unique Key
1:N = One-to-Many Relationship
```

**Figure 3.17 Description:** The Entity Relationship Diagram shows the complete database structure with all 8 tables and their relationships. Users can place multiple orders, each order contains multiple order items, and each order can have payment records. Products belong to categories, and users can have multiple items in their cart. The diagram illustrates primary keys, foreign keys, and cardinality of relationships.

## 3.6 Database Schema Diagram

### Figure 3.18: Detailed Database Schema

```
┌─────────────────────────────────────────────────────────────────┐
│                        SMART MALL DATABASE                      │
│                         (smartmall_db)                          │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│ users                                                            │
├──────────────────────────────────────────────────────────────────┤
│ id              INT             PRIMARY KEY AUTO_INCREMENT       │
│ name            VARCHAR(255)    NOT NULL                         │
│ email           VARCHAR(255)    UNIQUE NOT NULL                  │
│ password        VARCHAR(255)    NOT NULL (bcrypt hashed)         │
│ phone           VARCHAR(50)     NULL                             │
│ address         TEXT            NULL                             │
│ token           VARCHAR(255)    NULL (API authentication)        │
│ role            ENUM            DEFAULT 'user' ('user','admin')  │
│ created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP        │
└──────────────────────────────────────────────────────────────────┘
                    │
                    │ 1:N
                    ▼
┌──────────────────────────────────────────────────────────────────┐
│ orders                                                           │
├──────────────────────────────────────────────────────────────────┤
│ id              INT             PRIMARY KEY AUTO_INCREMENT       │
│ user_id         INT             FOREIGN KEY → users(id)          │
│ order_number    VARCHAR(50)     UNIQUE NOT NULL (ORD-XXXXXXXX)  │
│ total           DECIMAL(10,2)   NOT NULL                         │
│ status          ENUM            DEFAULT 'pending'                │
│                                 ('pending','processing',         │
│                                  'completed','cancelled')        │
│ shipping_address TEXT           NULL                             │
│ payment_method  VARCHAR(50)     NULL                             │
│ created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP        │
└──────────────────────────────────────────────────────────────────┘
                    │
                    │ 1:N
                    ▼
┌──────────────────────────────────────────────────────────────────┐
│ order_items                                                      │
├──────────────────────────────────────────────────────────────────┤
│ id              INT             PRIMARY KEY AUTO_INCREMENT       │
│ order_id        INT             FOREIGN KEY → orders(id)         │
│                                 ON DELETE CASCADE                │
│ product_id      INT             NOT NULL                         │
│ product_name    VARCHAR(255)    NOT NULL (snapshot)              │
│ quantity        INT             NOT NULL                         │
│ price           DECIMAL(10,2)   NOT NULL (snapshot)              │
└──────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│ categories                                                       │
├──────────────────────────────────────────────────────────────────┤
│ id              INT             PRIMARY KEY AUTO_INCREMENT       │
│ name            VARCHAR(100)    NOT NULL                         │
│ slug            VARCHAR(100)    UNIQUE NOT NULL                  │
│ description     TEXT            NULL                             │
│ created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP        │
│                                                                  │
│ ACTUAL DATA:                                                     │
│ 1. Fashion & Apparel (fashion)                                  │
│ 2. Electronics & Gadgets (electronics)                          │
│ 3. Home & Living (home)                                         │
│ 4. Beauty & Health (beauty)                                     │
└──────────────────────────────────────────────────────────────────┘
                    │
                    │ 1:N
                    ▼
┌──────────────────────────────────────────────────────────────────┐
│ products                                                         │
├──────────────────────────────────────────────────────────────────┤
│ id              INT             PRIMARY KEY AUTO_INCREMENT       │
│ category_id     INT             FOREIGN KEY → categories(id)     │
│ name            VARCHAR(255)    NOT NULL                         │
│ slug            VARCHAR(255)    UNIQUE NOT NULL                  │
│ description     TEXT            NULL                             │
│ price           DECIMAL(10,2)   NOT NULL                         │
│ stock           INT             DEFAULT 0                        │
│ image           VARCHAR(500)    NULL (Unsplash URLs)             │
│ created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP        │
│                                                                  │
│ ACTUAL DATA: 131 products                                       │
│ Sample: Blue & Black Check Shirt ($29.99)                       │
└──────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│ payments                                                         │
├──────────────────────────────────────────────────────────────────┤
│ id              INT             PRIMARY KEY AUTO_INCREMENT       │
│ order_id        INT             FOREIGN KEY → orders(id)         │
│ transaction_id  VARCHAR(255)    UNIQUE (Chapa transaction ID)   │
│ amount          DECIMAL(10,2)   NOT NULL                         │
│ status          VARCHAR(50)     NOT NULL                         │
│ payment_method  VARCHAR(50)     NULL                             │
│ created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP        │
└──────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│ cart                                                             │
├──────────────────────────────────────────────────────────────────┤
│ id              INT             PRIMARY KEY AUTO_INCREMENT       │
│ user_id         INT             FOREIGN KEY → users(id)          │
│                                 ON DELETE CASCADE                │
│ product_id      INT             FOREIGN KEY → products(id)       │
│                                 ON DELETE CASCADE                │
│ quantity        INT             NOT NULL                         │
│ created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP        │
└──────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│ password_resets                                                  │
├──────────────────────────────────────────────────────────────────┤
│ id              INT             PRIMARY KEY AUTO_INCREMENT       │
│ email           VARCHAR(255)    NOT NULL                         │
│ token           VARCHAR(255)    NOT NULL                         │
│ created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP        │
└──────────────────────────────────────────────────────────────────┘
```

**Figure 3.18 Description:** The database schema diagram provides detailed information about each table including column names, data types, constraints, and relationships. The diagram shows actual data types used in MySQL, foreign key relationships with cascade rules, and includes notes about actual data stored (131 products, 4 categories). This schema supports all system functionality including user management, product catalog, shopping cart, order processing, and payment tracking.

## 3.7 API/Backend Design

The Smart Mall backend provides RESTful API endpoints for mobile app communication and web application functionality.

### Table 3.10: API Endpoints Summary

| Endpoint | Method | Purpose | Authentication |
|----------|--------|---------|----------------|
| /api/auth.php | POST | Login/Register | No |
| /api/products.php | GET | List products | No |
| /api/categories.php | GET | List categories | No |
| /api/orders.php | GET | Get user orders | Required |
| /api/orders.php | POST | Create order | Required |
| /api/profile.php | GET | Get user profile | Required |
| /api/profile.php | PUT | Update profile | Required |
| /api/search.php | GET | Search products | No |

### 3.7.1 Authentication API

**Endpoint:** `/api/auth.php`  
**Method:** POST  
**Purpose:** User authentication and registration

**Login Request:**
```json
{
  "action": "login",
  "email": "user@example.com",
  "password": "password123"
}
```

**Login Response (Success):**
```json
{
  "success": true,
  "token": "abc123def456...",
  "name": "John Doe",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role": "user"
  }
}
```

**Register Request:**
```json
{
  "action": "register",
  "name": "John Doe",
  "email": "user@example.com",
  "password": "password123"
}
```

**Register Response (Success):**
```json
{
  "success": true,
  "token": "abc123def456...",
  "name": "John Doe",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com"
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

**Implementation Details:**
- Password hashing using `password_hash()` with bcrypt
- Token generation using `bin2hex(random_bytes(32))`
- Email validation and duplicate checking
- SQL injection prevention with prepared statements

### 3.7.2 Product API

**Endpoint:** `/api/products.php`  
**Method:** GET  
**Purpose:** Retrieve product catalog

**Request Parameters:**
- `category` (optional): Filter by category slug
- `q` (optional): Search query
- `id` (optional): Get single product

**Response (List):**
```json
[
  {
    "id": 1,
    "name": "Blue & Black Check Shirt",
    "slug": "blue-black-check-shirt",
    "description": "Stylish check pattern shirt",
    "price": 29.99,
    "stock": 50,
    "category_id": 1,
    "category_name": "Fashion & Apparel",
    "image": "https://images.unsplash.com/..."
  },
  {
    "id": 2,
    "name": "Gigabyte Aorus Men Tshirt",
    "slug": "gigabyte-aorus-tshirt",
    "description": "Gaming branded t-shirt",
    "price": 24.99,
    "stock": 30,
    "category_id": 1,
    "category_name": "Fashion & Apparel",
    "image": "https://images.unsplash.com/..."
  }
]
```

**Response (Single Product):**
```json
{
  "id": 1,
  "name": "Blue & Black Check Shirt",
  "slug": "blue-black-check-shirt",
  "description": "Stylish check pattern shirt for casual wear",
  "price": 29.99,
  "stock": 50,
  "category_id": 1,
  "category_name": "Fashion & Apparel",
  "image": "https://images.unsplash.com/..."
}
```

**Implementation:**
- JOIN with categories table for category names
- WHERE clause for filtering
- LIKE clause for search
- Returns empty array if no results

### 3.7.3 Cart API

**Endpoint:** `/api/cart.php`  
**Method:** POST  
**Purpose:** Manage shopping cart

**Add to Cart Request:**
```json
{
  "action": "add",
  "product_id": 1,
  "quantity": 2
}
```

**Update Cart Request:**
```json
{
  "action": "update",
  "cart_id": 1,
  "quantity": 3
}
```

**Remove from Cart Request:**
```json
{
  "action": "remove",
  "cart_id": 1
}
```

**Get Cart Request:**
```json
{
  "action": "get"
}
```

**Response:**
```json
{
  "success": true,
  "cart": [
    {
      "id": 1,
      "product_id": 1,
      "product_name": "Blue & Black Check Shirt",
      "price": 29.99,
      "quantity": 2,
      "total": 59.98,
      "image": "https://images.unsplash.com/..."
    }
  ],
  "cart_total": 59.98
}
```

### 3.7.4 Order API

**Endpoint:** `/api/orders.php`  
**Method:** GET, POST  
**Authentication:** Required (Bearer token)

**Create Order (POST):**
```json
{
  "total": 299.99,
  "address": "123 Main St, City, Country",
  "paymentMethod": "chapa",
  "items": [
    {
      "productId": 1,
      "name": "Blue & Black Check Shirt",
      "quantity": 2,
      "price": 29.99
    }
  ]
}
```

**Create Order Response:**
```json
{
  "success": true,
  "orderId": 1,
  "orderNumber": "ORD-ABC12345"
}
```

**Get Orders (GET):**
```json
[
  {
    "id": 1,
    "order_number": "ORD-ABC12345",
    "total": 299.99,
    "status": "completed",
    "date": "2024-01-15 10:30:00",
    "items": [
      {
        "productName": "Blue & Black Check Shirt",
        "quantity": 2,
        "price": 29.99
      }
    ]
  }
]
```

### 3.7.5 Payment API

**Endpoint:** `/api/payment.php`  
**Method:** POST  
**Purpose:** Process payments through Chapa gateway

**Payment Request:**
```json
{
  "order_id": 1,
  "amount": 299.99,
  "email": "user@example.com",
  "first_name": "John",
  "last_name": "Doe"
}
```

**Payment Response:**
```json
{
  "success": true,
  "checkout_url": "https://checkout.chapa.co/...",
  "transaction_id": "CHAPA-TXN-123456"
}
```

**Payment Verification:**
```json
{
  "action": "verify",
  "transaction_id": "CHAPA-TXN-123456"
}
```

**Verification Response:**
```json
{
  "success": true,
  "status": "success",
  "amount": 299.99,
  "reference": "CHAPA-TXN-123456"
}
```

## 3.8 Security Design

### 3.8.1 Authentication Security

**Password Security:**
- Passwords hashed using `password_hash()` with bcrypt algorithm
- Cost factor: 10 (default)
- Passwords never stored in plain text
- Password verification using `password_verify()`

**Session Management:**
- Secure session cookies with HTTP-only flag
- Session regeneration after login
- Session timeout after 30 minutes of inactivity
- Session destruction on logout

**Token-Based Authentication (Mobile):**
- JWT-style tokens generated with `bin2hex(random_bytes(32))`
- Tokens stored in database linked to user accounts
- Token sent in Authorization header: `Bearer {token}`
- Token validation on every API request
- Token expiration and refresh mechanism

### 3.8.2 Data Security

**SQL Injection Prevention:**
```php
// Using prepared statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
```

**XSS Prevention:**
```php
// Output escaping
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');
```

**CSRF Protection:**
```php
// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Validate CSRF token
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token');
}
```

**Input Validation:**
```php
// Email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new Exception('Invalid email format');
}

// Sanitization
$clean_input = filter_var($input, FILTER_SANITIZE_STRING);
```

### 3.8.3 Payment Security

**Chapa Integration Security:**
- API keys stored in environment variables
- HTTPS required for all payment requests
- Transaction verification before order confirmation
- Webhook signature validation
- No credit card data stored locally
- PCI DSS compliance through Chapa gateway

**Transaction Flow:**
1. Order created with "pending" status
2. Payment request sent to Chapa
3. User redirected to Chapa checkout
4. Payment processed by Chapa
5. Webhook received with transaction status
6. Transaction verified via Chapa API
7. Order status updated based on verification
8. User notified of payment result

### Figure 3.19: Security Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    CLIENT LAYER                             │
│  ┌──────────────┐              ┌──────────────┐            │
│  │ Web Browser  │              │  Mobile App  │            │
│  └──────┬───────┘              └──────┬───────┘            │
└─────────┼──────────────────────────────┼───────────────────┘
          │ HTTPS                        │ HTTPS + Token
          │                              │
┌─────────┼──────────────────────────────┼───────────────────┐
│         │      SECURITY LAYER          │                   │
│         │                              │                   │
│  ┌──────▼──────────────────────────────▼──────┐            │
│  │  Input Validation & Sanitization           │            │
│  └──────┬─────────────────────────────────────┘            │
│         │                                                   │
│  ┌──────▼─────────────────────────────────────┐            │
│  │  Authentication & Authorization            │            │
│  │  - Session Management                      │            │
│  │  - Token Validation                        │            │
│  │  - Role-Based Access Control               │            │
│  └──────┬─────────────────────────────────────┘            │
│         │                                                   │
│  ┌──────▼─────────────────────────────────────┐            │
│  │  CSRF Protection                           │            │
│  └──────┬─────────────────────────────────────┘            │
│         │                                                   │
└─────────┼───────────────────────────────────────────────────┘
          │
┌─────────┼───────────────────────────────────────────────────┐
│         │      APPLICATION LAYER                            │
│  ┌──────▼─────────────────────────────────────┐            │
│  │  Business Logic (PHP)                      │            │
│  │  - Prepared Statements                     │            │
│  │  - Password Hashing (bcrypt)               │            │
│  │  - Output Escaping                         │            │
│  └──────┬─────────────────────────────────────┘            │
└─────────┼───────────────────────────────────────────────────┘
          │
┌─────────┼───────────────────────────────────────────────────┐
│         │      DATA LAYER                                   │
│  ┌──────▼─────────────────────────────────────┐            │
│  │  MySQL Database                            │            │
│  │  - Encrypted connections                   │            │
│  │  - User privileges                         │            │
│  │  - Regular backups                         │            │
│  └────────────────────────────────────────────┘            │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│              EXTERNAL SERVICES                              │
│  ┌──────────────────────────────────────────┐              │
│  │  Chapa Payment Gateway                   │              │
│  │  - HTTPS only                            │              │
│  │  - Webhook signature validation          │              │
│  │  - Transaction verification              │              │
│  └──────────────────────────────────────────┘              │
└─────────────────────────────────────────────────────────────┘
```

**Figure 3.19 Description:** The security architecture diagram illustrates the multiple layers of security implemented in Smart Mall. The client layer uses HTTPS for all communications. The security layer implements input validation, authentication, authorization, and CSRF protection. The application layer uses secure coding practices including prepared statements and password hashing. The data layer ensures database security with encrypted connections and proper access controls. External payment services are integrated securely with signature validation and transaction verification.

---

**End of Chapter 3**
