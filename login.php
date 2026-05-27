<?php
// User Login — POST is processed BEFORE header.php to allow header() redirects
$page_title = 'Login - Smart Mall';

require_once 'includes/db.php';

// Start session early (before header.php) so redirects work
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Already logged in → redirect
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email))    $errors['email']    = 'Email is required';
    if (empty($password)) $errors['password'] = 'Password is required';

    if (empty($errors)) {
        try {
            $pdo  = getDB();
            $stmt = $pdo->prepare("SELECT user_id, name, email, password, role FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session to prevent fixation
                session_regenerate_id(true);

                $_SESSION['user_id']    = $user['user_id'];
                $_SESSION['user_name']  = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role']  = $user['role'];
                $_SESSION['success']    = 'Welcome back, ' . $user['name'] . '!';

                // Force session write before redirect
                session_write_close();

                $redirect = $_GET['redirect'] ?? '';
                if ($redirect && preg_match('/^[a-zA-Z0-9_\-\/\.]+\.php$/', $redirect)) {
                    header('Location: ' . $redirect);
                } else {
                    header('Location: index.php');
                }
                exit();
            } else {
                $errors['login'] = 'Invalid email or password';
            }
        } catch (PDOException $e) {
            $errors['database'] = 'Database error. Please try again.';
        }
    }
}

// Only NOW include header (outputs HTML)
require_once 'includes/header.php';
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

    .auth-card-header {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        padding: 1.25rem;
        text-align: center;
        color: #fff;
    }

    .auth-card-body {
        padding: 1.25rem;
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
        margin-top: 0.25rem;
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
        border-left: 3px solid var(--primary-color);
        padding: 0.65rem 0.75rem;
        font-size: 0.82rem;
        color: var(--text-dark);
        border-radius: 4px;
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
        border: 1.5px solid var(--border-color);
        font-size: 0.92rem;
        transition: border-color 0.2s, box-shadow 0.2s;
        background: var(--bg-light);
        color: var(--text-dark);
    }

    .form-group input:focus {
        outline: none;
        border-color: var(--primary-color);
        background: var(--surface);
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
        text-align: center;
        margin: 1rem 0;
        color: var(--text-light);
        font-size: 0.85rem;
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
                </div>

                <button type="submit" class="btn-login">Sign In →</button>
            </form>

            <div class="auth-footer">
                <a href="forgot_password.php" class="forgot-link">Forgot your password?</a>
                <div class="dropdown-divider" style="margin: 0.5rem 0;"></div>
                <a href="register.php" class="auth-register-link">Don't have an account? <span>Create one now</span></a>
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
</script>