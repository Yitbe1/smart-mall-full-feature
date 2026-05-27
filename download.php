<?php
require_once __DIR__ . '/config.php';
$apk_path = __DIR__ . '/Apk/Android/smartmall.apk';
$apk_size = file_exists($apk_path) ? number_format(filesize($apk_path) / 1024 / 1024, 1) : '?';
$page_title = 'Download App - Smart Mall';

require_once __DIR__ . '/includes/header.php';
?>

<style>
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
}
.dl-info .header-logo {
    width: clamp(56px, 10vw, 100px);
    height: clamp(56px, 10vw, 100px);
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    border-radius: clamp(14px, 2.5vw, 24px);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: clamp(0.75rem, 2vw, 1.25rem);
    box-shadow: 0 8px 24px rgba(59,130,246,0.3);
}
.dl-info .header-logo svg {
    width: clamp(28px, 5vw, 50px);
    height: clamp(28px, 5vw, 50px);
    color: #fff;
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
.dl-card .dl-tag.green {
    background: #e6f7e6;
    color: #2e7d32;
}
.dl-card .dl-tag.gray {
    background: #f0f0f0;
    color: #aaa;
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
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    box-shadow: 0 10px 30px rgba(59,130,246,0.4);
}
.phone-frame .phone-logo svg {
    width: 50px;
    height: 50px;
    color: #fff;
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
    .phone-frame .phone-logo svg { width: 32px; height: 32px; }
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
    .phone-frame .phone-logo svg { width: 28px; height: 28px; }
    .phone-frame h3 { font-size: 1rem; }
}
</style>

<div class="container dl-section">
    <div class="dl-inner">
        <div class="dl-info">
            <div class="header-logo">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>

            <h1><span>Smart Mall</span></h1>

            <p class="desc">
                Shop smarter, faster, and anywhere. Download the official Smart Mall app for the best shopping experience on your phone.
            </p>

            <div class="dl-cards">
                <a href="download_app.php" class="dl-card">
                    <div class="dl-icon green">
                        <svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M3.609 1.814L13.792 12 3.61 22.186a.996.996 0 01-.61-.92V2.734a1 1 0 01.609-.92zm10.89 10.893l2.302 2.302-10.937 6.333 8.635-8.635zm3.199-3.198l2.807 1.626a1 1 0 010 1.73l-2.808 1.626L15.206 12l2.492-2.491zM5.864 2.658L16.8 8.99l-2.302 2.302-8.634-8.634z"/>
                        </svg>
                    </div>
                    <div class="dl-body">
                        <div class="top">Download APK</div>
                        <div class="name">Android</div>
                    </div>
                    <span class="dl-tag green"><?= $apk_size ?> MB</span>
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
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3>Smart Mall</h3>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
