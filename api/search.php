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
    foreach ($results as &$product) {
        // Need to pass the relative path carefully or let frontend handle it.
        // It's safer to generate the absolute URL path here since we are in /api
        // The get_product_image_url returns relative paths.
        // If the image is external (http), keep it.
        // If it's local, it returns uploads/image.jpg (from index perspective) or ../uploads/image.jpg (from admin).
        // Let's manually construct the absolute path from the web root.
        if (!empty($product['image']) && strpos($product['image'], 'http') !== 0) {
            $product['image_url'] = '/uploads/' . $product['image'];
        } else if (!empty($product['image'])) {
            $product['image_url'] = $product['image'];
        } else {
            $product['image_url'] = null; // No image
        }
        $product['display_price'] = smartmall_format_money($product['price']);
    }

    echo json_encode($results);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
