<?php
$page = 'shop';
$title = 'Shop — Misaki Handcrafted';
$description = 'Lorem ipsum. Browse handcrafted bouquets, ikebana arrangements, and dried botanicals.';
require __DIR__.'/includes/products.php';
require __DIR__.'/includes/header.php';
?>
<div class="page-pad" data-shop>
  <section class="container">
    <div class="text-center reveal">
      <div class="eyebrow">店舗</div>
      <h1 style="font-size:clamp(2.5rem,5vw,3.75rem);margin-top:8px">Shop</h1>
      <p style="margin-top:16px;font-size:.875rem;color:var(--muted-fg);max-width:420px;margin-left:auto;margin-right:auto">
        Lorem ipsum dolor sit amet, consectetur adipiscing elit.
      </p>
    </div>
    <div class="shop-controls reveal">
      <label class="search-box">
        <span data-icon="eye" style="opacity:.4"></span>
        <input id="search" type="text" placeholder="Search blooms…">
      </label>
      <div class="sort-box">
        <select id="sort">
          <?php foreach($SORTS as $s): ?><option><?= $s ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="filter-box">
        <span id="priceLabel">≤ $100</span>
        <input id="price" type="range" min="20" max="100" value="100">
      </div>
    </div>
    <div class="type-chips reveal">
      <?php foreach($TYPES as $i=>$t): ?>
        <button class="chip <?= $i===0?'active':'' ?>" data-type="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></button>
      <?php endforeach; ?>
    </div>
    <div class="product-grid" style="margin-top:48px">
      <?php foreach($PRODUCTS as $i=>$p): ?>
        <div class="reveal" style="transition-delay:<?= ($i%6)*60 ?>ms"><?php renderProductCard($p); ?></div>
      <?php endforeach; ?>
    </div>
    <div class="empty" style="display:none;text-align:center;padding:80px 0;color:var(--muted-fg)">
      No blooms match — try a different search.
    </div>
  </section>
</div>
<?php require __DIR__.'/includes/footer.php'; ?>
