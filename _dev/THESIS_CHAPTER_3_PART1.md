# CHAPTER 3: SYSTEM DESIGN

## 3.1 System Architecture

The Smart Mall system follows a three-tier architecture pattern separating presentation, business logic, and data layers. This architectural approach provides modularity, scalability, and maintainability.

### Figure 3.1: System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                     PRESENTATION LAYER                          │
│                                                                 │
│  ┌──────────────────┐              ┌──────────────────┐        │
│  │   Web Browser    │              │   Mobile App     │        │
│  │                  │              │   (Flutter)      │        │
│  │  - HTML5         │              │                  │        │
│  │  - CSS3          │              │  - Dart          │        │
│  │  - JavaScript    │              │  - Material UI   │        │
│  │  - Bootstrap     │              │  - Provider      │        │
│  └────────┬─────────┘              └────────┬─────────┘        │
│           │                                 │                  │
└───────────┼─────────────────────────────────┼──────────────────┘
            │                                 │
            │         HTTP/HTTPS              │  REST API
            │                                 │
┌───────────┼─────────────────────────────────┼──────────────────┐
│           │        APPLICATION LAYER        │                  │
│           │                                 │                  │
│  ┌────────▼─────────────────────────────────▼────────┐         │
│  │              Apache Web Server                    │         │
│  │                                                    │         │
│  │  ┌──────────────────────────────────────────┐    │         │
│  │  │         PHP Backend                      │    │         │
│  │  │                                          │    │         │
│  │  │  - Authentication Module                 │    │         │
│  │  │  - Product Management Module             │    │         │
│  │  │  - Cart Management Module                │    │         │
│  │  │  - Order Processing Module               │    │         │
│  │  │  - Payment Integration Module            │    │         │
│  │  │  - API Endpoints                         │    │         │
│  │  │  - Session Management                    │    │         │
│  │  │  - Input Validation                      │    │         │
│  │  │  - Security Layer                        │    │         │
│  │  └──────────────────────────────────────────┘    │         │
│  └───────────────────────┬──────────────────────────┘         │
│                          │                                     │
└──────────────────────────┼─────────────────────────────────────┘
                           │
                           │  SQL Queries
                           │
┌──────────────────────────▼─────────────────────────────────────┐
│                      DATA LAYER                                │
│                                                                 │
│  ┌──────────────────────────────────────────────────────┐     │
│  │              MySQL Database Server                   │     │
│  │                                                       │     │
│  │  ┌─────────────┐  ┌─────────────┐  ┌────────────┐  │     │
│  │  │   users     │  │  products   │  │ categories │  │     │
│  │  └─────────────┘  └─────────────┘  └────────────┘  │     │
│  │                                                       │     │
│  │  ┌─────────────┐  ┌─────────────┐                   │     │
│  │  │   orders    │  │order_items  │                   │     │
│  │  └─────────────┘  └─────────────┘                   │     │
│  │                                                       │     │
│  └───────────────────────────────────────────────────────┘     │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                   EXTERNAL SERVICES                             │
│                                                                 │
│  ┌──────────────────────────────────────────────────────┐      │
│  │           Payment Gateway (Chapa)                    │      │
│  │                                                       │      │
│  │  - Payment Authorization                             │      │
│  │  - Transaction Processing                            │      │
│  │  - Payment Verification                              │      │
│  └──────────────────────────────────────────────────────┘      │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

**Figure 3.1 Description:** The system architecture diagram illustrates the three-tier structure of Smart Mall. The Presentation Layer includes both web browsers and mobile applications that users interact with. The Application Layer contains the PHP backend running on Apache server, handling all business logic, authentication, and API endpoints. The Data Layer consists of MySQL database storing all system data. External services like payment gateways integrate through the Application Layer.

### Architecture Components

**Presentation Layer:**
The presentation layer provides user interfaces for both web and mobile platforms. The web interface uses HTML5 for structure, CSS3 for styling, JavaScript for interactivity, and Bootstrap for responsive design. The mobile application is built with Flutter framework using Dart language, Material Design components, and Provider for state management.

Both interfaces communicate with the backend through HTTP/HTTPS protocols. The web interface makes traditional HTTP requests, while the mobile app uses RESTful API calls with JSON data exchange.

**Application Layer:**
The application layer implements all business logic and serves as the intermediary between presentation and data layers. Apache web server hosts the PHP backend which is organized into modular components:

- Authentication Module: Handles user registration, login, session management, and token generation
- Product Management Module: Manages product CRUD operations and catalog organization
- Cart Management Module: Maintains shopping cart state and calculations
- Order Processing Module: Handles checkout workflow and order creation
- Payment Integration Module: Interfaces with payment gateway for transaction processing
- API Endpoints: Provides RESTful services for mobile app communication
- Session Management: Maintains user sessions and authentication state
- Input Validation: Validates and sanitizes all user inputs
- Security Layer: Implements security measures including SQL injection prevention and XSS protection

**Data Layer:**
The data layer uses MySQL relational database to store all system data. The database is organized into five main tables: users (customer and admin accounts), products (product catalog), categories (product organization), orders (customer orders), and order_items (order details). Relationships between tables maintain data integrity through foreign key constraints.

**External Services:**
Payment processing is handled by external payment gateway services. The system integrates with Chapa payment gateway for transaction authorization, processing, and verification. This separation allows for future integration of additional payment providers without modifying core system architecture.

## 3.2 User Interface Design

The user interface design focuses on usability, consistency, and modern aesthetics. All interfaces follow Material Design principles with consistent color schemes, typography, and interaction patterns.

### 3.2.1 Home Page Interface

### Figure 3.2: Web Home Page

[Screenshot shows: Navigation bar with logo and menu items, hero section with gradient background and "Welcome to Smart Mall" heading, category cards with icons (Fashion, Electronics, Home, Beauty), product grid displaying multiple products with images and prices, search bar, and filter button]

**Figure 3.2 Description:** The home page serves as the main entry point for customers. The top navigation bar includes the Smart Mall logo, search functionality, profile icon, and shopping cart icon with item count badge. The hero section features a gradient blue background with welcome message and "Shop Now" call-to-action button.

Below the hero section, category cards are displayed horizontally with icons and gradient backgrounds. Each category card shows an icon (clothing for Fashion, devices for Electronics, home for Home, spa for Beauty) and category name. Selected categories have enhanced gradient and shadow effects.

The main content area displays products in a responsive grid layout. Each product card shows:
- Product image with rounded corners
- Category badge in top-left corner
- Wishlist heart icon in top-right corner
- Product name (truncated to 2 lines)
- Price in blue color with bold font
- Arrow icon indicating clickable card

The interface uses white background for product cards with subtle shadows. Hover effects include scale transformation and enhanced shadows for better interactivity feedback.

### 3.2.2 Product Listing Interface

### Figure 3.3: Product Listing with Filters

[Screenshot shows: Search bar with clear button, filter button with tune icon, category filter chips (All, Fashion, Electronics, Home, Beauty), sort options dropdown, product grid with filtering applied, price range display]

**Figure 3.3 Description:** The product listing interface provides comprehensive browsing and filtering capabilities. The search bar at the top allows text-based product search with real-time results and a clear button to reset search.

The filter button opens a bottom sheet modal displaying:
- Sort options as choice chips (Newest, Price: Low to High, Price: High to Low, Name)
- Price range slider with minimum and maximum values
- Current price range display ($0 - $10,000)
- Reset and Apply buttons

Category filter chips appear below the search bar, allowing quick category selection. The selected category has blue background while others have white background with borders.

Products matching the applied filters display in the grid below. The interface updates dynamically as filters change without page reload. Empty states show appropriate messages when no products match the criteria.

### 3.2.3 Product Detail Interface

### Figure 3.4: Product Detail Page

[Screenshot shows: Large product image, image gallery thumbnails, product name and category, price display, stock status, quantity selector, add to cart button, product description, specifications if available]

**Figure 3.4 Description:** The product detail page provides comprehensive information about a selected product. The top section features a large product image with swipeable image gallery for products with multiple images. Thumbnail images below allow direct navigation to specific images.

Product information section includes:
- Product name in large, bold font
- Category badge
- Price prominently displayed in blue
- Stock status indicator (In Stock/Out of Stock)
- Quantity selector with minus and plus buttons
- Add to Cart button (full width, blue background)

The description section uses expandable/collapsible format for longer descriptions. Product specifications are displayed in a structured format when available.

The interface maintains consistent spacing and typography with the rest of the application. The Add to Cart button is fixed at the bottom on mobile devices for easy access.

### 3.2.4 Shopping Cart Interface

### Figure 3.5: Shopping Cart Screen

[Screenshot shows: Cart item list with product images, names, prices, quantity controls, remove buttons, subtotal calculation, checkout button, empty cart state]

**Figure 3.5 Description:** The shopping cart interface displays all items added to the cart with management capabilities. Each cart item shows:
- Product thumbnail image
- Product name and category
- Unit price
- Quantity selector (minus, quantity display, plus buttons)
- Item total (quantity × price)
- Remove button (trash icon)

The quantity selector allows adjusting item quantities with immediate total recalculation. The minus button is disabled when quantity is 1. The plus button is disabled when quantity reaches stock limit.

The bottom section displays:
- Subtotal for all items
- Tax calculation (if applicable)
- Shipping cost (if applicable)
- Grand total in large, bold font
- Checkout button (full width, blue background)

Empty cart state shows an icon, "Your cart is empty" message, and "Continue Shopping" button to return to product browsing.

### 3.2.5 Checkout Interface

### Figure 3.6: Checkout Form

[Screenshot shows: Shipping information form (name, email, address, phone), order summary with item list and totals, payment method selection, place order button]

**Figure 3.6 Description:** The checkout interface guides users through the final purchase steps. The form is organized into clear sections:

**Shipping Information Section:**
- Full Name field with validation
- Email field with format validation
- Phone number field
- Shipping address textarea
- All fields marked as required with asterisks

**Order Summary Section:**
- List of cart items with quantities and prices
- Subtotal calculation
- Shipping cost
- Tax (if applicable)
- Grand total prominently displayed

**Payment Method Section:**
- Payment method selection (currently shows "Chapa Payment")
- Payment gateway logo/icon
- Security badges indicating secure transaction

The Place Order button is positioned at the bottom with loading state during processing. Form validation occurs on submission with clear error messages for invalid or missing fields.

### 3.2.6 Payment Interface

### Figure 3.7: Payment Processing Screen

[Screenshot shows: Payment gateway integration screen, transaction details, payment confirmation, loading states, success/failure messages]

**Figure 3.7 Description:** The payment interface handles the transaction processing workflow. When users click Place Order, the system:

1. **Validation Phase:** Validates all form inputs and displays errors if any
2. **Processing Phase:** Shows loading indicator with "Processing your order..." message
3. **Payment Gateway:** Redirects to payment gateway (or shows embedded payment form)
4. **Transaction Phase:** Displays transaction progress with security indicators
5. **Confirmation Phase:** Shows success message with order number or error message if failed

**Success State:**
- Green checkmark icon
- "Order Placed Successfully!" message
- Order number display (e.g., "Order #ORD-ABC123")
- Order summary
- "View Order" and "Continue Shopping" buttons

**Failure State:**
- Red error icon
- Clear error message explaining the failure
- "Try Again" button to retry payment
- "Contact Support" link for assistance

The interface maintains user confidence through clear status indicators, security badges, and professional error handling.

### 3.2.7 Login and Registration Interface

### Figure 3.8: Login Screen

[Screenshot shows: Smart Mall logo, "Welcome Back" heading, email input field, password input field, login button, "Don't have an account? Sign up" link, forgot password link]

**Figure 3.8 Description:** The login interface provides secure authentication with clean, focused design. The screen features:

- Smart Mall logo at the top center
- "Welcome Back" heading
- Email input field with email icon
- Password input field with lock icon and show/hide toggle
- "Remember Me" checkbox (optional)
- Login button (full width, blue background)
- "Forgot Password?" link
- "Don't have an account? Sign up" link at bottom

Input validation occurs on blur and submission. Error messages appear below respective fields. Loading state shows spinner on login button during authentication.

### Figure 3.9: Registration Screen

[Screenshot shows: Smart Mall logo, "Join Smart Mall" heading, name field, email field, password field, confirm password field, create account button, "Already have an account? Login" link]

**Figure 3.9 Description:** The registration interface collects necessary information for account creation:

- Smart Mall logo at top
- "Join Smart Mall" heading with subtitle
- Full Name input field
- Email input field with validation
- Password input field with strength indicator
- Confirm Password field with match validation
- Terms and Conditions checkbox
- Create Account button
- "Already have an account? Login" link

Real-time validation provides immediate feedback:
- Email format validation
- Password strength indicator (weak/medium/strong)
- Password match confirmation
- All fields required before submission

### 3.2.8 Admin Dashboard Interface

### Figure 3.10: Admin Dashboard

[Screenshot shows: Admin navigation sidebar, statistics cards (total products, orders, users, revenue), quick action buttons, recent orders table, product management section]

**Figure 3.10 Description:** The admin dashboard provides comprehensive business management tools. The interface includes:

**Statistics Cards (Top Section):**
- Total Products card with inventory icon and count
- Total Orders card with shopping bag icon and count
- Total Users card with people icon and count
- Revenue card with money icon and amount

Each card uses distinct colors (blue, green, orange, purple) and displays large numbers with icons.

**Quick Actions Section:**
- Manage Products button
- Manage Orders button
- View Users button
- Settings button

Each action card shows icon, title, subtitle, and chevron indicating navigation.

**Recent Activity:**
- Recent orders table with order number, customer, total, status
- Product performance metrics
- User activity summary

The admin interface uses a sidebar navigation for accessing different management sections. The design prioritizes information density while maintaining readability.

---

**Continue to Part 2 for Mobile UI Screens...**
