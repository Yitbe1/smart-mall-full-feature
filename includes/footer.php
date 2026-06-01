<?php
// Shared Smart Mall footer
?>
<footer class="smartmall-footer">
    <style>
        .smartmall-footer {
            margin-top: 6rem;
            background: var(--footer-bg);
            color: var(--footer-text);
            padding: 4rem 0 0;
        }

        .newsletter-bar {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto 4rem;
            padding: 3rem;
            background: var(--primary-color);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
            color: #fff;
        }

        .newsletter-content h3 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            color: #fff !important;
        }

        .newsletter-content p {
            opacity: 0.9;
        }

        .newsletter-form {
            display: flex;
            gap: 0.75rem;
            flex: 0 1 450px;
        }

        .newsletter-form input {
            flex: 1;
            padding: 1rem 1.5rem;
            border: none;
            border-radius: 12px;
            outline: none;
        }

        .newsletter-form button {
            padding: 1rem 2rem;
            background: #45a8aa;
            color: #fff;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .newsletter-form button:hover {
            transform: scale(1.05);
            filter: brightness(1.2);
        }

        .footer-grid {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.5fr repeat(3, 1fr);
            gap: 4rem;
            padding-bottom: 4rem;
        }

        .footer-brand {
            display: inline-flex;
            margin-bottom: 1.5rem;
            color: #fff !important;
            font-family: 'Outfit', sans-serif;
            font-size: 1.75rem;
            font-weight: 800;
            text-decoration: none;
        }

        .footer-brand span {
            color: var(--primary-color);
        }

        .footer-brand-column p {
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: var(--primary-color);
            transform: translateY(-3px);
        }

        .smartmall-footer h4 {
            margin-bottom: 1.5rem;
            color: var(--footer-heading);
            font-size: 1.1rem;
            font-weight: 700;
        }

        .smartmall-footer ul {
            list-style: none;
        }

        .smartmall-footer li {
            margin-bottom: 0.8rem;
        }

        .smartmall-footer a {
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .smartmall-footer a:hover {
            color: var(--primary-color);
        }

        .footer-bottom {
            padding: 2rem 0;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            text-align: center;
            font-size: 0.9rem;
        }

        @media (max-width: 980px) {
            .newsletter-bar {
                flex-direction: column;
                text-align: center;
                padding: 2.5rem;
            }

            .newsletter-form {
                width: 100%;
                max-width: none;
                flex: none;
            }

            .footer-grid {
                grid-template-columns: 1fr 1fr;
                gap: 3rem;
            }
        }

        @media (max-width: 560px) {
            .newsletter-bar {
                padding: 1.75rem 1.25rem;
                border-radius: 16px;
                margin-bottom: 2rem;
            }

            .newsletter-content h3 {
                font-size: 1.35rem;
            }

            .newsletter-form {
                flex-direction: column;
            }

            .newsletter-form input,
            .newsletter-form button {
                width: 100%;
                border-radius: 10px;
            }

            .footer-grid {
                grid-template-columns: 1fr 1fr;
                gap: 1.5rem 2rem;
                padding-bottom: 2.5rem;
            }

            .footer-brand-column {
                grid-column: 1 / -1;
                text-align: center;
            }

            .footer-brand {
                justify-content: center;
                max-width: 100%;
            }

            .footer-brand img {
                height: 32px !important;
            }

            .footer-brand span {
                font-size: clamp(1.25rem, 7vw, 1.65rem) !important;
                overflow-wrap: anywhere;
            }

            .social-links {
                justify-content: center;
            }

            .smartmall-footer li {
                margin-bottom: 0.5rem;
            }

            .smartmall-footer h4 {
                margin-bottom: 1rem;
            }

            .footer-bottom {
                padding: 1.5rem 0;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 480px) {
            .smartmall-footer {
                margin-top: 2rem;
                padding: 1.5rem 0 0;
            }

            .newsletter-bar {
                padding: 0.75rem;
                margin-bottom: 1rem;
                gap: 0.5rem;
            }

            .newsletter-content h3 {
                font-size: 0.9rem;
                margin-bottom: 0.125rem;
            }

            .newsletter-content p {
                font-size: 0.75rem;
                line-height: 1.3;
                margin-bottom: 0;
            }

            .newsletter-form {
                gap: 0.25rem;
            }

            .newsletter-form input,
            .newsletter-form button {
                padding: 0.4rem 0.5rem;
                font-size: 0.75rem;
            }

            .footer-grid {
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
                padding-bottom: 1rem;
            }

            .footer-brand-column {
                grid-column: 1 / -1;
                text-align: center;
            }

            .footer-brand {
                justify-content: center;
                max-width: 100%;
                font-size: 1.1rem;
                margin-bottom: 0.5rem;
            }

            .footer-brand img {
                height: 28px !important;
            }

            .footer-brand span {
                font-size: 1.1rem !important;
            }

            .footer-brand-column p {
                font-size: 0.75rem;
                margin-bottom: 0.5rem;
                line-height: 1.4;
            }

            .social-links {
                justify-content: center;
            }

            .social-links a {
                width: 28px;
                height: 28px;
            }

            .smartmall-footer h4 {
                font-size: 0.8rem;
                margin-bottom: 0.4rem;
            }

            .smartmall-footer li {
                margin-bottom: 0.2rem;
            }

            .smartmall-footer li a {
                font-size: 0.75rem;
            }

            .footer-bottom {
                padding: 0.75rem 0;
                font-size: 0.7rem;
            }
        }
    </style>

    <div class="newsletter-bar">
        <div class="newsletter-content">
            <h3>Stay ahead of the curve</h3>
            <p>Join our growing community for exclusive drops and deals.</p>
        </div>
        <form class="newsletter-form" id="newsletter-form">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Subscribe</button>
        </form>
    </div>

    <div class="footer-grid">
        <div class="footer-brand-column">
            <a class="footer-brand" href="<?= BASE_PATH ?>/index.php" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                <img src="<?= BASE_PATH ?>/assets/images/logo-icon.png" alt="Smart Mall Logo Icon" style="height: 38px; width: auto; filter: drop-shadow(0 2px 8px rgba(69, 168, 170, 0.4));">
                <span style="font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 1.75rem; letter-spacing: 0.04em; color: #fff;">Smart Mall<span style="color: #45a8aa;">.</span></span>
            </a>
            <p>Curating the finest in fashion, electronics, and home living. We bring global trends directly to your doorstep with a focus on quality and security.</p>
            <div class="social-links">
                <a href="#" aria-label="Instagram">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                    </svg>
                </a>
                <a href="#" aria-label="Twitter">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z" />
                    </svg>
                </a>
            </div>
        </div>

        <div class="footer-links-column">
            <h4>Shop</h4>
            <ul>
                <li><a href="<?= BASE_PATH ?>/index.php#products">Latest Drops</a></li>
                <li><a href="<?= BASE_PATH ?>/cart.php">Shopping Cart</a></li>
                <li><a href="<?= BASE_PATH ?>/orders.php">Order Status</a></li>
                <li><a href="<?= BASE_PATH ?>/index.php?category=fashion">Style Guide</a></li>
            </ul>
        </div>

        <div class="footer-links-column">
            <h4>Company</h4>
            <ul>
                <li><a href="<?= BASE_PATH ?>/about.php">About Us</a></li>
                <li><a href="<?= BASE_PATH ?>/contact.php">Contact Us</a></li>
                <li><a href="#">Careers</a></li>
                <li><a href="#">Sustainability</a></li>
                <li><a href="mailto:hello@smartmall.com">hello@smartmall.com</a></li>
                <li><a href="tel:+251907070707">(251) 907-070-707</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        &copy; <?php echo date('Y'); ?> Smart Mall.
    </div>
<script>
document.getElementById('newsletter-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('button');
    btn.disabled = true; btn.textContent = '...';
    const formData = new FormData(this);
    fetch(BASE_PATH + '/subscribe.php', { method: 'POST', body: formData })
        .then(r => r.json()).then(d => { showToast(d.message, d.success ? 'success' : 'error'); this.querySelector('input').value = ''; })
        .catch(() => showToast('Connection error. Try again.', 'error'))
        .finally(() => { btn.disabled = false; btn.textContent = 'Subscribe'; });
});
</script>
</footer>
</body>
</html>