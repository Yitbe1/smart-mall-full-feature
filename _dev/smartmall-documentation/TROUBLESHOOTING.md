# 🔧 Cart & Payment Troubleshooting Guide

## Issue 1: Cart Not Clearing After Payment ❌→✅

### Root Causes & Fixes

#### **Problem A: Callback Not Being Called**
Chapa's webhook callback might not be reaching your server.

**Diagnosis:**
1. Open: `http://localhost/reference/admin/chapa-debug.php`
2. Check "Last Payment Status"
3. Look for **`[Chapa Callback]` messages in error logs**

**Fix if not being called:**
- Verify callback URL in checkout.php: `/reference/chapa/callback.php`
- Check if your firewall/router allows incoming webhooks
- Ensure localhost is accessible from outside (if testing from external Chapa instance)

---

#### **Problem B: Cart User ID Mismatch**
The order and cart might have different user IDs.

**Diagnosis:**
```bash
# Check MySQL directly
mysql -u root
USE your_database;
SELECT id, user_id FROM orders ORDER BY created_at DESC LIMIT 1;
SELECT id, user_id FROM cart ORDER BY created_at DESC LIMIT 1;
```

**Fix:** Both should show same `user_id`

---

#### **Problem C: Callback Executing But Not Clearing Cart**
The verification might be failing silently.

**Diagnosis:**
```bash
# Check error log for Chapa callback messages
tail -50 /opt/lampp/logs/apache2_error.log | grep Chapa
```

**Look for:**
- `✅ Payment verified as PAID` → Payment is verified ✓
- `❌ Payment verification FAILED` → API response issue
- `Cleared X items from cart` → Cart cleared ✓

---

## Issue 2: Cart Page Buttons Not Working ❌→✅

### Diagnosis Steps

#### **Step 1: Test with Simple Form**
Access: `http://localhost/reference/test-cart.php`

This tests:
- ✓ Session is active
- ✓ CSRF token is valid
- ✓ Forms can be submitted

#### **Step 2: Check Browser Console**
Press `F12` → Console tab

Look for:
- JavaScript errors (red)
- Network errors (POST failures)
- CSRF token validation errors

#### **Step 3: Check PHP Error Log**
```bash
tail -20 /opt/lampp/logs/apache2_error.log
```

Look for:
- `Invalid or missing security token` → CSRF issue
- `PDO Exception` → Database error
- `POST` not being received → Form submission issue

---

### Common Button Issues & Fixes

#### **Issue: "Invalid or missing security token"**
**Cause:** CSRF token mismatch
**Fix:** Clear browser cookies and try again
```bash
# In browser DevTools:
# Application → Cookies → Delete localhost cookies
```

#### **Issue: Form submits but page doesn't redirect**
**Cause:** Redirect header being blocked
**Fix:** Check if header() redirect is working
```bash
# Test with curl:
curl -X POST http://localhost/reference/cart.php \
  -d "csrf_token=test&remove_item=1"
```

#### **Issue: "Call to undefined function" csrf_verify()**
**Cause:** db.php not included
**Fix:** Ensure at top of cart.php:
```php
<?php
require_once __DIR__ . '/config.php'; // Must be FIRST
```

---

## Complete Testing Workflow

### 1️⃣ Verify Configuration
```
Access: http://localhost/reference/admin/verify-chapa.php
Result: All tests should pass ✅
```

### 2️⃣ Test Cart Buttons
```
1. Add item to cart
2. Access: http://localhost/reference/test-cart.php
3. Click "Test Update" button
4. Should redirect to cart.php
5. Check if quantity changed
```

### 3️⃣ Test Payment Flow
```
1. Login with test account
2. Add products to cart
3. Go to checkout
4. Select Chapa Pay
5. Use test card: 4111111111111111
6. Complete payment
7. Check: http://localhost/reference/admin/chapa-debug.php
```

### 4️⃣ Check Cart Clearing
```
After payment completes:
1. View cart.php → Should be EMPTY
2. If NOT empty → Check debug dashboard for callback errors
3. If errors exist → See "Root Causes & Fixes" above
```

---

## Database Debugging Commands

### Check Cart Table
```sql
SELECT * FROM cart WHERE user_id = YOUR_USER_ID;
```

### Check Payment Status
```sql
SELECT o.id, o.status, p.status, p.tx_ref 
FROM orders o 
LEFT JOIN payments p ON o.id = p.order_id 
ORDER BY o.created_at DESC LIMIT 5;
```

### Clear Test Data
```sql
-- DELETE test cart items
DELETE FROM cart WHERE user_id = YOUR_USER_ID;

-- DELETE test orders (optional)
DELETE FROM orders WHERE user_id = YOUR_USER_ID AND created_at > NOW() - INTERVAL 1 HOUR;
```

---

## Files Recently Updated

| File | Change | Status |
|------|--------|--------|
| `reference/chapa/callback.php` | ✅ Fixed syntax, improved error handling | Ready |
| `payment/chapa/callback.php` | ✅ Updated to use centralized config | Ready |
| `reference/test-cart.php` | ✅ NEW: Simple cart test tool | Test Now |
| `reference/debug-cart-payment.php` | ✅ NEW: Database diagnostic tool | Check Data |

---

## Quick Fix Checklist

- [ ] Run verification: `/admin/verify-chapa.php`
- [ ] Test cart buttons: `/test-cart.php`
- [ ] Check payment debug: `/admin/chapa-debug.php`
- [ ] Review error logs
- [ ] Verify user_id matches in orders & cart
- [ ] Confirm callback URL in checkout.php
- [ ] Test payment with test card
- [ ] Verify cart clears after payment

---

## Still Having Issues?

1. **Generate Diagnostic Report:**
   ```bash
   # Collect all relevant info
   echo "=== CHECKOUT.PHP ===" && grep -A 5 "callback_url" reference/checkout.php
   echo "=== CALLBACK ===" && tail -20 reference/chapa/callback.php
   echo "=== ERRORS ===" && tail -50 /opt/lampp/logs/apache2_error.log | grep -i chapa
   ```

2. **Enable Debug Mode:**
   Add to top of `reference/chapa/callback.php`:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

3. **Test Database Connection:**
   ```bash
   php -r "require 'reference/includes/db.php'; echo 'DB Connected ✓';"
   ```

4. **Verify Chapa Test Key:**
   - Should start with: `CHASECK_TEST-`
   - Check `.env` file

---

**Last Updated:** May 2026
**Status:** Ready to Test
