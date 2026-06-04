<?php
$page_title = 'Verify Email - Smart Mall';
require_once __DIR__ . '/config.php';

$message = '';
$success = false;

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $message = 'Invalid verification link.';
} else {
    try {
        $pdo = getDB();
        $token_hash = hash('sha256', $token);

        $stmt = $pdo->prepare("SELECT user_id, email, name, verification_token_expires_at FROM users WHERE verification_token = :token AND email_verified_at IS NULL");
        $stmt->execute([':token' => $token_hash]);
        $user = $stmt->fetch();

        if ($user) {
            if ($user['verification_token_expires_at'] !== null && strtotime($user['verification_token_expires_at']) < time()) {
                $stmt = $pdo->prepare("UPDATE users SET verification_token = NULL, verification_token_expires_at = NULL WHERE user_id = :id");
                $stmt->execute([':id' => $user['user_id']]);

                $message = 'This verification link has expired. Please request a new one from the login page.';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET email_verified_at = NOW(), verification_token = NULL, verification_token_expires_at = NULL WHERE user_id = :id");
                $stmt->execute([':id' => $user['user_id']]);

                $success = true;
                $message = 'Email verified successfully!';
            }
        } else {
            $message = 'Invalid or expired verification link. Your email may already be verified.';
        }
    } catch (PDOException $e) {
        error_log("Verify email error: " . $e->getMessage());
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
    @media (min-width: 481px) {
        .auth-wrapper { min-height: 50vh; }
    }
</style>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-card-header">
            <h1>Email Verification</h1>
            <p><?php echo $success ? (isset($_SESSION['user_id']) ? 'You are now fully verified' : 'Your account is now active') : 'Verification failed'; ?></p>
        </div>
        <div class="auth-card-body">
            <div class="result-box <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php if ($success && isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="btn-continue">Go to Dashboard →</a>
            <?php else: ?>
                <a href="login.php" class="btn-continue">Go to Login →</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
