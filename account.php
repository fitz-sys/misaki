<?php
require_once __DIR__.'/includes/auth.php';
require_login('login.php');
require_once __DIR__.'/includes/products.php';

$me = current_user();

// fetch orders + items needing reviews
$st = db()->prepare(
  'SELECT o.order_id,o.total,o.status,o.created_at
     FROM `order` o WHERE o.user_id=? ORDER BY o.created_at DESC');
$st->execute([$me['user_id']]);
$orders = $st->fetchAll();

// items per order
$itemsByOrder = [];
foreach($orders as $o){
  $st = db()->prepare(
    'SELECT oi.order_item_id, oi.product_id, p.name, p.image, p.slug, oi.qty, oi.line_total,
            (SELECT review_id FROM review WHERE order_id=oi.order_id AND product_id=oi.product_id) AS review_id
       FROM order_item oi JOIN product p ON p.product_id=oi.product_id
      WHERE oi.order_id=?');
  $st->execute([$o['order_id']]);
  $itemsByOrder[$o['order_id']] = $st->fetchAll();
}

// handle review submission
$msg = '';
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='review'){
  $orderId = (int)$_POST['order_id'];
  $productId = (int)$_POST['product_id'];
  $rating = max(1,min(5,(int)$_POST['rating']));
  $body = trim($_POST['body'] ?? '');
  // verify order belongs to user
  $st = db()->prepare('SELECT 1 FROM `order` WHERE order_id=? AND user_id=?');
  $st->execute([$orderId,$me['user_id']]);
  if($st->fetchColumn() && $body){
    try{
      $st = db()->prepare('INSERT INTO review (product_id,user_id,order_id,rating,body) VALUES (?,?,?,?,?)');
      $st->execute([$productId,$me['user_id'],$orderId,$rating,$body]);
      $msg = 'Thanks for your review!';
    }catch(Throwable $e){ $msg = 'You already reviewed this item.'; }
    header('Location: account.php?msg='.urlencode($msg)); exit;
  }
}
if(isset($_GET['msg'])) $msg = $_GET['msg'];

$page='account'; $title='Account — Misaki';
require __DIR__.'/includes/header.php';
?>
<div class="page-pad">
  <section class="container reveal">
    <div class="eyebrow">アカウント</div>
    <h1 style="font-size:clamp(2rem,4vw,2.75rem);margin-top:6px">Hi, <?= htmlspecialchars($me['full_name']) ?></h1>
    <p style="color:var(--muted-fg);font-size:.9rem;margin-top:6px"><?= htmlspecialchars($me['email']) ?> · <a href="logout.php" style="text-decoration:underline">Sign out</a></p>
    <?php if($msg): ?><div class="auth-success" style="margin-top:16px"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <h2 style="margin-top:48px;font-size:1.75rem">Your orders</h2>
    <?php if(!$orders): ?>
      <p style="color:var(--muted-fg);margin-top:12px;font-size:.9rem">No orders yet.</p>
    <?php else: foreach($orders as $o): ?>
      <div class="order-card">
        <div class="order-head">
          <div><strong>Order #<?= $o['order_id'] ?></strong> <span style="opacity:.6;font-size:.8rem"> · <?= htmlspecialchars($o['created_at']) ?></span></div>
          <div>$<?= number_format($o['total'],2) ?> · <span class="pill"><?= htmlspecialchars($o['status']) ?></span></div>
        </div>
        <div class="order-items">
          <?php foreach($itemsByOrder[$o['order_id']] as $it): ?>
            <div class="order-item">
              <img src="<?= htmlspecialchars($it['image']) ?>" alt="">
              <div style="flex:1">
                <div class="font-display" style="font-size:1.05rem"><?= htmlspecialchars($it['name']) ?></div>
                <div style="font-size:.75rem;color:var(--muted-fg)">× <?= $it['qty'] ?> · $<?= number_format($it['line_total'],2) ?></div>
                <?php if($it['review_id']): ?>
                  <div style="font-size:.75rem;color:var(--sage-deep);margin-top:6px">✓ Reviewed</div>
                <?php else: ?>
                  <details class="review-form">
                    <summary>Leave a review</summary>
                    <form method="post" style="margin-top:10px">
                      <input type="hidden" name="action" value="review">
                      <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                      <input type="hidden" name="product_id" value="<?= $it['product_id'] ?>">
                      <div class="rating-input">
                        <?php for($r=5;$r>=1;$r--): ?>
                          <input type="radio" id="r<?= $o['order_id'].'_'.$it['product_id'].'_'.$r ?>" name="rating" value="<?= $r ?>" <?= $r===5?'checked':'' ?>>
                          <label for="r<?= $o['order_id'].'_'.$it['product_id'].'_'.$r ?>">★</label>
                        <?php endfor; ?>
                      </div>
                      <textarea name="body" rows="3" placeholder="How was your bloom?" required></textarea>
                      <button class="btn btn-ink" type="submit" style="margin-top:8px">Submit review</button>
                    </form>
                  </details>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </section>
</div>
<?php require __DIR__.'/includes/footer.php'; ?>
