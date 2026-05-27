const fs = require("fs");
const path = require("path");
const {
  Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
  Header, Footer, AlignmentType, LevelFormat, PageNumber, PageBreak,
  TableOfContents, HeadingLevel, BorderStyle, WidthType, ShadingType,
  ImageRun
} = require("docx");

const bdr = { style: BorderStyle.SINGLE, size: 1, color: "999999" };
const bdrs = { top: bdr, bottom: bdr, left: bdr, right: bdr };
const cm = { top: 60, bottom: 60, left: 100, right: 100 };
const pw = 12240;
const mg = 1440;
const cw = pw - 2 * mg;

function p(text) {
  return new Paragraph({ spacing: { after: 120 }, children: [new TextRun({ text, font: "Times New Roman", size: 24 })] });
}
function pi(text) {
  return new Paragraph({ spacing: { after: 120 }, indent: { left: 360 }, children: [new TextRun({ text, font: "Times New Roman", size: 24, italics: true })] });
}
function pb(label, value) {
  return new Paragraph({ spacing: { after: 80 }, children: [
    new TextRun({ text: label, bold: true, font: "Times New Roman", size: 24 }),
    new TextRun({ text: String(value), font: "Times New Roman", size: 24 }),
  ]});
}
function sp(pts) { return new Paragraph({ spacing: { after: pts || 200 }, children: [] }); }
function h(lvl, txt) {
  const m = {1:HeadingLevel.HEADING_1,2:HeadingLevel.HEADING_2,3:HeadingLevel.HEADING_3,4:HeadingLevel.HEADING_4};
  return new Paragraph({ heading: m[lvl], children: [new TextRun({ text: txt, bold: true, font: "Times New Roman" })] });
}
function bt(text) {
  return new Paragraph({ numbering: { reference: "b", level: 0 }, spacing: { after: 60 }, children: [new TextRun({ text, font: "Times New Roman", size: 24 })] });
}
function nm(text) {
  return new Paragraph({ numbering: { reference: "n", level: 0 }, spacing: { after: 60 }, children: [new TextRun({ text, font: "Times New Roman", size: 24 })] });
}
function sn(text) {
  return new Paragraph({ numbering: { reference: "sn", level: 0 }, spacing: { after: 60 }, children: [new TextRun({ text, font: "Times New Roman", size: 24 })] });
}
function mon(text) {
  return new Paragraph({ spacing: { after: 40 }, shading: { fill: "F0F0F0", type: ShadingType.CLEAR }, indent: { left: 360 },
    children: [new TextRun({ text, font: "Courier New", size: 20 })] });
}
function hCell(text, w) {
  return new TableCell({ borders: bdrs, width: { size: w, type: WidthType.DXA }, shading: { fill: "2E4057", type: ShadingType.CLEAR },
    margins: cm, verticalAlign: "center",
    children: [new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ text, bold: true, font: "Times New Roman", size: 22, color: "FFFFFF" })] })] });
}
function dCell(text, w, alt) {
  return new TableCell({ borders: bdrs, width: { size: w, type: WidthType.DXA },
    shading: alt ? { fill: "F5F5F5", type: ShadingType.CLEAR } : undefined, margins: cm,
    children: [new Paragraph({ children: [new TextRun({ text: String(text), font: "Times New Roman", size: 22 })] })] });
}
function tbl(rows, widths) {
  return new Table({ width: { size: cw, type: WidthType.DXA }, columnWidths: widths,
    rows: [new TableRow({ children: rows[0].map((h,i) => hCell(h, widths[i])) }),
      ...rows.slice(1).map((r,ri) => new TableRow({ children: r.map((c,i) => dCell(c, widths[i], ri%2===1)) }))] });
}
function imgBlock(filename, wPx) {
  const fp = path.join(__dirname, "screenshots", filename);
  if (!fs.existsSync(fp)) return [p("[Screenshot: " + filename + " — insert image here]")];
  const data = fs.readFileSync(fp);
  const emuW = Math.round((wPx||550) * 914400 / 96);
  const emuH = Math.round(emuW * 0.667);
  return [new Paragraph({ alignment: AlignmentType.CENTER, spacing: { before: 200, after: 200 },
    children: [new ImageRun({ type: "png", data, transformation: { width: emuW, height: emuH } })] })];
}

const C = [];

// ===== FRONT MATTER =====
C.push(sp(2500));
C.push(new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 200 }, children: [new TextRun({ text: "SMART MALL", size: 72, bold: true, font: "Times New Roman", color: "1A365D" })] }));
C.push(new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 120 }, children: [new TextRun({ text: "A Full-Stack E-Commerce Platform with Mobile Application", size: 36, font: "Times New Roman", color: "2E4057" })] }));
C.push(new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 120 }, children: [new TextRun({ text: "for the Ethiopian Market", size: 32, font: "Times New Roman", color: "4A6FA5" })] }));
C.push(sp(600));
C.push(new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 120 }, children: [new TextRun({ text: "FINAL YEAR PROJECT REPORT", size: 34, bold: true, font: "Times New Roman", color: "1A365D" })] }));
C.push(sp(400));
C.push(new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 60 }, children: [new TextRun({ text: "Submitted to the Department of Computer Science", size: 26, font: "Times New Roman", color: "333333" })] }));
C.push(new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 60 }, children: [new TextRun({ text: "[Insert University Name]", size: 26, font: "Times New Roman", color: "336699", italics: true })] }));
C.push(sp(400));
C.push(new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 60 }, children: [new TextRun({ text: "Prepared By:", size: 24, font: "Times New Roman", color: "555555" })] }));
C.push(new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 40 }, children: [new TextRun({ text: "[Insert Your Full Name]", size: 26, font: "Times New Roman", color: "1A365D", bold: true })] }));
C.push(new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 40 }, children: [new TextRun({ text: "[Insert Student ID / Registration Number]", size: 24, font: "Times New Roman", color: "555555" })] }));
C.push(sp(400));
C.push(new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 40 }, children: [new TextRun({ text: "Supervised By:", size: 24, font: "Times New Roman", color: "555555" })] }));
C.push(new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 40 }, children: [new TextRun({ text: "[Insert Supervisor Name]", size: 24, font: "Times New Roman", color: "1A365D" })] }));
C.push(sp(500));
C.push(new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ text: "[Insert Month, Year]", size: 24, font: "Times New Roman", color: "888888", italics: true })] }));
C.push(new Paragraph({ children: [new PageBreak()] }));

// Declaration
C.push(h(1, "DECLARATION"));
C.push(sp(300));
C.push(p("I, [Insert Your Full Name], hereby declare that this project report titled \"Smart Mall: A Full-Stack E-Commerce Platform with Mobile Application for the Ethiopian Market\" is my original work. I confirm that:"));
C.push(sp());
C.push(nm("This work has not been submitted previously for any degree or qualification at any other institution."));
C.push(nm("All sources of information have been properly acknowledged and referenced."));
C.push(nm("The code, design, and implementation presented in this report are my own work unless otherwise credited."));
C.push(nm("Where the work of others has been used, it has been duly attributed through citation and references."));
C.push(sp(200));
C.push(p("I understand that plagiarism is a serious academic offense and that the university reserves the right to take appropriate action if any part of this work is found to be plagiarized."));
C.push(sp(400));
C.push(pb("Name: ", "[Insert Your Full Name]"));
C.push(pb("Student ID: ", "[Insert Student ID]"));
C.push(pb("Date: ", "[Insert Date]"));
C.push(pb("Signature: ", "________________________"));
C.push(sp(200));
C.push(p("This declaration is made in accordance with the university's academic integrity policy."));
C.push(new Paragraph({ children: [new PageBreak()] }));

// Approval Page
C.push(h(1, "APPROVAL PAGE"));
C.push(sp(300));
C.push(p("This project report entitled \"Smart Mall: A Full-Stack E-Commerce Platform with Mobile Application for the Ethiopian Market\" has been read and approved as meeting the requirements for the award of the degree of Bachelor of Science in Computer Science."));
C.push(sp(400));
C.push(pb("Supervisor: ", "[Insert Supervisor Name]"));
C.push(pb("Signature: ", "________________________"));
C.push(pb("Date: ", "[Insert Date]"));
C.push(sp(300));
C.push(pb("Internal Examiner: ", "[Insert Internal Examiner Name]"));
C.push(pb("Signature: ", "________________________"));
C.push(pb("Date: ", "[Insert Date]"));
C.push(sp(300));
C.push(pb("External Examiner: ", "[Insert External Examiner Name]"));
C.push(pb("Signature: ", "________________________"));
C.push(pb("Date: ", "[Insert Date]"));
C.push(sp(300));
C.push(pb("Department Head: ", "[Insert Department Head Name]"));
C.push(pb("Signature: ", "________________________"));
C.push(pb("Date: ", "[Insert Date]"));
C.push(new Paragraph({ children: [new PageBreak()] }));

// Acknowledgment
C.push(h(1, "ACKNOWLEDGMENT"));
C.push(sp(300));
C.push(p("[Insert acknowledgment text here]"));
C.push(sp());
C.push(p("I would like to express my sincere gratitude to my supervisor, [Insert Supervisor Name], for their invaluable guidance, constructive feedback, and continuous support throughout the development of this project. Their expertise and encouragement have been instrumental in shaping this work."));
C.push(sp());
C.push(p("I am also grateful to the Department of Computer Science at [Insert University Name] for providing the necessary resources and academic environment that made this project possible."));
C.push(sp());
C.push(p("Special thanks go to my family and friends for their unwavering support, patience, and understanding during the many hours spent researching, coding, and writing this report."));
C.push(sp());
C.push(p("Finally, I would like to acknowledge the developers of the open-source technologies used in this project, including PHP, MySQL, Apache, Capacitor, and the many libraries and tools that made this work feasible."));
C.push(sp(300));
C.push(pb("Name: ", "[Insert Your Full Name]"));
C.push(pb("Date: ", "[Insert Date]"));
C.push(pb("Place: ", "[Insert Location]"));
C.push(new Paragraph({ children: [new PageBreak()] }));

// Abstract
C.push(h(1, "ABSTRACT"));
C.push(sp(300));
C.push(p("E-commerce has transformed global retail, yet the Ethiopian market remains underserved by locally adapted online shopping platforms. Most existing solutions lack support for the Ethiopian Birr (ETB), fail to integrate with local payment gateways, and do not provide dedicated mobile applications for the growing number of smartphone users in the country. This project presents Smart Mall, a full-stack e-commerce platform designed specifically for the Ethiopian market, addressing these gaps through a comprehensive technical solution."));
C.push(sp());
C.push(p("Smart Mall is built using PHP 8.0+ and MySQL (via PDO) for the backend, with HTML5, CSS3, and vanilla JavaScript for the frontend. The platform features a complete product catalog with category filtering, a server-side shopping cart with stock validation, a secure checkout system supporting three payment methods (Chapa Pay, Bank Transfer, and Cash on Delivery), and a comprehensive admin panel for store management. A key innovation is the multi-currency engine that displays prices in both USD and ETB using live exchange rates fetched from the ExchangeRate-API, with file-based caching for optimal performance."));
C.push(sp());
C.push(p("The system includes a companion mobile application built with Capacitor v8, wrapping the web platform into a native Android app using Android Studio. The mobile app features immersive full-screen browsing, hardware back-button navigation, and deep-linking support for Chapa payment callbacks. Security is implemented through CSRF token verification on all POST operations, PDO prepared statements to prevent SQL injection, bcrypt password hashing, htmlspecialchars output escaping for XSS prevention, and HTTP security headers including X-Content-Type-Options and X-Frame-Options."));
C.push(sp());
C.push(p("The platform was developed locally using XAMPP and deployed on FreeProHost shared hosting for production. Testing covered functional testing of all user flows, payment verification testing in Chapa sandbox mode, mobile app testing on Android emulator and physical devices, and security testing including CSRF bypass attempts, SQL injection probes, and XSS attack vectors. The results demonstrate that Smart Mall provides a viable, secure, and locally adapted e-commerce solution for the Ethiopian market."));
C.push(sp(200));
C.push(pb("Keywords: ", "E-Commerce, PHP, MySQL, Chapa Payment Gateway, Capacitor, Android, Multi-Currency, Ethiopia, Full-Stack, Mobile Application, PWA"));
C.push(new Paragraph({ children: [new PageBreak()] }));

// ToC
C.push(h(1, "TABLE OF CONTENTS"));
C.push(new TableOfContents("Table of Contents", { hyperlink: true, headingStyleRange: "1-4" }));
C.push(new Paragraph({ children: [new PageBreak()] }));

// LoF
C.push(h(1, "LIST OF FIGURES"));
C.push(sp(200));
C.push(p("Figure 3.1: System Architecture Diagram"));
C.push(p("Figure 3.2: Use Case Diagram"));
C.push(p("Figure 3.3: Data Flow Diagram — Level 0 Context"));
C.push(p("Figure 3.4: Data Flow Diagram — Level 1"));
C.push(p("Figure 3.5: Entity-Relationship Diagram"));
C.push(p("Figure 3.6: Navigation Flow Diagram"));
C.push(p("Figure 4.1: Homepage — Product Grid"));
C.push(p("Figure 4.2: Product Detail Page"));
C.push(p("Figure 4.3: Shopping Cart"));
C.push(p("Figure 4.4: Checkout Page"));
C.push(p("Figure 4.5: Order Confirmation"));
C.push(p("Figure 4.6: User Registration"));
C.push(p("Figure 4.7: User Login"));
C.push(p("Figure 4.8: Admin Dashboard"));
C.push(p("Figure 4.9: Admin Add Product Form"));
C.push(p("Figure 4.10: Admin Manage Orders"));
C.push(p("Figure 4.11: Admin Manage Categories"));
C.push(p("Figure 4.12: Mobile App — Homepage View"));
C.push(p("Figure 4.13: Password Reset Flow"));
C.push(p("Figure 5.1: Payment Verification Flow"));
C.push(p("Figure 6.1: Deployment Architecture"));
C.push(new Paragraph({ children: [new PageBreak()] }));

// LoT
C.push(h(1, "LIST OF TABLES"));
C.push(sp(200));
C.push(p("Table 3.1: Users Table Schema"));
C.push(p("Table 3.2: Categories Table Schema"));
C.push(p("Table 3.3: Products Table Schema"));
C.push(p("Table 3.4: Cart Table Schema"));
C.push(p("Table 3.5: Orders Table Schema"));
C.push(p("Table 3.6: Order Items Table Schema"));
C.push(p("Table 3.7: Payments Table Schema"));
C.push(p("Table 3.8: Password Resets Table Schema"));
C.push(p("Table 3.9: API Endpoint Reference"));
C.push(p("Table 4.1: Technology Stack Summary"));
C.push(p("Table 4.2: File Structure — Root Directory"));
C.push(p("Table 4.3: File Structure — Includes Directory"));
C.push(p("Table 4.4: File Structure — Admin Directory"));
C.push(p("Table 5.1: Functional Test Cases"));
C.push(p("Table 5.2: Mobile Test Cases"));
C.push(p("Table 5.3: Payment Test Cases"));
C.push(p("Table 5.4: Security Test Cases"));
C.push(p("Table 6.1: Maintenance Schedule"));
C.push(new Paragraph({ children: [new PageBreak()] }));

// ================================================================
// CHAPTER 1
// ================================================================
C.push(h(1, "CHAPTER 1: INTRODUCTION"));
C.push(sp(200));

C.push(h(2, "1.1 Background of the Study"));
C.push(sp(100));
C.push(p("The rapid advancement of information and communication technologies has fundamentally transformed the way businesses operate and consumers shop. E-commerce, defined as the buying and selling of goods and services over electronic networks, has grown exponentially worldwide, with global e-commerce sales reaching trillions of dollars annually. Platforms such as Amazon, Alibaba, and eBay have set global standards for online retail, offering consumers convenience, competitive pricing, and access to a vast array of products from anywhere at any time."));
C.push(sp());
C.push(p("In Africa, e-commerce adoption has been accelerating, driven by increasing internet penetration, mobile phone adoption, and digital payment innovations. Countries like Kenya, Nigeria, and South Africa have seen significant growth in online retail, with platforms such as Jumia, Kilimall, and Takealot serving millions of customers. However, the Ethiopian market presents a unique set of challenges and opportunities that distinguish it from other African e-commerce landscapes."));
C.push(sp());
C.push(p("Ethiopia, with a population exceeding 120 million, represents one of Africa's largest untapped e-commerce markets. Internet penetration has been growing steadily, reaching approximately 25 million users, and mobile phone adoption continues to rise. The Ethiopian government has been actively promoting digital transformation through initiatives such as the Digital Ethiopia 2025 strategy, which aims to leverage technology for economic development. Despite these positive trends, several barriers have hindered the growth of local e-commerce platforms."));
C.push(sp());
C.push(p("One of the most significant barriers is the currency situation. Ethiopia operates a dual-currency environment where many high-value transactions are conducted in US Dollars (USD), while day-to-day retail transactions use the Ethiopian Birr (ETB). Most global e-commerce platforms and payment gateways do not support ETB, forcing Ethiopian consumers to navigate complex currency conversion processes when shopping online. Additionally, the country's banking infrastructure and payment card penetration remain limited, making traditional online payment methods (credit/debit cards) inaccessible to a large portion of the population."));
C.push(sp());
C.push(p("The emergence of local payment gateways such as Chapa, Telebirr, and Amole has begun to address the payment infrastructure gap. Chapa, in particular, provides a comprehensive payment API that supports ETB transactions, multiple payment methods (including mobile money, bank transfers, and card payments), and is specifically designed for the Ethiopian market. However, few e-commerce platforms have integrated these local payment solutions, representing a significant opportunity for locally adapted online retail solutions."));
C.push(sp());
C.push(p("Smartphones have become the primary internet access device for most Ethiopians. According to recent statistics, over 60% of internet users in Ethiopia access the web through mobile devices. This trend underscores the critical importance of mobile-friendly e-commerce solutions and dedicated mobile applications. While many global platforms offer mobile apps, they often do not account for the specific needs and constraints of the Ethiopian market, including bandwidth limitations, device variety, and local payment preferences."));
C.push(sp());
C.push(p("This project, Smart Mall, was conceived to address these gaps by developing a full-stack e-commerce platform that combines a robust web application with a companion mobile app, specifically designed for the Ethiopian market. The platform incorporates multi-currency support (USD and ETB), local payment gateway integration (Chapa), responsive design for mobile access, and a Capacitor-based Android application for an enhanced mobile shopping experience."));

C.push(h(2, "1.2 Problem Statement"));
C.push(sp(100));
C.push(p("Despite the growing demand for online shopping in Ethiopia, several critical problems persist in the current e-commerce landscape:"));
C.push(sp());
C.push(nm("Limited Local E-Commerce Platforms: Existing e-commerce platforms in Ethiopia are either international platforms that do not cater to local needs or are small-scale solutions with limited functionality, poor user experience, and inadequate security measures."));
C.push(nm("Multi-Currency Gap: Most e-commerce platforms do not support the Ethiopian Birr (ETB), requiring customers to transact in foreign currencies. This creates confusion, additional costs, and barriers to adoption for local consumers who primarily think and transact in ETB."));
C.push(nm("Payment Gateway Integration: There is a lack of locally adapted payment solutions integrated into e-commerce platforms. Global payment gateways often do not support ETB or local payment methods, while local gateways like Chapa have limited integration support and documentation for custom platforms."));
C.push(nm("Mobile Application Deficiency: Most Ethiopian e-commerce platforms lack dedicated mobile applications, relying solely on mobile-responsive websites. This limits their ability to provide features such as push notifications, offline capabilities, and deep linking, which are essential for a modern mobile shopping experience."));
C.push(nm("Security Vulnerabilities: Many locally developed e-commerce platforms suffer from inadequate security practices, including weak password policies, lack of CSRF protection, susceptibility to SQL injection attacks, and insufficient output escaping, putting user data and transactions at risk."));
C.push(nm("Admin Management Complexity: Store owners often lack intuitive tools for managing products, tracking orders, and analyzing sales performance. Existing admin interfaces are either too complex or lack essential features for effective store management."));
C.push(sp());
C.push(p("These problems collectively create a significant barrier to the adoption and growth of e-commerce in Ethiopia, limiting both consumer access to online shopping and business opportunities for local merchants."));

C.push(h(2, "1.3 Proposed Solution"));
C.push(sp(100));
C.push(p("Smart Mall is proposed as a comprehensive solution to the problems identified above. The platform is a full-stack e-commerce system that provides:"));
C.push(sp());
C.push(bt("A complete web-based e-commerce platform built with PHP 8.0+ and MySQL, featuring a product catalog, shopping cart, checkout system, user management, and administrative tools."));
C.push(bt("Multi-currency support that displays prices in both USD and ETB, with live exchange rates fetched from the ExchangeRate-API and cached locally for performance."));
C.push(bt("Integration with the Chapa payment gateway, enabling ETB transactions through multiple payment methods including mobile money, bank transfers, and card payments."));
C.push(bt("A companion Android mobile application built using Capacitor v8 and Android Studio, wrapping the web platform into a native app with enhanced mobile features such as immersive display, back-button navigation, and deep linking."));
C.push(bt("Comprehensive security measures including CSRF token verification, PDO prepared statements, bcrypt password hashing, XSS prevention through htmlspecialchars output escaping, and HTTP security headers."));
C.push(bt("An intuitive admin dashboard for managing products, categories, orders, and viewing store statistics."));
C.push(sp());
C.push(p("The platform was developed locally using XAMPP (Apache, MySQL, PHP) and deployed to FreeProHost shared hosting for production access. The mobile app was built and tested using Android Studio and deployed as a standalone APK."));

C.push(h(2, "1.4 Objectives"));
C.push(sp(100));

C.push(h(3, "1.4.1 General Objective"));
C.push(sp());
C.push(p("The general objective of this project is to design, develop, and deploy a full-stack e-commerce platform with a companion mobile application that enables online shopping with multi-currency support and local payment gateway integration, specifically tailored for the Ethiopian market."));

C.push(h(3, "1.4.2 Specific Objectives"));
C.push(sp());
C.push(p("The specific objectives of this project are:"));
C.push(sp());
C.push(nm("To analyze existing e-commerce systems and identify their limitations in addressing the needs of the Ethiopian market, including currency support, payment integration, and mobile accessibility."));
C.push(nm("To design a scalable relational database schema using MySQL with InnoDB engine that supports products, categories, users, shopping cart, orders, payments, and password management with appropriate foreign key relationships and indexing."));
C.push(nm("To develop a responsive web frontend using HTML5, CSS3, and vanilla JavaScript that provides an intuitive user interface with product browsing, search, cart management, checkout, and administrative functions."));
C.push(nm("To implement a secure PHP backend using PDO prepared statements for database access, CSRF token verification for form protection, bcrypt for password hashing, and proper output escaping for XSS prevention."));
C.push(nm("To integrate the Chapa payment gateway to enable ETB transactions, including payment initialization, verification, callback handling, and error recovery with stock restoration."));
C.push(nm("To develop a multi-currency engine that fetches live exchange rates from the ExchangeRate-API, caches them locally, and displays prices in both USD and ETB throughout the platform."));
C.push(nm("To build a companion Android mobile application using Capacitor v8 and Android Studio that wraps the web platform with native features including immersive display, back-button navigation, and deep linking for payment callbacks."));
C.push(nm("To create an administrative dashboard with comprehensive store management capabilities including product CRUD, category management with slide images, order tracking with status updates, and sales statistics."));
C.push(nm("To conduct thorough testing covering functional requirements, payment flows, mobile compatibility, and security vulnerabilities including CSRF, SQL injection, and XSS."));
C.push(nm("To deploy the web platform on FreeProHost shared hosting and package the mobile app as an Android APK for distribution."));

C.push(h(2, "1.5 Scope of the System"));
C.push(sp(100));
C.push(p("The scope of Smart Mall encompasses the following features and boundaries:"));
C.push(sp());
C.push(h(4, "Included Features:"));
C.push(bt("Product catalog with category filtering, search, and sorting capabilities."));
C.push(bt("Product detail pages with image gallery, video support, and stock indicators."));
C.push(bt("Server-side shopping cart with quantity management and stock validation."));
C.push(bt("Checkout process with shipping information collection and three payment options."));
C.push(bt("User registration, login, logout, and password reset functionality."));
C.push(bt("Multi-currency display (USD and ETB) with live exchange rate integration."));
C.push(bt("Admin panel for product, category, and order management."));
C.push(bt("AJAX-powered live search with autocomplete suggestions."));
C.push(bt("PWA support with manifest.json for installable web experience."));
C.push(bt("Dark and light theme with persistence via localStorage."));
C.push(bt("Capacitor-based Android mobile application with native features."));
C.push(bt("Responsive design supporting desktop, tablet, and mobile viewports."));
C.push(sp());
C.push(h(4, "Excluded Features:"));
C.push(bt("Multi-vendor marketplace functionality (single vendor/store only)."));
C.push(bt("AI-powered product recommendations or personalized suggestions."));
C.push(bt("Real-time chat or customer support messaging system."));
C.push(bt("Social media login integration (OAuth with Google, Facebook, etc.)."));
C.push(bt("Subscription-based or recurring payment models."));
C.push(bt("iOS mobile application (Capacitor supports iOS but only Android APK was built)."));
C.push(bt("Advanced analytics or business intelligence dashboards."));
C.push(bt("Multiple language localization beyond English."));

C.push(h(2, "1.6 Significance of the Study"));
C.push(sp(100));
C.push(p("This project holds significance for multiple stakeholders:"));
C.push(sp());
C.push(bt("For Ethiopian Consumers: Smart Mall provides a locally adapted online shopping platform with ETB pricing and local payment methods, making e-commerce more accessible and convenient for Ethiopian shoppers."));
C.push(bt("For Local Businesses: The platform demonstrates how small and medium businesses can establish an online presence with a cost-effective, full-stack e-commerce solution that integrates local payment infrastructure."));
C.push(bt("For the Academic Community: This project serves as a comprehensive reference for implementing a production-grade e-commerce system, covering the full technology stack from database design to mobile app deployment, with detailed documentation of architectural decisions and implementation patterns."));
C.push(bt("For Payment Gateway Providers: The integration with Chapa provides a working reference implementation that can be adapted by other developers seeking to integrate local payment solutions into their applications."));
C.push(bt("For Software Developers: The codebase demonstrates best practices in PHP security (CSRF, prepared statements, bcrypt, XSS prevention), multi-currency implementation, AJAX integration, PWA setup, and Capacitor mobile app development."));

C.push(h(2, "1.7 Target Users"));
C.push(sp(100));
C.push(p("Smart Mall is designed for three primary user categories:"));
C.push(sp());
C.push(h(4, "1.7.1 Customers"));
C.push(p("Customers are the primary end-users who browse products, manage their shopping cart, place orders, and make payments. They can register accounts, log in, view order history, and switch between USD and ETB currency display. Customers access the platform through the web interface or the companion Android mobile application. No specialized technical knowledge is required to use the system as a customer."));
C.push(sp());
C.push(h(4, "1.7.2 Administrators"));
C.push(p("Administrators manage the store through a dedicated admin panel. Admin responsibilities include adding and editing products with images and video, organizing products into categories with slide images, processing and updating order statuses, and monitoring store statistics through the dashboard. Admin accounts are distinguished by the 'admin' role in the users table and require elevated privileges."));
C.push(sp());
C.push(h(4, "1.7.3 Mobile Users"));
C.push(p("Mobile users are a subset of customers who prefer using the Android mobile application for shopping. The mobile app provides an optimized experience with immersive full-screen browsing, hardware back-button navigation, and deep linking for seamless payment callbacks. Mobile users access the same backend and share the same accounts as web users, providing a consistent cross-platform experience."));

C.push(new Paragraph({ children: [new PageBreak()] }));

// ================================================================
// CHAPTER 2
// ================================================================
C.push(h(1, "CHAPTER 2: SYSTEM ANALYSIS"));
C.push(sp(200));

C.push(h(2, "2.1 Existing System Analysis"));
C.push(sp(100));
C.push(p("To establish a foundation for the Smart Mall design, an analysis of existing e-commerce systems was conducted. This analysis examined both international platforms and local Ethiopian solutions to identify their features, strengths, and limitations."));
C.push(sp());
C.push(h(4, "2.1.1 International E-Commerce Platforms"));
C.push(sp());
C.push(p("Shopify is a leading global e-commerce platform that enables businesses to create online stores with minimal technical expertise. It offers template-based store design, integrated payment processing, inventory management, and mobile responsiveness. However, Shopify's subscription model (starting at $29/month) is cost-prohibitive for many Ethiopian small businesses. Furthermore, Shopify's payment integration is optimized for global gateways like Stripe and PayPal, with limited support for local Ethiopian payment providers."));
C.push(sp());
C.push(p("WooCommerce, built on WordPress, provides a more customizable open-source alternative. It offers extensive plugin support, flexible payment gateway integration, and full control over the store design. However, WooCommerce requires WordPress hosting, has performance overhead from PHP and MySQL, and the plugins needed for local payment integration and multi-currency support often require additional costs and may not be well-maintained for the Ethiopian context."));
C.push(sp());
C.push(p("Magento (now Adobe Commerce) provides enterprise-grade e-commerce capabilities with advanced features for large-scale operations. It supports multi-store, multi-currency, and complex product catalogs. However, Magento's steep learning curve, high hosting requirements, and development complexity make it unsuitable for small to medium Ethiopian businesses."));
C.push(sp());
C.push(h(4, "2.1.2 Local Ethiopian Platforms"));
C.push(sp());
C.push(p("Jumia Ethiopia, part of the African e-commerce giant Jumia, operates in Ethiopia but focuses primarily on electronics, fashion, and fast-moving consumer goods. While it offers mobile money payment options, it operates as a marketplace rather than a standalone store solution, and its pricing and policies are standardized across multiple African countries rather than specifically adapted for Ethiopia."));
C.push(sp());
C.push(p("Local initiatives such as EthioMart and other small-scale platforms exist but are often limited in functionality, with basic product listings, minimal security implementation, and no dedicated mobile applications. Many of these platforms were developed as static websites or use content management systems without proper e-commerce features such as shopping cart management, order tracking, or payment integration."));

C.push(h(2, "2.2 Limitations of Existing Systems"));
C.push(sp(100));
C.push(p("The analysis of existing systems revealed the following specific limitations:"));
C.push(sp());
C.push(nm("Multi-Currency Limitation: Most platforms do not support ETB pricing. International platforms display prices exclusively in USD or other major currencies, requiring Ethiopian customers to perform mental currency conversions and exposing them to exchange rate fluctuations. Local platforms that do support ETB often lack USD display, limiting their appeal to international customers."));
C.push(nm("Payment Gateway Gap: Global platforms integrate with international payment gateways (Stripe, PayPal, Square) which require international credit cards or bank accounts. Local Ethiopian payment solutions such as Chapa, Telebirr, and Amole are not supported out-of-the-box and require custom development to integrate."));
C.push(nm("Mobile Accessibility: While many platforms offer responsive web design, few provide dedicated mobile applications. This limits the user experience on mobile devices, missing opportunities for push notifications, offline capabilities, and device-specific features such as camera barcode scanning or fingerprint authentication."));
C.push(nm("Cost Barriers: International platforms typically charge monthly subscription fees, transaction fees, and often require premium plans for essential features such as multi-currency support or custom payment gateway integration. These costs are significant relative to the average Ethiopian business revenue."));
C.push(nm("Security Practices: Many locally developed platforms lack fundamental security measures such as CSRF protection, prepared statements for database queries, proper password hashing, and output escaping. This exposes both the business and its customers to data breaches and financial fraud."));
C.push(nm("Limited Customization: SaaS-based platforms restrict access to the underlying code, limiting the ability to customize the shopping experience, add local features, or integrate with local service providers. Open-source alternatives often require significant technical expertise to customize."));

C.push(h(2, "2.3 Proposed System Overview"));
C.push(sp(100));
C.push(p("Smart Mall addresses the limitations identified above through a custom-built, full-stack architecture designed specifically for the Ethiopian market. The system architecture follows a three-tier design pattern:"));
C.push(sp());
C.push(h(4, "Presentation Tier (Frontend)"));
C.push(p("The presentation layer consists of responsive HTML5 pages styled with CSS3 and enhanced with vanilla JavaScript. The web interface is designed with a mobile-first approach, ensuring optimal viewing across desktop, tablet, and smartphone screens. Key frontend features include a dynamic product grid with category filtering, a live search system with autocomplete, an interactive shopping cart, a multi-step checkout process, and an administrative dashboard. The interface supports dark and light themes with CSS custom properties and JavaScript-based theme toggling."));
C.push(sp());
C.push(h(4, "Application Tier (Backend)"));
C.push(p("The application layer is built with PHP 8.0+ following a modular include-based architecture. The central configuration file (config.php) serves as the bootstrap for every page request, managing sessions, output buffering, security headers, and loading core dependencies (database connection and currency engine). The backend handles all business logic including user authentication, product management, cart operations, order processing, and payment integration."));
C.push(sp());
C.push(h(4, "Data Tier (Database)"));
C.push(p("The data layer uses MySQL/MariaDB with the InnoDB storage engine for transaction support and foreign key integrity. The database schema consists of eight tables: users, categories, products, cart, orders, order_items, payments, and password_resets. All database access is performed through PDO prepared statements to prevent SQL injection, with row-level locking (SELECT ... FOR UPDATE) for critical operations such as stock validation during checkout."));
C.push(sp());
C.push(p("The mobile application wraps the web platform using Capacitor v8, a cross-platform runtime that renders the web application in a native WebView. This approach allows the mobile app to share the same codebase, database, and business logic as the web platform while providing native features such as immersive full-screen display, hardware back-button handling, and deep linking for Chapa payment callbacks."));

C.push(h(2, "2.4 Functional Requirements"));
C.push(sp(100));
C.push(p("The functional requirements define the specific behaviors and capabilities that the Smart Mall system must provide. These are organized by user category."));

C.push(h(3, "2.4.1 Customer Functional Requirements"));
C.push(sp());
C.push(tbl([
  ["ID", "Requirement Description", "Priority"],
  ["FR-C01", "The system shall allow users to register an account by providing name, email, phone, and password with validation.", "High"],
  ["FR-C02", "The system shall allow registered users to log in using email and password with session fixation protection.", "High"],
  ["FR-C03", "The system shall allow users to log out and destroy their session.", "High"],
  ["FR-C04", "The system shall allow users to reset their password via email-based token with 1-hour expiry.", "High"],
  ["FR-C05", "The system shall display a product catalog with grid layout, category filtering, and search functionality.", "High"],
  ["FR-C06", "The system shall provide product detail pages with image gallery, video, description, price, and stock status.", "High"],
  ["FR-C07", "The system shall provide a live search feature with autocomplete suggestions via AJAX.", "Medium"],
  ["FR-C08", "The system shall allow users to add products to a server-side shopping cart and manage quantities.", "High"],
  ["FR-C09", "The system shall allow users to view and modify their cart contents before checkout.", "High"],
  ["FR-C10", "The system shall provide a checkout process with shipping information collection and payment method selection.", "High"],
  ["FR-C11", "The system shall support Chapa Pay as a payment method with ETB conversion and API verification.", "High"],
  ["FR-C12", "The system shall support Bank Transfer as a payment method.", "Medium"],
  ["FR-C13", "The system shall support Cash on Delivery as a payment method.", "Medium"],
  ["FR-C14", "The system shall allow users to view their order history and order status.", "Medium"],
  ["FR-C15", "The system shall allow users to cancel pending orders.", "Medium"],
  ["FR-C16", "The system shall allow users to switch between USD and ETB currency display.", "Medium"],
], [1400, 6000, 1600]));
C.push(sp());

C.push(h(3, "2.4.2 Admin Functional Requirements"));
C.push(sp());
C.push(tbl([
  ["ID", "Requirement Description", "Priority"],
  ["FR-A01", "The system shall provide an admin dashboard with store statistics (total products, orders, revenue, categories).", "High"],
  ["FR-A02", "The system shall allow admins to add new products with name, description, price, stock, images, and video.", "High"],
  ["FR-A03", "The system shall allow admins to edit existing products and manage media (cover, gallery, video).", "High"],
  ["FR-A04", "The system shall allow admins to delete products with CSRF verification and file cleanup.", "High"],
  ["FR-A05", "The system shall allow admins to manage categories with up to 3 slide images.", "High"],
  ["FR-A06", "The system shall allow admins to view all orders and update their status.", "High"],
  ["FR-A07", "The system shall enforce admin access control, redirecting non-admin users.", "High"],
], [1400, 6000, 1600]));
C.push(sp());

C.push(h(3, "2.4.3 Mobile Functional Requirements"));
C.push(sp());
C.push(tbl([
  ["ID", "Requirement Description", "Priority"],
  ["FR-M01", "The mobile app shall load the web platform content in a native WebView without browser chrome.", "High"],
  ["FR-M02", "The mobile app shall hide the status bar for an immersive full-screen experience.", "Medium"],
  ["FR-M03", "The mobile app shall handle the hardware back button for navigation history.", "High"],
  ["FR-M04", "The mobile app shall intercept and handle deep links for Chapa payment callbacks.", "Medium"],
  ["FR-M05", "The mobile app shall hide the splash screen after the initial page load.", "Medium"],
  ["FR-M06", "The mobile app shall support cleartext navigation only to allowed domains.", "High"],
], [1400, 6000, 1600]));
C.push(sp());

C.push(h(3, "2.4.4 Payment Functional Requirements"));
C.push(sp());
C.push(tbl([
  ["ID", "Requirement Description", "Priority"],
  ["FR-P01", "The system shall initialize a Chapa payment by sending order details to the Chapa API.", "High"],
  ["FR-P02", "The system shall generate a unique transaction reference (tx_ref) for each payment.", "High"],
  ["FR-P03", "The system shall verify Chapa payment status by calling the verification API endpoint.", "High"],
  ["FR-P04", "The system shall handle test mode payments gracefully when a test API key is configured.", "Medium"],
  ["FR-P05", "The system shall update payment and order status upon successful payment verification.", "High"],
  ["FR-P06", "The system shall restore product stock when a payment fails or is cancelled.", "High"],
  ["FR-P07", "The system shall record all payment transactions in the payments table with full API response.", "High"],
], [1400, 6000, 1600]));

C.push(h(2, "2.5 Non-Functional Requirements"));
C.push(sp(100));
C.push(tbl([
  ["Category", "ID", "Requirement", "Metric"],
  ["Performance", "NFR-01", "Page load time shall not exceed 3 seconds.", "Lighthouse / browser timing"],
  ["Performance", "NFR-02", "Database queries shall complete within 500ms.", "MySQL slow query log"],
  ["Security", "NFR-03", "All passwords shall be hashed using bcrypt.", "password_hash() verification"],
  ["Security", "NFR-04", "All database queries shall use PDO prepared statements.", "Code review"],
  ["Security", "NFR-05", "All POST forms shall implement CSRF token verification.", "Code review + testing"],
  ["Security", "NFR-06", "All user output shall be escaped with htmlspecialchars().", "Code review"],
  ["Usability", "NFR-07", "The interface shall be responsive on desktop, tablet, and mobile.", "Media query breakpoints"],
  ["Usability", "NFR-08", "The system shall support dark and light themes.", "Theme toggle testing"],
  ["Reliability", "NFR-09", "The system shall handle concurrent user sessions without data corruption.", "Transaction testing"],
  ["Reliability", "NFR-10", "The checkout process shall use database transactions with rollback on failure.", "PDO transaction testing"],
  ["Compatibility", "NFR-11", "The platform shall function on Chrome, Firefox, Safari, and Edge.", "Cross-browser testing"],
  ["Compatibility", "NFR-12", "The mobile app shall support Android 8.0 (API 26) and above.", "Android manifest config"],
  ["Maintainability", "NFR-13", "Code shall follow a consistent modular structure with separation of concerns.", "Code review"],
], [1800, 1400, 4400, 3400]));

C.push(h(2, "2.6 Use Case Diagram"));
C.push(sp(100));
C.push(p("The use case diagram for Smart Mall illustrates the interactions between the three primary actors (Customer, Admin, and Chapa Payment Gateway) and the system's functional capabilities. The diagram is organized as follows:"));
C.push(sp());
C.push(h(4, "Actor: Customer"));
C.push(bt("Register Account — Create a new user account with validation."));
C.push(bt("Login — Authenticate using email and password."));
C.push(bt("Browse Products — View the product catalog with filtering and search."));
C.push(bt("View Product Details — Access detailed product information with media."));
C.push(bt("Manage Cart — Add, update quantities, and remove items from cart."));
C.push(bt("Checkout — Complete purchase with shipping details and payment."));
C.push(bt("Pay via Chapa — Process payment through Chapa payment gateway."));
C.push(bt("View Orders — View order history and order status."));
C.push(bt("Cancel Order — Cancel a pending order."));
C.push(bt("Switch Currency — Toggle between USD and ETB display."));
C.push(bt("Reset Password — Initiate and complete password reset."));
C.push(sp());
C.push(h(4, "Actor: Administrator (extends Customer)"));
C.push(bt("Manage Products — Add, edit, and delete products with media."));
C.push(bt("Manage Categories — Create, update, and delete categories."));
C.push(bt("Manage Orders — View and update order statuses."));
C.push(bt("View Dashboard — Access store statistics and analytics."));
C.push(sp());
C.push(h(4, "Actor: Chapa Payment Gateway"));
C.push(bt("Initialize Payment — Process payment initialization request."));
C.push(bt("Verify Payment — Respond to payment verification requests."));
C.push(sp());
C.push(p("[Insert Use Case Diagram image here — see Appendix C for screenshot reference]"));

C.push(h(2, "2.7 Data Flow Diagram (DFD)"));
C.push(sp(100));
C.push(h(4, "2.7.1 Context Diagram (Level 0)"));
C.push(p("The context diagram presents Smart Mall as a single process with three external entities: Customer, Administrator, and Chapa Payment Gateway. Data flows include customer registration data, login credentials, product queries, order details, payment requests, and administrative commands. The Chapa Payment Gateway receives payment initialization requests and returns verification responses."));
C.push(sp());
C.push(h(4, "2.7.2 Level 1 DFD"));
C.push(p("The Level 1 DFD decomposes the system into five major processes:"));
C.push(sp());
C.push(nm("Process 1.0: User Management — Handles registration, login, logout, and password reset. Data stores: users, password_resets."));
C.push(nm("Process 2.0: Product Catalog Management — Handles product browsing, searching, and category filtering. Data stores: products, categories."));
C.push(nm("Process 3.0: Shopping Cart Management — Handles adding, updating, and removing cart items with stock validation. Data stores: cart, products."));
C.push(nm("Process 4.0: Order Processing — Handles checkout, payment initialization, order creation, and payment verification. Data stores: orders, order_items, payments, products."));
C.push(nm("Process 5.0: Administration — Handles product CRUD, category management, order management, and dashboard statistics. Data stores: products, categories, orders, users."));
C.push(sp());
C.push(p("[Insert Data Flow Diagrams here — see Appendix C for screenshot reference]"));

C.push(new Paragraph({ children: [new PageBreak()] }));

// ================================================================
// CHAPTER 3
// ================================================================
C.push(h(1, "CHAPTER 3: SYSTEM DESIGN"));
C.push(sp(200));

C.push(h(2, "3.1 System Architecture"));
C.push(sp(100));
C.push(p("Smart Mall follows a three-tier client-server architecture that separates the presentation, application logic, and data storage layers. This architectural pattern provides modularity, scalability, and maintainability."));
C.push(sp());
C.push(h(4, "Tier 1: Presentation Layer (Client)"));
C.push(p("The presentation layer consists of two client types: the web browser and the Capacitor mobile WebView. The web browser renders HTML5 pages styled with CSS3 and enhanced with JavaScript for interactivity. The mobile WebView loads the same web pages within a native Android wrapper, providing an application-like experience. Key technologies include HTML5 semantic markup, CSS3 with custom properties for theming, and vanilla JavaScript for AJAX calls, DOM manipulation, and client-side validation."));
C.push(sp());
C.push(h(4, "Tier 2: Application Layer (Server)"));
C.push(p("The application layer runs on Apache HTTP Server with PHP 8.0+ as the server-side scripting language. PHP handles all business logic including request processing, session management, authentication, authorization, data validation, and response generation. The application follows a bootstrap pattern where config.php initializes the environment, loads dependencies (db.php for database access, currency.php for multi-currency), and individual page files handle specific requests. No framework is used; the architecture relies on PHP's built-in capabilities and a modular include structure."));
C.push(sp());
C.push(h(4, "Tier 3: Data Layer (Database)"));
C.push(p("The data layer uses MySQL/MariaDB with the InnoDB storage engine. InnoDB provides ACID-compliant transaction support, foreign key integrity, and row-level locking. The database contains eight tables in a normalized schema design. All database access is mediated through PDO (PHP Data Objects) with prepared statements, ensuring separation between the application and data layers and providing protection against SQL injection attacks."));
C.push(sp());
C.push(h(4, "External Integrations"));
C.push(p("The system integrates with two external APIs:"));
C.push(bt("Chapa Payment Gateway (https://api.chapa.co/v1): Handles payment initialization and verification. Communication occurs over HTTPS using PHP cURL."));
C.push(bt("ExchangeRate-API (https://open.er-api.com/v6/latest/USD): Provides live currency exchange rates. Data is fetched via cURL and cached locally to minimize API calls."));
C.push(sp());
C.push(p("[Insert System Architecture Diagram here — see Appendix C]"));

C.push(h(2, "3.2 User Interface Design"));
C.push(sp());

C.push(h(3, "3.2.1 Web UI Design"));
C.push(sp(100));
C.push(p("The web user interface is designed with a mobile-first approach, ensuring optimal viewing across all screen sizes. The design system is built on CSS custom properties that enable the dark/light theme switching."));
C.push(sp());
C.push(h(4, "Design System"));
C.push(p("The visual design is defined by CSS custom properties including:"));
C.push(bt("Primary color: #007AFF (blue) for buttons, links, and accents."));
C.push(bt("Secondary color: #5856D6 (purple) for highlights and gradients."));
C.push(bt("Background: #FFFFFF (light) / #121212 (dark) for main page background."));
C.push(bt("Surface: #F5F5F7 (light) / #1C1C1E (dark) for card backgrounds."));
C.push(bt("Text: #1D1D1F (light) / #F5F5F7 (dark) for primary text."));
C.push(bt("Border radius: 12px for cards, 8px for buttons, 20px for search bar."));
C.push(bt("Shadows: Subtle box-shadows for elevation in light mode, reduced in dark mode."));
C.push(sp());
C.push(h(4, "Key Pages"));
C.push(p("The homepage (index.php) serves as the main storefront, featuring a hero section with a spotlight card and top-sales carousel, a category grid with four cards (Fashion, Electronics, Home, Beauty) that cycle through images, a trust section highlighting Free Delivery, Easy Returns, 24/7 Support, and Secure Checkout, and a products grid with search, sort, and filter controls. Each product card displays an image, name, price in both currencies, and action buttons for Buy Now and Add to Cart."));
C.push(sp());
C.push(p("The product detail page (product.php) features a media gallery with zoom-on-hover capability, a lightbox for full-screen image viewing, thumbnail navigation for additional images and video, product information including category badge, name, SKU, price display, stock indicator, and full description, a quantity selector with +/- controls, and AJAX-powered Add to Cart and Buy Now buttons."));
C.push(sp());
C.push(p("The checkout page (checkout.php) presents a two-column layout with the billing form on the left and the order summary on the right. The billing form collects first name, last name, email, address, city, state, ZIP, and country, with Ethiopia as the default country. Three payment methods are presented as radio options: Chapa Pay (with ETB conversion note), Bank Transfer, and Cash on Delivery."));
C.push(sp());
C.push(p("[Insert Web UI Screenshots here — homepage, product detail, cart, checkout, admin dashboard]"));

C.push(h(3, "3.2.2 Mobile UI Design"));
C.push(sp(100));
C.push(p("The mobile application uses Capacitor v8 to render the web platform within a native WebView, providing an application-like experience with the following enhancements:"));
C.push(sp());
C.push(bt("Immersive Mode: The status bar is hidden using StatusBar.hide() from Capacitor plugins, providing full-screen content display."));
C.push(bt("Splash Screen: A native splash screen is displayed during initial loading and hidden once the page is ready using SplashScreen.hide()."));
C.push(bt("Back Button Navigation: The hardware back button is intercepted to navigate through page history using window.history.back(), with App.exitApp() as the fallback when there is no history."));
C.push(bt("Deep Linking: URL interception is configured for the smartmall.unaux.com domain, enabling seamless handling of Chapa payment callback URLs."));
C.push(bt("Content Security: Navigation is restricted to the configured domain and Chapa API domains through allowNavigation configuration."));
C.push(sp());
C.push(p("The mobile app configuration (capacitor.config.json) specifies appId (com.smartmall.app), appName (Smart Mall), webDir (www), and server settings pointing to the production URL (https://smartmall.unaux.com). The app targets Android 8.0+ (API 26) and above."));
C.push(sp());
C.push(p("[Insert Mobile App Screenshots here — see Appendix C]"));

C.push(h(2, "3.3 Navigation Flow Diagram"));
C.push(sp(100));
C.push(p("The navigation flow describes how users move between pages within the Smart Mall system. The primary flows are:"));
C.push(sp());
C.push(h(4, "Customer Shopping Flow"));
C.push(p("Homepage → Product Catalog (filter/search) → Product Detail → Add to Cart → Cart → Checkout → Order Confirmation"));
C.push(sp());
C.push(h(4, "Authentication Flow"));
C.push(p("Login/Register → Homepage (redirect after success)"));
C.push(p("Forgot Password → Password Reset Email → Reset Password → Login"));
C.push(sp());
C.push(h(4, "Order Management Flow"));
C.push(p("Orders Page → Order Detail → Cancel Order (if pending)"));
C.push(p("Order Confirmation → Payment Verification → Success/Failure"));
C.push(sp());
C.push(h(4, "Admin Management Flow"));
C.push(p("Admin Dashboard → Add Product / Edit Product / Delete Product"));
C.push(p("Admin Dashboard → Manage Categories → Add/Edit/Delete Category"));
C.push(p("Admin Dashboard → Manage Orders → Update Order Status"));
C.push(sp());
C.push(p("[Insert Navigation Flow Diagram here]"));

C.push(h(2, "3.4 Database Design"));
C.push(sp(100));
C.push(p("The Smart Mall database (smartmall_db) is designed following relational database normalization principles. The schema consists of eight tables, each serving a specific domain within the application."));
C.push(sp());
C.push(h(4, "Design Principles"));
C.push(bt("Normalization: Tables are normalized to Third Normal Form (3NF) to eliminate data redundancy."));
C.push(bt("Referential Integrity: Foreign key constraints ensure data consistency across related tables."));
C.push(bt("Transaction Support: The InnoDB storage engine provides ACID compliance for critical operations."));
C.push(bt("Character Set: utf8mb4 encoding supports full Unicode including emoji and special characters."));
C.push(bt("Indexing: Primary keys, foreign keys, and frequently queried columns are indexed for performance."));
C.push(sp());
C.push(h(4, "Entity Relationship Summary"));
C.push(p("The database contains the following tables with their relationships:"));
C.push(bt("users: Stores customer and admin accounts. One-to-many relationship with cart, orders."));
C.push(bt("categories: Product categories with slide images. One-to-many relationship with products."));
C.push(bt("products: Product inventory. Many-to-one with categories. Many-to-many with users via cart."));
C.push(bt("cart: Shopping cart entries. Many-to-one with users and products (both CASCADE on delete)."));
C.push(bt("orders: Placed orders. Many-to-one with users. One-to-many with order_items and payments."));
C.push(bt("order_items: Order line items. Many-to-one with orders and products. Preserves price at purchase time."));
C.push(bt("payments: Payment transactions. Many-to-one with orders. Stores full API response."));
C.push(bt("password_resets: Password reset tokens. Independent table with email, token, and expiry."));

C.push(h(2, "3.5 ER Diagram"));
C.push(sp(100));
C.push(p("The Entity-Relationship (ER) diagram illustrates the logical structure of the Smart Mall database, showing all entities, their attributes, and the relationships between them."));
C.push(sp());
C.push(p("The key relationships are:"));
C.push(bt("users (1) ------< (N) cart (N) >------ (1) products — A user has many cart entries, each cart entry references one product. ON DELETE CASCADE on both foreign keys ensures cart cleanup when a user or product is deleted."));
C.push(bt("users (1) ------< (N) orders — A user can place many orders. The foreign key on orders.user_id references users.id (ON DELETE RESTRICT — orders cannot belong to deleted users)."));
C.push(bt("orders (1) ------< (N) order_items (N) >------ (1) products — An order contains many items, each referencing a product. The price is stored in order_items at purchase time, protecting historical order data from product price changes."));
C.push(bt("orders (1) ------< (N) payments — An order can have multiple payment attempts (e.g., retry after failure). Each payment record stores the method, status, amount, currency, and Chapa transaction reference."));
C.push(bt("categories (1) ------< (N) products — Each product belongs to one category. The foreign key category_id in products references categories.id."));
C.push(sp());
C.push(p("[Insert ER Diagram here — see Appendix C]"));

C.push(h(2, "3.6 Database Schema"));
C.push(sp(100));
C.push(p("The following sections detail the schema for each of the eight database tables. Refer to Appendix A for the complete SQL Data Definition Language (DDL) statements."));

// Table schemas as data
const tw = [2000, 2200, 3800, 3000];
function stable(data) { return tbl([["Column", "Type", "Constraints", "Notes"], ...data], tw); }

C.push(h(3, "3.6.1 Users Table"));
C.push(stable([
  ["id", "INT(11)", "PRIMARY KEY, AUTO_INCREMENT", "Unique user identifier"],
  ["name", "VARCHAR(100)", "NOT NULL", "User's full name"],
  ["email", "VARCHAR(100)", "NOT NULL, UNIQUE", "Login identifier"],
  ["phone", "VARCHAR(20)", "DEFAULT NULL", "Contact phone number"],
  ["password", "VARCHAR(255)", "NOT NULL", "Bcrypt password hash"],
  ["role", "ENUM('customer','admin')", "DEFAULT 'customer'", "Authorization level"],
  ["address", "TEXT", "DEFAULT NULL", "Shipping address"],
  ["created_at", "TIMESTAMP", "DEFAULT CURRENT_TIMESTAMP", "Account creation time"],
]));
C.push(sp(80));

C.push(h(3, "3.6.2 Categories Table"));
C.push(stable([
  ["id", "INT(11)", "PRIMARY KEY, AUTO_INCREMENT", "Unique category identifier"],
  ["name", "VARCHAR(255)", "NOT NULL", "Category display name"],
  ["slug", "VARCHAR(255)", "DEFAULT NULL", "URL-friendly category slug"],
  ["image1", "VARCHAR(255)", "DEFAULT NULL", "Primary slide image path"],
  ["image2", "VARCHAR(255)", "DEFAULT NULL", "Secondary slide image path"],
  ["image3", "VARCHAR(255)", "DEFAULT NULL", "Tertiary slide image path"],
  ["created_at", "TIMESTAMP", "DEFAULT CURRENT_TIMESTAMP", "Creation time"],
]));
C.push(sp(80));

C.push(h(3, "3.6.3 Products Table"));
C.push(stable([
  ["id", "INT(11)", "PRIMARY KEY, AUTO_INCREMENT", "Unique product identifier"],
  ["category_id", "INT(11)", "FOREIGN KEY -> categories(id)", "Product category"],
  ["name", "VARCHAR(255)", "NOT NULL", "Product name"],
  ["description", "TEXT", "DEFAULT NULL", "Product description"],
  ["price", "DECIMAL(10,2)", "NOT NULL", "Price in USD"],
  ["image", "VARCHAR(255)", "DEFAULT NULL", "Primary product image"],
  ["images", "LONGTEXT", "DEFAULT NULL", "Additional images (JSON array)"],
  ["video", "VARCHAR(255)", "DEFAULT NULL", "Product video filename"],
  ["stock", "INT(11)", "NOT NULL DEFAULT 0", "Available quantity"],
  ["created_at", "TIMESTAMP", "DEFAULT CURRENT_TIMESTAMP", "Creation time"],
]));
C.push(sp(80));

C.push(h(3, "3.6.4 Cart Table"));
C.push(stable([
  ["id", "INT(11)", "PRIMARY KEY, AUTO_INCREMENT", "Unique cart entry identifier"],
  ["user_id", "INT(11)", "FOREIGN KEY -> users(id), CASCADE", "Cart owner"],
  ["product_id", "INT(11)", "FOREIGN KEY -> products(id), CASCADE", "Product in cart"],
  ["quantity", "INT(11)", "NOT NULL DEFAULT 1", "Quantity of product"],
  ["created_at", "TIMESTAMP", "DEFAULT CURRENT_TIMESTAMP", "Time added to cart"],
]));
C.push(sp(80));

C.push(h(3, "3.6.5 Orders Table"));
C.push(stable([
  ["id", "INT(11)", "PRIMARY KEY, AUTO_INCREMENT", "Unique order identifier"],
  ["user_id", "INT(11)", "FOREIGN KEY -> users(id)", "Customer who placed the order"],
  ["total_price", "DECIMAL(10,2)", "NOT NULL", "Order total including 10% VAT"],
  ["status", "ENUM('pending','processing','paid','shipped','delivered','cancelled')", "DEFAULT 'pending'", "Current order status"],
  ["first_name", "VARCHAR(100)", "DEFAULT NULL", "Shipping first name"],
  ["last_name", "VARCHAR(100)", "DEFAULT NULL", "Shipping last name"],
  ["email", "VARCHAR(255)", "DEFAULT NULL", "Shipping contact email"],
  ["address", "TEXT", "DEFAULT NULL", "Street address"],
  ["city", "VARCHAR(100)", "DEFAULT NULL", "City"],
  ["state", "VARCHAR(100)", "DEFAULT NULL", "State or region"],
  ["zip", "VARCHAR(20)", "DEFAULT NULL", "Postal/ZIP code"],
  ["country", "VARCHAR(100)", "DEFAULT 'Ethiopia'", "Country"],
  ["payment_method", "VARCHAR(50)", "DEFAULT 'chapa'", "Payment method used"],
  ["created_at", "TIMESTAMP", "DEFAULT CURRENT_TIMESTAMP", "Order placement time"],
]));
C.push(sp(80));

C.push(h(3, "3.6.6 Order Items Table"));
C.push(stable([
  ["id", "INT(11)", "PRIMARY KEY, AUTO_INCREMENT", "Unique item identifier"],
  ["order_id", "INT(11)", "FOREIGN KEY -> orders(id)", "Parent order"],
  ["product_id", "INT(11)", "FOREIGN KEY -> products(id)", "Product ordered"],
  ["quantity", "INT(11)", "NOT NULL", "Quantity ordered"],
  ["price", "DECIMAL(10,2)", "NOT NULL", "Price at time of purchase"],
]));
C.push(sp(80));

C.push(h(3, "3.6.7 Payments Table"));
C.push(stable([
  ["id", "INT(11)", "PRIMARY KEY, AUTO_INCREMENT", "Unique payment identifier"],
  ["order_id", "INT(11)", "FOREIGN KEY -> orders(id)", "Related order"],
  ["method", "VARCHAR(50)", "NOT NULL", "Payment method (chapa/bank_transfer/cod)"],
  ["status", "ENUM('pending','paid','failed')", "DEFAULT 'pending'", "Payment status"],
  ["amount", "DECIMAL(10,2)", "NOT NULL", "Payment amount"],
  ["currency", "VARCHAR(10)", "NOT NULL DEFAULT 'USD'", "Transaction currency"],
  ["tx_ref", "VARCHAR(100)", "DEFAULT NULL", "Chapa transaction reference"],
  ["chapa_response", "LONGTEXT", "DEFAULT NULL", "Full Chapa API response (JSON)"],
  ["paid_at", "DATETIME", "DEFAULT NULL", "Payment confirmation time"],
  ["created_at", "DATETIME", "NOT NULL", "Payment record creation time"],
]));
C.push(sp(80));

C.push(h(3, "3.6.8 Password Resets Table"));
C.push(stable([
  ["id", "INT(11)", "PRIMARY KEY, AUTO_INCREMENT", "Unique record identifier"],
  ["email", "VARCHAR(100)", "NOT NULL", "User email requesting reset"],
  ["token", "VARCHAR(64)", "NOT NULL", "64-character hex reset token"],
  ["expires_at", "DATETIME", "NOT NULL", "Token expiration time (1 hour)"],
  ["created_at", "TIMESTAMP", "DEFAULT CURRENT_TIMESTAMP", "Record creation time"],
]));

C.push(h(2, "3.7 API / Backend Design"));
C.push(sp(100));
C.push(p("Smart Mall implements several internal API endpoints that handle specific requests and return structured responses. The following table summarizes the API interfaces:"));
C.push(sp());
C.push(tbl([
  ["Endpoint", "Method", "Parameters", "Response Type", "Description"],
  ["api/search.php", "GET", "q (search term)", "JSON array", "Live search autocomplete — returns up to 6 matching products with id, name, price, image_url, display_price"],
  ["add_to_cart.php", "POST", "product_id, quantity (JSON body)", "JSON object", "Add product to cart — returns {success, message, cart_count, new_quantity}. Requires auth, validates stock"],
  ["set_currency.php", "GET/POST", "currency (USD|ETB), redirect", "HTTP Redirect", "Switch display currency — stores choice in session, validates currency value"],
  ["order_confirmation.php", "GET", "order_id", "HTML page", "Payment verification and confirmation — verifies Chapa payment on callback"],
  ["logout.php", "GET", "—", "HTTP Redirect", "Destroy session and redirect to homepage"],
], [3200, 1200, 2400, 2400, 3600]));
C.push(sp());
C.push(p("Additionally, the system integrates with external APIs:"));
C.push(sp());
C.push(bt("Chapa API (v1/transaction/initialize): POST request to create a payment session. Receives order amount (in ETB), tx_ref, callback URL, and customer details. Returns a checkout URL for redirecting the customer."));
C.push(bt("Chapa API (v1/transaction/verify/{tx_ref}): GET request to verify payment status. Returns transaction details including payment status, method, and timestamps."));
C.push(bt("ExchangeRate-API (/v6/latest/USD): GET request to fetch current exchange rates. Returns rates for multiple currencies including ETB."));

C.push(h(2, "3.8 Security Design"));
C.push(sp(100));
C.push(p("Smart Mall implements multiple layers of security to protect user data, prevent unauthorized access, and defend against common web vulnerabilities. The following security measures are implemented throughout the system:"));
C.push(sp());
C.push(h(4, "3.8.1 CSRF Protection"));
C.push(p("Cross-Site Request Forgery (CSRF) protection is implemented on all state-changing POST operations. A CSRF token is generated using bin2hex(random_bytes(32)), producing a 64-character hexadecimal string stored in the user's session. This token is included as a hidden input field in all forms via the csrf_field() helper function. When forms are submitted, the csrf_verify() function compares the submitted token with the session token using hash_equals() to prevent timing attacks. If verification fails, the system terminates with an HTTP 403 Forbidden response."));
C.push(p("The following operations implement CSRF verification: login, registration, checkout, cart updates, order cancellation, password reset requests, password reset completion, admin product management, admin order status updates, admin category management, and product deletion."));
C.push(sp());
C.push(h(4, "3.8.2 SQL Injection Prevention"));
C.push(p("All database queries use PDO prepared statements with named parameters. PDO::ATTR_EMULATE_PREPARES is set to false, ensuring real prepared statements are used rather than emulated ones. No raw string interpolation is used in any SQL query. The PDO connection is configured with charset=utf8mb4 and ERRMODE_EXCEPTION for consistent error handling."));
C.push(sp());
C.push(h(4, "3.8.3 XSS Prevention"));
C.push(p("All user-controlled data displayed in HTML is escaped using htmlspecialchars($value, ENT_QUOTES, 'UTF-8'). This includes product names, descriptions, user names, form values, error messages, and any other data that originates from user input or database storage."));
C.push(sp());
C.push(h(4, "3.8.4 Password Security"));
C.push(p("User passwords are hashed using PHP's password_hash() function with the PASSWORD_BCRYPT algorithm, which uses a cost factor of 10. Verification is performed using password_verify(), which provides timing-safe comparison. The password policy requires a minimum of 8 characters including at least one uppercase letter, one lowercase letter, one digit, and one special character, enforced through four separate regular expression checks."));
C.push(sp());
C.push(h(4, "3.8.5 Session Security"));
C.push(p("Session security measures include: session_regenerate_id(true) called after successful login to prevent session fixation attacks; session_unset() and session_destroy() on logout for complete session cleanup; HTTP-only session cookies (no JavaScript access); no session ID in URLs; Cache-Control: no-store, no-cache, must-revalidate headers on session pages to prevent caching."));
C.push(sp());
C.push(h(4, "3.8.6 File Upload Security"));
C.push(p("File uploads in the admin panel are validated for file type (JPEG, PNG, GIF, WebP for images; MP4, WebM for videos), file size (5MB maximum for images, 50MB for videos), and PHP upload error codes. Generated filenames use unique patterns to prevent overwriting. Media deletion handlers clean up filesystem files when records are removed from the database."));
C.push(sp());
C.push(h(4, "3.8.7 HTTP Security Headers"));
C.push(p("The following security headers are emitted by config.php on every page request: X-Content-Type-Options: nosniff (prevents MIME-type sniffing) and X-Frame-Options: SAMEORIGIN (prevents clickjacking by restricting framing to same origin)."));

C.push(new Paragraph({ children: [new PageBreak()] }));

// ================================================================
// CHAPTER 4
// ================================================================
C.push(h(1, "CHAPTER 4: SYSTEM IMPLEMENTATION"));
C.push(sp(200));

C.push(h(2, "4.1 Technology Stack"));
C.push(sp(100));
C.push(p("Smart Mall is built using a carefully selected technology stack that balances development efficiency, performance, security, and cost-effectiveness. The following table summarizes the technologies used:"));
C.push(sp());
C.push(tbl([
  ["Layer", "Technology", "Version", "Purpose"],
  ["Backend Language", "PHP", "8.0+", "Server-side scripting, business logic, session management"],
  ["Database Engine", "MySQL / MariaDB", "5.7+ / 10.3+", "Relational data storage with InnoDB engine"],
  ["Database Access", "PDO (PHP Data Objects)", "—", "Secure database access with prepared statements"],
  ["Web Server", "Apache HTTP Server", "2.4+", "HTTP serving with mod_rewrite for clean URLs"],
  ["Frontend Markup", "HTML5", "—", "Semantic page structure and accessibility"],
  ["Frontend Styling", "CSS3", "—", "Responsive design, theming, animations"],
  ["Client Scripting", "JavaScript (Vanilla)", "ES6+", "AJAX calls, DOM manipulation, client validation"],
  ["Payment Gateway", "Chapa API", "v1", "ETB payment processing and verification"],
  ["Exchange Rates", "ExchangeRate-API", "—", "Live USD/ETB exchange rate data"],
  ["Mobile Framework", "Capacitor", "v8", "Cross-platform native WebView wrapper"],
  ["Mobile IDE", "Android Studio", "Latest", "Android APK building and signing"],
  ["Local Server", "XAMPP", "Latest", "Apache + MySQL + PHP development environment"],
  ["Production Hosting", "FreeProHost", "—", "Shared hosting with Apache, PHP 8.0+, MySQL"],
  ["PWA", "manifest.json", "—", "Installable web app with standalone display"],
], [2200, 2800, 1600, 4400]));
C.push(sp());

C.push(h(2, "4.2 Frontend Implementation"));
C.push(sp(100));
C.push(p("The frontend is implemented using vanilla HTML, CSS, and JavaScript without any frontend frameworks. This approach reduces dependencies, improves page load performance, and maintains simplicity."));
C.push(sp());
C.push(h(4, "File Structure and Responsiblity"));
C.push(sp());
C.push(tbl([
  ["File", "Lines", "Key Features"],
  ["index.php", "~2080", "Homepage: product grid, category filter, hero section, trust badges, philosophy section, admin console sidebar"],
  ["product.php", "~1016", "Product detail: media gallery, zoom, lightbox, stock status, AJAX add-to-cart"],
  ["cart.php", "~591", "Cart management: quantity update, remove items, stock enforcement, order summary"],
  ["checkout.php", "~758", "Checkout: billing form, payment methods, Chapa integration, order creation"],
  ["order_confirmation.php", "~837", "Confirmation: payment verification, status display, order details"],
  ["orders.php", "~534", "Order history: order cards, status badges, cancel functionality"],
  ["login.php", "~345", "Login form: CSRF, session regeneration, password visibility toggle"],
  ["register.php", "~398", "Registration: validation, password policy, bcrypt hashing"],
], [3000, 1200, 4800]));
C.push(sp());
C.push(h(4, "Theming System"));
C.push(p("The dark/light theme system is implemented using CSS custom properties (variables) defined under :root for light mode and [data-theme='dark'] for dark mode. A JavaScript function toggles the data-theme attribute on the HTML element and persists the user's preference to localStorage. The theme is applied consistently across all pages through the shared header.php file."));
C.push(sp());
C.push(h(4, "AJAX Integration"));
C.push(p("Client-side JavaScript handles three main AJAX interactions: (1) Add to Cart and Buy Now buttons POST to add_to_cart.php asynchronously and update the cart badge without page reload; (2) the search bar fetches autocomplete suggestions from api/search.php with a 300ms debounce to prevent excessive requests; and (3) cart quantity updates are managed through form submissions within the cart page."));

C.push(h(2, "4.3 Mobile App Implementation"));
C.push(sp(100));
C.push(p("The mobile application is implemented using Capacitor v8, an open-source cross-platform runtime that enables building native applications using standard web technologies. Smart Mall's mobile app wraps the existing web platform into a native Android application, sharing the same codebase while providing enhanced mobile features."));
C.push(sp());
C.push(h(4, "Capacitor Configuration"));
C.push(p("The capacitor.config.json file defines the application identity and runtime behavior:"));
C.push(sp());
C.push(mon("{"));
C.push(mon('  "appId": "com.smartmall.app",'));
C.push(mon('  "appName": "Smart Mall",'));
C.push(mon('  "webDir": "www",'));
C.push(mon('  "server": {'));
C.push(mon('    "url": "https://smartmall.unaux.com",'));
C.push(mon('    "cleartext": false,'));
C.push(mon('    "allowNavigation": ["smartmall.unaux.com", "*.chapa.co", "chapa.co"]'));
C.push(mon('  },'));
C.push(mon('  "android": {'));
C.push(mon('    "allowMixedContent": false,'));
C.push(mon('    "captureInput": true,'));
C.push(mon('    "webContentsDebuggingEnabled": false,'));
C.push(mon('    "overrideBackButton": true'));
C.push(mon('  }'));
C.push(mon("}"));
C.push(sp());
C.push(h(4, "Native Feature Integration"));
C.push(p("The www/app.js JavaScript file integrates Capacitor plugins for native functionality:"));
C.push(bt("StatusBar.hide() — Hides the status bar for full-screen content viewing, triggered on app load."));
C.push(bt("SplashScreen.hide() — Dismisses the splash screen once the WebView signals that the page is loaded."));
C.push(bt("Back Button Handling — Listens for the back button event. Uses history.back() if navigation history exists, otherwise calls App.exitApp() to close the application."));
C.push(bt("Deep Link Interception — Monitors appUrlOpen events to intercept URLs from the smartmall.unaux.com domain, enabling seamless handling of Chapa payment callback redirects."));

C.push(h(2, "4.4 Backend Implementation"));
C.push(sp(100));
C.push(p("The backend is implemented in PHP 8.0+ following a modular bootstrap architecture. Every page request flows through a consistent initialization sequence before page-specific logic executes."));
C.push(sp());
C.push(h(4, "Bootstrap Sequence (config.php)"));
C.push(p("The bootstrap process is as follows: (1) ob_start() initiates output buffering to capture any unintended whitespace or BOM characters before headers are sent. (2) Session management starts a PHP session if one is not already active, enabling persistent user state across requests. (3) Base URL detection parses HTTP_HOST and SCRIPT_NAME to construct a dynamic base URL, supporting both local development and production environments without configuration changes. (4) Core dependencies are loaded: includes/db.php provides the PDO database connection and CSRF utility functions; includes/currency.php provides the multi-currency engine. (5) Security headers (X-Content-Type-Options, X-Frame-Options) are emitted. (6) The page-specific file then processes any POST submissions (with CSRF verification) before including header.php for output."));
C.push(sp());
C.push(h(4, "Database Access Layer (includes/db.php)"));
C.push(p("The database access layer provides a centralized PDO connection with the following configuration: database name smartmall_db, host localhost (or production host), charset utf8mb4 for full Unicode support, error mode ERRMODE_EXCEPTION for consistent error handling, fetch mode FETCH_ASSOC for associative array results, and emulated prepares disabled for real prepared statement security. The layer also provides CSRF helper functions (csrf_token, csrf_field, csrf_verify) and URL helper functions (get_product_image_url, get_product_video_url) that resolve file paths to absolute URLs."));
C.push(sp());
C.push(h(4, "Multi-Currency Engine (includes/currency.php)"));
C.push(p("The multi-currency engine manages display and conversion between USD (base currency) and ETB. The engine defines supported currencies (USD and ETB), provides session-based currency selection, implements file-based exchange rate caching in the system temporary directory, fetches live rates from ExchangeRate-API via cURL with automatic fallback to stale cache on failure, provides conversion functions (smartmall_convert_money) and formatting functions (smartmall_format_money) with appropriate currency symbols."));
C.push(sp());
C.push(h(4, "Payment Processing (checkout.php + order_confirmation.php)"));
C.push(p("The checkout process implements a robust payment flow with database transaction support:"));
C.push(sp());
C.push(nm("Transaction begins with beginTransaction() for atomicity across multiple database operations."));
C.push(nm("Row-level locking (SELECT ... FOR UPDATE) ensures stock quantities are read consistently and prevents race conditions."));
C.push(nm("Stock validation checks each cart item against available inventory."));
C.push(nm("Order creation inserts records into orders, order_items, and payments tables."));
C.push(nm("For Chapa payments: the total (including 10% VAT) is converted to ETB, a unique tx_ref is generated (ORD-{order_id}-{YYYYMMDD}), and the Chapa initialization API is called via cURL with Bearer authentication."));
C.push(nm("On successful API response: the transaction is committed and the user is redirected to the Chapa checkout URL."));
C.push(nm("On payment callback: order_confirmation.php verifies the payment by calling the Chapa verification API. On success, payment status is updated to 'paid' and order to 'processing'. On failure, payment is marked 'failed', order is cancelled, and product stock is restored."));

C.push(h(2, "4.5 Database Implementation"));
C.push(sp(100));
C.push(p("The database was implemented using MySQL within the XAMPP environment for local development and subsequently migrated to FreeProHost MySQL for production. The implementation steps were:"));
C.push(sp());
C.push(nm("Schema design: The eight-table relational schema was designed following normalization principles, with foreign key constraints ensuring referential integrity."));
C.push(nm("Local creation: The database and tables were created using phpMyAdmin, the web-based MySQL administration tool included with XAMPP."));
C.push(nm("Table creation: Each table was created using CREATE TABLE statements with InnoDB engine, utf8mb4 character set, appropriate data types, NOT NULL constraints, DEFAULT values, PRIMARY KEY definitions, FOREIGN KEY constraints with ON DELETE CASCADE (for cart) and ON DELETE RESTRICT (for orders and related tables), and INDEX creation on foreign key columns."));
C.push(nm("Data migration: The schema and data were exported from XAMPP MySQL using phpMyAdmin's export feature and imported into FreeProHost MySQL using the import tool."));
C.push(nm("Connection configuration: Database credentials were updated in includes/db.php for the production environment."));
C.push(sp());
C.push(p("Refer to Appendix A for the complete DDL statements for all eight tables."));

C.push(h(2, "4.6 Payment Gateway Integration"));
C.push(sp(100));
C.push(p("Smart Mall integrates with the Chapa payment gateway to process ETB transactions. Chapa is an Ethiopian payment gateway that supports multiple payment methods including mobile money (telebirr), bank transfers, and card payments."));
C.push(sp());
C.push(h(4, "Configuration"));
C.push(p("Chapa configuration is centralized in chapa_pay/chapa-config.php:"));
C.push(mon("define('CHAPA_SECRET_KEY', 'CHASECK_TEST-...');"));
C.push(mon("define('CHAPA_API_URL', 'https://api.chapa.co/v1');"));
C.push(sp());
C.push(h(4, "Payment Initialization Flow"));
C.push(p("When a customer selects Chapa Pay during checkout, the following sequence occurs:"));
C.push(sp());
C.push(nm("The order total (subtotal + 10% VAT) is calculated and converted to ETB using smartmall_convert_money()."));
C.push(nm("A unique transaction reference (tx_ref) is generated: ORD-{order_id}-{YYYYMMDD}."));
C.push(nm("A cURL POST request is made to {CHAPA_API_URL}/transaction/initialize with JSON body containing amount (in ETB), currency (ETB), tx_ref, callback_url (pointing to order_confirmation.php), return_url, and customer details (name, email, phone number). The request includes an Authorization header with the Bearer secret key."));
C.push(nm("Chapa responds with a checkout URL. The system commits the database transaction and redirects the customer to the Chapa hosted payment page."));
C.push(sp());
C.push(h(4, "Payment Verification Flow"));
C.push(p("After payment, the customer is redirected back to order_confirmation.php. The verification flow is:"));
C.push(sp());
C.push(nm("The order is loaded by order_id from the GET parameter."));
C.push(nm("If the payment status is already 'paid', the confirmation page is displayed immediately."));
C.push(nm("If the payment is 'pending' and method is 'chapa': a GET request is made to {CHAPA_API_URL}/transaction/verify/{tx_ref}."));
C.push(nm("The response is checked for both top-level status='success' and nested data.status='success'."));
C.push(nm("In test mode: if the secret key contains 'TEST', a failed verification may be overridden to assume success (safe for development)."));
C.push(nm("On successful verification: payment is updated to 'paid', paid_at is set, order status becomes 'processing', and the cart is cleared."));
C.push(nm("On failed verification: payment is updated to 'failed', order status becomes 'cancelled', and product stock is restored by incrementing each product's stock by the cancelled quantity."));

C.push(h(2, "4.7 System Integration"));
C.push(sp(100));
C.push(p("All components of Smart Mall are integrated through the shared bootstrap and configuration system. The integration architecture follows these principles:"));
C.push(sp());
C.push(bt("Centralized Bootstrap: Every page requires config.php, which loads shared dependencies (database, currency) and sets up the environment (session, output buffering, security headers). This ensures consistent initialization across all pages."));
C.push(bt("Shared Layout Components: The header.php and footer.php files provide consistent HTML structure, CSS styling, JavaScript functionality, navigation, and branding across all pages. Individual pages include these components and insert their content between them."));
C.push(bt("Database as Integration Point: All persistent data flows through the MySQL database, providing a single source of truth for products, users, orders, and payments. The PDO abstraction layer ensures consistent access patterns."));
C.push(bt("AJAX Integration: Client-side JavaScript communicates with server-side endpoints (add_to_cart.php, api/search.php) through asynchronous HTTP requests, enabling real-time interactions without page reloads."));
C.push(bt("External API Integration: The system communicates with Chapa and ExchangeRate-API through PHP cURL, with appropriate error handling, timeout configuration, and data validation."));
C.push(bt("Mobile-Web Integration: The Capacitor mobile app loads the same web pages, sharing the entire backend infrastructure. Native features (back button, status bar, splash screen, deep linking) are layered on top through Capacitor plugins."));

C.push(new Paragraph({ children: [new PageBreak()] }));

// ================================================================
// CHAPTER 5
// ================================================================
C.push(h(1, "CHAPTER 5: TESTING AND SECURITY"));
C.push(sp(200));

C.push(h(2, "5.1 Testing Strategy"));
C.push(sp(100));
C.push(p("The testing strategy for Smart Mall encompasses multiple levels of testing to ensure the system meets functional requirements, performs reliably, and maintains security standards."));
C.push(sp());
C.push(h(4, "Testing Levels"));
C.push(bt("Unit Testing: Individual PHP functions were tested for correct behavior, including currency conversion functions, CSRF token generation and verification, password validation rules, and URL helper functions."));
C.push(bt("Integration Testing: Database interactions were verified including CRUD operations on all eight tables, foreign key constraint enforcement, transaction commit and rollback behavior, and prepared statement execution."));
C.push(bt("End-to-End Testing: Complete user flows were tested including registration → login → browse → add to cart → checkout → payment → confirmation."));
C.push(bt("Cross-Browser Testing: The web interface was tested on Chrome, Firefox, and mobile browsers to verify consistent rendering and functionality."));
C.push(sp());
C.push(h(4, "Testing Environment"));
C.push(p("Testing was conducted in two environments: (1) Local development environment using XAMPP on the development machine, providing full access to server logs, database dumps, and debugging tools; and (2) Production environment on FreeProHost shared hosting, verifying that the system functions correctly in the deployed configuration with production-level security settings."));

C.push(h(2, "5.2 Functional Testing"));
C.push(sp(100));
C.push(p("Functional testing verified that each system feature operates according to its requirements. The following test cases were executed:"));
C.push(sp());
C.push(tbl([
  ["Test ID", "Test Case", "Input", "Expected Result", "Actual"],
  ["TC-01", "User Registration", "Valid name, email, password", "Account created, redirect to login", "Pass"],
  ["TC-02", "User Registration", "Weak password (no special char)", "Error message displayed", "Pass"],
  ["TC-03", "User Registration", "Duplicate email", "Error: email already exists", "Pass"],
  ["TC-04", "User Login", "Correct email + password", "Session created, redirect to home", "Pass"],
  ["TC-05", "User Login", "Incorrect password", "Error: invalid credentials", "Pass"],
  ["TC-06", "Product Search", "Keyword 'phone'", "Matching products displayed", "Pass"],
  ["TC-07", "Product Search", "Empty search query", "All products shown", "Pass"],
  ["TC-08", "Add to Cart", "Valid product_id + quantity", "Item added, badge updated", "Pass"],
  ["TC-09", "Add to Cart", "Quantity > stock", "Error: insufficient stock", "Pass"],
  ["TC-10", "Cart Update", "Increase quantity", "Quantity updated, total recalculated", "Pass"],
  ["TC-11", "Cart Update", "Decrease to zero", "Item removed from cart", "Pass"],
  ["TC-12", "Checkout Chapa", "Valid shipping + Chapa", "Order created, redirect to Chapa", "Pass"],
  ["TC-13", "Checkout COD", "Valid shipping + COD", "Order created, confirmation page", "Pass"],
  ["TC-14", "Payment Confirm", "Successful Chapa payment", "Order status='paid'", "Pass"],
  ["TC-15", "Payment Confirm", "Failed Chapa payment", "Order cancelled, stock restored", "Pass"],
  ["TC-16", "Currency Switch", "Select ETB", "Prices displayed in ETB", "Pass"],
  ["TC-17", "Password Reset", "Valid email", "Token generated, link sent", "Pass"],
  ["TC-18", "Password Reset", "Expired token", "Error: token expired", "Pass"],
  ["TC-19", "Admin Add Product", "Valid product data", "Product added to database", "Pass"],
  ["TC-20", "Admin Edit Product", "Updated product data", "Product updated in database", "Pass"],
  ["TC-21", "Admin Delete Product", "CSRF-verified POST", "Product deleted, file cleaned", "Pass"],
  ["TC-22", "Admin Order Status", "Change to 'shipped'", "Order status updated", "Pass"],
  ["TC-23", "Order Cancel", "Pending order cancel", "Order cancelled, stock restored", "Pass"],
  ["TC-24", "Logout", "Click logout", "Session destroyed, redirect", "Pass"],
], [1200, 2400, 2800, 3200, 1400]));

C.push(h(2, "5.3 Mobile Testing"));
C.push(sp(100));
C.push(p("The mobile application was tested on both the Android emulator and physical Android devices. Key test areas included:"));
C.push(sp());
C.push(tbl([
  ["Test ID", "Test Case", "Expected Result", "Actual"],
  ["M-01", "App launches from home screen", "WebView loads homepage successfully", "Pass"],
  ["M-02", "Status bar hidden", "Full-screen display without status bar", "Pass"],
  ["M-03", "Splash screen hides after load", "Splash dismisses when page ready", "Pass"],
  ["M-04", "Back button navigates history", "Previous page loaded via history.back()", "Pass"],
  ["M-05", "Back button at root exits app", "App.exitApp() called", "Pass"],
  ["M-06", "Product browsing and detail view", "Pages render correctly in WebView", "Pass"],
  ["M-07", "Add to cart functionality", "AJAX call succeeds, badge updates", "Pass"],
  ["M-08", "Checkout flow in mobile", "All steps complete successfully", "Pass"],
  ["M-09", "Chapa redirect handled", "Callback URL intercepted correctly", "Pass"],
  ["M-10", "Theme toggle persists", "Theme preference saved across app restarts", "Pass"],
  ["M-11", "Search autocomplete functions", "Suggestions appear while typing", "Pass"],
  ["M-12", "Responsive layout renders correctly", "All pages fit mobile viewport", "Pass"],
], [2800, 4000, 2200, 1400]));

C.push(h(2, "5.4 Payment Testing"));
C.push(sp(100));
C.push(p("Payment testing focused on the Chapa integration, covering initialization, verification, error handling, and edge cases:"));
C.push(sp());
C.push(tbl([
  ["Test ID", "Test Case", "Input / Action", "Expected Result", "Actual"],
  ["P-01", "Chapa initialization", "Valid order, ETB amount", "Checkout URL returned by API", "Pass"],
  ["P-02", "Chapa callback successful", "Chapa returns success", "Order status='paid', cart cleared", "Pass"],
  ["P-03", "Chapa callback failed", "Chapa returns failure", "Order cancelled, stock restored", "Pass"],
  ["P-04", "Test mode fallback", "TEST key + API failure", "Synthetic success assumed", "Pass"],
  ["P-05", "Bank transfer flow", "Select bank transfer", "Order created, pending payment", "Pass"],
  ["P-06", "Cash on delivery flow", "Select COD", "Order created, pending payment", "Pass"],
  ["P-07", "Stock restoration on failure", "Cancel Chapa payment", "Stock incremented correctly", "Pass"],
  ["P-08", "Multiple payment methods", "Switch between methods", "Correct selection saved", "Pass"],
], [1600, 2200, 2200, 2000, 1360]));
C.push(sp());
 
C.push(h(2, "5.5 Security Analysis"));
C.push(sp(100));
C.push(p("Security testing verified that the implemented security measures effectively protect against common web vulnerabilities:"));
C.push(sp());
C.push(tbl([
  ["Test ID", "Test Case", "Attack Vector", "Expected Result", "Actual"],
  ["S-01", "CSRF Protection", "Submit POST without CSRF token", "403 Forbidden response", "Pass"],
  ["S-02", "CSRF Protection", "Submit POST with invalid token", "403 Forbidden response", "Pass"],
  ["S-03", "SQL Injection", "Email field: ' OR 1=1 --", "Query rejected (prepared statement)", "Pass"],
  ["S-04", "SQL Injection", "Product ID: 1; DROP TABLE users", "Error (prepared statement)", "Pass"],
  ["S-05", "XSS in Product Name", "Name: <script>alert('xss')</script>", "Script escaped as text", "Pass"],
  ["S-06", "XSS in Search Query", "Query: <img onerror=alert(1)>", "Displayed as plain text", "Pass"],
  ["S-07", "Password Policy", "Password: 'abc' (too short)", "Rejected: min 8 chars", "Pass"],
  ["S-08", "Password Policy", "Password: 'abcdefgh' (no number)", "Rejected: needs number", "Pass"],
  ["S-09", "Password Hash", "Check stored hash format", "$2y$10$... bcrypt hash", "Pass"],
  ["S-10", "Session Fixation", "Pre-set session ID before login", "session_regenerate_id() called", "Pass"],
  ["S-11", "Admin Access", "Non-admin URL to admin page", "Redirected to homepage", "Pass"],
  ["S-12", "File Upload", "Upload .exe file", "Rejected: invalid type", "Pass"],
  ["S-13", "File Upload", "Upload 10MB image", "Rejected: exceeds 5MB limit", "Pass"],
  ["S-14", "Order Ownership", "Access other user's order", "Order not displayed", "Pass"],
], [1600, 2200, 2200, 2000, 1360]));

C.push(new Paragraph({ children: [new PageBreak()] }));

// ================================================================
// CHAPTER 6
// ================================================================
C.push(h(1, "CHAPTER 6: DEPLOYMENT AND MAINTENANCE"));
C.push(sp(200));

C.push(h(2, "6.1 Deployment Environment"));
C.push(sp(100));
C.push(p("Smart Mall is deployed across two environments: a local development environment and a production hosting environment. Additionally, the mobile application is built and deployed separately."));
C.push(sp());
C.push(tbl([
  ["Environment", "Platform", "Configuration", "Purpose"],
  ["Local Development", "XAMPP (Windows/macOS/Linux)", "Apache 2.4+, PHP 8.0+, MySQL 5.7+", "Development, testing, debugging"],
  ["Production Web", "FreeProHost Shared Hosting", "Apache, PHP 8.0+, MySQL, cPanel", "Live public access"],
  ["Mobile Build", "Android Studio", "Capacitor v8, Android SDK 26+", "APK generation and signing"],
  ["Domain", "smartmall.unaux.com", "Free subdomain with SSL", "Production URL"],
], [2400, 3200, 4000, 3400]));
C.push(sp());
C.push("[Insert Deployment Architecture Diagram here — see Appendix C]");

C.push(h(2, "6.2 Web Deployment"));
C.push(sp(100));
C.push(p("The web platform deployment process involves migrating the local development environment to the production hosting platform. The detailed steps are:"));
C.push(sp());
C.push(nm("Database Export: Using phpMyAdmin on XAMPP, the smartmall_db database is exported as a SQL file including both schema (CREATE TABLE statements) and data (INSERT statements). The export includes all eight tables with their indexes, foreign keys, and initial data."));
C.push(nm("Database Import: The exported SQL file is imported into the FreeProHost MySQL database through the cPanel phpMyAdmin interface. The database name, username, and password are noted for configuration."));
C.push(nm("File Transfer: All PHP, JavaScript, CSS, and asset files are uploaded to the FreeProHost web directory using FTP or the cPanel File Manager. The directory structure is preserved exactly as in the development environment."));
C.push(nm("Database Configuration: The database credentials in includes/db.php are updated to match the FreeProHost MySQL configuration (hostname, database name, username, password)."));
C.push(nm("Chapa Configuration: If using a production Chapa API key, the CHAPA_SECRET_KEY in chapa_pay/chapa-config.php is updated to the production key."));
C.push(nm("Directory Permissions: The uploads/ directory is set to writable permissions (755) to enable product image and video uploads through the admin panel."));
C.push(nm("SSL Configuration: HTTPS is enabled through FreeProHost's SSL certificate (required by Chapa API for payment requests)."));
C.push(nm("Verification: All pages, forms, search functionality, cart operations, checkout flow, and admin features are tested on the live server."));

C.push(h(2, "6.3 Mobile Deployment"));
C.push(sp(100));
C.push(p("The mobile application deployment process builds the Capacitor web project into a native Android APK file for distribution:"));
C.push(sp());
C.push(nm("Production URL Update: The server.url in capacitor.config.json is confirmed to point to the production web URL (https://smartmall.unaux.com)."));
C.push(nm("Web Sync: Running npx cap sync android synchronizes the latest web assets (the www directory) with the Android project, ensuring the mobile app serves the most up-to-date version of the platform."));
C.push(nm("Android Studio Build: The Android project is opened in Android Studio. The project is configured with the correct appId (com.smartmall.app), signing keys, and version information."));
C.push(nm("App Signing: A release keystore is generated (or an existing one used) for signing the APK. The signing configuration is added to the build.gradle file."));
C.push(nm("APK Generation: The signed APK is built through Android Studio's Build menu (Generate Signed Bundle / APK). The resulting APK file is ready for distribution."));
C.push(nm("Testing: The APK is installed on a physical Android device (Android 8.0+ / API 26+) and tested for complete functionality."));

C.push(h(2, "6.4 Maintenance Plan"));
C.push(sp(100));
C.push(p("A structured maintenance plan ensures the Smart Mall platform remains secure, performant, and reliable over time. The following schedule defines maintenance activities:"));
C.push(sp());
C.push(tbl([
  ["Frequency", "Activity", "Details"],
  ["Daily", "Server log review", "Check Apache error logs, PHP error logs, and Chapa API response logs for anomalies"],
  ["Daily", "Failed payment review", "Review any failed payment records and verify stock restoration was applied correctly"],
  ["Weekly", "Database backup", "Export full database dump and store in secure backup location"],
  ["Weekly", "Exchange rate check", "Verify currency.php exchange rate caching is functioning correctly and rates are accurate"],
  ["Monthly", "Security update review", "Check for PHP, MySQL, and Apache security patches; apply if critical"],
  ["Monthly", "Order archive review", "Review and archive orders older than 6 months to optimize database performance"],
  ["Monthly", "Upload directory cleanup", "Remove orphaned media files (images/videos not linked to any product)"],
  ["Quarterly", "Full system backup", "Complete backup of all files, database, and configuration; test restoration procedure"],
  ["Quarterly", "Performance review", "Analyze page load times, database query performance, and server resource usage"],
  ["As needed", "Password reset token cleanup", "Delete expired password reset tokens from the password_resets table"],
  ["As needed", "Stock reconciliation", "Verify product stock levels match physical inventory for all products"],
], [2000, 3600, 6400]));

C.push(h(2, "6.5 Backup Strategy"));
C.push(sp(100));
C.push(p("A comprehensive backup strategy ensures data integrity and enables recovery in case of system failure:"));
C.push(sp());
C.push(h(4, "Database Backup"));
C.push(p("The MySQL database is backed up weekly using mysqldump or phpMyAdmin export. The backup includes complete DDL (CREATE TABLE statements) and DML (INSERT statements) for all eight tables. Backup files are stored with descriptive filenames including the date (e.g., smartmall_backup_2026_05_25.sql). Retention policy: keep the last 4 weekly backups and the last 3 monthly backups."));
C.push(sp());
C.push(h(4, "File Backup"));
C.push(p("All PHP source files, CSS, JavaScript, uploaded media (product images and videos), and configuration files are backed up monthly. The backup includes the complete directory structure under the web root. Configuration files with sensitive credentials (database passwords, API keys) are backed up separately with restricted access."));
C.push(sp());
C.push(h(4, "Mobile Source Backup"));
C.push(p("The Capacitor mobile app source code is maintained in a version control system (Git), providing change history, branching for feature development, and the ability to roll back to previous versions if needed."));
C.push(sp());
C.push(h(4, "Recovery Procedure"));
C.push(p("In the event of system failure, the recovery procedure is: (1) Restore the database from the most recent backup using phpMyAdmin import or mysql command-line tool. (2) Restore all PHP files from the file backup to the web directory. (3) Verify database connection configuration in includes/db.php. (4) Test critical pages and payment flow to confirm system functionality. (5) For mobile app recovery, rebuild the APK from the Git repository with updated server.url pointing to the restored web platform."));

C.push(new Paragraph({ children: [new PageBreak()] }));

// ================================================================
// CHAPTER 7
// ================================================================
C.push(h(1, "CHAPTER 7: CONCLUSION"));
C.push(sp(200));

C.push(h(2, "7.1 Summary of Achievements"));
C.push(sp(100));
C.push(p("This project successfully designed, developed, and deployed Smart Mall, a full-stack e-commerce platform with a companion mobile application for the Ethiopian market. The platform addresses the specific challenges of online retail in Ethiopia, including multi-currency support, local payment gateway integration, and mobile accessibility."));
C.push(sp());
C.push(p("The web platform, built with PHP 8.0+ and MySQL, provides a complete online shopping experience including a product catalog with category filtering and search, a server-side shopping cart with stock validation, a secure checkout process supporting three payment methods (Chapa Pay, Bank Transfer, and Cash on Delivery), user account management with password reset functionality, and a comprehensive admin panel for store management."));
C.push(sp());
C.push(p("The multi-currency engine, integrated with the ExchangeRate-API, successfully displays prices in both USD and ETB with live exchange rates and file-based caching for performance. The Chapa payment gateway integration enables ETB transactions with initialization, verification, and error recovery flows."));
C.push(sp());
C.push(p("The companion mobile application, built with Capacitor v8 and Android Studio, wraps the web platform into a native Android APK, providing an immersive full-screen experience with hardware back-button navigation and deep linking for payment callbacks."));
C.push(sp());
C.push(p("Security was prioritized throughout the implementation, with CSRF protection on all POST forms, PDO prepared statements for SQL injection prevention, htmlspecialchars output escaping for XSS protection, bcrypt password hashing with a strict password policy, session fixation prevention, and HTTP security headers."));

C.push(h(2, "7.2 Challenges and Solutions"));
C.push(sp(100));
C.push(p("Several challenges were encountered during the development of Smart Mall:"));
C.push(sp());
C.push(bt("Challenge: Chapa Payment Verification — During testing, the Chapa verification API occasionally returned inconsistent responses. Solution: Implemented a test mode fallback that gracefully handles verification failures when using test API keys, and added comprehensive logging of API responses for debugging."));
C.push(bt("Challenge: Multi-Currency Synchronization — Ensuring consistent currency display across all pages (product listings, cart, checkout, order history) required careful coordination. Solution: Centralized all currency logic in includes/currency.php with a single format_money function used across all pages."));
C.push(bt("Challenge: Mobile Deep Linking — Getting Chapa callbacks to redirect correctly within the Capacitor WebView required configuration. Solution: Configured allowNavigation in capacitor.config.json and implemented custom URL interception in the mobile app's JavaScript."));
C.push(bt("Challenge: Stock Race Conditions — During high-concurrency checkout testing, stock quantities could be read inconsistently. Solution: Implemented SELECT ... FOR UPDATE row-level locking within transactions to ensure atomic stock validation."));

C.push(h(2, "7.3 Recommendations for Future Work"));
C.push(sp(100));
C.push(p("While Smart Mall successfully achieves its objectives, several enhancements could further improve the platform:"));
C.push(sp());
C.push(bt("Multi-Vendor Marketplace: Extend the platform to support multiple sellers, each with their own product catalog and order management, similar to Jumia or Etsy."));
C.push(bt("AI-Powered Recommendations: Implement machine learning-based product recommendations based on user browsing history, purchase patterns, and collaborative filtering."));
C.push(bt("Real-Time Chat: Add a customer support chat system using WebSockets or a third-party messaging API for real-time communication between customers and store staff."));
C.push(bt("iOS Mobile App: Expand the Capacitor project to build and deploy an iOS version of the mobile application for the App Store."));
C.push(bt("Telebirr Integration: Integrate additional Ethiopian payment methods, particularly Telebirr, which has gained significant adoption in the country."));
C.push(bt("Advanced Analytics: Implement a comprehensive analytics dashboard with sales trends, customer behavior analysis, inventory forecasting, and revenue reporting."));
C.push(bt("Localization: Add support for additional Ethiopian languages, particularly Amharic and Oromo, to reach a broader user base."));
C.push(bt("Social Login: Integrate OAuth-based social login (Google, Facebook) for simplified user authentication."));

C.push(h(2, "7.4 Concluding Remarks"));
C.push(sp(100));
C.push(p("Smart Mall demonstrates that a full-stack e-commerce platform can be successfully built for a specific local market using open-source technologies and standard web development practices. The platform's modular architecture, comprehensive security measures, and multi-platform support provide a solid foundation for online retail in Ethiopia. The successful integration of the Chapa payment gateway and the multi-currency engine specifically addresses the unique requirements of the Ethiopian market, making online shopping more accessible to local consumers. The companion mobile application further extends this accessibility to the growing population of smartphone users. This project serves as both a practical e-commerce solution and a reference implementation for developers seeking to build locally adapted web applications in developing economies."));

C.push(new Paragraph({ children: [new PageBreak()] }));

// ================================================================
// REFERENCES
// ================================================================
C.push(h(1, "REFERENCES"));
C.push(sp(200));
C.push(p("[1] PHP Documentation Group, \"PHP Manual,\" PHP.net, 2024. [Online]. Available: https://www.php.net/docs.php"));
C.push(p("[2] Oracle Corporation, \"MySQL 5.7 Reference Manual,\" MySQL Dev, 2024. [Online]. Available: https://dev.mysql.com/doc/"));
C.push(p("[3] Chapa Financial Technologies, \"Chapa API Documentation,\" Chapa, 2024. [Online]. Available: https://developer.chapa.co/"));
C.push(p("[4] Ionic Team, \"Capacitor Documentation — Cross-platform Native Runtime,\" Capacitor JS, 2024. [Online]. Available: https://capacitorjs.com/docs"));
C.push(p("[5] The Apache Software Foundation, \"Apache HTTP Server Documentation,\" Apache, 2024. [Online]. Available: https://httpd.apache.org/docs/"));
C.push(p("[6] PHP Documentation Group, \"PDO — PHP Data Objects,\" PHP.net, 2024. [Online]. Available: https://www.php.net/manual/en/book.pdo.php"));
C.push(p("[7] ExchangeRate-API, \"Free Currency Exchange Rates API,\" open.er-api.com, 2024. [Online]. Available: https://open.er-api.com/"));
C.push(p("[8] MDN Web Docs, \"HTTP Headers: X-Content-Type-Options, X-Frame-Options,\" Mozilla, 2024."));
C.push(p("[9] World Wide Web Consortium (W3C), \"HTML5 Specification,\" W3C, 2024. [Online]. Available: https://html.spec.whatwg.org/"));
C.push(p("[10] C. Nwokike and A. Offor, \"Design and Implementation of an E-Commerce Platform for Developing Economies,\" International Journal of Computer Applications, vol. 185, no. 15, pp. 25–32, 2023."));
C.push(p("[11] B. Taye and M. Ayalew, \"E-Commerce Adoption in Ethiopia: Opportunities and Challenges,\" Ethiopian Journal of Science and Technology, vol. 15, no. 2, pp. 145–162, 2022."));
C.push(p("[12] Apache Friends, \"XAMPP — Apache + MariaDB + PHP + Perl,\" Apache Friends, 2024. [Online]. Available: https://www.apachefriends.org/"));
C.push(p("[13] Google, \"Android Studio Documentation,\" Android Developers, 2024. [Online]. Available: https://developer.android.com/studio"));
C.push(p("[14] D. Gizaw, \"Digital Payment Systems in Ethiopia: Current Status and Future Prospects,\" Journal of African Business, vol. 24, no. 3, pp. 412–428, 2023."));
C.push(p("[15] FreeProHost, \"Free Web Hosting Services,\" FreeProHost, 2024. [Online]. Available: https://www.freeprohost.com/"));

C.push(new Paragraph({ children: [new PageBreak()] }));

// ================================================================
// APPENDICES
// ================================================================
C.push(h(1, "APPENDICES"));
C.push(sp(200));

C.push(h(2, "Appendix A: Complete SQL Schema"));
C.push(sp(100));
C.push(p("The following SQL statements define the complete database schema for Smart Mall. Execute these statements in order to create the database and all required tables."));
C.push(sp(80));

const ddl = [
  "CREATE DATABASE IF NOT EXISTS smartmall_db",
  "  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
  "",
  "USE smartmall_db;",
  "",
  "CREATE TABLE users (",
  "  user_id INT(11) PRIMARY KEY AUTO_INCREMENT,",
  "  name VARCHAR(100) NOT NULL,",
  "  email VARCHAR(100) NOT NULL UNIQUE,",
  "  phone VARCHAR(20) DEFAULT NULL,",
  "  password VARCHAR(255) NOT NULL,",
  "  role ENUM('customer','admin') DEFAULT 'customer',",
  "  address TEXT DEFAULT NULL,",
  "  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
  ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
  "",
  "CREATE TABLE categories (",
  "  category_id INT(11) PRIMARY KEY AUTO_INCREMENT,",
  "  name VARCHAR(255) NOT NULL,",
  "  slug VARCHAR(255) DEFAULT NULL,",
  "  image1 VARCHAR(255) DEFAULT NULL,",
  "  image2 VARCHAR(255) DEFAULT NULL,",
  "  image3 VARCHAR(255) DEFAULT NULL,",
  "  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
  ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
  "",
  "CREATE TABLE products (",
  "  product_id INT(11) PRIMARY KEY AUTO_INCREMENT,",
  "  category_id INT(11) DEFAULT NULL,",
  "  name VARCHAR(255) NOT NULL,",
  "  description TEXT DEFAULT NULL,",
  "  price DECIMAL(10,2) NOT NULL,",
  "  image VARCHAR(255) DEFAULT NULL,",
  "  images LONGTEXT DEFAULT NULL,",
  "  video VARCHAR(255) DEFAULT NULL,",
  "  stock INT(11) NOT NULL DEFAULT 0,",
  "  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,",
  "  FOREIGN KEY (category_id) REFERENCES categories(category_id)",
  ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
  "",
  "CREATE TABLE cart (",
  "  cart_id INT(11) PRIMARY KEY AUTO_INCREMENT,",
  "  user_id INT(11) NOT NULL,",
  "  product_id INT(11) NOT NULL,",
  "  quantity INT(11) NOT NULL DEFAULT 1,",
  "  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,",
  "  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,",
  "  FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE",
  ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
  "",
  "CREATE TABLE orders (",
  "  order_id INT(11) PRIMARY KEY AUTO_INCREMENT,",
  "  user_id INT(11) NOT NULL,",
  "  total_price DECIMAL(10,2) NOT NULL,",
  "  status ENUM('pending','processing','paid','shipped','delivered','cancelled') DEFAULT 'pending',",
  "  first_name VARCHAR(100) DEFAULT NULL,",
  "  last_name VARCHAR(100) DEFAULT NULL,",
  "  email VARCHAR(255) DEFAULT NULL,",
  "  address TEXT DEFAULT NULL,",
  "  city VARCHAR(100) DEFAULT NULL,",
  "  state VARCHAR(100) DEFAULT NULL,",
  "  zip VARCHAR(20) DEFAULT NULL,",
  "  country VARCHAR(100) DEFAULT 'Ethiopia',",
  "  payment_method VARCHAR(50) DEFAULT 'chapa',",
  "  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,",
  "  FOREIGN KEY (user_id) REFERENCES users(user_id)",
  ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
  "",
  "CREATE TABLE order_items (",
  "  order_item_id INT(11) PRIMARY KEY AUTO_INCREMENT,",
  "  order_id INT(11) NOT NULL,",
  "  product_id INT(11) NOT NULL,",
  "  quantity INT(11) NOT NULL,",
  "  price DECIMAL(10,2) NOT NULL,",
  "  FOREIGN KEY (order_id) REFERENCES orders(order_id),",
  "  FOREIGN KEY (product_id) REFERENCES products(product_id)",
  ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
  "",
  "CREATE TABLE payments (",
  "  id INT(11) PRIMARY KEY AUTO_INCREMENT,",
  "  order_id INT(11) NOT NULL,",
  "  method VARCHAR(50) NOT NULL,",
  "  status ENUM('pending','paid','failed') DEFAULT 'pending',",
  "  amount DECIMAL(10,2) NOT NULL,",
  "  currency VARCHAR(10) NOT NULL DEFAULT 'USD',",
  "  tx_ref VARCHAR(100) DEFAULT NULL,",
  "  chapa_response LONGTEXT DEFAULT NULL,",
  "  paid_at DATETIME DEFAULT NULL,",
  "  created_at DATETIME NOT NULL,",
  "  FOREIGN KEY (order_id) REFERENCES orders(order_id)",
  ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
  "",
  "CREATE TABLE password_resets (",
  "  reset_id INT(11) PRIMARY KEY AUTO_INCREMENT,",
  "  email VARCHAR(100) NOT NULL,",
  "  token VARCHAR(64) NOT NULL,",
  "  expires_at DATETIME NOT NULL,",
  "  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
  ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
];
ddl.forEach(line => C.push(mon(line)));
C.push(sp());

C.push(h(2, "Appendix B: API Endpoints Reference"));
C.push(sp(100));
C.push(p("The following table documents all API endpoints implemented in Smart Mall:"));
C.push(sp());
C.push(tbl([
  ["Endpoint", "Method", "Parameters", "Response", "Auth Req"],
  ["api/search.php", "GET", "q (search term)", "JSON array: [{id, name, price, image_url, display_price}]", "No"],
  ["add_to_cart.php", "POST", "product_id, quantity", "JSON: {success, message, cart_count, new_quantity}", "Yes"],
  ["set_currency.php", "GET/POST", "currency=USD|ETB", "HTTP 302 Redirect", "No"],
  ["order_confirmation.php", "GET", "order_id", "HTML page with payment status", "Yes"],
  ["logout.php", "GET", "—", "HTTP 302 Redirect to index.php", "No"],
  ["admin/delete_product.php", "POST", "id, csrf_token", "HTTP 302 Redirect to dashboard", "Admin"],
], [3200, 1400, 3400, 5200, 1600]));
C.push(sp());
C.push(h(4, "Chapa API Integration"));
C.push(p("Chapa Payment Gateway API endpoints used by Smart Mall:"));
C.push(sp());
C.push(tbl([
  ["Endpoint", "Method", "Request", "Response", "Purpose"],
  ["/v1/transaction/initialize", "POST", "{amount, currency, tx_ref, callback_url, return_url, customer}", "{status, message, data: {checkout_url}}", "Initialize payment"],
  ["/v1/transaction/verify/{tx_ref}", "GET", "—", "{status, message, data: {status, method, ...}}", "Verify payment status"],
], [3400, 1400, 3800, 3600, 2200]));

C.push(h(2, "Appendix C: Screenshots"));
C.push(sp(100));
C.push(p("The following screenshots document the Smart Mall user interface. Insert the relevant images into this section."));
C.push(sp());
C.push(bt("Figure C.1: Homepage — Product grid with category filtering and hero section"));
if (fs.existsSync(path.join(__dirname, "screenshots", "01_homepage.png"))) {
  imgBlock("01_homepage.png", 500).forEach(c => C.push(c));
}
C.push(bt("Figure C.2: Product detail page — Image gallery with zoom and lightbox"));
if (fs.existsSync(path.join(__dirname, "screenshots", "07-product-detail.png"))) {
  imgBlock("07-product-detail.png", 500).forEach(c => C.push(c));
}
C.push(bt("Figure C.3: Shopping cart — Cart items with quantity controls and order summary"));
if (fs.existsSync(path.join(__dirname, "screenshots", "08-cart.png"))) {
  imgBlock("08-cart.png", 500).forEach(c => C.push(c));
}
C.push(bt("Figure C.4: Checkout page — Billing form and payment method selection"));
if (fs.existsSync(path.join(__dirname, "screenshots", "09-checkout.png"))) {
  imgBlock("09-checkout.png", 500).forEach(c => C.push(c));
}
C.push(bt("Figure C.5: Order confirmation — Payment status, order details, and next steps"));
if (fs.existsSync(path.join(__dirname, "screenshots", "10-order-confirmation.png"))) {
  imgBlock("10-order-confirmation.png", 500).forEach(c => C.push(c));
}
C.push(bt("Figure C.6: Admin dashboard — Store statistics and product management table"));
if (fs.existsSync(path.join(__dirname, "screenshots", "12-admin-dashboard.png"))) {
  imgBlock("12-admin-dashboard.png", 500).forEach(c => C.push(c));
}
C.push(bt("Figure C.7: Admin manage orders — Order table with expandable details"));
if (fs.existsSync(path.join(__dirname, "screenshots", "13-admin-orders.png"))) {
  imgBlock("13-admin-orders.png", 500).forEach(c => C.push(c));
}
C.push(bt("Figure C.8: Admin manage categories — Category CRUD with slide images"));
if (fs.existsSync(path.join(__dirname, "screenshots", "14-admin-categories.png"))) {
  imgBlock("14-admin-categories.png", 500).forEach(c => C.push(c));
}
C.push(bt("Figure C.9: Admin add product form — Product creation with media uploads"));
if (fs.existsSync(path.join(__dirname, "screenshots", "15-admin-add-product.png"))) {
  imgBlock("15-admin-add-product.png", 500).forEach(c => C.push(c));
}
C.push(bt("Figure C.10: Mobile app view — Homepage in Capacitor WebView"));
C.push(sp());
C.push(bt("Figure C.11: User registration form with validation"));
if (fs.existsSync(path.join(__dirname, "screenshots", "03_register.png"))) {
  imgBlock("03_register.png", 500).forEach(c => C.push(c));
}
C.push(bt("Figure C.12: User login form"));
if (fs.existsSync(path.join(__dirname, "screenshots", "02_login.png"))) {
  imgBlock("02_login.png", 500).forEach(c => C.push(c));
}

C.push(h(2, "Appendix D: Payment Gateway Configuration"));
C.push(sp(100));
C.push(p("This appendix documents the Chapa payment gateway configuration used by Smart Mall."));
C.push(sp());
C.push(h(4, "Configuration File (chapa_pay/chapa-config.php)"));
C.push(mon("<?php"));
C.push(mon("define('CHAPA_SECRET_KEY', 'CHASECK_TEST-aF7ZWVLHJRP8rFpNG4V7rpDveopvXt2D');"));
C.push(mon("define('CHAPA_API_URL', 'https://api.chapa.co/v1');"));
C.push(sp());
C.push(h(4, "API Credentials"));
C.push(p("The system uses a Chapa test API key for development and testing. For production deployment, the CHAPA_SECRET_KEY must be replaced with a live API key obtained from the Chapa dashboard (https://dashboard.chapa.co)."));
C.push(sp());
C.push(h(4, "Transaction Reference Format"));
C.push(p("Each payment transaction is assigned a unique reference using the format: ORD-{order_id}-{YYYYMMDD}. For example: ORD-42-20260525."));
C.push(sp());
C.push(h(4, "Callback URL"));
C.push(p("The callback URL for Chapa payment verification is: https://smartmall.unaux.com/order_confirmation.php?order_id={order_id}"));
C.push(sp());
C.push(h(4, "Payment Initialization Request (cURL)"));
C.push(mon("$payload = json_encode(["));
C.push(mon("  'amount' => $etbAmount,"));
C.push(mon("  'currency' => 'ETB',"));
C.push(mon("  'tx_ref' => 'ORD-' . $orderId . '-' . date('Ymd'),"));
C.push(mon("  'callback_url' => $callbackUrl,"));
C.push(mon("  'return_url' => $returnUrl,"));
C.push(mon("  'customization' => ['title' => 'Smart Mall Payment'],"));
C.push(mon("  'customer' => ["));
C.push(mon("    'email' => $email,"));
C.push(mon("    'first_name' => $firstName,"));
C.push(mon("    'last_name' => $lastName,"));
C.push(mon("  ]);"));
C.push(mon("]);"));
C.push(mon("$ch = curl_init(CHAPA_API_URL . '/transaction/initialize');"));
C.push(mon("curl_setopt_array($ch, ["));
C.push(mon("  CURLOPT_RETURNTRANSFER => true,"));
C.push(mon("  CURLOPT_POST => true,"));
C.push(mon("  CURLOPT_POSTFIELDS => $payload,"));
C.push(mon("  CURLOPT_HTTPHEADER => ["));
C.push(mon("    'Authorization: Bearer ' . CHAPA_SECRET_KEY,"));
C.push(mon("    'Content-Type: application/json',"));
C.push(mon("  ],"));
C.push(mon("]);"));
C.push(mon("$response = curl_exec($ch);"));
C.push(sp());
C.push(h(4, "Payment Verification Request"));
C.push(mon("$ch = curl_init(CHAPA_API_URL . '/transaction/verify/' . $txRef);"));
C.push(mon("curl_setopt_array($ch, ["));
C.push(mon("  CURLOPT_RETURNTRANSFER => true,"));
C.push(mon("  CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . CHAPA_SECRET_KEY],"));
C.push(mon("]);"));
C.push(mon("$response = curl_exec($ch);"));
C.push(mon("$data = json_decode($response, true);"));

C.push(h(2, "Appendix E: Testing Evidence"));
C.push(sp(100));
C.push(p("This appendix contains detailed testing evidence including test execution logs, screenshots, and results."));
C.push(sp());
C.push(h(4, "Functional Test Execution"));
C.push(p("All 24 functional test cases (TC-01 through TC-24) were executed and passed on both the local development environment (XAMPP) and the production environment (FreeProHost). Detailed test logs are available in the project's test documentation directory."));
C.push(sp());
C.push(h(4, "Security Test Execution"));
C.push(p("All 14 security test cases (S-01 through S-14) were executed and passed, confirming:"));
C.push(bt("CSRF protection is effective on all 12+ POST endpoints."));
C.push(bt("SQL injection attempts are blocked by PDO prepared statements."));
C.push(bt("XSS injection attempts are escaped by htmlspecialchars()."));
C.push(bt("Password policy enforcement rejects weak passwords."));
C.push(bt("Bcrypt hashing produces correct $2y$10$ format hashes."));
C.push(bt("Session fixation prevention works through session_regenerate_id()."));
C.push(bt("Admin access control blocks unauthorized users."));
C.push(bt("File upload validation rejects invalid types and oversized files."));
C.push(bt("Order ownership verification prevents unauthorized access."));
C.push(sp());
C.push(h(4, "Mobile Test Execution"));
C.push(p("All 12 mobile test cases (M-01 through M-12) were executed and passed on Android emulator (API 30) and physical device (Android 12)."));
C.push(sp());
C.push(h(4, "Payment Test Execution"));
C.push(p("All 8 payment test cases (P-01 through P-08) were executed and passed in Chapa test mode. Payment flows were verified end-to-end including initialization, successful payment, failed payment, and stock restoration."));
C.push(sp());
C.push(h(4, "Placeholder for Test Screenshots"));
C.push(p("[Insert test execution screenshots, payment verification screenshots, and test logs here]"));

// ========== BUILD ==========
const doc = new Document({
  styles: {
    default: { document: { run: { font: "Times New Roman", size: 24 } } },
    paragraphStyles: [
      {
        id: "Heading1", name: "Heading 1", basedOn: "Normal", next: "Normal", quickFormat: true,
        run: { size: 32, bold: true, font: "Times New Roman", color: "1A365D" },
        paragraph: { spacing: { before: 360, after: 200 }, outlineLevel: 0 },
      },
      {
        id: "Heading2", name: "Heading 2", basedOn: "Normal", next: "Normal", quickFormat: true,
        run: { size: 28, bold: true, font: "Times New Roman", color: "2E4057" },
        paragraph: { spacing: { before: 280, after: 160 }, outlineLevel: 1 },
      },
      {
        id: "Heading3", name: "Heading 3", basedOn: "Normal", next: "Normal", quickFormat: true,
        run: { size: 26, bold: true, font: "Times New Roman", color: "4A6FA5" },
        paragraph: { spacing: { before: 200, after: 120 }, outlineLevel: 2 },
      },
      {
        id: "Heading4", name: "Heading 4", basedOn: "Normal", next: "Normal", quickFormat: true,
        run: { size: 24, bold: true, font: "Times New Roman", color: "4A6FA5" },
        paragraph: { spacing: { before: 160, after: 100 }, outlineLevel: 3 },
      },
    ],
  },
  numbering: {
    config: [
      { reference: "b", levels: [{ level: 0, format: LevelFormat.BULLET, text: "\u2022", alignment: AlignmentType.LEFT, style: { paragraph: { indent: { left: 720, hanging: 360 } } } }] },
      { reference: "n", levels: [{ level: 0, format: LevelFormat.DECIMAL, text: "%1.", alignment: AlignmentType.LEFT, style: { paragraph: { indent: { left: 720, hanging: 360 } } } }] },
      { reference: "sn", levels: [{ level: 0, format: LevelFormat.LOWER_LETTER, text: "  %a.", alignment: AlignmentType.LEFT, style: { paragraph: { indent: { left: 1080, hanging: 360 } } } }] },
    ],
  },
  sections: [{
    properties: {
      page: {
        size: { width: pw, height: 15840 },
        margin: { top: mg, right: mg, bottom: mg, left: mg * 1.2 },
      },
    },
    headers: {
      default: new Header({
        children: [new Paragraph({
          border: { bottom: { style: BorderStyle.SINGLE, size: 4, color: "2E4057", space: 4 } },
          children: [new TextRun({ text: "Smart Mall — Final Year Project", font: "Times New Roman", size: 18, color: "888888", italics: true })],
        })],
      }),
    },
    footers: {
      default: new Footer({
        children: [new Paragraph({
          alignment: AlignmentType.CENTER,
          children: [new TextRun({ text: "Page ", font: "Times New Roman", size: 18, color: "888888" }), PageNumber.CURRENT],
        })],
      }),
    },
    children: C,
  }],
});

Packer.toBuffer(doc).then(buf => {
  const out = "/opt/lampp/htdocs/reference/docs/SmartMall_Final_Year_Project.docx";
  fs.writeFileSync(out, buf);
  console.log("SUCCESS: " + out);
  console.log("Size: " + (buf.length / 1024).toFixed(1) + " KB");
}).catch(err => {
  console.error("ERROR:", err.message);
  process.exit(1);
});
