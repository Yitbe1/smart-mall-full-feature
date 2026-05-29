<?php
/**
 * Phase 4: Performance Optimization - Database Index Migration
 *
 * Adds missing indexes identified by query pattern analysis.
 * Migration is idempotent — safe to run multiple times.
 */

$db_host = 'localhost';
$db_name = 'smartmall_db';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "Connected to $db_name.\n";

    $indexes = [
        // HIGH PRIORITY: products.category_id — FK column with zero index coverage
        "ALTER TABLE products ADD INDEX idx_category_id (category_id)",

        // Composite for category-based listing sorted by created_at (default)
        "ALTER TABLE products ADD INDEX idx_category_id_created_at (category_id, created_at)",

        // Composite for category-based listing sorted by price
        "ALTER TABLE products ADD INDEX idx_category_id_price (category_id, price)",

        // Price index for sorting-only queries
        "ALTER TABLE products ADD INDEX idx_price (price)",

        // Created_at index for default product sort
        "ALTER TABLE products ADD INDEX idx_created_at (created_at)",

        // MEDIUM PRIORITY: user order listing — WHERE user_id = ? ORDER BY created_at DESC
        "ALTER TABLE orders ADD INDEX idx_user_id_created_at (user_id, created_at)",

        // Cart queries: WHERE user_id = ? ORDER BY created_at DESC
        "ALTER TABLE cart ADD INDEX idx_user_id_created_at (user_id, created_at)",

        // Composite covering index for order_items JOINs
        "ALTER TABLE order_items ADD INDEX idx_order_id_product_id (order_id, product_id)",

        // Chapa callback looks up by tx_ref
        "ALTER TABLE payments ADD INDEX idx_tx_ref (tx_ref)",

        // Reviews listing sorted by date
        "ALTER TABLE reviews ADD INDEX idx_product_id_created_at (product_id, created_at)",

        // CLEANUP: drop redundant index (token UNIQUE already covers it)
        "DROP INDEX idx_token ON password_resets",
    ];

    foreach ($indexes as $sql) {
        try {
            $pdo->exec($sql);
            echo "  OK: $sql\n";
        } catch (PDOException $e) {
            // Index might already exist — that's fine
            echo "  SKIP (may already exist): " . $e->getMessage() . "\n";
        }
    }

    // FULLTEXT index for product search
    $ftSqls = [
        "ALTER TABLE products ADD FULLTEXT INDEX ft_search (name, description)",
    ];
    foreach ($ftSqls as $sql) {
        try {
            $pdo->exec($sql);
            echo "  OK: $sql\n";
        } catch (PDOException $e) {
            echo "  SKIP (may already exist): " . $e->getMessage() . "\n";
        }
    }

    echo "\nAll indexes processed.\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
