<?php
// footer.php — all text pulled from site_settings via setting()
// settings.php is already loaded by header.php, but require_once is safe
require_once __DIR__.'/settings.php';

$brand_name   = setting('brand_name',     'MISAKI');
$brand_tagline= setting('brand_tagline',  'Handcrafted floral studio rooted in quiet ritual and seasonal bloom.');
$quote_jp     = setting('brand_quote_jp', '花のように静かに · like flowers, quietly');
$email        = setting('contact_email',  'hello@misaki.lorem');
$phone        = setting('contact_phone',  '+00 000 0000');
$instagram    = setting('contact_instagram', '@misaki.handcrafted');
$tos_text     = setting('footer_link_2_text', 'Terms of Service');
$tos_url      = setting('footer_link_2_url',  '#');

// Adjust paths when inside /legal/ subfolder
$base = (isset($page) && $page === 'legal') ? '../' : '';
?>
</main>

<footer>
  <div class="footer-inner">
    <div class="footer-brand">
      <div class="logo"><?= htmlspecialchars($brand_name) ?></div>
      <p><?= htmlspecialchars($brand_tagline) ?></p>
    </div>
    <div class="footer-col">
      <div class="h">Explore</div>
      <ul>
        <li><a href="<?= $base ?>shop.php">Shop</a></li>
        <li><a href="<?= $base ?>gallery.php">Gallery</a></li>
        <li><a href="<?= $base ?>about.php">About</a></li>
        <li><a href="<?= $base ?>cart.php">Cart</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <div class="h">Legal</div>
      <ul>
        <li><a href="<?= $base ?>legal/privacy.php">Privacy Policy</a></li>
        <li><a href="<?= $base ?><?= htmlspecialchars($tos_url) ?>"><?= htmlspecialchars($tos_text) ?></a></li>
      </ul>
    </div>
    <div class="footer-col">
      <div class="h">Contact</div>
      <ul>
        <li>✉ <?= htmlspecialchars($email) ?></li>
        <li>☎ <?= htmlspecialchars($phone) ?></li>
        <li>◎ <?= htmlspecialchars($instagram) ?></li>
      </ul>
    </div>
  </div>
  <div class="footer-base">
    <div class="footer-base-inner">
      <div>© <?= date('Y') ?> <?= htmlspecialchars($brand_name) ?> Handcrafted.</div>
      <div class="font-jp"><?= htmlspecialchars($quote_jp) ?></div>
    </div>
  </div>
</footer>

<button class="scroll-top" aria-label="Scroll to top"><span data-icon="up"></span></button>

<div class="modal faq-modal" id="faqModal" role="dialog" aria-modal="true" aria-label="FAQ">
  <div class="modal-bg"></div>
  <div class="modal-panel faq-panel">
    <button class="close" aria-label="Close"><span data-icon="x"></span></button>
    <div class="faq-head">
      <div class="eyebrow">よくあるご質問</div>
      <h2 class="font-display" style="font-size:2rem;margin-top:6px">Frequently asked</h2>
    </div>
    <div class="faq-list">
      <details><summary>Do you offer same-day delivery?</summary><p>Yes, within the city for orders placed before 1pm.</p></details>
      <details><summary>How long do your bouquets last?</summary><p>Fresh arrangements last 5–7 days. Dried pieces last a full season.</p></details>
      <details><summary>Can I add a custom note or letter?</summary><p>Yes — choose the "Letter" or "Acrylic Dedication" add-on at checkout.</p></details>
      <details><summary>Do you ship outside the city?</summary><p>Please contact us for nationwide shipping options.</p></details>
      <details><summary>Refunds and cancellations?</summary><p>Cancellations accepted up to 4 hours before scheduled delivery.</p></details>
    </div>
  </div>
</div>

<div class="modal" id="quickModal" role="dialog" aria-modal="true">
  <div class="modal-bg"></div>
  <div class="modal-panel">
    <button class="close" aria-label="Close"><span data-icon="x"></span></button>
    <div class="modal-img"><img src="" alt=""></div>
    <div class="modal-info">
      <h2 class="qv-name font-display"></h2>
      <div class="qv-jp" style="font-family:'Shippori Mincho',serif;font-size:.875rem;color:var(--muted-fg);margin-top:4px"></div>
      <div class="qv-price" style="font-size:1.5rem;font-family:'Cormorant Garamond',serif;margin-top:16px;color:var(--sage-deep)"></div>
      <p class="qv-desc" style="margin-top:16px;font-size:.875rem;color:var(--muted-fg);line-height:1.75"></p>
      <div class="qv-addons"></div>
      <div style="margin-top:24px;display:flex;align-items:center;gap:12px">
        <div class="qty">
          <button class="qv-minus"><span data-icon="minus"></span></button>
          <input type="number" class="qv-qty" value="1" min="1">
          <button class="qv-plus"><span data-icon="plus"></span></button>
        </div>
        <button class="qv-add btn btn-ink" style="flex:1">Add to cart</button>
      </div>
      <div style="margin-top:auto;padding-top:28px;display:flex;align-items:center;gap:16px;font-size:.75rem">
        <button class="qv-share" style="display:inline-flex;align-items:center;gap:6px;opacity:.65">
          <span data-icon="share"></span> Share link
        </button>
        <a class="qv-link" href="#" style="margin-left:auto;display:inline-flex;align-items:center;gap:6px;opacity:.65">View full page →</a>
      </div>
    </div>
  </div>
</div>

<div class="lightbox" id="lightbox" aria-hidden="true" role="dialog">
  <button class="lb-close" aria-label="Close"><span data-icon="x"></span></button>
  <button class="lb-prev" aria-label="Previous"><span data-icon="left"></span></button>
  <img alt="">
  <button class="lb-next" aria-label="Next"><span data-icon="right"></span></button>
</div>

<script>
window.MISAKI_ADDONS = <?= json_encode(fetch_addons(true)) ?>;
window.MISAKI_AUTH   = <?= current_user_id() ? 'true' : 'false' ?>;
</script>
<script src="<?= $base ?>js/main.js"></script>
</body>
</html>