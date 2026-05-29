<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/db.php';

try {
    $pdo = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // ===================== GET =====================
    if ($method === 'GET') {
        // Single order with items
        if (isset($_GET['id'])) {
            $id = (int) $_GET['id'];

            $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = :id");
            $stmt->execute([':id' => $id]);
            $order = $stmt->fetch();

            if (!$order) {
                http_response_code(404);
                echo json_encode(['error' => 'Order not found']);
                exit;
            }

            $stmt = $pdo->prepare("
                SELECT oi.*, p.name AS product_name, p.image
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.product_id
                WHERE oi.order_id = :id
            ");
            $stmt->execute([':id' => $id]);
            $order['items'] = $stmt->fetchAll();

            echo json_encode($order);
            exit;
        }

        // Orders by user_id
        if (!isset($_GET['user_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required parameter: user_id']);
            exit;
        }

        $userId = (int) $_GET['user_id'];
        $stmt = $pdo->prepare("
            SELECT * FROM orders
            WHERE user_id = :uid
            ORDER BY created_at DESC
        ");
        $stmt->execute([':uid' => $userId]);
        $orders = $stmt->fetchAll();

        echo json_encode($orders);
        exit;
    }

    // ===================== POST =====================
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || empty($input['user_id']) || empty($input['items']) || !is_array($input['items'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request. Required: user_id (int) and items (array of {product_id, quantity})']);
            exit;
        }

        $userId = (int) $input['user_id'];

        // Validate that user exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_id = :uid");
        $stmt->execute([':uid' => $userId]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            exit;
        }

        $pdo->beginTransaction();

        try {
            $totalPrice = 0;
            $lineItems = [];

            foreach ($input['items'] as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $quantity  = (int) ($item['quantity'] ?? 0);

                if ($productId <= 0 || $quantity <= 0) {
                    throw new Exception('Invalid product_id or quantity');
                }

                $stmt = $pdo->prepare("SELECT product_id, name, price, stock FROM products WHERE product_id = :pid");
                $stmt->execute([':pid' => $productId]);
                $product = $stmt->fetch();

                if (!$product) {
                    throw new Exception("Product ID $productId not found");
                }

                if ($product['stock'] < $quantity) {
                    throw new Exception("Insufficient stock for product '{$product['name']}' (requested: $quantity, available: {$product['stock']})");
                }

                $lineTotal = $product['price'] * $quantity;
                $totalPrice += $lineTotal;

                $lineItems[] = [
                    'product_id' => $productId,
                    'quantity'   => $quantity,
                    'price'      => $product['price'],
                ];

                // Decrement stock
                $stmt = $pdo->prepare("UPDATE products SET stock = stock - :qty WHERE product_id = :pid");
                $stmt->execute([':qty' => $quantity, ':pid' => $productId]);
            }

            // Create order
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, total_price, status, created_at)
                VALUES (:uid, :total, 'pending', NOW())
            ");
            $stmt->execute([
                ':uid'   => $userId,
                ':total' => $totalPrice,
            ]);
            $orderId = (int) $pdo->lastInsertId();

            // Create order_items
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (:oid, :pid, :qty, :price)
            ");
            foreach ($lineItems as $li) {
                $stmt->execute([
                    ':oid'   => $orderId,
                    ':pid'   => $li['product_id'],
                    ':qty'   => $li['quantity'],
                    ':price' => $li['price'],
                ]);
            }

            $pdo->commit();

            http_response_code(201);
            echo json_encode([
                'success'    => true,
                'order_id'   => $orderId,
                'total_price' => (float) $totalPrice,
                'status'     => 'pending',
            ]);
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    // ===================== Other methods =====================
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    error_log('Smart Mall API (orders): ' . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
    error_log('Smart Mall API (orders): ' . $e->getMessage());
}
