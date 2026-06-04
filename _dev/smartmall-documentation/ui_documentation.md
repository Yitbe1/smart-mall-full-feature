**Smart Mall — UI / Frontend Documentation**  
***Platform*** *: PHP/MySQL E-Commerce · * ***Theme*** *: Dual Light/Dark Mode · * ***Stack*** *: Vanilla HTML/CSS/JS · Google Fonts (Outfit, Inter)*  
![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAnEAAAACCAYAAAA3pIp+AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAANklEQVR4nO3OQQmAABRAsSeYxZy/lHd7GMACBrCCNxG2BFtmZquOAAD4i3Ot7mr/egIAwGvXA7GTBde8bLBeAAAAAElFTkSuQmCC)  
**Table of Contents**  
| | | | |  
|-|-|-|-|  
| **#** | **Page** | **Route** | **Section** |   
| 0 | Preloader Splash | *(auto on first visit)* | Storefront |   
| 1 | Homepage / Shop | /reference/index.php | Storefront |   
| 2 | Login | /reference/login.php | Auth |   
| 3 | Register | /reference/register.php | Auth |   
| 4 | Shopping Cart | /reference/cart.php | Shopping |   
| 5 | My Orders | /reference/orders.php | Shopping |   
| 6 | Product Detail | /reference/product.php?id={id} | Shopping |   
| 7 | Checkout | /reference/checkout.php | Shopping |   
| 8 | Admin Dashboard | /reference/admin/dashboard.php | Admin |   
| 9 | Admin — Add Product | /reference/admin/add_product.php | Admin |   
| 10 | Admin — Manage Categories | /reference/admin/manage_categories.php | Admin |   
| 11 | Admin — Manage Orders | /reference/admin/manage_orders.php | Admin |   
   
![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAnEAAAACCAYAAAA3pIp+AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAM0lEQVR4nO3KsQ0AIRAEsUW6Qij1KvnevhMSYmKQ7GiCGd09k3wBAOAVf+2o4wYAwE1qAdYuAy151mgcAAAAAElFTkSuQmCC)  
**Storefront Pages**  
![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAnEAAAACCAYAAAA3pIp+AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAANUlEQVR4nO3OMQ2AUBBAsUfyNTCi9VwgEA3sWGAjJK2CbjNzVGcAAPzFtapV7V9PAAB47X4AEW4ELQDBN+AAAAAASUVORK5CYII=)  
**00 · Preloader Splash Screen**  
**Route**: Automatically displayed on the first page visit (session-aware)  
**Description**: A premium dark fullscreen entry animation that plays once per browser session. Features:  
- Teal shopping cart icon bouncing in with spring physics  
- Animated speed streaks shooting behind the cart  
- "SMART MALL" letters flipping in one by one with staggered timing  
- "Smart. Secure. Stylish." tagline revealing word by word  
- Radial teal glow pulse emanating behind the logo  
- Smooth upward-slide exit after 2.6 seconds  
![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAnEAAAACCAYAAAA3pIp+AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAANUlEQVR4nO3OMQ2AABAAsSNhZscYahheJwqQgQU2QtIq6DIze3UGAMBf3Gu1VcfXEwAAXrseoqcEQXyAWBgAAAAASUVORK5CYII=)  
**01 · Homepage / Shop**  
**Route**: http://localhost/reference/index.php  
**Description**: The main storefront and landing page. Includes:  
- **Sticky navigation bar** with animated logo (icon + wordmark), live search, cart/account actions, theme toggle  
- **Hero Section** — Editorial grid layout with featured categories and promotional banners  
- **Category Filter Bar** — Horizontal scrollable quick-filter pills  
- **Product Grid** — Responsive masonry-style product cards with hover effects, add-to-cart CTA  
- **Spotlight / Brand Banner** — Full-width editorial promotional section  
![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAnEAAAACCAYAAAA3pIp+AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAANUlEQVR4nO3OMQ2AABAAsSPBCj7fFjsymJHAjAU2QtIq6DIzW7UHAMBfnGt1V8fXEwAAXrsexNkF4H1/HJoAAAAASUVORK5CYII=)  
**Auth Pages**  
![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAnEAAAACCAYAAAA3pIp+AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAANUlEQVR4nO3OMQ2AABAAsSPBCUZfE2IYmVDBhAU2QtIq6DIzW7UHAMBfnGt1V8fXEwAAXrse/xcF7U7sx4wAAAAASUVORK5CYII=)  
**02 · Login**  
**Route**: http://localhost/reference/login.php  
**Description**: Clean, centered authentication form. Features:  
- Email + password fields with floating labels  
- "Remember me" toggle  
- Link to registration page  
- Error toast notifications on invalid credentials  
- Redirects to homepage or admin dashboard based on user role  
![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAnEAAAACCAYAAAA3pIp+AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAANUlEQVR4nO3OQQ2AQBAAsSE5CbzRujLwhwQMYIEfIWkVdJuZozoDAOAvrlWtav96AgDAa/cDEXQEKquakOYAAAAASUVORK5CYII=)  
**03 · Register**  
**Route**: http://localhost/reference/register.php  
**Description**: New user account registration. Features:  
- Full name, email, password, and confirm password fields  
- Client-side password strength validation  
- Server-side duplicate email detection with inline feedback  
- Smooth redirect to login on successful registration  
![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAnEAAAACCAYAAAA3pIp+AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAANUlEQVR4nO3OQQmAABRAsSd4NIGRTPXNaQBrWMGbCFuCLTOzV2cAAPzFvVZbdXw9AQDgtesBhZQEOYZGgUEAAAAASUVORK5CYII=)  
**Shopping Pages**  
![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAnEAAAACCAYAAAA3pIp+AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAANklEQVR4nO3OMQ2AABAAsSNBACPq8MH2NpGACyywEZJWQZeZ2aszAAD+4l6rrTq+ngAA8Nr1AL/KBEe6dElaAAAAAElFTkSuQmCC)  
**04 · Shopping Cart**  
**Route**: http://localhost/reference/cart.php  
**Description**: Full cart management interface. Features:  
- Product thumbnail, name, price per unit, quantity stepper  
- Live quantity update (AJAX) with total recalculation  
- Remove item action with confirmation  
- Order summary sidebar with subtotal, taxes, and total  
- "Proceed to Checkout" CTA button  
- Empty cart state with illustrated prompt  
![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAnEAAAACCAYAAAA3pIp+AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAANUlEQVR4nO3OMQ2AABAAsSNBCkLfE07YGfHAiAU2QtIq6DIzW7UHAMBfnGt1V8fXEwAAXrse4eQF6VhvmPsAAAAASUVORK5CYII=)  
**05 · My Orders**  
**Route**: http://localhost/reference/orders.php  
**Description**: Customer order history page. Features:  
- Chronological list of all past orders  
- Order ID, date, item count, total, and status badge (Pending / Confirmed / Shipped / Delivered)  
- Expandable order detail panel  
- Empty state for new users  
![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAnEAAAACCAYAAAA3pIp+AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAANElEQVR4nO3OQQmAABRAsad4FCtY9ecwnkms4E2ELcGWmTmrKwAA/uLeqrU6vp4AAPDa/gDzUgM9+S8z3AAAAABJRU5ErkJggg==)  
**06 · Product Detail**  
**Route**: http://localhost/reference/product.php?id={id}  
**Description**: Individual product page. Features:  
- Full-width product media gallery (multiple images + video support)  
- Product title, price, category badge, stock availability  
- Description with rich formatting  
- Quantity selector + "Add to Cart" CTA  
- Related/recommended products section  
![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAnEAAAACCAYAAAA3pIp+AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAANUlEQVR4nO3OQQmAABRAsSd4NIGRTPXNaQBrWMGbCFuCLTOzV2cAAPzFvVZbdXw9AQDgtesBhZQEOYZGgUEAAAAASUVORK5CYII=)  
**07 · Checkout**  
**Route**: http://localhost/reference/checkout.php  
**Description**: Multi-step order placement and payment form. Features:  
- Customer shipping information fields  
- Order summary with itemized list and totals  
- Payment method selection (Chapa payment gateway integration)  
- Form validation before payment submission  
- Secure checkout flow with session protection  
![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAnEAAAACCAYAAAA3pIp+AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAMUlEQVR4nO3WAQkAIBAEsBPMYs4PZhMDWMAA5njYUmxU1UqyAwBAF2cmeZE4AIBO7gentgXapSWpbgAAAABJRU5ErkJggg==)  
**Admin Panel Pages**  
***Access*** *: Admin routes are protected. Only users with the * *admin* * role can access these pages. Unauthorized access redirects to the login page.*  
![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAnEAAAACCAYAAAA3pIp+AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAANUlEQVR4nO3OMQ2AABAAsSNBCUpfEJ5YGBDBgAU2QtIq6DIzW7UHAMBfHGt1V+fXEwAAXrseHDYF+yOk59sAAAAASUVORK5CYII=)  
**08 · Admin Dashboard**  
**Route**: http://localhost/reference/admin/dashboard.php  
**Description**: Central admin overview and analytics hub. Features:  
- Summary KPI cards: Total Revenue, Total Orders, Total Products, Total Users  
- Recent orders table with quick status update actions  
- Low stock / out of stock product alerts  
- Quick navigation links to all admin sub-sections  
![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAnEAAAACCAYAAAA3pIp+AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAANElEQVR4nO3OQQmAUBBAwSf8GGLWDWFDY3ixgjcRZhLMNjNHdQYAwF9cq1rV/vUEAIDX7gcRXAQ2s/16gwAAAABJRU5ErkJggg==)  
**09 · Admin — Add / Edit Product**  
**Route**: http://localhost/reference/admin/add_product.php  
**Description**: Comprehensive product creation and editing form. Features:  
- Product name, SKU, description (rich text area)  
- Category assignment dropdown  
- Price, compare-at price, stock quantity fields  
- Multi-image upload with drag-and-drop support  
- Video URL field for product demo videos  
- Featured/active toggle switches  
- Save, preview, and discard actions  
![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAnEAAAACCAYAAAA3pIp+AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAANklEQVR4nO3OMQ2AABAAsSNBACPykMH4NpGACyywEZJWQZeZ2aszAAD+4l6rrTo+jgAA8N71AL/CBEiG5xPoAAAAAElFTkSuQmCC)  
**10 · Admin — Manage Categories**  
**Route**: http://localhost/reference/admin/manage_categories.php  
**Description**: Full category management panel. Features:  
- Category list with thumbnail image, name, and product count  
- Add new category with image upload (file picker or URL)  
- Edit category name and cover image inline  
- Delete category with confirmation dialog  
- Drag-reorder support for display ordering  
![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAnEAAAACCAYAAAA3pIp+AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAANklEQVR4nO3OMQ2AABAAsSNBCkLfFDZwwIgHRiywEZJWQZeZ2ao9AAD+4lyruzq+ngAA8Nr1AOH0BedHjjlfAAAAAElFTkSuQmCC)  
**11 · Admin — Manage Orders**  
**Route**: http://localhost/reference/admin/manage_orders.php  
**Description**: Order management and fulfillment center. Features:  
- Paginated orders table with ID, customer, date, total, status  
- Inline status update dropdown (Pending → Confirmed → Shipped → Delivered → Cancelled)  
- Order detail expansion with full line items  
- Filter by status tabs  
- Bulk status update actions  
![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAnEAAAACCAYAAAA3pIp+AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAANElEQVR4nO3OQQmAABRAsad4FCtY9ecwnkms4E2ELcGWmTmrKwAA/uLeqrU6vp4AAPDa/gDzUgM9+S8z3AAAAABJRU5ErkJggg==)  
**Design System**  
| | | |  
|-|-|-|  
| **Token** | **Value** | **Usage** |   
| Primary Color | #2563eb (blue) | Buttons, links, active states |   
| Brand Teal | #45a8aa | Logo accent, brand dot |   
| Dark Background | #080c14 | Preloader, dark surfaces |   
| Surface | CSS var --surface | Cards, panels |   
| Border | CSS var --border-color | Dividers |   
| Font — Headings | **Outfit** (Google Fonts) | Nav logo, headings |   
| Font — Body | **Inter** (Google Fonts) | Body text, labels |   
   
**Responsive Breakpoints**  
| | |  
|-|-|  
| **Breakpoint** | **Width** |   
| Mobile | < 640px |   
| Tablet | 640px – 1024px |   
| Desktop | > 1024px |   
   
![](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAnEAAAACCAYAAAA3pIp+AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAANklEQVR4nO3OQQmAABRAsScYxpg/h5VMYARvRrCCNxG2BFtmZquOAAD4i3Ot7mr/egIAwGvXA224BcUMk6pDAAAAAElFTkSuQmCC)  
*2026-05-21 · Smart Mall v1.0*  
