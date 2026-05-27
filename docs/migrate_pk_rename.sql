-- ================================================================
-- Migration: Rename PRIMARY KEY columns to use explicit table-prefixed names
-- 
-- Changes:
--   users.id           → users.user_id
--   categories.id      → categories.category_id  
--   products.id        → products.product_id
--   cart.id            → cart.cart_id
--   orders.id          → orders.order_id
--   order_items.id     → order_items.order_item_id
--   payments.id        → payments.payment_id
--   password_resets.id → password_resets.reset_id
--
-- This migration is safe to run against the live smartmall_db.
-- Foreign key columns (user_id, product_id, category_id, order_id) 
-- are already correctly named and are NOT affected.
-- ================================================================

USE smartmall_db;

ALTER TABLE users CHANGE id user_id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE categories CHANGE id category_id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE products CHANGE id product_id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE cart CHANGE id cart_id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE orders CHANGE id order_id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE order_items CHANGE id order_item_id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE payments CHANGE id payment_id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE password_resets CHANGE id reset_id INT(11) NOT NULL AUTO_INCREMENT;
