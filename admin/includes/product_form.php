<?php

function render_product_form(?array $product, array $categories, array $errors, bool $is_edit, ?array $current_additional = null): void
{
    $additional_images_names = $current_additional ?? (json_decode($product['additional_images'] ?? '[]', true) ?: []);
    ?>
<style>
    .form-container {
        max-width: 760px;
        margin: 2rem auto;
        background: var(--surface);
        padding: 2.5rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border-color);
    }

    .form-container h2 {
        color: var(--text-dark);
        margin-bottom: 0.5rem;
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
    }

    .form-intro {
        color: var(--text-light);
        margin-bottom: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 700;
        color: var(--text-dark);
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 0.85rem;
        border: 1.5px solid var(--border-color);
        border-radius: 8px;
        font-size: 1rem;
        font-family: inherit;
        background: var(--bg-light);
        color: var(--text-dark);
        transition: all 0.2s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 130px;
    }

    .form-error {
        color: var(--danger-color);
        font-size: 0.9rem;
        margin-top: 0.35rem;
        font-weight: 700;
    }

    .form-group input.error,
    .form-group textarea.error {
        border-color: var(--danger-color);
    }

    .image-preview {
        margin-top: 1rem;
    }

    .image-preview img {
        max-width: 220px;
        max-height: 220px;
        border: 1px solid var(--border-color);
        object-fit: cover;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    .btn-submit,
    .btn-cancel {
        flex: 1;
        padding: 0.95rem;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 800;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
    }

    .btn-submit {
        background-color: var(--primary-color);
        color: white !important;
    }

    .btn-cancel {
        background: var(--surface);
        color: var(--text-dark) !important;
        border: 1.5px solid var(--border-color);
    }

    .form-hint {
        color: var(--text-light);
        font-size: 0.86rem;
        margin-top: 0.35rem;
    }

    .back-btn {
        padding: 0.6rem 1.2rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.85rem;
        text-decoration: none;
        border: 1.5px solid var(--input-border);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        background: var(--surface);
        color: var(--text-dark);
        transition: all 0.25s;
    }

    .back-btn:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
        background: var(--primary-light);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
    }

    @media (max-width: 520px) {
        .form-container {
            margin: 1rem 0;
            padding: 1.4rem;
        }

        .form-actions {
            flex-direction: column;
        }
    }

    @media (max-width: 480px) {
        .container {
            padding-bottom: 0.5rem;
        }

        .form-container {
            padding: 1rem;
        }

        .form-container h2 {
            font-size: 1.2rem;
        }

        .form-intro {
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            padding: 0.65rem;
            font-size: 0.85rem;
        }

        .image-grid-2col {
            grid-template-columns: 1fr !important;
        }

        .video-preview-card {
            flex-direction: column;
            align-items: flex-start;
            max-width: 100%;
        }

        .video-preview-card video {
            width: 100%;
        }

        .btn-submit,
        .btn-cancel {
            padding: 0.75rem;
            font-size: 0.9rem;
        }
    }

    /* ── High-End Media Previews & Overlay Deletes ── */
    .media-preview-container {
        position: relative;
        display: inline-block;
        margin-top: 0.75rem;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid var(--border-color);
        box-shadow: var(--shadow-md);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        background: var(--bg-light);
    }

    .media-preview-container:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
        border-color: var(--primary-color);
    }

    .media-preview-container img {
        display: block;
        max-width: 150px;
        max-height: 150px;
        width: auto;
        height: auto;
        object-fit: cover;
    }

    .delete-btn-overlay {
        position: absolute;
        top: 8px;
        right: 8px;
        background: rgba(220, 38, 38, 0.95);
        color: #fff;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        opacity: 0.85;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    }

    .delete-btn-overlay:hover {
        opacity: 1;
        transform: scale(1.1) rotate(9deg);
        background: rgb(220, 38, 38);
    }

    /* Video Preview Design */
    .video-preview-card {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        background: var(--bg-light);
        border: 1.5px solid var(--border-color);
        padding: 1rem;
        border-radius: 12px;
        margin-top: 0.75rem;
        max-width: 450px;
    }

    .video-preview-card video {
        border-radius: 8px;
        background: #000;
        object-fit: cover;
    }

    .btn-delete-video {
        background: rgba(220, 38, 38, 0.1);
        color: var(--danger-color);
        border: 1px solid rgba(220, 38, 38, 0.2);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.85rem;
    }

    .btn-delete-video:hover {
        background: var(--danger-color);
        color: #fff;
    }
</style>

<div class="container" style="width: min(1300px, calc(100% - 32px)); padding-top: 4rem;">
    <div style="text-align:center; padding-bottom:0.5rem;">
        <h2 style="font-size:2.25rem; font-weight:700; font-family:'Outfit',sans-serif; margin:0; letter-spacing:-0.5px; background:linear-gradient(135deg, var(--text-dark) 0%, var(--primary-color) 100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; filter:drop-shadow(0 2px 4px rgba(0,0,0,0.1)); position:relative;">
            <?php echo $is_edit ? 'Edit Product' : 'Add New Product'; ?>
        </h2>
        <span style="display:block; width:80px; height:4px; background:linear-gradient(90deg, var(--primary-color), transparent); border-radius:2px; margin:0.5rem auto 0;"></span>
    </div>
    <div style="display:flex; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:0.5rem;">
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>
    <div class="form-container">
        <p class="form-intro">Upload a product image and add clear details so customers can shop confidently.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>Please fix the following errors:</strong>
                <ul style="margin-top: 0.5rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <?php csrf_field(); ?>
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category_id">
                    <option value="">— Select a category —</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category_id']; ?>"
                            <?php echo (int)($_POST['category_id'] ?? $product['category_id'] ?? 0) === (int)$cat['category_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="name">Product Name *</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?php echo htmlspecialchars($_POST['name'] ?? $product['name'] ?? ''); ?>"
                    class="<?php echo isset($errors['name']) ? 'error' : ''; ?>"
                    required>
                <?php if (isset($errors['name'])): ?>
                    <div class="form-error"><?php echo htmlspecialchars($errors['name']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="description">Description *</label>
                <textarea
                    id="description"
                    name="description"
                    class="<?php echo isset($errors['description']) ? 'error' : ''; ?>"
                    required><?php echo htmlspecialchars($_POST['description'] ?? $product['description'] ?? ''); ?></textarea>
                <?php if (isset($errors['description'])): ?>
                    <div class="form-error"><?php echo htmlspecialchars($errors['description']); ?></div>
                <?php endif; ?>
                <div class="form-hint">Include useful words like fashion, phone, furniture, skincare, makeup, kitchenware, etc. Category cards use these words for filtering.</div>
            </div>

            <div class="form-group">
                <label for="price">Price (USD) *</label>
                <input
                    type="number"
                    id="price"
                    name="price"
                    step="0.01"
                    min="0"
                    value="<?php echo htmlspecialchars($_POST['price'] ?? $product['price'] ?? ''); ?>"
                    class="<?php echo isset($errors['price']) ? 'error' : ''; ?>"
                    required>
                <?php if (isset($errors['price'])): ?>
                    <div class="form-error"><?php echo htmlspecialchars($errors['price']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="stock">Stock Quantity *</label>
                <input
                    type="number"
                    id="stock"
                    name="stock"
                    min="0"
                    value="<?php echo htmlspecialchars($_POST['stock'] ?? $product['stock'] ?? '0'); ?>"
                    class="<?php echo isset($errors['stock']) ? 'error' : ''; ?>"
                    required>
                <?php if (isset($errors['stock'])): ?>
                    <div class="form-error"><?php echo htmlspecialchars($errors['stock']); ?></div>
                <?php endif; ?>
            </div>

            <div style="background: rgba(var(--primary-rgb), 0.05); padding: 1.5rem; border-radius: 16px; margin-bottom: 2rem; border: 1px dashed var(--border-color);">
                <h4 style="margin-bottom: 1.5rem; font-family: 'Outfit', sans-serif; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--primary-color);">Product Imagery (3-Angle Sequence)</h4>

                <div class="form-group">
                    <label for="image">1. Main Cover Image *</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <?php if ($is_edit && !empty($product['image'])): ?>
                        <div class="image-preview">
                            <div class="media-preview-container">
                                <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="Cover">
                                <button type="button" class="delete-btn-overlay" onclick="deleteMedia('cover')" title="Delete cover image">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="image-grid-2col" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>2. Secondary Angle</label>
                        <input type="file" name="slide2" accept="image/*">
                        <?php if ($is_edit && !empty($additional_images_names[0])): ?>
                            <div class="image-preview" style="margin-top: 0.5rem;">
                                <div class="media-preview-container" style="max-width: 120px;">
                                    <img src="../uploads/<?php echo htmlspecialchars($additional_images_names[0]); ?>" alt="Angle 2" style="max-width: 120px;">
                                    <button type="button" class="delete-btn-overlay" style="width: 26px; height: 26px; top: 4px; right: 4px;" onclick="deleteMedia('slide2')" title="Delete secondary image">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>3. Detail/lifestyle Angle</label>
                        <input type="file" name="slide3" accept="image/*">
                        <?php if ($is_edit && !empty($additional_images_names[1])): ?>
                            <div class="image-preview" style="margin-top: 0.5rem;">
                                <div class="media-preview-container" style="max-width: 120px;">
                                    <img src="../uploads/<?php echo htmlspecialchars($additional_images_names[1]); ?>" alt="Angle 3" style="max-width: 120px;">
                                    <button type="button" class="delete-btn-overlay" style="width: 26px; height: 26px; top: 4px; right: 4px;" onclick="deleteMedia('slide3')" title="Delete detail image">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="form-group" style="border-top: 1px solid var(--border-color); padding-top: 2rem;">
                <label for="additional_images">Extended Gallery (Bulk Upload)</label>
                <input
                    type="file"
                    id="additional_images"
                    name="additional_images[]"
                    multiple
                    accept="image/*">
                <div class="form-hint">Select up to 4 more images for deep product discovery. These appear after your 3 main angles.</div>

                <?php
                $all_gallery = json_decode($product['additional_images'] ?? '[]', true);
                $extended_gallery = array_slice($all_gallery, 2);
                if ($is_edit && !empty($extended_gallery)):
                ?>
                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-top: 1rem;">
                        <?php foreach ($extended_gallery as $img): ?>
                            <?php if (!empty($img)): ?>
                                <div class="media-preview-container" style="border-radius: 8px;">
                                    <img src="<?php echo htmlspecialchars(get_product_image_url($img)); ?>" alt="Gallery item" style="max-width: 80px; max-height: 80px;">
                                    <button type="button" class="delete-btn-overlay" style="width: 24px; height: 24px; top: 4px; right: 4px;" onclick="deleteMedia('gallery', '<?php echo addslashes($img); ?>')" title="Delete gallery image">
                                        <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="video">Product Video (Optional)</label>
                <input
                    type="file"
                    id="video"
                    name="video"
                    accept="video/mp4,video/webm">
                <div class="form-hint">Max 50MB. Accepted formats: MP4, WebM. Only 1 video allowed per product.</div>

                <?php if ($is_edit && !empty($product['video'])): ?>
                    <div class="video-preview-card">
                        <video width="140" height="80" controls>
                            <source src="<?php echo htmlspecialchars(get_product_video_url($product['video'])); ?>">
                            Your browser does not support the video tag.
                        </video>
                        <div style="flex: 1;">
                            <div style="font-weight: 700; font-size: 0.9rem; color: var(--text-dark); margin-bottom: 0.35rem;">Attached Video</div>
                            <button type="button" class="btn-delete-video" onclick="deleteMedia('video')">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Remove Video
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <?php echo $is_edit ? 'Update Product' : 'Add Product'; ?>
                </button>
                <a href="dashboard.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<form id="delete-media-form" method="POST" action="" style="display: none;">
    <?php csrf_field(); ?>
    <input type="hidden" name="action" value="delete_media">
    <input type="hidden" name="media_type" id="delete-media-type">
    <input type="hidden" name="filename" id="delete-media-filename">
</form>

<script>
    function deleteMedia(type, filename = '') {
        if (confirm('Are you sure you want to permanently delete this media? This action will immediately unlink the file from the server.')) {
            document.getElementById('delete-media-type').value = type;
            document.getElementById('delete-media-filename').value = filename;
            document.getElementById('delete-media-form').submit();
        }
    }
</script>
<?php
}
