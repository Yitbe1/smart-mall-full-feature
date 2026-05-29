<?php
require_once __DIR__ . '/../config.php';
// Admin Category Management
$error = '';
$success = '';
$page_title = 'Manage Categories - Smart Mall';
$current_page = 'manage_categories.php';
// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$categories = [];

/**
 * Handle image upload for category slides
 */
function handle_category_upload($file_key, $existing_path = '')
{
    if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
        return $existing_path;
    }

    $upload_dir = '../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_info = pathinfo($_FILES[$file_key]['name']);
    $extension = strtolower($file_info['extension']);
    $new_name = 'cat_' . uniqid() . '.' . $extension;
    $dest_path = $upload_dir . $new_name;

    if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $dest_path)) {
        return $new_name;
    }
    return $existing_path;
}

// Handle deleting specific category slide
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_category_slide') {
    csrf_verify();

    $cat_id = (int)$_POST['category_id'];
    $slide_num = (int)$_POST['slide_num'];

    if ($slide_num >= 1 && $slide_num <= 3) {
        try {
            $pdo = getDB();
            $stmt = $pdo->prepare("SELECT image1, image2, image3 FROM categories WHERE category_id = :category_id");
            $stmt->execute([':category_id' => $cat_id]);
            $cat_data = $stmt->fetch();

            if ($cat_data) {
                $slide_key = 'image' . $slide_num;
                $filename = $cat_data[$slide_key] ?? '';

                if (!empty($filename)) {
                    // Check local file uploads first
                    $old_path = '../uploads/' . $filename;
                    if (file_exists($old_path)) {
                        unlink($old_path);
                    }

                    $update_stmt = $pdo->prepare("UPDATE categories SET $slide_key = '' WHERE category_id = :category_id");
                    $update_stmt->execute([':category_id' => $cat_id]);
                    $success = "Category slide $slide_num deleted successfully!";
                }
            }
        } catch (PDOException $e) {
            error_log("Admin categories error (delete slide): " . $e->getMessage());
            $error = "Error deleting category slide. Please try again.";
        }
    }
}

// Handle add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    csrf_verify();
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Check for uploads first, fallback to text URLs
    $image1 = handle_category_upload('upload1', trim($_POST['image1'] ?? ''));
    $image2 = handle_category_upload('upload2', trim($_POST['image2'] ?? ''));
    $image3 = handle_category_upload('upload3', trim($_POST['image3'] ?? ''));

    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));

    if ($name === '') {
        $error = "Category name is required";
    } else {
        try {
            $pdo = getDB();
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, image1, image2, image3) VALUES (:name, :slug, :description, :image1, :image2, :image3)");
            $stmt->execute([
                ':name' => $name,
                ':slug' => $slug,
                ':description' => $description,
                ':image1' => $image1,
                ':image2' => $image2,
                ':image3' => $image3
            ]);
            $success = "Category '$name' added successfully!";
        } catch (PDOException $e) {
            error_log("Admin categories error (add): " . $e->getMessage());
            $error = "Error adding category. Please try again.";
        }
    }
}

// Handle update category (The "Edit Option")
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    csrf_verify();

    $cat_id = (int)($_POST['category_id'] ?? 0);  // ← ADD THIS ONE LINE

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Fetch current paths... (rest of your code stays exactly the same)
    $pdo = getDB();
    $curr = $pdo->prepare("SELECT image1, image2, image3 FROM categories WHERE category_id = :category_id");
    $curr->execute([':category_id' => $cat_id]);
    // ...

    $image1 = handle_category_upload('upload1', trim($_POST['image1'] ?? ($paths['image1'] ?? '')));
    $image2 = handle_category_upload('upload2', trim($_POST['image2'] ?? ($paths['image2'] ?? '')));
    $image3 = handle_category_upload('upload3', trim($_POST['image3'] ?? ($paths['image3'] ?? '')));

    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("UPDATE categories SET name = :name, description = :description, image1 = :image1, image2 = :image2, image3 = :image3 WHERE category_id = :category_id");
        $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':image1' => $image1,
            ':image2' => $image2,
            ':image3' => $image3,
            ':category_id' => $cat_id
        ]);
        $success = "Category updated successfully!";
    } catch (PDOException $e) {
        error_log("Admin categories error (update): " . $e->getMessage());
        $error = "Error updating category. Please try again.";
    }
}

// Handle delete category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    csrf_verify();
    $cat_id = (int)$_POST['category_id'];
    try {
        $pdo = getDB();
        // Check if products are using this category
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = :category_id");
        $stmt->execute([':category_id' => $cat_id]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Cannot delete category: products are still assigned to it.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = :category_id");
            $stmt->execute([':category_id' => $cat_id]);
            $success = "Category deleted successfully!";
        }
    } catch (PDOException $e) {
        error_log("Admin categories error (delete): " . $e->getMessage());
        $error = "Error deleting category. Please try again.";
    }
}

try {
    $categories = getDB()->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    error_log("Admin categories error: " . $e->getMessage());
    $error = "Database error. Please try again.";
}
require_once __DIR__ . '/../includes/header.php';
?>
<?php include __DIR__ . '/includes/admin_nav.php'; ?>

<style>
    .cat-grid {
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 2rem;
        margin-top: 1.5rem;
        align-items: start;
    }

    .cat-form,
    .cat-list {
        background: var(--surface);
        padding: 2rem;
        border-radius: 24px;
        box-shadow: 0 2px 16px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border-color);
    }

    .cat-form h3,
    .cat-list h3 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--secondary-color);
        margin-bottom: 1.25rem;
    }

    .cat-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .cat-table th,
    .cat-table td {
        padding: 1rem 0.75rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.9rem;
    }

    .cat-table th {
        font-weight: 800;
        color: var(--primary-color);
        font-family: 'Outfit', sans-serif;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.1em;
    }

    .cat-table th:first-child {
        width: auto;
    }

    .cat-table th:nth-child(2) {
        width: 110px;
        text-align: center;
    }

    .cat-table th:last-child {
        width: 110px;
        text-align: center;
    }

    .cat-table td:nth-child(2) {
        text-align: center;
    }

    .cat-table td:last-child {
        text-align: center;
    }

    .cat-table tr:last-child td {
        border-bottom: none;
    }

    .cat-table tbody tr:hover {
        background: rgba(0, 0, 0, 0.015);
    }

    .category-name-cell strong {
        color: var(--secondary-color);
        display: block;
        margin-bottom: 0.25rem;
    }

    .btn-delete-cat {
        background: rgba(239, 68, 68, 0.1);
        border: none;
        color: #ef4444;
        padding: 0.45rem 1rem;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 700;
        font-size: 0.78rem;
        transition: all 0.2s;
    }

    .btn-delete-cat:hover {
        background: #ef4444;
        color: #fff;
        transform: translateY(-1px);
    }

    .cat-count-badge {
        background: var(--primary-light);
        color: var(--primary-color);
        padding: 0.2rem 0.7rem;
        border-radius: 100px;
        font-weight: 800;
        font-size: 0.72rem;
        white-space: nowrap;
    }

    .cat-inline-name {
        font-weight: 800;
        font-size: 1rem;
        border: 1px solid transparent;
        background: transparent;
        padding: 0.25rem 0.35rem;
        color: var(--secondary-color);
        border-radius: 6px;
        width: 100%;
        transition: border-color 0.2s;
    }

    .cat-inline-name:focus {
        border-color: var(--primary-color);
        outline: none;
        background: white;
    }

    .cat-inline-desc {
        font-size: 0.85rem;
        border: 1px solid transparent;
        background: transparent;
        padding: 0.25rem 0.35rem;
        color: var(--text-light);
        width: 100%;
        resize: vertical;
        border-radius: 6px;
        transition: border-color 0.2s;
    }

    .cat-inline-desc:focus {
        border-color: var(--border-color);
        outline: none;
        background: white;
    }

    .cat-slides-box {
        background: rgba(0, 0, 0, 0.02);
        padding: 1rem;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        display: grid;
        gap: 0.75rem;
        margin-top: 0.5rem;
    }

    .cat-slide-row {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .cat-slide-label {
        font-size: 0.6rem;
        font-weight: 800;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .cat-slide-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        align-items: center;
    }

    .cat-slide-preview {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-top: 0.25rem;
    }

    .cat-slide-thumb {
        width: 48px;
        height: 32px;
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid var(--border-color);
        background: var(--bg-light);
        flex-shrink: 0;
    }

    .cat-slide-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .btn-primary-sm {
        padding: 0.4rem 1rem;
        font-size: 0.75rem;
        align-self: flex-start;
        margin-top: 0.5rem;
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-primary-sm:hover {
        background: var(--primary-dark);
    }

    .btn-delete-slide {
        padding: 0.2rem 0.5rem;
        font-size: 0.65rem;
        background: rgba(220, 38, 38, 0.1);
        color: var(--danger-color);
        border: 1px solid rgba(220, 38, 38, 0.1);
        border-radius: 6px;
        cursor: pointer;
        font-weight: 700;
        transition: all 0.2s;
    }

    .btn-delete-slide:hover {
        background: var(--danger-color);
        color: white;
    }

    .cat-back-btn {
        background: var(--surface);
        color: var(--secondary-color);
        border: 1px solid var(--border-color);
        padding: 0.6rem 1.25rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.85rem;
        text-decoration: none;
        transition: all 0.2s;
    }

    .cat-back-btn:hover {
        background: var(--primary-light);
        border-color: var(--primary-color);
    }

    .cat-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 2rem;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 1.5rem;
    }

    .cat-heading {
        font-family: 'Outfit', sans-serif;
        font-size: 2.5rem;
        line-height: 1.1;
        color: var(--secondary-color);
        margin: 0;
    }

    .hero-kicker {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--primary-color);
        font-weight: 700;
        display: block;
        margin-bottom: 0.35rem;
    }

    @media (max-width: 980px) {
        .cat-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .container {
            padding-bottom: 0.5rem;
        }

        .cat-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 0.5rem;
            margin-bottom: 1rem !important;
            padding-bottom: 0.75rem !important;
        }

        .cat-heading {
            font-size: 1.5rem !important;
        }

        .hero-kicker {
            font-size: 0.7rem !important;
        }

        .cat-back-btn {
            padding: 0.4rem 0.8rem !important;
            font-size: 0.75rem;
        }

        .cat-form,
        .cat-list {
            padding: 0.75rem;
            border-radius: 12px;
        }

        .cat-table,
        .cat-table thead,
        .cat-table tbody,
        .cat-table tr,
        .cat-table th,
        .cat-table td {
            display: block;
        }

        .cat-table thead {
            display: none;
        }

        .cat-table tr {
            background: var(--surface);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .cat-table td {
            border: none;
            padding: 0.3rem 0 !important;
            font-size: 0.78rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.4rem;
        }

        .cat-table td::before {
            content: attr(data-label);
            font-weight: 700;
            font-size: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--text-light);
            min-width: 4rem;
            flex-shrink: 0;
        }

        .cat-table td:first-child {
            padding-top: 0 !important;
            display: block !important;
        }

        .cat-table td:first-child::before {
            display: none;
        }

        .cat-table td:last-child {
            padding-bottom: 0 !important;
        }

        .cat-table td:not(:last-child) {
            border-bottom: 1px solid var(--border-color) !important;
            padding-bottom: 0.3rem !important;
            margin-bottom: 0.3rem;
        }

        .cat-table td.category-name-cell form>div {
            gap: 0.35rem !important;
        }

        .cat-count-badge {
            font-size: 0.6rem;
            padding: 0.1rem 0.45rem;
        }

        .btn-delete-cat {
            font-size: 0.65rem;
            padding: 0.3rem 0.6rem;
        }

        .cat-list h3 {
            font-size: 0.95rem;
            margin-bottom: 0.75rem !important;
        }

        .cat-form h3 {
            font-size: 0.95rem;
            margin-bottom: 0.75rem !important;
        }

        .cat-grid {
            gap: 0.75rem;
            margin-top: 0.75rem;
        }

        .cat-table td form>div>input[name="name"] {
            font-size: 0.85rem !important;
        }

        .cat-table td form>div>textarea {
            font-size: 0.78rem !important;
        }

        .cat-table input[type="file"] {
            font-size: 0.55rem !important;
        }

        .cat-slide-grid {
            grid-template-columns: 1fr !important;
        }

        .cat-form .form-group input,
        .cat-form .form-group textarea {
            font-size: 0.8rem;
            padding: 0.4rem;
        }

        .cat-form form>div:last-of-type {
            padding: 0.65rem !important;
            margin-top: 0.65rem !important;
        }

        .cat-form form>div:last-of-type h4 {
            font-size: 0.7rem !important;
        }

        .cat-form form>div:last-of-type input[type="text"] {
            font-size: 0.65rem;
            padding: 0.3rem;
        }

        .cat-form form>div:last-of-type input[type="file"] {
            font-size: 0.55rem;
        }

        .cat-form button[type="submit"] {
            margin-top: 0.75rem !important;
            padding: 0.5rem !important;
            font-size: 0.8rem;
        }

        .cat-table button[type="submit"] {
            margin-top: 0.25rem !important;
            padding: 0.35rem 0.75rem !important;
            font-size: 0.72rem;
        }
    }
</style>

<div class="container">
    <div class="cat-header">
        <div>
            <span class="hero-kicker">System Management</span>
            <h1 class="cat-heading">Categories</h1>
        </div>
        <a href="dashboard.php" class="cat-back-btn">Back to Dashboard</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="cat-grid">
        <div class="cat-form">
            <h3>Add New Category</h3>
            <form method="POST" enctype="multipart/form-data">
                <?php csrf_field(); ?>
                <div class="form-group">
                    <label for="name">Category Name</label>
                    <input type="text" id="name" name="name" required placeholder="e.g. Footwear">
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" placeholder="Briefly describe what's in this category..."></textarea>
                </div>
                <div style="background: rgba(var(--primary-rgb), 0.04); padding: 1.25rem; border-radius: 14px; margin-top: 1.25rem;">
                    <h4 style="margin-bottom: 0.75rem; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--primary-color); font-weight: 800;">Image Slides</h4>
                    <div style="display: grid; gap: 0.85rem;">
                        <div>
                            <label style="font-size: 0.7rem; font-weight: 700; color: var(--text-light); display: block; margin-bottom: 0.25rem;">Slide 1 (Main)</label>
                            <input type="file" name="upload1" style="margin-bottom: 0.4rem;">
                            <input type="text" name="image1" placeholder="Or paste image URL..." style="font-size: 0.85rem;">
                        </div>
                        <div>
                            <label style="font-size: 0.7rem; font-weight: 700; color: var(--text-light); display: block; margin-bottom: 0.25rem;">Slide 2</label>
                            <input type="file" name="upload2" style="margin-bottom: 0.4rem;">
                            <input type="text" name="image2" placeholder="Or paste image URL..." style="font-size: 0.85rem;">
                        </div>
                        <div>
                            <label style="font-size: 0.7rem; font-weight: 700; color: var(--text-light); display: block; margin-bottom: 0.25rem;">Slide 3</label>
                            <input type="file" name="upload3" style="margin-bottom: 0.4rem;">
                            <input type="text" name="image3" placeholder="Or paste image URL..." style="font-size: 0.85rem;">
                        </div>
                    </div>
                </div>
                <button type="submit" name="add_category" class="btn-primary" style="width: 100%; margin-top: 1.5rem;">Create Category</button>
            </form>
        </div>

        <div class="cat-list">
            <h3>Existing Categories</h3>
            <?php if (count($categories) > 0): ?>
                <table class="cat-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Products</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <?php
                            // Count products in this category
                            $stmt = getDB()->prepare("SELECT COUNT(*) FROM products WHERE category_id = :category_id");
                            $stmt->execute([':category_id' => $cat['category_id']]);
                            $p_count = $stmt->fetchColumn();
                            ?>
                            <tr>
                                <td class="category-name-cell" data-label="Name">
                                    <form method="POST" enctype="multipart/form-data" style="display: block;">
                                        <?php csrf_field(); ?>
                                        <input type="hidden" name="category_id" value="<?php echo $cat['category_id']; ?>">
                                        <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                                            <input type="text" name="name" value="<?php echo htmlspecialchars($cat['name']); ?>" class="cat-inline-name">
                                            <textarea name="description" class="cat-inline-desc"><?php echo htmlspecialchars($cat['description']); ?></textarea>

                                            <div class="cat-slides-box">
                                                <?php for ($s = 1; $s <= 3; $s++): ?>
                                                    <?php $slide_val = $cat['image' . $s] ?? ''; ?>
                                                    <div class="cat-slide-row">
                                                        <span class="cat-slide-label">Slide <?php echo $s; ?></span>
                                                        <div class="cat-slide-grid">
                                                            <input type="file" name="upload<?php echo $s; ?>" style="font-size: 0.75rem;">
                                                            <input type="text" name="image<?php echo $s; ?>" value="<?php echo htmlspecialchars($slide_val); ?>" placeholder="Slide URL" style="font-size: 0.75rem; padding: 0.4rem; border-radius: 8px;">
                                                        </div>
                                                        <?php if (!empty($slide_val)): ?>
                                                            <div class="cat-slide-preview">
                                                                <div class="cat-slide-thumb">
                                                                    <img loading="lazy" src="<?php echo htmlspecialchars(get_product_image_url($slide_val)); ?>">
                                                                </div>
                                                                <button type="button" class="btn-delete-slide" onclick="deleteCategorySlide(<?php echo $cat['category_id']; ?>, <?php echo $s; ?>)">Delete</button>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endfor; ?>
                                            </div>
                                            <button type="submit" name="update_category" class="btn-primary-sm">Save Changes</button>
                                        </div>
                                    </form>
                                </td>
                                <td data-label="Products">
                                    <span class="cat-count-badge"><?php echo $p_count; ?> Items</span>
                                </td>
                                <td data-label="Action">
                                    <form method="POST" onsubmit="return confirm('Are you sure? This will permanently remove the category.')">
                                        <?php csrf_field(); ?>
                                        <input type="hidden" name="category_id" value="<?php echo $cat['category_id']; ?>">
                                        <button type="submit" name="delete_category" class="btn-delete-cat">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No categories found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<!-- 🗑️ Hidden delete category slide form -->
<form id="delete-cat-slide-form" method="POST" action="" style="display: none;">
    <?php csrf_field(); ?>
    <input type="hidden" name="action" value="delete_category_slide">
    <input type="hidden" name="category_id" id="delete-cat-id">
    <input type="hidden" name="slide_num" id="delete-cat-slide-num">
</form>

<script>
    function deleteCategorySlide(catId, slideNum) {
        if (confirm('Are you sure you want to permanently delete this category slide image? This action will immediately unlink the file from the server.')) {
            document.getElementById('delete-cat-id').value = catId;
            document.getElementById('delete-cat-slide-num').value = slideNum;
            document.getElementById('delete-cat-slide-form').submit();
        }
    }
</script>