<?php
require_once __DIR__.'/includes/auth.php';
require_login('login.php?next='.urlencode('checkout.php'));
require_once __DIR__.'/includes/products.php';

$msg = '';
$createdOrderId = null;

if($_SERVER['REQUEST_METHOD']==='POST'){
  $raw = $_POST['cart'] ?? '[]';
  $cart = json_decode($raw, true) ?: [];
  if(!$cart){ header('Location: cart.php'); exit; }

  $pdo = db();
  $pdo->beginTransaction();
  try{
    // recompute server-side totals
    $allProducts = [];
    foreach(fetch_products(false) as $p) $allProducts[$p['id']] = $p;
    $allAddons = [];
    foreach(fetch_addons(false) as $a) $allAddons[$a['id']] = $a;

    $total = 0;
    foreach($cart as &$line){
      $prod = $allProducts[(string)$line['id']] ?? null;
      if(!$prod) throw new Exception('Unknown product');
      $unit = (float)$prod['price'];
      $addonSum = 0;
      $addonRows = [];
      foreach(($line['addons'] ?? []) as $aid){
        $a = $allAddons[(int)$aid] ?? null;
        if(!$a) continue;
        $addonSum += (float)$a['price'];
        $addonRows[] = $a;
      }
      $line['_unit'] = $unit;
      $line['_addonRows'] = $addonRows;
      $line['_lineTotal'] = ($unit + $addonSum) * (int)$line['qty'];
      $total += $line['_lineTotal'];
    }
    unset($line);

    $st = $pdo->prepare('INSERT INTO `order` (user_id,status,total) VALUES (?,?,?)');
    $st->execute([current_user_id(),'paid',$total]);
    $orderId = (int)$pdo->lastInsertId();

    foreach($cart as $line){
      $st = $pdo->prepare('INSERT INTO order_item (order_id,product_id,qty,unit_price,line_total) VALUES (?,?,?,?,?)');
      $st->execute([$orderId,(int)$line['id'],(int)$line['qty'],$line['_unit'],$line['_lineTotal']]);
      $itemId = (int)$pdo->lastInsertId();
      foreach($line['_addonRows'] as $a){
        $st = $pdo->prepare('INSERT INTO order_item_addon (order_item_id,addon_id,unit_price) VALUES (?,?,?)');
        $st->execute([$itemId,(int)$a['id'],(float)$a['price']]);
      }
      // bump sales counter
      $pdo->prepare('UPDATE product SET sales=sales+? WHERE product_id=?')
          ->execute([(int)$line['qty'],(int)$line['id']]);
    }
    $pdo->commit();
    $createdOrderId = $orderId;
  }catch(Throwable $e){
    $pdo->rollBack();
    $msg = 'Order failed: '.$e->getMessage();
  }
}

$page='cart'; $title='Checkout — Misaki';
require __DIR__.'/includes/header.php';
?>
<div class="page-pad">
  <section class="container reveal" data-checkout-page>
    <div class="eyebrow">お支払い</div>
    <h1 style="font-size:clamp(2rem,4vw,2.75rem);margin-top:6px">Checkout</h1>

    <?php if($createdOrderId): ?>
      <div class="auth-success" style="margin-top:24px">Order #<?= $createdOrderId ?> placed. Lorem ipsum — thank you!</div>
      <p style="margin-top:24px"><a class="btn btn-ink" href="account.php">Leave a review →</a></p>
      <script>localStorage.removeItem('misaki_cart');</script>
    <?php elseif($msg): ?>
      <div class="auth-error" style="margin-top:24px"><?= htmlspecialchars($msg) ?></div>
    <?php else: ?>
      <p style="color:var(--muted-fg);margin-top:8px;font-size:.9rem">Please review your cart and confirm.</p>
      <div class="checkout-summary" style="margin-top:24px"></div>
      <form method="post" id="checkoutForm" style="margin-top:24px">
        <input type="hidden" name="cart" id="cartJson">
        <button class="btn btn-ink" type="submit">Place order</button>
      </form>
    <?php endif; ?>
  </section>
</div>
<?php require __DIR__.'/includes/footer.php'; ?>
