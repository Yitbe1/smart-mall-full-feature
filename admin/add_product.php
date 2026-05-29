<?php
// Add/Edit Product Page
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$page_title = 'Add Product - Smart Mall';
$product = null;
$is_edit = false;
$errors = [];
$product_id = null;
$upload_dir = realpath(__DIR__ . '/../uploads');
if ($upload_dir === false) {
    $upload_dir = __DIR__ . '/../uploads';
}
$additional_images_arr = null;

require_once __DIR__ . '/includes/product_handler.php';

// Check if editing
if (isset($_GET['product_id']) && !empty($_GET['product_id'])) {
    $product_id = (int)$_GET['product_id'];

    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = :product_id");
        $stmt->execute([':product_id' => $product_id]);
        $product = $stmt->fetch();

        if (!$product) {
            header('Location: dashboard.php');
            exit();
        }

        $is_edit = true;
        $page_title = 'Edit Product - Smart Mall';
    } catch (PDOException $e) {
        error_log("Admin add_product error (fetch product): " . $e->getMessage());
        $errors[] = "Database error occurred.";
    }
}

// Load categories for the dropdown
$categories_list = [];
try {
    $categories_list = getDB()->query("SELECT category_id, name FROM categories ORDER BY name")->fetchAll();
} catch (Exception $e) {
    $categories_list = [];
}

// Handle form submission / delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete_media') {
        csrf_verify();

        if (!$is_edit || !$product) {
            header('Location: dashboard.php');
            exit();
        }

        $media_type = $_POST['media_type'] ?? '';

        try {
            $pdo = getDB();

            if ($media_type === 'cover') {
                if (!empty($product['image'])) {
                    delete_product_file($product['image'], $upload_dir);
                    $stmt = $pdo->prepare("UPDATE products SET image = '' WHERE product_id = :product_id");
                    $stmt->execute([':product_id' => $product_id]);
                    $_SESSION['success'] = 'Main cover image deleted successfully.';
                }
            } elseif ($media_type === 'slide2' || $media_type === 'slide3') {
                $additional_images_names = json_decode($product['additional_images'] ?? '[]', true) ?: [];
                $idx = ($media_type === 'slide2') ? 0 : 1;

                if (!empty($additional_images_names[$idx])) {
                    delete_product_file($additional_images_names[$idx], $upload_dir);
                    $additional_images_names[$idx] = '';
                    $updated_json = json_encode($additional_images_names);

                    $stmt = $pdo->prepare("UPDATE products SET additional_images = :imgs WHERE product_id = :product_id");
                    $stmt->execute([':imgs' => $updated_json, ':product_id' => $product_id]);
                    $_SESSION['success'] = 'Angle image deleted successfully.';
                }
            } elseif ($media_type === 'gallery') {
                $filename = $_POST['filename'] ?? '';
                $additional_images_names = json_decode($product['additional_images'] ?? '[]', true) ?: [];

                if (($key = array_search($filename, $additional_images_names)) !== false) {
                    delete_product_file($filename, $upload_dir);
                    unset($additional_images_names[$key]);
                    $additional_images_names = array_values($additional_images_names);
                    $updated_json = json_encode($additional_images_names);

                    $stmt = $pdo->prepare("UPDATE products SET additional_images = :imgs WHERE product_id = :product_id");
                    $stmt->execute([':imgs' => $updated_json, ':product_id' => $product_id]);
                    $_SESSION['success'] = 'Gallery image deleted successfully.';
                }
            } elseif ($media_type === 'video') {
                if (!empty($product['video'])) {
                    delete_product_file($product['video'], $upload_dir);
                    $stmt = $pdo->prepare("UPDATE products SET video = NULL WHERE product_id = :product_id");
                    $stmt->execute([':product_id' => $product_id]);
                    $_SESSION['success'] = 'Product video deleted successfully.';
                }
            }

            header("Location: add_product.php?product_id=" . $product_id);
            exit();
        } catch (PDOException $e) {
            error_log("Admin add_product error (delete media): " . $e->getMessage());
            $errors[] = "Error deleting media. Please try again.";
        }
    } else {
        // Standard Add/Edit Save Flow
        csrf_verify();

        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = (float)($_POST['price'] ?? 0);
        $stock       = (int)($_POST['stock'] ?? 0);
        $category_id = (int)($_POST['category_id'] ?? 0) ?: null;

        if ($name === '') {
            $errors['name'] = 'Product name is required';
        }

        if ($description === '') {
            $errors['description'] = 'Description is required';
        }

        if ($price <= 0) {
            $errors['price'] = 'Price must be greater than 0';
        }

        if ($stock < 0) {
            $errors['stock'] = 'Stock cannot be negative';
        }

        $img_result = handle_main_image_upload($_FILES['image'] ?? null, $product['image'] ?? null, $is_edit, $upload_dir);
        $image_name = $img_result['filename'];
        if ($img_result['error'] !== null) {
            $errors['image'] = $img_result['error'];
        }

        $existing_additional = json_decode($product['additional_images'] ?? '[]', true) ?: [];
        $additional_images_arr = handle_additional_images_upload(
            $_FILES['slide2'] ?? null,
            $_FILES['slide3'] ?? null,
            $_FILES['additional_images'] ?? null,
            $existing_additional,
            $upload_dir
        );
        $additional_images_json = json_encode($additional_images_arr);

        $video_name = handle_video_upload($_FILES['video'] ?? null, $product['video'] ?? null, $upload_dir);

        if (empty($errors)) {
            try {
                $pdo = getDB();

                save_product($pdo, [
                    ':name'        => $name,
                    ':description' => $description,
                    ':price'       => $price,
                    ':stock'       => $stock,
                    ':image'       => $image_name,
                    ':category_id' => $category_id,
                    ':additional_images' => $additional_images_json,
                    ':video'       => $video_name,
                ], $is_edit, $product_id);

                $_SESSION['success'] = $is_edit ? 'Product updated successfully!' : 'Product added successfully!';

                require_once __DIR__ . '/../includes/cache.php';
                invalidate_cache_pattern('product');

                header('Location: dashboard.php');
                exit();
            } catch (PDOException $e) {
                error_log("Admin add_product error (save): " . $e->getMessage());
                $errors['database'] = 'Database error occurred. Please try again.';
            }
        }
    }
}

require_once __DIR__ . '/includes/product_form.php';
require_once '../includes/header.php';
include __DIR__ . '/includes/admin_nav.php';
render_product_form($product, $categories_list, $errors, $is_edit, ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($errors)) ? $additional_images_arr : null);
require_once '../includes/footer.php';
