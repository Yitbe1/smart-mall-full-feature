<?php
/**
 * Product image/video upload, deletion, and database save helpers.
 */

function upload_error_message(int $code): string
{
    $messages = [
        UPLOAD_ERR_INI_SIZE => 'The image is larger than the server upload limit.',
        UPLOAD_ERR_FORM_SIZE => 'The image is larger than the allowed form size.',
        UPLOAD_ERR_PARTIAL => 'The image was only partially uploaded. Please try again.',
        UPLOAD_ERR_NO_FILE => '',
        UPLOAD_ERR_NO_TMP_DIR => 'The server temporary upload folder is missing.',
        UPLOAD_ERR_CANT_WRITE => 'The server could not write the uploaded image.',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the image upload.',
    ];

    return $messages[$code] ?? 'Unknown upload error.';
}

function handle_main_image_upload(?array $file, ?string $existing_image, bool $is_edit, string $upload_dir): array
{
    $image_name = $existing_image ?? '';
    $error = null;

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $upload_error = $file['error'] ?? UPLOAD_ERR_OK;
        $max_size = 5 * 1024 * 1024;

        if ($upload_error !== UPLOAD_ERR_OK) {
            $error = upload_error_message($upload_error);
        } elseif ($file['size'] > $max_size) {
            $error = 'Image size must be less than 5MB';
        } else {
            if (!is_dir($upload_dir) && !mkdir($upload_dir, 0775, true)) {
                $error = 'Could not create uploads folder.';
            } elseif (!is_writable($upload_dir)) {
                $error = 'Uploads folder is not writable. Please check folder permissions.';
            } else {
                $allowed_types = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                ];

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
                if ($finfo) {
                    finfo_close($finfo);
                }

                if (!isset($allowed_types[$mime_type])) {
                    $error = 'Invalid image type. Allowed: JPEG, PNG, GIF, WebP';
                } else {
                    $safe_base = preg_replace('/[^a-zA-Z0-9_-]+/', '-', pathinfo($file['name'], PATHINFO_FILENAME));
                    $safe_base = trim($safe_base, '-');
                    if ($safe_base === '') {
                        $safe_base = 'product';
                    }

                    $image_name = 'product_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '_' . $safe_base . '.' . $allowed_types[$mime_type];
                    $upload_path = rtrim($upload_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $image_name;

                    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                        $error = 'Failed to upload image. Please check uploads folder permissions.';
                    } else {
                        if ($is_edit && !empty($existing_image) && $image_name !== $existing_image) {
                            $old_path = rtrim($upload_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $existing_image;
                            if (file_exists($old_path)) {
                                unlink($old_path);
                            }
                        }
                    }
                }
            }
        }
    }

    return ['filename' => $image_name, 'error' => $error];
}

function handle_additional_images_upload(?array $slide2, ?array $slide3, ?array $bulk, array $existing_names, string $upload_dir): array
{
    $additional_images_names = $existing_names;

    // Explicit Slide 2 & 3
    $upload_keys = ['slide2', 'slide3'];
    foreach ($upload_keys as $idx => $key) {
        $file = ($key === 'slide2') ? $slide2 : $slide3;
        if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
            if (isset($allowed[$mime_type])) {
                $img_name = 'product_gallery_' . date('YmdHis') . '_' . bin2hex(random_bytes(2)) . '.' . $allowed[$mime_type];
                if (move_uploaded_file($file['tmp_name'], $upload_dir . DIRECTORY_SEPARATOR . $img_name)) {
                    $additional_images_names[$idx] = $img_name;
                }
            }
        }
    }

    // Bulk gallery appends to the rest
    if (isset($bulk) && !empty($bulk['name'][0])) {
        $file_count = min(count($bulk['name']), 4);
        for ($i = 0; $i < $file_count; $i++) {
            if ($bulk['error'][$i] === UPLOAD_ERR_OK) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $bulk['tmp_name'][$i]);
                finfo_close($finfo);

                $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
                if (isset($allowed[$mime_type])) {
                    $img_name = 'product_bulk_' . date('YmdHis') . '_' . bin2hex(random_bytes(2)) . '.' . $allowed[$mime_type];
                    if (move_uploaded_file($bulk['tmp_name'][$i], $upload_dir . DIRECTORY_SEPARATOR . $img_name)) {
                        $additional_images_names[] = $img_name;
                    }
                }
            }
        }
    }

    return $additional_images_names;
}

function handle_video_upload(?array $file, ?string $existing_video, string $upload_dir): ?string
{
    $video_name = $existing_video;

    if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
        if ($finfo) finfo_close($finfo);

        $allowed_vid_types = ['video/mp4' => 'mp4', 'video/webm' => 'webm'];
        if (isset($allowed_vid_types[$mime_type]) && $file['size'] <= 50 * 1024 * 1024) {
            $safe_base = 'video_' . date('YmdHis') . '_' . bin2hex(random_bytes(2));
            $vid_name = $safe_base . '.' . $allowed_vid_types[$mime_type];
            $upload_path = rtrim($upload_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $vid_name;
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $video_name = $vid_name;
            }
        }
    }

    return $video_name;
}

function delete_product_file(string $filename, string $upload_dir): void
{
    if ($filename === '') {
        return;
    }

    $path = rtrim($upload_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
    if (file_exists($path)) {
        unlink($path);
    }
}

function save_product(PDO $pdo, array $data, bool $is_edit, ?int $product_id = null): int
{
    if ($is_edit) {
        $stmt = $pdo->prepare("
            UPDATE products
            SET name = :name, description = :description, price = :price,
                stock = :stock, image = :image, category_id = :category_id,
                additional_images = :additional_images, video = :video
            WHERE product_id = :product_id
        ");
        $stmt->execute([
            ':name'        => $data[':name'],
            ':description' => $data[':description'],
            ':price'       => $data[':price'],
            ':stock'       => $data[':stock'],
            ':image'       => $data[':image'],
            ':category_id' => $data[':category_id'],
            ':additional_images' => $data[':additional_images'],
            ':video'       => $data[':video'],
            ':product_id'  => $product_id,
        ]);
        return $product_id;
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO products (name, description, price, stock, image, category_id, additional_images, video)
            VALUES (:name, :description, :price, :stock, :image, :category_id, :additional_images, :video)
        ");
        $stmt->execute([
            ':name'        => $data[':name'],
            ':description' => $data[':description'],
            ':price'       => $data[':price'],
            ':stock'       => $data[':stock'],
            ':image'       => $data[':image'],
            ':category_id' => $data[':category_id'],
            ':additional_images' => $data[':additional_images'],
            ':video'       => $data[':video'],
        ]);
        return (int)$pdo->lastInsertId();
    }
}
