ALTER TABLE users
  DROP INDEX idx_verification_token,
  DROP COLUMN verification_token,
  DROP COLUMN email_verified_at;
