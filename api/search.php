<?php
header('Content-Type: application/json');

// Path relative to api/ directory
require_once __DIR__ . '/../config.php';

$search_query = trim($_GET['q'] ?? '');

if (empty($search_query)) {
    echo json_encode([]);
    exit;
}

try {
    $pdo = getDB();
    $search_term = '%' . $search_query . '%';

    // Fetch top 6 matching products
    $stmt = $pdo->prepare("
        SELECT product_id, name, description, price, image 
        FROM products 
        WHERE name LIKE :query1 OR description LIKE :query2 
        ORDER BY created_at DESC 
        LIMIT 6
    ");
    $stmt->execute([
        ':query1' => $search_term,
        ':query2' => $search_term
    ]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the image URLs properly before sending to the client
    $subfolder = base_url_path();
    foreach ($results as &$product) {
        if (!empty($product['image']) && strpos($product['image'], 'http') !== 0) {
            $product['image_url'] = $subfolder . '/uploads/' . $product['image'];
        } else if (!empty($product['image'])) {
            $product['image_url'] = $product['image'];
        } else {
            $product['image_url'] = null;
        }
        $product['display_price'] = smartmall_format_money($product['price']);
    }

    echo json_encode($results);
} catch (PDOException $e) {
    error_log("API search error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
