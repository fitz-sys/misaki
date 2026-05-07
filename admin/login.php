<?php
require_once __DIR__.'/../includes/auth.php';
$err = '';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(admin_login($_POST['username'] ?? '', $_POST['password'] ?? '')){
    header('Location: index.php'); exit;
  }
  $err = 'Invalid credentials.';
}
?><!DOCTYPE html><html><head><meta charset="utf-8"><title>Admin login</title>
<link rel="stylesheet" href="../css/styles.css">
</head><body style="background:var(--cream);min-height:100vh;display:grid;place-items:center">
<form method="post" class="admin-card" style="width:340px">
  <h1 class="font-display" style="font-size:1.75rem">Admin sign in</h1>
  <?php if($err): ?><div class="auth-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>
  <label>Username <input name="username" required></label>
  <label>Password <input name="password" type="password" required></label>
  <button class="btn btn-ink" type="submit">Sign in</button>
  <p style="margin-top:12px;font-size:.75rem;opacity:.7">default: admin / admin123</p>
</form>
</body></html>
