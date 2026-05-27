# CHAPTER 6: DEPLOYMENT AND MAINTENANCE

## 6.1 Deployment Environment

The Smart Mall system is deployed on a XAMPP/LAMPP server environment providing Apache, MySQL, and PHP.

### Table 6.1: Server Requirements

| Component | Requirement | Actual | Status |
|-----------|-------------|--------|--------|
| Operating System | Linux/Windows | Ubuntu 24.04 | ✅ |
| Web Server | Apache 2.4+ | Apache 2.4.58 | ✅ |
| PHP | 7.4+ | PHP 8.3.6 | ✅ |
| MySQL | 8.0+ | MySQL 8.0.37 | ✅ |
| Disk Space | 500MB+ | 2GB available | ✅ |
| RAM | 2GB+ | 8GB | ✅ |
| PHP Extensions | mysqli, pdo, json | All installed | ✅ |

### 6.1.1 Server Configuration

**XAMPP/LAMPP Setup:**
- Installation Path: `/opt/lampp`
- Document Root: `/opt/lampp/htdocs`
- Project Path: `/opt/lampp/htdocs/reference`
- Database: `smartmall_db`

**Services:**
- Apache Web Server: Port 80
- MySQL Database: Port 3306
- phpMyAdmin: http://localhost/phpmyadmin

### 6.1.2 Apache Configuration

**.htaccess Configuration:**
```apache
RewriteEngine On
RewriteBase /reference/

# Remove .php extension
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME}.php -f
RewriteRule ^(.*)$ $1.php [L]

# Security headers
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
```

### 6.1.3 PHP Configuration

**php.ini Settings:**
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 256M
display_errors = Off
error_reporting = E_ALL
session.cookie_httponly = 1
session.use_only_cookies = 1
```

### 6.1.4 Database Configuration

**Database Setup:**
```sql
CREATE DATABASE smartmall_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
```

**Connection Configuration:**
```php
function getDB() {
    $host = 'localhost';
    $dbname = 'smartmall_db';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    return $pdo;
}
```

## 6.2 Web Application Deployment

### 6.2.1 File Structure

**Deployed Files:**
```
/opt/lampp/htdocs/reference/
├── index.php (Home page)
├── login.php (User login)
├── register.php (User registration)
├── products.php (Product listing)
├── product.php (Product details)
├── cart.php (Shopping cart)
├── checkout.php (Checkout form)
├── orders.php (Order history)
├── payment.php (Payment processing)
├── admin/
│   ├── dashboard.php
│   ├── manage_products.php
│   ├── add_product.php
│   ├── edit_product.php
│   ├── delete_product.php
│   └── manage_orders.php
├── api/
│   ├── auth.php
│   ├── products.php
│   ├── categories.php
│   ├── orders.php
│   └── profile.php
├── includes/
│   ├── db.php
│   ├── header.php
│   └── footer.php
└── assets/
    ├── css/
    ├── js/
    └── images/
```

### 6.2.2 Database Deployment

**Tables Created:**
1. users (user accounts)
2. products (131 products)
3. categories (4 categories)
4. orders (customer orders)
5. order_items (order details)
6. payments (payment records)
7. cart (shopping cart)
8. password_resets (password recovery)

**Data Loaded:**
- 131 products across 4 categories
- Fashion & Apparel: 33 products
- Electronics & Gadgets: 32 products
- Home & Living: 33 products
- Beauty & Health: 33 products

### 6.2.3 Access URLs

**Public URLs:**
- Website: http://localhost/reference/
- Login: http://localhost/reference/login.php
- Register: http://localhost/reference/register.php
- Products: http://localhost/reference/products.php

**Admin URLs:**
- Admin Dashboard: http://localhost/reference/admin/
- Manage Products: http://localhost/reference/admin/manage_products.php
- Manage Orders: http://localhost/reference/admin/manage_orders.php

**API Endpoints:**
- Auth API: http://localhost/reference/api/auth.php
- Products API: http://localhost/reference/api/products.php
- Orders API: http://localhost/reference/api/orders.php

## 6.3 Mobile Application Deployment

### 6.3.1 APK Generation

**Build Commands:**

**Debug APK (for testing):**
```bash
cd /opt/lampp/htdocs/reference/flutter_app/smart_mall_app
flutter build apk --debug
```

**Release APK (for distribution):**
```bash
flutter build apk --release
```

**Split APKs (optimized per architecture):**
```bash
flutter build apk --split-per-abi
```

**APK Output Locations:**
- Debug: `build/app/outputs/flutter-apk/app-debug.apk`
- Release: `build/app/outputs/flutter-apk/app-release.apk`
- ARM 32-bit: `build/app/outputs/flutter-apk/app-armeabi-v7a-release.apk`
- ARM 64-bit: `build/app/outputs/flutter-apk/app-arm64-v8a-release.apk`
- x86 64-bit: `build/app/outputs/flutter-apk/app-x86_64-release.apk`

### 6.3.2 APK Installation

**Method 1: ADB (Android Debug Bridge)**
```bash
# Install ADB
sudo apt install adb

# Enable USB Debugging on phone
# Settings → About Phone → Tap Build Number 7 times
# Settings → Developer Options → Enable USB Debugging

# Connect phone and verify
adb devices

# Install APK
adb install build/app/outputs/flutter-apk/app-release.apk
```

**Method 2: Manual Installation**
1. Copy APK to phone (USB, email, cloud)
2. Open Files app on phone
3. Navigate to APK location
4. Tap APK file
5. Enable "Install from Unknown Sources" if prompted
6. Tap "Install"

**Method 3: QR Code Distribution**
1. Upload APK to file hosting (Google Drive, Dropbox)
2. Generate shareable link
3. Create QR code from link
4. Scan QR code with phone
5. Download and install

### 6.3.3 Mobile Distribution Options

**1. Direct Distribution:**
- Share APK file directly with users
- Host on website for download
- Distribute via email or messaging apps

**2. Google Play Store (Future):**
- Create Google Play Developer account ($25 one-time fee)
- Prepare store listing (screenshots, description, icon)
- Sign APK with release keystore
- Upload to Play Console
- Submit for review (typically 1-3 days)

**3. Internal Testing:**
- Google Play Internal Testing track
- Firebase App Distribution
- TestFlight (for future iOS version)

## 6.4 System Maintenance

### Table 6.2: Maintenance Schedule

| Task | Frequency | Responsibility |
|------|-----------|----------------|
| Database Backup | Daily | System Admin |
| Log Review | Weekly | Developer |
| Security Updates | Monthly | Developer |
| Performance Monitoring | Continuous | System Admin |
| Bug Fixes | As needed | Developer |
| Feature Updates | Quarterly | Development Team |
| User Feedback Review | Weekly | Product Manager |
| Server Maintenance | Monthly | System Admin |

### 6.4.1 Database Backup

**Backup Script:**
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/opt/lampp/htdocs/reference/backups"
DB_NAME="smartmall_db"

mkdir -p $BACKUP_DIR

/opt/lampp/bin/mysqldump -u root $DB_NAME > \
  $BACKUP_DIR/smartmall_$DATE.sql

# Keep only last 30 days
find $BACKUP_DIR -name "smartmall_*.sql" -mtime +30 -delete

echo "Backup completed: smartmall_$DATE.sql"
```

**Restore Database:**
```bash
/opt/lampp/bin/mysql -u root smartmall_db < backup_file.sql
```

### 6.4.2 System Updates

**PHP Updates:**
```bash
# Check current version
php -v

# Update PHP (Ubuntu)
sudo apt update
sudo apt upgrade php
```

**MySQL Updates:**
```bash
# Check current version
mysql --version

# Update MySQL
sudo apt update
sudo apt upgrade mysql-server
```

**Flutter Updates:**
```bash
# Check current version
flutter --version

# Update Flutter
flutter upgrade
```

### 6.4.3 Log Monitoring

**Apache Error Log:**
```bash
tail -f /opt/lampp/logs/error_log
```

**MySQL Error Log:**
```bash
tail -f /opt/lampp/var/mysql/*.err
```

**PHP Error Log:**
```bash
tail -f /opt/lampp/logs/php_error_log
```

### 6.4.4 Performance Optimization

**Database Optimization:**
```sql
-- Optimize tables
OPTIMIZE TABLE products, orders, order_items;

-- Analyze tables
ANALYZE TABLE products, orders, order_items;

-- Check indexes
SHOW INDEX FROM products;
```

**Cache Configuration:**
```php
// Enable OPcache in php.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
```

## 6.5 Future Enhancements

### 6.5.1 Planned Features

**Phase 1 (Next 3 months):**
- Product reviews and ratings
- Wishlist functionality
- Email notifications for orders
- Advanced product search with filters
- Product recommendations

**Phase 2 (Next 6 months):**
- Multi-vendor support
- Live chat customer support
- Loyalty points program
- Gift cards and coupons
- Social media integration

**Phase 3 (Next 12 months):**
- iOS mobile app
- Progressive Web App (PWA)
- AI-powered product recommendations
- Augmented Reality product preview
- Multi-language support

### 6.5.2 Scalability Considerations

**Database Scaling:**
- Implement database replication (master-slave)
- Add database connection pooling
- Implement query caching
- Consider NoSQL for product catalog

**Application Scaling:**
- Implement load balancing
- Use CDN for static assets
- Implement Redis for session storage
- Add application-level caching

**Infrastructure Scaling:**
- Move to cloud hosting (AWS, Azure, GCP)
- Implement auto-scaling
- Add monitoring and alerting
- Implement CI/CD pipeline

### 6.5.3 Security Enhancements

**Planned Security Improvements:**
- Implement two-factor authentication (2FA)
- Add CAPTCHA to prevent bots
- Implement rate limiting on API endpoints
- Add Web Application Firewall (WAF)
- Implement Content Security Policy (CSP)
- Add SSL/TLS certificates (HTTPS)
- Implement security headers
- Regular security audits and penetration testing

---

**End of Chapter 6**
