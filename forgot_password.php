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
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires)");
                $stmt->execute([':email' => $email, ':token' => $token, ':expires' => $expires]);

                $reset_link = $base_url . '/reset_password.php?token=' . $token;
                $log_file = sys_get_temp_dir() . '/smartmall_reset_links.txt';
                @file_put_contents($log_file, date('Y-m-d H:i:s') . " - $email - $reset_link\n", FILE_APPEND);
                $_SESSION['reset_link'] = $reset_link;

                $to = $email;
                $subject = 'Smart Mall - Password Reset';
                $message = "Hello,\n\nYou requested a password reset.\n\nClick this link to reset your password:\n$reset_link\n\nThis link expires in 1 hour.\n\nIf you did not request this, ignore this email.\n\n- Smart Mall Team";
                $headers = 'From: noreply@smartmall.com';
                mail($to, $subject, $message, $headers);
            }

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
                    If an account with that email exists, a password reset link has been sent.
                    <?php if (isset($_SESSION['reset_link'])): ?>
                        <br><br><strong>Local test link:</strong><br>
                        <a href="<?php echo htmlspecialchars($_SESSION['reset_link']); ?>" style="color:#065f46;word-break:break-all;">
                            <?php echo htmlspecialchars($_SESSION['reset_link']); ?>
                        </a>
                        <?php unset($_SESSION['reset_link']); ?>
                    <?php endif; ?>
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
                    <button type="submit" class="btn-submit">Send Reset Link →</button>
                </form>

                <div class="auth-footer" style="margin-top:1rem;">
                    <a href="login.php">← Back to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
