<?php
session_start();
$SECRET = 'smartmalltest!'; // Change this

if (!isset($_SESSION['unlocked']) || $_SESSION['unlocked'] !== true) {
    if (isset($_POST['pass']) && $_POST['pass'] === $SECRET) {
        $_SESSION['unlocked'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    $error = isset($_POST['pass']) ? '<p class="error">❌ Incorrect password</p>' : '';
    die('<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <title>🔐 Secure Access</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 15px;
                line-height: 1.5;
            }
            .container {
                background: white;
                padding: 30px 25px;
                border-radius: 12px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                width: 100%;
                max-width: 380px;
                animation: slideUp 0.4s ease;
            }
            @keyframes slideUp {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .icon {
                font-size: 40px;
                text-align: center;
                margin-bottom: 15px;
            }
            h1 {
                color: #1f2937;
                font-size: 22px;
                text-align: center;
                margin-bottom: 8px;
                font-weight: 700;
            }
            .subtitle {
                color: #6b7280;
                text-align: center;
                margin-bottom: 25px;
                font-size: 14px;
            }
            .error {
                background: #fef2f2;
                color: #dc2626;
                padding: 10px;
                border-radius: 6px;
                margin-bottom: 15px;
                font-size: 14px;
                text-align: center;
                border-left: 3px solid #dc2626;
            }
            input[type="password"] {
                width: 100%;
                padding: 14px 15px;
                border: 2px solid #e5e7eb;
                border-radius: 8px;
                font-size: 16px;
                transition: all 0.3s;
                margin-bottom: 12px;
                -webkit-appearance: none;
            }
            input[type="password"]:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
            }
            button {
                width: 100%;
                padding: 14px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s;
                -webkit-appearance: none;
                touch-action: manipulation;
            }
            button:hover {
                transform: translateY(-1px);
                box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
            }
            button:active {
                transform: translateY(0);
                box-shadow: 0 4px 8px rgba(102, 126, 234, 0.2);
            }
            .footer {
                margin-top: 20px;
                text-align: center;
                color: #9ca3af;
                font-size: 12px;
            }
            @media (max-width: 480px) {
                body { padding: 10px; }
                .container { 
                    padding: 25px 20px; 
                    border-radius: 10px;
                }
                .icon { font-size: 36px; margin-bottom: 12px; }
                h1 { font-size: 20px; }
                .subtitle { font-size: 13px; margin-bottom: 20px; }
                input[type="password"], button { 
                    padding: 13px 14px; 
                    font-size: 16px;
                }
            }
            @media (max-width: 360px) {
                .container { padding: 20px 18px; }
                h1 { font-size: 18px; }
                .subtitle { font-size: 12px; }
                .icon { font-size: 32px; }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="icon">🔒</div>
            <h1>Restricted Access</h1>
            <p class="subtitle">Enter password to continue</p>
            ' . $error . '
            <form method="POST">
                <input type="password" name="pass" placeholder="Password" required autofocus autocomplete="off">
                <button type="submit">Unlock</button>
            </form>
            <div class="footer">🔐 Secure</div>
        </div>
    </body>
    </html>');
}
?>