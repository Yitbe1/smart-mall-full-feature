ALTER TABLE users
  ADD COLUMN email_verified_at TIMESTAMP NULL DEFAULT NULL AFTER role,
  ADD COLUMN verification_token VARCHAR(64) DEFAULT NULL AFTER email_verified_at,
  ADD UNIQUE KEY idx_verification_token (verification_token);
