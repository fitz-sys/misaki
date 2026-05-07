<?php
require __DIR__.'/includes/products.php';
$slug = $_GET['slug'] ?? '';
$p = findProductBySlug($slug);
if(!$p){
  http_response_code(404);
  $page=''; $title='Not found — Misaki';
  require __DIR__.'/includes/header.php';
  echo '<div class="container page-pad" style="text-align:center;padding-bottom:96px"><h1 style="font-size:3rem">404</h1><p style="margin-top:16px;color:var(--muted-fg)">Lorem ipsum — that bloom isn\'t here.</p><a class="btn btn-ink" style="margin-top:32px" href="shop.php">Back to shop</a></div>';
  require __DIR__.'/includes/footer.php';
  exit;
}
$page='shop'; $title = $p['name'].' — Misaki Handcrafted';
$description = $p['description'];
require __DIR__.'/includes/header.php';
$json = htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8');
$reviews = fetch_reviews_for_product($p['id']);
?>
<div class="container product-page reveal" data-product-page='<?= $json ?>'>
  <div class="image"><img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>"></div>
  <div>
    <?php if(!empty($p['badge'])): ?>
      <span class="badge" style="position:static;display:inline-block"><?= htmlspecialchars($p['badge']) ?></span>
    <?php endif; ?>
    <h1 style="margin-top:16px"><?= htmlspecialchars($p['name']) ?></h1>
    <div class="font-jp" style="font-size:.875rem;color:var(--muted-fg);margin-top:4px"><?= htmlspecialchars($p['jp']) ?> · <?= htmlspecialchars($p['type']) ?></div>
    <div class="price">$<?= number_format($p['price'],2) ?></div>
    <p class="desc"><?= htmlspecialchars($p['description']) ?></p>

    <div class="addon-block">
      <div class="addon-head">Add-ons</div>
      <div class="addon-list">
        <?php foreach($ADDONS as $a): ?>
          <label class="addon-row">
            <input type="checkbox" class="pp-addon" value="<?= (int)$a['id'] ?>" data-name="<?= htmlspecialchars($a['name']) ?>" data-price="<?= $a['price'] ?>">
            <span><?= htmlspecialchars($a['name']) ?></span>
            <span class="addon-price">+₱<?= number_format($a['price'],2) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="product-actions-page">
      <div class="qty">
        <button class="pp-minus" data-icon="minus"></button>
        <span class="pp-qty">1</span>
        <button class="pp-plus" data-icon="plus"></button>
      </div>
      <button class="pp-add btn btn-ink" style="flex:1">Add to cart</button>
    </div>
    <div class="product-share">
      <button class="pp-share" style="display:inline-flex;align-items:center;gap:6px;opacity:.7"><span data-icon="share"></span> Share this bloom</button>
    </div>
  </div>
</div>

<section class="container section reveal" style="padding-top:64px">
  <div class="eyebrow">お客様の声</div>
  <h2 style="font-size:clamp(1.75rem,3vw,2.25rem);margin-top:6px">Customer reviews</h2>
  <?php if(!$reviews): ?>
    <p style="margin-top:24px;color:var(--muted-fg);font-size:.9rem">No reviews yet — be the first after your order.</p>
  <?php else: ?>
    <div class="reviews-grid" style="margin-top:32px">
      <?php foreach($reviews as $r): ?>
        <figure class="review">
          <div class="stars"><?= str_repeat('★',(int)$r['rating']) ?></div>
          <blockquote>"<?= htmlspecialchars($r['body']) ?>"</blockquote>
          <figcaption>— <?= htmlspecialchars($r['full_name']) ?></figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require __DIR__.'/includes/footer.php'; ?>
