<?php
// Delete Product Handler
require_once __DIR__ . '/../config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Must be a POST request with a valid CSRF token
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit();
}

csrf_verify();

if (empty($_POST['product_id'])) {
    header('Location: dashboard.php');
    exit();
}

$product_id = (int)$_POST['product_id'];

try {
    $pdo = getDB();

    // Get product to find image file
    $stmt = $pdo->prepare("SELECT image FROM products WHERE product_id = :product_id");
    $stmt->execute([':product_id' => $product_id]);
    $product = $stmt->fetch();

    if ($product) {
        // Delete product from database
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = :product_id");
        $stmt->execute([':product_id' => $product_id]);

        // Delete image file if it exists
        if (!empty($product['image'])) {
            $image_path = realpath(__DIR__ . '/../uploads/') . DIRECTORY_SEPARATOR . $product['image'];
            if ($image_path && file_exists($image_path)) {
                unlink($image_path);
            }
        }

        require_once __DIR__ . '/../includes/cache.php';
        invalidate_cache_pattern('product');
        $_SESSION['success'] = 'Product deleted successfully!';
    } else {
        $_SESSION['error'] = 'Product not found.';
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Could not delete product. Please try again.';
}

header('Location: dashboard.php');
exit();
