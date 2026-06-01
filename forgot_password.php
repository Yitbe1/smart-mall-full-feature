<?php
$page_title = 'Forgot Password - Smart Mall';
require_once __DIR__ . '/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    require_once __DIR__ . '/helpers/captcha.php';
    if (!verify_recaptcha()) {
        $errors['captcha'] = 'Captcha verification failed. Please try again.';
    }

    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }

    if (empty($errors)) {
        try {
            $pdo = getDB();
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $token_hash = hash('sha256', $token);
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires)");
                $stmt->execute([':email' => $email, ':token' => $token_hash, ':expires' => $expires]);

                $reset_link = $base_url . '/reset_password.php?token=' . $token;

                require_once __DIR__ . '/helpers/mail.php';
                $sent = send_mail($email, 'Smart Mall - Password Reset',
                    "Hello,\n\nYou requested a password reset.\n\nClick this link to reset your password:\n$reset_link\n\nThis link expires in 1 hour.\n\nIf you did not request this, ignore this email.\n\n- Smart Mall Team",
                    null, $token_hash,
                    email_html_template(
                        '<h2 style="margin:0 0 15px;color:#111;font-size:20px;">Password Reset</h2>' .
                        '<p style="margin:0 0 15px;">Hello,</p>' .
                        '<p style="margin:0 0 15px;">You requested a password reset. Click the button below to reset your password:</p>' .
                        '<p style="margin:0 0 15px;text-align:center;"><a href="' . e($reset_link) . '" style="display:inline-block;background:linear-gradient(135deg,#2563eb,#1e40af);color:#fff;text-decoration:none;padding:14px 28px;border-radius:8px;font-size:16px;font-weight:700;">Reset Password</a></p>' .
                        '<p style="margin:0 0 15px;">Or paste this link in your browser:</p>' .
                        '<p style="margin:0 0 15px;word-break:break-all;color:#2563eb;font-size:13px;">' . e($reset_link) . '</p>' .
                        '<p style="margin:0 0 15px;color:#888;font-size:13px;">This link expires in 1 hour.</p>' .
                        '<hr style="border:none;border-top:1px solid #eee;margin:20px 0;">' .
                        '<p style="margin:0;color:#888;font-size:13px;">If you did not request this, please ignore this email.</p>'
                    ));
            }
            // Always show success to prevent user enumeration
            $success = true;
        } catch (PDOException $e) {
            error_log("Forgot password error: " . $e->getMessage());
            $errors['database'] = 'Something went wrong. Please try again.';
        }
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
    .auth-card-header p {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.88rem;
    }
    .auth-card-body {
        padding: 1.25rem;
    }
    .form-group {
        margin-bottom: 0.85rem;
    }
    .form-group label {
        display: block;
        margin-bottom: 0.35rem;
        font-weight: 700;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-light);
    }
    .form-group input {
        width: 100%;
        padding: 0.7rem 0.85rem;
        border: 1.5px solid var(--input-border);
        font-size: 0.92rem;
        transition: border-color 0.2s, box-shadow 0.2s;
        background: var(--input-bg);
        color: var(--text-dark);
    }
    .form-group input:focus {
        outline: none;
        border-color: var(--primary-color);
        background: var(--input-bg);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }
    .form-group input.error {
        border-color: var(--danger-color);
    }
    .form-error {
        color: var(--danger-color);
        font-size: 0.78rem;
        margin-top: 0.2rem;
        font-weight: 600;
    }
    .btn-submit {
        width: 100%;
        padding: 0.75rem;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: #fff;
        border: none;
        font-size: 1rem;
        font-weight: 800;
        cursor: pointer;
        letter-spacing: 0.03em;
        transition: opacity 0.2s, transform 0.2s;
        margin-top: 0.25rem;
    }
    .btn-submit:hover {
        opacity: 0.92;
        transform: translateY(-1px);
    }
    .auth-footer {
        margin-top: 1rem;
        text-align: center;
    }
    .auth-footer a {
        color: var(--primary-color);
        font-weight: 700;
        font-size: 0.85rem;
        text-decoration: none;
    }
    .auth-footer a:hover {
        text-decoration: underline;
    }
    .success-box {
        background: #ecfdf5;
        border-left: 4px solid var(--success-color);
        padding: 1rem;
        margin-bottom: 1rem;
        font-size: 0.9rem;
        color: #065f46;
        border-radius: 4px;
    }
    @media (min-width: 481px) {
        .auth-wrapper { min-height: 50vh; }
    }
</style>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-card-header">
            <h1>Reset Password</h1>
            <p>Enter your email to receive a reset link</p>
        </div>
        <div class="auth-card-body">
            <?php if ($success): ?>
                <div class="success-box">
                    A password reset link has been sent to your email.
                </div>
                <div class="auth-footer">
                    <a href="login.php">Back to Login</a>
                </div>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger" style="margin-bottom:1.25rem;">
                        <?php foreach ($errors as $e): ?>
                            <div><?php echo htmlspecialchars($e); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <script src="https://www.google.com/recaptcha/api.js?render=<?php echo htmlspecialchars($_ENV['RECAPTCHA_SITE_KEY'] ?? ''); ?>"></script>
                <form method="POST" action="">
                    <?php csrf_field(); ?>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            class="<?php echo isset($errors['email']) ? 'error' : ''; ?>"
                            placeholder="you@example.com" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="form-error"><?php echo htmlspecialchars($errors['email']); ?></div>
                        <?php endif; ?>
                    </div>
                                    <div class="form-group">
                        <input type="hidden" name="g-recaptcha-response" id="recaptcha-response">
                        <?php if (isset($errors['captcha'])): ?><div class="form-error"><?php echo htmlspecialchars($errors['captcha']); ?></div><?php endif; ?>
                    </div>
                    <button type="submit" class="btn-submit">Send Reset Link →</button>
                    <script>
                    (function() {
                        var siteKey = '<?php echo htmlspecialchars($_ENV['RECAPTCHA_SITE_KEY'] ?? ''); ?>';
                        var form = document.getElementById('email').closest('form');
                        if (!form) return;
                        grecaptcha.ready(function() {
                            function refreshToken() {
                                grecaptcha.execute(siteKey, {action: 'forgot_password'}).then(function(token) {
                                    document.getElementById('recaptcha-response').value = token;
                                });
                            }
                            refreshToken();
                            setInterval(refreshToken, 90000);
                        });
                        var submitted = false;
                        form.addEventListener('submit', function(e) {
                            if (submitted) return;
                            if (document.getElementById('recaptcha-response').value) return;
                            submitted = true;
                            e.preventDefault();
                            grecaptcha.ready(function() {
                                grecaptcha.execute(siteKey, {action: 'forgot_password'}).then(function(token) {
                                    document.getElementById('recaptcha-response').value = token;
                                    form.submit();
                                });
                            });
                        });
                    })();
                    </script>
                </form>

                <div class="auth-footer" style="margin-top:1rem;">
                    <a href="login.php">← Back to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
