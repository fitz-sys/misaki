<?php
$page='cart'; $title='Cart — Misaki Handcrafted';
$description='Your bag at Misaki Handcrafted.';
require __DIR__.'/includes/header.php';
?>
<div class="page-pad">
  <section class="container cart-wrap" data-cart>
    <div class="text-center reveal">
      <div class="eyebrow">買い物かご</div>
      <h1 style="font-size:clamp(2.5rem,5vw,3.5rem);margin-top:8px">Your cart</h1>
    </div>
    <div class="cart-list" style="margin-top:48px"></div>
    <div class="cart-empty" style="display:none;text-align:center;padding:60px 0;color:var(--muted-fg)">
      Your cart is empty. <a href="shop.php" style="text-decoration:underline">Shop blooms →</a>
    </div>
    <div class="cart-total"><span>Total</span><span>$0</span></div>
    <div style="margin-top:32px;text-align:right">
      <button class="btn btn-ink" data-checkout>Checkout</button>
    </div>
  </section>
</div>
<?php require __DIR__.'/includes/footer.php'; ?>
