<?php
require_once __DIR__.'/../includes/auth.php';
$err = '';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(admin_login(trim($_POST['username']??''), $_POST['password']??'')){
    header('Location: index.php'); exit;
  }
  $err = 'Invalid credentials.';
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Login — Misaki</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500&family=Inter:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body style="background:var(--cream);min-height:100vh;display:grid;place-items:center">
<form method="post" class="admin-card" style="width:360px;display:flex;flex-direction:column;gap:16px">
  <h1 class="font-display" style="font-size:2rem;font-weight:400">Admin sign in</h1>
  <?php if($err): ?><div class="auth-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>
  <label style="display:flex;flex-direction:column;gap:6px;font-size:.75rem;font-weight:500;text-transform:uppercase;letter-spacing:.08em;color:var(--muted-fg)">
    Username
    <input name="username" required autocomplete="username">
  </label>
  <label style="display:flex;flex-direction:column;gap:6px;font-size:.75rem;font-weight:500;text-transform:uppercase;letter-spacing:.08em;color:var(--muted-fg)">
    Password
    <input name="password" type="password" required autocomplete="current-password">
  </label>
  <button class="btn btn-ink" type="submit" style="margin-top:8px">Sign in</button>
</form>
</body>
</html>