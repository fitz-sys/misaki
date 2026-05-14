<?php
$page        = 'shop';
$title       = 'Shop — Misaki Handcrafted';
$description = 'Browse handcrafted bouquets, ikebana arrangements, and dried botanicals.';
require __DIR__.'/includes/products.php';
require __DIR__.'/includes/header.php';
?>
<div class="page-pad" data-shop>
  <section class="container">
    <div class="text-center reveal">
      <div class="eyebrow">店舗</div>
      <h1 style="font-size:clamp(2.5rem,5vw,3.75rem);margin-top:8px">Shop</h1>
      <p style="margin-top:16px;font-size:.875rem;color:var(--muted-fg);max-width:420px;margin-left:auto;margin-right:auto">
        Seasonal blooms, dried botanicals and ikebana arrangements — each made by hand.
      </p>
    </div>

    <div class="shop-controls reveal">
      <label class="search-box">
        <span data-icon="eye"></span>
        <input id="search" type="text" placeholder="Search blooms…" aria-label="Search products">
      </label>
      <div class="sort-box">
        <select id="sort" aria-label="Sort by">
          <?php foreach($SORTS as $s): ?><option><?= $s ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="filter-box">
        <span id="priceLabel">≤ ₱100</span>
        <input id="price" type="range" min="20" max="200" value="200" aria-label="Max price">
      </div>
    </div>

    <div class="type-chips reveal">
      <?php foreach($TYPES as $i=>$t): ?>
        <button class="chip <?= $i===0?'active':'' ?>" data-type="<?= htmlspecialchars($t) ?>">
          <?= htmlspecialchars($t) ?>
        </button>
      <?php endforeach; ?>
    </div>

    <div class="product-grid" style="margin-top:48px">
      <?php foreach($PRODUCTS as $i=>$p): ?>
        <div class="reveal" style="transition-delay:<?= ($i%6)*60 ?>ms">
          <?php renderProductCard($p); ?>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="empty" style="display:none;text-align:center;padding:80px 0;color:var(--muted-fg)">
      No blooms match — try a different search or filter.
    </div>
  </section>
</div>
<?php require __DIR__.'/includes/footer.php'; ?>