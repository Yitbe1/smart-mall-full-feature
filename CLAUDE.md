# Smart Mall Project

## Verified File Structure

```
reference/
  .env
  .gitignore
  .git/
  .htaccess
  _dev/                       # dev-only (docs, tests, Flutter, Docker)
  admin/
    includes/
      admin_nav.php
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
    images/
    js/
  backups/
  cache/
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
- All 9 files that require `includes/db.php` must load `.env` first
- Fixed: login.php, google_login.php, add_to_cart.php, mark_notification_read.php
- Fixed: api/categories.php, api/products.php, api/orders.php

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

## Key Conventions
- DB credentials: `.env` only, no fallback defaults
- BASE_PATH: auto-detected from `__DIR__` minus `DOCUMENT_ROOT`
- Asset URLs: use `get_product_image_url()` / `get_product_video_url()`, never hardcoded paths
- `.env` must be loaded before `require_once 'includes/db.php'` in every file
- `includes/header.php.current` is stale -- don't rename to header.php

## Memory Management
- Use `self-improving-agent` skill for memory curation: `/si:review`, `/si:promote`, `/si:status`
- Commands: `skill self-improving-agent` to load before running si:* commands
