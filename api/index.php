<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$base = rtrim((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . dirname($_SERVER['SCRIPT_NAME']), '/');

$endpoints = [
    'products' => [
        'list'    => ['method' => 'GET', 'url' => $base . '/products.php', 'description' => 'List all products. Optional ?category_id=X to filter by category.'],
        'single'  => ['method' => 'GET', 'url' => $base . '/products.php?id=X', 'description' => 'Get a single product with full details.'],
    ],
    'categories' => [
        'list'    => ['method' => 'GET', 'url' => $base . '/categories.php', 'description' => 'List all categories.'],
        'single'  => ['method' => 'GET', 'url' => $base . '/categories.php?id=X', 'description' => 'Get a single category with its products.'],
    ],
    'orders' => [
        'list'    => ['method' => 'GET', 'url' => $base . '/orders.php?user_id=X', 'description' => 'Get all orders for a user. Requires user_id.'],
        'single'  => ['method' => 'GET', 'url' => $base . '/orders.php?id=X', 'description' => 'Get a single order with its items.'],
        'create'  => ['method' => 'POST', 'url' => $base . '/orders.php', 'description' => 'Create a new order. Send JSON body: {"user_id": X, "items": [{"product_id": X, "quantity": X}]}'],
    ],
    'search' => [
        'search'  => ['method' => 'GET', 'url' => $base . '/search.php?q=keyword', 'description' => 'Search products by name or description.'],
    ],
];

echo json_encode([
    'api'       => 'Smart Mall REST API',
    'version'   => '1.0.0',
    'base_url'  => $base,
    'endpoints' => $endpoints,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
