-- Add google_id column to users table for Google Sign-In
-- Allows linking Google accounts to user profiles and prevents re-registration

ALTER TABLE users
    ADD COLUMN google_id VARCHAR(255) NULL UNIQUE AFTER user_id;

-- Down migration:
-- ALTER TABLE users DROP COLUMN google_id;
