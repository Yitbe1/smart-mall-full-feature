<?php
require_once __DIR__ . '/config.php';
$page_title = 'Contact Us - Smart Mall';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $order_id = $subject === 'order' ? (int) ($_POST['order_id'] ?? 0) : 0;
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    }

    require_once __DIR__ . '/helpers/captcha.php';
    if (!verify_recaptcha()) {
        $error = 'Captcha verification failed. Please try again.';
    }

    if (empty($error)) {
        $pdo = getDB();
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (:name, :email, :subject, :message)");
        $stmt->execute([':name' => $name, ':email' => $email, ':subject' => $subject, ':message' => $message]);

        if ($subject === 'order') {
            try {
                $order_label = $order_id > 0 ? " (Order #$order_id)" : '';
                $notif_msg = "Order Support$order_label from $name <$email> — " . mb_substr($message, 0, 80);
                $notif_link = $order_id > 0 ? "admin/manage_orders.php?search=$order_id" : 'admin/manage_orders.php';
                $pdo->prepare("INSERT INTO notifications (type, message, link) VALUES (?, ?, ?)")
                    ->execute(['order_support', $notif_msg, $notif_link]);
            } catch (Throwable $e) {
                error_log("Notification insert failed: " . $e->getMessage());
            }
        }
        require_once __DIR__ . '/helpers/mail.php';
        send_mail(
            'yitbarekteklu5@gmail.com',
            "Contact: $subject",
            "From: $name <$email>\nSubject: $subject\n\n$message"
        );
        $success = 'Thank you for contacting us! We\'ll get back to you within 24 hours.';
        $name = $email = $subject = $message = '';
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<style>
    .contact-hero {
        padding: clamp(3rem, 8vw, 6rem) 0 4rem;
        background: radial-gradient(circle at top right, var(--primary-light), transparent 60%);
        text-align: center;
    }

    .contact-hero h1 {
        font-size: clamp(2.5rem, 6vw, 4rem);
        margin-bottom: 1.5rem;
        color: var(--secondary-color);
    }

    .contact-hero p {
        max-width: 700px;
        margin: 0 auto;
        font-size: 1.25rem;
        color: var(--text-light);
        line-height: 1.8;
    }

    .contact-grid {
        display: grid;
        grid-template-columns: 1fr 1.5fr;
        gap: 3rem;
        margin: 4rem 0;
    }

    .contact-info {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .contact-card {
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 2rem;
        box-shadow: var(--shadow-md);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .contact-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .contact-card-icon {
        width: 50px;
        height: 50px;
        background: var(--primary-light);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        color: var(--primary-color);
    }

    .contact-card h3 {
        margin-bottom: 0.5rem;
        font-size: 1.25rem;
    }

    .contact-card p {
        color: var(--text-light);
        line-height: 1.7;
    }

    .contact-card a {
        color: var(--primary-color);
        font-weight: 600;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .contact-card a:hover {
        color: var(--primary-dark);
    }

    .contact-form-wrapper {
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 3rem;
        box-shadow: var(--shadow-md);
    }

    .contact-form-wrapper h2 {
        margin-bottom: 2rem;
        font-size: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--text-dark);
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        background: var(--bg-light);
        color: var(--text-dark);
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        background: var(--surface);
    }

    .form-group textarea {
        min-height: 150px;
        resize: vertical;
    }

    .btn-submit {
        width: 100%;
        padding: 1rem;
        background: var(--primary-color);
        color: #fff;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    }

    .btn-submit:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
    }

    .map-section {
        margin: 4rem 0;
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: var(--shadow-md);
    }

    .map-placeholder {
        width: 100%;
        height: 400px;
        background: var(--bg-light);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-light);
        font-size: 1.1rem;
    }

    @media (max-width: 980px) {
        .contact-grid {
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .contact-form-wrapper {
            padding: 2rem;
        }
    }

    @media (max-width: 640px) {
        .contact-hero {
            padding: 2rem 0 3rem;
        }

        .contact-hero h1 {
            font-size: 2rem;
        }

        .contact-hero p {
            font-size: 1.1rem;
        }

        .contact-form-wrapper {
            padding: 1.5rem;
        }
    }

    @media (max-width: 480px) {
        .contact-hero {
            padding: 1.5rem 0 2rem;
        }

        .contact-hero h1 {
            font-size: 1.6rem;
            margin-bottom: 1rem;
        }

        .contact-hero p {
            font-size: 0.95rem;
        }

        .contact-grid {
            gap: 1.25rem;
            margin: 2rem 0;
        }

        .contact-card {
            padding: 1.25rem;
        }

        .contact-card-icon {
            width: 40px;
            height: 40px;
            margin-bottom: 0.75rem;
        }

        .contact-card-icon svg {
            width: 20px;
            height: 20px;
        }

        .contact-card h3 {
            font-size: 1.1rem;
        }

        .contact-card p {
            font-size: 0.9rem;
        }

        .contact-form-wrapper {
            padding: 1.25rem;
        }

        .contact-form-wrapper h2 {
            font-size: 1.4rem;
            margin-bottom: 1.25rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            padding: 0.75rem 0.875rem;
            font-size: 0.9rem;
        }

        .form-group textarea {
            min-height: 120px;
        }

        .btn-submit {
            padding: 0.85rem;
            font-size: 0.95rem;
        }

        .map-section {
            margin: 2rem 0;
        }

        .map-placeholder,
        .map-section iframe {
            height: 250px;
        }
    }
</style>

<div class="container">
    <div class="contact-hero">
        <h1>Get In Touch</h1>
        <p>Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="contact-grid">
        <div class="contact-info">
            <div class="contact-card">
                <div class="contact-card-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3>Email Us</h3>
                <p>Our team is here to help</p>
                <a href="mailto:hello@smartmall.com">hello@smartmall.com</a>
            </div>

            <div class="contact-card">
                <div class="contact-card-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                </div>
                <h3>Call Us</h3>
                <p>Mon-Fri from 8am to 6pm</p>
                <a href="tel:+251907070707">+251 907 070 707</a>
            </div>

            <div class="contact-card">
                <div class="contact-card-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <h3>Visit Us</h3>
                <p>Come say hello at our office</p>
                <a href="#">Addis Ababa, Ethiopia</a>
            </div>
        </div>

        <div class="contact-form-wrapper">
            <h2>Send us a message</h2>
            <script src="https://www.google.com/recaptcha/api.js?render=<?php echo htmlspecialchars($_ENV['RECAPTCHA_SITE_KEY'] ?? ''); ?>"></script>
            <form method="POST" action="" id="contactForm">
                <?php csrf_field(); ?>
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="subject">Subject *</label>
                    <select id="subject" name="subject" required>
                        <option value="">Select a subject</option>
                        <option value="general" <?php echo ($subject ?? '') === 'general' ? 'selected' : ''; ?>>General Inquiry</option>
                        <option value="order" <?php echo ($subject ?? '') === 'order' ? 'selected' : ''; ?>>Order Support</option>
                        <option value="product" <?php echo ($subject ?? '') === 'product' ? 'selected' : ''; ?>>Product Question</option>
                        <option value="partnership" <?php echo ($subject ?? '') === 'partnership' ? 'selected' : ''; ?>>Partnership</option>
                        <option value="feedback" <?php echo ($subject ?? '') === 'feedback' ? 'selected' : ''; ?>>Feedback</option>
                    </select>
                </div>

                <div class="form-group" id="order-id-group" style="display:none;">
                    <label for="order_id">Order ID</label>
                    <input type="number" id="order_id" name="order_id" min="1" placeholder="e.g. 42">
                </div>

                <div class="form-group">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <input type="hidden" name="g-recaptcha-response" id="recaptcha-response">
                </div>
                
                <button type="submit" class="btn-submit">Send Message</button>
            </form>
            <script>
            (function() {
                var siteKey = '<?php echo htmlspecialchars($_ENV['RECAPTCHA_SITE_KEY'] ?? ''); ?>';
                var form = document.getElementById('contactForm');
                if (!form) return;
                grecaptcha.ready(function() {
                    function refreshToken() {
                        grecaptcha.execute(siteKey, {action: 'contact'}).then(function(token) {
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
                        grecaptcha.execute(siteKey, {action: 'contact'}).then(function(token) {
                            document.getElementById('recaptcha-response').value = token;
                            form.submit();
                        });
                    });
                });
            })();
            </script>
        </div>
    </div>

    <div class="map-section">
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7393.505136632543!2d38.711421051504324!3d9.05280311393879!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x164b8798c5d75451%3A0xe35a8f3593d5646a!2sGeneral%20Winget%20Poly%20Technic%20College!5e0!3m2!1sen!2set!4v1780203424102!5m2!1sen!2set"
            width="100%"
            height="400"
            style="border:0; display:block;"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
    document.getElementById('subject')?.addEventListener('change', function() {
        const group = document.getElementById('order-id-group');
        if (group) group.style.display = this.value === 'order' ? 'block' : 'none';
    });
    <?php if (($subject ?? '') === 'order'): ?>
        document.getElementById('order-id-group').style.display = 'block';
    <?php endif; ?>
</script>