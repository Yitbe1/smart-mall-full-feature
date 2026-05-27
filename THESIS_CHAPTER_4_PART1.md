# CHAPTER 4: SYSTEM IMPLEMENTATION

## 4.1 Technology Stack

The Smart Mall system is built using a carefully selected technology stack that balances functionality, performance, and ease of development.

### Table 4.1: Complete Technology Stack

| Layer | Technology | Version | Purpose |
|-------|------------|---------|---------|
| **Frontend (Web)** | HTML5 | - | Structure and markup |
| | CSS3 | - | Styling and layout |
| | JavaScript | ES6+ | Client-side interactivity |
| | Bootstrap | 5.x | Responsive framework |
| | jQuery | 3.x | DOM manipulation |
| **Backend** | PHP | 7.4+ | Server-side logic |
| | Apache | 2.4+ | Web server |
| | XAMPP/LAMPP | Latest | Development environment |
| **Database** | MySQL | 8.0+ | Relational database |
| | phpMyAdmin | Latest | Database management |
| **Mobile** | Flutter | 3.0+ | Cross-platform framework |
| | Dart | 3.0+ | Programming language |
| | Material Design | 3 | UI components |
| | Provider | 6.1+ | State management |
| **APIs & Libraries** | HTTP | 1.1+ | API communication |
| | Cached Network Image | 3.3+ | Image caching |
| | Google Fonts | 6.1+ | Typography |
| | Intl | 0.19+ | Internationalization |
| **Payment** | Chapa | Latest | Payment gateway |
| **Version Control** | Git | Latest | Source control |
| **Deployment** | XAMPP/LAMPP | Latest | Local server |

### Table 4.2: Frontend Technologies Detail

| Technology | Purpose | Key Features Used |
|------------|---------|-------------------|
| HTML5 | Document structure | Semantic tags, forms, validation |
| CSS3 | Styling | Flexbox, Grid, animations, transitions |
| JavaScript | Interactivity | Event handling, AJAX, DOM manipulation |
| Bootstrap 5 | UI Framework | Grid system, components, utilities |
| jQuery | DOM Library | AJAX requests, event handling |

### Table 4.3: Backend Technologies Detail

| Technology | Purpose | Key Features Used |
|------------|---------|-------------------|
| PHP 7.4+ | Server logic | Sessions, PDO, password hashing |
| MySQL 8.0 | Database | Transactions, foreign keys, indexes |
| Apache 2.4 | Web server | mod_rewrite, .htaccess |

## 4.2 Frontend Implementation

### 4.2.1 Responsive Design

The web interface implements responsive design using Bootstrap 5 framework and custom CSS.

**Breakpoints:**
```css
/* Mobile First Approach */
/* Extra small devices (phones, less than 576px) */
@media (max-width: 575.98px) {
    .product-grid { grid-template-columns: 1fr; }
}

/* Small devices (landscape phones, 576px and up) */
@media (min-width: 576px) {
    .product-grid { grid-template-columns: repeat(2, 1fr); }
}

/* Medium devices (tablets, 768px and up) */
@media (min-width: 768px) {
    .product-grid { grid-template-columns: repeat(3, 1fr); }
}

/* Large devices (desktops, 992px and up) */
@media (min-width: 992px) {
    .product-grid { grid-template-columns: repeat(4, 1fr); }
}
```

**Responsive Grid Implementation:**
```html
<div class="container">
    <div class="row">
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <!-- Product Card -->
        </div>
    </div>
</div>
```

### 4.2.2 Navigation Bar Implementation

**HTML Structure:**
```html
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="assets/images/logo.png" alt="Smart Mall" height="40">
        </a>
        
        <button class="navbar-toggler" type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="products.php">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        Cart <span class="badge bg-primary">3</span>
                    </a>
                </li>
                <?php if(isset($_SESSION['user_id'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="orders.php">Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Login</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
```

**CSS Styling:**
```css
.navbar {
    padding: 1rem 0;
    transition: all 0.3s ease;
}

.navbar-brand img {
    transition: transform 0.3s ease;
}

.navbar-brand:hover img {
    transform: scale(1.05);
}

.nav-link {
    font-weight: 500;
    padding: 0.5rem 1rem;
    transition: color 0.3s ease;
}

.nav-link:hover {
    color: #2563EB;
}

.badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}
```

### 4.2.3 Product Card Implementation

**HTML Structure:**
```html
<div class="product-card">
    <div class="product-image">
        <img src="<?php echo htmlspecialchars($product['image']); ?>" 
             alt="<?php echo htmlspecialchars($product['name']); ?>">
        <span class="category-badge">
            <?php echo htmlspecialchars($product['category_name']); ?>
        </span>
    </div>
    <div class="product-info">
        <h5 class="product-name">
            <?php echo htmlspecialchars($product['name']); ?>
        </h5>
        <p class="product-price">
            $<?php echo number_format($product['price'], 2); ?>
        </p>
        <form method="POST" action="add_to_cart.php">
            <input type="hidden" name="product_id" 
                   value="<?php echo $product['id']; ?>">
            <button type="submit" class="btn btn-primary w-100">
                Add to Cart
            </button>
        </form>
    </div>
</div>
```

**CSS Styling:**
```css
.product-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(37,99,235,0.2);
}

.product-image {
    position: relative;
    padding-top: 100%;
    overflow: hidden;
}

.product-image img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.category-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: rgba(37,99,235,0.9);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.product-info {
    padding: 1rem;
}

.product-name {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: #2563EB;
    margin-bottom: 1rem;
}
```

### 4.2.4 Shopping Cart UI Implementation

**Cart Display:**
```php
<?php
session_start();
require_once 'includes/db.php';

$pdo = getDB();
$cart_items = [];
$total = 0;

if(isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.price, p.image 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
}
?>

<div class="cart-container">
    <?php if(empty($cart_items)): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart fa-5x text-muted"></i>
            <h3>Your cart is empty</h3>
            <a href="index.php" class="btn btn-primary">
                Continue Shopping
            </a>
        </div>
    <?php else: ?>
        <div class="cart-items">
            <?php foreach($cart_items as $item): ?>
            <div class="cart-item">
                <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                <div class="item-details">
                    <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                    <p class="price">
                        $<?php echo number_format($item['price'], 2); ?>
                    </p>
                </div>
                <div class="quantity-controls">
                    <button class="btn btn-sm btn-outline-secondary" 
                            onclick="updateQuantity(<?php echo $item['id']; ?>, -1)">
                        -
                    </button>
                    <span class="quantity"><?php echo $item['quantity']; ?></span>
                    <button class="btn btn-sm btn-outline-secondary" 
                            onclick="updateQuantity(<?php echo $item['id']; ?>, 1)">
                        +
                    </button>
                </div>
                <div class="item-total">
                    $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                </div>
                <button class="btn btn-sm btn-danger" 
                        onclick="removeItem(<?php echo $item['id']; ?>)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="cart-summary">
            <h4>Order Summary</h4>
            <div class="summary-row">
                <span>Subtotal:</span>
                <span>$<?php echo number_format($total, 2); ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping:</span>
                <span>Free</span>
            </div>
            <div class="summary-row total">
                <span>Total:</span>
                <span>$<?php echo number_format($total, 2); ?></span>
            </div>
            <a href="checkout.php" class="btn btn-primary w-100">
                Proceed to Checkout
            </a>
        </div>
    <?php endif; ?>
</div>
```

**JavaScript for Cart Updates:**
```javascript
function updateQuantity(cartId, change) {
    fetch('update_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            cart_id: cartId,
            change: change
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    });
}

function removeItem(cartId) {
    if(confirm('Remove this item from cart?')) {
        fetch('remove_from_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cart_id: cartId
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            }
        });
    }
}
```

### 4.2.5 Checkout UI Implementation

**Checkout Form:**
```html
<form method="POST" action="process_checkout.php" id="checkoutForm">
    <div class="checkout-container">
        <div class="checkout-form">
            <h3>Shipping Information</h3>
            
            <div class="mb-3">
                <label for="fullName" class="form-label">Full Name *</label>
                <input type="text" class="form-control" id="fullName" 
                       name="full_name" required>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email *</label>
                <input type="email" class="form-control" id="email" 
                       name="email" required>
            </div>
            
            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number *</label>
                <input type="tel" class="form-control" id="phone" 
                       name="phone" required>
            </div>
            
            <div class="mb-3">
                <label for="address" class="form-label">
                    Shipping Address *
                </label>
                <textarea class="form-control" id="address" 
                          name="address" rows="3" required></textarea>
            </div>
            
            <div class="mb-3">
                <label for="city" class="form-label">City *</label>
                <input type="text" class="form-control" id="city" 
                       name="city" required>
            </div>
            
            <h3 class="mt-4">Payment Method</h3>
            <div class="payment-methods">
                <div class="form-check">
                    <input class="form-check-input" type="radio" 
                           name="payment_method" id="chapa" 
                           value="chapa" checked>
                    <label class="form-check-label" for="chapa">
                        <img src="assets/images/chapa-logo.png" 
                             alt="Chapa" height="30">
                        Chapa Payment
                    </label>
                </div>
            </div>
        </div>
        
        <div class="order-summary-sidebar">
            <h3>Order Summary</h3>
            <?php foreach($cart_items as $item): ?>
            <div class="summary-item">
                <span><?php echo htmlspecialchars($item['name']); ?> 
                      × <?php echo $item['quantity']; ?></span>
                <span>$<?php echo number_format(
                    $item['price'] * $item['quantity'], 2
                ); ?></span>
            </div>
            <?php endforeach; ?>
            
            <hr>
            
            <div class="summary-total">
                <span>Total:</span>
                <span>$<?php echo number_format($total, 2); ?></span>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 mt-3">
                Place Order
            </button>
        </div>
    </div>
</form>
```

**Form Validation:**
```javascript
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate form
    if(!this.checkValidity()) {
        this.classList.add('was-validated');
        return;
    }
    
    // Show loading
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
    
    // Submit form
    this.submit();
});
```

---

**Continue to Chapter 4 Part 2...**
