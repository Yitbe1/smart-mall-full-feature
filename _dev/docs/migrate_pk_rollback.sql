-- ================================================================
-- Rollback: Restore original PRIMARY KEY column names
-- ================================================================

USE smartmall_db;

ALTER TABLE users CHANGE user_id id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE categories CHANGE category_id id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE products CHANGE product_id id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE cart CHANGE cart_id id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE orders CHANGE order_id id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE order_items CHANGE order_item_id id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE payments CHANGE payment_id id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE password_resets CHANGE reset_id id INT(11) NOT NULL AUTO_INCREMENT;
