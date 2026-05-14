<?php
$page        = 'about';
$title       = 'About — Misaki Handcrafted';
$description = 'About Misaki, a handcrafted floral studio.';
require __DIR__.'/includes/settings.php';
require __DIR__.'/includes/header.php';

$eyebrow = setting('about_eyebrow',  '店について');
$heading = setting('about_heading',  'About the studio');
$body    = setting('about_body',
  "Misaki was founded on the quiet belief that flowers are most beautiful when they are most themselves — a philosophy borrowed from ikebana and the wabi-sabi tradition.\n\nEvery arrangement is hand-tied in our small studio with seasonal blooms sourced weekly from local growers. We do not chase trends; we follow the bloom calendar."
);
$quote_jp = setting('brand_quote_jp', '花のように静かに');
?>
<div class="about-wrap container">
  <div class="text-center reveal">
    <div class="eyebrow"><?= htmlspecialchars($eyebrow) ?></div>
    <h1 style="font-size:clamp(2.5rem,5vw,3.5rem);margin-top:8px"><?= htmlspecialchars($heading) ?></h1>
  </div>
  <div class="reveal" style="margin-top:48px">
    <?php foreach(explode("\n\n", trim($body)) as $para): ?>
      <p><?= nl2br(htmlspecialchars($para)) ?></p>
    <?php endforeach; ?>
    <p class="font-jp" style="text-align:center;margin-top:56px;font-size:1.25rem;color:var(--sage-deep)">
      <?= htmlspecialchars($quote_jp) ?>
    </p>
  </div>
</div>
<?php require __DIR__.'/includes/footer.php'; ?>