<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Something went wrong — Smart Mall</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; background:#f7f7f7; display:flex; justify-content:center; align-items:center; min-height:100vh; color:#333; }
        .card { background:#fff; padding:3rem 4rem; border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,.08); text-align:center; max-width:480px; }
        h1 { font-size:1.5rem; margin-bottom:.75rem; color:#222; }
        p { color:#666; line-height:1.6; margin-bottom:1.5rem; }
        .btn { display:inline-block; background:#2563eb; color:#fff; padding:.75rem 2rem; border-radius:8px; text-decoration:none; font-weight:500; }
        .btn:hover { background:#1d4ed8; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Something went wrong</h1>
        <p>We encountered an unexpected error. Our team has been notified. Please try again shortly.</p>
        <a href="<?= BASE_PATH ?>/index.php" class="btn">Go to Homepage</a>
    </div>
</body>
</html>
