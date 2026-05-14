<?php
require_once __DIR__.'/../includes/auth.php';
if (current_admin_id()) { header('Location: index.php'); exit; }

// Try to get brand name from settings if table exists
$brand = 'MISAKI';
try {
  require_once __DIR__.'/../includes/settings.php';
  $brand = setting('brand_name', 'MISAKI');
} catch (Throwable $e) {}

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (admin_login(trim($_POST['username'] ?? ''), $_POST['password'] ?? '')) {
    header('Location: index.php'); exit;
  }
  $err = 'Invalid credentials.';
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Login — <?= htmlspecialchars($brand) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500&family=Inter:wght@400;500&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', system-ui, sans-serif;
      background:  #17211a;
      min-height:  100vh;
      display:     grid;
      place-items: center;
      padding:     24px;
      -webkit-font-smoothing: antialiased;
    }
    .login-card {
      background:    #1f2e24;
      border:        1px solid rgba(255,255,255,.07);
      border-radius: 16px;
      padding:       48px 40px;
      width:         100%;
      max-width:     380px;
      box-shadow:    0 24px 64px rgba(0,0,0,.4);
    }
    .login-brand {
      font-family:    'Cormorant Garamond', serif;
      font-size:      1.6rem;
      font-weight:    500;
      letter-spacing: .24em;
      color:          #e8e0d4;
      margin-bottom:  4px;
    }
    .login-sub {
      font-size:      .65rem;
      letter-spacing: .18em;
      text-transform: uppercase;
      color:          rgba(255,255,255,.3);
      margin-bottom:  36px;
    }
    .login-label {
      display:        flex;
      flex-direction: column;
      gap:            7px;
      font-size:      .68rem;
      font-weight:    600;
      letter-spacing: .1em;
      text-transform: uppercase;
      color:          rgba(255,255,255,.35);
      margin-bottom:  16px;
    }
    .login-label input {
      font-family:   'Inter', sans-serif;
      font-size:     .875rem;
      color:         #e8e0d4;
      background:    rgba(255,255,255,.06);
      border:        1px solid rgba(255,255,255,.1);
      border-radius: 6px;
      padding:       11px 14px;
      width:         100%;
      outline:       none;
      transition:    border-color .2s;
      appearance:    none;
    }
    .login-label input:focus { border-color: #6b8f6c; }
    .login-error {
      background:    rgba(176,40,40,.2);
      border:        1px solid rgba(176,40,40,.4);
      color:         #f87171;
      font-size:     .8rem;
      padding:       10px 14px;
      border-radius: 6px;
      margin-bottom: 20px;
    }
    .login-btn {
      width:          100%;
      padding:        12px;
      background:     #3d5a3e;
      color:          #c4d9c4;
      border:         none;
      border-radius:  6px;
      font-family:    'Inter', sans-serif;
      font-size:      .82rem;
      font-weight:    500;
      letter-spacing: .08em;
      cursor:         pointer;
      margin-top:     8px;
      transition:     background .2s;
    }
    .login-btn:hover { background: #4a6e4b; }
    .login-back {
      display:    block;
      text-align: center;
      margin-top: 20px;
      font-size:  .75rem;
      color:      rgba(255,255,255,.25);
      text-decoration: none;
      transition: color .2s;
    }
    .login-back:hover { color: rgba(255,255,255,.5); }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="login-brand"><?= htmlspecialchars($brand) ?></div>
    <div class="login-sub">Admin Panel</div>

    <?php if ($err): ?>
      <div class="login-error"><?= htmlspecialchars($err) ?></div>
    <?php endif; ?>

    <form method="post">
      <label class="login-label">
        Username
        <input name="username" required autocomplete="username">
      </label>
      <label class="login-label">
        Password
        <input name="password" type="password" required autocomplete="current-password">
      </label>
      <button class="login-btn" type="submit">Sign in →</button>
    </form>
    <a href="../index.php" class="login-back">← Back to storefront</a>
  </div>
</body>
</html>