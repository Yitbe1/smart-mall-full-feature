# CHAPTER 4: SYSTEM IMPLEMENTATION (Part 2)

## 4.3 Backend Implementation

### 4.3.1 Session Management

**Session Initialization (includes/session.php):**
```php
<?php
// Start session with secure settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS

session_start();

// Regenerate session ID periodically
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Session timeout (30 minutes)
if (isset($_SESSION['last_activity']) && 
    (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
$_SESSION['last_activity'] = time();
?>
```

### 4.3.2 Authentication System

**User Registration (register.php):**
```php
<?php
require_once 'includes/db.php';
require_once 'includes/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($errors)) {
        $pdo = getDB();
        
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $errors[] = "Email already registered";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            
            if ($stmt->execute([$name, $email, $hashed_password])) {
                $_SESSION['success'] = "Registration successful! Please login.";
                header('Location: login.php');
                exit;
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
        }
    }
    
    $_SESSION['errors'] = $errors;
}
?>
```

**User Login (login.php):**
```php
<?php
require_once 'includes/db.php';
require_once 'includes/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        // Redirect based on role
        if ($user['role'] === 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        $_SESSION['error'] = "Invalid email or password";
    }
}
?>
```

### 4.3.3 CRUD Operations

**Product Management - Create:**
```php
<?php
// admin/add_product.php
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once 'protect.php'; // Admin authentication check

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);
    $image = trim($_POST['image']);
    
    // Generate slug
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    
    $pdo = getDB();
    $stmt = $pdo->prepare("
        INSERT INTO products 
        (name, slug, description, price, stock, category_id, image, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    if ($stmt->execute([$name, $slug, $description, $price, $stock, $category_id, $image])) {
        $_SESSION['success'] = "Product added successfully";
        header('Location: manage_products.php');
        exit;
    } else {
        $_SESSION['error'] = "Failed to add product";
    }
}
?>
```

**Product Management - Update:**
```php
<?php
// admin/edit_product.php
require_once '../includes/db.php';
require_once 'protect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);
    $image = trim($_POST['image']);
    
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    
    $pdo = getDB();
    $stmt = $pdo->prepare("
        UPDATE products 
        SET name = ?, slug = ?, description = ?, price = ?, 
            stock = ?, category_id = ?, image = ? 
        WHERE id = ?
    ");
    
    if ($stmt->execute([$name, $slug, $description, $price, $stock, $category_id, $image, $id])) {
        $_SESSION['success'] = "Product updated successfully";
    } else {
        $_SESSION['error'] = "Failed to update product";
    }
    
    header('Location: manage_products.php');
    exit;
}
?>
```

**Product Management - Delete:**
```php
<?php
// admin/delete_product.php
require_once '../includes/db.php';
require_once 'protect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    
    $pdo = getDB();
    
    // Check if product is in any orders
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ?");
    $stmt->execute([$id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $_SESSION['error'] = "Cannot delete product that has been ordered";
    } else {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['success'] = "Product deleted successfully";
        } else {
            $_SESSION['error'] = "Failed to delete product";
        }
    }
    
    header('Location: manage_products.php');
    exit;
}
?>
```

### 4.3.4 Order Processing

**Order Creation:**
```php
<?php
// process_checkout.php
require_once 'includes/db.php';
require_once 'includes/session.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDB();
    
    try {
        $pdo->beginTransaction();
        
        // Get cart items
        $stmt = $pdo->prepare("
            SELECT c.*, p.name, p.price 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($cart_items)) {
            throw new Exception("Cart is empty");
        }
        
        // Calculate total
        $total = 0;
        foreach ($cart_items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        // Generate order number
        $order_number = 'ORD-' . strtoupper(substr(md5(time() . $_SESSION['user_id']), 0, 8));
        
        // Create order
        $stmt = $pdo->prepare("
            INSERT INTO orders 
            (user_id, order_number, total, status, shipping_address, payment_method, created_at) 
            VALUES (?, ?, ?, 'pending', ?, ?, NOW())
        ");
        
        $shipping_address = $_POST['address'] . ', ' . $_POST['city'];
        $payment_method = $_POST['payment_method'];
        
        $stmt->execute([
            $_SESSION['user_id'],
            $order_number,
            $total,
            $shipping_address,
            $payment_method
        ]);
        
        $order_id = $pdo->lastInsertId();
        
        // Create order items
        $stmt = $pdo->prepare("
            INSERT INTO order_items 
            (order_id, product_id, product_name, quantity, price) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($cart_items as $item) {
            $stmt->execute([
                $order_id,
                $item['product_id'],
                $item['name'],
                $item['quantity'],
                $item['price']
            ]);
            
            // Update product stock
            $update_stmt = $pdo->prepare("
                UPDATE products 
                SET stock = stock - ? 
                WHERE id = ?
            ");
            $update_stmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        // Clear cart
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        $pdo->commit();
        
        // Redirect to payment
        $_SESSION['order_id'] = $order_id;
        $_SESSION['order_number'] = $order_number;
        $_SESSION['order_total'] = $total;
        
        header('Location: payment.php');
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Order processing failed: " . $e->getMessage();
        header('Location: checkout.php');
        exit;
    }
}
?>
```

## 4.4 Mobile App Implementation

### 4.4.1 Flutter Project Structure

**Actual Project Structure:**
```
smart_mall_app/
├── lib/
│   ├── main.dart                      # App entry point
│   ├── models/
│   │   └── product.dart               # Product & Category models
│   ├── services/
│   │   ├── api_service.dart           # API integration
│   │   ├── auth_service.dart          # Authentication
│   │   └── cart_service.dart          # Shopping cart
│   ├── screens/
│   │   ├── home_screen.dart           # Main screen
│   │   ├── product_detail_screen.dart # Product details
│   │   ├── cart_screen.dart           # Shopping cart
│   │   ├── checkout_screen.dart       # Checkout
│   │   ├── login_screen.dart          # Login
│   │   ├── register_screen.dart       # Registration
│   │   ├── orders_screen.dart         # Order history
│   │   ├── profile_screen.dart        # User profile
│   │   └── admin/
│   │       ├── admin_dashboard_screen.dart
│   │       ├── admin_products_screen.dart
│   │       └── admin_orders_screen.dart
│   └── widgets/                       # Reusable widgets
├── assets/
│   └── images/
│       ├── logo.png
│       └── logo-icon.png
├── pubspec.yaml                       # Dependencies
└── README.md
```

### 4.4.2 API Communication

**API Service Implementation (lib/services/api_service.dart):**
```dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/product.dart';

class ApiService {
  static const String baseUrl = 'http://localhost/reference';
  
  // Get all products
  static Future<List<Product>> getProducts({
    String? category, 
    String? search
  }) async {
    try {
      String url = '$baseUrl/api/products.php?';
      if (category != null) url += 'category=$category&';
      if (search != null) url += 'q=$search';
      
      final response = await http.get(Uri.parse(url));
      
      if (response.statusCode == 200) {
        final List<dynamic> data = json.decode(response.body);
        return data.map((json) => Product.fromJson(json)).toList();
      }
      return [];
    } catch (e) {
      print('Error fetching products: $e');
      return [];
    }
  }
  
  // Login
  static Future<Map<String, dynamic>> login(
    String email, 
    String password
  ) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/auth.php'),
        body: json.encode({
          'action': 'login',
          'email': email,
          'password': password,
        }),
        headers: {'Content-Type': 'application/json'},
      );
      
      return json.decode(response.body);
    } catch (e) {
      return {'success': false, 'message': 'Connection error'};
    }
  }
  
  // Register
  static Future<Map<String, dynamic>> register(
    String name, 
    String email, 
    String password
  ) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/auth.php'),
        body: json.encode({
          'action': 'register',
          'name': name,
          'email': email,
          'password': password,
        }),
        headers: {'Content-Type': 'application/json'},
      );
      
      return json.decode(response.body);
    } catch (e) {
      return {'success': false, 'message': 'Connection error'};
    }
  }
  
  // Get orders
  static Future<List<dynamic>> getOrders(String? token) async {
    if (token == null) return [];
    
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/orders.php'),
        headers: {'Authorization': 'Bearer $token'},
      );
      
      if (response.statusCode == 200) {
        return json.decode(response.body);
      }
      return [];
    } catch (e) {
      return [];
    }
  }
  
  // Create order
  static Future<Map<String, dynamic>> createOrder(
    String? token, 
    Map<String, dynamic> orderData
  ) async {
    if (token == null) {
      return {'success': false, 'message': 'Not authenticated'};
    }
    
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/orders.php'),
        body: json.encode(orderData),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );
      
      return json.decode(response.body);
    } catch (e) {
      return {'success': false, 'message': 'Connection error'};
    }
  }
}
```

### 4.4.3 State Management

**Cart Service with Provider (lib/services/cart_service.dart):**
```dart
import 'package:flutter/foundation.dart';
import '../models/product.dart';

class CartItem {
  final Product product;
  int quantity;

  CartItem({required this.product, this.quantity = 1});

  double get total => product.price * quantity;
}

class CartService extends ChangeNotifier {
  final List<CartItem> _items = [];

  List<CartItem> get items => _items;

  int get itemCount => _items.fold(0, (sum, item) => sum + item.quantity);

  double get total => _items.fold(0, (sum, item) => sum + item.total);

  void addItem(Product product) {
    final existingIndex = _items.indexWhere(
      (item) => item.product.id == product.id
    );

    if (existingIndex >= 0) {
      _items[existingIndex].quantity++;
    } else {
      _items.add(CartItem(product: product));
    }
    
    notifyListeners();
  }

  void removeItem(int productId) {
    _items.removeWhere((item) => item.product.id == productId);
    notifyListeners();
  }

  void updateQuantity(int productId, int quantity) {
    final index = _items.indexWhere(
      (item) => item.product.id == productId
    );
    
    if (index >= 0) {
      if (quantity <= 0) {
        _items.removeAt(index);
      } else {
        _items[index].quantity = quantity;
      }
      notifyListeners();
    }
  }

  void clear() {
    _items.clear();
    notifyListeners();
  }
}
```

**Authentication Service (lib/services/auth_service.dart):**
```dart
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'api_service.dart';

class AuthService extends ChangeNotifier {
  bool _isAuthenticated = false;
  String? _token;
  String? _userName;

  bool get isAuthenticated => _isAuthenticated;
  String? get userName => _userName;
  String? get token => _token;

  Future<void> checkAuth() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('token');
    _userName = prefs.getString('userName');
    _isAuthenticated = _token != null;
    notifyListeners();
  }

  Future<Map<String, dynamic>> login(String email, String password) async {
    final result = await ApiService.login(email, password);
    
    if (result['success'] == true) {
      _token = result['token'];
      _userName = result['name'];
      _isAuthenticated = true;
      
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('token', _token!);
      await prefs.setString('userName', _userName!);
      
      notifyListeners();
    }
    
    return result;
  }

  Future<Map<String, dynamic>> register(
    String name, 
    String email, 
    String password
  ) async {
    final result = await ApiService.register(name, email, password);
    
    if (result['success'] == true) {
      return await login(email, password);
    }
    
    return result;
  }

  Future<void> logout() async {
    _token = null;
    _userName = null;
    _isAuthenticated = false;
    
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
    await prefs.remove('userName');
    
    notifyListeners();
  }
}
```

---

**Continue to Chapter 4 Part 3...**
