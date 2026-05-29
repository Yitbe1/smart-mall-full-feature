<?php
// Shared Smart Mall header
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/currency.php';

// Prevent browser caching for session-dependent pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$current_page = basename($_SERVER['PHP_SELF']);
$page_title = isset($page_title) ? $page_title : 'Smart Mall - Your Online Marketplace';
$cart_count = 0;
$selected_currency = smartmall_selected_currency();
$currency_redirect = $_SERVER['REQUEST_URI'] ?? '/reference/index.php';

if (isset($_SESSION['user_id']) && function_exists('getDB')) {
    try {
        $stmt = getDB()->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $_SESSION['user_id']]);
        $cart_count = (int)$stmt->fetchColumn();
    } catch (Throwable $e) {
        $cart_count = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#007AFF">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Smart Mall">
    <link rel="apple-touch-icon" href="/assets/images/logo-icon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('smartmall-theme');
            if (savedTheme) {
                document.documentElement.setAttribute('data-theme', savedTheme);
            } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="512x512" href="/reference/assets/images/logo-icon.png">
    <style>
        :root {
            /* Premium Palette */
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #eff6ff;
            --secondary-color: #0f172a;
            --accent-color: #f59e0b;
            --text-dark: #0f172a;
            --text-light: #64748b;
            --bg-light: #f8fafc;
            --surface: #ffffff;
            --border-color: #e2e8f0;
            --input-border: #cbd5e1;
            --input-bg: #ffffff;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;

            /* Shadows & Depth */
            --shadow-sm: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);
            --shadow-md: 0 12px 24px -4px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 20px 40px -8px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 32px 64px -12px rgba(0, 0, 0, 0.12);

            /* Misc */
            --radius: 16px;
            --nav-bg: rgba(255, 255, 255, 0.94);
            --nav-border: rgba(241, 245, 249, 0.9);
            --footer-bg: #0f172a;
            --footer-text: rgba(255, 255, 255, 0.7);
            --footer-heading: #ffffff;

            /* ☀️ Light Mode Focus: White crisp barrier + White glow wrapping the text */
            --text-stroke-color: #ffffff;
            --text-focus-shadow: 0 0 2px #ffffff, 1px 2px 4px rgba(255, 255, 255, 0.5);

        }

        [data-theme='dark'] {
            --primary-color: #3b82f6;
            --primary-dark: #2563eb;
            --primary-light: #1e293b;
            --secondary-color: #f8fafc;
            --accent-color: #fbbf24;
            --text-dark: #f8fafc;
            --text-light: #94a3b8;
            --bg-light: #020617;
            --surface: #0f172a;
            --border-color: #1e293b;
            --input-border: #475569;
            --input-bg: #0f172a;
            --nav-bg: rgba(15, 23, 42, 0.96);
            --nav-border: rgba(30, 41, 59, 0.9);
            --footer-bg: #020617;
            --footer-text: rgba(248, 250, 252, 0.7);
            --footer-heading: #ffffff;

            /* Light Mode Defaults */
            --shadow-opacity-modifier: 1;
            --shadow-rgb: 0, 0, 0;

            /* Formulas multiplied by the modifier to scale visibility */
            --shadow-sm: 0 1px 3px 0 rgb(var(--shadow-rgb) / calc(0.1 * var(--shadow-opacity-modifier))), 0 1px 2px -1px rgb(var(--shadow-rgb) / calc(0.1 * var(--shadow-opacity-modifier)));
            --shadow: 0 4px 6px -1px rgb(var(--shadow-rgb) / calc(0.05 * var(--shadow-opacity-modifier))), 0 2px 4px -2px rgb(var(--shadow-rgb) / calc(0.05 * var(--shadow-opacity-modifier)));
            --shadow-md: 0 12px 24px -4px rgba(var(--shadow-rgb), calc(0.08 * var(--shadow-opacity-modifier)));
            --shadow-lg: 0 20px 40px -8px rgba(var(--shadow-rgb), calc(0.1 * var(--shadow-opacity-modifier)));
            --shadow-xl: 0 32px 64px -12px rgba(var(--shadow-rgb), calc(0.12 * var(--shadow-opacity-modifier)));

            /* 🌙 Dark Mode Focus: Black crisp barrier + High-contrast deep drop shadow */
            --text-stroke-color: #000000;
            --text-focus-shadow: 0 0 2px #000000, 1px 2px 5px rgba(0, 0, 0, 0.85);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            background-color: var(--bg-light);
            color: var(--text-dark);
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            overflow-x: hidden;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            line-height: 1.2;
            color: var(--text-dark);
        }

        img {
            display: block;
            max-width: 100%;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input,
        select,
        textarea {
            font: inherit;
        }

        .site-nav {
            position: sticky;
            top: 1.5rem;
            z-index: 1000;
            margin: 0 auto;
            width: min(1300px, calc(100% - 3rem));
            background: var(--nav-bg);
            backdrop-filter: blur(30px) saturate(200%);
            -webkit-backdrop-filter: blur(30px) saturate(200%);
            border: 1px solid var(--nav-border);
            border-radius: 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
        }

        .nav-container {
            padding: 0.6rem 2.5rem;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 2rem;
        }

        /* Theme Toggle Button */
        .theme-toggle {
            background: none;
            border: 1px solid var(--border-color);
            color: var(--text-dark);
            cursor: pointer;
            padding: 6px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            transition: all 0.3s ease;
        }

        .theme-toggle:hover {
            background: var(--primary-light);
            transform: rotate(15deg);
            color: var(--primary-color);
        }

        .theme-toggle svg {
            width: 17px;
            height: 17px;
            transition: all 0.3s ease;
        }

        .moon-icon {
            fill: currentColor;
            display: block;
        }

        .sun-icon {
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            display: none;
        }

        [data-theme='dark'] .sun-icon {
            display: block;
        }

        [data-theme='dark'] .moon-icon {
            display: none;
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 100px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 12px;
            pointer-events: none;
        }

        .toast {
            min-width: 300px;
            max-width: 450px;
            background: var(--surface);
            color: var(--text-dark);
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            border-left: 4px solid var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            pointer-events: auto;
            animation: toast-in 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
            backdrop-filter: blur(8px);
        }

        .toast.success {
            border-left-color: var(--success-color);
        }

        .toast.error {
            border-left-color: var(--danger-color);
        }

        .toast.warning {
            border-left-color: var(--warning-color);
        }

        .toast-content {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        .toast-close {
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .toast-close:hover {
            color: var(--text-dark);
        }

        @keyframes toast-in {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes toast-out {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* Skeleton Loaders */
        .skeleton {
            background: linear-gradient(90deg, var(--bg-light) 25%, var(--border-color) 50%, var(--bg-light) 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite;
            border-radius: 4px;
        }

        @keyframes skeleton-loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .nav-container {
            width: min(1240px, calc(100% - 28px));
            min-height: 56px;
            margin: 0 auto;
            padding: 0.4rem 1.5rem;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 0.6rem;
        }

        /* ── Logo Brand Style ── */
        .logo-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            color: var(--secondary-color);
            text-decoration: none;
            white-space: nowrap;
            transition: all 0.3s ease;
        }

        .logo-brand-icon {
            height: 34px;
            width: auto;
            object-fit: contain;
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.05));
        }

        .logo-brand:hover .logo-brand-icon {
            transform: translateX(4px) scale(1.08);
            filter: drop-shadow(0 4px 12px rgba(69, 168, 170, 0.3));
        }

        .logo-brand-text {
            font-family: 'Outfit', sans-serif;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--text-dark);
            transition: color 0.3s ease;
        }

        .logo-brand-dot {
            color: #45a8aa;
            transition: color 0.3s ease;
        }

        /* ── Preloader CSS ── */
        html.preloading,
        html.preloading body {
            overflow: hidden !important;
            height: 100% !important;
        }

        #smartmall-preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #080c14;
            z-index: 100000;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.8s cubic-bezier(0.76, 0, 0.24, 1), visibility 0.8s;
        }

        /* Fast-load: immediately hide preloader */
        html.fast-load #smartmall-preloader {
            display: none !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        .preloader-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .preloader-logo-wrapper {
            position: relative;
            width: 200px;
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .preloader-logo-icon {
            width: 140px;
            height: auto;
            z-index: 5;
            opacity: 0;
            transform: scale(0.5) translateY(10px);
            filter: drop-shadow(0 4px 20px rgba(69, 168, 170, 0.3));
            animation: preloader-icon-in 1.4s cubic-bezier(0.34, 1.56, 0.64, 1) 0.2s forwards;
        }

        .logo-glow {
            position: absolute;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(69, 168, 170, 0.2) 0%, rgba(69, 168, 170, 0) 70%);
            border-radius: 50%;
            z-index: 2;
            opacity: 0;
            animation: preloader-glow-in 1.2s ease-out 0.6s forwards, preloader-glow-pulse 3s ease-in-out infinite 1.8s;
        }

        /* Speed Streaks Motion Graphic */
        .speed-streaks {
            position: absolute;
            left: -50px;
            width: 90px;
            height: 70px;
            z-index: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            pointer-events: none;
        }

        .streak {
            height: 3px;
            background: linear-gradient(90deg, transparent, #45a8aa);
            border-radius: 3px;
            opacity: 0;
            transform: scaleX(0);
            transform-origin: left center;
        }

        .streak-1 {
            width: 45px;
            animation: streak-shoot 1.5s cubic-bezier(0.16, 1, 0.3, 1) 0.8s forwards;
        }

        .streak-2 {
            width: 65px;
            animation: streak-shoot 1.5s cubic-bezier(0.16, 1, 0.3, 1) 0.9s forwards;
        }

        .streak-3 {
            width: 40px;
            animation: streak-shoot 1.5s cubic-bezier(0.16, 1, 0.3, 1) 1.0s forwards;
        }

        .preloader-logo-text {
            font-family: 'Outfit', sans-serif;
            font-size: 2.5rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            display: flex;
            justify-content: center;
            margin-top: 1rem;
            perspective: 400px;
        }

        .preloader-logo-text span {
            display: inline-block;
            opacity: 0;
            transform: rotateX(-90deg) translateY(20px);
            transform-origin: top center;
        }

        .preloader-logo-text span.space {
            width: 0.4em;
        }

        .preloader-logo-tagline {
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            color: #94a3b8;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            margin-top: 0.75rem;
            opacity: 0;
            transform: translateY(15px);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .preloader-logo-tagline .tag-word {
            opacity: 0;
            transform: translateY(10px);
        }

        .preloader-logo-tagline .tag-dot {
            color: #45a8aa;
            font-weight: 800;
            opacity: 0;
        }

        /* Entrance / Exit Animation Classes */
        .preloader-exit {
            transform: translateY(-100%) !important;
            visibility: hidden;
        }

        /* Staggered text delays */
        .char-1 {
            animation: char-reveal 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.6s forwards;
        }

        .char-2 {
            animation: char-reveal 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.68s forwards;
        }

        .char-3 {
            animation: char-reveal 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.76s forwards;
        }

        .char-4 {
            animation: char-reveal 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.84s forwards;
        }

        .char-5 {
            animation: char-reveal 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.92s forwards;
        }

        .char-6 {
            animation: char-reveal 0.6s cubic-bezier(0.16, 1, 0.3, 1) 1.0s forwards;
        }

        .char-7 {
            animation: char-reveal 0.6s cubic-bezier(0.16, 1, 0.3, 1) 1.08s forwards;
        }

        .char-8 {
            animation: char-reveal 0.6s cubic-bezier(0.16, 1, 0.3, 1) 1.16s forwards;
        }

        .char-9 {
            animation: char-reveal 0.6s cubic-bezier(0.16, 1, 0.3, 1) 1.24s forwards;
        }

        .tag-reveal-anim {
            animation: tagline-reveal 0.8s cubic-bezier(0.16, 1, 0.3, 1) 1.3s forwards;
        }

        .tag-w1 {
            animation: word-reveal 0.5s cubic-bezier(0.16, 1, 0.3, 1) 1.4s forwards;
        }

        .tag-d1 {
            animation: dot-reveal 0.4s ease 1.6s forwards;
        }

        .tag-w2 {
            animation: word-reveal 0.5s cubic-bezier(0.16, 1, 0.3, 1) 1.7s forwards;
        }

        .tag-d2 {
            animation: dot-reveal 0.4s ease 1.9s forwards;
        }

        .tag-w3 {
            animation: word-reveal 0.5s cubic-bezier(0.16, 1, 0.3, 1) 2.0s forwards;
        }

        /* Animation Keyframes */
        @keyframes preloader-icon-in {
            0% {
                opacity: 0;
                transform: scale(0.4) translateY(20px) rotate(-12deg);
            }

            100% {
                opacity: 1;
                transform: scale(1) translateY(0) rotate(0deg);
            }
        }

        @keyframes preloader-glow-in {
            to {
                opacity: 1;
            }
        }

        @keyframes preloader-glow-pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.8;
            }

            50% {
                transform: scale(1.15);
                opacity: 1;
            }
        }

        @keyframes streak-shoot {
            0% {
                transform: scaleX(0);
                opacity: 0;
            }

            35% {
                transform: scaleX(1);
                opacity: 0.8;
            }

            100% {
                transform: scaleX(1.4) translateX(50px);
                opacity: 0;
            }
        }

        @keyframes char-reveal {
            to {
                opacity: 1;
                transform: rotateX(0) translateY(0);
            }
        }

        @keyframes tagline-reveal {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes word-reveal {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes dot-reveal {
            to {
                opacity: 1;
            }
        }

        .search-bar {
            position: relative;
            flex: 1 1 180px;
            min-width: 160px;
            max-width: 340px;
            display: flex;
            align-items: center;
            background: var(--bg-light);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 2px 0.6rem;
            transition: all 0.4s cubic-bezier(0.2, 1, 0.2, 1);
        }

        .search-bar:focus-within {
            background: var(--surface);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
            flex: 1 1 220px;
        }

        /* Live Search Styles */
        .search-bar {
            position: relative;
        }

        .live-search-results {
            position: absolute;
            top: 120%;
            left: 0;
            right: 0;
            background: var(--surface);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border: 1px solid var(--border-color);
            z-index: 9999;
            display: none;
            overflow: hidden;
            max-height: 400px;
            overflow-y: auto;
        }

        .site-nav {
            overflow: visible !important;
        }

        .nav-container {
            overflow: visible !important;
        }

        .live-search-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            border-bottom: 1px solid var(--border-color);
            text-decoration: none;
            color: var(--text-dark);
            transition: background 0.2s;
        }

        .live-search-item:last-child {
            border-bottom: none;
        }

        .live-search-item:hover {
            background: var(--bg-light);
        }

        .live-search-item img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 12px;
            background: var(--bg-light);
        }

        .live-search-item-info {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .live-search-item-title {
            font-size: 0.9rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .live-search-item-desc {
            font-size: 0.75rem;
            color: var(--text-light);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 2px;
        }

        .live-search-item-price {
            font-size: 0.85rem;
            color: var(--primary-color);
            font-weight: bold;
        }

        .live-search-loading {
            padding: 15px;
            text-align: center;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .nav-links {
            display: flex;
            gap: clamp(1.5rem, 2.5vw, 2.5rem);
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .nav-links a {
            position: relative;
            color: var(--text-light);
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 0.25rem 0;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            border-radius: 2px;
            transform: translateX(-50%);
            transition: width 0.3s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a.is-active {
            color: var(--primary-color);
        }

        .nav-links a.is-active::after {
            width: 100%;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.65rem;
        }

        .currency-form {
            display: flex;
            align-items: center;
        }

        .currency-form select,
        .drawer-currency-form select {
            border: 1px solid var(--border-color);
            border-radius: 999px;
            background: var(--surface);
            color: var(--text-dark);
            font-size: 0.78rem;
            font-weight: 800;
            padding: 0.48rem 0.7rem;
            cursor: pointer;
        }

        .currency-form select:focus,
        .drawer-currency-form select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            background: none;
            border: none;
            color: var(--text-dark);
            cursor: pointer;
            padding: 6px;
            width: 34px;
            height: 34px;
            transition: all 0.3s ease;
            position: relative;
        }

        .action-btn:hover {
            color: var(--primary-color);
            transform: scale(1.1);
        }

        .user-dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: var(--surface);
            min-width: 180px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border: 1px solid var(--nav-border);
            padding: 0.75rem 0;
            display: none;
            z-index: 10000;
            animation: dropdown-fade 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes dropdown-fade {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .user-dropdown:hover .dropdown-menu,
        .user-dropdown.is-open .dropdown-menu {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: var(--text-dark);
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background: var(--primary-light);
            color: var(--primary-color);
        }

        .dropdown-divider {
            height: 1px;
            background: var(--border-color);
            margin: 0.5rem 0;
        }

        .cart-count {
            position: absolute;
            top: 2px;
            right: 2px;
            display: grid;
            place-items: center;
            min-width: 18px;
            height: 18px;
            border-radius: 50%;
            background: var(--primary-color);
            color: #fff;
            font-size: 0.65rem;
            font-weight: 800;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-primary,
        .btn-secondary {
            display: inline-flex;
            min-height: 42px;
            align-items: center;
            justify-content: center;
            padding: 0.7rem 1rem;
            border: 1px solid transparent;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 800;
            text-decoration: none;
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease, color 0.18s ease;
        }

        .btn-primary {
            background: var(--primary-color);
            color: #fff !important;
            box-shadow: 0 10px 22px rgba(37, 99, 235, 0.2);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-secondary {
            border-color: var(--border-color);
            background: var(--surface);
            color: var(--text-dark) !important;
        }

        .btn-secondary:hover {
            border-color: var(--primary-color);
            color: var(--primary-color) !important;
            background: var(--primary-light);
        }

        .container {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 2rem 0;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
            background: #fff;
            box-shadow: var(--shadow);
        }

        .alert-success {
            color: #065f46;
            border-left-color: var(--success-color);
        }

        .alert-danger {
            color: #991b1b;
            border-left-color: var(--danger-color);
        }

        .alert-warning {
            color: #92400e;
            border-left-color: var(--warning-color);
        }

        .alert-info {
            color: #1e3a8a;
            border-left-color: var(--primary-color);
        }

        .product-card,
        .cart-summary,
        .checkout-summary,
        .checkout-form-section,
        .auth-container,
        .form-container,
        .product-card,
        .order-card,
        .confirmation-container,
        .stat-card,
        .cart-items-section {
            border: 1px solid var(--border-color);
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .product-card:hover,
        .order-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg) !important;
        }

        .product-image,
        .product-large-image,
        .cart-item-image,
        .order-item-image {
            background: var(--bg-light);
        }

        .product-name,
        .product-title,
        .section-title,
        .auth-header h1,
        .orders-header h1,
        .form-container h2,
        .cart-summary h3,
        .checkout-summary h3 {
            color: var(--text-dark) !important;
        }

        .btn-cart,
        .btn-view,
        .btn-checkout,
        .btn-place-order,
        .btn-submit,
        .btn-add-cart-detail,
        .btn-add,
        .btn-edit,
        .btn-action,
        .empty-cart a,
        .empty-orders a {
            border-radius: var(--radius);
            font-weight: 600;
            padding: 0.6rem 1.2rem;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-cart,
        .btn-checkout,
        .btn-place-order,
        .btn-submit,
        .btn-add-cart-detail,
        .btn-add,
        .btn-action.primary,
        .empty-cart a,
        .empty-orders a {
            background: var(--primary-color);
            color: #fff;
        }

        .btn-view,
        .btn-edit,
        .btn-action.secondary,
        .quantity-form button {
            background: var(--bg-light);
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        /* Premium Buttons */
        .btn-buy,
        .btn-buy-detail {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)) !important;
            color: #fff !important;
            font-weight: 800 !important;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border: none;
            border-radius: 8px;
            padding: 0.8rem 1.5rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .btn-buy:hover,
        .btn-buy-detail:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
            filter: brightness(1.1);
        }

        .btn-cart,
        .btn-add-cart-detail {
            background: var(--surface) !important;
            color: var(--primary-color) !important;
            border: 2px solid var(--primary-color) !important;
            font-weight: 700 !important;
            border-radius: 8px;
            padding: 0.8rem 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-cart:hover,
        .btn-add-cart-detail:hover {
            background: var(--primary-color) !important;
            color: #fff !important;
        }

        .btn-cart:disabled,
        .btn-add-cart-detail:disabled {
            background: var(--bg-light) !important;
            border-color: var(--border-color) !important;
            color: var(--text-light) !important;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* ── Mobile Hamburger Button ── */
        .mobile-menu-btn {
            display: none;
            align-items: center;
            justify-content: center;
            background: none;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-dark);
            cursor: pointer;
            padding: 6px;
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .mobile-menu-btn:hover {
            background: var(--primary-light);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        /* ── Mobile Drawer ── */
        .mobile-drawer {
            position: fixed;
            inset: 0;
            z-index: 9997;
            pointer-events: none;
            visibility: hidden;
        }

        .mobile-drawer.is-open {
            pointer-events: auto;
            visibility: visible;
        }

        .mobile-drawer-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            opacity: 0;
            transition: opacity 0.35s ease;
        }

        .mobile-drawer.is-open .mobile-drawer-backdrop {
            opacity: 1;
        }

        .mobile-drawer-panel {
            position: absolute;
            top: 0;
            left: 0;
            width: min(300px, 85vw);
            height: 100%;
            background: var(--surface);
            overflow-y: auto;
            transform: translateX(-100%);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            box-shadow: var(--shadow-xl);
        }

        .mobile-drawer.is-open .mobile-drawer-panel {
            transform: translateX(0);
        }

        .drawer-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
        }

        .drawer-close-btn {
            background: none;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-dark);
            cursor: pointer;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .drawer-close-btn:hover {
            background: var(--primary-light);
            color: var(--primary-color);
        }

        .drawer-search {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
        }

        .drawer-search-form {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--bg-light);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 0.5rem 0.75rem;
        }

        .drawer-search-form input {
            flex: 1;
            border: none;
            background: transparent;
            color: var(--text-dark);
            font-size: 0.95rem;
            outline: none;
        }

        .drawer-search-form button {
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            display: flex;
            align-items: center;
            padding: 4px;
        }

        .drawer-nav {
            padding: 0.5rem 0;
            flex: 1;
        }

        .drawer-nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.85rem 1.25rem;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 0.95rem;
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .drawer-nav-link:hover,
        .drawer-nav-link.is-active {
            background: var(--primary-light);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
        }

        .drawer-divider {
            height: 1px;
            background: var(--border-color);
            margin: 0.5rem 1.25rem;
        }

        /* ── Responsive Breakpoints ── */
        @media (max-width: 980px) {
            .site-nav {
                width: min(calc(100% - 2rem), 1300px);
            }

            .nav-container {
                grid-template-columns: 1fr auto 1fr;
                padding: 0.6rem 1rem;
                gap: 0.75rem;
                min-height: 64px;
            }

            .nav-links {
                display: none;
            }

            .mobile-menu-btn {
                display: flex;
                order: -1;
            }

            .search-bar {
                display: none;
            }
        }

        @media (max-width: 1220px) and (min-width: 981px) {
            .search-bar {
                display: none;
            }

            .nav-container {
                width: min(1120px, calc(100% - 24px));
            }

            .nav-links {
                gap: 1rem;
            }
        }

        @media (max-width: 640px) {
            .site-nav {
                width: min(calc(100% - 1rem), 1300px);
            }

            .nav-container {
                padding: 0.5rem 0.85rem;
                gap: 0.75rem;
            }

            .nav-actions {
                gap: 0.5rem;
            }

            .nav-actions>.currency-form {
                display: none;
            }

            .cart-link {
                display: none;
            }

            .logo-brand {
                gap: 0.35rem;
            }

            .logo-brand-text {
                font-size: 1.1rem;
            }

            .logo-brand-icon {
                height: 30px;
            }

            .btn-primary,
            .btn-secondary {
                padding: 0.62rem 0.75rem;
            }

            .toast-container {
                right: 12px;
                left: 12px;
            }

            .toast {
                min-width: unset;
                max-width: none;
                width: 100%;
            }
        }

        @media (max-width: 400px) {

            .action-btn,
            .theme-toggle {
                width: 34px;
                height: 34px;
            }

            .logo-brand {
                gap: 0.25rem;
            }

            .logo-brand-text {
                font-size: 0.9rem;
            }

            .logo-brand-icon {
                height: 24px;
            }
        }
    </style>
</head>

<body>
    <!-- ── Fullscreen Premium Preloader ── -->
    <div id="smartmall-preloader">
        <div class="preloader-content">
            <div class="preloader-logo-wrapper">
                <div class="speed-streaks">
                    <span class="streak streak-1"></span>
                    <span class="streak streak-2"></span>
                    <span class="streak streak-3"></span>
                </div>
                <img src="/reference/assets/images/logo-icon.png" alt="Smart Mall Logo Icon" class="preloader-logo-icon">
                <div class="logo-glow"></div>
            </div>
            <h1 class="preloader-logo-text">
                <span class="char-1">S</span>
                <span class="char-2">M</span>
                <span class="char-3">A</span>
                <span class="char-4">R</span>
                <span class="char-5">T</span>
                <span class="space"></span>
                <span class="char-6">M</span>
                <span class="char-7">A</span>
                <span class="char-8">L</span>
                <span class="char-9">L</span>
            </h1>
            <p class="preloader-logo-tagline tag-reveal-anim">
                <span class="tag-word tag-w1">Smart</span>
                <span class="tag-dot tag-d1">.</span>
                <span class="tag-word tag-w2">Secure</span>
                <span class="tag-dot tag-d2">.</span>
                <span class="tag-word tag-w3">Stylish</span>
            </p>
        </div>
    </div>

    <script>
        // Synchronous fast preloader check to prevent screen flash
        if (sessionStorage.getItem('smartmall-preloaded')) {
            document.documentElement.classList.add('fast-load');
        } else {
            document.documentElement.classList.add('preloading');
        }
    </script>

    <nav class="site-nav">
        <div id="toast-container" class="toast-container"></div>
        <div class="nav-container">
            <?php
            $current_dir = basename(dirname(__DIR__));
            $base_url = '/' . $current_dir;
            ?>

            <!-- Hamburger (mobile only) -->
            <button id="mobile-menu-btn" class="mobile-menu-btn" aria-label="Open menu" aria-expanded="false">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M3 6h18M3 12h18M3 18h18"></path>
                </svg>
            </button>

            <ul class="nav-links">
                <li><a class="<?php echo $current_page === 'index.php' ? 'is-active' : ''; ?>" href="/reference/index.php">Shop</a></li>
                <li><a class="<?php echo $current_page === 'orders.php' ? 'is-active' : ''; ?>" href="/reference/orders.php">Orders</a></li>
                <?php if ($current_page !== 'download.php'): ?>
                <li><a href="/reference/download.php">App</a></li>
                <?php endif; ?>
            </ul>

            <a href="/reference/index.php" class="logo-brand" aria-label="Smart Mall home" style="justify-self: center;">
                <img src="/reference/assets/images/logo-icon.png" alt="Smart Mall Logo Icon" class="logo-brand-icon">
                <span class="logo-brand-text">Smart Mall<span class="logo-brand-dot">.</span></span>
            </a>

            <div class="nav-actions">
                <form action="/reference/index.php#products" method="GET" class="search-bar">
                    <input type="text" name="q" id="live-search-input" placeholder="Search..." required autocomplete="off" style="padding: 0.5rem 0.75rem; flex: 1; min-width: 0;">
                    <button type="submit" class="action-btn" style="width: 34px; height: 34px; padding: 0;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="M21 21l-4.35-4.35"></path>
                        </svg>
                    </button>
                    <div id="live-search-results" class="live-search-results"></div>
                </form>

                <form action="/reference/set_currency.php" method="POST" class="currency-form" aria-label="Display currency">
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($currency_redirect); ?>">
                    <select name="currency" onchange="this.form.submit()" title="Display currency">
                        <?php foreach (smartmall_supported_currencies() as $currency): ?>
                            <option value="<?php echo $currency; ?>" <?php echo $selected_currency === $currency ? 'selected' : ''; ?>>
                                <?php echo $currency; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <a class="action-btn cart-link" href="/reference/cart.php">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span class="cart-count"><?php echo $cart_count; ?></span>
                </a>

                <div class="user-dropdown">
                    <button class="action-btn" aria-label="Account">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </button>
                    <div class="dropdown-menu">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div style="padding: 0.5rem 1.25rem; font-size: 0.8rem; color: var(--text-light);">
                                Signed in as<br>
                                <strong style="color: var(--text-dark);"><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="/reference/orders.php" class="dropdown-item">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                                My Orders
                            </a>
                            <a href="/reference/wishlist.php" class="dropdown-item">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                                Wishlist
                            </a>
                            <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                                <a href="/reference/admin/dashboard.php" class="dropdown-item">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    Admin Panel
                                </a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a href="/reference/logout.php" class="dropdown-item" style="color: var(--danger-color);">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Logout
                            </a>
                        <?php else: ?>
                            <a href="/reference/login.php" class="dropdown-item">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Login
                            </a>
                            <a href="/reference/register.php" class="dropdown-item">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                                Register
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <button id="theme-toggle" class="theme-toggle">
                    <svg class="moon-icon" viewBox="0 0 24 24">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                    <svg class="sun-icon" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="4.22" x2="19.78" y2="5.64"></line>
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    <!-- Mobile Navigation Drawer -->
    <div id="mobile-drawer" class="mobile-drawer" role="dialog" aria-modal="true" aria-label="Navigation">
        <div class="mobile-drawer-backdrop" id="drawer-backdrop"></div>
        <div class="mobile-drawer-panel">
            <div class="drawer-header">
                <a href="/reference/index.php" class="logo-brand logo-brand-drawer">
                    <img src="/reference/assets/images/logo-icon.png" alt="Smart Mall Logo Icon" class="logo-brand-icon">
                    <span class="logo-brand-text">Smart Mall<span class="logo-brand-dot">.</span></span>
                </a>
                <button id="drawer-close-btn" class="drawer-close-btn" aria-label="Close menu">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M18 6L6 18M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="drawer-search">
                <form action="/reference/index.php#products" method="GET" class="drawer-search-form">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="M21 21l-4.35-4.35"></path>
                    </svg>
                    <input type="text" name="q" placeholder="Search products..." autocomplete="off">
                    <button type="submit"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M9 18l6-6-6-6"></path>
                        </svg></button>
                </form>
            </div>
            <nav class="drawer-nav">
                <form action="/reference/set_currency.php" method="POST" class="drawer-currency-form" style="padding:0.65rem 1.25rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;">
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($currency_redirect); ?>">
                    <span style="font-size:0.82rem;font-weight:800;color:var(--text-light);text-transform:uppercase;letter-spacing:0.08em;">Currency</span>
                    <select name="currency" onchange="this.form.submit()" title="Display currency">
                        <?php foreach (smartmall_supported_currencies() as $currency): ?>
                            <option value="<?php echo $currency; ?>" <?php echo $selected_currency === $currency ? 'selected' : ''; ?>>
                                <?php echo $currency; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <a href="/reference/index.php" class="drawer-nav-link <?php echo $current_page === 'index.php' ? 'is-active' : ''; ?>">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                    </svg>
                    Shop
                </a>
                <a href="/reference/orders.php" class="drawer-nav-link <?php echo $current_page === 'orders.php' ? 'is-active' : ''; ?>">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    My Orders
                </a>
                <a href="/reference/wishlist.php" class="drawer-nav-link <?php echo $current_page === 'wishlist.php' ? 'is-active' : ''; ?>">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                    Wishlist
                </a>
                <a href="/reference/cart.php" class="drawer-nav-link">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Cart
                </a>
                <a href="/reference/about.php" class="drawer-nav-link <?php echo $current_page === 'about.php' ? 'is-active' : ''; ?>">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    About
                </a>
                <a href="/reference/contact.php" class="drawer-nav-link <?php echo $current_page === 'contact.php' ? 'is-active' : ''; ?>">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Contact
                </a>
                <?php if ($current_page !== 'download.php'): ?>
                <a href="/reference/download.php" class="drawer-nav-link">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    App
                </a>
                <?php endif; ?>
                <div class="drawer-divider"></div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div style="padding:0.65rem 1.25rem;font-size:0.82rem;color:var(--text-light);">Signed in as <strong style="color:var(--text-dark);"><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></div>
                    <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                        <a href="/reference/admin/dashboard.php" class="drawer-nav-link">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            Admin Panel
                        </a>
                    <?php endif; ?>
                    <a href="/reference/logout.php" class="drawer-nav-link" style="color:var(--danger-color);">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Logout
                    </a>
                <?php else: ?>
                    <a href="/reference/login.php" class="drawer-nav-link">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Login
                    </a>
                    <a href="/reference/register.php" class="drawer-nav-link">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        Register
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </div>

    <!-- Live Search Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Premium Preloader exit animation logic
            const preloader = document.getElementById('smartmall-preloader');
            if (preloader && !sessionStorage.getItem('smartmall-preloaded')) {
                setTimeout(() => {
                    preloader.classList.add('preloader-exit');
                    document.documentElement.classList.remove('preloading');
                    document.documentElement.classList.add('preloaded-animation');
                    sessionStorage.setItem('smartmall-preloaded', 'true');
                }, 2600); // 2.6 seconds matches the CSS motion animation timeline
            }

            // Toggle nav background on scroll
            const siteNav = document.querySelector('.site-nav');
            if (siteNav) {
                window.addEventListener('scroll', () => {
                    if (window.scrollY > 20) {
                        siteNav.classList.add('scrolled');
                    } else {
                        siteNav.classList.remove('scrolled');
                    }
                });
            }

            // User dropdown: click to toggle on touch devices
            const userBtn = document.querySelector('.user-dropdown > .action-btn');
            const userDropdown = document.querySelector('.user-dropdown');
            if (userBtn && userDropdown) {
                userBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userDropdown.classList.toggle('is-open');
                });
                document.addEventListener('click', function() {
                    userDropdown.classList.remove('is-open');
                });
            }

            // Live Search Setup (wrapped conditionally to avoid script-breaking early returns)
            const searchInput = document.getElementById('live-search-input');
            const resultsContainer = document.getElementById('live-search-results');
            let debounceTimer;

            if (searchInput && resultsContainer) {
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                        resultsContainer.style.display = 'none';
                    }
                });

                searchInput.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    const query = this.value.trim();

                    if (query.length < 2) {
                        resultsContainer.style.display = 'none';
                        return;
                    }

                    debounceTimer = setTimeout(() => {
                        resultsContainer.style.display = 'block';
                        resultsContainer.innerHTML = `
                            <div class="live-search-item">
                                <div class="skeleton" style="width:40px;height:40px;border-radius:4px;margin-right:12px;"></div>
                                <div class="live-search-item-info" style="flex:1;">
                                    <div class="skeleton" style="width:70%;height:14px;margin-bottom:8px;"></div>
                                    <div class="skeleton" style="width:40%;height:12px;"></div>
                                </div>
                            </div>
                        `;

                        const fetchPath = '/reference/api/search.php';

                        fetch(`${fetchPath}?q=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.length === 0) {
                                    resultsContainer.innerHTML = '<div class="live-search-loading">No results found.</div>';
                                    return;
                                }

                                let html = '';
                                data.forEach(product => {
                                    const imgTag = product.image_url ?
                                        `<img src="${product.image_url}" alt="${product.name}">` :
                                        `<div style="width:40px;height:40px;background:#eee;border-radius:4px;margin-right:12px;display:flex;align-items:center;justify-content:center;">📦</div>`;

                                    const shortDesc = product.description ? (product.description.length > 50 ? product.description.substring(0, 50) + '...' : product.description) : '';

                                    html += `
                                        <a href="/reference/product.php?product_id=${product.product_id}" class="live-search-item">
                                            ${imgTag}
                                            <div class="live-search-item-info">
                                                <span class="live-search-item-title">${product.name}</span>
                                                <span class="live-search-item-desc">${shortDesc}</span>
                                                <span class="live-search-item-price">${product.display_price || parseFloat(product.price).toFixed(2)}</span>
                                            </div>
                                        </a>
                                    `;
                                });

                                html += `
                                    <a href="/reference/index.php?q=${encodeURIComponent(query)}#products" class="live-search-item" style="justify-content: center; color: var(--primary-color); font-weight: bold; background: var(--bg-light);">
                                        View all results for "${query}"
                                    </a>
                                `;

                                resultsContainer.innerHTML = html;
                            })
                            .catch(error => {
                                console.error('Search error:', error);
                                resultsContainer.innerHTML = '<div class="live-search-loading">Error fetching results.</div>';
                            });
                    }, 300); // 300ms debounce
                });

                searchInput.addEventListener('focus', function() {
                    if (this.value.trim().length >= 2 && resultsContainer.innerHTML.trim() !== '') {
                        resultsContainer.style.display = 'block';
                    }
                });
            }

            // Theme Toggle Functionality
            const themeToggle = document.getElementById('theme-toggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', () => {
                    const currentTheme = document.documentElement.getAttribute('data-theme');
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                    document.documentElement.setAttribute('data-theme', newTheme);
                    localStorage.setItem('smartmall-theme', newTheme);
                });
            }

            // ── Mobile Drawer ──
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const drawerCloseBtn = document.getElementById('drawer-close-btn');
            const mobileDrawer = document.getElementById('mobile-drawer');
            const drawerBackdrop = document.getElementById('drawer-backdrop');

            function openDrawer() {
                if (mobileDrawer) {
                    mobileDrawer.classList.add('is-open');
                    document.body.style.overflow = 'hidden';
                    mobileMenuBtn && mobileMenuBtn.setAttribute('aria-expanded', 'true');
                }
            }

            function closeDrawer() {
                if (mobileDrawer) {
                    mobileDrawer.classList.remove('is-open');
                    document.body.style.overflow = '';
                    mobileMenuBtn && mobileMenuBtn.setAttribute('aria-expanded', 'false');
                }
            }
            if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', openDrawer);
            if (drawerCloseBtn) drawerCloseBtn.addEventListener('click', closeDrawer);
            if (drawerBackdrop) drawerBackdrop.addEventListener('click', closeDrawer);
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') closeDrawer();
            });

            // Global Toast Function
            window.showToast = function(message, type = 'success') {
                const container = document.getElementById('toast-container');
                if (!container) return;

                const toast = document.createElement('div');
                toast.className = `toast ${type}`;

                let icon = '✓';
                if (type === 'error') icon = '✕';
                if (type === 'warning') icon = '⚠';

                toast.innerHTML = `
                    <div class="toast-content">
                        <span class="toast-icon">${icon}</span>
                        <span>${message}</span>
                    </div>
                    <button class="toast-close" onclick="this.parentElement.remove()">✕</button>
                `;

                container.appendChild(toast);

                setTimeout(() => {
                    toast.style.animation = 'toast-out 0.5s ease forwards';
                    setTimeout(() => toast.remove(), 500);
                }, 4000);
            };
        });
    </script>