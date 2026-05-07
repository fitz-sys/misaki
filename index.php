<?php
$page = 'home';
$title = 'Misaki Handcrafted — Floral Studio';
$description = 'Lorem ipsum. Handcrafted floral arrangements with quiet ritual.';
require __DIR__.'/includes/products.php';
require __DIR__.'/includes/header.php';

$featured = array_slice($PRODUCTS, 0, 3);

$FEATURES = [
  ['icon'=>'❀','title'=>'Seasonal','body'=>'Lorem ipsum dolor sit amet, sourced weekly from local growers.'],
  ['icon'=>'✿','title'=>'Hand-tied','body'=>'Lorem ipsum, every stem placed by our florists with intention.'],
  ['icon'=>'❁','title'=>'Delivered','body'=>'Lorem ipsum dolor sit, same-day delivery in the city.'],
];
// dynamic reviews from DB; fall back to lorem if none
$dynReviews = fetch_recent_reviews(3);
$REVIEWS = $dynReviews ?: [
  ['full_name'=>'Aiko T.','body'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit. The most beautiful arrangement I\'ve ever received.','rating'=>5],
  ['full_name'=>'Marcus L.','body'=>'Lorem ipsum. Quiet, considered, and absolutely stunning. Will order again.','rating'=>5],
  ['full_name'=>'Sora K.','body'=>'Lorem ipsum dolor sit. Felt like a piece of art arrived at my door.','rating'=>5],
];
?>

<section class="hero">
  <img src="images/hero.jpg" alt="Floral arrangement">
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <div class="hero-eyebrow">美咲 · MISAKI</div>
    <h1>Lorem ipsum, <em>handcrafted</em> in bloom</h1>
    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt.</p>
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
      <div class="reveal" style="transition-delay:<?= $i*80 ?>ms"><?php renderProductCard($p); ?></div>
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
          <div class="stars"><?= str_repeat('★', (int)($r['rating'] ?? 5)) ?></div>
          <blockquote>"<?= htmlspecialchars($r['body']) ?>"</blockquote>
          <figcaption>— <?= htmlspecialchars($r['full_name']) ?></figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__.'/includes/footer.php'; ?>
