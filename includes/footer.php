</main>

<footer>
  <div class="footer-inner">
    <div class="footer-brand">
      <div class="logo">MISAKI</div>
      <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Handcrafted floral studio rooted in quiet ritual and seasonal bloom.</p>
    </div>
    <div class="footer-col">
      <div class="h">Explore</div>
      <ul>
        <li><a href="shop.php">Shop</a></li>
        <li><a href="gallery.php">Gallery</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="cart.php">Cart</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <div class="h">Contact</div>
      <ul>
        <li>✉ hello@misaki.lorem</li>
        <li>☎ +00 000 0000</li>
        <li>◎ @misaki.handcrafted</li>
      </ul>
    </div>
  </div>
  <div class="footer-base">
    <div class="footer-base-inner">
      <div>© <?= date('Y') ?> Misaki Handcrafted. Lorem ipsum.</div>
      <div class="font-jp">花のように静かに · like flowers, quietly</div>
    </div>
  </div>
</footer>

<!-- scroll to top -->
<button class="scroll-top" aria-label="Scroll to top"><span data-icon="up"></span></button>

<!-- FAQ modal (replaces chat) -->
<div class="modal faq-modal" id="faqModal">
  <div class="modal-bg"></div>
  <div class="modal-panel faq-panel">
    <button class="close" aria-label="Close" data-icon="x"></button>
    <div class="faq-head">
      <div class="eyebrow">よくあるご質問</div>
      <h2 class="font-display" style="font-size:2rem;margin-top:6px">Frequently asked</h2>
    </div>
    <div class="faq-list">
      <details><summary>Do you offer same-day delivery?</summary><p>Lorem ipsum — yes, within the city for orders placed before 1pm.</p></details>
      <details><summary>How long do your bouquets last?</summary><p>Fresh arrangements last 5–7 days. Dried pieces last a full season.</p></details>
      <details><summary>Can I add a custom note or letter?</summary><p>Yes — choose the “Letter” or “Acrylic Dedication” add-on at checkout.</p></details>
      <details><summary>Do you ship outside the city?</summary><p>Lorem ipsum dolor sit amet — please contact us for nationwide shipping.</p></details>
      <details><summary>Refunds and cancellations?</summary><p>Cancellations accepted up to 4 hours before scheduled delivery.</p></details>
    </div>
  </div>
</div>

<!-- quick view modal -->
<div class="modal" id="quickModal">
  <div class="modal-bg"></div>
  <div class="modal-panel">
    <button class="close" aria-label="Close" data-icon="x"></button>
    <div class="modal-img"><img src="" alt=""></div>
    <div class="modal-info">
      <h2 class="qv-name font-display"></h2>
      <div class="qv-jp" style="font-family:'Shippori Mincho',serif;font-size:.875rem;color:var(--muted-fg);margin-top:4px"></div>
      <div class="qv-price" style="font-size:1.5rem;margin-top:16px"></div>
      <p class="qv-desc" style="margin-top:20px;font-size:.875rem;color:var(--muted-fg);line-height:1.7"></p>
      <div class="qv-addons"></div>
      <div style="margin-top:24px;display:flex;align-items:center;gap:12px">
        <div class="qty">
          <button class="qv-minus" data-icon="minus"></button>
          <span class="qv-qty">1</span>
          <button class="qv-plus" data-icon="plus"></button>
        </div>
        <button class="qv-add btn btn-ink" style="flex:1">Add to cart</button>
      </div>
      <div style="margin-top:auto;padding-top:32px;display:flex;align-items:center;gap:16px;font-size:.75rem">
        <button class="qv-share" style="display:inline-flex;align-items:center;gap:6px;opacity:.7"><span data-icon="share"></span> Share link</button>
        <a class="qv-link" href="#" style="margin-left:auto;display:inline-flex;align-items:center;gap:6px;opacity:.7">View full page →</a>
      </div>
    </div>
  </div>
</div>

<!-- gallery lightbox -->
<div class="lightbox" id="lightbox" aria-hidden="true">
  <button class="lb-close" aria-label="Close" data-icon="x"></button>
  <button class="lb-prev" aria-label="Previous" data-icon="left"></button>
  <img alt="">
  <button class="lb-next" aria-label="Next" data-icon="right"></button>
</div>

<script>
window.MISAKI_ADDONS = <?= json_encode(fetch_addons(true)) ?>;
window.MISAKI_AUTH = <?= current_user_id() ? 'true' : 'false' ?>;
</script>
<script src="js/main.js"></script>
</body>
</html>
