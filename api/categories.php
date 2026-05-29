<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/db.php';

try {
    $pdo = getDB();

    // Single category with its products
    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];

        $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = :id");
        $stmt->execute([':id' => $id]);
        $category = $stmt->fetch();

        if (!$category) {
            http_response_code(404);
            echo json_encode(['error' => 'Category not found']);
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT product_id, name, description, price, stock, image
            FROM products
            WHERE category_id = :id
            ORDER BY created_at DESC
        ");
        $stmt->execute([':id' => $id]);
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

        $category['products'] = $products;
        echo json_encode($category);
        exit;
    }

    // List all categories
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll();

    echo json_encode($categories);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    error_log('Smart Mall API (categories): ' . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
    error_log('Smart Mall API (categories): ' . $e->getMessage());
}
