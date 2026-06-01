CREATE TABLE IF NOT EXISTS admin_promotion_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_user_id INT NOT NULL,
    target_user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    completed TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_token (token),
    KEY idx_admin_user_id (admin_user_id),
    KEY idx_target_user_id (target_user_id),
    CONSTRAINT fk_admin_user FOREIGN KEY (admin_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_target_user FOREIGN KEY (target_user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
