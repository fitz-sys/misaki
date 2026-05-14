<?php
$page        = 'home';
$title       = 'Misaki Handcrafted — Floral Studio';
$description = 'Handcrafted floral arrangements with quiet ritual and seasonal bloom.';
require __DIR__.'/includes/products.php';
require __DIR__.'/includes/header.php';

$featured = array_slice($PRODUCTS, 0, 3);

$FEATURES = [
  ['icon'=>'❀','title'=>'Seasonal','body'=>'Sourced weekly from local growers, every bloom chosen at its peak.'],
  ['icon'=>'✿','title'=>'Hand-tied','body'=>'Every stem placed by our florists with intention and care.'],
  ['icon'=>'❁','title'=>'Delivered','body'=>'Same-day delivery in the city for orders placed before 1pm.'],
];

$dynReviews = fetch_recent_reviews(3);
$REVIEWS = $dynReviews ?: [
  ['full_name'=>'Aiko T.','body'=>'The most beautiful arrangement I\'ve ever received. Quiet and considered.','rating'=>5],
  ['full_name'=>'Marcus L.','body'=>'Absolutely stunning. Will order again and again.','rating'=>5],
  ['full_name'=>'Sora K.','body'=>'Felt like a piece of art arrived at my door.','rating'=>5],
];
?>

<section class="hero">
  <img src="images/hero.jpg" alt="Floral arrangement">
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <div class="hero-eyebrow">美咲 · MISAKI</div>
    <h1>Blooms made with <em>quiet intention</em></h1>
    <p>Handcrafted floral arrangements rooted in seasonal rhythm and wabi-sabi beauty.</p>
    <div class="hero-cta">
      <a href="shop.php" class="btn btn-cream">Shop blooms <span data-icon="arrow"></span></a>
      <a href="gallery.php" class="btn btn-outline">Gallery</a>
    </div>
  </div>
  <div class="hero-scroll">Scroll · 下へ</div>
</section>

<section class="container section">
  <div class="features-grid">
    <?php foreach($FEATURES as $i=>$f): ?>
      <div class="feature reveal" style="transition-delay:<?= $i*100 ?>ms">
        <div style="font-size:1.5rem;color:var(--sage-deep)"><?= $f['icon'] ?></div>
        <h3><?= $f['title'] ?></h3>
        <p><?= $f['body'] ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="container section-pad">
  <div class="row-head reveal">
    <div>
      <div class="eyebrow">本日のおすすめ</div>
      <h2>Featured blooms</h2>
    </div>
    <a class="view-all" href="shop.php">View all <span data-icon="arrow"></span></a>
  </div>
  <div class="product-grid">
    <?php foreach($featured as $i=>$p): ?>
      <div class="reveal" style="transition-delay:<?= $i*80 ?>ms">
        <?php renderProductCard($p); ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="reviews">
  <div class="container section">
    <div class="text-center reveal">
      <div class="eyebrow">お客様の声</div>
      <h2>Words from our garden</h2>
    </div>
    <div class="reviews-grid">
      <?php foreach($REVIEWS as $i=>$r): ?>
        <figure class="review reveal" style="transition-delay:<?= $i*80 ?>ms">
          <div class="stars"><?= str_repeat('★',(int)($r['rating']??5)) ?></div>
          <blockquote>"<?= htmlspecialchars($r['body']) ?>"</blockquote>
          <figcaption>— <?= htmlspecialchars($r['full_name']) ?></figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__.'/includes/footer.php'; ?>