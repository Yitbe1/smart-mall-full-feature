<?php
require_once __DIR__ . '/config.php';

// Smart Mall home page
$page_title = 'Home - Smart Mall';

$products       = [];
$error          = '';
$search_value   = trim($_GET['q'] ?? '');
$category_slug  = trim($_GET['category'] ?? '');
$sort_option    = trim($_GET['sort'] ?? 'newest');
$active_category = null;
$categories     = [];

try {
    $pdo = getDB();

    // Load categories from DB
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

    // Build category map by slug
    $cat_map = [];
    foreach ($categories as $cat) {
        $cat_map[$cat['slug']] = $cat;
    }
    $active_category = $cat_map[$category_slug] ?? null;

    // Category accent colors (matched by slug for styling)
    $cat_accents = [
        'fashion'     => '#ec4899',
        'electronics' => '#2563eb',
        'home'        => '#10b981',
        'beauty'      => '#f59e0b',
    ];

    $query = "
        SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE 1=1
    ";
    $params = [];

    if ($active_category) {
        $query .= " AND p.category_id = :cat_id";
        $params[':cat_id'] = $active_category['category_id'];
    }

    if ($search_value !== '') {
        $query .= " AND (p.name LIKE :query1 OR p.description LIKE :query2)";
        $params[':query1'] = '%' . $search_value . '%';
        $params[':query2'] = '%' . $search_value . '%';
    }

    if ($sort_option === 'price_asc') {
        $query .= " ORDER BY p.price ASC";
    } elseif ($sort_option === 'price_desc') {
        $query .= " ORDER BY p.price DESC";
    } else {
        $query .= " ORDER BY p.created_at DESC";
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Smart Mall index.php: " . $e->getMessage());
    $error = 'Could not load products. Please try again.';
}

include __DIR__ . '/includes/header.php';
?>

<style>
    .spotlight-card {
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: 24px;
        padding: 1.5rem 2rem;
        box-shadow: var(--shadow-lg);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 3rem;
        align-items: center;
        position: relative;
    }

    .spotlight-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-xl);
    }

    .spotlight-content {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        max-width: 700px;
    }

    .spotlight-logo {
        position: relative;
        width: 420px;
        height: 360px;
        border-radius: 20px;
        background: var(--bg-light);
        overflow: hidden;
        box-shadow: var(--shadow-sm) 0px 4px 12px;
        /* Subtle glow */
        animation: float 6s ease-in-out infinite, glow 3s ease-in-out infinite alternate;
    }

    .spotlight-logo::before,
    .spotlight-logo::after {
        content: '';
        position: absolute;
        top: 0;
        z-index: 2;
        width: 60px;
        height: 100%;
        pointer-events: none;
    }

    .spotlight-logo::before {
        left: 0;
        background: linear-gradient(to right, var(--bg-light), transparent);
    }

    .spotlight-logo::after {
        right: 0;
        background: linear-gradient(to left, var(--bg-light), transparent);
    }

    .products-slider {
        display: flex;
        flex-direction: column;
        width: 100%;
        height: 100%;
        will-change: transform;
    }

    .slider-item {
        min-width: 420px;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        text-decoration: none;
        position: relative;
    }

    .slider-item img {
        width: calc(100% - 3rem);
        height: calc(100% - 3rem);
        object-fit: contain;
        border-radius: 14px;
        filter: drop-shadow(0 4px 16px rgba(0, 0, 0, 0.06));
        transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .slider-item:hover img {
        transform: scale(1.06);
    }

    .slider-label {
        position: absolute;
        top: 0.75rem;
        left: 50%;
        transform: translateX(-50%);
        background: var(--primary-color);
        color: #fff;
        padding: 0.5rem 1.25rem;
        border-radius: 100px;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.14em;
        z-index: 10;
        pointer-events: none;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
    }

    .slider-dots {
        position: absolute;
        bottom: 1rem;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 6px;
        z-index: 10;
    }

    .slider-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.15);
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        cursor: pointer;
        border: 0;
        padding: 0;
    }

    .slider-dot.is-active {
        background: var(--primary-color);
        width: 20px;
        border-radius: 4px;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px) rotate(0deg);
        }

        50% {
            transform: translateY(-10px) rotate(2deg);
        }
    }

    @keyframes glow {
        0% {
            filter: drop-shadow(0 4px 20px rgba(69, 168, 170, 0.3));
        }

        100% {
            filter: drop-shadow(0 8px 30px rgba(69, 168, 170, 0.6));
        }
    }

    @media (max-width: 768px) {
        .spotlight-card {
            grid-template-columns: 1fr;
            padding: 2rem;
            gap: 2rem;
            text-align: center;
        }

        .spotlight-logo {
            width: 100%;
            max-width: 420px;
            height: 300px;
            margin: 0 auto;
        }

        .spotlight-content {
            align-items: center;
        }

        .spotlight-badge {
            align-self: center;
        }
    }

    @media (max-width: 600px) {
        .spotlight-card {
            grid-template-columns: 1fr;
            padding: 1.5rem;
            gap: 1.5rem;
        }

        .spotlight-content {
            align-items: center;
            text-align: center;
        }

        .spotlight-content .btn-primary,
        .spotlight-content .btn-secondary {
            width: 100%;
            text-align: center;
        }

        .spotlight-badge {
            align-self: center;
        }

        .spotlight-logo {
            width: 100%;
            max-width: 340px;
            height: 340px;
            margin: 0 auto;
        }

        .spotlight-logo::before,
        .spotlight-logo::after {
            width: 24px;
        }

        .slider-item {
            min-width: 0;
            width: 100%;
            height: 100%;
        }

        .slider-item img {
            width: calc(100% - 1.5rem);
            height: calc(100% - 1.5rem);
            border-radius: 10px;
        }

        .slider-label {
            font-size: 0.65rem;
            padding: 0.4rem 1rem;
        }

    }

    @media (max-width: 400px) {
        .spotlight-logo {
            max-width: 280px;
            height: 280px;
        }

        .slider-item {
            height: 100%;
        }

        .slider-item img {
            width: calc(100% - 1.2rem);
            height: calc(100% - 1.2rem);
            border-radius: 8px;
        }
    }

    .spotlight-badge {
        display: inline-flex;
        align-self: flex-start;
        padding: 0.5rem 1rem;
        background: var(--primary-light);
        color: var(--primary-color);
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        border-radius: 20px;
    }

    .spotlight-content h1 {
        font-size: clamp(2rem, 5vw, 3rem);
        margin: 0;
        line-height: 1.1;
    }

    .spotlight-content p {
        font-size: 1rem;
        line-height: 1.6;
        color: var(--text-light);
        margin: 0;
    }

    .spotlight-content .btn-primary {
        align-self: flex-start;
        padding: 1rem 2rem;
    }



    .hero-kicker {
        display: inline-flex;
        margin-bottom: 1.25rem;
        padding: 0.5rem 1rem;
        background: var(--primary-light);
        color: var(--primary-color);
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        border-radius: 20px;
    }

    .smart-hero h1 {
        max-width: 12ch;
        margin-bottom: 1.5rem;
        color: var(--secondary-color);
        font-family: 'Outfit', sans-serif;
        font-size: clamp(3.5rem, 9vw, 5.5rem);
        font-weight: 800;
        line-height: 0.95;
        letter-spacing: -0.02em;
    }

    .hero-lead {
        max-width: 44rem;
        margin-bottom: 2rem;
        color: var(--text-light);
        font-size: clamp(1.1rem, 2vw, 1.25rem);
    }

    .hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.9rem;
        align-items: center;
    }

    .hero-capabilities {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        margin-top: 3.5rem;
    }

    .capability-card {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        background: var(--surface);
        padding: 2.5rem;
        border-radius: 24px;
        border: 1px solid var(--border-color);
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: var(--shadow-sm);
    }

    .capability-card:hover {
        transform: translateY(-5px);
        border-color: var(--primary-color);
        box-shadow: var(--shadow-xl);
    }

    .capability-icon {
        width: 64px;
        height: 64px;
        background: var(--bg-light);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .capability-card:hover .capability-icon {
        background: var(--primary-light);
        transform: scale(1.1) rotate(5deg);
    }

    .capability-card strong {
        display: block;
        font-family: 'Outfit', sans-serif;
        color: var(--secondary-color);
        font-size: 1.25rem;
        font-weight: 800;
        margin-bottom: 0.4rem;
        letter-spacing: -0.01em;
    }

    .capability-card p {
        color: var(--text-light);
        font-size: 0.95rem;
        line-height: 1.5;
    }

    @media (max-width: 1024px) {
        .hero-capabilities {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .spotlight-card {
            padding: 2.5rem 2rem;
        }

        .spotlight-content .btn-primary,
        .spotlight-content .btn-secondary {
            padding: 1rem 2rem;
            width: 100%;
            text-align: center;
        }
    }

    @media (max-width: 480px) {
        .spotlight-card {
            padding: 1.5rem;
            gap: 1rem;
        }

        .spotlight-content {
            padding: 0;
        }

        .spotlight-content h1 {
            font-size: 1.5rem;
        }

        .spotlight-card>.spotlight-content p {
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 0;
        }

        .spotlight-content .btn-primary,
        .spotlight-content .btn-secondary {
            padding: 0.6rem 1rem;
            font-size: 0.85rem;
        }
    }

    .capability-icon {
        width: 48px;
        height: 48px;
        font-size: 1.4rem;
    }

    .smart-section {
        margin-top: 4rem;
    }

    .section-heading {
        text-align: center;
        margin: 6rem 0 3.5rem;
        position: relative;
    }

    .section-heading::before {
        content: 'EST. 2026';
        display: block;
        font-weight: 800;
        font-size: 0.75rem;
        letter-spacing: 0.3em;
        color: var(--primary-color);
        margin-bottom: 1rem;
    }

    .section-heading h2 {
        color: var(--secondary-color);
        font-family: 'Outfit', sans-serif;
        font-size: clamp(2.5rem, 6vw, 4.5rem);
        font-weight: 800;
        line-height: 0.95;
        letter-spacing: -0.04em;
        margin-bottom: 1.5rem;
    }

    .section-heading p {
        max-width: 40rem;
        margin: 0 auto;
        color: var(--text-light);
        font-size: 1.1rem;
        line-height: 1.6;
    }

    .heading-accent {
        width: 60px;
        height: 2px;
        background: var(--primary-color);
        margin: 2rem auto;
    }

    .category-grid,
    .products-grid,
    .trust-grid {
        display: grid;
    }

    .category-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.2rem;
        margin-top: 2rem;
    }

    .category-card {
        position: relative;
        height: 380px;
        border-radius: 30px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 1rem;
        text-decoration: none;
        border: 1px solid var(--border-color);
    }

    .category-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
        background-color: var(--bg-light);
    }

    .category-bg img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0;
        animation: auto-cycle 15s infinite;
    }

    .category-bg img:nth-child(1) {
        animation-delay: 0s;
    }

    .category-bg img:nth-child(2) {
        animation-delay: 5s;
    }

    .category-bg img:nth-child(3) {
        animation-delay: 10s;
    }

    @keyframes auto-cycle {
        0% {
            opacity: 0;
            transform: scale(1);
        }

        5% {
            opacity: 1;
        }

        33% {
            opacity: 1;
            transform: scale(1.05);
        }

        /* Subtle zoom */
        38% {
            opacity: 0;
            transform: scale(1.1);
        }

        100% {
            opacity: 0;
            transform: scale(1);
        }
    }

    .category-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, transparent 70%, var(--bg-light)) 10%;
        z-index: 2;
    }

    .category-content {
        position: relative;
        z-index: 3;
        color: var(--secondary-color);
        transform: translateY(10px);
        transition: transform 0.5s ease;

    }

    .category-card:hover .category-bg {
        transform: scale(1.1);
    }

    .category-card:hover .category-content {
        transform: translateY(0);
    }

    .category-card h3 {
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--secondary-color);
        margin-bottom: 0.1rem;
        letter-spacing: -0.04em;
        text-shadow: var(--text-focus-shadow);
        -webkit-text-stroke: 1.5px var(--text-stroke-color);
        paint-order: stroke fill;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        text-rendering: optimizeLegibility;
        will-change: transform;

    }

    .category-card p {
        font-size: 1rem;
        color: var(--secondary-color);
        line-height: 1.5;
        font-weight: 800;
        opacity: 0;
        transition: opacity 0.5s ease;
        margin-bottom: 0rem;
        text-shadow: var(--text-focus-shadow);
        -webkit-text-stroke: 0.1px var(--text-stroke-color);
        paint-order: stroke fill;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        text-rendering: optimizeLegibility;
        will-change: transform;
    }

    .category-card:hover p {
        opacity: 2;
    }

    .category-card .category-mark {
        position: absolute;
        top: 1.5rem;
        right: 1.5rem;
        width: 44px;
        height: 44px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 5;
        color: var(--secondary-color);
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .category-card:hover .category-mark {
        background: var(--category-accent);
        transform: rotate(45deg);
    }

    .category-card.is-active {
        border: 4px solid var(--category-accent);
        box-shadow: 0 0 40px rgba(0, 0, 0, 0.3);
    }

    .category-card.is-active .category-mark {
        background: var(--category-accent);
        transform: rotate(45deg);
    }

    .trust-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0;
        margin: 5rem 0;
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: 32px;
        overflow: hidden;
        box-shadow: var(--shadow-lg);
    }

    .trust-item {
        padding: 3.5rem 2rem;
        text-align: center;
        border-right: 1px solid var(--border-color);
        transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
    }

    .trust-item:last-child {
        border-right: 0;
    }

    .trust-item:hover {
        background: var(--bg-light);
    }

    .trust-item::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        width: 0;
        height: 3px;
        background: var(--primary-color);
        transition: all 0.5s ease;
        transform: translateX(-50%);
    }

    .trust-item:hover::after {
        width: 100%;
    }

    .trust-item strong {
        display: block;
        font-family: 'Outfit', sans-serif;
        color: var(--secondary-color);
        font-size: 1.4rem;
        font-weight: 800;
        margin-bottom: 0.75rem;
        letter-spacing: -0.02em;
    }

    .trust-item span {
        display: block;
        color: var(--text-light);
        font-size: 0.9rem;
        line-height: 1.5;
        max-width: 180px;
        margin: 0 auto;
    }

    .trust-icon {
        font-size: 2.5rem;
        margin-bottom: 1.5rem;
        display: block;
        opacity: 0.8;
    }

    /* Featured Spotlight */
    .featured-spotlight {
        margin: 6rem 0;
        background: #0f172a;
        /* Consistent dark background for editorial impact */
        border-radius: 40px;
        overflow: hidden;
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        min-height: 600px;
        color: #fff;
        box-shadow: var(--shadow-xl);
    }

    [data-theme='dark'] .featured-spotlight {
        background: #020617;
        border: 1px solid var(--border-color);
    }

    .spotlight-content {
        padding: 5rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .spotlight-content span {
        color: var(--primary-color);
        font-weight: 800;
        letter-spacing: 0.3em;
        text-transform: uppercase;
        font-size: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .spotlight-content h2 {
        font-size: 4rem;
        line-height: 0.95;
        margin-bottom: 2rem;
        color: #fff;
        font-family: 'Outfit', sans-serif;
    }

    .spotlight-content p {
        font-size: 1.1rem;
        color: var(--text-light);
        margin-bottom: 3rem;
        line-height: 1.7;
        max-width: 400px;
    }

    .spotlight-image {
        position: relative;
        overflow: hidden;
    }

    .spotlight-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 1s ease;
    }

    .featured-spotlight:hover .spotlight-image img {
        transform: scale(1.05);
    }

    /* Philosophy Section */
    .philosophy-section {
        padding: 8rem 0;
        text-align: center;
        background: radial-gradient(circle at bottom, var(--primary-light), transparent 70%);
    }

    .philosophy-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .philosophy-container span {
        display: block;
        font-weight: 800;
        font-size: 0.75rem;
        letter-spacing: 0.3em;
        color: var(--primary-color);
        text-transform: uppercase;
        margin-bottom: 1.5rem;
    }

    .philosophy-container h3 {
        font-size: clamp(2rem, 5vw, 3.5rem);
        margin-bottom: 2rem;
        letter-spacing: -0.04em;
        color: var(--text-dark);
        font-family: 'Outfit', sans-serif;
    }

    .philosophy-container p {
        font-size: 1.25rem;
        line-height: 1.8;
        color: var(--text-light);
    }

    @media (max-width: 1024px) {
        .featured-spotlight {
            grid-template-columns: 1fr;
        }

        .spotlight-content {
            padding: 3rem 2rem;
        }

        .spotlight-image {
            height: 300px;
        }
    }

    @media (max-width: 480px) {
        .featured-spotlight {
            border-radius: 20px;
            margin: 2rem 0;
        }

        .featured-spotlight .spotlight-content {
            padding: 1.5rem;
        }

        .featured-spotlight .spotlight-content span {
            margin-bottom: 0.5rem;
        }

        .featured-spotlight .spotlight-content p {
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 0.75rem;
        }

        .featured-spotlight .spotlight-content h2 {
            font-size: 1.6rem;
            margin-bottom: 0.75rem;
            line-height: 1.1;
        }

        .featured-spotlight .btn-primary {
            padding: 0.6rem 1.25rem !important;
            font-size: 0.85rem;
        }

        .featured-spotlight .spotlight-image {
            height: 240px;
        }
    }

    .spotlight-card>.spotlight-content {
        padding: 0;
    }

    .spotlight-card>.spotlight-content span {
        margin-bottom: 0;
    }

    .spotlight-card>.spotlight-content p {
        margin-bottom: 0;
    }

    .products-toolbar {
        display: flex;
        justify-content: center;
        margin-bottom: 4rem;
    }

    .toolbar-container {
        display: flex;
        align-items: center;
        gap: 1rem;
        background: var(--surface);
        padding: 0.75rem 1rem;
        border-radius: 100px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-lg);
        max-width: 900px;
        width: 100%;
        backdrop-filter: blur(10px);
    }

    .toolbar-search {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding-left: 1rem;
    }

    .toolbar-search input {
        width: 100%;
        border: 0;
        outline: 0;
        background: transparent;
        color: var(--text-dark);
        font-size: 1rem;
        font-family: inherit;
    }

    .toolbar-sort {
        border-left: 1px solid var(--border-color);
        padding-left: 1rem;
    }

    .toolbar-sort select {
        border: 0;
        outline: 0;
        background: transparent;
        color: var(--primary-color);
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        padding-right: 1rem;
    }

    .btn-apply {
        background: var(--primary-color);
        color: #fff;
        padding: 0.75rem 2rem;
        border-radius: 100px;
        border: 0;
        font-weight: 800;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .btn-apply:hover {
        transform: scale(1.05);
        background: var(--primary-dark);
    }

    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1.25rem;
    }

    /* Interactive Product Card Enhancements */
    .product-card {
        transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), box-shadow 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
        position: relative;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .product-card>a {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .product-card .product-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        padding: 0.75rem 1rem;
    }

    .product-card .product-info[style*="padding-bottom"] {
        padding: 0.5rem 1rem 0.75rem !important;
    }

    .product-card .product-info[style*="padding-bottom"] .product-actions {
        gap: 0.6rem;
    }

    .product-description {
        -webkit-line-clamp: 1;
        margin: 0.3rem 0 0.5rem;
    }

    .product-price {
        font-size: 1.1rem;
    }

    .product-card:hover {
        transform: translateY(-8px) scale(1.02) !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
        z-index: 10;
        border-color: var(--primary-light) !important;
    }

    .product-image {
        position: relative;
        aspect-ratio: 1;
        width: 100%;
        overflow: hidden;
        background-color: var(--bg-light);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .product-card:hover .product-image img {
        transform: translateX(10px) scale(1.05);
    }

    .product-actions {
        opacity: 0.85;
        transition: opacity 0.3s ease;
    }

    .product-card:hover .product-actions {
        opacity: 1;
    }

    .product-price {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--primary-color);
        margin-top: auto;
    }

    .product-category {
        color: var(--primary-color);
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 0.25rem;
    }

    .product-description {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin: 0.5rem 0 1rem;
        color: var(--text-light);
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .empty-state {
        padding: 3rem;
        text-align: center;
        border: 1px dashed var(--border-color);
        background: var(--surface);
        box-shadow: var(--shadow);
    }

    @media (max-width: 980px) {

        .smart-hero,
        .section-heading {
            display: block;
        }

        .hero-panel {
            margin-top: 2rem;
        }

        .category-grid,
        .trust-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .trust-item:nth-child(2) {
            border-right: 0;
        }

        .trust-item:nth-child(-n + 2) {
            border-bottom: 1px solid var(--border-color);
        }
    }

    @media (max-width: 640px) {
        .smart-hero h1 {
            font-size: clamp(2.6rem, 12vw, 4rem);
        }

        .category-grid,
        .products-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }

        .product-description {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            margin-bottom: 0.5rem;
        }

        .product-category {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 0.7rem;
        }

        .product-info {
            padding: 0.85rem !important;
        }

        .product-name {
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            white-space: normal;
            overflow: hidden;
        }

        .category-card {
            height: 260px;
        }

        .trust-grid,
        .hero-metrics {
            grid-template-columns: 1fr;
        }

        .hero-metric,
        .trust-item,
        .trust-item:nth-child(2) {
            border-right: 0;
        }

        .products-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .toolbar-container {
            flex-direction: column;
            border-radius: 20px;
            padding: 0.75rem;
            gap: 0.5rem;
        }

        .toolbar-search {
            padding-left: 0.5rem;
        }

        .toolbar-sort {
            border-left: none;
            border-top: 1px solid var(--border-color);
            padding-left: 0;
            padding-top: 0.5rem;
        }

        .btn-apply {
            width: 100%;
        }

        .section-heading h2 {
            font-size: clamp(2rem, 8vw, 3rem);
        }
    }

    @media (max-width: 480px) {
        .category-grid {
            grid-template-columns: 1fr;
        }

        .category-card {
            height: 220px;
        }

        .trust-grid {
            border-radius: 20px;
        }

        .trust-item {
            padding: 2rem 1.5rem;
        }

        .product-actions {
            gap: 0.375rem !important;
        }

        .btn-buy {
            padding: 0.5rem 0.6rem !important;
            font-size: 0.625rem !important;
            gap: 4px;
            border-radius: 6px !important;
            overflow: hidden;
        }

        .btn-buy svg {
            width: 14px;
            height: 14px;
        }

        .btn-cart {
            width: 2rem !important;
            height: 2rem !important;
            flex-shrink: 0 !important;
            border-radius: 6px !important;
        }

        .btn-cart svg {
            width: 16px;
            height: 16px;
        }

        .product-price {
            font-size: 0.9rem;
            white-space: nowrap;
        }

        .product-stock {
            font-size: 0.7rem;
            margin-top: 0.1rem;
        }

        .product-actions .btn-cart[disabled] {
            width: 100% !important;
            height: auto !important;
            padding: 0.5rem 0.6rem !important;
            font-size: 0.75rem !important;
            opacity: 0.6;
            cursor: not-allowed;
            border-radius: 6px;
        }

        .trust-grid {
            margin: 2rem 0;
            border-radius: 16px;
        }

        .trust-item {
            padding: 1.5rem 1rem;
        }

        .trust-icon {
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
        }

        .trust-icon svg {
            width: 24px;
            height: 24px;
        }

        .trust-item strong {
            font-size: 1rem;
            margin-bottom: 0.35rem;
        }

        .trust-item span {
            font-size: 0.8rem;
            max-width: 100%;
        }

        .philosophy-section {
            padding: 3rem 0;
        }

        .philosophy-container {
            padding: 0 1rem;
        }

        .philosophy-container span {
            font-size: 0.7rem;
            margin-bottom: 1rem;
        }

        .philosophy-container h3 {
            font-size: 1.75rem;
            margin-bottom: 1.25rem;
        }

        .philosophy-container p {
            font-size: 1rem;
            line-height: 1.6;
        }

        .philosophy-container .heading-accent {
            margin-top: 2rem !important;
        }

        .product-name {
            font-size: 0.8rem;
            margin-bottom: 0.15rem !important;
        }

        .product-info {
            padding: 0.4rem !important;
        }

        .product-image {
            height: 130px;
        }

        .product-image img {
            object-fit: contain;
            padding: 0.25rem;
        }

        .product-card .product-info[style*="padding-bottom"] {
            padding-bottom: 0.35rem !important;
        }

        .product-description {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: 0.8rem;
            margin: 0.35rem 0 0.6rem;
        }

        .toolbar-container {
            padding: 0.375rem;
            gap: 0.25rem;
        }

        .toolbar-search {
            font-size: 0.8rem;
        }

        .toolbar-sort {
            padding-top: 0.15rem;
        }

        .toolbar-search input {
            font-size: 0.8rem;
        }

        .toolbar-sort select {
            font-size: 0.75rem;
        }

        .btn-apply {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .admin-overlay {
            top: 0.35rem;
            left: 0.35rem;
            right: 0.35rem;
        }

        .admin-badge {
            padding: 0.2rem 0.45rem;
            font-size: 0.6rem;
            gap: 0.25rem;
        }

        .admin-badge div {
            width: 6px !important;
            height: 6px !important;
        }

        .admin-edit-btn {
            width: 24px !important;
            height: 24px !important;
            border-radius: 6px !important;
        }

        .admin-edit-btn svg {
            width: 12px;
            height: 12px;
        }
    }

    /* Admin View Styles */
    .admin-console {
        position: fixed;
        left: 2rem;
        top: 50%;
        transform: translateY(-50%);
        width: 64px;
        background: rgba(15, 23, 42, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 32px;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 1.5rem 0;
        gap: 1.5rem;
        z-index: 9999;
        box-shadow: var(--shadow-xl);
        transition: width 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        overflow: hidden;
    }

    @media (max-width: 1100px) {
        .admin-console {
            left: 0.5rem;
        }
    }

    @media (max-width: 768px) {
        .admin-console {
            display: none;
        }
    }

    .admin-console:hover {
        width: 200px;
        align-items: flex-start;
        padding: 1.5rem;
    }

    .admin-console-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
        width: 100%;
        justify-content: center;
    }

    .admin-console:hover .admin-console-header {
        justify-content: flex-start;
    }

    .admin-console-header span {
        display: none;
        font-weight: 800;
        font-size: 0.7rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .admin-console:hover .admin-console-header span {
        display: block;
    }

    .admin-bar-link {
        color: #94a3b8;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s ease;
        width: 100%;
        justify-content: center;
    }

    .admin-console:hover .admin-bar-link {
        justify-content: flex-start;
    }

    .admin-bar-link span {
        display: none;
        font-size: 0.85rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .admin-console:hover .admin-bar-link span {
        display: block;
    }

    .admin-bar-link:hover {
        color: #fff;
        transform: translateX(5px);
    }

    .admin-bar-link svg {
        flex-shrink: 0;
    }

    /* Resetting header for Admin */
    .site-nav {
        top: 1.5rem !important;
    }

    .smart-hero {
        padding-top: 6rem !important;
    }

    .admin-overlay {
        position: absolute;
        top: 0.75rem;
        left: 0.75rem;
        right: 0.75rem;
        z-index: 10;
        display: flex;
        justify-content: space-between;
        pointer-events: none;
    }

    .admin-badge {
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(8px);
        color: #fff;
        padding: 0.4rem 0.75rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 800;
        border: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        gap: 0.4rem;
        pointer-events: auto;
    }

    .admin-edit-btn {
        background: var(--primary-color);
        color: #fff;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: grid;
        place-items: center;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        transition: all 0.3s ease;
        pointer-events: auto;
    }

    .admin-edit-btn:hover {
        background: var(--primary-dark);
        transform: scale(1.1);
    }
</style>

<?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
    <div class="admin-console">
        <div class="admin-console-header">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
            </svg>
            <span>Pro Admin</span>
        </div>

        <a href="admin/dashboard.php" class="admin-bar-link" title="Dashboard">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
            </svg>
            <span>Dashboard</span>
        </a>

        <a href="admin/add_product.php" class="admin-bar-link" title="Add Product">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Add Product</span>
        </a>

        <div style="margin-top: auto; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); width: 100%; display: flex; justify-content: center;">
            <div class="admin-bar-link" title="Logged in as Admin">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span>Manager</span>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="container">
    <?php if (isset($_SESSION['success'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($_SESSION['success']); ?>, "success"));
        </script>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($error); ?>, "error"));
        </script>
    <?php endif; ?>

    <div class="spotlight-card">
        <div class="spotlight-content">
            <span class="spotlight-badge">Smart Mall ecommerce platform</span>
            <h1>One mall. Many categories.</h1>
            <p>Shop fashion, electronics, home products, and beauty essentials through one simple web-based marketplace.</p>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="#products" class="btn-primary">Start Shopping</a>
                <a href="#categories" class="btn-secondary">Browse Categories</a>
            </div>
        </div>
        <div class="spotlight-logo">
            <div class="slider-label">Top Sales</div>
            <div class="products-slider">
                <?php
                try {
                    $pdo = getDB();
                    $stmt = $pdo->query("
                SELECT p.product_id, p.name, p.image, COUNT(oi.order_item_id) as order_count 
                FROM products p
                INNER JOIN order_items oi ON p.product_id = oi.product_id
                INNER JOIN orders o ON oi.order_id = o.order_id
                WHERE o.status IN ('completed', 'processing', 'shipped')
                GROUP BY p.product_id
                HAVING order_count > 0
                ORDER BY order_count DESC
                LIMIT 4
            ");
                    $top_products = $stmt->fetchAll();

                    if (count($top_products) > 0):
                        $first_product = $top_products[0];
                        foreach ($top_products as $product):
                            $image = isset($product['image']) && $product['image'] ? '/reference/uploads/' . $product['image'] : '/reference/assets/images/logo-icon.png';
                ?>
                            <a href="/reference/product.php?product_id=<?php echo $product['product_id']; ?>" class="slider-item">
                                <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </a>
                        <?php
                        endforeach;
                        // Clone first item for seamless loop
                        $image_clone = isset($first_product['image']) && $first_product['image'] ? '/reference/uploads/' . $first_product['image'] : '/reference/assets/images/logo-icon.png';
                        ?>
                        <a href="/reference/product.php?product_id=<?php echo $first_product['product_id']; ?>" class="slider-item slider-clone">
                            <img src="<?php echo htmlspecialchars($image_clone); ?>" alt="<?php echo htmlspecialchars($first_product['name']); ?>">
                        </a>
                <?php
                    endif;
                } catch (PDOException $e) {
                    echo '<div class="slider-item"><img src="/reference/assets/images/logo-icon.png" alt="Smart Mall"></div>';
                }
                ?>
            </div>
            <div class="slider-dots"></div>
        </div>
    </div>

    <section class="smart-section" id="categories">
        <div class="section-heading">
            <h2>Curated Collections</h2>
        </div>

        <div class="category-grid">
            <?php foreach ($categories as $cat): ?>
                <?php
                $slug = $cat['slug'];
                // Collect images from DB, fallback to high-quality Unsplash defaults if empty
                // Collect images from DB, prepend /uploads/ path, fallback to Unsplash if empty
                $slides = [];
                if (!empty($cat['image1'])) $slides[] = '/reference/uploads/' . $cat['image1'];
                if (!empty($cat['image2'])) $slides[] = '/reference/uploads/' . $cat['image2'];
                if (!empty($cat['image3'])) $slides[] = '/reference/uploads/' . $cat['image3'];
                ?>
                <a
                    class="category-card <?php echo $category_slug === $slug ? 'is-active' : ''; ?>"
                    style="--category-accent: <?php echo htmlspecialchars($cat_accents[$slug] ?? '#2563eb'); ?>;"
                    href="index.php?category=<?php echo urlencode($slug); ?>#products">
                    <div class="category-bg">
                        <?php foreach ($slides as $idx => $slide_url): ?>
                            <img src="<?php echo $slide_url; ?>" alt="<?php echo htmlspecialchars($cat['name']); ?> slide <?php echo $idx + 1; ?>" loading="lazy">
                        <?php endforeach; ?>
                    </div>
                    <div class="category-mark">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </div>
                    <div class="category-content">
                        <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                        <p><?php echo htmlspecialchars($cat['description']); ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <div class="trust-grid">
        <div class="trust-item">
            <span class="trust-icon">
                <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                </svg>
            </span>
            <strong>Free Delivery</strong>
            <span>Complimentary courier on every order</span>
        </div>
        <div class="trust-item">
            <span class="trust-icon">
                <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M16 15v-1a4 4 0 00-8 0v1m0 0a2 2 0 100 4 2 2 0 000-4zm8 0a2 2 0 100 4 2 2 0 000-4zm-8 0h8"></path>
                </svg>
            </span>
            <strong>Easy Returns</strong>
            <span>30-day hassle-free return policy</span>
        </div>
        <div class="trust-item">
            <span class="trust-icon">
                <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                </svg>
            </span>
            <strong>24/7 Support</strong>
            <span>Round-the-clock customer care</span>
        </div>
        <div class="trust-item">
            <span class="trust-icon">
                <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0110 0v4"></path>
                </svg>
            </span>
            <strong>Secure Checkout</strong>
            <span>Protected payments with SSL encryption</span>
        </div>
    </div>

    <section class="smart-section" id="products">
        <div class="section-heading" style="margin-bottom: 5rem;">
            <h2>
                <?php
                if ($active_category) {
                    echo htmlspecialchars($active_category['name']);
                } elseif ($search_value !== '') {
                    echo 'Search Results';
                } else {
                    echo 'Latest Products';
                }
                ?>
            </h2>
            <div class="heading-accent"></div>
        </div>

        <div class="products-toolbar">
            <form action="index.php#products" method="GET" class="toolbar-container">
                <?php if ($active_category): ?>
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_slug); ?>">
                <?php endif; ?>

                <div class="toolbar-search">
                    <svg width="20" height="20" fill="none" stroke="var(--text-light)" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search_value); ?>" placeholder="Search current products">
                </div>

                <div class="toolbar-sort">
                    <select name="sort">
                        <option value="newest" <?php echo $sort_option === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="price_asc" <?php echo $sort_option === 'price_asc' ? 'selected' : ''; ?>>Price: Low-High</option>
                        <option value="price_desc" <?php echo $sort_option === 'price_desc' ? 'selected' : ''; ?>>Price: High-Low</option>
                    </select>
                </div>

                <button type="submit" class="btn-apply">Filter</button>
            </form>
        </div>

        <?php if (count($products) > 0): ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <article class="product-card">
                        <a href="product.php?product_id=<?php echo $product['product_id']; ?>" style="text-decoration: none; color: inherit;">
                            <div class="product-image">
                                <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                                    <div class="admin-overlay">
                                        <div class="admin-badge">
                                            <div style="width: 8px; height: 8px; background: #10b981; border-radius: 50%;"></div>
                                            Active
                                        </div>
                                        <span class="admin-edit-btn" title="Edit Product" onclick="event.stopPropagation(); location.href='admin/add_product.php?product_id=<?php echo $product['product_id']; ?>'" style="display: inline-flex; cursor: pointer;">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                            </svg>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?php echo htmlspecialchars(get_product_image_url($product['image'])); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <span>Product</span>
                                <?php endif; ?>
                            </div>

                            <div class="product-info">
                                <?php if (!empty($product['category_name'])): ?>
                                    <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                                <?php endif; ?>
                                <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 110)) . '...'; ?></p>
                                <div class="product-price"><?php echo smartmall_format_money($product['price']); ?></div>
                                <div class="product-stock <?php echo $product['stock'] <= 5 ? 'low' : ''; ?>">
                                    <?php echo $product['stock'] > 0 ? (int)$product['stock'] . ' in stock' : 'Out of stock'; ?>
                                </div>
                            </div>
                        </a>

                        <div class="product-info" style="padding-top: 0; padding-bottom: 1.5rem; margin-top: auto; flex: none;">
                            <div class="product-actions" style="margin-top: 0; display: flex; gap: 0.75rem;">
                                <?php if ($product['stock'] > 0): ?>
                                    <button class="btn-buy" style="flex: 1; white-space: nowrap; font-size: 0.9rem; padding: 0.65rem 0.75rem;" onclick="event.preventDefault(); buyNow(<?php echo $product['product_id']; ?>)">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                        Buy Now
                                    </button>
                                    <button class="btn-cart" style="display: flex; align-items: center; justify-content: center; width: 3rem; height: 3rem; padding: 0;" onclick="event.preventDefault(); addToCart(<?php echo $product['product_id']; ?>)" title="Add to Cart">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </button>
                                <?php else: ?>
                                    <button class="btn-cart" disabled style="width: 100%; white-space: nowrap; font-size: 0.9rem; padding: 0.65rem 0.75rem;">Out of Stock</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>No products found</h3>
                <p>
                    <?php if ($active_category): ?>
                        Try checking other categories or searching for products.
                    <?php elseif ($search_value !== ''): ?>
                        Try another search term.
                    <?php else: ?>
                        Add products from the admin dashboard to begin.
                    <?php endif; ?>
                </p>
                <a href="index.php" class="btn-primary" style="margin-top: 1rem;">Back to Home</a>
            </div>
        <?php endif; ?>
    </section>
</div>

<section class="philosophy-section">
    <div class="philosophy-container">
        <span>The Smart Mall Standard</span>
        <h3>Where Luxury Meets Precision.</h3>
        <p>Every interaction is designed for the modern connoisseur, from cinematic discovery to instantaneous global logistics.</p>
        <div class="heading-accent" style="margin-top: 3rem;"></div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var slider = document.querySelector('.products-slider');
        if (!slider) return;
        var items = slider.querySelectorAll('.slider-item');
        if (items.length < 2) return;
        var current = 0;
        var totalItems = items.length;
        var realCount = totalItems - 1;
        var dotsContainer = document.querySelector('.slider-dots');

        for (var i = 0; i < realCount; i++) {
            var dot = document.createElement('button');
            dot.className = 'slider-dot' + (i === 0 ? ' is-active' : '');
            dot.setAttribute('aria-label', 'Slide ' + (i + 1));
            (function(index) {
                dot.addEventListener('click', function() {
                    var target = index;
                    var diff = target - current;
                    if (diff > 0) {
                        current = target;
                        slider.style.transition = 'transform 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                        slider.style.transform = 'translateY(-' + (current * getItemHeight()) + 'px)';
                    } else if (diff < 0) {
                        current = target;
                        slider.style.transition = 'transform 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                        slider.style.transform = 'translateY(-' + (current * getItemHeight()) + 'px)';
                    }
                    updateDots();
                });
            })(i);
            dotsContainer.appendChild(dot);
        }

        function getItemHeight() {
            return items[0].offsetHeight;
        }

        function updateDots() {
            var dots = dotsContainer.querySelectorAll('.slider-dot');
            for (var i = 0; i < dots.length; i++) {
                dots[i].className = 'slider-dot' + (i === current ? ' is-active' : '');
            }
        }

        function advance() {
            current++;
            slider.style.transition = 'transform 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            slider.style.transform = 'translateY(-' + (current * getItemHeight()) + 'px)';
            if (current === totalItems - 1) {
                setTimeout(function() {
                    slider.style.transition = 'none';
                    slider.style.transform = 'translateY(0)';
                    current = 0;
                    updateDots();
                }, 600);
            } else {
                updateDots();
            }
        }

        setInterval(advance, 3500);
    });

    function buyNow(productId) {
        <?php if (!isset($_SESSION['user_id'])): ?>
            showToast('Please login to purchase items', 'warning');
            setTimeout(() => window.location.href = 'login.php', 1500);
            return;
        <?php endif; ?>

        fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'cart.php';
                } else {
                    showToast('Error adding to cart: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error adding to cart', 'error');
            });
    }

    function addToCart(productId) {
        <?php if (!isset($_SESSION['user_id'])): ?>
            showToast('Please login to add items to cart', 'warning');
            setTimeout(() => window.location.href = 'login.php', 1500);
            return;
        <?php endif; ?>

        fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Product added to cart!', 'success');
                    // Optional: Update cart count without reload
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) cartCount.textContent = parseInt(cartCount.textContent) + 1;
                } else {
                    showToast('Error adding to cart: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error adding to cart', 'error');
            });
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>