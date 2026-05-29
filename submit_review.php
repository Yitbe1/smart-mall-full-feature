<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

csrf_verify();

$product_id = (int)($_POST['product_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$review = trim($_POST['review'] ?? '');

if ($product_id <= 0 || $rating < 1 || $rating > 5) {
    $_SESSION['error'] = 'Invalid rating or product.';
    header('Location: product.php?product_id=' . $product_id);
    exit;
}

try {
    $pdo = getDB();

    $stmt = $pdo->prepare("SELECT product_id FROM products WHERE product_id = :pid");
    $stmt->execute([':pid' => $product_id]);
    if (!$stmt->fetch()) {
        $_SESSION['error'] = 'Product not found.';
        header('Location: index.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT review_id FROM reviews WHERE user_id = :uid AND product_id = :pid");
    $stmt->execute([':uid' => $_SESSION['user_id'], ':pid' => $product_id]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = 'You have already reviewed this product.';
        header('Location: product.php?product_id=' . $product_id);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, review) VALUES (:pid, :uid, :rating, :review)");
    $stmt->execute([
        ':pid' => $product_id,
        ':uid' => $_SESSION['user_id'],
        ':rating' => $rating,
        ':review' => $review !== '' ? $review : null,
    ]);

    $_SESSION['success'] = 'Review submitted successfully!';
} catch (PDOException $e) {
    error_log('Submit review error: ' . $e->getMessage());
    $_SESSION['error'] = 'Error submitting review. Please try again.';
}

header('Location: product.php?product_id=' . $product_id);
exit;
