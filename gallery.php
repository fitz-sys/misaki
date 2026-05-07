<?php
$page='gallery'; $title='Gallery — Misaki Handcrafted';
$description='Lorem ipsum. A visual diary of seasonal arrangements.';
require __DIR__.'/includes/header.php';
$images = ['gallery-1.jpg','gallery-2.jpg','gallery-3.jpg','gallery-4.jpg','gallery-5.jpg','gallery-6.jpg'];
?>
<div class="page-pad">
  <section class="container">
    <div class="text-center reveal">
      <div class="eyebrow">作品集</div>
      <h1 style="font-size:clamp(2.5rem,5vw,3.75rem);margin-top:8px">Gallery</h1>
      <p style="margin-top:16px;font-size:.875rem;color:var(--muted-fg);max-width:480px;margin-inline:auto">
        Lorem ipsum dolor sit amet — a quiet diary of arrangements through the seasons. Click any image to zoom.
      </p>
    </div>
    <div class="gallery-grid" data-gallery>
      <?php foreach($images as $i=>$img): ?>
        <button type="button" class="gallery-item reveal" style="transition-delay:<?= $i*60 ?>ms" data-lightbox="images/<?= $img ?>">
          <img src="images/<?= $img ?>" alt="Gallery <?= $i+1 ?>" loading="lazy">
          <span class="lb-zoom" data-icon="zoom"></span>
        </button>
      <?php endforeach; ?>
    </div>
  </section>
</div>
<?php require __DIR__.'/includes/footer.php'; ?> ef
