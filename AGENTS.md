# AGENTS.md — Smart Mall Project

## Goal
- Add email verification on registration and fix email delivery using Brevo REST API SDK
- Ensure resend verification email works reliably
- Add reCAPTCHA v3 (invisible, score-based) and Google Sign-In (GIS) to auth pages

## Constraints & Preferences
- Follow existing code patterns (SHA-256 token hashing, same-site session handling)
- Reuse existing migration system (`deploy/migrations/` timestamped SQL files)
- Use `getbrevo/brevo-php` SDK with REST API (not raw SMTP)
- Use API key (`xkeysib-...`), not SMTP key (`xsmtpsib-...`), for REST API
- Install via Composer

## Progress
### Done
- Migration SQL (`20260529_120000_email_verification.sql`) adds `email_verified_at TIMESTAMP NULL` and `verification_token VARCHAR(64)` to `users` table, plus unique index on token
- Down migration for rollback
- Migration ran successfully — columns verified in `users` table
- `register.php`: after INSERT, generates token, stores SHA-256 hash, sends verification email, shows inline success message instead of redirect
- `verify_email.php` (new): reads `?token=`, hashes it, finds unverified user, sets `email_verified_at = NOW()`, clears token
- `login.php`: SELECT includes `email_verified_at`, rejects unverified logins
- `helpers/mail.php`: added `APP_ENV=development` shortcut that writes `.eml` files locally; production mode uses Brevo SDK via REST API
- `.env`: added `BREVO_API_KEY=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`; `SMTP_FROM=yitbarekteklu5@gmail.com`; `APP_ENV=production`
- Composer installed at project root (`composer` binary)
- `getbrevo/brevo-php` (v4.0.13) installed via Composer
- `guzzlehttp/guzzle` (v7.10.5) installed as PSR-18 HTTP client required by Brevo SDK
- `helpers/mail.php` refactored: removed raw SMTP (`stream_socket_client`, AUTH LOGIN, STARTTLS), replaced with `\Brevo\Brevo` client calling `sendTransacEmail()`
- `vendor/` already in `.gitignore`
- Resend in `login.php` changed from GET link (`?resend=1&email=`) to POST form with a dedicated resend button
- Resend button and feedback moved OUTSIDE the `$errors` block into their own `$unverified_email` alert block so they remain visible after resend succeeds (when `$errors` is empty)
- Removed `$errors['login']` registration error since the verification warning is now shown separately in the unverified block
- Resend now checks `send_mail()` return value — shows "Failed to send" instead of always claiming success
- Root cause of resend failure: `login.php` never loaded `.env`, so `send_mail()` had no API key/sender; fixed by loading `.env` inline in `login.php` (without requiring the full `config.php`)
- Consistent HTML styling applied across all three auth pages (login.php, register.php, forgot_password.php): added missing `<hr>` separator and "We'll never share your email" plain-text disclaimer to `login.php` to match the other two pages
- `forgot_password.php`: changed error message from generic "Email not found" to "No account found with that email address" when email doesn't exist; changed success message from vague confirmation to "A password reset link has been sent to your email"
- New migration (`20260529_130000_verification_expiry.sql`) adds `verification_token_expires_at TIMESTAMP` column
- Registration and resend both set `verification_token_expires_at = DATE_ADD(NOW(), INTERVAL 5 MINUTE)`
- `verify_email.php` checks expiry: expired links show error message and clear the token so user can re-request
- Email body text changed from "24 hours" to "5 minutes"
- `product.php`: fixed "An error occurred. Please try again later." crash when clicking products — column was `r.comment` in DB but query used `r.review`
- `submit_review.php`: same `review` → `comment` column fix in INSERT statement
- **Full codebase audit** — found and fixed 10+ bugs and security holes
- **Fixed `chapa_pay/callback.php`**: wrong column names and invalid ENUM values
- **Fixed `admin/manage_categories.php`**: undefined variable crash on category image update
- **Deleted `admin/reports_test.php`**: security hole (hardcoded admin session), dead code
- **Fixed `orders.php`**: removed paid-only filter so users see all their orders
- **Fixed `contact.php`**: added DB persistence, CSRF field, fixed CSP for Google Maps, real email recipient
- **Created migration** `20260529_140000_contact_messages.sql` for contact form storage
- **Deleted `check_schema.php`**: exposed DB column structure publicly
- **Secured sensitive dirs** (credentials/, deploy/, install/): added `.htaccess` deny all
- **Fixed `order_confirmation.php`**: removed instant redirect after Chapa payment verification so users see the confirmation page and can download receipts
- **Created `receipt.php`**: clean printable receipt page with Print/Save as PDF button
- **Created `admin/manage_users.php`**: list users, verify/unverify, toggle admin role, delete
- **Added Users link** to admin navbar and Users stat card to admin dashboard

### In Progress
- (none)

### Done
- Migration `deploy/migrations/20260530_google_id.sql`: adds `google_id VARCHAR(255) NULL UNIQUE` to `users` table; ran successfully
- `.env`: Added `RECAPTCHA_SITE_KEY`, `RECAPTCHA_SECRET_KEY`, `GOOGLE_CLIENT_ID`
- `config.php` CSP updated: `script-src`, `frame-src`, `connect-src` include `accounts.google.com`; `frame-src` includes `www.google.com` (reCAPTCHA)
- `login.php` rewritten: removed deprecated `platform.js`/`g-signin2`, replaced with GIS (`gsi/client`) + reCAPTCHA v3 widget + server-side captcha verification
- `register.php` rewritten: same conversion (deprecated `platform.js`/`g-signin2` → GIS + reCAPTCHA v3)
- `google_login.php` (new): POST JSON endpoint — verifies Google ID token via `tokeninfo` endpoint, finds/creates user, sets session, returns redirect
- Root cause of "white page header only in middle" found and fixed: `google_login.php` was missing `$_SESSION['user_name']` and `$_SESSION['user_email']`; `APP_ENV=production` custom error handler in `config.php:29-33` calls `exit` on any PHP warning, so `header.php:1900` accessing undefined `$_SESSION['user_name']` killed the page after the header. Added missing session vars + `?? 'User'` null coalescing at `header.php:1900` and `:2086`
- Second header bug fixed: `google_login.php` used `$_SESSION['role']` but `header.php` checks `$_SESSION['user_role']` in 4 places
- Replaced GIS iframe button (`g_id_signin`) with custom "Continue with Google" / "Sign up with Google" HTML button on both login and register pages (matching Qwen's style: dark `#35353d` background, pill shape, light text)
- GIS popup triggered via invisible `g_id_signin` iframe overlay (`opacity: 0.01`, `z-index: 2`, `data-width="380"`) layered on top of custom button — `google.accounts.id.prompt()` only shows One Tap, not full account chooser

### Done (2026-05-31 — Security Audit & Bug Fixes)
- Switched from reCAPTCHA v2 Checkbox to v3 (invisible, score-based) on all 4 pages: login, register, forgot_password, contact
- CSP `connect-src` fixed to include `www.google.com` and `www.gstatic.com` — reCAPTCHA v3 XHR was blocked by CSP on pages using `config.php`
- Rewrote captcha JS on all 4 pages: pre-populate token on load via `grecaptcha.execute()`, 90s refresh interval, submit handler as fallback only
- **Codebase security audit**: 18 bugs found and fixed across the codebase
- **CRITICAL — Double stock deduction**: `checkout.php` deducted stock before Chapa redirect, then `callback.php` deducted again. Fixed: Chapa payments no longer deduct stock in checkout; only callback deducts on payment confirmation
- **CRITICAL — Unauthenticated API**: `api/` directory had open endpoints with no auth (IDOR, open CORS). Added `.htaccess deny all` to entire `api/` directory
- **HIGH — `health.php` info disclosure**: Exposed DB stats, PHP version, `.env` loading with no auth. Added admin session check
- **HIGH — `captcha.php` hardcoded credentials**: Dead file with exposed keys. Deleted
- **HIGH — `mark_notification_read.php` CSRF**: Accepted GET requests with no CSRF token. Changed to POST-only + `csrf_verify()`
- **HIGH — `callback.php` no transaction**: Three independent UPDATEs with no rollback. Wrapped in `beginTransaction/commit/rollBack` with `FOR UPDATE` row lock
- **MEDIUM — Status not validated**: `manage_orders.php` accepted arbitrary status values. Added allowlist validation
- Verified reCAPTCHA v3 working on all 4 pages via browser automation (tokens populated, forms submit, no CSP errors)
- Fixed `admin/manage_orders.php` mobile overflow: `flex-wrap: wrap` + word-break on card-layout `<td>` at 480px
- All 18 fixes verified via PHP syntax check

### Blocked
- Brevo sender verification: `yitbarekteklu5@gmail.com` must be verified in Brevo dashboard before transactional emails work reliably
- Google OAuth client ID needs `http://localhost` registered as Authorized JavaScript origin in GCP Console for GIS popup to work on localhost

## GCP Console Guide
To register `http://localhost` as an Authorized JavaScript origin:
1. Go to https://console.cloud.google.com/apis/credentials
2. Select your OAuth 2.0 Client ID (`1003727523085-vk311f184eqrt95a3ggdq17h2fnqe5bl.apps.googleusercontent.com`)
3. Under **Authorized JavaScript origins**, click **+ Add URI**
4. Add `http://localhost`
5. Click **Save**
6. Wait 5 minutes for propagation, then refresh any login/register page — the GIS popup should now work on localhost

## Key Decisions
- Store SHA-256 hash of verification token (matching existing `password_resets` pattern), not raw token
- No automatic login after verification; user must return to login page
- `login.php` keeps its own session start and `$base_url` definition instead of requiring `config.php`
- Use `getbrevo/brevo-php` v4 SDK REST API instead of raw SMTP — avoids IP-based blocking by not using SMTP relay at all
- Differentiate between API key (`xkeysib-...`) for REST API and SMTP key (`xsmtpsib-...`) for SMTP — SDK uses `api-key` header, not SMTP auth
- `guzzlehttp/guzzle` required as concrete PSR-18/PSR-17 implementation for the SDK
- Resend moved from GET (triggered on page load via query params) to POST (only triggered by explicit button click)
- Resend button and feedback placed in a separate `$unverified_email` block (below the `$errors` block) so they remain visible even when no errors exist
- Verification tokens expire after 5 minutes (ISO standard short-lived token practice)
- `order_confirmation.php` no longer redirects after payment success — page stays loaded with updated status so user sees confirmation and can download receipt
- `receipt.php` uses `window.print()` button — no server-side PDF library needed
- Admin user management supports verify/unverify, role toggle (customer/admin), and delete (with self-deletion prevention)

## Next Steps
- User to verify `yitbarekteklu5@gmail.com` in Brevo dashboard (Settings > Senders > Add a Sender)
- If resend verification still silently dropped by Gmail, change `SMTP_FROM` to a different sender address
- Register `http://localhost` as Authorized JavaScript origin in GCP Console for GIS to work locally

## Critical Context
- `send_mail()` uses `\Brevo\TransactionalEmails\Requests\SendTransacEmailRequest` with sender, recipient, subject, htmlContent, textContent
- API key sourced from `$_ENV['BREVO_API_KEY']` — the `xkeysib-` key from `credentials/smtp.md`
- Previously failed with `No PSR-18 clients found` — fixed by installing `guzzlehttp/guzzle`
- Sender must be verified in Brevo or API will reject the request
- `APP_ENV=development` makes `send_mail()` write `.eml` files locally instead of calling the API
- Registration and forgot-password emails are now working (confirmed by user)
- All 31 tests pass
- reCAPTCHA v3 (invisible, score-based) site key: `6Lcv-AQtAAAAAPp3I6Lo3cG7N2rp4siIFDxvOl9i`, secret key: `6Lcv-AQtAAAAAEv0my9wPfwdHMq2iIf6sfGpEiUT`
- Google OAuth client ID: `1003727523085-vk311f184eqrt95a3ggdq17h2fnqe5bl.apps.googleusercontent.com`
- `APP_ENV=production` custom error handler (`config.php:29-33`) calls `exit` on any PHP warning — breaks pages with undefined `$_SESSION` keys

## Relevant Files
- `deploy/migrations/20260529_120000_email_verification.sql`: adds `email_verified_at`, `verification_token` to `users`
- `deploy/migrations/20260529_130000_verification_expiry.sql`: adds `verification_token_expires_at` column
- `register.php`: sends verification email after INSERT
- `verify_email.php`: new — handles verification link with expiry check
- `login.php`: blocks unverified logins; resend button in standalone warning block via POST form with inline feedback
- `helpers/mail.php`: refactored — uses `\Brevo\Brevo` client via REST API (not raw SMTP)
- `.env`: `BREVO_API_KEY`, `SMTP_FROM=yitbarekteklu5@gmail.com`, `APP_ENV=production`
- `credentials/smtp.md`: contains both the SMTP key (`xsmtpsib-...`) and the API key (`xkeysib-...`)
- `composer.json` / `vendor/`: Brevo SDK and Guzzle installed via Composer
- `order_confirmation.php`: Chapa payment confirmation — no longer redirects instantly after verification
- `receipt.php`: new — clean printable receipt with Print/Save as PDF
- `admin/manage_users.php`: new — user management (verify, role toggle, delete)
- `deploy/migrations/20260530_google_id.sql`: adds `google_id VARCHAR(255) NULL UNIQUE` to `users`
- `google_login.php`: new — Google Sign-In POST endpoint
- `login.php`: GIS + reCAPTCHA v3 login page
- `api/.htaccess`: deny all — secures orphaned REST endpoints
- `health.php`: admin-only health check endpoint
- `captcha.php`: deleted (hardcoded credentials, dead code)
- `mark_notification_read.php`: POST-only + CSRF verified
- `admin/manage_orders.php`: status allowlist validation + mobile responsive
