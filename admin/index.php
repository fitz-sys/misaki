<?php
require_once __DIR__.'/../includes/auth.php';
if(!current_admin_id()){ header('Location: login.php'); exit; }
$tab = $_GET['tab'] ?? 'products';

function admin_nav($cur){
  $items = ['products'=>'Products','addons'=>'Add-ons','orders'=>'Orders'];
  echo '<nav class="admin-tabs">';
  foreach($items as $k=>$v){
    $active = $cur===$k ? 'active' : '';
    echo "<a class=\"$active\" href=\"?tab=$k\">$v</a>";
  }
  echo '<a href="logout.php" style="margin-left:auto;color:#c0392b">Sign out</a></nav>';
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Misaki Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Inter:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body class="admin-body">
<header class="admin-header">
  <div style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;letter-spacing:.2em;font-weight:500">MISAKI · ADMIN</div>
  <a href="../index.php">← View site</a>
</header>
<main class="admin-main">
<?php admin_nav($tab); ?>
<?php
if     ($tab==='products') require __DIR__.'/products.php';
elseif ($tab==='addons')   require __DIR__.'/addons.php';
elseif ($tab==='orders')   require __DIR__.'/orders.php';
?>
</main>
</body>
</html>