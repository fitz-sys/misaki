<?php
require_once __DIR__.'/db.php';

function fetch_products($visibleOnly=true){
  $sql = 'SELECT p.product_id AS id, p.slug, p.name, p.jp_name AS jp,
                 t.name AS type, p.price, p.image, p.badge, p.description,
                 p.sales, p.created_at AS createdAt, p.is_visible
          FROM product p JOIN product_type t ON t.type_id=p.type_id';
  if($visibleOnly) $sql .= ' WHERE p.is_visible=1';
  $sql .= ' ORDER BY p.product_id';
  $rows = db()->query($sql)->fetchAll();
  foreach($rows as &$r){ $r['price'] = (float)$r['price']; $r['id']=(string)$r['id']; }
  return $rows;
}
function fetch_types(){
  $rows = db()->query('SELECT name FROM product_type ORDER BY type_id')->fetchAll();
  return array_merge(['All'], array_column($rows,'name'));
}
function find_product_by_slug($slug){
  foreach(fetch_products(false) as $p) if($p['slug']===$slug) return $p;
  return null;
}
function fetch_addons($activeOnly=true){
  $sql = 'SELECT addon_id AS id, name, price, is_active FROM addon';
  if($activeOnly) $sql .= ' WHERE is_active=1';
  $sql .= ' ORDER BY addon_id';
  $rows = db()->query($sql)->fetchAll();
  foreach($rows as &$r){ $r['price']=(float)$r['price']; $r['id']=(int)$r['id']; }
  return $rows;
}
function fetch_reviews_for_product($productId){
  $st = db()->prepare(
    'SELECT r.rating, r.body, r.created_at, u.full_name
       FROM review r JOIN user u ON u.user_id=r.user_id
      WHERE r.product_id=? ORDER BY r.created_at DESC');
  $st->execute([$productId]);
  return $st->fetchAll();
}
function fetch_recent_reviews($limit=3){
  $st = db()->prepare(
    'SELECT r.rating, r.body, r.created_at, u.full_name, p.name AS product_name
       FROM review r
       JOIN user u ON u.user_id=r.user_id
       JOIN product p ON p.product_id=r.product_id
       ORDER BY r.created_at DESC LIMIT '.(int)$limit);
  $st->execute();
  return $st->fetchAll();
}

// shared globals used by some pages
$PRODUCTS = fetch_products(true);
$TYPES    = fetch_types();
$ADDONS   = fetch_addons(true);
$SORTS    = ['Top sales','Latest','Price: Low to High','Price: High to Low'];

function findProductBySlug($slug){ return find_product_by_slug($slug); }

function renderProductCard($p){
  $json = htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8');
  $shareUrl = 'product.php?slug='.urlencode($p['slug']);
  ?>
  <div class="product-card" data-product='<?= $json ?>'>
    <a href="product.php?slug=<?= urlencode($p['slug']) ?>" style="display:block">
      <div class="product-image">
        <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy" width="800" height="1024">
        <?php if(!empty($p['badge'])): ?>
          <span class="badge"><?= htmlspecialchars($p['badge']) ?></span>
        <?php endif; ?>
        <div class="product-actions">
          <button class="quick" data-quick='<?= $json ?>'><span data-icon="eye"></span> Quick view</button>
          <button class="add" aria-label="Add to cart" data-add='<?= $json ?>'><span data-icon="plus"></span></button>
          <button class="share" aria-label="Share" data-share="<?= htmlspecialchars($shareUrl) ?>"><span data-icon="share"></span></button>
        </div>
      </div>
    </a>
    <div class="product-meta">
      <div>
        <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
        <div class="product-jp"><?= htmlspecialchars($p['jp']) ?> · <?= htmlspecialchars($p['type']) ?></div>
      </div>
      <div class="product-price">$<?= number_format($p['price'],2) ?></div>
    </div>
  </div>
  <?php
}
