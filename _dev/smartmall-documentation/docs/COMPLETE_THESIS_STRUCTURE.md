# SMART MALL E-COMMERCE SYSTEM
## A Full-Stack Web and Mobile Commerce Platform with Integrated Payment Gateway

**A Thesis Submitted in Partial Fulfillment of the Requirements for the Degree of [Your Degree]**

**By**  
**[Your Name]**  
**[Your ID Number]**

**Department of [Your Department]**  
**[Your Institution Name]**  
**[Month, Year]**

---

## DECLARATION PAGE

### STUDENT ORIGINALITY STATEMENT

I hereby declare that this project report entitled **"Smart Mall E-Commerce System: A Full-Stack Web and Mobile Commerce Platform with Integrated Payment Gateway"** is my own original work and has been carried out under the supervision of [Supervisor Name]. All sources of information and assistance have been duly acknowledged. This work has not been submitted elsewhere for any other degree or qualification.

**Student Name:** [Your Full Name]  
**Student ID:** [Your ID Number]  
**Department:** [Your Department]  
**Signature:** _______________  
**Date:** _______________

---

## APPROVAL PAGE

This project report entitled **"Smart Mall E-Commerce System: A Full-Stack Web and Mobile Commerce Platform with Integrated Payment Gateway"** submitted by [Your Name], ID: [Your ID], has been examined and is approved for the award of [Degree Name] in [Department Name].

**Project Supervisor:**  
Name: _______________  
Title: _______________  
Signature: _______________  
Date: _______________

**Head of Department:**  
Name: _______________  
Title: _______________  
Signature: _______________  
Date: _______________

**External Examiner:**  
Name: _______________  
Title: _______________  
Signature: _______________  
Date: _______________

**Dean of Faculty:**  
Name: _______________  
Title: _______________  
Signature: _______________  
Date: _______________

---

## ACKNOWLEDGMENT

I would like to express my sincere gratitude and appreciation to all those who contributed to the successful completion of this project.

First and foremost, I am deeply grateful to my project supervisor, [Supervisor Name], for their invaluable guidance, continuous support, constructive feedback, and patience throughout the development of this system. Their expertise in software engineering and e-commerce systems significantly shaped the direction and quality of this work.

I extend my heartfelt thanks to the faculty members of the [Department Name] for their knowledge, expertise, and dedication that laid the foundation for my understanding of full-stack development, database management, and software engineering principles.

Special appreciation goes to my family for their unwavering support, encouragement, and understanding during the intensive development period of this project. Their moral support was instrumental in overcoming challenges and maintaining focus.

I am grateful to my fellow students and friends who provided valuable feedback during the testing phase and offered suggestions that improved the system's usability and functionality.

I acknowledge the open-source community and various online resources, including PHP documentation, Flutter community, MySQL documentation, and Stack Overflow contributors, whose shared knowledge and tools were essential in implementing this system.

Finally, I thank all the test users who volunteered their time to test the system and provided honest feedback that helped identify issues and improve the overall user experience.

---

## ABSTRACT

Traditional shopping methods face significant limitations including geographical constraints, time restrictions, limited product accessibility, and inefficient comparison shopping. Small businesses struggle to reach wider markets while customers face inconvenience in browsing products and completing purchases. The absence of integrated mobile commerce solutions further restricts shopping flexibility in today's mobile-first digital landscape. Existing e-commerce platforms often suffer from poor mobile support, limited payment options, and complex user interfaces that create friction in the shopping experience.

This project presents Smart Mall, a comprehensive full-stack e-commerce system that addresses these challenges through an integrated web and mobile platform with secure payment processing. The system provides a complete online marketplace where customers can browse 131 products across 4 categories (Fashion & Apparel, Electronics & Gadgets, Home & Living, Beauty & Health), manage shopping carts, and complete secure transactions. Administrators can efficiently manage products, process orders, and monitor business operations through a dedicated dashboard with real-time analytics.

The system is built using modern web technologies including HTML5, CSS3, and JavaScript with Bootstrap framework for the responsive frontend, PHP 7.4+ for backend processing and business logic, MySQL 8.0 for relational database management, and Flutter framework for the cross-platform mobile application. The architecture implements RESTful API design principles for seamless communication between web and mobile platforms. Security features include bcrypt password hashing, prepared statements for SQL injection prevention, CSRF token protection, input validation and sanitization, and token-based authentication for mobile API access.

The implementation successfully delivers 16 core features across 12 mobile screens, 8 database tables, and 5 API endpoints. The system manages 131 real products with complete product information, images, pricing, and inventory tracking. Testing results demonstrate system reliability with average response times under 500ms, successful transaction processing, and proper error handling. The platform is production-ready, deployed on XAMPP/LAMPP server environment, scalable to handle growing user bases, and provides a solid foundation for future enhancements including AI-powered recommendations, multi-vendor marketplace support, and advanced analytics.

**Keywords:** E-commerce, Mobile Commerce, Full-Stack Development, RESTful API, Flutter, PHP, MySQL, Payment Gateway Integration, Chapa Payment, Responsive Web Design, Material Design

---

## TABLE OF CONTENTS

**DECLARATION PAGE** .................................................. i  
**APPROVAL PAGE** ...................................................... ii  
**ACKNOWLEDGMENT** .................................................... iii  
**ABSTRACT** ........................................................... iv  
**TABLE OF CONTENTS** .................................................. vi  
**LIST OF FIGURES** .................................................... ix  
**LIST OF TABLES** ..................................................... xi

**CHAPTER 1: INTRODUCTION** ............................................ 1  
1.1 Background of the Study ............................................ 1  
1.2 Problem Statement .................................................. 3  
    1.2.1 Traditional Shopping Problems ................................ 3  
    1.2.2 Existing E-commerce Problems ................................. 4  
1.3 Proposed Solution .................................................. 5  
    1.3.1 Web Platform ................................................. 5  
    1.3.2 Mobile Application ........................................... 6  
    1.3.3 Payment Integration .......................................... 6  
    1.3.4 Admin Dashboard .............................................. 7  
1.4 Objectives ......................................................... 7  
    1.4.1 General Objective ............................................ 7  
    1.4.2 Specific Objectives .......................................... 8  
1.5 Scope of the System ................................................ 9  
    1.5.1 Included Features ............................................ 9  
    1.5.2 Excluded Features ............................................ 11  
1.6 Significance of the Project ........................................ 12  
    1.6.1 Benefits to Customers ........................................ 12  
    1.6.2 Benefits to Businesses ....................................... 12  
    1.6.3 Educational Value ............................................ 13  
1.7 Target Users ....................................................... 13  
1.8 Organization of the Thesis ......................................... 14

**CHAPTER 2: SYSTEM ANALYSIS** ......................................... 15  
2.1 Existing System Analysis ........................................... 15  
2.2 Limitations of Existing Systems .................................... 17  
2.3 Proposed System Overview ........................................... 19  
    2.3.1 Web System ................................................... 19  
    2.3.2 Mobile Application ........................................... 20  
    2.3.3 Payment System ............................................... 20  
    2.3.4 System Workflow .............................................. 21  
2.4 Functional Requirements ............................................ 22  
    2.4.1 Customer Requirements ........................................ 22  
    2.4.2 Admin Requirements ........................................... 24  
    2.4.3 Mobile App Requirements ...................................... 26  
2.5 Non-Functional Requirements ........................................ 27  
    2.5.1 Security Requirements ........................................ 27  
    2.5.2 Performance Requirements ..................................... 28  
    2.5.3 Reliability Requirements ..................................... 29  
    2.5.4 Scalability Requirements ..................................... 29  
    2.5.5 Usability Requirements ....................................... 30  
2.6 Use Case Diagram ................................................... 31  
2.7 Data Flow Diagram (DFD) ............................................ 33  
    2.7.1 Level 0 DFD (Context Diagram) ................................ 33  
    2.7.2 Level 1 DFD (Detailed System) ................................ 34

**CHAPTER 3: SYSTEM DESIGN** ........................................... 36  
3.1 System Architecture ................................................ 36  
    3.1.1 Three-Tier Architecture ...................................... 36  
    3.1.2 Presentation Layer ........................................... 38  
    3.1.3 Application Layer ............................................ 39  
    3.1.4 Data Layer ................................................... 40  
    3.1.5 External Services ............................................ 41  
3.2 User Interface Design .............................................. 42  
    3.2.1 Home Page Interface .......................................... 42  
    3.2.2 Product Listing Interface .................................... 44  
    3.2.3 Product Detail Interface ..................................... 45  
    3.2.4 Shopping Cart Interface ...................................... 46  
    3.2.5 Checkout Interface ........................................... 47  
    3.2.6 Payment Interface ............................................ 48  
    3.2.7 Login and Registration Interface ............................. 49  
    3.2.8 Admin Dashboard Interface .................................... 50  
    3.2.9 Mobile Home Screen ........................................... 52  
    3.2.10 Mobile Product Screen ....................................... 53  
    3.2.11 Mobile Cart Screen .......................................... 54  
    3.2.12 Mobile Checkout Screen ...................................... 55  
    3.2.13 Mobile Payment Screen ....................................... 56  
3.3 Navigation Flow Diagram ............................................ 57  
3.4 Database Design .................................................... 59  
    3.4.1 Database Tables .............................................. 59  
    3.4.2 Table Relationships .......................................... 62  
3.5 ER Diagram ......................................................... 64  
3.6 Database Schema Diagram ............................................ 66  
3.7 API/Backend Design ................................................. 68  
    3.7.1 Authentication API ........................................... 68  
    3.7.2 Product API .................................................. 69  
    3.7.3 Cart API ..................................................... 70  
    3.7.4 Order API .................................................... 71  
    3.7.5 Payment API .................................................. 72  
3.8 Security Design .................................................... 73  
    3.8.1 Authentication Security ...................................... 73  
    3.8.2 Data Security ................................................ 74  
    3.8.3 Payment Security ............................................. 75

**CHAPTER 4: SYSTEM IMPLEMENTATION** ................................... 76  
4.1 Technology Stack ................................................... 76  
4.2 Frontend Implementation ............................................ 78  
    4.2.1 Responsive Design ............................................ 78  
    4.2.2 Navigation Bar ............................................... 79  
    4.2.3 Product Cards ................................................ 80  
    4.2.4 Cart UI ...................................................... 81  
    4.2.5 Checkout UI .................................................. 82  
4.3 Backend Implementation ............................................. 83  
    4.3.1 Session Management ........................................... 83  
    4.3.2 Authentication System ........................................ 84  
    4.3.3 CRUD Operations .............................................. 85  
    4.3.4 Order Processing ............................................. 86  
4.4 Mobile App Implementation .......................................... 87  
    4.4.1 Flutter Project Structure .................................... 87  
    4.4.2 API Communication ............................................ 88  
    4.4.3 State Management ............................................. 89  
    4.4.4 Mobile Navigation ............................................ 90  
4.5 Database Implementation ............................................ 91  
    4.5.1 SQL Queries .................................................. 91  
    4.5.2 Insert/Update/Delete Operations .............................. 92  
    4.5.3 Relationships and Constraints ................................ 93  
4.6 Payment Gateway Integration ........................................ 94  
    4.6.1 Chapa Integration Setup ...................................... 94  
    4.6.2 Payment Request Flow ......................................... 95  
    4.6.3 Transaction Verification ..................................... 96  
    4.6.4 Payment Confirmation ......................................... 97  
    4.6.5 Order Update After Payment ................................... 98  
4.7 System Integration ................................................. 99  
    4.7.1 Frontend-Backend Integration ................................. 99  
    4.7.2 Mobile-Backend Integration ................................... 100  
    4.7.3 Database Integration ......................................... 101  
    4.7.4 Payment Gateway Integration .................................. 102

**CHAPTER 5: TESTING AND SECURITY** .................................... 103  
5.1 Testing Strategy ................................................... 103  
    5.1.1 Unit Testing ................................................. 103  
    5.1.2 Integration Testing .......................................... 104  
    5.1.3 User Acceptance Testing ...................................... 105  
5.2 Functional Testing ................................................. 106  
    5.2.1 Login Testing ................................................ 106  
    5.2.2 Product Browsing Testing ..................................... 107  
    5.2.3 Cart Testing ................................................. 108  
    5.2.4 Checkout Testing ............................................. 109  
    5.2.5 Payment Testing .............................................. 110  
5.3 Mobile Testing ..................................................... 111  
    5.3.1 Mobile Responsiveness ........................................ 111  
    5.3.2 Mobile Checkout .............................................. 112  
    5.3.3 Mobile Payment ............................................... 113  
5.4 Payment Testing .................................................... 114  
    5.4.1 Successful Payment ........................................... 114  
    5.4.2 Failed Payment ............................................... 115  
    5.4.3 Transaction Validation ....................................... 116  
5.5 Security Analysis .................................................. 117  
    5.5.1 SQL Injection Prevention ..................................... 117  
    5.5.2 Password Hashing ............................................. 118  
    5.5.3 Session Security ............................................. 119  
    5.5.4 Input Validation ............................................. 120

**CHAPTER 6: DEPLOYMENT AND MAINTENANCE** .............................. 121  
6.1 Deployment Environment ............................................. 121  
    6.1.1 Server Environment ........................................... 121  
    6.1.2 Apache Configuration ......................................... 122  
    6.1.3 PHP Configuration ............................................ 123  
    6.1.4 MySQL Configuration .......................................... 124  
6.2 Web Deployment ..................................................... 125  
    6.2.1 File Upload .................................................. 125  
    6.2.2 Database Configuration ....................................... 126  
    6.2.3 Environment Setup ............................................ 127  
6.3 Mobile Deployment .................................................. 128  
    6.3.1 APK Generation ............................................... 128  
    6.3.2 APK Installation ............................................. 129  
    6.3.3 Mobile Distribution .......................................... 130  
6.4 Maintenance ........................................................ 131  
    6.4.1 System Updates ............................................... 131  
    6.4.2 Database Backups ............................................. 132  
    6.4.3 Bug Fixing ................................................... 133  
    6.4.4 Performance Monitoring ....................................... 134  
6.5 Future Enhancements ................................................ 135  
    6.5.1 AI Product Recommendations ................................... 135  
    6.5.2 Multi-Language Support ....................................... 136  
    6.5.3 Advanced Analytics ........................................... 137  
    6.5.4 Multi-Vendor Marketplace ..................................... 138

**CHAPTER 7: CONCLUSION** .............................................. 139  
7.1 Summary of Achievements ............................................ 139  
7.2 Challenges Faced ................................................... 140  
7.3 Lessons Learned .................................................... 141  
7.4 Recommendations .................................................... 142  
7.5 Final Remarks ...................................................... 143

**REFERENCES** ......................................................... 144

**APPENDICES** ......................................................... 146  
Appendix A: Complete SQL Schema ........................................ 146  
Appendix B: API Endpoint Documentation ................................. 150  
Appendix C: System Screenshots ......................................... 153  
Appendix D: Testing Evidence ........................................... 158  
Appendix E: User Manual ................................................ 161  
Appendix F: Source Code Samples ........................................ 164

---

## LIST OF FIGURES

Figure 2.1: Smart Mall Use Case Diagram ................................ 31  
Figure 2.2: Level 0 DFD (Context Diagram) .............................. 33  
Figure 2.3: Level 1 DFD (Detailed System) .............................. 34  

Figure 3.1: System Architecture Diagram ................................ 37  
Figure 3.2: Web Home Page Interface .................................... 43  
Figure 3.3: Product Listing with Filters ............................... 44  
Figure 3.4: Product Detail Page ........................................ 45  
Figure 3.5: Shopping Cart Screen ....................................... 46  
Figure 3.6: Checkout Form .............................................. 47  
Figure 3.7: Payment Processing Screen .................................. 48  
Figure 3.8: Login Screen ............................................... 49  
Figure 3.9: Registration Screen ........................................ 49  
Figure 3.10: Admin Dashboard ........................................... 51  
Figure 3.11: Mobile Home Screen ........................................ 52  
Figure 3.12: Mobile Product Screen ..................................... 53  
Figure 3.13: Mobile Cart Screen ........................................ 54  
Figure 3.14: Mobile Checkout Screen .................................... 55  
Figure 3.15: Mobile Payment Screen ..................................... 56  
Figure 3.16: Navigation Flow Diagram ................................... 58  
Figure 3.17: Entity Relationship Diagram ............................... 65  
Figure 3.18: Database Schema Diagram ................................... 67  
Figure 3.19: Security Architecture ..................................... 73  

Figure 4.1: Responsive Design Breakpoints .............................. 78  
Figure 4.2: Flutter Project Structure .................................. 87  
Figure 4.3: Payment Flow Diagram ....................................... 95  
Figure 4.4: System Integration Architecture ............................ 99  

Figure 5.1: Testing Workflow ........................................... 103  
Figure 5.2: Security Testing Results ................................... 117  

Figure 6.1: Deployment Architecture .................................... 121  
Figure 6.2: APK Build Process .......................................... 128  

---

## LIST OF TABLES

Table 2.1: Customer Functional Requirements ............................ 22  
Table 2.2: Admin Functional Requirements ............................... 24  
Table 2.3: Mobile App Functional Requirements .......................... 26  

Table 3.1: Database Tables Overview .................................... 59  
Table 3.2: Users Table Schema .......................................... 60  
Table 3.3: Products Table Schema ....................................... 60  
Table 3.4: Categories Table Schema ..................................... 61  
Table 3.5: Orders Table Schema ......................................... 61  
Table 3.6: Order Items Table Schema .................................... 62  
Table 3.7: Payments Table Schema ....................................... 62  
Table 3.8: Cart Table Schema ........................................... 63  
Table 3.9: Password Resets Table Schema ................................ 63  
Table 3.10: API Endpoints Summary ...................................... 68  

Table 4.1: Technology Stack ............................................ 77  
Table 4.2: Frontend Technologies ....................................... 78  
Table 4.3: Backend Technologies ........................................ 83  
Table 4.4: Database Queries Summary .................................... 91  

Table 5.1: Functional Test Cases ....................................... 106  
Table 5.2: Mobile Test Cases ........................................... 111  
Table 5.3: Payment Test Cases .......................................... 114  
Table 5.4: Security Test Results ....................................... 117  

Table 6.1: Server Requirements ......................................... 121  
Table 6.2: Maintenance Schedule ........................................ 131  

---

*[Document continues with full chapters - This is the complete structure. Due to length, I'll create the full content in the next message]*
