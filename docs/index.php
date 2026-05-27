<?php
require_once __DIR__ . '/../config.php';
$page_title = 'Documentation - Smart Mall';
include __DIR__ . '/../includes/header.php';
?>
<style>
    .docs-hero {
        text-align: center;
        padding: 4rem 0 2rem;
    }

    .docs-hero h1 {
        font-family: 'Outfit', sans-serif;
        font-size: clamp(2.5rem, 6vw, 4rem);
        font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--secondary-color);
        margin-bottom: 1rem;
    }

    .docs-hero p {
        max-width: 600px;
        margin: 0 auto 2rem;
        color: var(--text-light);
        font-size: 1.15rem;
        line-height: 1.7;
    }

    .docs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 3rem;
    }

    .doc-card {
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: 24px;
        padding: 2.5rem;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
    }

    .doc-card:hover {
        transform: translateY(-6px);
        box-shadow: var(--shadow-xl);
        border-color: var(--primary-color);
    }

    .doc-card-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        font-size: 1.5rem;
    }

    .doc-card h2 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--secondary-color);
        margin-bottom: 0.75rem;
    }

    .doc-card p {
        color: var(--text-light);
        font-size: 0.95rem;
        line-height: 1.6;
        flex: 1;
    }

    .doc-card .doc-topics {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-color);
    }

    .doc-card .doc-topics span {
        display: inline-block;
        padding: 0.3rem 0.75rem;
        margin: 0.2rem;
        background: var(--bg-light);
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-light);
    }

    .doc-card .card-arrow {
        margin-top: 1.5rem;
        color: var(--primary-color);
        font-weight: 700;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .doc-card:hover .card-arrow svg {
        transform: translateX(4px);
    }

    .card-arrow svg {
        transition: transform 0.3s ease;
    }

    .icon-user { background: #eff6ff; color: #2563eb; }
    .icon-admin { background: #fef2f2; color: #dc2626; }
    .icon-dev { background: #f0fdf4; color: #16a34a; }

    .download-section {
        margin-top: 4rem;
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: 24px;
        padding: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2rem;
    }

    .download-section h3 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.25rem;
        color: var(--secondary-color);
        margin-bottom: 0.5rem;
    }

    .download-section p {
        color: var(--text-light);
        font-size: 0.95rem;
    }

    @media (max-width: 640px) {
        .download-section {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<div class="container">
    <div class="docs-hero">
        <h1>Documentation</h1>
        <p>Everything you need to use, manage, and develop the Smart Mall platform.</p>
    </div>

    <div class="docs-grid">
        <a href="user-guide.php" class="doc-card">
            <div class="doc-card-icon icon-user">
                <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <h2>User Guide</h2>
            <p>Learn how to browse products, manage your cart, checkout, track orders, and use the mobile app.</p>
            <div class="doc-topics">
                <span>Getting Started</span>
                <span>Shopping</span>
                <span>Checkout</span>
                <span>Orders</span>
                <span>Mobile App</span>
            </div>
            <div class="card-arrow">
                Read Guide <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14m0 0l-6-6m6 6l-6 6"/></svg>
            </div>
        </a>

        <a href="admin-guide.php" class="doc-card">
            <div class="doc-card-icon icon-admin">
                <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h2>Admin Guide</h2>
            <p>Manage products, categories, orders, payments, and system maintenance through the admin panel.</p>
            <div class="doc-topics">
                <span>Dashboard</span>
                <span>Products</span>
                <span>Categories</span>
                <span>Orders</span>
                <span>Payments</span>
            </div>
            <div class="card-arrow">
                Read Guide <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14m0 0l-6-6m6 6l-6 6"/></svg>
            </div>
        </a>

        <a href="full-documentation.php" class="doc-card">
            <div class="doc-card-icon icon-dev">
                <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h2>Full Documentation</h2>
            <p>Complete project documentation covering abstract, architecture, features, database, security, deployment, and usage guides.</p>
            <div class="doc-topics">
                <span>Architecture</span>
                <span>Database</span>
                <span>Security</span>
                <span>Deployment</span>
                <span>ER Diagram</span>
            </div>
            <div class="card-arrow">
                Read Guide <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14m0 0l-6-6m6 6l-6 6"/></svg>
            </div>
        </a>

        <a href="developer-guide.php" class="doc-card">
            <div class="doc-card-icon icon-dev">
                <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                </svg>
            </div>
            <h2>Developer Guide</h2>
            <p>Architecture, setup, database schema, API reference, Flutter mobile app, and design system.</p>
            <div class="doc-topics">
                <span>Architecture</span>
                <span>Setup</span>
                <span>Database Schema</span>
                <span>API Reference</span>
                <span>Security</span>
                <span>Payment</span>
                <span>Multi-Currency</span>
                <span>Flutter App</span>
                <span>Deployment</span>
            </div>
            <div class="card-arrow">
                Read Guide <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14m0 0l-6-6m6 6l-6 6"/></svg>
            </div>
        </a>
    </div>

    <div class="download-section">
        <div>
            <h3>Download Printable Version</h3>
            <p>Complete documentation in .docx format for offline reading and printing.</p>
        </div>
        <a href="Smart_Mall_Documentation.docx" class="btn-primary" style="white-space: nowrap; padding: 0.9rem 2rem;" download>Download .docx</a>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
