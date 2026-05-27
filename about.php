<?php
require_once __DIR__ . '/config.php';
$page_title = 'About Us - Smart Mall';

// Fetch real-time statistics
$stats = [
    'products' => 0,
    'orders' => 0,
    'customers' => 0,
    'categories' => 0
];

try {
    $pdo = getDB();
    $stats['products'] = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $stats['orders'] = $pdo->query("SELECT COUNT(*) FROM orders WHERE status != 'pending'")->fetchColumn();
    $stats['customers'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    $stats['categories'] = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
} catch (PDOException $e) {
    // Silently fail, keep zeros
}

require_once __DIR__ . '/includes/header.php';
?>

<style>
    .about-hero {
        padding: clamp(3rem, 8vw, 6rem) 0 4rem;
        background: radial-gradient(circle at top right, var(--primary-light), transparent 60%);
        text-align: center;
    }

    .about-hero h1 {
        font-size: clamp(2.5rem, 6vw, 4rem);
        margin-bottom: 1.5rem;
        color: var(--secondary-color);
    }

    .about-hero p {
        max-width: 700px;
        margin: 0 auto;
        font-size: 1.25rem;
        color: var(--text-light);
        line-height: 1.8;
    }

    .about-section {
        padding: 3rem 0;
    }

    .about-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .about-card {
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 2rem;
        box-shadow: var(--shadow-md);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .about-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .about-card-icon {
        width: 60px;
        height: 60px;
        background: var(--primary-light);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        color: var(--primary-color);
    }

    .about-card h3 {
        margin-bottom: 1rem;
        font-size: 1.5rem;
    }

    .about-card p {
        color: var(--text-light);
        line-height: 1.7;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1.5rem;
        margin: 2rem 0;
    }

    .stat-item {
        text-align: center;
        padding: 2rem 1rem;
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        box-shadow: var(--shadow-md);
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: var(--text-light);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 0.85rem;
    }

    @media (max-width: 768px) {
        .about-hero {
            padding: 2.5rem 0 2rem;
        }

        .about-hero h1 {
            font-size: 2rem;
        }

        .about-hero p {
            font-size: 1.05rem;
        }

        .about-section {
            padding: 2rem 0;
        }

        .about-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .stat-item {
            padding: 1.5rem 0.75rem;
        }

        .stat-number {
            font-size: 2rem;
        }
    }

    @media (max-width: 480px) {
        .about-hero {
            padding: 1.5rem 0 2rem;
        }

        .about-hero h1 {
            font-size: 1.6rem;
            margin-bottom: 1rem;
        }

        .about-hero p {
            font-size: 0.95rem;
        }

        .about-section {
            padding: 2rem 0;
        }

        .about-section h2 {
            font-size: 1.5rem !important;
        }

        .about-section p {
            font-size: 0.95rem !important;
        }

        .about-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .about-card {
            padding: 1.25rem;
        }

        .about-card-icon {
            width: 44px;
            height: 44px;
            margin-bottom: 1rem;
        }

        .about-card-icon svg {
            width: 24px;
            height: 24px;
        }

        .about-card h3 {
            font-size: 1.15rem;
            margin-bottom: 0.5rem;
        }

        .about-card p {
            font-size: 0.9rem;
        }

        .stats-grid {
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }

        .stat-item {
            padding: 1.25rem 0.75rem;
        }

        .stat-number {
            font-size: 1.6rem;
        }

        .stat-label {
            font-size: 0.75rem;
        }
    }
</style>

<div class="container">
    <div class="about-hero">
        <h1>About Smart Mall</h1>
        <p>We're redefining online shopping with a curated selection of premium products, secure transactions, and exceptional customer service.</p>
    </div>

    <div class="about-section">
        <h2 style="text-align: center; margin-bottom: 1rem; font-size: 2.5rem;">Our Mission</h2>
        <p style="text-align: center; max-width: 800px; margin: 0 auto 3rem; color: var(--text-light); font-size: 1.1rem; line-height: 1.8;">
            To bring global trends and quality products directly to your doorstep while maintaining the highest standards of security, authenticity, and customer satisfaction.
        </p>

        <div class="about-grid">
            <div class="about-card">
                <div class="about-card-icon">
                    <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <h3>Secure Shopping</h3>
                <p>Your security is our priority. We use industry-leading encryption and secure payment gateways to protect your data.</p>
            </div>

            <div class="about-card">
                <div class="about-card-icon">
                    <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                    </svg>
                </div>
                <h3>Quality Products</h3>
                <p>Every product is carefully curated and verified for authenticity, ensuring you receive only the best.</p>
            </div>

            <div class="about-card">
                <div class="about-card-icon">
                    <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3>Fast Delivery</h3>
                <p>Experience lightning-fast delivery with real-time tracking and reliable logistics partners.</p>
            </div>

            <div class="about-card">
                <div class="about-card-icon">
                    <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3>24/7 Support</h3>
                <p>Our dedicated support team is always ready to assist you with any questions or concerns.</p>
            </div>
        </div>
    </div>

    <div class="about-section">
        <h2 style="text-align: center; margin-bottom: 2rem; font-size: 2.5rem;">Our Progress</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number"><?php echo number_format($stats['products']); ?>+</div>
                <div class="stat-label">Products Listed</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo number_format($stats['orders']); ?>+</div>
                <div class="stat-label">Orders Delivered</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo number_format($stats['customers']); ?>+</div>
                <div class="stat-label">Happy Customers</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Customer Support</div>
            </div>
        </div>
    </div>

    <div class="about-section" style="background: var(--bg-light); margin: 0 -1rem; padding: 3rem 1rem; border-radius: var(--radius);">
        <h2 style="text-align: center; margin-bottom: 1.25rem; font-size: 2.5rem;">Our Story</h2>
        <div style="max-width: 800px; margin: 0 auto; color: var(--text-light); line-height: 1.8; font-size: 1.05rem;">
            <p style="margin-bottom: 1.5rem;">
                Launched in February 2026, Smart Mall was born from a simple observation: online shopping in Ethiopia needed a platform that prioritizes security, quality, and customer trust above all else.
            </p>
            <p style="margin-bottom: 1.5rem;">
                As a young startup, we're building something different. Every product is carefully verified, every transaction is secured with industry-standard encryption, and every customer interaction matters to us. We're not just selling products—we're building trust.
            </p>
            <p style="margin-bottom: 1.5rem;">
                <?php if ($stats['orders'] > 0): ?>
                    In just three months, we've successfully delivered <?php echo number_format($stats['orders']); ?>+ orders, built a catalog of <?php echo number_format($stats['products']); ?>+ quality products, and established partnerships with reliable suppliers.
                <?php else: ?>
                    In just three months, we've built a catalog of quality products and established partnerships with reliable suppliers.
                <?php endif; ?>
                Our focus remains on sustainable growth, exceptional service, and creating a shopping experience that Ethiopian customers can truly rely on.
            </p>
            <p>
                We're just getting started, and we're excited to grow with you. Thank you for being part of our journey from day one.
            </p>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>