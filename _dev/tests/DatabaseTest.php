<?php

require_once __DIR__ . '/TestRunner.php';

class DatabaseTest
{
    private ?PDO $pdo = null;

    public function setUp(): void
    {
        $env_file = __DIR__ . '/../.env';
        $env_vars = file_exists($env_file) ? parse_ini_file($env_file) : [];

        $host = $env_vars['DB_HOST'] ?? 'localhost';
        $port = $env_vars['DB_PORT'] ?? '3306';
        $name = $env_vars['DB_NAME'] ?? 'smartmall_db';
        $user = $env_vars['DB_USER'] ?? 'root';
        $pass = $env_vars['DB_PASS'] ?? '';

        $this->pdo = new PDO(
            "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }

    public function testConnectionIsPdo(): void
    {
        TestRunner::assertNotNull($this->pdo, 'PDO connection should not be null');
        TestRunner::assertTrue($this->pdo instanceof PDO, 'Connection should be PDO instance');
    }

    public function testProductsTableExists(): void
    {
        $stmt = $this->pdo->query("SHOW TABLES LIKE 'products'");
        TestRunner::assertTrue($stmt->rowCount() > 0, 'products table should exist');
    }

    public function testProductsHaveExpectedColumns(): void
    {
        $stmt = $this->pdo->query("DESCRIBE products");
        $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $expected = ['product_id', 'name', 'description', 'price', 'stock', 'image', 'category_id'];
        foreach ($expected as $col) {
            TestRunner::assertTrue(in_array($col, $cols), "products table should have column '$col'");
        }
    }

    public function testCategoriesTableExists(): void
    {
        $stmt = $this->pdo->query("SHOW TABLES LIKE 'categories'");
        TestRunner::assertTrue($stmt->rowCount() > 0, 'categories table should exist');
    }

    public function testUsersTableExists(): void
    {
        $stmt = $this->pdo->query("SHOW TABLES LIKE 'users'");
        TestRunner::assertTrue($stmt->rowCount() > 0, 'users table should exist');
    }

    public function testOrdersTableExists(): void
    {
        $stmt = $this->pdo->query("SHOW TABLES LIKE 'orders'");
        TestRunner::assertTrue($stmt->rowCount() > 0, 'orders table should exist');
    }

    public function testWishlistTableExists(): void
    {
        $stmt = $this->pdo->query("SHOW TABLES LIKE 'wishlist'");
        TestRunner::assertTrue($stmt->rowCount() > 0, 'wishlist table should exist');
    }

    public function testReviewsTableExists(): void
    {
        $stmt = $this->pdo->query("SHOW TABLES LIKE 'reviews'");
        TestRunner::assertTrue($stmt->rowCount() > 0, 'reviews table should exist');
    }

    public function testNewslettersTableExists(): void
    {
        $stmt = $this->pdo->query("SHOW TABLES LIKE 'newsletters'");
        TestRunner::assertTrue($stmt->rowCount() > 0, 'newsletters table should exist');
    }

    public function testProductsHaveData(): void
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM products");
        $count = (int)$stmt->fetchColumn();
        TestRunner::assertTrue($count > 0, "Expected products > 0, got $count");
    }

    public function testCategoriesHaveData(): void
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM categories");
        $count = (int)$stmt->fetchColumn();
        TestRunner::assertTrue($count > 0, "Expected categories > 0, got $count");
    }
}
