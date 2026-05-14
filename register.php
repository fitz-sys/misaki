<?php
require_once __DIR__.'/includes/auth.php';
$err  = '';
$next = $_GET['next'] ?? 'index.php';
if(!preg_match('/^[a-z0-9_.\/\-?=%]+$/i',$next)) $next='index.php';

if($_SERVER['REQUEST_METHOD']==='POST'){
  $email = trim($_POST['email'] ?? '');
  $name  = trim($_POST['name']  ?? '');
  $pass  = $_POST['password'] ?? '';
  if(strlen($pass)<6)
    $err = 'Password must be at least 6 characters.';
  elseif(!filter_var($email,FILTER_VALIDATE_EMAIL))
    $err = 'Please enter a valid email.';
  else{
    try{
      register_user($email,$pass,$name?:'Customer');
      header('Location: '.$next); exit;
    } catch(Throwable $e){
      $err = 'Email already registered.';
    }
  }
}
$page='auth'; $title='Create account — Misaki';
require __DIR__.'/includes/header.php';
?>
<div class="page-pad">
  <section class="container auth-wrap reveal">
    <div class="eyebrow">登録</div>
    <h1 style="font-size:clamp(2rem,4vw,2.75rem);margin-top:6px">Create account</h1>
    <?php if($err): ?><div class="auth-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>
    <form method="post" class="auth-form" novalidate>
      <label>Full name <input name="name" required autocomplete="name" value="<?= htmlspecialchars($_POST['name']??'') ?>"></label>
      <label>Email <input name="email" type="email" required autocomplete="email" value="<?= htmlspecialchars($_POST['email']??'') ?>"></label>
      <label>Password (6+ chars) <input name="password" type="password" required minlength="6" autocomplete="new-password"></label>
      <button class="btn btn-ink" type="submit">Create account</button>
    </form>
    <p style="margin-top:20px;font-size:.85rem;color:var(--muted-fg)">
      Already have an account?
      <a href="login.php?next=<?= urlencode($next) ?>" style="text-decoration:underline;color:var(--ink)">Sign in</a>
    </p>
  </section>
</div>
<?php require __DIR__.'/includes/footer.php'; ?>