# Smart Mall Project

## Project Identity
- **Name:** Smart Mall (e-commerce)
- **Dev URL:** `http://localhost/reference` | **Prod URL:** `https://smartmall.unaux.com`
- **Stack:** PHP 8.x (procedural), MySQL, Apache, HTML/CSS/JS, reCAPTCHA v3, Brevo SMTP, Chapa payment
- **DB:** MySQL `smartmall_db` (credentials in `.env` only, no fallback defaults)
- **BASE_PATH:** Auto-detected in `config.php` from `__DIR__` minus `DOCUMENT_ROOT`
- **Git:** Single branch history, no remotes
- **Mobile:** Capacitor Android app loading prod URL via WebView

## Verified File Structure

```
reference/
  .env
  .env.example
  .gitignore
  .htaccess
  _dev/                       # dev-only (docs, tests, Docker)
  admin/
    includes/
      product_form.php
      product_handler.php
    add_product.php
    dashboard.php
    delete_product.php
    manage_categories.php
    manage_orders.php
    manage_products.php
    manage_users.php
    reports.php
  api/
    .htaccess
    categories.php
    index.php
    openapi.yaml
    orders.php
    products.php
    search.php
  assets/
    Google-play-icon.png
    chart.umd.min.js
    logo-icon.png
    logo.png
    qr-code.png
  backups/
  cache/
  capacitor_push_token.php
  capacitor/
    android/
    capacitor.config.json
    package.json
    www/
  chapa_pay/
    .htaccess
    callback.php
    chapa-config.php
  composer                     # PHP phar binary
  composer.json
  composer.lock
  credentials/
  deploy/
    .htaccess
    deploy.sh
    init.sh
    migrate.php
    migrations/
  errors/
    500.php
  helpers/
    captcha.php
    mail.php
  includes/
    .htaccess
    cache.php
    currency.php
    db.php
    footer.php
    header.php
    header.php.current          # stale backup -- skip on deploy
    seo.php
  logs/
  mail/
  uploads/
  vendor/
  about.php
  add_to_cart.php
  cart.php
  checkout.php
  config.php
  confirm_admin.php
  contact.php
  download.php
  download_app.php
  forgot_password.php
  google_login.php
  health.php
  index.php
  login.php
  logout.php
  manifest.json
  mark_notification_read.php
  offline.html
  order_confirmation.php
  orders.php
  product.php
  receipt.php
  register.php
  reset_password.php
  set_currency.php
  sitemap.xml
  SMART_MALL_COMPLETE_THESIS.md
  submit_review.php
  subscribe.php
  sw.js
  toggle_wishlist.php
  verify_email.php
  wishlist.php
```

## FCM & Google Sign-In (Capacitor Native)

### Google Sign-In
- `@capgo/capacitor-social-login` requires `SocialLogin.initialize()` before every `login()` call
- `webClientId` = **Web OAuth client ID** (type 1), NOT Android client ID (type 3)
  - Web (type 1): `1003727523085-ff6nuocamjk1mh8r51k6v94tcbsbsql1`
  - Android (type 3): `1003727523085-vk311f184eqrt95a3ggdq17h2fnqe5bl`
- Capacitor code guarded by `if (Capacitor.isNativePlatform())` — website path uses `google.accounts.id` in the `else` branch

### FCM Push Notifications
- `addListener('registration', ...)` must fire **before** `register()` to catch the token
- Token stored in `localStorage` → sent to `capacitor_push_token.php`; queued as `fcm_token_pending` if user not authenticated
- `device_tokens` table must exist (migration: `deploy/migrations/20260602_100000_device_tokens.sql`)
- `logout.php` wraps `DELETE FROM device_tokens` in try-catch (table may be missing)
- `sw.js` does NOT need `push`/`notificationclick` — `@capacitor/push-notifications` handles natively

### APK Build
- Source: `capacitor/` (not `_dev/smartmall-app/` — that was the old Flutter app)
- Command: `npx cap sync android && ./gradlew assembleRelease` in `capacitor/android/`
- Output: `capacitor/android/app/build/outputs/apk/release/app-release.apk`

## Recent Changes

### Moved dev-only files to `_dev/`
- Moved: smartmall-app/, tests/, docs/, thesis files, Docker files, AGENTS.md, install/
- `_dev/` is web-inaccessible via `.htaccess`
- `node_modules/` under `_dev/smartmall-app/` is gitignored
- `deploy/deploy.sh` references `_dev/tests/run.php`

### Fixed BASE_PATH
- File: `config.php`
- Uses `__DIR__` minus `DOCUMENT_ROOT` (NOT `dirname($_SERVER['SCRIPT_NAME'])`)
- Old approach gave wrong values on admin pages (`/reference/admin` instead of `/reference`)

### Fixed .env loading order
- All files that require `includes/db.php` must load `.env` first
- Fixed: login.php, google_login.php, add_to_cart.php, mark_notification_read.php
- Fixed: api/categories.php, api/products.php, api/orders.php
- Fixed: logout.php (was missing .env loading entirely — session)

### Removed hardcoded DB fallback defaults
- `includes/db.php` reads strictly from `$_ENV` with empty-string validation
- `deploy/migrate.php` reads strictly from `$_ENV` with empty-string validation
- `.env` is the single source of truth for DB credentials

### Removed hardcoded paths
- `admin/includes/product_form.php`: 3 `../uploads/` paths → `get_product_image_url()`
- `admin/manage_orders.php`: `../uploads/` path → `get_product_image_url()`
- `get_product_image_url()` and `get_product_video_url()` in `includes/db.php` use `BASE_PATH` instead of `$_SERVER['PHP_SELF']`

### Removed from .env
- `BASE_PATH=/reference` — now auto-detected
- `APP_URL=http://localhost/reference` — now auto-detected

### Fixed GSI iframe overlay in Capacitor
- `login.php` and `register.php`: Capacitor IIFE destroys GSI iframe (`credential_picker_container`) after initialization to prevent it from blocking button taps
- Website path (`google.accounts.id`) is unchanged

### Removed orphan fetch from header.php
- `includes/header.php`: deleted dead `fetch()` call that referenced undefined `token` variable, plus extra `});` brace that would break FCM block

### Fixed strings.xml client ID
- `capacitor/android/app/src/main/res/values/strings.xml`: `google_web_client_id` changed from Android client ID (type 3) to Web client ID (type 1)

## Key Conventions
- DB credentials: `.env` only, no fallback defaults
- BASE_PATH: auto-detected from `__DIR__` minus `DOCUMENT_ROOT`
- Asset URLs: use `get_product_image_url()` / `get_product_video_url()`, never hardcoded paths
- `.env` must be loaded before `require_once 'includes/db.php'` in every file
- `includes/header.php.current` is stale -- don't rename to header.php
- **No fabrication:** Never generate code samples, screenshots, or diagrams from memory. Use descriptive placeholders for anything not physically verified: `[Screenshot: <what it shows>]` or `[Code: <file> — <what it does>]`. Only paste exact code after reading the real file.

## Memory Management
- Use `self-improving-agent` skill for memory curation: `/si:review`, `/si:promote`, `/si:status`
- Commands: `skill self-improving-agent` to load before running si:* commands

## GSI Iframe Overlay Fix (Capacitor)
- Google Sign-In's declarative `g_id_signin` renders a GSI iframe that sits on top of the Capacitor web view, blocking taps on any button in the same area
- The Capacitor IIFE in `login.php` and `register.php` calls `document.getElementById('credential_picker_container')?.remove()` and `document.querySelector('iframe[src*="accounts.google.com/gsi"]')?.remove()` to destroy the overlay
- This runs inside the `if (Capacitor.isNativePlatform())` guard — website is unaffected
- Must preserve the `else` branch (website GSI), never remove or modify it

## Kiro Agent
- Located at `/home/test/.kiro/`
- Has its own skill collection (36+ engineering skills), agent definitions, and directives
- `coder` agent (ctrl+shift+x) is the primary coding agent with `fs_read`, `fs_write`, `grep`, `glob`, `code`, `execute_bash` tools
- Can be referenced for reusable skill patterns or agent configurations
