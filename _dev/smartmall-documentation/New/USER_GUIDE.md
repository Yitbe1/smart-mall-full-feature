# Smart Mall User Guide

> **Document ID:** USER-GUIDE-001  
> **Version:** 2.0 | **Date:** June 2026 | **Platform:** Web + Mobile (Android)

Your complete guide to browsing, shopping, and managing orders on Smart Mall — including account setup, payment, PWA installation, and troubleshooting.

> **Cross-reference:** See MASTER_DOCUMENTATION.md §1.5 (System Scope), §4.1 (Technology Stack), and §5.1–§5.7 (Testing) for system-level context.

---

## Table of Contents

1. [Welcome & System Overview](#1-welcome--system-overview)
2. [Creating Your Account](#2-creating-your-account)
3. [Signing In](#3-signing-in)
4. [Browsing Products](#4-browsing-products)
5. [Shopping Cart](#5-shopping-cart)
6. [Checkout & Payment](#6-checkout--payment)
7. [Orders & History](#7-orders--history)
8. [Wishlist](#8-wishlist)
9. [PWA & Mobile App](#9-pwa--mobile-app)
10. [Currency & Contact](#10-currency--contact)
11. [Troubleshooting](#11-troubleshooting)
12. [FAQ & Glossary](#12-faq--glossary)
13. [Appendix: Revision History](#appendix-revision-history)

---

## 1. Welcome & System Overview

Smart Mall is a full-stack e-commerce platform supporting both web and mobile (Android) shopping. Customers can browse products across 4 categories, manage a cart, pay via Chapa (debit/credit card, mobile money) or bank transfer / cash on delivery, track orders, and install the store as a Progressive Web App (PWA) on any device.

### 1.1 Quick Facts

| Feature | Detail |
|---------|--------|
| Product categories | Fashion & Apparel, Electronics & Gadgets, Home & Living, Beauty & Health |
| Currencies | USD (default), ETB |
| Payment methods | Chapa Pay, Bank Transfer, Cash on Delivery |
| Authentication | Email + password, Google Sign-In |
| Mobile app | Android via Capacitor + PWA installable on any device |
| Offline support | PWA service worker caches static assets; dedicated offline fallback page |

### 1.2 Requirements

| Platform | Requirement |
|----------|-------------|
| Web browser | Chrome 90+, Firefox 90+, Safari 15+, Edge 90+ |
| Mobile app | Android 8.0+ |
| JavaScript | Required (for cart, Google Sign-In, reCAPTCHA, image lightbox) |
| Internet | Required for checkout; PWA allows browsing cached pages offline |

[Screenshot: Smart Mall homepage showing hero banner, featured products grid, and category navigation]

---

## 2. Creating Your Account

### 2.1 Standard Registration

1. Navigate to the **Register** page.
2. Fill in the registration form:

| Field | Required | Validation |
|-------|----------|------------|
| Full Name | Yes | At least 2 characters |
| Email | Yes | Valid email format |
| Password | Yes | At least 8 characters; must contain uppercase, lowercase, digit, and special character |
| Confirm Password | Yes | Must match password |

3. Complete the reCAPTCHA v3 challenge (runs silently in the background — invisible score-based check, no visual challenge).
4. Click **Register**.
5. Check your email for a **verification link** (expires in **5 minutes**).

> [!WARNING]
> The verification link expires 5 minutes after registration. If it expires, go to the **Login** page and use the **Resend verification email** option.

6. Click the verification link to activate your account.

[Screenshot: Registration form with all fields filled and the Register button visible]

### 2.2 Email Verification

After clicking the verification link, you will see one of these outcomes:

| Result | What you see | Next step |
|--------|-------------|-----------|
| Success | "Email verified successfully!" | Go to Login |
| Expired | "This verification link has expired." | Go to Login → Resend verification |
| Already verified | "Your email may already be verified." | Go to Login |

### 2.3 Password Rules (Reference)

Your password must satisfy **all** of these conditions:

- Minimum 8 characters
- At least one uppercase letter (A–Z)
- At least one lowercase letter (a–z)
- At least one digit (0–9)
- At least one special character (e.g., `!@#$%^&*`)

---

## 3. Signing In

### 3.1 Email + Password Login

1. Go to the **Login** page.
2. Enter your email and password.
3. Complete the reCAPTCHA v3 verification.
4. Click **Login**.

> [!NOTE]
> If your email is not yet verified, a message will appear offering a **Resend verification email** option. Click it to receive a new link.

### 3.2 Google Sign-In

1. On the Login page, click **Sign in with Google**.
2. Select your Google account from the prompt.
3. On first use, a new Smart Mall account is created automatically using your Google profile (name, email, verified status).
4. On subsequent visits, the Google account is linked to your existing Smart Mall account.

> [!NOTE]
> Google Sign-In uses Google Identity Services (GSI) on the web and native Google Sign-In on the Android app. Both authenticate against the same system.

### 3.3 Forgot Password

1. Click **Forgot Password?** on the Login page.
2. Enter your registered email address.
3. Check your inbox for a password reset link (expires in **1 hour**).
4. Click the link and enter a new password (same rules as registration).
5. Your password is now updated.

[Screenshot: Forgot Password form with email field and Send Reset Link button]

### 3.4 Password Reset Flow

| Step | Action |
|------|--------|
| 1 | Submit your email address |
| 2 | A secure reset token is generated and stored |
| 3 | An email with the reset link is sent to you |
| 4 | Click the link and enter a new password |
| 5 | Your password is updated and you can log in |

---

## 4. Browsing Products

### 4.1 Home Page

The home page displays:

- **Hero banner** — promotional image with call-to-action
- **Featured products** — grid of promoted items
- **Category links** — quick navigation to each product category
- **Search bar** — keyword search across product names and descriptions

[Screenshot: Home page hero section with featured products below]

### 4.2 Product Detail Page

Click a product to view its full details. The page includes:

| Element | Description |
|---------|-------------|
| Product image | Main image with zoom on hover |
| Image lightbox | Click image to view in full-screen overlay with arrow navigation and keyboard support (← →) |
| Additional images | Gallery of supplementary product photos |
| Product name, price, description | Core product information |
| Category badge | Links back to category filter |
| Stock indicator | Shows "In Stock" with count or "Out of Stock" badge |
| Rating & reviews | Average rating with count, plus individual reviews |
| Add to Cart button | Adds item to your cart without page reload |
| Wishlist toggle | Heart icon to save for later |

> [!TIP]
> Use the keyboard arrow keys (← →) when the image lightbox is open to navigate between product images without clicking.

### 4.3 Search

1. Type a keyword in the search bar (top of any page).
2. Results update in real time, showing up to **6** matching products.
3. Click a result to view the product detail page.

Search matches against product names and descriptions.

### 4.4 Categories

Use the category navigation to filter products:

- **Fashion & Apparel** — clothing, shoes, accessories
- **Electronics & Gadgets** — phones, laptops, accessories
- **Home & Living** — furniture, decor, kitchen
- **Beauty & Health** — cosmetics, skincare, wellness

---

## 5. Shopping Cart

### 5.1 Adding Items to Cart

1. On any product detail page, select a **quantity** (default: 1).
2. Click **Add to Cart**.
3. A confirmation message appears, and the cart icon in the header updates with the new item count.

> [!NOTE]
> If the item is already in your cart, the quantity is increased (capped at available stock).

### 5.2 Viewing & Managing Cart

Go to **Cart** (link in the header) to see all items:

| Action | How |
|--------|-----|
| Change quantity | Update the quantity field and click **Update** |
| Remove item | Click **Remove** |
| See totals | Subtotal per item, overall subtotal, estimated tax (10%), total |
| Proceed to checkout | Click **Checkout** button |

> [!WARNING]
> The quantity update is capped by available stock. If you enter a quantity exceeding stock, an error message appears: *"Requested quantity exceeds available stock (N available)."*

[Screenshot: Shopping cart page showing items with quantity controls, prices, and Checkout button]

### 5.3 Cart Ownership

Each cart item is tied to your user account. You cannot see or modify another user's cart.

### 5.4 Empty Cart

When your cart is empty, a friendly message is displayed with a **Start Shopping** link back to the home page.

---

## 6. Checkout & Payment

### 6.1 Before You Begin

- You must be logged in.
- Your cart must contain at least one item in stock.
- Your billing address details must be ready.

### 6.2 Checkout Page

Navigate to **Checkout** (or click **Checkout** in your cart).

#### Billing Information Form

| Field | Required | Notes |
|-------|----------|-------|
| First Name | Yes | |
| Last Name | Yes | |
| Email | Yes | Prefilled from your account |
| Address | Yes | Street address |
| City | Yes | |
| State | Yes | |
| ZIP / Postal Code | Yes | |
| Country | Yes | Defaults to Ethiopia |

#### Order Summary

| Item | Source |
|------|--------|
| Product list with quantities | Cart contents |
| Subtotal | Sum of price × quantity |
| Tax | 10% of subtotal |
| Total | Subtotal + tax |

#### Payment Method

Select one of three options:

| Method | Code | Description |
|--------|------|-------------|
| Chapa Pay | chapa | Online payment via debit/credit card or mobile money (default) |
| Bank Transfer | bank | Manual bank transfer; instructions shown after order |
| Cash on Delivery | cod | Pay cash when order arrives |

> [!NOTE]
> Chapa Pay converts the amount to ETB using the live exchange rate before redirecting. Bank Transfer and COD remain in USD.

### 6.3 Placing an Order

1. Review all items and amounts.
2. Fill in the billing address.
3. Select a payment method.
4. Click **Place Order**.

The system will:
- Verify stock availability
- Deduct stock quantities
- Create the order with status `pending`
- If Chapa Pay selected: redirect to Chapa's secure checkout page
- If Bank Transfer / COD: show the order confirmation page immediately

[Screenshot: Checkout page with billing form, order summary, payment method selection, and Place Order button]

### 6.4 Chapa Payment Flow

| Step | What happens |
|------|-------------|
| 1 | System converts total to ETB |
| 2 | System creates a payment record |
| 3 | System initiates the Chapa transaction |
| 4 | Your browser redirects to Chapa's secure checkout page |
| 5 | You complete payment on Chapa's website |
| 6 | Chapa sends confirmation back to Smart Mall |
| 7 | You are returned to the order confirmation page |

### 6.5 Order Confirmation

After placing an order, you land on the **Order Confirmation** page. This page:

- Displays your order details, payment status, and next steps
- For Chapa payments, it checks the payment status in real time
- Shows a success message when payment is confirmed

> [!TIP]
> Bookmark the order confirmation page or note your Order ID — you will need it to track your order later.

### 6.6 Receipt

A printable receipt is available from your order confirmation or orders page. It contains:

- Store name and contact information
- Order ID and date
- Billing address
- Product list with prices
- Subtotal, tax, and total
- Payment method and status

---

## 7. Orders & History

### 7.1 Viewing Your Orders

Go to **My Orders** to see all your past orders, sorted newest first.

Each order card displays:

| Field | Description |
|-------|-------------|
| Order ID | Unique identifier (clickable, links to order confirmation) |
| Date | Timestamp of when the order was placed |
| Total | Total amount paid |
| Status | Color-coded badge (see below) |
| Payment Method | Chapa Pay, Bank Transfer, or COD |

#### Order Status Reference

| Status | Color | Meaning |
|--------|-------|---------|
| `pending` | 🟡 Yellow | Order received, awaiting processing |
| `processing` | 🔵 Blue | Order is being prepared |
| `shipped` | 🟢 Green | Order has been dispatched |
| `delivered` | ✅ Green (bold) | Order completed successfully |
| `cancelled` | 🔴 Red | Order was cancelled |

### 7.2 Cancelling an Order

1. Go to **My Orders**.
2. Locate the order you want to cancel.
3. Click the **Cancel** button (only visible if status is `pending`).

> [!WARNING]
> Orders can **only** be cancelled while their status is `pending`. Once an order moves to `processing` or beyond, cancellation is blocked.

When you cancel an order:
- Status changes to `cancelled`
- Associated payment records are marked as failed
- Product stock is **restored** automatically

[Screenshot: My Orders page showing multiple order cards with different status badges]

### 7.3 Re-ordering

If an order was cancelled while `pending`, you can place a new order with the same items from scratch via the checkout flow.

---

## 8. Wishlist

### 8.1 Adding Items to Wishlist

On any product detail page:
- Click the **heart (♡) icon** to add the product to your wishlist.
- The icon fills in (♥) to confirm it's saved.

### 8.2 Viewing Wishlist

Go to **My Wishlist** to see all saved items in a grid layout.

Each wishlist card shows:
- Product image
- Product name (linked to product page)
- Price
- **Add to Cart** button (quick-add)
- **Remove** button

### 8.3 Removing Items

- On the wishlist page: click **Remove**
- On the product page: click the filled heart (♥) to toggle it off

---

## 9. PWA & Mobile App

### 9.1 Installing the PWA

Smart Mall can be installed as a Progressive Web App on any device. This gives you an app-like experience with offline support.

**Android (Chrome):**
1. Open the Smart Mall website in Chrome.
2. Tap the browser menu (⋮) → **Add to Home screen**.
3. Confirm the installation.
4. Smart Mall now appears as an app icon on your home screen.

**iOS (Safari):**
1. Open the Smart Mall website in Safari.
2. Tap the **Share** button.
3. Scroll down and tap **Add to Home Screen**.
4. Name the app and tap **Add**.

**Desktop (Chrome/Edge):**
1. Look for the install icon (⊕) in the address bar.
2. Click **Install**.

### 9.2 PWA Features

| Feature | Description |
|---------|-------------|
| App icon | Custom icon on your home screen at multiple resolutions |
| Standalone mode | Opens without browser chrome (no address bar or tabs) |
| Portrait orientation | Optimized for mobile portrait viewing |
| Offline page | Dedicated page shown when you are offline |
| Cache strategy | Previously visited pages are available offline |
| Theme color | Blue (#007AFF) title bar matching the brand |

[Screenshot: Smart Mall installed as a PWA on an Android home screen]

### 9.3 Android App (Capacitor)

A native Android app is available. It wraps the Smart Mall web experience with native device support:

- **Google Sign-In** — native Google login (no browser redirect)
- **Push notifications** — receive order updates and promotions
- **Network status** — detects connectivity changes
- **Local storage** — remembers your preferences

> [!NOTE]
> The native app loads the same Smart Mall website. It is **not** a separate app — it enhances the web experience with native capabilities.

### 9.4 Offline Behavior

When you lose internet connectivity:
- Previously visited pages may still be accessible (cached by the service worker)
- Checkout, adding to cart, and submitting forms require a network connection
- A custom offline page is shown for pages that haven't been cached

---

## 10. Currency & Contact

### 10.1 Changing Currency

Smart Mall supports **USD** and **ETB** (Ethiopian Birr).

To switch currency:
1. Find the currency selector dropdown in the site header (desktop) or navigation menu (mobile).
2. Select **USD** or **ETB**.
3. The page refreshes with prices displayed in your chosen currency.

The selection persists until you change it or close your browser.

> [!NOTE]
> Exchange rates are fetched from a live API and cached for performance. If the rate service is temporarily unavailable, the last known rate is used.

### 10.2 Contacting Support

Go to the **Contact Us** page.

| Field | Required | Notes |
|-------|----------|-------|
| Name | Yes | |
| Email | Yes | Valid email required |
| Subject | Yes | Dropdown with options |
| Order ID | Conditional | Only if subject is "Order Support" |
| Message | Yes | |

**Subject options:**
- General Inquiry
- Order Support (shows an Order ID field)
- Product Inquiry
- Bug Report
- Other

Submissions are sent to the site administrator for follow-up.

### 10.3 Newsletter Subscription

Enter your email in the **Subscribe** field (found in the footer on every page) to receive Smart Mall updates.

---

## 11. Troubleshooting

### 11.1 Login Problems

| Symptom | Likely Cause | Solution |
|---------|-------------|----------|
| "Captcha verification failed" | reCAPTCHA v3 couldn't verify you | Refresh the page and try again. Ensure JavaScript is enabled. |
| "Email not verified" | You haven't clicked the verification link | Use the **Resend verification email** option on the login page |
| "Invalid credentials" | Email or password is wrong | Use the **Forgot Password** link to reset |
| Google button does nothing | Ad blocker blocking GSI script | Disable ad blocker for this site, or use email login |

### 11.2 Registration Problems

| Symptom | Likely Cause | Solution |
|---------|-------------|----------|
| "Email already registered" | Account exists under this email | Use **Forgot Password** or try Google Sign-In |
| "Must contain an uppercase letter" | Password lacks A–Z | Add at least one uppercase character |
| "Must contain a special character" | Password lacks non-alphanumeric char | Add `!@#$%^&*` or similar |
| "This verification link has expired" | 5-minute window passed | Request a new link from the login page |

### 11.3 Cart Issues

| Symptom | Likely Cause | Solution |
|---------|-------------|----------|
| "Please login to add items to cart" | Session expired | Log in again |
| "Not enough stock available" | Someone else purchased the last available items | Reduce quantity or remove item |
| "Requested quantity exceeds available stock" | You tried to set quantity > stock | Enter a lower number |
| Cart shows wrong count | Cart indicator is stale | Refresh the page |

### 11.4 Checkout Problems

| Symptom | Likely Cause | Solution |
|---------|-------------|----------|
| "Could not load the ETB exchange rate" | Exchange rate API unreachable | Wait a moment and try again, or select Bank Transfer / COD |
| "[Product] is out of stock" | Stock depleted since you added to cart | Remove the item or reduce quantity |
| Chapa page shows an error | Test mode / invalid API key | Contact support with the Order ID |
| Chapa redirect doesn't happen | JavaScript redirect blocked | Try disabling your ad blocker |

### 11.5 Order Problems

| Symptom | Likely Cause | Solution |
|---------|-------------|----------|
| "Order cannot be cancelled at this stage" | Status is no longer `pending` | Contact support |
| Order status stuck at `pending` | Payment verification incomplete | Check the order confirmation page for the latest payment status |
| Receipt page is blank | Invalid or unauthorized Order ID | Verify you are logged into the correct account |

[Screenshot: A sample error message displayed on the checkout page]

### 11.6 General Errors

| Error | Meaning |
|-------|---------|
| "Database configuration error" | System configuration issue — contact the site administrator |
| "A database connection error occurred" | Database server is unavailable — contact the site administrator |
| "An error occurred. Please try again later." | Unexpected system error — try again or contact support |
| "Offline" page shown | You are offline and the page isn't cached |

---

## 12. FAQ & Glossary

### 12.1 Frequently Asked Questions

**Q: Is my payment information secure?**
A: Yes. Payment data is handled by **Chapa Pay**, a PCI-DSS compliant payment gateway. Your card details never touch Smart Mall's servers.

**Q: Can I change my order after placing it?**
A: You can **cancel** an order while its status is `pending`. Once it moves to `processing`, modifications are not possible — contact support for assistance.

**Q: How long does delivery take?**
A: Delivery times depend on your location and the seller. Contact support for estimated delivery windows.

**Q: What currencies are supported?**
A: USD (US Dollar) and ETB (Ethiopian Birr). You can switch between them using the currency selector in the header.

**Q: Does the mobile app support push notifications?**
A: Yes. The Android app supports push notifications for order updates and promotions.

**Q: Can I use Smart Mall offline?**
A: Partially. Pages you've visited may be available offline via caching. Checkout and cart operations require an active internet connection.

**Q: How do I delete my account?**
A: Account deletion is not available through the user interface. Contact support to request account removal.

### 12.2 Glossary

| Term | Definition |
|------|------------|
| **AJAX** | Technology that allows adding to cart without page reload |
| **Bcrypt** | Secure method used to encrypt and store passwords |
| **Capacitor** | Framework that wraps the Smart Mall web app as a native Android app |
| **Chapa** | Ethiopian payment gateway supporting cards and mobile money |
| **CSRF Token** | Security measure that prevents unauthorized form submissions |
| **FCM** | Firebase Cloud Messaging — push notification service for the Android app |
| **GSI** | Google Identity Services — the library enabling Google Sign-In |
| **JSON** | Data format used for communication between the browser and server |
| **PWA** | Progressive Web App — installable website with offline capabilities |
| **reCAPTCHA v3** | Google's invisible bot detection (score-based, no checkbox) |
| **Session** | Server-side storage of your login state, cart, and preferences |
| **Service Worker** | Background script that enables caching and offline support |
| **SKU** | Stock Keeping Unit — unique identifier for each product variant |
| **SQL Injection** | A security attack type that is prevented by the system's architecture |

---

## Appendix: Revision History

| Date | Version | Author | Changes |
|------|---------|--------|---------|
| June 2026 | 2.0 | Smart Mall Team | Updated for v2.0 release; added PWA, Capacitor, Google Sign-In, Chapa payment details |
| March 2026 | 1.0 | Smart Mall Team | Initial user guide |

---

**End of User Guide**
