<?php
$page_title = 'Manage Users - Smart Mall';
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once __DIR__ . '/../helpers/mail.php';
$users = [];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $pdo = getDB();
    try {
        if (isset($_POST['verify_user'])) {
            $user_id = (int)$_POST['user_id'];
            $stmt = $pdo->prepare("UPDATE users SET email_verified_at = NOW() WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $success = "User #$user_id verified successfully";
        } elseif (isset($_POST['unverify_user'])) {
            $user_id = (int)$_POST['user_id'];
            $stmt = $pdo->prepare("UPDATE users SET email_verified_at = NULL WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $success = "User #$user_id unverified";
        } elseif (isset($_POST['delete_user'])) {
            $user_id = (int)$_POST['user_id'];
            if ($user_id === (int)$_SESSION['user_id']) {
                $error = "Cannot delete your own account";
            } else {
                $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $success = "User #$user_id deleted";
            }
        } elseif (isset($_POST['toggle_admin'])) {
            $user_id = (int)$_POST['user_id'];
            if ($user_id === (int)$_SESSION['user_id']) {
                $error = "Cannot change your own role";
            } else {
                $stmt = $pdo->prepare("SELECT role, name, email FROM users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $target = $stmt->fetch();

                if (!$target) {
                    $error = "User not found";
                } elseif ($target['role'] === 'admin') {
                    $stmt = $pdo->prepare("UPDATE users SET role = 'customer' WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    $success = "User #$user_id demoted to customer";
                } else {
                    $token = bin2hex(random_bytes(32));
                    $token_hash = hash('sha256', $token);

                    $stmt = $pdo->prepare("INSERT INTO admin_promotion_tokens (admin_user_id, target_user_id, token, expires_at) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))");
                    $stmt->execute([(int)$_SESSION['user_id'], $user_id, $token_hash]);

                    $root_url = rtrim($base_url, '/');
                    $root_dir = dirname($root_url);
                    if (basename($root_url) === 'admin') {
                        $root_url = $root_dir;
                    }
                    if ($root_url === '' || $root_url === '/') {
                        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https://' : 'http://';
                        $root_url = $protocol . ($_SERVER['SERVER_NAME'] ?? 'localhost');
                    }
                    $confirm_link = rtrim($root_url, '/') . '/confirm_admin.php?token=' . $token;

                    $admin_name = $_SESSION['user_name'] ?? 'Admin';
                    $target_name = e($target['name']);
                    $target_email = e($target['email']);

                    $owner_email = $_ENV['SMTP_FROM'] ?? $_SESSION['user_email'] ?? '';
                    $decline_link = rtrim($root_url, '/') . '/confirm_admin.php?token=' . $token . '&action=decline';
                    $sent = send_mail($owner_email, 'Smart Mall - Confirm Admin Promotion',
                        "Hello $admin_name,\n\nYou requested to promote $target_name ($target_email) to Admin.\n\nConfirm: $confirm_link\nDecline: $decline_link\n\nThis link expires in 30 minutes.\n\nIf you did not request this, please ignore this email.\n\n- Smart Mall Team",
                        null, $token_hash,
                        email_html_template(
                            '<h2 style="margin:0 0 15px;color:#111;font-size:20px;">Confirm Admin Promotion</h2>' .
                            '<p style="margin:0 0 15px;">Hello <strong>' . e($admin_name) . '</strong>,</p>' .
                            '<p style="margin:0 0 15px;">You requested to promote <strong>' . $target_name . '</strong> (' . $target_email . ') to <strong>Admin</strong>.</p>' .
                            '<p style="margin:0 0 15px;">Click the button below to confirm:</p>' .
                            '<p style="margin:0 0 15px;text-align:center;"><a href="' . e($confirm_link) . '" style="display:inline-block;background:linear-gradient(135deg,#2563eb,#1e40af);color:#fff;text-decoration:none;padding:14px 28px;border-radius:8px;font-size:16px;font-weight:700;">Confirm Promotion</a></p>' .
                            '<p style="margin:0 0 15px;text-align:center;margin-top:8px;"><a href="' . e($decline_link) . '" style="display:inline-block;background:#dc2626;color:#fff;text-decoration:none;padding:10px 24px;border-radius:8px;font-size:14px;font-weight:600;">Decline</a></p>' .
                            '<p style="margin:0 0 15px;">Or paste this link in your browser:</p>' .
                            '<p style="margin:0 0 15px;word-break:break-all;color:#2563eb;font-size:13px;">' . e($confirm_link) . '</p>' .
                            '<p style="margin:0 0 15px;color:#888;font-size:13px;">This link expires in 30 minutes.</p>' .
                            '<hr style="border:none;border-top:1px solid #eee;margin:20px 0;">' .
                            '<p style="margin:0;color:#888;font-size:13px;">If you did not request this, please ignore this email.</p>'
                        ));

                    if ($sent) {
                        $success = "A confirmation email has been sent to your email. Click the link to complete the promotion of $target_name.";
                    } else {
                        $error = "Failed to send confirmation email. Please try again.";
                        $pdo->prepare("DELETE FROM admin_promotion_tokens WHERE token = ?")->execute([$token_hash]);
                    }
                }
            }
        }
    } catch (PDOException $e) {
        error_log("Admin manage_users error: " . $e->getMessage());
        $error = "Database error. Please try again.";
    }
}

try {
    $pdo = getDB();
    $stmt = $pdo->query("
        SELECT u.*,
            (SELECT COUNT(*) FROM orders WHERE user_id = u.user_id) as order_count,
            (SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE user_id = u.user_id) as total_spent
        FROM users u
        ORDER BY u.created_at DESC
    ");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Admin manage_users error: " . $e->getMessage());
    $error = "Database error. Please try again.";
}
include __DIR__ . '/../includes/header.php';
?>

<style>
    .users-table {
        width: 100%;
        border-collapse: collapse;
        background: var(--surface);
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: var(--shadow-md);
        margin-top: 2rem;
        border: 1px solid var(--border-color);
    }

    .users-table thead {
        background-color: var(--primary-color);
        color: white;
    }

    .users-table th {
        padding: 1.25rem;
        text-align: left;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.05em;
    }

    .users-table td {
        padding: 1rem 1.25rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-dark);
        vertical-align: middle;
    }

    .btn-sm {
        padding: 0.45rem 0.85rem;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 700;
        font-size: 0.8rem;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-sm:hover {
        transform: translateY(-1px);
    }

    .btn-verify {
        background: #dcfce7;
        color: #166534;
    }

    .btn-verify:hover {
        background: #166534;
        color: #fff;
    }

    .btn-unverify {
        background: #fef3c7;
        color: #92400e;
    }

    .btn-unverify:hover {
        background: #92400e;
        color: #fff;
    }

    .btn-toggle-admin {
        background: #e0f2fe;
        color: #075985;
    }

    .btn-toggle-admin:hover {
        background: #075985;
        color: #fff;
    }

    .btn-delete-user {
        background: #fee2e2;
        color: #991b1b;
    }

    .btn-delete-user:hover {
        background: #991b1b;
        color: #fff;
    }

    .badge {
        display: inline-block;
        padding: 0.25rem 0.65rem;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .badge-admin {
        background: #e0f2fe;
        color: #075985;
    }

    .badge-customer {
        background: #f3f4f6;
        color: #6b7280;
    }

    .badge-verified {
        background: #dcfce7;
        color: #166534;
    }

    .badge-unverified {
        background: #fee2e2;
        color: #991b1b;
    }

    [data-theme='dark'] .badge-admin {
        background: #082f49;
        color: #38bdf8;
    }

    [data-theme='dark'] .badge-customer {
        background: #1f2937;
        color: #9ca3af;
    }

    [data-theme='dark'] .badge-verified {
        background: #064e3b;
        color: #34d399;
    }

    [data-theme='dark'] .badge-unverified {
        background: #450a0a;
        color: #f87171;
    }

    [data-theme='dark'] .btn-verify {
        background: #064e3b;
        color: #34d399;
    }

    [data-theme='dark'] .btn-verify:hover {
        background: #34d399;
        color: #064e3b;
    }

    [data-theme='dark'] .btn-unverify {
        background: #451a03;
        color: #fbbf24;
    }

    [data-theme='dark'] .btn-unverify:hover {
        background: #fbbf24;
        color: #451a03;
    }

    [data-theme='dark'] .btn-toggle-admin {
        background: #082f49;
        color: #38bdf8;
    }

    [data-theme='dark'] .btn-toggle-admin:hover {
        background: #38bdf8;
        color: #082f49;
    }

    [data-theme='dark'] .btn-delete-user {
        background: #450a0a;
        color: #f87171;
    }

    [data-theme='dark'] .btn-delete-user:hover {
        background: #f87171;
        color: #450a0a;
    }

    .action-group {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .action-group form {
        width: 100%;
    }

    .action-group .btn-sm {
        width: 100%;
    }

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: var(--radius);
    }

    @media (max-width: 768px) {
        .users-table {
            min-width: 750px;
            font-size: 0.85rem;
        }

        .users-table th,
        .users-table td {
            padding: 0.75rem 0.6rem;
        }
    }

    @media (max-width: 480px) {
        .container {
            padding-bottom: 0.5rem;
        }

        .users-table {
            min-width: unset;
            border-collapse: separate;
            border-spacing: 0;
            box-shadow: none;
            background: transparent;
            border: none;
        }

        .users-table thead {
            display: none;
        }

        .users-table tbody tr {
            display: block;
            background: var(--surface);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            box-shadow: var(--shadow-sm);
        }

        .users-table tbody tr td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.35rem 0 !important;
            border: none;
            font-size: 0.8rem;
            gap: 0.5rem;
        }

        .users-table tbody tr td:before {
            content: attr(data-label);
            font-weight: 700;
            font-size: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-light);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .users-table tbody tr td:not(:last-child) {
            border-bottom: 1px solid var(--border-color) !important;
            padding-bottom: 0.5rem !important;
            margin-bottom: 0.35rem;
        }

        .action-group {
            width: 100%;
            flex-wrap: wrap;
        }

        .action-group form {
            flex: 1;
        }

        .action-group .btn-sm {
            width: 100%;
            text-align: center;
        }
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

    .search-box {
        position: relative;
    }

    .search-box .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.9rem;
        opacity: 0.45;
        pointer-events: none;
        transition: opacity 0.2s;
    }

    .search-box:focus-within .search-icon {
        opacity: 0.8;
    }

    .search-box input {
        padding: 0.65rem 2.2rem 0.65rem 2.1rem;
        border-radius: 12px;
        border: 1.5px solid var(--primary-color);
        font-size: 0.85rem;
        background: var(--input-bg);
        color: var(--text-dark);
        min-width: 220px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .search-box input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .search-box .clear-search {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%) scale(0);
        background: var(--border-color);
        border: none;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 0.65rem;
        line-height: 20px;
        text-align: center;
        cursor: pointer;
        color: var(--text-light);
        transition: transform 0.2s, opacity 0.2s;
        opacity: 0;
        padding: 0;
    }

    .search-box .clear-search.visible {
        transform: translateY(-50%) scale(1);
        opacity: 1;
    }

    .search-box .clear-search:hover {
        background: var(--text-light);
        color: var(--surface);
    }

    .no-results {
        display: none;
        text-align: center;
        padding: 4rem 2rem;
        background: var(--surface);
        border-radius: var(--radius);
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-color);
        margin-top: 1.5rem;
    }

    .no-results.show {
        display: block;
        animation: fadeIn 0.2s ease-out;
    }

    .no-results h3 {
        color: var(--text-dark);
        margin: 0 0 0.5rem;
    }

    .no-results p {
        color: var(--text-light);
        margin: 0;
    }

    .users-table tbody tr {
        transition: opacity 0.15s;
    }

    .users-table tbody tr.hidden-row {
        display: none;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
    function filterUsers() {
        const input = document.getElementById('userSearch');
        const clearBtn = document.getElementById('clearSearch');
        const filter = input.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.users-table tbody tr');
        const noResults = document.getElementById('noResults');
        let visibleCount = 0;

        clearBtn.classList.toggle('visible', filter.length > 0);

        rows.forEach(row => {
            const id = row.querySelector('td[data-label="ID"]')?.textContent.toLowerCase() || '';
            const name = row.querySelector('td[data-label="Name"]')?.textContent.toLowerCase() || '';
            const email = row.querySelector('td[data-label="Email"]')?.textContent.toLowerCase() || '';
            const role = row.querySelector('td[data-label="Role"]')?.textContent.toLowerCase() || '';

            const match = !filter || id.includes(filter) || name.includes(filter) || email.includes(filter) || role.includes(filter);
            row.classList.toggle('hidden-row', !match);
            if (match) visibleCount++;
        });

        noResults.classList.toggle('show', visibleCount === 0 && filter.length > 0);
    }

    function clearSearch() {
        const input = document.getElementById('userSearch');
        input.value = '';
        filterUsers();
        input.focus();
    }
</script>

<div class="container" style="width: min(1300px, calc(100% - 32px)); padding-top: 4rem;">
    <div style="text-align:center; padding-bottom:1.5rem;">
        <h2 style="font-size:2.25rem; font-weight:700; font-family:'Outfit',sans-serif; margin:0; letter-spacing:-0.5px; background:linear-gradient(135deg, var(--text-dark) 0%, var(--primary-color) 100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; filter:drop-shadow(0 2px 4px rgba(0,0,0,0.1)); position:relative;">
            Manage Users
        </h2>
        <span style="display:block; width:80px; height:4px; background:linear-gradient(90deg, var(--primary-color), transparent); border-radius:2px; margin:0.5rem auto 0;"></span>
    </div>
    <div style="display:flex; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:2rem;">
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
        <div style="margin-left:auto;" class="search-box">
            <span class="search-icon">&#x1F50D;</span>
            <input type="text" id="userSearch" placeholder="Search by name, email, ID, or role..." oninput="filterUsers()">
            <button class="clear-search" id="clearSearch" onclick="clearSearch()" aria-label="Clear search">&times;</button>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="no-results" id="noResults">
        <h3>No users match your search</h3>
        <p>Try a different name, email, or ID.</p>
    </div>

    <?php if (count($users) > 0): ?>
        <div class="table-wrapper">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Verified</th>
                        <th>Orders</th>
                        <th>Spent</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td data-label="ID"><strong>#<?php echo $user['user_id']; ?></strong></td>
                            <td data-label="Name"><?php echo htmlspecialchars($user['name']); ?></td>
                            <td data-label="Email"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td data-label="Role">
                                <span class="badge badge-<?php echo $user['role']; ?>">
                                    <?php echo $user['role']; ?>
                                </span>
                            </td>
                            <td data-label="Verified">
                                <?php if ($user['email_verified_at']): ?>
                                    <span class="badge badge-verified">Verified</span>
                                <?php else: ?>
                                    <span class="badge badge-unverified">Unverified</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Orders"><?php echo $user['order_count']; ?></td>
                            <td data-label="Spent"><?php echo smartmall_format_money($user['total_spent']); ?></td>
                            <td data-label="Joined"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td data-label="Actions">
                                <div class="action-group">
                                    <?php if ($user['email_verified_at']): ?>
                                        <form method="POST" style="display:inline">
                                            <?php csrf_field(); ?>
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <button type="submit" name="unverify_user" class="btn-sm btn-unverify">Unverify</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" style="display:inline">
                                            <?php csrf_field(); ?>
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <button type="submit" name="verify_user" class="btn-sm btn-verify">Verify</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ((int)$user['user_id'] !== (int)$_SESSION['user_id']): ?>
                                        <form method="POST" style="display:inline">
                                            <?php csrf_field(); ?>
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <button type="submit" name="toggle_admin" class="btn-sm btn-toggle-admin">
                                                <?php echo $user['role'] === 'admin' ? 'Demote' : 'Make Admin'; ?>
                                            </button>
                                        </form>
                                        <form method="POST" style="display:inline" data-confirm="Delete user <?php echo htmlspecialchars($user['name'], ENT_QUOTES); ?> (#<?php echo $user['user_id']; ?>)? This cannot be undone.">
                                            <?php csrf_field(); ?>
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <button type="submit" name="delete_user" class="btn-sm btn-delete-user">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 4rem; background: var(--surface); border-radius: var(--radius); box-shadow: var(--shadow-md); border: 1px solid var(--border-color);">
            <h3 style="color: var(--text-dark);">No users found</h3>
            <p style="color: var(--text-light);">Users will appear here once they register.</p>
        </div>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('form[data-confirm]').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        if (!confirm(this.getAttribute('data-confirm'))) {
            e.preventDefault();
        }
    });
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>