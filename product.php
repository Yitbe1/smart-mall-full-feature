<?php
// Product Details Page
require_once __DIR__ . '/config.php';

$page_title = 'Product Details - Smart Mall';
// Check if product ID is provided
if (!isset($_GET['product_id']) || empty($_GET['product_id'])) {
    header('Location: index.php');
    exit();
}

$product_id = (int)$_GET['product_id'];

try {
    $pdo = getDB();

    // Get product details
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = :product_id");
    $stmt->execute([':product_id' => $product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: index.php');
        exit();
    }

    // Get category name
    $category_name = '';
    if (!empty($product['category_id'])) {
        $cat_stmt = $pdo->prepare("SELECT name FROM categories WHERE category_id = :category_id");
        $cat_stmt->execute([':category_id' => $product['category_id']]);
        $category_name = $cat_stmt->fetchColumn();
    }

    // Check wishlist status
    $in_wishlist = false;
    if (isset($_SESSION['user_id'])) {
        $w_stmt = $pdo->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = :uid AND product_id = :pid");
        $w_stmt->execute([':uid' => $_SESSION['user_id'], ':pid' => $product_id]);
        $in_wishlist = (bool)$w_stmt->fetch();
    }

    // Fetch reviews
    $reviews = [];
    $avg_rating = 0;
    $review_count = 0;
    $stmt_r = $pdo->prepare("SELECT r.review_id, r.rating, r.comment AS review, r.created_at, u.name AS user_name FROM reviews r JOIN users u ON r.user_id = u.user_id WHERE r.product_id = :pid ORDER BY r.created_at DESC");
    $stmt_r->execute([':pid' => $product_id]);
    $reviews = $stmt_r->fetchAll();
    $review_count = count($reviews);
    if ($review_count > 0) {
        $stmt_avg = $pdo->query("SELECT AVG(rating) AS avg_rating FROM reviews WHERE product_id = " . (int)$product_id);
        $avg_rating = round((float)$stmt_avg->fetchColumn(), 1);
    }
} catch (PDOException $e) {
    error_log('Product page DB error: ' . $e->getMessage());
    die('An error occurred. Please try again later.');
}
include __DIR__ . '/includes/header.php';
?>

<style>
    .product-detail-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin: 1.5rem 0;
    }

    .product-media-section {
        display: flex;
        flex-direction: column;
        width: 100%;
        align-self: start;
    }

    .product-info-section {
        max-width: 580px;
    }

    .product-info-section .product-description-full {
        max-width: 540px;
    }

    .product-image-section {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .product-large-image {
        position: relative;
        width: 100%;
        aspect-ratio: 1;
        background: var(--bg-light);
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border-color);
        cursor: crosshair;
    }

    .product-large-image img,
    .product-large-image video {
        width: 100%;
        height: 100%;
        object-fit: contain;
        transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        background: var(--bg-light);
    }

    .product-large-image:hover img {
        transform: scale(1.5);
    }

    .product-gallery-thumbnails {
        display: flex;
        gap: 10px;
        margin-top: 15px;
        overflow-x: auto;
        padding-bottom: 5px;
        width: 100%;
    }

    .gallery-thumb {
        width: 70px;
        height: 70px;
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid transparent;
        transition: border-color 0.2s;
        flex-shrink: 0;
        background: var(--surface);
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border-color);
    }

    .gallery-thumb.active {
        border-color: var(--primary-color);
    }

    .gallery-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .gallery-thumb.video-thumb {
        position: relative;
    }

    .gallery-thumb.video-thumb::after {
        content: '▶';
        position: absolute;
        color: var(--text-dark);
        font-size: 20px;
        text-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
    }

    .product-large-image:hover .fullscreen-btn {
        opacity: 1;
        transform: scale(1);
    }

    .fullscreen-btn {
        position: absolute;
        top: 1.5rem;
        right: 1.5rem;
        width: 44px;
        height: 44px;
        background: var(--surface);
        backdrop-filter: blur(10px);
        border: 0.5px solid var(--text-light);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-dark);
        cursor: pointer;
        opacity: 0;
        transform: scale(0.8);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        z-index: 10;
    }

    .fullscreen-btn:hover {
        background: var(--text-dark);
        transform: scale(1.1);
        color: var(--bg-light);
    }

    /* Lightbox Styles */
    .lightbox {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--surface);
        backdrop-filter: blur(20px);
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
        cursor: zoom-out;
        animation: fade-in 0.4s ease;
    }

    .lightbox-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 60px;
        height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-dark);
        font-size: 3rem;
        cursor: pointer;
        opacity: 0.5;
        transition: all 0.3s ease;
        z-index: 10001;
    }

    .lightbox-nav:hover {
        opacity: 1;
        background: rgba(255, 255, 255, 0.05);
    }

    .lightbox-prev {
        left: 1rem;
    }

    .lightbox-next {
        right: 1rem;
    }

    .lightbox img {
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
        border-radius: 12px;
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5);
        pointer-events: none;
        /* Let clicks pass through to nav buttons if needed */
    }

    .lightbox-close {
        position: absolute;
        top: 2rem;
        right: 2rem;
        color: var(--text-dark);
        font-size: 2.5rem;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.2s;
    }

    .lightbox-close:hover {
        opacity: 1;
    }

    @keyframes fade-in {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .product-breadcrumb {
        color: var(--text-light);
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }

    .product-breadcrumb a {
        color: var(--primary-color);
        text-decoration: none;
    }

    .product-breadcrumb a:hover {
        text-decoration: underline;
    }

    .product-title {
        font-family: 'Outfit', sans-serif;
        font-size: 3rem;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
        font-weight: 800;
        line-height: 1.1;
    }

    .product-category-badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        background: var(--primary-light);
        color: var(--primary-color);
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        border-radius: 4px;
        margin-bottom: 1rem;
    }

    .product-rating {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
    }

    .product-price-large {
        font-size: 2rem;
        color: var(--primary-color);
        font-weight: bold;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .product-description-full {
        color: var(--text-light);
        line-height: 1.8;
        margin-bottom: 1.5rem;
        font-size: 1rem;
    }

    .product-stock-info {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding: 1rem;
        background-color: var(--bg-light);
        border-radius: 5px;
    }

    .stock-status {
        font-weight: 600;
    }

    .stock-status.in-stock {
        color: var(--success-color);
    }

    .stock-status.low-stock {
        color: var(--warning-color);
    }

    .stock-status.out-of-stock {
        color: var(--danger-color);
    }

    .product-actions-detail {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .quantity-selector {
        display: flex;
        align-items: center;
        border: 1px solid var(--border-color);
        border-radius: 5px;
        overflow: hidden;
    }

    .quantity-selector button {
        background: none;
        border: none;
        padding: 0.5rem 1rem;
        cursor: pointer;
        font-weight: bold;
        color: var(--primary-color);
    }

    .quantity-selector button:hover {
        background-color: var(--bg-light);
    }

    .quantity-selector input {
        width: 50px;
        border: none;
        text-align: center;
        padding: 0.5rem;
        font-weight: 600;
        background: transparent;
        color: var(--text-dark);
    }

    .quantity-selector input:focus {
        outline: none;
    }

    .btn-add-cart-detail {
        flex: 1;
        min-width: 200px;
        padding: 1rem;
        background-color: var(--primary-color);
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .btn-add-cart-detail:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
    }

    .btn-add-cart-detail:disabled {
        background-color: #ccc;
        cursor: not-allowed;
    }

    .btn-buy-detail {
        width: 100%;
        padding: 1rem;
        background: var(--secondary-color);
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-buy-detail:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }

    .btn-wishlist {
        padding: 1rem;
        background-color: var(--surface);
        color: var(--primary-color);
        border: 2px solid var(--primary-color);
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-wishlist:hover {
        background-color: var(--primary-color);
        color: #fff;
    }

    .product-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
        padding: 1rem 1.25rem;
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 0.9rem;
    }

    .product-meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-light);
    }

    .product-meta-item strong {
        color: var(--text-dark);
        font-weight: 600;
    }

    .product-features {
        margin-top: 1.5rem;
        padding: 1.5rem;
        background-color: var(--bg-light);
        border-radius: 10px;
    }

    .product-features h3 {
        color: var(--secondary-color);
        margin-bottom: 1rem;
        font-size: 1.2rem;
    }

    .features-list {
        list-style: none;
    }

    .features-list li {
        padding: 0.5rem 0;
        color: var(--text-light);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .features-list li::before {
        content: '✓';
        color: var(--success-color);
        font-weight: bold;
        font-size: 1.2rem;
    }

    /* Responsive */
    @media (min-width: 1200px) {
        .product-detail-container {
            gap: 2.5rem;
        }

        .product-title {
            font-size: 3rem;
        }

        .product-price-large {
            font-size: 2.5rem;
        }

        .product-media-section {
            max-width: 440px;
            justify-self: center;
        }

        .product-info-section {
            max-width: 500px;
        }

        .product-info-section .product-description-full {
            max-width: 460px;
        }
    }

    @media (max-width: 1024px) and (min-width: 769px) {
        .product-detail-container {
            gap: 2rem;
        }

        .product-title {
            font-size: 2.2rem;
        }

        .product-price-large {
            font-size: 1.6rem;
        }
    }

    @media (max-width: 768px) {
        .product-detail-container {
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .product-title {
            font-size: 1.8rem;
        }

        .product-price-large {
            font-size: 1.5rem;
        }

        .product-actions-detail {
            flex-direction: column;
        }

        .btn-add-cart-detail {
            min-width: unset;
        }

        .product-meta {
            flex-direction: column;
            gap: 0.5rem;
        }
    }

    .btn-wishlist-detail {
        width: 44px;
        height: 44px;
        padding: 0;
        background: transparent;
        color: #999;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.25s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .btn-wishlist-detail:hover {
        background: #fff0f0;
        color: #e74c3c;
        transform: scale(1.12);
    }
    .btn-wishlist-detail.in-wishlist {
        color: #e74c3c;
        background: #fff0f0;
    }
    .btn-wishlist-detail.in-wishlist:hover {
        color: #e74c3c;
        background: #ffe0e0;
    }
    .btn-wishlist-detail svg {
        transition: transform 0.25s ease;
    }
    .btn-wishlist-detail:hover svg {
        transform: scale(1.15);
    }

    @media (max-width: 480px) {
        .product-large-image {
            font-size: 3rem;
        }

        .product-detail-container {
            gap: 1rem;
            margin: 0.75rem 0;
        }

        .product-large-image {
            max-height: 250px;
        }

        .product-gallery-thumbnails {
            gap: 4px;
            margin-top: 6px;
        }

        .gallery-thumb {
            width: 40px;
            height: 40px;
        }

        .product-breadcrumb {
            margin-bottom: 0.5rem;
            font-size: 0.8rem;
        }

        .product-category-badge {
            padding: 0.2rem 0.5rem;
            margin-bottom: 0.35rem;
            font-size: 0.7rem;
        }

        .product-title {
            font-size: 1.1rem;
            margin-bottom: 0.15rem;
        }

        .product-price-large {
            font-size: 0.95rem;
            margin-bottom: 0.35rem;
        }

        .product-meta {
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
            gap: 0.4rem;
            margin-bottom: 0.65rem;
        }

        .product-stock-info {
            padding: 0.4rem;
            gap: 0.35rem;
            margin-bottom: 0.65rem;
            font-size: 0.8rem;
        }

        .product-description-full {
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 0.65rem;
        }

        .product-actions-detail {
            gap: 0.5rem;
            margin-bottom: 0.65rem;
        }

        .quantity-selector button {
            padding: 0.25rem 0.5rem;
            font-size: 0.9rem;
        }

        .quantity-selector input {
            width: 32px;
            padding: 0.25rem;
            font-size: 0.85rem;
        }

        .btn-add-cart-detail {
            padding: 0.55rem;
            font-size: 0.85rem;
        }

        .btn-buy-detail {
            padding: 0.55rem;
            font-size: 0.85rem;
        }

        .product-actions-detail {
            flex-direction: row;
            flex-wrap: wrap;
        }

        .quantity-selector {
            flex: 0 0 100%;
        }

        .btn-add-cart-detail,
        .btn-buy-detail {
            flex: 1;
        }

        .btn-buy-detail {
            width: auto !important;
            margin-top: 0 !important;
        }

        .product-features {
            padding: 0.75rem;
            margin-top: 0.65rem;
        }

        .product-features h3 {
            font-size: 0.9rem;
            margin-bottom: 0.35rem;
        }

        .features-list li {
            padding: 0.2rem 0;
            font-size: 0.8rem;
        }

        .container {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        .smartmall-footer {
            margin-top: 0.5rem !important;
        }
    }
</style>

<div class="container">
    <!-- Breadcrumb -->
    <div class="product-breadcrumb">
        <a href="index.php">Home</a> /
        <?php if ($category_name): ?>
            <span><?php echo htmlspecialchars($category_name); ?></span> /
        <?php else: ?>
            Products /
        <?php endif; ?>
        <span><?php echo htmlspecialchars($product['name']); ?></span>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success" style="margin-bottom:1rem;"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger" style="margin-bottom:1rem;"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Product Details -->
    <div class="product-detail-container">
        <!-- Media Gallery -->
        <div class="product-media-section">
            <div class="product-large-image" id="main-media-container">
                <button id="fullscreen-btn" class="fullscreen-btn" onclick="openLightbox()" title="Full Screen">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"></path>
                    </svg>
                </button>
                <?php if (!empty($product['image'])): ?>
                    <img src="<?php echo htmlspecialchars(get_product_image_url($product['image'])); ?>" id="main-image" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <?php else: ?>
                    <div id="main-image" style="font-size: 5rem;">📦</div>
                <?php endif; ?>
                <video id="main-video" controls style="display: none; width: 100%; height: 100%; object-fit: contain; background: black;"></video>
            </div>

            <?php
            $gallery_images = [];
            if (!empty($product['image'])) $gallery_images[] = $product['image'];
            $additional = json_decode($product['additional_images'] ?? '[]', true);
            if (is_array($additional)) {
                $gallery_images = array_merge($gallery_images, $additional);
            }
            ?>

            <?php if (count($gallery_images) > 1 || !empty($product['video'])): ?>
                <div class="product-gallery-thumbnails">
                    <?php foreach ($gallery_images as $index => $img): ?>
                        <div class="gallery-thumb <?php echo $index === 0 ? 'active' : ''; ?>" onclick="switchMedia('image', '<?php echo htmlspecialchars(get_product_image_url($img)); ?>', this)">
                            <img loading="lazy" src="<?php echo htmlspecialchars(get_product_image_url($img)); ?>" alt="Thumbnail">
                        </div>
                    <?php endforeach; ?>

                    <?php if (!empty($product['video'])): ?>
                        <div class="gallery-thumb video-thumb" onclick="switchMedia('video', '<?php echo htmlspecialchars(get_product_video_url($product['video'])); ?>', this)">
                            <video src="<?php echo htmlspecialchars(get_product_video_url($product['video'])); ?>" style="width: 100%; height: 100%; object-fit: cover;" muted></video>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Info -->
        <div class="product-info-section">
            <?php if ($category_name): ?>
                <div class="product-category-badge"><?php echo htmlspecialchars($category_name); ?></div>
            <?php endif; ?>
            <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>

            <div class="product-meta">
                <?php if ($category_name): ?>
                    <div class="product-meta-item">
                        <strong>Category:</strong> <?php echo htmlspecialchars($category_name); ?>
                    </div>
                <?php endif; ?>
                <div class="product-meta-item">
                    <strong>SKU:</strong> #<?php echo str_pad($product['product_id'], 4, '0', STR_PAD_LEFT); ?>
                </div>
            </div>

            <div class="product-price-large">
                <span class="price-amount"><?php echo smartmall_format_money($product['price']); ?></span>
            </div>

            <div class="product-stock-info">
                <?php if ($product['stock'] > 10): ?>
                    <span class="stock-status in-stock">✓ In Stock (<?php echo $product['stock']; ?> available)</span>
                <?php elseif ($product['stock'] > 0): ?>
                    <span class="stock-status low-stock">⚠ Only <?php echo $product['stock']; ?> left</span>
                <?php else: ?>
                    <span class="stock-status out-of-stock">✗ Out of Stock</span>
                <?php endif; ?>
            </div>

            <p class="product-description-full">
                <?php echo htmlspecialchars($product['description']); ?>
            </p>

            <?php if ($product['stock'] > 0): ?>
                <div class="product-actions-detail">
                    <div class="quantity-selector">
                        <button onclick="decreaseQuantity()">−</button>
                        <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                        <button onclick="increaseQuantity()">+</button>
                    </div>
                    <button class="btn-add-cart-detail" onclick="addProductToCart(<?php echo $product['product_id']; ?>)">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Add to Cart
                    </button>
                    <button class="btn-wishlist-detail <?php echo $in_wishlist ? 'in-wishlist' : ''; ?>" id="wishlist-btn" onclick="toggleWishlist(<?php echo $product['product_id']; ?>)" title="Save to wishlist">
                        <svg width="22" height="22" fill="<?php echo $in_wishlist ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                    </button>
                    <button class="btn-buy-detail" onclick="buyNow(<?php echo $product['product_id']; ?>)">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Buy Now
                    </button>
                </div>
            <?php else: ?>
                <div class="product-actions-detail">
                    <button class="btn-add-cart-detail" disabled style="opacity: 0.5;">
                        Out of Stock
                    </button>
                    <button class="btn-wishlist-detail <?php echo $in_wishlist ? 'in-wishlist' : ''; ?>" id="wishlist-btn" onclick="toggleWishlist(<?php echo $product['product_id']; ?>)" title="Save to wishlist">
                        <svg width="22" height="22" fill="<?php echo $in_wishlist ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                    </button>
                </div>
            <?php endif; ?>

            <div class="product-features">
                <h3>Store Guarantees</h3>
                <ul class="features-list">
                    <li>Secure checkout</li>
                    <li>Free shipping on all orders</li>
                    <li>Easy returns accepted</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="reviews-section">
        <h2>Customer Reviews <?php if ($review_count > 0): ?><span class="avg-rating"><?php echo str_repeat('★', (int)$avg_rating) . str_repeat('☆', 5 - (int)$avg_rating); ?> <?php echo $avg_rating; ?> (<?php echo $review_count; ?>)</span><?php endif; ?></h2>

        <?php if ($review_count > 0): ?>
            <div class="reviews-list">
                <?php foreach ($reviews as $rev): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <strong><?php echo htmlspecialchars($rev['user_name']); ?></strong>
                            <span class="review-stars"><?php echo str_repeat('★', (int)$rev['rating']) . str_repeat('☆', 5 - (int)$rev['rating']); ?></span>
                            <span class="review-date"><?php echo date('M j, Y', strtotime($rev['created_at'])); ?></span>
                        </div>
                        <?php if ($rev['review']): ?>
                            <p class="review-text"><?php echo htmlspecialchars($rev['review']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-reviews">No reviews yet. Be the first to review this product!</p>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="review-form">
                <h3>Write a Review</h3>
                <form method="POST" action="submit_review.php">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    <div class="star-rating">
                        <span class="star-label">Your Rating:</span>
                        <div class="stars-input">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>" <?php echo $i === 5 ? 'checked' : ''; ?>>
                                <label for="star<?php echo $i; ?>">★</label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <textarea name="review" rows="4" placeholder="Share your thoughts about this product... (optional)"></textarea>
                    <button type="submit" class="btn-review-submit">Submit Review</button>
                </form>
            </div>
        <?php else: ?>
            <p class="login-to-review"><a href="login.php?redirect=product.php&product_id=<?php echo $product_id; ?>">Login</a> to write a review.</p>
        <?php endif; ?>
    </div>
</div>

<style>
.reviews-section { margin-top: 2rem; }
.reviews-section h2 { font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--text-dark); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; }
.avg-rating { font-size: 1rem; font-weight: 600; color: var(--text-light); display: flex; align-items: center; gap: 0.25rem; }
.reviews-list { display: flex; flex-direction: column; gap: 1rem; margin-bottom: 2rem; }
.review-card { background: var(--surface); border: 1px solid var(--border-color); border-radius: var(--radius); padding: 1.25rem; }
.review-header { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem; flex-wrap: wrap; }
.review-header strong { font-size: 0.95rem; color: var(--text-dark); }
.review-stars { color: #f59e0b; font-size: 1rem; letter-spacing: 2px; }
.review-date { font-size: 0.8rem; color: var(--text-light); margin-left: auto; }
.review-text { font-size: 0.9rem; color: var(--text-muted); line-height: 1.5; }
.no-reviews { color: var(--text-light); font-style: italic; margin-bottom: 1.5rem; }
.login-to-review { color: var(--text-light); font-size: 0.9rem; }
.login-to-review a { color: var(--primary-color); font-weight: 600; }

.review-form { background: var(--surface); border: 1px solid var(--border-color); border-radius: var(--radius); padding: 1.5rem; margin-top: 1.5rem; }
.review-form h3 { font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 1rem; }
.star-rating { display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; }
.star-label { font-size: 0.9rem; font-weight: 600; color: var(--text-dark); }
.stars-input { display: flex; flex-direction: row-reverse; gap: 0.25rem; }
.stars-input input { display: none; }
.stars-input label { font-size: 1.75rem; color: #ddd; cursor: pointer; transition: color 0.15s; }
.stars-input input:checked ~ label,
.stars-input label:hover,
.stars-input label:hover ~ label { color: #f59e0b; }
.review-form textarea { width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 5px; font-family: inherit; font-size: 0.9rem; resize: vertical; margin-bottom: 1rem; background: var(--bg-light); color: var(--text-dark); }
.review-form textarea:focus { outline: none; border-color: var(--primary-color); }
.btn-review-submit { background: var(--primary-color); color: #fff; border: none; padding: 0.7rem 1.5rem; border-radius: 5px; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: background 0.2s; }
.btn-review-submit:hover { background: var(--primary-dark); }
</style>

<script>
    const mainImageContainer = document.querySelector('#main-media-container');
    const mainImage = document.getElementById('main-image');

    mainImageContainer.addEventListener('mousemove', (e) => {
        if (mainImageContainer.querySelector('#main-video').style.display !== 'none') return;

        const rect = mainImageContainer.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        mainImage.style.transformOrigin = `${x}px ${y}px`;
        mainImage.style.transform = "scale(2.5)";
    });

    mainImageContainer.addEventListener('mouseleave', () => {
        mainImage.style.transformOrigin = "center";
        mainImage.style.transform = "scale(1)";
    });

    function closeLightbox() {
        document.getElementById('lightbox').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Close lightbox on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') changeLightboxImage(-1);
        if (e.key === 'ArrowRight') changeLightboxImage(1);
    });

    let currentGallery = <?php
                            $full_urls = array_map(function ($img) {
                                return get_product_image_url($img);
                            }, $gallery_images);
                            echo json_encode($full_urls);
                            ?>;
    let currentIndex = 0;

    function openLightbox() {
        const img = document.getElementById('main-image');
        const lb = document.getElementById('lightbox');
        const lbImg = document.getElementById('lightbox-img');

        if (img.style.display === 'none') return;

        // Find current index
        const currentSrc = img.src;
        currentIndex = currentGallery.findIndex(url => currentSrc.includes(url) || url.includes(currentSrc));
        if (currentIndex === -1) currentIndex = 0;

        lbImg.src = currentGallery[currentIndex];
        lb.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function changeLightboxImage(direction) {
        if (currentGallery.length <= 1) return;

        currentIndex += direction;
        if (currentIndex >= currentGallery.length) currentIndex = 0;
        if (currentIndex < 0) currentIndex = currentGallery.length - 1;

        const lbImg = document.getElementById('lightbox-img');
        lbImg.src = currentGallery[currentIndex];
    }

    // Cursor-tracked depth zoom on desktop (disabled on touch devices)
    if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
        const zoomContainer = document.querySelector('.product-large-image');
        const zoomImg = document.getElementById('main-image');
        zoomContainer.addEventListener('mousemove', function(e) {
            if (zoomImg.style.display === 'none') return;
            const rect = zoomContainer.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            zoomImg.style.transformOrigin = x + '% ' + y + '%';
        });
        zoomContainer.addEventListener('mouseleave', function() {
            zoomImg.style.transformOrigin = 'center center';
        });
    }

    function switchMedia(type, url, thumbElement) {
        // Update active thumbnail
        document.querySelectorAll('.gallery-thumb').forEach(el => el.classList.remove('active'));
        thumbElement.classList.add('active');

        const imgEl = document.getElementById('main-image');
        const vidEl = document.getElementById('main-video');

        if (type === 'image') {
            document.getElementById('fullscreen-btn').style.display = 'flex';
            vidEl.style.display = 'none';
            vidEl.pause();
            imgEl.style.display = 'block';
            imgEl.src = url;
        } else if (type === 'video') {
            document.getElementById('fullscreen-btn').style.display = 'none';
            imgEl.style.display = 'none';
            vidEl.style.display = 'block';
            vidEl.src = url;
            vidEl.play();
        }
    }

    function increaseQuantity() {
        const input = document.getElementById('quantity');
        const max = parseInt(input.max);
        if (parseInt(input.value) < max) {
            input.value = parseInt(input.value) + 1;
        }
    }

    function decreaseQuantity() {
        const input = document.getElementById('quantity');
        if (parseInt(input.value) > 1) {
            input.value = parseInt(input.value) - 1;
        }
    }

    function addProductToCart(productId) {
        <?php if (!isset($_SESSION['user_id'])): ?>
            showToast('Please login to add items to cart', 'warning');
            setTimeout(() => window.location.href = 'login.php', 1500);
            return;
        <?php endif; ?>

        const quantity = document.getElementById('quantity').value;

        fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: parseInt(quantity)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Product added to cart!', 'success');
                    document.getElementById('quantity').value = 1;
                    // Update cart count in header
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        const newCount = parseInt(cartCount.textContent) + parseInt(quantity);
                        cartCount.textContent = newCount;
                        cartCount.style.display = 'grid';
                    }
                } else {
                    showToast('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error adding to cart', 'error');
            });
    }

    function toggleWishlist(productId) {
        <?php if (!isset($_SESSION['user_id'])): ?>
            showToast('Please login to save items', 'warning');
            setTimeout(() => window.location.href = 'login.php', 1500);
            return;
        <?php endif; ?>
        const btn = document.getElementById('wishlist-btn');
        fetch('toggle_wishlist.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                showToast(d.message, 'success');
                if (d.action === 'added') {
                    btn.classList.add('in-wishlist');
                    btn.querySelector('svg').setAttribute('fill', 'currentColor');
                } else {
                    btn.classList.remove('in-wishlist');
                    btn.querySelector('svg').setAttribute('fill', 'none');
                }
            } else {
                showToast(d.message, 'error');
            }
        })
        .catch(() => showToast('Error updating wishlist', 'error'));
    }

    function buyNow(productId) {
        const quantity = document.getElementById('quantity')?.value || 1;
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
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'cart.php';
                } else {
                    showToast('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error adding to cart', 'error');
            });
    }
</script>

<div id="lightbox" class="lightbox" onclick="closeLightbox()">
    <span class="lightbox-close" onclick="event.stopPropagation(); closeLightbox()">&times;</span>
    <div class="lightbox-nav lightbox-prev" onclick="event.stopPropagation(); changeLightboxImage(-1)">&#10094;</div>
    <div class="lightbox-nav lightbox-next" onclick="event.stopPropagation(); changeLightboxImage(1)">&#10095;</div>
    <img loading="lazy" id="lightbox-img" src="" alt="" onclick="event.stopPropagation()">
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>