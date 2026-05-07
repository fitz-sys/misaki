<?php
require_once __DIR__.'/includes/auth.php';
$err = '';
$next = $_GET['next'] ?? 'index.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  if(login_user($email,$pass)){ header('Location: '.$next); exit; }
  $err = 'Invalid email or password.';
}
$page='auth'; $title='Sign in — Misaki';
require __DIR__.'/includes/header.php';
?>
<div class="page-pad">
  <section class="container auth-wrap reveal">
    <div class="eyebrow">ログイン</div>
    <h1 style="font-size:clamp(2rem,4vw,2.75rem);margin-top:6px">Sign in</h1>
    <p style="color:var(--muted-fg);margin-top:8px;font-size:.9rem">Welcome back. Lorem ipsum dolor sit amet.</p>
    <?php if($err): ?><div class="auth-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>
    <form method="post" class="auth-form">
      <label>Email <input name="email" type="email" required></label>
      <label>Password <input name="password" type="password" required></label>
      <button class="btn btn-ink" type="submit">Sign in</button>
    </form>
    <p style="margin-top:20px;font-size:.85rem">No account yet?
      <a href="register.php?next=<?= urlencode($next) ?>" style="text-decoration:underline">Create one</a>
    </p>
  </section>
</div>
<?php require __DIR__.'/includes/footer.php'; ?>
