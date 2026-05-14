<?php
$page        = 'about';
$title       = 'About — Misaki Handcrafted';
$description = 'About Misaki, a handcrafted floral studio.';
require __DIR__.'/includes/header.php';
?>
<div class="about-wrap container">
  <div class="text-center reveal">
    <div class="eyebrow">店について</div>
    <h1 style="font-size:clamp(2.5rem,5vw,3.5rem);margin-top:8px">About the studio</h1>
  </div>
  <div class="reveal" style="margin-top:48px">
    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
    <p>Misaki was founded on the quiet belief that flowers are most beautiful when they are most themselves — a philosophy borrowed from ikebana and the wabi-sabi tradition.</p>
    <p>Every arrangement is hand-tied in our small studio with seasonal blooms sourced weekly from local growers. We do not chase trends; we follow the bloom calendar.</p>
    <p class="font-jp" style="text-align:center;margin-top:56px;font-size:1.25rem;color:var(--sage-deep)">花のように静かに</p>
  </div>
</div>
<?php require __DIR__.'/includes/footer.php'; ?>