# CHAPTER 2: SYSTEM ANALYSIS

## 2.1 Existing System Analysis

The current shopping ecosystem relies primarily on traditional brick-and-mortar retail stores supplemented by basic online presence. In the existing system, customers must physically visit stores to browse products, compare options, and make purchases. Store employees manually manage inventory, process transactions using point-of-sale systems, and maintain paper-based or simple digital records.

For businesses attempting online sales, the existing approach typically involves basic websites with product listings and contact forms. Customers view products online but must call or email to place orders. Payment processing occurs offline through bank transfers or cash on delivery. This manual process creates delays, increases error rates, and limits scalability.

Small businesses often rely on social media platforms like Facebook or Instagram for product promotion, using direct messages for order taking and manual record-keeping for inventory and sales. While this approach has low entry barriers, it lacks professional features, security, and scalability.

The existing manual shopping process follows this workflow:
1. Customer travels to physical store location
2. Browses products on shelves with limited information
3. Asks staff for product details and availability
4. Makes purchase decision based on available options
5. Waits in checkout line for payment processing
6. Receives paper receipt with limited tracking capability
7. Returns to store for any issues or additional purchases

This process is time-consuming, geographically limited, and provides poor customer experience compared to modern e-commerce expectations.

## 2.2 Limitations of Existing Systems

The existing shopping systems suffer from several critical limitations that hinder both customer experience and business efficiency:

• **No Online Ordering:** Customers cannot place orders remotely, requiring physical presence at stores. This limitation excludes customers who are geographically distant, have mobility constraints, or prefer the convenience of online shopping. Businesses lose sales opportunities outside store operating hours and cannot serve customers beyond their immediate vicinity.

• **No Centralized Product Catalog:** Product information is scattered across physical locations, making it impossible for customers to view complete inventory or compare options efficiently. Businesses struggle to maintain consistent product information across multiple channels. There is no unified system for managing product details, pricing, and availability.

• **Manual Payment Processing:** All transactions require manual handling, whether cash, card, or bank transfer. This creates delays, increases error rates, and limits transaction volume. Manual reconciliation of payments with orders is time-consuming and error-prone. There is no automated tracking of payment status or integration with accounting systems.

• **Limited Mobile Accessibility:** Existing systems are not optimized for mobile devices. Customers using smartphones face poor user experiences with difficult navigation, unreadable text, and non-functional features. The lack of mobile applications means businesses miss the growing mobile commerce market segment.

Additional limitations include:
- No real-time inventory tracking leading to stock-outs and overselling
- Lack of customer purchase history and personalization
- Inefficient order fulfillment without automated workflows
- No analytics or reporting for business intelligence
- Poor scalability as business grows
- High operational costs for manual processes

## 2.3 Proposed System Overview

The Smart Mall system addresses these limitations through a comprehensive digital commerce platform that integrates web, mobile, and payment technologies into a unified ecosystem.

### Web System

The web-based platform provides a full-featured e-commerce website accessible from any modern browser. Customers can browse products organized by categories, use search and filter functions to find specific items, view detailed product information with images, add items to shopping cart, and complete secure checkout with integrated payment processing.

The responsive design ensures optimal viewing across desktop, tablet, and mobile browsers. The interface adapts automatically to screen size while maintaining full functionality. Server-side rendering with PHP ensures fast page loads and SEO optimization.

Administrative features are accessed through a secure dashboard where authorized users can manage the complete product catalog, process orders, update inventory, and view business analytics. The web system serves as the central hub for all business operations.

### Mobile Application

The Flutter-based mobile application provides native-quality shopping experience on Android devices. The app communicates with the backend through RESTful APIs, ensuring data consistency across platforms. Users can perform all shopping activities from their mobile devices with interfaces optimized for touch interaction.

The mobile app includes offline capabilities for browsing previously viewed products, maintains shopping cart state locally, and synchronizes with the server when connectivity is available. Push notifications keep users informed about order status and special offers.

The app architecture follows modern mobile development patterns with state management, efficient image caching, and optimized network requests. The Flutter framework enables potential future expansion to iOS with minimal additional development.

### Payment System

Integrated payment processing enables secure online transactions. The system supports multiple payment methods and handles the complete payment lifecycle from initiation through confirmation. Payment gateway integration follows industry security standards with encrypted data transmission and secure token storage.

Transaction records are maintained in the database with proper audit trails. Failed payments are handled gracefully with clear error messages and retry options. Successful payments trigger automatic order confirmation and inventory updates.

The payment system architecture separates payment processing from business logic, allowing future integration of additional payment providers without core system changes.

### System Workflow

The complete Smart Mall workflow operates as follows:

1. **Customer Registration/Login:** Users create accounts or authenticate to access personalized features
2. **Product Browsing:** Customers explore products via web or mobile interface with search and filtering
3. **Cart Management:** Selected products are added to shopping cart with quantity selection
4. **Checkout Process:** Customer provides shipping information and reviews order
5. **Payment Processing:** Secure payment gateway handles transaction authorization
6. **Order Confirmation:** System confirms order and sends notification to customer
7. **Admin Processing:** Business receives order notification and begins fulfillment
8. **Order Tracking:** Customer can view order status and history
9. **Analytics:** System collects data for business intelligence and reporting

This integrated workflow eliminates manual steps, reduces errors, and provides seamless experience across all touchpoints.

## 2.4 Functional Requirements

Functional requirements define the specific behaviors and functions the system must provide. These requirements are organized by user role and platform.

### Table 2.1: Customer Functional Requirements

| ID | Requirement | Description |
|----|-------------|-------------|
| FR1 | User Registration | System shall allow new users to create accounts by providing name, email, and password. System validates email format, checks for duplicates, and securely stores credentials with password hashing. |
| FR2 | User Login | System shall authenticate users using email and password. Upon successful authentication, system creates secure session and provides access to personalized features. |
| FR3 | Browse Products | System shall display product catalog with images, names, prices, and categories. Users can view products in grid or list format and navigate between categories. |
| FR4 | Search Products | System shall provide search functionality allowing users to find products by name or description. Search results update in real-time as users type. |
| FR5 | Filter Products | System shall allow filtering products by category, price range, and availability. Multiple filters can be applied simultaneously. |
| FR6 | View Product Details | System shall display comprehensive product information including description, price, stock status, category, and multiple images when user selects a product. |
| FR7 | Add to Cart | System shall allow users to add products to shopping cart with quantity selection. Cart updates immediately and displays current total. |
| FR8 | Update Cart | System shall allow users to modify cart contents by changing quantities or removing items. Total recalculates automatically. |
| FR9 | Checkout | System shall guide users through checkout process collecting shipping information and displaying order summary before payment. |
| FR10 | Make Payment | System shall process payments securely through integrated payment gateway. System handles payment authorization and provides confirmation. |
| FR11 | View Order History | System shall display user's past orders with details including order number, date, items, total, and status. |
| FR12 | Track Order Status | System shall show current status of orders (pending, processing, completed, cancelled) with status update timestamps. |

### Table 2.2: Admin Functional Requirements

| ID | Requirement | Description |
|----|-------------|-------------|
| FR13 | Admin Login | System shall authenticate administrators using secure credentials with elevated privileges separate from customer accounts. |
| FR14 | View Dashboard | System shall display administrative dashboard with key metrics including total products, orders, users, and revenue statistics. |
| FR15 | Add Product | System shall allow administrators to add new products by providing name, description, price, category, stock quantity, and images. System validates all inputs. |
| FR16 | Edit Product | System shall allow administrators to modify existing product information. Changes reflect immediately across all platforms. |
| FR17 | Delete Product | System shall allow administrators to remove products from catalog. System confirms deletion and maintains database integrity. |
| FR18 | Manage Categories | System shall allow administrators to create, edit, and delete product categories for organizing the catalog. |
| FR19 | View Orders | System shall display all customer orders with filtering options by status, date, and customer. |
| FR20 | Update Order Status | System shall allow administrators to change order status (pending, processing, completed, cancelled). Status changes are logged with timestamps. |
| FR21 | View Customers | System shall display registered customer list with account information and order history. |
| FR22 | Generate Reports | System shall provide sales reports, product performance analytics, and customer behavior insights. |

### Table 2.3: Mobile App Functional Requirements

| ID | Requirement | Description |
|----|-------------|-------------|
| FR23 | Mobile Login | Mobile app shall authenticate users using same credentials as web platform. App stores authentication token securely for persistent login. |
| FR24 | Mobile Registration | Mobile app shall allow new user registration with same validation as web platform. |
| FR25 | Mobile Product Browsing | App shall display products in mobile-optimized interface with touch-friendly navigation and swipe gestures. |
| FR26 | Mobile Search | App shall provide search functionality with mobile keyboard optimization and search history. |
| FR27 | Mobile Cart Management | App shall maintain shopping cart with local storage and server synchronization. Cart persists across app sessions. |
| FR28 | Mobile Checkout | App shall provide complete checkout workflow optimized for mobile input with auto-fill support. |
| FR29 | Mobile Payment | App shall integrate payment processing with mobile-optimized payment forms and secure data handling. |
| FR30 | Mobile Order History | App shall display order history with pull-to-refresh functionality and detailed order views. |
| FR31 | Push Notifications | App shall send notifications for order status updates and promotional messages (future enhancement). |
| FR32 | Offline Browsing | App shall cache product data for offline viewing of previously browsed items (future enhancement). |

## 2.5 Non-Functional Requirements

Non-functional requirements define system qualities and constraints that affect user experience and system operation.

### Security

**Authentication Security:** The system implements secure user authentication using password hashing with bcrypt algorithm. Passwords are never stored in plain text. Session management uses secure, HTTP-only cookies to prevent XSS attacks. Token-based authentication for mobile API access uses JWT tokens with expiration.

**Data Security:** All sensitive data transmission occurs over HTTPS in production. SQL injection is prevented through prepared statements and parameterized queries. Input validation occurs on both client and server sides. Cross-Site Request Forgery (CSRF) protection is implemented for state-changing operations.

**Access Control:** Role-based access control separates customer and administrator privileges. Administrative functions require elevated authentication. Users can only access their own order history and account information.

### Performance

**Response Time:** System responds to user requests within 500 milliseconds under normal load conditions. Database queries are optimized with proper indexing. Image loading uses lazy loading and caching strategies.

**Scalability:** System architecture supports horizontal scaling to handle increased user load. Database design allows for efficient querying even with large product catalogs. API endpoints are stateless to enable load balancing.

**Concurrent Users:** System handles multiple simultaneous users without performance degradation. Database connection pooling manages concurrent database access efficiently.

### Reliability

**Availability:** System maintains 99% uptime during business hours. Automated backups occur daily to prevent data loss. Error handling prevents system crashes from unexpected inputs.

**Data Integrity:** Database constraints ensure referential integrity. Transactions are used for operations requiring multiple database changes. Failed transactions roll back completely to maintain consistency.

**Error Recovery:** System handles errors gracefully with user-friendly error messages. Failed payment transactions do not create orphaned orders. System logs errors for debugging and monitoring.

### Scalability

**User Growth:** System architecture accommodates growing user base without major redesign. Database schema supports millions of products and orders. API design allows for future feature additions without breaking existing functionality.

**Feature Expansion:** Modular code structure enables adding new features without affecting existing functionality. API versioning supports backward compatibility. Database schema allows for new tables and relationships.

### Usability

**User Interface:** Interface follows modern design principles with intuitive navigation. Consistent design language across web and mobile platforms. Clear visual hierarchy guides users through tasks.

**Accessibility:** Interface is usable by people with varying technical skills. Error messages are clear and actionable. Help text and tooltips provide guidance where needed.

**Mobile Optimization:** Mobile interfaces are touch-friendly with appropriate button sizes. Text is readable without zooming. Forms are optimized for mobile input.

### Compatibility

**Browser Support:** Web platform works on modern browsers including Chrome, Firefox, Safari, and Edge. Responsive design adapts to different screen sizes.

**Mobile Platform:** Mobile app supports Android 5.0 and above. App adapts to different screen sizes and resolutions.

**Database:** System uses MySQL 5.7 or higher with standard SQL for portability.

## 2.6 Use Case Diagram

### Figure 2.1: Smart Mall Use Case Diagram

```
                    ┌─────────────────────────────────────────┐
                    │         Smart Mall System               │
                    │                                         │
    ┌──────┐        │  ┌──────────────────────────────────┐  │
    │      │        │  │  Register                        │  │
    │      │───────────│  Login                           │  │
    │      │        │  │  Browse Products                 │  │
    │      │        │  │  Search Products                 │  │
    │Customer       │  │  Filter Products                 │  │
    │      │        │  │  View Product Details            │  │
    │      │        │  │  Add to Cart                     │  │
    │      │───────────│  Update Cart                     │  │
    │      │        │  │  Checkout                        │  │
    │      │        │  │  Make Payment ◄──────────────────┼──┐
    └──────┘        │  │  View Order History              │  │
                    │  │  Track Order Status              │  │
                    │  └──────────────────────────────────┘  │
                    │                                         │
    ┌──────┐        │  ┌──────────────────────────────────┐  │
    │      │        │  │  Admin Login                     │  │
    │      │        │  │  View Dashboard                  │  │
    │      │───────────│  Add Product                     │  │
    │Admin │        │  │  Edit Product                    │  │
    │      │        │  │  Delete Product                  │  │
    │      │───────────│  Manage Categories               │  │
    │      │        │  │  View Orders                     │  │
    │      │        │  │  Update Order Status             │  │
    └──────┘        │  │  View Customers                  │  │
                    │  │  Generate Reports                │  │
                    │  └──────────────────────────────────┘  │
                    │                                         │
                    │  ┌──────────────────────────────────┐  │
                    │  │  Verify Transaction              │  │
  ┌─────────────┐  │  │  Process Payment                 │  │
  │  Payment    │──┼──│  Authorize Payment               │  │
  │  Gateway    │  │  │  Confirm Transaction             │  │
  └─────────────┘  │  │  Handle Payment Failure          │  │
                    │  └──────────────────────────────────┘  │
                    │                                         │
                    └─────────────────────────────────────────┘
```

**Figure 2.1 Description:** The use case diagram illustrates the interactions between three actors (Customer, Admin, and Payment Gateway) and the Smart Mall system. Customers can perform shopping-related activities including registration, product browsing, cart management, and order placement. Administrators manage the system through product and order management functions. The Payment Gateway actor represents the external payment processing system that handles transaction verification and payment processing.

## 2.7 Data Flow Diagram (DFD)

### Figure 2.2: Level 0 DFD (Context Diagram)

```
                    ┌─────────────────────────┐
                    │                         │
    Customer ──────▶│                         │──────▶ Order Confirmation
                    │                         │
    Product Info ◀──│   Smart Mall System     │──────▶ Payment Request
                    │                         │
    Admin ──────────│                         │◀────── Payment Confirmation
                    │                         │
    Product Data ──▶│                         │──────▶ Order Data
                    │                         │
                    └─────────────────────────┘
```

**Figure 2.2 Description:** The context diagram shows the Smart Mall system as a single process interacting with external entities. Customers provide input and receive product information and order confirmations. Administrators input product data and receive order information. The system exchanges payment requests and confirmations with external payment systems.

### Figure 2.3: Level 1 DFD (Detailed System)

```
                        ┌──────────────────────────────────────────────┐
                        │                                              │
    Customer            │                                              │
       │                │                                              │
       │ Login/Register │                                              │
       ▼                │                                              │
    ┌─────────────┐     │     ┌──────────────┐                        │
    │   1.0       │     │     │              │                        │
    │ User        │────────────│  Users DB    │                        │
    │ Management  │     │     │              │                        │
    └─────────────┘     │     └──────────────┘                        │
       │                │                                              │
       │ Browse         │                                              │
       ▼                │                                              │
    ┌─────────────┐     │     ┌──────────────┐                        │
    │   2.0       │     │     │              │                        │
    │ Product     │◀───────────│ Products DB  │                        │
    │ Management  │     │     │              │                        │
    └─────────────┘     │     └──────────────┘                        │
       │                │            ▲                                 │
       │ Add to Cart    │            │                                 │
       ▼                │            │ Product Data                    │
    ┌─────────────┐     │     ┌──────────────┐                        │
    │   3.0       │     │     │              │                        │
    │ Shopping    │────────────│  Cart DB     │                        │
    │ Cart        │     │     │              │                        │
    └─────────────┘     │     └──────────────┘                        │
       │                │                                              │
       │ Checkout       │                                              │
       ▼                │                                              │
    ┌─────────────┐     │     ┌──────────────┐                        │
    │   4.0       │     │     │              │                        │
    │ Order       │────────────│  Orders DB   │                        │
    │ Processing  │     │     │              │                        │
    └─────────────┘     │     └──────────────┘                        │
       │                │            │                                 │
       │ Payment        │            │                                 │
       ▼                │            ▼                                 │
    ┌─────────────┐     │     ┌──────────────┐                        │
    │   5.0       │     │     │              │                        │
    │ Payment     │◀───────────│ Payments DB  │                        │
    │ Processing  │     │     │              │                        │
    └─────────────┘     │     └──────────────┘                        │
       │                │            │                                 │
       │                │            │                                 │
       ▼                │            ▼                                 │
    Payment Gateway     │         Admin                                │
                        │                                              │
                        └──────────────────────────────────────────────┘
```

**Figure 2.3 Description:** The Level 1 DFD breaks down the system into five major processes: User Management handles authentication and registration; Product Management manages product catalog; Shopping Cart maintains cart state; Order Processing handles checkout and order creation; Payment Processing manages transactions. Each process interacts with its corresponding database and exchanges data with other processes as needed. The diagram shows how data flows from customer input through various processing stages to final order confirmation.

---

**End of Chapter 2**
