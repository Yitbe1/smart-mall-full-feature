# Smart Mall — Deployment Guide

> **Document Version:** 1.0  
> **Applies to:** Smart Mall v1.0+  
> **Last Updated:** June 2026  
> **Document ID:** DEPLOY-GUIDE-001  
> **Cross-Reference:** See *MASTER_DOCUMENTATION.md* Chapter 6 (Deployment & Maintenance) for architectural context.

---

## Table of Contents

1. [Overview](#1-overview)
2. [System Requirements](#2-system-requirements)
3. [Environment Configuration](#3-environment-configuration)
4. [Local Development Setup](#4-local-development-setup)
5. [Production Deployment](#5-production-deployment)
6. [Database & Migration Management](#6-database--migration-management)
7. [Security Considerations](#7-security-considerations)
8. [Backup & Disaster Recovery](#8-backup--disaster-recovery)
9. [Troubleshooting](#9-troubleshooting)

---

## 1. Overview

This guide covers deploying the Smart Mall e-commerce platform across local, staging, and production environments. Smart Mall is a PHP 8.x application running on Apache with a MySQL 8.x database.

The deployment pipeline consists of three phases:

| Phase | Script | Purpose |
|-------|--------|---------|
| Init | `deploy/init.sh` | First-time environment setup |
| Migration | `deploy/migrate.php` | Schema and seed data management |
| Validation | `deploy/deploy.sh` | Pre-deployment quality checks |

### 1.1 Deployment Architecture

```
┌─────────────────────────────────────────────┐
│                 Web Server                    │
│        Apache 2.4 with mod_rewrite            │
├─────────────────────────────────────────────┤
│            PHP 8.x Application                │
│     config.php → includes/db.php              │
│      .env → parse_ini_file()                   │
├─────────────────────────────────────────────┤
│              MySQL 8.x Database                │
│           smartmall_db (InnoDB)               │
└─────────────────────────────────────────────┘
```

**Key Design Decisions:**
- **No framework** — Procedural PHP using `require_once` includes
- **No remote deployment script** — `deploy.sh` is a local validation pipeline (`deploy/deploy.sh`)
- **No CDN** — Assets served from `assets/` with `?v=APP_VERSION` cache busting (`config.php`)
- **No containerization** — Native Apache/PHP/MySQL stack

---

## 2. System Requirements

### 2.1 Minimum Requirements

| Component | Requirement | Verified By |
|-----------|-------------|-------------|
| PHP | 8.0+ (8.2+ recommended) | `deploy/deploy.sh` |
| MySQL | 8.0+ (or MariaDB 10.5+) | `deploy/deploy.sh` |
| Apache | 2.4 with `mod_rewrite`, `mod_authz_core` | Root `.htaccess` |
| Memory | 256 MB (PHP), 1 GB (MySQL) | — |
| Disk | 500 MB + upload capacity | — |

### 2.2 Required PHP Extensions

| Extension | Used By | File |
|-----------|---------|------|
| `pdo_mysql` | Database connection | `includes/db.php` |
| `mysqli` | Database connection (fallback) | `includes/db.php` |
| `pdo` | Migration runner | `deploy/migrate.php` |
| `gd` | Image processing (future use) | — |
| `json` | API responses | All `api/*.php` |
| `session` | User sessions | `config.php` |
| `ctype` | Input validation | Various |
| `mbstring` | UTF-8 string handling | Various |
| `curl` | Chapa API + Brevo SMTP | `helpers/mail.php`, `chapa_pay/` |
| `fileinfo` | File upload validation | `admin/includes/product_handler.php` |

### 2.3 Network Requirements

| Direction | Protocol | Port | Purpose |
|-----------|----------|------|---------|
| Outbound | HTTPS | 443 | Chapa payment API |
| Outbound | HTTPS | 443 | Brevo SMTP API |
| Outbound | HTTPS | 443 | reCAPTCHA verification |
| Outbound | HTTPS | 443 | Google Sign-In |
| Outbound | HTTPS | 443 | Firebase Cloud Messaging |
| Inbound | HTTP/HTTPS | 80/443 | Web traffic |
| Inbound | TCP | 3306 | MySQL (local only) |

---

## 3. Environment Configuration

### 3.1 `.env` Reference

| Variable | Required | Default | Source File | Purpose |
|----------|----------|---------|-------------|---------|
| `APP_ENV` | Yes | `development` | `config.php` | Controls CSP, error display, HTML minification |
| `DB_HOST` | Yes | `localhost` | `includes/db.php` | MySQL host |
| `DB_NAME` | Yes | `smartmall_db` | `includes/db.php` | Database name |
| `DB_USER` | Yes | `root` | `includes/db.php` | Database user |
| `DB_PASS` | Yes | (empty) | `includes/db.php` | Database password |
| `CHAPA_SECRET_KEY` | Yes | — | `chapa_pay/chapa-config.php` | Chapa payment API key |
| `BREVO_API_KEY` | No | — | `helpers/mail.php` | Brevo API key for email |
| `SMTP_HOST` | No* | `smtp-relay.brevo.com` | `helpers/mail.php` | SMTP server |
| `SMTP_PORT` | No* | `587` | `helpers/mail.php` | SMTP port |
| `SMTP_USER` | No* | — | `helpers/mail.php` | SMTP username |
| `SMTP_PASS` | No* | — | `helpers/mail.php` | SMTP password |
| `SMTP_FROM` | No* | — | `helpers/mail.php` | From address |
| `RECAPTCHA_SITE_KEY` | No | — | `helpers/captcha.php`, `login.php` | reCAPTCHA v3 site key |
| `RECAPTCHA_SECRET_KEY` | No | — | `helpers/captcha.php` | reCAPTCHA v3 secret key |
| `FIREBASE_API_KEY` | No | — | `includes/header.php` | Firebase API key |
| `FIREBASE_PROJECT_ID` | No | — | `includes/header.php` | Firebase project ID |
| `FIREBASE_APP_ID` | No | — | `includes/header.php` | Firebase app ID |
| `FIREBASE_VAPID_KEY` | No | — | `includes/header.php` | Firebase VAPID key for push |

> *SMTP is required for transactional emails (verification, password reset, order confirmation). The app degrades gracefully — registration and checkout succeed without SMTP, but the user won't receive email notifications.

### 3.2 Environment-Specific Configuration

| Setting | Development | Staging | Production |
|---------|-------------|---------|------------|
| `APP_ENV` | `development` | `staging` | `production` |
| Error display | `config.php` shows errors | Hidden | Hidden |
| HTML minification | Off | Off | On (`config.php`) |
| CSP | Lax | Strict | Strict (`config.php`) |
| Chapa key | Test (`CHASECK_TEST-*`) | Test | Live |
| reCAPTCHA keys | Test | Test | Production |
| Session cookie secure | Off | On | On |

### 3.3 Environment Detection

The environment is detected through `APP_ENV` in `.env` (`config.php`):

```php
// config.php
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $_ENV = parse_ini_file($envFile) ?: [];
    foreach ($_ENV as $key => $value) {
        putenv("$key=$value");
    }
}
```

The `APP_ENV` value controls:
- CSP header strictness (`config.php`)
- HTML output minification (`config.php`)
- Error handler behavior (`config.php`)

### 3.4 Chapa Payment Configuration

Chapa is configured in `chapa_pay/chapa-config.php`. The project ships with a **test key**:

```ini
CHAPA_SECRET_KEY=CHASECK_TEST-npPtwYgafr7v8Om7tDhyfLovM3zqbmdk
```

**To switch to live:**
1. Obtain your live secret key from [Chapa Dashboard](https://dashboard.chapa.co)
2. Update `CHAPA_SECRET_KEY` in `.env`
3. Update the callback URL in `chapa_pay/callback.php`
4. In production, Chapa webhook URLs must be configured in the Chapa dashboard

---

## 4. Local Development Setup

### 4.1 Prerequisites

Ensure the following are installed:

```bash
# Check PHP version
php --version
# Must be 8.0+

# Check MySQL
mysql --version

# Check Apache
apache2ctl -v
```

### 4.2 Quick Start with `init.sh`

The `init.sh` script handles first-time setup (`deploy/init.sh`):

```bash
cd /path/to/smartmall
bash deploy/init.sh
```

This script performs the following steps:

| Step | Lines | Action |
|------|-------|--------|
| 1 | 20-39 | Creates `.env` from `.env.example` or writes a minimal default |
| 2 | 41-42 | Creates `logs/` and `backups/` directories |
| 3 | 44-46 | Sets `chmod 755` on both directories |
| 4 | 48-49 | Creates `.gitkeep` files |
| 5 | 52-54 | Displays PHP version |
| 6 | 57-61 | Prompts to run migrations |

**Note:** The default `.env` created by `init.sh` (`deploy/init.sh`) contains only:

```ini
DB_HOST=localhost
DB_NAME=smartmall_db
DB_USER=root
DB_PASS=
CHAPA_SECRET_KEY=your_chapa_secret_key_here
APP_ENV=development
```

You **must** edit `.env` with real values before running migrations.

### 4.3 Manual Setup

If you prefer to set up manually:

```bash
# 1. Clone the repository
git clone <repository-url> smartmall
cd smartmall

# 2. Create directories
mkdir -p logs backups
chmod 755 logs backups
touch logs/.gitkeep backups/.gitkeep

# 3. Create .env from example
cp .env.example .env
# Or create from scratch (see Section 3)

# 4. Install PHP dependencies
php composer install

# 5. Run database migrations
php deploy/migrate.php

# 6. Configure Apache virtual host (see Section 4.4)
```

### 4.4 Apache Virtual Host Configuration

**Development (XAMPP/LAMP):**

```apache
<VirtualHost *:80>
    ServerName smartmall.local
    DocumentRoot "/opt/lampp/htdocs/reference"
    
    <Directory "/opt/lampp/htdocs/reference">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog "/opt/lampp/logs/smartmall-error.log"
    CustomLog "/opt/lampp/logs/smartmall-access.log" combined
</VirtualHost>
```

> [!IMPORTANT] The `.htaccess` file at the document root (`reference/.htaccess`) requires `AllowOverride All` for these directives to work:

```
<FilesMatch "\.env">           (line 1-4)
    Require all denied
</FilesMatch>

<FilesMatch "composer\.(json|lock)|package(-lock)?\.json|yarn\.lock">  (line 6-9)
    Require all denied
</FilesMatch>
```

### 4.5 Installing PHP Dependencies

```bash
# Using the bundled composer phar
php composer install
```

This installs two dependencies (`composer.json`):

| Package | Version | Purpose |
|---------|---------|---------|
| `getbrevo/brevo-php` | ^4.0 | Brevo transactional email API |
| `guzzlehttp/guzzle` | ^7.10 | HTTP client (dependency of brevo-php) |

---

## 5. Production Deployment

### 5.1 Pre-Deployment Checklist

- [ ] `.env` configured with production values (see Section 3)
- [ ] `APP_ENV=production` set in `.env`
- [ ] HTTPS certificate installed and enforced
- [ ] Database migrations have been run
- [ ] File permissions are correct
- [ ] PHP extensions verified
- [ ] Composer dependencies installed (no dev dependencies)
- [ ] Chapa payment configured with **live** API keys
- [ ] reCAPTCHA configured with **production** site/secret keys
- [ ] SMTP credentials verified by sending a test email
- [ ] `logs/` directory writable by web server user
- [ ] `uploads/` directory writable by web server user

### 5.2 Deployment Script: `deploy.sh`

The main deployment validation script (`deploy/deploy.sh`) performs these checks:

```bash
# Full deployment (lint + tests + migration)
bash deploy/deploy.sh

# Quick deployment (skip tests)
bash deploy/deploy.sh --quick

# Migration-only (skip lint + tests)
bash deploy/deploy.sh --migrate

# Dry run (preview without executing)
bash deploy/deploy.sh --dry-run

# Lint specific files only
bash deploy/deploy.sh path/to/file1.php path/to/file2.php
```

**What the script does (`deploy/deploy.sh`):**

| Phase | Lines | Action | Fail Mode |
|-------|-------|--------|-----------|
| Prerequisites | 78-88 | Checks `php` and `mysql` CLIs exist | Fatal error |
| Config check | 91-100 | Lints `config.php` with `php -l` | Fatal error |
| PHP Lint | 102-129 | Lints all `.php` files (excludes `vendor/`, `node_modules/`, `cache/`) | Warnings only |
| Tests | 132-142 | Runs `_dev/tests/run.php` | Skipped with `--quick` or `--migrate` |
| Migrations | 145-147 | Runs `deploy/migrate.php` | Fatal error on failure |

**Flags:**

| Flag | Description |
|------|-------------|
| `--dry-run` | Print steps without executing |
| `--migrate` | Skip tests, run only migrations |
| `--quick` | Skip tests, run migrations |

### 5.3 Manual Production Deployment

For environments where the script cannot be used directly:

```bash
# 1. Copy files to production
rsync -avz --exclude='.env' --exclude='logs/' --exclude='cache/' \
  --exclude='node_modules/' --exclude='_dev/' \
  ./ user@server:/var/www/smartmall/

# 2. Set file permissions
find /var/www/smartmall -type f -exec chmod 644 {} \;
find /var/www/smartmall -type d -exec chmod 755 {} \;
chmod 775 /var/www/smartmall/logs
chmod 775 /var/www/smartmall/uploads

# 3. Run PHP lint
find /var/www/smartmall -name '*.php' -not -path '*/vendor/*' \
  -not -path '*/cache/*' -exec php -l {} \; 2>&1 | grep -v 'No syntax errors'

# 4. Install production dependencies
cd /var/www/smartmall
php composer install --no-dev --optimize-autoloader

# 5. Run migrations
php deploy/migrate.php
```

### 5.4 Server-Level Configuration

#### PHP Configuration (`php.ini`)

| Directive | Development | Production | Reason |
|-----------|-------------|------------|--------|
| `display_errors` | On | Off | `config.php` handles error display |
| `error_reporting` | E_ALL | E_ALL & ~E_DEPRECATED | — |
| `upload_max_filesize` | 64M | 64M | 50MB video upload limit (`admin/includes/product_handler.php`) |
| `post_max_size` | 70M | 70M | Must exceed upload_max_filesize |
| `max_execution_time` | 120 | 120 | Chapa callback processing |
| `session.gc_maxlifetime` | 1800 | 1800 | 30-minute idle timeout (`config.php`) |
| `memory_limit` | 256M | 256M | — |
| `date.timezone` | UTC | UTC | — |

#### Apache Modules

Ensure these modules are enabled:

```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod expires
sudo systemctl restart apache2
```

### 5.5. Deploying to a Subdirectory

Smart Mall can run in a server subdirectory (e.g., `http://localhost/reference/`).

The `BASE_PATH` is **auto-detected** (`config.php`) using:

```php
// config.php
$docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
$scriptDir = dirname(__DIR__);
return str_replace($docRoot, '', $scriptDir) . '/';
```

This computes the path by subtracting `DOCUMENT_ROOT` from `__DIR__`. On a standard LAMP stack where the project lives at `/opt/lampp/htdocs/reference`, this yields `/reference/`.

**No manual `BASE_PATH` configuration is needed** in `.env`.

---

## 6. Database & Migration Management

### 6.1 Migration System

The migration runner (`deploy/migrate.php`) manages database schema changes through versioned SQL files.

#### How It Works

1. Creates a `schema_migrations` tracking table (`deploy/migrate.php`):

```sql
CREATE TABLE IF NOT EXISTS schema_migrations (
    version VARCHAR(20) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    duration_ms INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

2. Scans `deploy/migrations/` for SQL files matching the pattern `YYYYMMDD_HHMMSS_description.sql`
3. Compares file versions to the `schema_migrations` table
4. Executes pending migrations in version order

#### Migration File Format

```
YYYYMMDD_HHMMSS_descriptive_name.sql       # Up migration
YYYYMMDD_HHMMSS_descriptive_name_down.sql  # Down migration (optional)
```

**Example:**

```
20260528_120000_initial_schema.sql
20260528_120000_initial_schema_down.sql
20260529_120000_email_verification.sql
20260529_120000_email_verification_down.sql
```

#### Running Migrations

```bash
# Run all pending migrations
php deploy/migrate.php

# Rollback the most recent migration
php deploy/migrate.php --down

# Show help
php deploy/migrate.php --help
```

> [!IMPORTANT] The rollback feature (`deploy/migrate.php`) only reverts the **single most recent** migration. There is no chain rollback support.

### 6.2 Migration Inventory

| File | Lines | Type | Description |
|------|-------|------|-------------|
| `20260528_120000_initial_schema.sql` | 145 | Schema | 11 tables: categories, users, products, cart, orders, order_items, payments, password_resets, newsletters, wishlist, reviews. FULLTEXT index on `products(name, description)`. |
| `20260529_120000_email_verification.sql` | 4 | Alter | Adds `email_verified_at`, `verification_token` (VARCHAR 64), unique index on token |
| `20260529_130000_verification_expiry.sql` | 3 | Alter | Adds `verification_token_expires_at` (TIMESTAMP), index on it |
| `20260529_140000_contact_messages.sql` | 8 | Create | `contact_messages` table: id, name, email, subject, message, created_at |
| `20260529_150000_admin_promotion_tokens.sql` | 14 | Create | `admin_promotion_tokens` with FK to `users` for admin/user promotion |
| `20260530_100000_notifications.sql` | 8 | Create | `notifications` table: id, type, message, link, is_read, created_at |
| `20260530_google_id.sql` | 8 | Alter | Adds `google_id` (VARCHAR 255, UNIQUE) to `users`. **No down file exists.** |
| `20260531_120000_luxury_products.sql` | 257 | Seed | 50 luxury demo products across 4 categories (fashion 15, electronics 15, home 15, beauty 5). Idempotent via `WHERE NOT EXISTS`. |
| `20260531_130000_notifications_is_read_index.sql` | 1 | Alter | Adds index `idx_is_read` on `notifications.is_read` |
| `20260602_100000_device_tokens.sql` | 13 | Create | `device_tokens` table for FCM push: id, user_id, fcm_token (UNIQUE), platform, timestamps |

**Down files exist for all migrations** except `20260530_google_id.sql` — the revert SQL is documented only as a comment.

### 6.3 Creating a New Migration

```bash
# Create the up migration
touch deploy/migrations/$(date +%Y%m%d_%H%M%S)_description.sql

# Create the down migration
touch deploy/migrations/$(date +%Y%m%d_%H%M%S)_description_down.sql
```

**Convention:**
- All tables use `InnoDB` engine and `utf8mb4` charset
- Use `IF NOT EXISTS` / `IF EXISTS` for idempotency
- Wrap DDL changes that might fail (e.g., adding existing columns in concurrent deploys)
- For seed data, use `INSERT ... WHERE NOT EXISTS` to prevent duplicates on re-run

### 6.4 Seed Data Note

The only seed data migration is `20260531_120000_luxury_products.sql` (257 lines). It inserts 50 luxury products with image references stored as `lux_20260531_*.webp` in the `uploads/` directory.

The corresponding down migration (`20260531_120000_luxury_products_down.sql`) has a **guard clause** (`deploy/migrate.php`): if any seeded product has been purchased, the rollback is blocked and displays a warning.

---

## 7. Security Considerations

### 7.1 File Permissions

| Path | Type | Permission | Owner |
|------|------|------------|-------|
| All `.php` files | File | 644 | `deploy:deploy` |
| Directories | Dir | 755 | `deploy:deploy` |
| `.env` | File | 600 | `deploy:deploy` |
| `logs/` | Dir | 775 | `deploy:www-data` |
| `uploads/` | Dir | 775 | `deploy:www-data` |
| `cache/` | Dir | 775 | `deploy:www-data` |
| `backups/` | Dir | 755 | `deploy:deploy` |

### 7.2 `.htaccess` Security Rules

**Root `.htaccess` (`reference/.htaccess`):**

| Lines | Rule | Purpose |
|-------|------|---------|
| 1-4 | Deny access to `.env` | Prevents credential exposure |
| 6-9 | Deny access to `composer.*`, `package*.json` | Prevents dependency info leaks |

**`deploy/.htaccess`:**

| Lines | Rule | Purpose |
|-------|------|---------|
| 1-6 | Deny all access to `deploy/` | Protects migration files, credentials |

**`api/.htaccess`:**

| Lines | Rule | Purpose |
|-------|------|---------|
| 1-6 | Deny all except `search.php` | Prevents direct API file access |

> [!NOTE]
> The root `.htaccess` does **not** include HTTPS redirect, cache headers, or `ErrorDocument` directives. These should be configured at the server/virtual-host level.

### 7.3 Session Security

Session hardening is handled in `config.php`:

```php
// config.php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', 1);
}
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
```

**Additional protections:**
- 30-minute idle timeout (`config.php`)
- Session ID regeneration every ~540s (`config.php`)
- CSRF token helpers: `csrf_token()`, `csrf_field()`, `csrf_verify()` in `includes/db.php`

### 7.4 Security Headers

In production mode (`config.php`):

| Header | Value |
|--------|-------|
| `X-Content-Type-Options` | `nosniff` |
| `X-Frame-Options` | `SAMEORIGIN` |
| `Content-Security-Policy` | (see below, line 157) |
| `Referrer-Policy` | `strict-origin-when-cross-origin` |
| `Permissions-Policy` | (restricts camera, microphone, geolocation) |

**Production CSP (`config.php`):**

```txt
default-src 'self';
script-src 'self' 'unsafe-inline' https://checkout.chapa.co
    https://accounts.google.com https://www.google.com https://www.gstatic.com;
style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;
font-src 'self' https://fonts.gstatic.com;
img-src 'self' data: https://www.google.com https://www.gstatic.com;
frame-src https://checkout.chapa.co https://www.google.com
    https://accounts.google.com https://www.gstatic.com;
connect-src 'self' https://accounts.google.com https://www.google.com
    https://www.gstatic.com;
```

### 7.5 Input Validation & SQL Injection Prevention

| Protection | Implementation | Location |
|-----------|---------------|----------|
| Prepared statements | PDO with named parameters | `includes/db.php` |
| SQL injection proof | Parameterized queries everywhere | All pages |
| XSS prevention | `htmlspecialchars()` on all output | Template files |
| CSRF tokens | `csrf_token()` / `csrf_verify()` | `includes/db.php` |
| File upload validation | MIME type + extension + size check | `admin/includes/product_handler.php` |
| Payment integrity | Chapa callback verification | `chapa_pay/callback.php` |

### 7.6 Additional Security Recommendations

1. **Rate limiting**: Not currently implemented. Consider adding to `api/` endpoints and login page.
2. **Fail2ban**: Configure to block repeated failed login attempts.
3. **DB encryption**: Enable MySQL TDE or filesystem-level encryption for the database.
4. **Backup encryption**: Encrypt backup files before transferring off-server.
5. **WAF**: Consider Cloudflare or ModSecurity for production deployments.
6. **PHP open_basedir**: Restrict PHP file access to web root.

---

## 8. Backup & Disaster Recovery

### 8.1 Current State

The `backups/` directory exists but **no automated backup mechanism is implemented**. The `init.sh` script creates the directory structure (`deploy/init.sh`), but there is no cron job, `mysqldump` script, or file archiving in place.

### 8.2 Recommended Backup Strategy

#### Database Backups

Create a `backup.sh` script (not included):

```bash
#!/bin/bash
# Database backup script — add to crontab

set -euo pipefail

BACKUP_DIR="/var/www/smartmall/backups"
DB_NAME="smartmall_db"
DB_USER="root"
DB_PASS="$(grep DB_PASS /var/www/smartmall/.env | cut -d= -f2)"
RETENTION_DAYS=30
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Dump database
mysqldump --user="$DB_USER" --password="$DB_PASS" \
  --routines --triggers --single-transaction \
  "$DB_NAME" | gzip > "$BACKUP_DIR/db_$TIMESTAMP.sql.gz"

# Encrypt (optional)
# gpg --encrypt --recipient admin@example.com \
#   "$BACKUP_DIR/db_$TIMESTAMP.sql.gz"

# Rotate old backups
find "$BACKUP_DIR" -name 'db_*.sql.gz' -mtime +$RETENTION_DAYS -delete

echo "Backup complete: db_$TIMESTAMP.sql.gz"
```

**Cron schedule (daily at 2 AM):**

```cron
0 2 * * * /var/www/smartmall/backup.sh >> /var/log/smartmall-backup.log 2>&1
```

#### File Backups

```bash
# Backup uploads and configuration
tar -czf "$BACKUP_DIR/files_$TIMESTAMP.tar.gz" \
  /var/www/smartmall/uploads \
  /var/www/smartmall/.env
```

### 8.3 Disaster Recovery Steps

| Scenario | Recovery Procedure |
|----------|--------------------|
| **Database corruption** | 1. Restore latest `db_*.sql.gz` with `gunzip -c \| mysql`<br>2. Run `php deploy/migrate.php` to catch any missing migrations |
| **File corruption** | 1. Restore from git: `git checkout -- .`<br>2. Reinstall composer: `php composer install --no-dev` |
| **Complete server failure** | 1. Provision new server<br>2. Clone repository<br>3. Restore database from backup<br>4. Restore `uploads/` directory<br>5. Run migrations<br>6. Update DNS |
| **Accidental data deletion** | 1. Restore specific tables from backup<br>2. Or restore full database to a temporary DB and export specific rows |

---

## 9. Troubleshooting

### 9.1 Deployment Issues

| Symptom | Likely Cause | Fix |
|---------|-------------|-----|
| `deploy.sh` fails at "Prerequisites" | PHP or MySQL CLI not found | Set `$PHP_BIN` and `$MYSQL_BIN` env vars, or install missing CLI tools |
| Migration fails with "could not find driver" | Missing `pdo_mysql` PHP extension | Install: `sudo apt install php-mysql` or `sudo docker-php-ext-install pdo_mysql` |
| `php -l` lint errors in deploy.sh | PHP syntax error in committed file | Fix the file; lint failures are warnings, not fatal, but should be addressed |
| "No such file or directory" on deploy paths | Script run from wrong directory | Always run `deploy.sh` from the project root |

### 9.2 Database Issues

| Symptom | Likely Cause | Fix |
|---------|-------------|-----|
| "Connection refused" in `includes/db.php` | MySQL not running | `sudo systemctl start mysql` or check `mysqld` |
| "Access denied for user" in `includes/db.php` | Wrong DB credentials in `.env` | Verify `DB_USER`/`DB_PASS` — `.env` is the single source of truth |
| "Base table or view not found" | Migrations not run | `php deploy/migrate.php` |
| Migration skips files | Already recorded in `schema_migrations` | Check `SELECT * FROM schema_migrations`; delete entry to re-run |
| Rollback `--down` does nothing | No migrations to roll back, or seed data has been purchased (guard blocks down) | Check latest migration; if blocked by purchased products, create a compensating migration |

### 9.3 File Permission Issues

| Symptom | Likely Cause | Fix |
|---------|-------------|-----|
| "Failed to open stream: Permission denied" in `logs/` | Web server can't write to logs | `chmod 775 logs/ && chown www-data:www-data logs/` |
| File upload fails in `admin/includes/product_handler.php` | `uploads/` not writable | `chmod 775 uploads/` |
| `.env` readable by web users | Wrong file permissions | `chmod 600 .env` |
| `.htaccess` not working | `AllowOverride` not set | Add `AllowOverride All` to the `<Directory>` block in Apache config |

### 9.4 Payment Issues

| Symptom | Likely Cause | Fix |
|---------|-------------|-----|
| Chapa checkout page doesn't load | CSP blocking `checkout.chapa.co` | Check CSP in `config.php`; ensure `frame-src` includes Chapa |
| "Invalid response" from Chapa callback | `CHAPA_SECRET_KEY` mismatch between `.env` and Chapa dashboard | Verify the key matches exactly, including test vs live |
| Callback URL 404 | Chapa webhook URL not configured | Set callback URL in Chapa dashboard to `https://yourdomain.com/chapa_pay/callback.php` |

### 9.5 Email Issues

| Symptom | Likely Cause | Fix |
|---------|-------------|-----|
| Verification email not sent | SMTP credentials missing or wrong | Verify `BREVO_API_KEY` and/or `SMTP_*` vars in `.env` |
| "Failed to send email" in `helpers/mail.php` | Brevo API quota exceeded or key invalid | Check Brevo dashboard for API limits |
| Emails going to spam | Missing SPF/DKIM records | Add SPF and DKIM DNS records for your sending domain |

### 9.6 Health Check

The health check endpoint (`health.php`) provides a JSON status report (admin-only):

```bash
# Requires admin session cookie
curl -X GET http://localhost/reference/health.php \
  -b "PHPSESSID=your_session_id"
```

**Success response (from `health.php`):**

```json
{
    "status": "ok",
    "app": "Smart Mall",
    "checks": {
        "database": {
            "status": "ok",
            "query_time_ms": 2.15,
            "product_count": 50,
            "order_count": 0,
            "user_count": 1
        },
        "php_version": "8.2.x",
        "server_time": "2026-06-04T10:30:00+00:00",
        "memory_mb": 2.5
    }
}
```

**Degraded response (database down — `health.php`):**

```json
{
    "status": "degraded",
    "app": "Smart Mall",
    "checks": {
        "database": {
            "status": "error",
            "message": "SQLSTATE[HY000] [2002] Connection refused"
        },
        "php_version": "8.2.x",
        "server_time": "2026-06-04T10:30:00+00:00",
        "memory_mb": 2.5
    }
}
```

---

*End of Deployment Guide*
