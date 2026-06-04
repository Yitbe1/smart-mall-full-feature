<?php
require_once __DIR__ . '/config.php';
$apk_path = __DIR__ . '/Apk/Android/smartmall.apk';
$apk_size = file_exists($apk_path) ? number_format(filesize($apk_path) / 1024 / 1024, 1) : '?';
$page_title = 'Download App - Smart Mall';

require_once __DIR__ . '/includes/header.php';
?>

<style>
:root {
    --logo-shadow: 0 8px 24px rgba(0,0,0,0.1);
}
[data-theme='dark'] {
    --logo-shadow: 0 8px 24px rgba(255,255,255,0.06);
}
.dl-section {
    padding: 4rem 0 3rem;
}
.dl-inner {
    display: flex;
    align-items: center;
    gap: 4rem;
    max-width: 960px;
    margin: 0 auto;
}
.dl-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.dl-info .header-logo {
    width: clamp(48px, 8vw, 80px);
    height: clamp(48px, 8vw, 80px);
    background: var(--surface);
    border-radius: clamp(12px, 2vw, 20px);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: clamp(0.75rem, 2vw, 1.25rem);
    box-shadow: var(--logo-shadow);
}
.dl-info .header-logo img {
    width: clamp(36px, 6vw, 60px);
    height: clamp(36px, 6vw, 60px);
    object-fit: contain;
}
.dl-info h1 {
    font-size: clamp(1.4rem, 5vw, 2.5rem);
    line-height: 1.2;
    margin-bottom: 0.5rem;
    color: var(--secondary-color);
}
.dl-info h1 span {
    color: var(--secondary-color);
}
.dl-info .desc {
    color: var(--text-light);
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 2rem;
    max-width: 420px;
}
.dl-cards {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    max-width: 380px;
}
.dl-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    border-radius: 16px;
    border: 1.5px solid var(--border-color);
    background: var(--surface);
    text-decoration: none;
    color: inherit;
    transition: all 0.25s ease;
}
.dl-card:hover {
    border-color: var(--primary-color);
    box-shadow: 0 8px 25px rgba(0,0,0,0.06);
    transform: translateY(-1px);
}
.dl-card .dl-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.dl-card .dl-icon.blue {
    background: #3b82f6;
    color: #fff;
}
.dl-card .dl-icon.green {
    background: #34a853;
    color: #fff;
}
.dl-card .dl-icon.gray {
    background: #e0e0e0;
    color: #999;
}
.dl-card .dl-body {
    flex: 1;
}
.dl-card .dl-body .top {
    font-size: 0.7rem;
    color: var(--text-light);
    letter-spacing: 0.3px;
}
.dl-card .dl-body .name {
    font-weight: 600;
    font-size: 0.95rem;
}
.dl-card .dl-tag {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.25rem 0.7rem;
    border-radius: 20px;
    flex-shrink: 0;
}
.dl-card .dl-tag.blue {
    background: #dbeafe;
    color: #1d4ed8;
}
.dl-card .dl-tag.green {
    background: #e6f7e6;
    color: #2e7d32;
}
.dl-card .dl-tag.gray {
    background: #f0f0f0;
    color: #aaa;
}
.dl-qr-card {
    gap: 0.75rem;
}
.dl-qr-img {
    width: 120px;
    height: 120px;
    border-radius: 4px;
}
.qr-modal {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.7);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.qr-modal.show {
    display: flex;
}
.qr-modal img {
    width: 300px;
    height: 300px;
    background: #fff;
    padding: 12px;
    border-radius: 12px;
}
.qr-modal-close {
    position: fixed;
    top: 20px;
    right: 30px;
    font-size: 2rem;
    color: #fff;
    cursor: pointer;
}
.dl-card.disabled {
    cursor: not-allowed;
    opacity: 0.5;
}
.dl-card.disabled:hover {
    border-color: var(--border-color);
    transform: none;
    box-shadow: none;
}
.dl-meta {
    display: flex;
    gap: 1.5rem;
    margin-top: 2rem;
    font-size: 0.78rem;
    color: var(--text-light);
}
.dl-meta span {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.dl-phone {
    flex-shrink: 0;
}
.phone-frame {
    width: 260px;
    height: 530px;
    background: linear-gradient(145deg, #1a1a2e, #16213e);
    border-radius: 36px;
    border: 3px solid #333;
    box-shadow: 0 25px 60px rgba(0,0,0,0.2);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2.5rem 1.5rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.phone-frame .notch {
    position: absolute;
    top: 12px;
    left: 50%;
    transform: translateX(-50%);
    width: 120px;
    height: 24px;
    background: #111;
    border-radius: 0 0 16px 16px;
}
.phone-frame .home-indicator {
    position: absolute;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
    width: 120px;
    height: 4px;
    background: rgba(255,255,255,0.15);
    border-radius: 2px;
}
.phone-frame .phone-logo {
    width: 80px;
    height: 80px;
    background: var(--surface);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    box-shadow: var(--logo-shadow);
}
.phone-frame .phone-logo img {
    width: 60px;
    height: 60px;
    object-fit: contain;
}
.phone-frame h3 {
    color: #fff;
    font-size: 1.05rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
}
.phone-frame p {
    color: rgba(255,255,255,0.5);
    font-size: 0.85rem;
    line-height: 1.5;
}
.phone-frame .phone-dots {
    display: flex;
    gap: 6px;
    margin-top: 1.5rem;
}
.phone-frame .phone-dots span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
}
.phone-frame .phone-dots span:nth-child(2) {
    background: var(--primary-color);
    width: 28px;
    border-radius: 4px;
}

@media (max-width: 800px) {
    .dl-inner {
        flex-direction: column-reverse;
        gap: 2rem;
    }
    .dl-info { text-align: center; }
    .dl-info .desc { margin: 0 auto 2rem; }
    .dl-cards { margin: 0 auto; }
    .dl-meta { justify-content: center; flex-wrap: wrap; }
    .phone-frame {
        width: 200px;
        height: 410px;
        border-radius: 28px;
        padding: 2rem 1.25rem;
    }
    .phone-frame .phone-logo { width: 64px; height: 64px; }
    .phone-frame .phone-logo img { width: 32px; height: 32px; }
    .phone-frame h3 { font-size: 1.15rem; }
    .phone-frame .notch { width: 90px; height: 20px; }
    .dl-info h1 { font-size: 1.7rem; }
}
@media (max-width: 480px) {
    .dl-section { padding: 2rem 0 1.5rem; }
    .dl-info h1 { font-size: 1.4rem; }
    .dl-cards { max-width: 100%; }
    .phone-frame {
        width: 160px;
        height: 330px;
        border-radius: 22px;
        padding: 1.5rem 1rem;
    }
    .phone-frame .phone-logo { width: 56px; height: 56px; }
    .phone-frame .phone-logo img { width: 28px; height: 28px; }
    .phone-frame h3 { font-size: 1rem; }
}
</style>

<div class="container dl-section">
    <div class="dl-inner">
        <div class="dl-info">
            <div class="header-logo">
                <img src="assets/images/logo-icon.png" alt="Smart Mall Logo">
            </div>

            <h1><span style="font-family:'Poppins',sans-serif;font-weight:700;letter-spacing:0.04em">Smart Mall</span></h1>

            <p class="desc">
                Shop smarter, faster, and anywhere. Download the official Smart Mall app for the best shopping experience on your phone.
            </p>

            <div class="dl-cards">
                <a href="download_app.php" class="dl-card">
                    <div class="dl-icon" style="background:transparent;padding:0;width:auto;height:auto;">
                        <img src="assets/images/Google-play-icon.png" alt="Google Play" style="width:44px;height:44px;object-fit:contain;">
                    </div>
                    <div class="dl-body">
                        <div class="top">Get it on</div>
                        <div class="name">Google Play</div>
                    </div>
                    <span class="dl-tag blue">APK <?= $apk_size ?> MB</span>
                </a>

                <div class="dl-card disabled">
                    <div class="dl-icon gray">
                        <svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C4.79 17.1 4.48 11.62 7.48 9.16c.89-.91 2.05-1.43 3.27-1.45 1.04-.02 2.02.42 2.72.42.68 0 1.95-.52 3.28-.44.56.02 2.12.22 3.13 1.65-.07.05-1.87 1.12-1.85 3.33.03 2.7 2.3 3.6 2.33 3.61-.02.07-.36 1.28-1.21 2.53zM14.96 5.2c.63-.76.98-1.8.84-2.85-.8.04-1.78.54-2.36 1.22-.54.63-.98 1.63-.84 2.58.87.06 1.76-.44 2.36-1.22z"/>
                        </svg>
                    </div>
                    <div class="dl-body">
                        <div class="top">Coming Soon</div>
                        <div class="name">iPhone / iOS</div>
                    </div>
                    <span class="dl-tag gray">Soon</span>
                </div>

                <div class="dl-card dl-qr-card" onclick="openQrModal()">
                    <img src="assets/images/qr-code.png" alt="QR Code" class="dl-qr-img" loading="lazy">
                    <div class="dl-body">
                        <div class="top">Scan with your phone</div>
                        <div class="name">QR Code</div>
                    </div>
                    <span class="dl-tag green" style="background:#e8d5f5;color:#6f42c1;">Tap to enlarge</span>
                </div>
            </div>
            <div id="qrModal" class="qr-modal" onclick="closeQrModal()">
                <span class="qr-modal-close">&times;</span>
                <img src="assets/images/qr-code.png" alt="QR Code" id="qrModalImg">
            </div>

            <div class="dl-meta">
                <span>
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <?= $apk_size ?> MB
                </span>
                <span>
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Android 8.0+
                </span>
                <span>
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    v1.0.0
                </span>
            </div>
        </div>

        <div class="dl-phone">
            <div class="phone-frame">
                <div class="notch"></div>
                <div class="home-indicator"></div>
                <div class="phone-logo">
                    <img src="assets/images/logo-icon.png" alt="Smart Mall Logo">
                </div>
                <h3 style="font-family:'Poppins',sans-serif;font-weight:700;letter-spacing:0.04em">Smart Mall</h3>
                <p>Your premium marketplace,<br>now in your pocket.</p>
                <div class="phone-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openQrModal() { document.getElementById('qrModal').classList.add('show'); }
function closeQrModal() { document.getElementById('qrModal').classList.remove('show'); }
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
