# SMART MALL E-COMMERCE SYSTEM
## A Full-Stack Web and Mobile Commerce Platform

---

## DECLARATION PAGE

### STUDENT ORIGINALITY STATEMENT

I hereby declare that this project report entitled **"Smart Mall E-Commerce System: A Full-Stack Web and Mobile Commerce Platform"** is my own work and has been carried out under the supervision of [Supervisor Name]. All sources of information have been duly acknowledged.

**Student Name:** [Your Name]  
**Student ID:** [Your ID]  
**Signature:** _______________  
**Date:** _______________

---

## APPROVAL PAGE

This project report entitled **"Smart Mall E-Commerce System"** submitted by [Your Name] has been examined and is approved for the award of [Degree Name] in [Department Name].

**Supervisor:**  
Name: _______________  
Signature: _______________  
Date: _______________

**Head of Department:**  
Name: _______________  
Signature: _______________  
Date: _______________

**External Examiner:**  
Name: _______________  
Signature: _______________  
Date: _______________

---

## ACKNOWLEDGMENT

I would like to express my sincere gratitude to all those who contributed to the successful completion of this project.

First and foremost, I thank my supervisor [Supervisor Name] for their invaluable guidance, continuous support, and constructive feedback throughout this project.

I am grateful to the faculty members of [Department Name] for their knowledge and expertise that shaped my understanding of software development and e-commerce systems.

Special thanks to my family and friends for their unwavering support and encouragement during the development of this project.

I also acknowledge the open-source community and various online resources that provided essential tools and documentation for implementing this system.

Finally, I thank all the test users who provided valuable feedback that helped improve the system's usability and functionality.

---

## ABSTRACT

Traditional shopping methods face significant limitations including geographical constraints, time restrictions, and limited product accessibility. Small businesses struggle to reach wider markets, while customers face inconvenience in comparing products and making purchases. The lack of integrated mobile commerce solutions further restricts shopping flexibility in today's mobile-first world.

This project presents Smart Mall, a comprehensive full-stack e-commerce system that addresses these challenges through an integrated web and mobile platform. The system provides a complete online marketplace where customers can browse products, manage shopping carts, and complete secure transactions. Administrators can efficiently manage products, orders, and monitor business operations through a dedicated dashboard.

The system is built using modern web technologies including HTML5, CSS3, and JavaScript for the frontend, PHP for backend processing, MySQL for database management, and Flutter for the mobile application. The architecture implements RESTful API design for seamless communication between web and mobile platforms. Security features include password hashing, SQL injection prevention, and token-based authentication.

The implementation successfully delivers 16 core features across 12 mobile screens and 8 API endpoints, managing 131 products across 4 categories. Testing results demonstrate system reliability with response times under 500ms and successful transaction processing. The platform is production-ready, scalable, and provides a foundation for future enhancements including AI recommendations and multi-vendor support.

**Keywords:** E-commerce, Mobile Commerce, Full-Stack Development, RESTful API, Flutter, PHP, MySQL

---

## TABLE OF CONTENTS

**DECLARATION PAGE** .................................................. i  
**APPROVAL PAGE** ...................................................... ii  
**ACKNOWLEDGMENT** .................................................... iii  
**ABSTRACT** ........................................................... iv  
**TABLE OF CONTENTS** .................................................. v  
**LIST OF FIGURES** .................................................... viii  
**LIST OF TABLES** ..................................................... x

**CHAPTER 1: INTRODUCTION** ............................................ 1  
1.1 Background of the Study ............................................ 1  
1.2 Problem Statement .................................................. 2  
1.3 Proposed Solution .................................................. 3  
1.4 Objectives ......................................................... 4  
    1.4.1 General Objective ............................................ 4  
    1.4.2 Specific Objectives .......................................... 4  
1.5 Scope of the System ................................................ 5  
1.6 Significance of the Project ........................................ 6  
1.7 Target Users ....................................................... 7

**CHAPTER 2: SYSTEM ANALYSIS** ......................................... 8  
2.1 Existing System Analysis ........................................... 8  
2.2 Limitations of Existing Systems .................................... 9  
2.3 Proposed System Overview ........................................... 10  
2.4 Functional Requirements ............................................ 11  
2.5 Non-Functional Requirements ........................................ 14  
2.6 Use Case Diagram ................................................... 15  
2.7 Data Flow Diagram (DFD) ............................................ 16

**CHAPTER 3: SYSTEM DESIGN** ........................................... 18  
3.1 System Architecture ................................................ 18  
3.2 User Interface Design .............................................. 20  
    3.2.1 Home Page Interface .......................................... 20  
    3.2.2 Product Listing Interface .................................... 21  
    3.2.3 Product Detail Interface ..................................... 22  
    3.2.4 Shopping Cart Interface ...................................... 23  
    3.2.5 Checkout Interface ........................................... 24  
    3.2.6 Payment Interface ............................................ 25  
    3.2.7 Login and Registration Interface ............................. 26  
    3.2.8 Admin Dashboard Interface .................................... 27  
    3.2.9 Mobile Home Screen ........................................... 28  
    3.2.10 Mobile Product Screen ....................................... 29  
    3.2.11 Mobile Cart Screen .......................................... 30  
    3.2.12 Mobile Checkout Screen ...................................... 31  
    3.2.13 Mobile Payment Screen ....................................... 32  
3.3 Navigation Flow Diagram ............................................ 33  
3.4 Database Design .................................................... 34  
3.5 ER Diagram ......................................................... 36  
3.6 Database Schema Diagram ............................................ 37  
3.7 API/Backend Design ................................................. 38  
3.8 Security Design .................................................... 40

**CHAPTER 4: SYSTEM IMPLEMENTATION** ................................... 42  
4.1 Technology Stack ................................................... 42  
4.2 Frontend Implementation ............................................ 43  
4.3 Backend Implementation ............................................. 45  
4.4 Mobile App Implementation .......................................... 47  
4.5 Database Implementation ............................................ 49  
4.6 Payment Gateway Integration ........................................ 51  
4.7 System Integration ................................................. 53

**CHAPTER 5: TESTING AND SECURITY** .................................... 55  
5.1 Testing Strategy ................................................... 55  
5.2 Functional Testing ................................................. 56  
5.3 Mobile Testing ..................................................... 58  
5.4 Payment Testing .................................................... 59  
5.5 Security Analysis .................................................. 60

**CHAPTER 6: DEPLOYMENT AND MAINTENANCE** .............................. 62  
6.1 Deployment Environment ............................................. 62  
6.2 Web Deployment ..................................................... 63  
6.3 Mobile Deployment .................................................. 64  
6.4 Maintenance ........................................................ 65  
6.5 Future Enhancements ................................................ 66

**CHAPTER 7: CONCLUSION** .............................................. 68

**REFERENCES** ......................................................... 70

**APPENDICES** ......................................................... 72  
Appendix A: SQL Schema ................................................. 72  
Appendix B: API Endpoints .............................................. 75  
Appendix C: Screenshots ................................................ 77  
Appendix D: Testing Evidence ........................................... 80

---

# CHAPTER 1: INTRODUCTION

## 1.1 Background of the Study

The evolution of e-commerce has fundamentally transformed the retail landscape over the past two decades. From the early days of simple online catalogs to today's sophisticated digital marketplaces, electronic commerce has become an integral part of modern business operations. The global e-commerce market has experienced exponential growth, with worldwide sales reaching trillions of dollars annually and showing no signs of slowing down.

Digital commerce growth has been particularly accelerated by several key factors. The widespread adoption of internet connectivity, increasing consumer confidence in online transactions, and the convenience of shopping from anywhere at any time have all contributed to this transformation. Businesses of all sizes, from multinational corporations to small local shops, are recognizing the necessity of establishing an online presence to remain competitive in the digital age.

Mobile commerce has emerged as a critical component of the e-commerce ecosystem. With smartphone penetration exceeding 80% in many markets and mobile devices becoming the primary means of internet access for billions of users, mobile-first commerce is no longer optional—it is essential. Consumers expect seamless shopping experiences across all devices, with the ability to browse, compare, and purchase products directly from their mobile phones.

Online payment systems have evolved to become secure, fast, and user-friendly, removing one of the major barriers to e-commerce adoption. Modern payment gateways support multiple payment methods, provide fraud protection, and ensure secure transaction processing. The integration of digital wallets, mobile payments, and instant payment verification has made online transactions as convenient as traditional cash or card payments.

This convergence of web technology, mobile computing, and secure payment systems creates an opportunity to develop comprehensive e-commerce solutions that serve both businesses and consumers effectively. The Smart Mall project addresses this opportunity by providing a full-stack platform that combines web accessibility, mobile convenience, and secure transaction processing in a single integrated system.

## 1.2 Problem Statement

### Traditional Shopping Problems

Traditional brick-and-mortar shopping faces several inherent limitations that affect both consumers and businesses. Physical shopping is constrained by geographical boundaries, requiring customers to travel to store locations, which consumes time and resources. Store operating hours limit when purchases can be made, creating inconvenience for customers with busy schedules or those in different time zones.

The lack of a centralized marketplace means customers must visit multiple stores to compare products and prices, making informed purchasing decisions time-consuming and inefficient. Physical stores have limited shelf space, restricting the variety of products available to customers. Additionally, businesses face high overhead costs for maintaining physical locations, including rent, utilities, and staffing.

Time wasting is a significant issue in traditional shopping. Customers spend considerable time traveling to stores, searching for products, waiting in checkout lines, and dealing with stock availability issues. This inefficiency reduces customer satisfaction and limits the number of transactions businesses can process.

### Existing E-commerce Problems

While many e-commerce solutions exist, they often suffer from critical shortcomings that limit their effectiveness. Poor mobile support is a common issue, with many platforms offering desktop-optimized experiences that translate poorly to mobile devices. This creates frustration for mobile users and results in lost sales opportunities.

Payment limitations restrict transaction capabilities. Many existing systems support only limited payment methods, lack proper payment gateway integration, or provide inadequate security measures for financial transactions. This creates barriers to purchase completion and raises security concerns among users.

Usability problems plague many e-commerce platforms. Complex navigation structures, cluttered interfaces, slow loading times, and poor search functionality create friction in the shopping experience. Inadequate product information, lack of filtering options, and confusing checkout processes lead to cart abandonment and lost revenue.

Furthermore, many existing solutions lack proper administrative tools for business management. Insufficient inventory management, limited order tracking capabilities, and poor reporting features make it difficult for businesses to operate efficiently and make data-driven decisions.

## 1.3 Proposed Solution

The Smart Mall system addresses these challenges through a comprehensive, integrated approach that combines multiple platforms and technologies into a cohesive e-commerce ecosystem.

### Web Platform

The web platform provides a fully-featured online e-commerce website accessible from any modern web browser. Built with responsive design principles, the website adapts seamlessly to different screen sizes and devices. The platform features an intuitive user interface with clear navigation, comprehensive product catalogs organized by categories, advanced search and filtering capabilities, and a streamlined checkout process.

The web interface serves as the primary administrative hub, providing business owners with powerful tools for product management, order processing, and business analytics. Real-time inventory tracking, order status management, and customer relationship tools enable efficient business operations.

### Mobile Application

The mobile application extends the e-commerce experience to native mobile devices, providing customers with convenient shopping access on-the-go. Built using Flutter framework, the app delivers a smooth, responsive user experience optimized for touch interfaces and mobile interaction patterns.

The mobile app includes all essential shopping features: product browsing with image galleries, search and filter functionality, shopping cart management, secure checkout, and order history tracking. Push notifications keep users informed about order status, special offers, and new products. The app maintains feature parity with the web platform while optimizing the experience for mobile usage patterns.

### Payment Integration

Secure online transaction processing is implemented through integrated payment gateway functionality. The system supports multiple payment methods and provides real-time transaction verification. Payment processing follows industry security standards, including encryption of sensitive data, secure token-based authentication, and compliance with payment card industry (PCI) requirements.

The payment system handles the complete transaction lifecycle: payment initiation, authorization, capture, and confirmation. Failed transactions are handled gracefully with clear error messages and retry options. Transaction records are maintained for accounting and reconciliation purposes.

### Admin Dashboard

The administrative dashboard provides comprehensive system management capabilities. Administrators can manage the complete product catalog, including adding new products, updating existing listings, managing inventory levels, and organizing products into categories.

Order management features allow tracking of all customer orders from placement through fulfillment. Status updates, customer communication, and order history are centralized in an intuitive interface. Business analytics provide insights into sales performance, popular products, customer behavior, and revenue trends.

User management capabilities enable administration of customer accounts, access control, and role-based permissions. The dashboard provides real-time system monitoring and reporting tools for informed decision-making.

## 1.4 Objectives

### 1.4.1 General Objective

To develop a secure, scalable, and user-friendly full-stack e-commerce and mobile commerce system that enables businesses to sell products online and provides customers with convenient shopping experiences across web and mobile platforms.

### 1.4.2 Specific Objectives

The project aims to achieve the following specific objectives:

• **Develop responsive frontend interface:** Create an intuitive, modern web interface using HTML5, CSS3, and JavaScript that provides excellent user experience across all devices and screen sizes. Implement responsive design patterns that adapt seamlessly from desktop to tablet to mobile viewports.

• **Develop robust backend system:** Build a secure, efficient backend using PHP that handles business logic, data processing, user authentication, session management, and API endpoints. Implement proper error handling, input validation, and security measures throughout the backend architecture.

• **Develop relational database:** Design and implement a normalized MySQL database schema that efficiently stores and manages products, categories, users, orders, and transactions. Establish proper relationships, constraints, and indexes for optimal performance and data integrity.

• **Integrate payment gateway:** Implement secure payment processing capabilities that support online transactions, handle payment verification, manage transaction records, and provide proper error handling for payment failures.

• **Develop mobile application:** Create a native-quality mobile application using Flutter framework that provides full shopping functionality on Android devices. Ensure the mobile app communicates effectively with the backend API and provides an optimized mobile user experience.

• **Implement authentication system:** Develop secure user authentication and authorization mechanisms including user registration, login, session management, password hashing, and token-based API authentication for mobile app access.

• **Implement order management system:** Create a comprehensive order processing system that handles cart management, checkout workflow, order placement, order tracking, and order history. Provide both customer-facing and administrative order management interfaces.

## 1.5 Scope of the System

### INCLUDED FEATURES

#### Customer Features

**Registration:** New users can create accounts by providing necessary information including name, email, and password. The system validates input data, checks for duplicate accounts, and securely stores user credentials with password hashing.

**Login:** Registered users can authenticate using email and password credentials. The system verifies credentials, creates secure sessions, and provides access to personalized features including order history and saved preferences.

**Browse Products:** Customers can view the complete product catalog organized by categories. Product listings display essential information including images, names, prices, and availability. The interface supports both grid and list views for user preference.

**Add to Cart:** Users can add products to their shopping cart with quantity selection. The cart maintains state across sessions, allows quantity updates, and calculates totals in real-time. Cart contents are preserved for logged-in users across devices.

**Checkout:** A streamlined checkout process collects shipping information, displays order summary, and guides users through payment. The system validates all input data and provides clear feedback at each step.

**Payment:** Secure payment processing allows customers to complete transactions using supported payment methods. The system handles payment authorization, captures transaction details, and provides confirmation upon successful payment.

#### Admin Features

**Add Products:** Administrators can add new products to the catalog by providing product details including name, description, price, category, stock quantity, and images. The system validates all inputs and updates the database accordingly.

**Edit Products:** Existing products can be modified to update information, adjust pricing, change categories, or update stock levels. Changes are reflected immediately across all platforms.

**Delete Products:** Products can be removed from the catalog when discontinued or out of stock. The system handles deletion safely, maintaining referential integrity in the database.

**Manage Orders:** Administrators can view all customer orders, update order status, track fulfillment progress, and manage customer communications regarding orders.

#### Mobile App Features

**Mobile Shopping:** The mobile application provides full product browsing capabilities optimized for mobile devices. Touch-friendly interfaces, swipe gestures, and mobile-optimized layouts enhance the shopping experience.

**Mobile Checkout:** Complete checkout functionality is available in the mobile app, allowing users to complete purchases entirely from their mobile devices without switching to the web platform.

### EXCLUDED FEATURES

The following features are not included in the current implementation but may be considered for future enhancements:

**AI Recommendation Engine:** Artificial intelligence-powered product recommendations based on user behavior, purchase history, and browsing patterns are not implemented in this version.

**Real-time Delivery Tracking:** GPS-based delivery tracking and real-time shipment status updates are not included in the current scope.

## 1.6 Significance of the Project

### Benefits to Customers

The Smart Mall system provides customers with unprecedented convenience in shopping. Users can browse and purchase products 24/7 from any location with internet access, eliminating geographical and temporal constraints. The ability to compare products, read descriptions, and make informed decisions at their own pace enhances the shopping experience.

Mobile accessibility ensures customers can shop during commutes, breaks, or any convenient moment using their smartphones. The integrated platform maintains shopping cart and preferences across devices, allowing users to start shopping on mobile and complete purchases on desktop, or vice versa.

Secure payment processing and order tracking provide peace of mind and transparency throughout the purchase journey. Customers receive immediate confirmation of orders and can track their purchase history for reference and reordering.

### Benefits to Businesses

For businesses, the system provides a cost-effective entry into digital commerce without the overhead of physical retail locations. The platform enables reaching customers beyond geographical boundaries, expanding market reach significantly.

The administrative dashboard provides powerful tools for inventory management, order processing, and business analytics. Real-time insights into sales performance, popular products, and customer behavior enable data-driven decision making.

Automated order processing reduces manual work and human error, improving operational efficiency. The scalable architecture allows businesses to grow without platform limitations, accommodating increasing product catalogs and customer bases.

### Educational Value to Institution

From an academic perspective, this project demonstrates practical application of full-stack development principles, integrating multiple technologies into a cohesive system. Students and faculty can study the implementation as a reference for modern web development, mobile application development, database design, and API architecture.

The project showcases industry-standard practices including security implementation, RESTful API design, responsive web design, and mobile-first development. It serves as a comprehensive example of how theoretical computer science concepts translate into real-world applications.

The documentation and codebase provide educational resources for future students learning e-commerce development, full-stack programming, and software engineering principles.

## 1.7 Target Users

### Customers

The primary target users are online shoppers seeking convenient access to products. This includes:

- **Tech-savvy consumers** who prefer online shopping for convenience and variety
- **Busy professionals** who lack time for traditional shopping
- **Mobile-first users** who primarily access internet services via smartphones
- **Price-conscious shoppers** who want to compare products and prices easily
- **Remote customers** who lack access to physical stores in their area

### Administrators

Business owners and staff who manage the e-commerce operations:

- **Store owners** who need to manage product catalogs and monitor sales
- **Inventory managers** responsible for stock levels and product information
- **Customer service representatives** handling order inquiries and issues
- **Marketing personnel** analyzing customer behavior and sales trends

### Future Vendors

The platform architecture supports future expansion to a multi-vendor marketplace:

- **Small businesses** seeking online presence without technical expertise
- **Individual sellers** wanting to reach broader markets
- **Specialty retailers** offering niche products
- **Local artisans** expanding beyond local markets

The system's scalable design accommodates growth from single-vendor to multi-vendor marketplace, making it suitable for various business models and future expansion opportunities.
