<?php
$page_title = 'Confirm Admin Promotion - Smart Mall';
require_once __DIR__ . '/config.php';

$message = '';
$success = false;
$demoted_user_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['demote_user_id'])) {
    try {
        $pdo = getDB();
        $demote_id = (int)$_POST['demote_user_id'];
        $stmt = $pdo->prepare("SELECT user_id, name, email, role FROM users WHERE user_id = :id");
        $stmt->execute([':id' => $demote_id]);
        $demote_user = $stmt->fetch();
        if ($demote_user && $demote_user['role'] === 'admin') {
            $pdo->prepare("UPDATE users SET role = 'customer' WHERE user_id = :id")
                ->execute([':id' => $demote_id]);
            $demoted_user_id = $demote_id;
            $message = $demote_user['name'] . ' has been demoted to Customer.';
            $success = true;
        } else {
            $message = 'User not found or not an admin.';
        }
    } catch (PDOException $e) {
        error_log("Admin demotion error: " . $e->getMessage());
        $message = 'Something went wrong. Please try again.';
    }
}

$token = $_GET['token'] ?? '';

if ($demoted_user_id) {
    // demotion was just handled above, skip token processing
} elseif (empty($token)) {
    $message = 'Invalid confirmation link.';
} else {
    try {
        $pdo = getDB();
        $token_hash = hash('sha256', $token);

        $stmt = $pdo->prepare("
            SELECT apt.id, apt.admin_user_id, apt.target_user_id, apt.expires_at,
                   apt.expires_at < NOW() AS expired,
                   a.name AS admin_name, a.email AS admin_email,
                   u.name AS target_name, u.email AS target_email, u.role AS target_role
            FROM admin_promotion_tokens apt
            JOIN users a ON a.user_id = apt.admin_user_id
            JOIN users u ON u.user_id = apt.target_user_id
            WHERE apt.token = :token AND apt.completed = 0
        ");
        $stmt->execute([':token' => $token_hash]);
        $row = $stmt->fetch();

        if ($row) {
            if ($row['expired']) {
                $pdo->prepare("UPDATE admin_promotion_tokens SET completed = 1 WHERE id = :id")
                    ->execute([':id' => $row['id']]);
                $message = 'This confirmation link has expired. Please request a new promotion from the admin panel.';
            } elseif ($row['target_role'] === 'admin') {
                $pdo->prepare("UPDATE admin_promotion_tokens SET completed = 1 WHERE id = :id")
                    ->execute([':id' => $row['id']]);
                $message = 'This user is already an admin.';
            } elseif (($_GET['action'] ?? '') === 'decline') {
                $pdo->prepare("UPDATE admin_promotion_tokens SET completed = 1 WHERE id = :id")
                    ->execute([':id' => $row['id']]);

                $success = true;
                $message = 'Admin promotion for ' . $row['target_name'] . ' has been declined.';

                require_once __DIR__ . '/helpers/mail.php';
                send_mail($row['admin_email'], 'Smart Mall - Admin Promotion Declined',
                    "Hello {$row['admin_name']},\n\nThe admin promotion request for {$row['target_name']} ({$row['target_email']}) has been declined.\n\n- Smart Mall Team",
                    null, 'admin_promo_declined_' . $row['id'],
                    email_html_template(
                        '<h2 style="margin:0 0 15px;color:#111;font-size:20px;">Promotion Declined</h2>' .
                        '<p style="margin:0 0 15px;">Hello <strong>' . e($row['admin_name']) . '</strong>,</p>' .
                        '<p style="margin:0 0 15px;">The admin promotion request for <strong>' . e($row['target_name']) . '</strong> (' . e($row['target_email']) . ') has been <strong style="color:#dc2626;">declined</strong>.</p>' .
                        '<hr style="border:none;border-top:1px solid #eee;margin:20px 0;">' .
                        '<p style="margin:0;color:#888;font-size:13px;">- Smart Mall Team</p>'
                    ));
            } else {
                $pdo->prepare("UPDATE users SET role = 'admin' WHERE user_id = :id")
                    ->execute([':id' => $row['target_user_id']]);
                $pdo->prepare("UPDATE admin_promotion_tokens SET completed = 1 WHERE id = :id")
                    ->execute([':id' => $row['id']]);

                $success = true;
                $promoted_user_id = (int)$row['target_user_id'];
                $message = $row['target_name'] . ' has been promoted to Admin successfully!';

                require_once __DIR__ . '/helpers/mail.php';

                $notification_link = rtrim($base_url, '/') . '/admin/manage_users.php';

                send_mail($row['admin_email'], 'Smart Mall - Admin Promotion Confirmed',
                    "Hello {$row['admin_name']},\n\n{$row['target_name']} ({$row['target_email']}) has been promoted to Admin successfully.\n\nYou can manage users here:\n$notification_link\n\n- Smart Mall Team",
                    null, 'admin_promo_notify_' . $row['id'],
                    email_html_template(
                        '<h2 style="margin:0 0 15px;color:#111;font-size:20px;">Promotion Confirmed</h2>' .
                        '<p style="margin:0 0 15px;">Hello <strong>' . e($row['admin_name']) . '</strong>,</p>' .
                        '<p style="margin:0 0 15px;"><strong>' . e($row['target_name']) . '</strong> (' . e($row['target_email']) . ') has been promoted to <strong>Admin</strong> successfully.</p>' .
                        '<p style="margin:0 0 15px;text-align:center;"><a href="' . e($notification_link) . '" style="display:inline-block;background:linear-gradient(135deg,#2563eb,#1e40af);color:#fff;text-decoration:none;padding:14px 28px;border-radius:8px;font-size:16px;font-weight:700;">Manage Users</a></p>' .
                        '<hr style="border:none;border-top:1px solid #eee;margin:20px 0;">' .
                        '<p style="margin:0;color:#888;font-size:13px;">- Smart Mall Team</p>'
                    ));

                send_mail($row['target_email'], 'Smart Mall - You are now an Admin!',
                    "Hello {$row['target_name']},\n\nCongratulations! You have been promoted to Admin at Smart Mall.\n\nYou now have access to the admin dashboard:\n$notification_link\n\n- Smart Mall Team",
                    null, 'admin_promo_notified_' . $row['id'],
                    email_html_template(
                        '<h2 style="margin:0 0 15px;color:#111;font-size:20px;">You are now an Admin!</h2>' .
                        '<p style="margin:0 0 15px;">Hello <strong>' . e($row['target_name']) . '</strong>,</p>' .
                        '<p style="margin:0 0 15px;">Congratulations! You have been promoted to <strong>Admin</strong> at Smart Mall.</p>' .
                        '<p style="margin:0 0 15px;">You now have access to the admin dashboard:</p>' .
                        '<p style="margin:0 0 15px;text-align:center;"><a href="' . e($notification_link) . '" style="display:inline-block;background:linear-gradient(135deg,#2563eb,#1e40af);color:#fff;text-decoration:none;padding:14px 28px;border-radius:8px;font-size:16px;font-weight:700;">Go to Admin Dashboard</a></p>' .
                        '<hr style="border:none;border-top:1px solid #eee;margin:20px 0;">' .
                        '<p style="margin:0;color:#888;font-size:13px;">- Smart Mall Team</p>'
                    ));
            }
        } else {
            $message = 'Invalid or expired confirmation link. The promotion may have already been completed.';
        }
    } catch (PDOException $e) {
        error_log("Admin promotion confirmation error: " . $e->getMessage());
        $message = 'Something went wrong. Please try again.';
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<style>
    .auth-wrapper {
        min-height: auto;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1rem;
        margin-top: 2rem;
    }
    .auth-card {
        width: 100%;
        max-width: 440px;
        background: var(--surface);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-lg);
        overflow: hidden;
        border-radius: var(--radius);
    }
    .auth-card-header {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        padding: 1.25rem;
        text-align: center;
        color: #fff;
    }
    .auth-card-header h1 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.5rem;
        margin-bottom: 0.25rem;
        color: #fff;
        font-weight: 800;
    }
    .auth-card-body {
        padding: 1.25rem;
        text-align: center;
    }
    .result-box {
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 4px;
        font-size: 0.9rem;
        line-height: 1.6;
    }
    .result-box.success {
        background: #ecfdf5;
        border-left: 4px solid var(--success-color);
        color: #065f46;
    }
    .result-box.error {
        background: #fef2f2;
        border-left: 4px solid var(--danger-color);
        color: #991b1b;
    }
    .btn-continue {
        display: inline-block;
        padding: 0.75rem 2rem;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: #fff;
        border: none;
        font-size: 1rem;
        font-weight: 800;
        cursor: pointer;
        text-decoration: none;
        border-radius: var(--radius);
        transition: opacity 0.2s, transform 0.2s;
    }
    .btn-continue:hover {
        opacity: 0.92;
        transform: translateY(-1px);
    }
    .btn-demote {
        display: inline-block;
        padding: 0.4rem 1rem;
        background: transparent;
        color: #dc2626;
        border: 1.5px solid #dc2626;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        border-radius: var(--radius);
        transition: background 0.2s, color 0.2s;
    }
    .btn-demote:hover {
        background: #dc2626;
        color: #fff;
    }
    @media (min-width: 481px) {
        .auth-wrapper { min-height: 50vh; }
    }
</style>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-card-header">
            <h1>Admin Promotion</h1>
            <p><?php echo $demoted_user_id ? 'User demoted' : ($success ? 'Promotion completed' : 'Confirmation failed'); ?></p>
        </div>
        <div class="auth-card-body">
            <div class="result-box <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php if (!empty($promoted_user_id)): ?>
                <form method="post" style="margin-bottom:10px;">
                    <input type="hidden" name="demote_user_id" value="<?php echo $promoted_user_id; ?>">
                    <button type="submit" class="btn-demote" onclick="return confirm('Demote this user back to Customer?');">Demote this User</button>
                </form>
            <?php endif; ?>
            <a href="admin/manage_users.php" class="btn-continue">Go to Admin Panel →</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
