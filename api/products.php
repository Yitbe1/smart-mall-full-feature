<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/db.php';

try {
    $pdo = getDB();

    // Single product by id
    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $stmt = $pdo->prepare("
            SELECT p.*, c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            WHERE p.product_id = :id
        ");
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch();

        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            exit;
        }

        if (!empty($product['image']) && strpos($product['image'], 'http') !== 0) {
            $product['image_url'] = '/uploads/' . $product['image'];
        } elseif (!empty($product['image'])) {
            $product['image_url'] = $product['image'];
        } else {
            $product['image_url'] = null;
        }

        echo json_encode($product);
        exit;
    }

    // List products, optionally filtered by category
    $sql = "
        SELECT p.product_id, p.name, p.description, p.price, p.stock, p.image, p.category_id,
               c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
    ";
    $params = [];

    if (!empty($_GET['category_id'])) {
        $sql .= ' WHERE p.category_id = :cat_id';
        $params[':cat_id'] = (int) $_GET['category_id'];
    }

    $sql .= ' ORDER BY p.created_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    foreach ($products as &$p) {
        if (!empty($p['image']) && strpos($p['image'], 'http') !== 0) {
            $p['image_url'] = '/uploads/' . $p['image'];
        } elseif (!empty($p['image'])) {
            $p['image_url'] = $p['image'];
        } else {
            $p['image_url'] = null;
        }
    }

    echo json_encode($products);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    error_log('Smart Mall API (products): ' . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
    error_log('Smart Mall API (products): ' . $e->getMessage());
}
