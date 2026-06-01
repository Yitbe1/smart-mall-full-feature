ALTER TABLE users
  ADD COLUMN verification_token_expires_at TIMESTAMP NULL DEFAULT NULL AFTER verification_token,
  ADD INDEX idx_verification_expires (verification_token_expires_at);
