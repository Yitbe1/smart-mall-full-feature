# Smart Mall API Reference

> **Document ID:** API-REF-001  
> **Version:** 1.0.0  
> **Format:** JSON  
> **Base path:** `/api/`  
> **OpenAPI Spec:** `api/openapi.yaml` (667 lines)  
> **Cross-reference:** MASTER_DOCUMENTATION.md §3.1 (System Architecture), §3.7 (API/Backend Design)

---

## Table of Contents

1. [Overview](#1-overview)
2. [Authentication & Headers](#2-authentication--headers)
3. [Products API](#3-products-api)
4. [Categories API](#4-categories-api)
5. [Orders API](#5-orders-api)
6. [Search API](#6-search-api)
7. [API Index (Discovery)](#7-api-index-discovery)
8. [Standard Responses & Error Codes](#8-standard-responses--error-codes)
9. [Schema Definitions](#9-schema-definitions)
10. [cURL Examples](#10-curl-examples)

---

## 1. Overview

The Smart Mall REST API provides programmatic access to products, categories, orders, and search. All endpoints return JSON exclusively and are rate-limit free (intended for first-party use).

**Base URL** (dynamically resolved):
| Environment | URL |
|---|---|
| Production | `https://smartmall.unaux.com/api/` |
| Local dev | `http://localhost/reference/api/` |

**API Index endpoint** (`api/index.php`) returns a self-describing directory with every available endpoint, its method, and description.

[Screenshot: Browser showing `http://localhost/reference/api/index.php` with JSON endpoint listing]

### Endpoint Summary

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/index.php` | API discovery — lists all endpoints |
| `GET` | `/api/products.php` | List all products (optional `?category_id=X`) |
| `GET` | `/api/products.php?id=X` | Get a single product with full details |
| `GET` | `/api/categories.php` | List all categories |
| `GET` | `/api/categories.php?id=X` | Get a single category with its products |
| `GET` | `/api/orders.php?user_id=X` | List orders for a user |
| `GET` | `/api/orders.php?id=X` | Get a single order with line items |
| `POST` | `/api/orders.php` | Create a new order |
| `GET` | `/api/search.php?q=keyword` | Search products by name or description |

---

## 2. Authentication & Headers

### 2.1 Authentication

The API does **not** require authentication keys or tokens. Authentication is handled at the session level in browser-based flows.

> [!IMPORTANT]
> The Orders API endpoints (`api/orders.php`) require a valid `user_id` to function. In production, session-based ownership checks should be enforced at the application layer (see `checkout.php`, `orders.php` in the user-facing codebase).

### 2.2 Request Headers

All endpoints accept standard HTTP headers:

```http
Accept: application/json
Content-Type: application/json   (POST only)
```

### 2.3 Response Headers

All endpoints return:

```http
Content-Type: application/json
Access-Control-Allow-Origin: *
```

The CORS header is set at the file level for every API endpoint (`api/products.php`, `api/categories.php`, `api/orders.php`, `api/index.php`).

### 2.4 `.htaccess` Restriction

The `api/.htaccess` file restricts access:

```apache
Require all denied

<Files "search.php">
    Require all granted
</Files>
```

This means only `search.php` is publicly accessible by default. The other API endpoints (`products.php`, `categories.php`, `orders.php`) are **denied** to direct HTTP access. In local development, this may not apply depending on Apache configuration.

---

## 3. Products API

**File:** `api/products.php` (91 lines)  
**Tags:** Products

### 3.1 List All Products

Retrieves all products, optionally filtered by category.

```
GET /api/products.php
GET /api/products.php?category_id=3
```

**Parameters (query string):**

| Parameter | Type | Required | Description |
|---|---|---|---|
| `category_id` | int | No | Filter by category ID |

**Response `200`:**
```json
[
  {
    "product_id": 12,
    "name": "Wireless Bluetooth Headphones",
    "description": "High-quality wireless headphones with noise cancellation...",
    "price": 79.99,
    "stock": 45,
    "image": "headphones-black.jpg",
    "image_url": "/uploads/headphones-black.jpg",
    "category_id": 3,
    "category_name": "Electronics"
  }
]
```

**Details:**
- Sorted by `created_at DESC` (`api/products.php`)
- `image_url` is computed dynamically — local filenames get `/uploads/` prefix, external URLs pass through, empty images return `null` (`api/products.php`)
- The list endpoint returns a subset of fields (`product_id, name, description, price, stock, image, category_id, category_name` via `api/products.php`)

### 3.2 Get Single Product

Retrieves full details for one product by ID.

```
GET /api/products.php?id=12
```

**Parameters (query string):**

| Parameter | Type | Required | Description |
|---|---|---|---|
| `id` | int | Yes | Product ID (minimum 1) |

**Response `200`:**
```json
{
  "product_id": 12,
  "name": "Wireless Bluetooth Headphones",
  "description": "High-quality wireless headphones with noise cancellation and 30-hour battery life.",
  "price": 79.99,
  "stock": 45,
  "image": "headphones-black.jpg",
  "image_url": "/uploads/headphones-black.jpg",
  "category_id": 3,
  "category_name": "Electronics",
  "created_at": "2026-05-20 14:30:00"
}
```

**Details:**
- `image_url` resolution same as list endpoint (`api/products.php`)
- Includes `created_at` timestamp (not present in list response)
- Uses `LEFT JOIN categories` to include category name (`api/products.php`)

**Error `404`:**
```json
{
  "error": "Product not found"
}
```

**Error `500`:**
```json
{
  "error": "Database error"
}
```

### 3.3 Product Schema

| Field | Type | Nullable | Description |
|---|---|---|---|
| `product_id` | int | No | Unique product identifier |
| `name` | string | No | Product name |
| `description` | string | No | Detailed product description |
| `price` | float | No | Product price |
| `stock` | int | No | Available quantity in stock |
| `image` | string | Yes | Raw image filename or full URL |
| `image_url` | string | Yes | Resolved absolute image URL |
| `category_id` | int | No | Foreign key to category |
| `category_name` | string | No | Category display name (from JOIN) |
| `created_at` | string | No | Creation timestamp (single product only) |

---

## 4. Categories API

**File:** `api/categories.php` (73 lines)  
**Tags:** Categories

### 4.1 List All Categories

```
GET /api/categories.php
```

**Response `200`:**
```json
[
  {
    "category_id": 1,
    "name": "Electronics",
    "image1": "/uploads/cat-electronics-1.jpg",
    "image2": "/uploads/cat-electronics-2.jpg",
    "image3": "/uploads/cat-electronics-3.jpg"
  }
]
```

**Details:**
- Sorted by `name ASC` (`api/categories.php`)
- Categories have up to 3 image fields (`image1`, `image2`, `image3`) stored in the database

### 4.2 Get Single Category with Products

```
GET /api/categories.php?id=3
```

**Parameters (query string):**

| Parameter | Type | Required | Description |
|---|---|---|---|
| `id` | int | Yes | Category ID (minimum 1) |

**Response `200`:**
```json
{
  "category_id": 3,
  "name": "Electronics",
  "image1": "/uploads/cat-electronics-1.jpg",
  "image2": "/uploads/cat-electronics-2.jpg",
  "image3": "/uploads/cat-electronics-3.jpg",
  "products": [
    {
      "product_id": 14,
      "name": "Wireless Bluetooth Headphones",
      "description": "High-quality wireless headphones...",
      "price": 79.99,
      "stock": 45,
      "image": "headphones-black.jpg",
      "image_url": "/uploads/headphones-black.jpg"
    }
  ]
}
```

**Details:**
- Products within a category are sorted by `created_at DESC` (`api/categories.php`)
- Product fields in nested array: `product_id, name, description, price, stock, image, image_url` (`api/categories.php`)
- Image URLs are resolved with `/uploads/` prefix the same way as the Products API (`api/categories.php`)

**Error `404`:**
```json
{
  "error": "Category not found"
}
```

**Error `500`:**
```json
{
  "error": "Database error"
}
```

### 4.3 Category Schema

| Field | Type | Nullable | Description |
|---|---|---|---|
| `category_id` | int | No | Unique category identifier |
| `name` | string | No | Category display name |
| `image1` | string | Yes | Primary category image |
| `image2` | string | Yes | Secondary category image |
| `image3` | string | Yes | Tertiary category image |
| `products` | array | No | Array of product objects (single category only) |

---

## 5. Orders API

**File:** `api/orders.php` (188 lines)  
**Tags:** Orders  
**Methods supported:** `GET`, `POST`

### 5.1 List Orders by User

Retrieves all orders for a given user.

```
GET /api/orders.php?user_id=7
```

**Parameters (query string):**

| Parameter | Type | Required | Description |
|---|---|---|---|
| `user_id` | int | Yes | User ID to fetch orders for |

**Response `200`:**
```json
[
  {
    "order_id": 15,
    "user_id": 7,
    "total_price": 149.99,
    "status": "pending",
    "created_at": "2026-05-27 10:15:00"
  }
]
```

**Details:**
- Returns **summary only** (no line items — use single order endpoint for items) (`api/orders.php`)
- Sorted by `created_at DESC` (`api/orders.php`)
- Order statuses include: `pending`, `processing`, `shipped`, `delivered`, `cancelled`

**Error `400` (missing user_id):**
```json
{
  "error": "Missing required parameter: user_id"
}
```

### 5.2 Get Single Order with Items

```
GET /api/orders.php?id=15
```

**Parameters (query string):**

| Parameter | Type | Required | Description |
|---|---|---|---|
| `id` | int | Yes | Order ID to retrieve |

**Response `200`:**
```json
{
  "order_id": 15,
  "user_id": 7,
  "total_price": 149.99,
  "status": "pending",
  "created_at": "2026-05-27 10:15:00",
  "items": [
    {
      "id": 87,
      "order_id": 15,
      "product_id": 12,
      "quantity": 2,
      "price": 49.99,
      "product_name": "Wireless Bluetooth Headphones",
      "image": "headphones-black.jpg"
    }
  ]
}
```

**Details:**
- Line items are fetched from `order_items` table with `LEFT JOIN products` for `product_name` and `image` (`api/orders.php`)
- `price` in items is the unit price at time of order (not current product price)

**Error `404`:**
```json
{
  "error": "Order not found"
}
```

### 5.3 Create Order

Creates a new order with line items. Stock is decremented atomically within a database transaction.

```
POST /api/orders.php
Content-Type: application/json

{
  "user_id": 7,
  "items": [
    { "product_id": 12, "quantity": 2 },
    { "product_id": 8,  "quantity": 1 }
  ]
}
```

**Request body:**

| Field | Type | Required | Description |
|---|---|---|---|
| `user_id` | int | Yes | ID of the user placing the order |
| `items` | array | Yes | Array of product-quantity pairs |

**Item object:**

| Field | Type | Required | Description |
|---|---|---|---|
| `product_id` | int | Yes | Product ID to purchase |
| `quantity` | int | Yes | Quantity to purchase |

**Validation rules** (`api/orders.php`):

1. `user_id` and `items` must be present; `items` must be a non-empty array
2. Each item must have `product_id > 0` and `quantity > 0`
3. The user must exist (`api/orders.php`)
4. Each product must exist (`api/orders.php`)
5. Stock must be sufficient for the requested quantity (`api/orders.php`)

**Transaction flow** (`api/orders.php`):

1. Begin transaction
2. For each item: validate product, check stock, calculate line total, decrement stock (`UPDATE products SET stock = stock - :qty`)
3. Insert order record (`INSERT INTO orders`)
4. Insert order_items records (`INSERT INTO order_items`)
5. Commit transaction
6. On any error: rollback and return error message

**Response `201`:**
```json
{
  "success": true,
  "order_id": 42,
  "total_price": 149.99,
  "status": "pending"
}
```

**Error `400` (validation):**
```json
{
  "error": "Invalid request. Required: user_id (int) and items (array of {product_id, quantity})"
}
```

**Error `400` (insufficient stock):**
```json
{
  "error": "Insufficient stock for product 'Gaming Mouse' (requested: 10, available: 3)"
}
```

**Error `400` (product not found):**
```json
{
  "error": "Product ID 999 not found"
}
```

**Error `404` (user not found):**
```json
{
  "error": "User not found"
}
```

**Error `405` (wrong method):**
```json
{
  "error": "Method not allowed"
}
```

### 5.4 Order Schema

**Order object:**

| Field | Type | Nullable | Description |
|---|---|---|---|
| `order_id` | int | No | Unique order identifier |
| `user_id` | int | No | ID of the ordering user |
| `total_price` | float | No | Total monetary value |
| `status` | string | No | Current status (`pending`, `processing`, `shipped`, `delivered`, `cancelled`) |
| `created_at` | string | No | Creation timestamp |
| `items` | array | Yes | Line items (only when `?id=X`) |

**OrderItem object:**

| Field | Type | Nullable | Description |
|---|---|---|---|
| `id` | int | No | Unique order item identifier |
| `order_id` | int | No | Parent order ID |
| `product_id` | int | No | Product ID |
| `quantity` | int | No | Quantity ordered |
| `price` | float | No | Unit price at time of order |
| `product_name` | string | No | Product name (from JOIN) |
| `image` | string | Yes | Product image filename or URL (from JOIN) |

**CreateOrderRequest object:**

| Field | Type | Required | Description |
|---|---|---|---|
| `user_id` | int | Yes | User ID |
| `items` | array | Yes | Array of `{product_id, quantity}` |

---

## 6. Search API

**File:** `api/search.php` (51 lines)  
**Tags:** Products  
**Access:** Public (`.htaccess` allows `search.php` to all)

### 6.1 Search Products

Searches products by name or description using a MySQL `LIKE` query.

```
GET /api/search.php?q=wireless
```

**Parameters (query string):**

| Parameter | Type | Required | Description |
|---|---|---|---|
| `q` | string | Yes | Search keyword (minimum 1 character) |

**Response `200`:**
```json
[
  {
    "product_id": 12,
    "name": "Wireless Bluetooth Headphones",
    "description": "High-quality wireless headphones...",
    "price": 79.99,
    "image": "headphones-black.jpg",
    "image_url": "/reference/uploads/headphones-black.jpg",
    "display_price": "Br 2,699.53"
  }
]
```

**Details:**
- Searches both `name LIKE` and `description LIKE` with `%` wildcards on both sides (`api/search.php`)
- Limited to **6 results maximum** (`api/search.php`)
- Sorted by `created_at DESC`
- `image_url` is resolved using `base_url_path()` from `config.php` (note: this differs from other API endpoints which use a hardcoded `/uploads/` prefix — `api/search.php`)
- `display_price` is added via `smartmall_format_money()` for formatted currency display (`api/search.php`)
- Returns empty array `[]` when `q` is missing or empty (`api/search.php`)

**Error `500`:**
```json
{
  "error": "Database error"
}
```

### 6.2 Search Schema

| Field | Type | Nullable | Description |
|---|---|---|---|
| `product_id` | int | No | Unique product identifier |
| `name` | string | No | Product name |
| `description` | string | No | Product description |
| `price` | float | No | Product price |
| `image` | string | Yes | Raw image filename or URL |
| `image_url` | string | Yes | Resolved image URL |
| `display_price` | string | Yes | Formatted price with currency symbol |

---

## 7. API Index (Discovery)

**File:** `api/index.php` (31 lines)

Returns a self-describing directory of all available API endpoints. Useful for API exploration and debugging.

```
GET /api/index.php
```

**Response `200`:**
```json
{
  "api": "Smart Mall REST API",
  "version": "1.0.0",
  "base_url": "https://smartmall.unaux.com/api",
  "endpoints": {
    "products": {
      "list": {
        "method": "GET",
        "url": "https://smartmall.unaux.com/api/products.php",
        "description": "List all products. Optional ?category_id=X to filter by category."
      },
      "single": {
        "method": "GET",
        "url": "https://smartmall.unaux.com/api/products.php?id=X",
        "description": "Get a single product with full details."
      }
    },
    "categories": { ... },
    "orders": { ... },
    "search": { ... }
  }
}
```

**Details:**
- The `base_url` is dynamically computed from the request protocol and host (`api/index.php`)
- All endpoint URLs are auto-generated using `$base`, so they reflect the actual deployment domain

---

## 8. Standard Responses & Error Codes

### 8.1 HTTP Status Codes

| Code | Meaning | When It Occurs |
|---|---|---|
| `200` | Success | GET requests return data |
| `201` | Created | POST order created successfully |
| `400` | Bad Request | Missing parameters, invalid input, insufficient stock |
| `404` | Not Found | Requested product, category, order, or user doesn't exist |
| `405` | Method Not Allowed | Non-GET/POST method on orders endpoint |
| `500` | Internal Server Error | Database or server failure |

### 8.2 Error Response Format

All errors follow this structure:

```json
{
  "error": "Human-readable error message"
}
```

### 8.3 Common Error Messages

| Endpoint | Error | Status | Source |
|---|---|---|---|
| Products | `Product not found` | 404 | `api/products.php` |
| Products | `Database error` | 500 | `api/products.php` |
| Categories | `Category not found` | 404 | `api/categories.php` |
| Categories | `Database error` | 500 | `api/categories.php` |
| Orders | `Missing required parameter: user_id` | 400 | `api/orders.php` |
| Orders | `Order not found` | 404 | `api/orders.php` |
| Orders | `Invalid request...` | 400 | `api/orders.php` |
| Orders | `User not found` | 404 | `api/orders.php` |
| Orders | `Product ID X not found` | 400 | `api/orders.php` |
| Orders | `Insufficient stock...` | 400 | `api/orders.php` |
| Orders | `Method not allowed` | 405 | `api/orders.php` |
| Orders | `Database error` | 500 | `api/orders.php` |
| Search | `Database error` | 500 | `api/search.php` |

---

## 9. Schema Definitions

### 9.1 Product Schema

```json
{
  "product_id":    "integer (read-only, auto-increment)",
  "name":          "string (max 255 chars)",
  "description":   "text",
  "price":         "float (decimal(10,2) in DB)",
  "stock":         "integer",
  "image":         "string | null (filename or external URL)",
  "image_url":     "string | null (resolved absolute path)",
  "category_id":   "integer (FK → categories.category_id)",
  "category_name": "string (from LEFT JOIN categories)",
  "created_at":    "datetime (list endpoint omits this)"
}
```

### 9.2 Category Schema

```json
{
  "category_id": "integer (read-only, auto-increment)",
  "name":        "string (max 255 chars)",
  "image1":      "string | null",
  "image2":      "string | null",
  "image3":      "string | null",
  "products":    "array (only when fetched by id)"
}
```

### 9.3 Order Schema

```json
{
  "order_id":    "integer (read-only, auto-increment)",
  "user_id":     "integer (FK → users.user_id)",
  "total_price": "float",
  "status":      "string (enum: pending | processing | shipped | delivered | cancelled)",
  "created_at":  "datetime",
  "items":       "array (only when fetched by id)"
}
```

### 9.4 OrderItem Schema

```json
{
  "id":           "integer (read-only, auto-increment)",
  "order_id":     "integer (FK → orders.order_id)",
  "product_id":   "integer (FK → products.product_id)",
  "quantity":     "integer",
  "price":        "float (unit price at time of order)",
  "product_name": "string (from LEFT JOIN products)",
  "image":        "string | null (from LEFT JOIN products)"
}
```

---

## 10. cURL Examples

### 10.1 List Products

```bash
curl -s http://localhost/reference/api/products.php | jq
```

### 10.2 Get Product by ID

```bash
curl -s http://localhost/reference/api/products.php?id=12 | jq
```

### 10.3 Filter Products by Category

```bash
curl -s "http://localhost/reference/api/products.php?category_id=3" | jq
```

### 10.4 Search Products

```bash
curl -s "http://localhost/reference/api/search.php?q=wireless" | jq
```

### 10.5 List Categories

```bash
curl -s http://localhost/reference/api/categories.php | jq
```

### 10.6 Get Category with Products

```bash
curl -s http://localhost/reference/api/categories.php?id=3 | jq
```

### 10.7 List Orders for User

```bash
curl -s "http://localhost/reference/api/orders.php?user_id=7" | jq
```

### 10.8 Get Order with Items

```bash
curl -s http://localhost/reference/api/orders.php?id=15 | jq
```

### 10.9 Create Order

```bash
curl -s -X POST http://localhost/reference/api/orders.php \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 7,
    "items": [
      {"product_id": 12, "quantity": 2},
      {"product_id": 8,  "quantity": 1}
    ]
  }' | jq
```

### 10.10 API Discovery

```bash
curl -s http://localhost/reference/api/index.php | jq
```
