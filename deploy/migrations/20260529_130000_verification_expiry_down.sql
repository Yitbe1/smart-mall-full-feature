ALTER TABLE users
  DROP INDEX idx_verification_expires,
  DROP COLUMN verification_token_expires_at;
