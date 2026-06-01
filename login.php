<?php
// User Login — POST is processed BEFORE header.php to allow header() redirects
$page_title = 'Login - Smart Mall';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    $env_vars = parse_ini_file($env_file);
    if ($env_vars) {
        foreach ($env_vars as $key => $value) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

require_once 'includes/db.php';

// Already logged in → redirect
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Base URL (duplicated from config.php since login.php uses its own session start)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
    || !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    ? 'https://' : 'http://';
$host = $_SERVER['SERVER_NAME'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$subfolder = rtrim(dirname($scriptName), '/\\');
if ($subfolder === '/') {
    $subfolder = '';
}
$base_url = $protocol . $host . $subfolder;

// BASE_PATH — derive from login.php's location, not the requesting script
$base_path = strtr(str_replace($_SERVER['DOCUMENT_ROOT'] ?? '', '', __DIR__), '\\', '/');
if ($base_path === '/') { $base_path = ''; }
$base_path_env = $_ENV['BASE_PATH'] ?? '';
if ($base_path_env !== '') { $base_path = $base_path_env; }
define('BASE_PATH', $base_path);

$errors = [];
$unverified_email = '';
$resend_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // --- Resend verification email ---
    if (isset($_POST['resend']) && !empty($email)) {
        try {
            $pdo  = getDB();
            $stmt = $pdo->prepare("SELECT user_id, name FROM users WHERE email = :email AND email_verified_at IS NULL");
            $stmt->execute([':email' => $email]);
            $unverified = $stmt->fetch();

            if ($unverified) {
                $token = bin2hex(random_bytes(32));
                $token_hash = hash('sha256', $token);

                $stmt = $pdo->prepare("UPDATE users SET verification_token = :token, verification_token_expires_at = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE user_id = :id");
                $stmt->execute([':token' => $token_hash, ':id' => $unverified['user_id']]);

                $verify_link = $base_url . '/verify_email.php?token=' . $token;

                require_once __DIR__ . '/helpers/mail.php';
                if (send_mail(
                    $email,
                    'Smart Mall - Verify Your Email',
                    "Hello {$unverified['name']},\n\nHere is a new verification link for your Smart Mall account:\n$verify_link\n\nThis link expires in 5 minutes.\n\nIf you did not create an account, please ignore this email.\n\n- Smart Mall Team",
                    null,
                    $token_hash,
                    email_html_template(
                        '<h2 style="margin:0 0 15px;color:#111;font-size:20px;">Verify Your Email</h2>' .
                            '<p style="margin:0 0 15px;">Hello <strong>' . e($unverified['name']) . '</strong>,</p>' .
                            '<p style="margin:0 0 15px;">Here is a new verification link for your Smart Mall account:</p>' .
                            '<p style="margin:0 0 15px;text-align:center;"><a href="' . e($verify_link) . '" style="display:inline-block;background:linear-gradient(135deg,#2563eb,#1e40af);color:#fff;text-decoration:none;padding:14px 28px;border-radius:8px;font-size:16px;font-weight:700;">Verify Email Address</a></p>' .
                            '<p style="margin:0 0 15px;">Or paste this link in your browser:</p>' .
                            '<p style="margin:0 0 15px;word-break:break-all;color:#2563eb;font-size:13px;">' . e($verify_link) . '</p>' .
                            '<p style="margin:0 0 15px;color:#888;font-size:13px;">This link expires in 5 minutes.</p>' .
                            '<hr style="border:none;border-top:1px solid #eee;margin:20px 0;">' .
                            '<p style="margin:0;color:#888;font-size:13px;">If you did not create an account, please ignore this email.</p>'
                    )
                )) {
                    $resend_message = 'A verification link has been sent to ' . htmlspecialchars($email) . '.';
                } else {
                    $resend_message = 'Failed to send email. Please try again later.';
                }
                $unverified_email = $email;
            } else {
                $resend_message = 'This email is already verified or not registered.';
            }
        } catch (PDOException $e) {
            error_log("Login resend error: " . $e->getMessage());
            $resend_message = 'Something went wrong. Please try again.';
        }
    }

    // --- Normal login ---
    if (!isset($_POST['resend'])) {
        if (empty($email))    $errors['email']    = 'Email is required';
        if (empty($password)) $errors['password'] = 'Password is required';

        // reCAPTCHA v3 verification
        require_once __DIR__ . '/helpers/captcha.php';
        if (!verify_recaptcha()) {
            $errors['captcha'] = 'Captcha verification failed. Please try again.';
        }

        if (empty($errors)) {
            try {
                $pdo  = getDB();
                $stmt = $pdo->prepare("SELECT user_id, name, email, password, role, email_verified_at FROM users WHERE email = :email");
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    if ($user['email_verified_at'] === null) {
                        $unverified_email = $email;
                    } else {
                        session_regenerate_id(true);

                        $_SESSION['user_id']    = $user['user_id'];
                        $_SESSION['user_name']  = $user['name'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_role']  = $user['role'];
                        $_SESSION['success']    = 'Welcome back, ' . $user['name'] . '!';

                        session_write_close();

                        $redirect = $_GET['redirect'] ?? '';
                        if ($redirect && preg_match('/^[a-zA-Z0-9_-]+\.php$/', $redirect)) {
                            header('Location: ' . $redirect);
                        } else {
                            header('Location: index.php');
                        }
                        exit();
                    }
                } else {
                    $errors['login'] = 'Invalid email or password';
                }
            } catch (PDOException $e) {
                error_log("Login error: " . $e->getMessage());
                $errors['database'] = 'Database error. Please try again.';
            }
        }
    }
}

// Only NOW include header (outputs HTML)
include __DIR__ . '/includes/header.php';
?>

<!-- Google Identity Services (Sign In With Google) -->
<script src="https://accounts.google.com/gsi/client" async defer></script>
<!-- reCAPTCHA v3 -->
<script src="https://www.google.com/recaptcha/api.js?render=<?php echo htmlspecialchars($_ENV['RECAPTCHA_SITE_KEY'] ?? ''); ?>"></script>
<style>
    .auth-wrapper {
        min-height: auto;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1rem;
        margin-top: 4rem;
    }

    .auth-card-header {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        padding: 1.25rem;
        text-align: center;
        color: #fff;
    }

    .auth-card-body {
        padding: 1.5rem;
    }

    .form-group {
        margin-bottom: 0.85rem;
    }

    @media (min-width: 481px) {
        .auth-wrapper {
            min-height: 60vh;
        }
    }

    .btn-login {
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
    }

    .auth-footer {
        margin-top: 1rem;
        text-align: center;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .demo-info {
        margin-top: 0.75rem;
        background: var(--primary-light);
        padding: 0.75rem 1rem;
        font-size: 0.82rem;
        color: var(--text-dark);
        border-radius: var(--radius);
    }

    .auth-card {
        width: 100%;
        max-width: 440px;
        background: var(--surface);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-lg);
        overflow: hidden;
        border-radius: var(--radius);
        animation: card-in 0.35s ease-out;
    }

    @keyframes card-in {
        from {
            opacity: 0;
            transform: translateY(12px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
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

    .password-wrapper {
        position: relative;
    }

    .password-wrapper input {
        padding-right: 2.5rem;
    }

    .toggle-password {
        position: absolute;
        right: 0.5rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        padding: 0.35rem;
        color: var(--text-light);
        line-height: 1;
        transition: color 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .toggle-password:hover {
        color: var(--primary-color);
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

    .btn-login:hover {
        opacity: 0.92;
        transform: translateY(-1px);
    }

    .auth-divider {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 1.25rem 0;
        color: var(--text-light);
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .auth-divider::before,
    .auth-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border-color);
    }

    .forgot-link {
        color: var(--text-light);
        font-size: 0.82rem;
        font-weight: 600;
        text-decoration: none;
        transition: color 0.2s;
    }

    .forgot-link:hover {
        color: var(--primary-color);
    }

    .auth-register-link {
        display: inline-block;
        color: var(--text-dark);
        font-size: 0.85rem;
        font-weight: 700;
        text-decoration: none;
    }

    .auth-register-link span {
        color: var(--primary-color);
        text-decoration: underline;
        text-underline-offset: 4px;
        transition: opacity 0.2s;
    }

    .auth-register-link:hover span {
        opacity: 0.8;
    }

    .demo-info strong {
        color: var(--secondary-color);
    }

    .google-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        height: 48px;
        padding: 4px 16px;
        background: var(--surface-muted);
        border: 1px solid transparent;
        border-radius: 999px;
        font-family: 'Segoe UI', Roboto, system-ui, sans-serif;
        font-size: 16px;
        font-weight: 500;
        color: var(--text-on-muted);
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .google-btn:hover {
        background: color-mix(in srgb, var(--surface-muted), var(--text-dark) 12%);
    }

    .google-btn:active {
        background: color-mix(in srgb, var(--surface-muted), var(--text-dark) 20%);
    }

    .google-btn svg {
        flex-shrink: 0;
        width: 24px;
        height: 24px;
    }

    .gsi-container {
        position: relative;
        display: inline-block;
        width: 100%;
    }

    .gsi-container .g_id_signin {
        position: absolute;
        inset: 0;
        opacity: 0.01;
        z-index: 2;
        height: 48px;
        overflow: hidden !important;
    }
</style>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-card-header">
            <h1>Welcome Back</h1>
            <p>Sign in to your Smart Mall account</p>
        </div>
        <div class="auth-card-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" style="margin-bottom:1.25rem;">
                    <?php foreach ($errors as $e): ?>
                        <div><?php echo htmlspecialchars($e); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ($unverified_email): ?>
                <div class="alert alert-warning" style="margin-bottom:1.25rem;">
                    <div style="font-weight:700;">Please verify your email address before logging in.</div>
                    <?php if ($resend_message): ?>
                        <div style="margin-top:0.5rem;padding:0.5rem;background:#d4edda;color:#155724;border-radius:4px;font-size:0.85rem;"><?php echo htmlspecialchars($resend_message); ?></div>
                    <?php endif; ?>
                    <form method="POST" action="" style="margin-top:0.75rem;">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($unverified_email); ?>">
                        <button type="submit" name="resend" value="1" style="background:none;border:2px solid #2563eb;color:#2563eb;padding:0.5rem 1rem;border-radius:6px;font-weight:700;font-size:0.82rem;cursor:pointer;width:100%;">Resend Verification Email</button>
                    </form>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?php csrf_field(); ?>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        class="<?php echo isset($errors['email']) ? 'error' : ''; ?>"
                        placeholder="you@example.com" required>
                    <?php if (isset($errors['email'])): ?><div class="form-error"><?php echo htmlspecialchars($errors['email']); ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password"
                            class="<?php echo isset($errors['password']) ? 'error' : ''; ?>"
                            placeholder="••••••••" required>
                        <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Toggle password visibility">
                            <svg id="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg id="eye-off-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" />
                                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
                                <line x1="1" y1="1" x2="23" y2="23" />
                            </svg>
                        </button>
                    </div>
                    <?php if (isset($errors['password'])): ?><div class="form-error"><?php echo htmlspecialchars($errors['password']); ?></div><?php endif; ?>
                    <div style="margin-top:0.4rem;"><a href="forgot_password.php" class="forgot-link">Forgot your password?</a></div>
                </div>

                <div class="form-group">
                    <input type="hidden" name="g-recaptcha-response" id="recaptcha-response">
                    <?php if (isset($errors['captcha'])): ?><div class="form-error"><?php echo htmlspecialchars($errors['captcha']); ?></div><?php endif; ?>
                </div>

                <div class="auth-footer">
                    <button type="submit" class="btn-login">Sign In</button>
                    <div class="dropdown-divider" style="margin: 0.5rem 0;"></div>
                    <a href="register.php" class="auth-register-link">Don't have an account? <span>Create one now</span></a>
                </div>
            </form>
            <script>
                (function() {
                    var siteKey = '<?php echo htmlspecialchars($_ENV['RECAPTCHA_SITE_KEY'] ?? ''); ?>';
                    var form = document.getElementById('email').closest('form');
                    if (!form) return;
                    // Pre-populate token on load and keep refreshing
                    grecaptcha.ready(function() {
                        function refreshToken() {
                            grecaptcha.execute(siteKey, {
                                action: 'login'
                            }).then(function(token) {
                                document.getElementById('recaptcha-response').value = token;
                            });
                        }
                        refreshToken();
                        setInterval(refreshToken, 90000);
                    });
                    // Submit safety: if token field is empty, intercept and get one
                    var submitted = false;
                    form.addEventListener('submit', function(e) {
                        if (submitted) return;
                        if (document.getElementById('recaptcha-response').value) return;
                        submitted = true;
                        e.preventDefault();
                        grecaptcha.ready(function() {
                            grecaptcha.execute(siteKey, {
                                action: 'login'
                            }).then(function(token) {
                                document.getElementById('recaptcha-response').value = token;
                                form.submit();
                            });
                        });
                    });
                })();
            </script>

            <div class="auth-divider"><span>or</span></div>

            <div style="text-align:center;">
                <div id="g_id_onload"
                    data-client_id="1003727523085-vk311f184eqrt95a3ggdq17h2fnqe5bl.apps.googleusercontent.com"
                    data-context="signin"
                    data-ux_mode="popup"
                    data-callback="handleGoogleCredential"
                    data-auto_prompt="false">
                </div>
                <div class="gsi-container">
                    <button type="button" class="google-btn" tabindex="-1">
                        <svg width="20" height="20" viewBox="0 0 48 48">
                            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
                            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
                            <path fill="#FBBC05" d="M10.53 28.59A14.5 14.5 0 0 1 9.5 24c0-1.59.28-3.14.76-4.59l-7.98-6.19A23.99 23.99 0 0 0 0 24c0 3.77.87 7.35 2.56 10.58l7.97-5.99z" />
                            <path fill="#34A853" d="M24 48c6.48 0 11.94-2.13 15.92-5.78l-7.73-6c-2.15 1.45-4.92 2.33-8.19 2.33-6.26 0-11.57-4.22-13.47-9.91l-7.98 5.99C6.51 42.62 14.62 48 24 48z" />
                        </svg>
                        Continue with Google
                    </button>
                    <div class="g_id_signin"
                        data-type="standard"
                        data-shape="rectangular"
                        data-theme="outline"
                        data-text="sign_in_with"
                        data-size="large"
                        data-width="380"
                        data-logo_alignment="left">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
    function togglePassword() {
        const pw = document.getElementById('password');
        const eye = document.getElementById('eye-icon');
        const eyeOff = document.getElementById('eye-off-icon');
        if (pw.type === 'password') {
            pw.type = 'text';
            eye.style.display = 'none';
            eyeOff.style.display = 'block';
        } else {
            pw.type = 'password';
            eye.style.display = 'block';
            eyeOff.style.display = 'none';
        }
    }

    async function handleGoogleCredential(response) {
        try {
            const res = await fetch('google_login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'credential=' + encodeURIComponent(response.credential)
            });
            const data = await res.json();
            if (data.success) {
                window.location.href = data.redirect || 'index.php';
            } else {
                alert(data.error || 'Google sign-in failed');
            }
        } catch (e) {
            alert('Google sign-in failed. Please try again.');
        }
    }
</script>