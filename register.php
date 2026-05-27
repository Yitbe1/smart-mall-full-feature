<?php
require_once __DIR__ . '/config.php';
// User Registration
$page_title = 'Register - Smart Mall';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$errors  = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    csrf_verify();

    $name             = trim($_POST['name'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    } elseif (strlen($name) < 2) {
        $errors['name'] = 'Name must be at least 2 characters';
    }

    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }

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

            // Check if email already exists
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);

            if ($stmt->rowCount() > 0) {
                $errors['email'] = 'Email already registered';
            } else {
                // Hash password with bcrypt
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                $stmt = $pdo->prepare("
                    INSERT INTO users (name, email, password, role)
                    VALUES (:name, :email, :password, 'customer')
                ");
                $stmt->execute([
                    ':name'     => $name,
                    ':email'    => $email,
                    ':password' => $hashed_password
                ]);

                $success = true;
                $_SESSION['success'] = 'Registration successful! Please login.';
                header('Location: login.php');
                exit();
            }
        } catch (PDOException $e) {
            $errors['database'] = 'Database error. Please try again.';
        }
    }
}
include __DIR__ . '/includes/header.php';
?>

<style>
    .auth-container {
        max-width: 450px;
        margin: 2.5rem auto 1.5rem;
        padding: 1.25rem;
        background: var(--surface);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-lg);
        border-radius: var(--radius);
    }

    .auth-header {
        text-align: center;
        margin-bottom: 1rem;
    }

    .auth-header h1 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.5rem;
        color: var(--text-dark);
        margin-bottom: 0.25rem;
        font-weight: 800;
    }

    .auth-header p {
        color: var(--text-light);
        font-size: 0.88rem;
    }

    .form-group {
        margin-bottom: 0.85rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.35rem;
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--text-dark);
    }

    .form-group input {
        width: 100%;
        padding: 0.7rem 0.85rem;
        border: 1px solid var(--border-color);
        font-size: 0.92rem;
        transition: border-color 0.3s ease;
        background: var(--bg-light);
        color: var(--text-dark);
    }

    .form-group input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        background: var(--surface);
    }

    .form-error {
        color: var(--danger-color);
        font-size: 0.8rem;
        margin-top: 0.2rem;
    }

    .form-group input.error {
        border-color: var(--danger-color);
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

    .btn-submit {
        width: 100%;
        padding: 0.75rem;
        background-color: var(--primary-color);
        color: white;
        border: none;
        font-size: 0.95rem;
        font-weight: 700;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .btn-submit:hover {
        background-color: var(--primary-dark);
        transform: translateY(-1px);
    }

    .auth-footer {
        text-align: center;
        margin-top: 1rem;
        font-size: 0.88rem;
        color: var(--text-light);
    }

    .auth-footer a {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 600;
    }

    .auth-footer a:hover {
        text-decoration: underline;
    }

    .password-hint {
        margin-top: 0.2rem;
        font-size: 0.8rem;
        color: var(--text-light);
    }

    @media (max-width: 480px) {
        .container {
            padding: 0.5rem 0;
        }

        .auth-container {
            margin: 2rem 0.75rem 1rem;
            padding: 1rem;
        }

        .auth-header {
            margin-bottom: 0.75rem;
        }

        .auth-header h1 {
            font-size: 1.25rem;
        }

        .form-group {
            margin-bottom: 0.65rem;
        }

        .form-group input {
            padding: 0.6rem 0.75rem;
            font-size: 0.88rem;
        }

        .btn-submit {
            padding: 0.65rem;
            font-size: 0.9rem;
        }
    }

    @media (min-width: 481px) {
        .auth-container {
            margin: 2.5rem auto 3rem;
        }
    }
</style>

<div class="container">
    <div class="auth-container">
        <div class="auth-header">
            <h1>Create Account</h1>
            <p>Join Smart Mall today and start shopping</p>
        </div>

        <?php if (!empty($errors)): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    <?php foreach ($errors as $error): ?>
                        showToast(<?php echo json_encode($error); ?>, "error");
                    <?php endforeach; ?>
                });
            </script>
        <?php endif; ?>

        <form method="POST" action="">
            <?php csrf_field(); ?>

            <div class="form-group">
                <label for="name">Full Name *</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                    class="<?php echo isset($errors['name']) ? 'error' : ''; ?>"
                    required>
                <?php if (isset($errors['name'])): ?>
                    <div class="form-error"><?php echo htmlspecialchars($errors['name']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    class="<?php echo isset($errors['email']) ? 'error' : ''; ?>"
                    required>
                <?php if (isset($errors['email'])): ?>
                    <div class="form-error"><?php echo htmlspecialchars($errors['email']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <div class="password-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="<?php echo isset($errors['password']) ? 'error' : ''; ?>"
                        required>
                    <button type="button" class="toggle-password" onclick="togglePassword('password')" aria-label="Toggle password visibility">
                        <svg id="eye-icon-password" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <svg id="eye-off-icon-password" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" />
                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
                            <line x1="1" y1="1" x2="23" y2="23" />
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
                <label for="confirm_password">Confirm Password *</label>
                <div class="password-wrapper">
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="<?php echo isset($errors['confirm_password']) ? 'error' : ''; ?>"
                        required>
                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')" aria-label="Toggle password visibility">
                        <svg id="eye-icon-confirm_password" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <svg id="eye-off-icon-confirm_password" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" />
                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
                            <line x1="1" y1="1" x2="23" y2="23" />
                        </svg>
                    </button>
                </div>
                <?php if (isset($errors['confirm_password'])): ?>
                    <div class="form-error"><?php echo htmlspecialchars($errors['confirm_password']); ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-submit">Create Account</button>

            <div class="auth-footer">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

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