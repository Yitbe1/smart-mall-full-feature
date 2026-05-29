<?php
$page_title = 'Set New Password - Smart Mall';
require_once __DIR__ . '/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$errors = [];
$success = false;
$token_valid = false;
$email = '';

$token = $_GET['token'] ?? $_POST['token'] ?? '';

if (!empty($token)) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT email, expires_at FROM password_resets WHERE token = :token");
        $stmt->execute([':token' => hash('sha256', $token)]);
        $reset = $stmt->fetch();

        if ($reset && strtotime($reset['expires_at']) > time()) {
            $token_valid = true;
            $email = $reset['email'];
        } else {
            $errors['token'] = 'Invalid or expired reset link. Please request a new one.';
        }
    } catch (PDOException $e) {
        $errors['database'] = 'Something went wrong. Please try again.';
    }
} else {
    $errors['token'] = 'No reset token provided.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {
    csrf_verify();

    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors['password'] = 'Must contain an uppercase letter';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors['password'] = 'Must contain a lowercase letter';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Must contain a number';
    } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors['password'] = 'Must contain a special character';
    }

    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }

    if (empty($errors)) {
        try {
            $pdo = getDB();
            $hashed = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
            $stmt->execute([':password' => $hashed, ':email' => $email]);

            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = :email");
            $stmt->execute([':email' => $email]);

            $success = true;
        } catch (PDOException $e) {
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
    .password-hint {
        margin-top: 0.2rem;
        font-size: 0.8rem;
        color: var(--text-light);
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
            <h1><?php echo $success ? 'Password Reset!' : ($token_valid ? 'Set New Password' : 'Invalid Link'); ?></h1>
            <p><?php echo $success ? 'Your password has been updated.' : ($token_valid ? 'Enter your new password below' : 'This reset link is invalid or expired'); ?></p>
        </div>
        <div class="auth-card-body">
            <?php if ($success): ?>
                <div class="success-box">
                    Your password has been reset successfully!
                </div>
                <div class="auth-footer">
                    <a href="login.php">Login with your new password →</a>
                </div>
            <?php elseif (!$token_valid): ?>
                <div class="alert alert-danger" style="margin-bottom:1.25rem;">
                    <?php foreach ($errors as $e): ?>
                        <div><?php echo htmlspecialchars($e); ?></div>
                    <?php endforeach; ?>
                </div>
                <div class="auth-footer">
                    <a href="forgot_password.php">Request a new reset link →</a>
                </div>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger" style="margin-bottom:1.25rem;">
                        <?php foreach ($errors as $e): ?>
                            <div><?php echo htmlspecialchars($e); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                    <div class="form-group">
                        <label for="password">New Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password"
                                class="<?php echo isset($errors['password']) ? 'error' : ''; ?>"
                                required>
                            <button type="button" class="toggle-password" onclick="togglePassword('password')" aria-label="Toggle password visibility">
                                <svg id="eye-icon-password" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg id="eye-off-icon-password" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <div class="form-error"><?php echo htmlspecialchars($errors['password']); ?></div>
                        <?php else: ?>
                            <div class="password-hint">Min 8 chars, uppercase, lowercase, number &amp; special character</div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password"
                                class="<?php echo isset($errors['confirm_password']) ? 'error' : ''; ?>"
                                required>
                            <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')" aria-label="Toggle password visibility">
                                <svg id="eye-icon-confirm_password" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg id="eye-off-icon-confirm_password" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="form-error"><?php echo htmlspecialchars($errors['confirm_password']); ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn-submit">Reset Password →</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const pw = document.getElementById(fieldId);
    const eye = document.getElementById('eye-icon-' + fieldId);
    const eyeOff = document.getElementById('eye-off-icon-' + fieldId);
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
