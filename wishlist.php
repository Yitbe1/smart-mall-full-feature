<?php
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=wishlist.php');
    exit;
}

$page_title = 'My Wishlist - Smart Mall';
$user_id = (int)$_SESSION['user_id'];
$items = [];

// Handle remove from wishlist page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
    csrf_verify();
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE wishlist_id = :id AND user_id = :uid");
        $stmt->execute([':id' => (int)$_POST['remove_id'], ':uid' => $user_id]);
        $_SESSION['success'] = 'Item removed from wishlist.';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error removing item.';
    }
    header('Location: wishlist.php');
    exit;
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT w.wishlist_id, w.product_id, w.created_at,
               p.name, p.price, p.image, p.stock
        FROM wishlist w
        JOIN products p ON w.product_id = p.product_id
        WHERE w.user_id = :uid
        ORDER BY w.created_at DESC
    ");
    $stmt->execute([':uid' => $user_id]);
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Wishlist page error: " . $e->getMessage());
    $error = 'Could not load wishlist.';
}

include __DIR__ . '/includes/header.php';
?>

<style>
    .wishlist-container { max-width: 1000px; margin: 2rem auto; }
    .wishlist-container h1 { font-family: 'Outfit', sans-serif; font-size: 2.5rem; font-weight: 800; color: var(--text-dark); margin-bottom: 0.5rem; }
    .wishlist-container > p { color: var(--text-light); margin-bottom: 2rem; }
    .wishlist-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem; }
    .wish-card { background: var(--surface); border: 1px solid var(--border-color); border-radius: var(--radius); overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; }
    .wish-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
    .wish-card img { width: 100%; height: 200px; object-fit: cover; display: block; }
    .wish-card-body { padding: 1rem; }
    .wish-card-body h3 { font-size: 1rem; margin-bottom: 0.5rem; }
    .wish-card-body h3 a { color: var(--text-dark); text-decoration: none; }
    .wish-card-body h3 a:hover { color: var(--primary-color); }
    .wish-price { font-size: 1.2rem; font-weight: 700; color: var(--primary-color); margin-bottom: 0.5rem; }
    .wish-actions { display: flex; gap: 0.5rem; }
    .wish-actions form { flex: 1; }
    .btn-wish-remove, .btn-wish-cart { width: 100%; padding: 0.6rem; border: none; border-radius: 5px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
    .btn-wish-cart { background: var(--primary-color); color: #fff; }
    .btn-wish-cart:hover { background: var(--primary-dark); }
    .btn-wish-remove { background: var(--bg-light); color: var(--danger-color); border: 1px solid var(--border-color); }
    .btn-wish-remove:hover { background: #fee2e2; }
    .wish-empty { text-align: center; padding: 4rem 2rem; color: var(--text-light); }
    .wish-empty svg { margin-bottom: 1rem; opacity: 0.3; }
    .wish-empty h2 { font-size: 1.5rem; color: var(--text-dark); margin-bottom: 0.5rem; }
    .wish-empty a { color: var(--primary-color); font-weight: 600; }
    @media (max-width: 480px) {
        .wishlist-grid { grid-template-columns: 1fr 1fr; gap: 0.75rem; }
        .wish-card img { height: 140px; }
    }
</style>

<div class="container">
    <div class="wishlist-container">
        <h1>My Wishlist</h1>
        <p><?php echo count($items); ?> saved item<?php echo count($items) !== 1 ? 's' : ''; ?></p>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success" style="margin-bottom:1rem;"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger" style="margin-bottom:1rem;"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <?php if (count($items) > 0): ?>
            <div class="wishlist-grid">
                <?php foreach ($items as $item): ?>
                    <div class="wish-card">
                        <a href="product.php?product_id=<?php echo $item['product_id']; ?>">
                            <img loading="lazy" src="<?php echo htmlspecialchars(get_product_image_url($item['image'])); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </a>
                        <div class="wish-card-body">
                            <h3><a href="product.php?product_id=<?php echo $item['product_id']; ?>"><?php echo htmlspecialchars($item['name']); ?></a></h3>
                            <div class="wish-price"><?php echo smartmall_format_money($item['price']); ?></div>
                            <div class="wish-actions">
                                <?php if ($item['stock'] > 0): ?>
                                    <button class="btn-wish-cart" onclick="addToCart(<?php echo $item['product_id']; ?>)">Add to Cart</button>
                                <?php else: ?>
                                    <button class="btn-wish-cart" disabled style="opacity:0.5;cursor:not-allowed;">Out of Stock</button>
                                <?php endif; ?>
                                <form method="POST" style="flex:1;">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="remove_id" value="<?php echo $item['wishlist_id']; ?>">
                                    <button type="submit" class="btn-wish-remove">Remove</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="wish-empty">
                <svg width="64" height="64" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                <h2>Your wishlist is empty</h2>
                <p>Start <a href="index.php">browsing products</a> and save your favorites!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function addToCart(productId) {
    <?php if (!isset($_SESSION['user_id'])): ?>
        showToast('Please login first', 'warning');
        setTimeout(() => window.location.href = 'login.php', 1500);
        return;
    <?php endif; ?>
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId, quantity: 1 })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            showToast('Added to cart!', 'success');
            const cc = document.querySelector('.cart-count');
            if (cc) cc.textContent = parseInt(cc.textContent) + 1;
        } else {
            showToast(d.message || 'Error', 'error');
        }
    })
    .catch(() => showToast('Error adding to cart', 'error'));
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
