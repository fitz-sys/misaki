<?php
require_once __DIR__.'/../includes/auth.php';
if(!current_admin_id()){ header('Location: login.php'); exit; }
$tab = $_GET['tab'] ?? 'products';
function admin_nav($cur){
  $items = ['products'=>'Products','addons'=>'Add-ons','orders'=>'Orders'];
  echo '<nav class="admin-tabs">';
  foreach($items as $k=>$v) echo '<a class="'.($cur===$k?'active':'').'" href="?tab='.$k.'">'.$v.'</a>';
  echo '<a href="logout.php" style="margin-left:auto">Sign out</a></nav>';
}
?><!DOCTYPE html><html><head><meta charset="utf-8"><title>Misaki Admin</title>
<link rel="stylesheet" href="../css/styles.css"></head>
<body class="admin-body">
<header class="admin-header">
  <div class="brand-logo" style="font-family:'Cormorant Garamond';font-size:1.5rem;letter-spacing:.18em">MISAKI · ADMIN</div>
  <a href="../index.php" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.18em">View site →</a>
</header>
<main class="admin-main">
<?php admin_nav($tab); ?>
<?php
if($tab==='products') require __DIR__.'/products.php';
elseif($tab==='addons') require __DIR__.'/addons.php';
elseif($tab==='orders') require __DIR__.'/orders.php';
?>
</main>
</body></html>
