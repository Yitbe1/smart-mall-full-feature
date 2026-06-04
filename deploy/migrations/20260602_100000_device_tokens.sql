-- Migration: Create device_tokens table for FCM push notifications
-- Created: 2026-06-02

CREATE TABLE IF NOT EXISTS `device_tokens` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `fcm_token` VARCHAR(500) NOT NULL,
    `platform` VARCHAR(20) DEFAULT 'android',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_token` (`fcm_token`),
    KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
