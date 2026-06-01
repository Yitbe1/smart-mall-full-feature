<?php
// Add to Cart Handler (AJAX endpoint)
session_start();

$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    $env_vars = parse_ini_file($env_file);
    if ($env_vars) {
        foreach ($env_vars as $key => $value) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

require_once 'includes/db.php';

header('Content-Type: application/json');

// Require login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit();
}

$data       = json_decode(file_get_contents('php://input'), true);
$product_id = (int)($data['product_id'] ?? 0);
$quantity   = (int)($data['quantity'] ?? 1);
$user_id    = $_SESSION['user_id'];

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

try {
    $pdo = getDB();

    // Check product stock
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE product_id = :product_id");
    $stmt->execute([':product_id' => $product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit();
    }

    if ($product['stock'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
        exit();
    }

    // Check if already in cart
    $stmt = $pdo->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = :user_id AND product_id = :product_id");
    $stmt->execute([':user_id' => $user_id, ':product_id' => $product_id]);
    $cart_item = $stmt->fetch();

    if ($cart_item) {
        // Cap new quantity at available stock
        $new_quantity = min($cart_item['quantity'] + $quantity, $product['stock']);
        $stmt = $pdo->prepare("UPDATE cart SET quantity = :quantity WHERE cart_id = :cart_id");
        $stmt->execute([':quantity' => $new_quantity, ':cart_id' => $cart_item['cart_id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
        $stmt->execute([':user_id' => $user_id, ':product_id' => $product_id, ':quantity' => $quantity]);
    }

    echo json_encode(['success' => true, 'message' => 'Item added to cart']);
} catch (Exception $e) {
    error_log("Add to cart error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Could not add item to cart. Please try again.']);
}
