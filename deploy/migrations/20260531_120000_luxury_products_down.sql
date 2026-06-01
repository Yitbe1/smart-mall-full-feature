-- Down: Remove seeded luxury demo products.
-- Abort if any seeded product has been purchased so order history is not orphaned.

INSERT INTO products (name, price)
SELECT NULL, 0
WHERE EXISTS (
    SELECT 1
    FROM order_items oi
    JOIN products p ON p.product_id = oi.product_id
    WHERE p.image LIKE 'lux_20260531\_%' ESCAPE '\\'
    LIMIT 1
);

DELETE FROM products
WHERE image LIKE 'lux_20260531\_%' ESCAPE '\\';
