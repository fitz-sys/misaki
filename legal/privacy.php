<?php
$page  = 'legal';
$title = 'Privacy Policy — Misaki Handcrafted';
// Kailangan i-adjust ang paths dahil nasa loob tayo ng subfolder na /legal/
require __DIR__.'/../includes/auth.php';
require __DIR__.'/../includes/products.php';
require __DIR__.'/../includes/header.php';
?>

<div class="page-pad">
  <section class="container reveal" style="max-width:800px; line-height:1.8;">
    <div class="eyebrow">プライバシーポリシー</div>
    <h1 style="font-size:clamp(2rem,4vw,2.75rem);margin-top:6px; margin-bottom:32px;">Privacy Policy</h1>
    
    <p style="color:var(--muted-fg); margin-bottom:24px;"><strong>Last Updated: May 12, 2026</strong></p>

    <div style="display:flex; flex-direction:column; gap:32px; font-size:0.95rem; color:var(--ink-lt);">
      <div>
        <h3 style="font-family:var(--ff-display); font-size:1.4rem; color:var(--ink); margin-bottom:12px;">1. Introduction</h3>
        <p>Welcome to Misaki Handcrafted. We are committed to protecting and respecting your privacy. This policy sets out the basis on which we process any personal information we collect from you, inspired by international data protection standards.</p>
      </div>

      <div>
        <h3 style="font-family:var(--ff-display); font-size:1.4rem; color:var(--ink); margin-bottom:12px;">2. Information We Collect</h3>
        <p>We collect information such as your name, email address, phone number, and delivery address to fulfill your orders and provide a seamless floral shopping experience.</p>
      </div>

      <div>
        <h3 style="font-family:var(--ff-display); font-size:1.4rem; color:var(--ink); margin-bottom:12px;">3. Recipient Data (Someone Else)</h3>
        <p>When you choose "Someone Else" as a delivery label, we process the recipient's name and address solely for delivery purposes. To protect your privacy and the recipient's experience, orders for others are restricted to prepaid methods (GCash).</p>
      </div>

      <div>
        <h3 style="font-family:var(--ff-display); font-size:1.4rem; color:var(--ink); margin-bottom:12px;">4. Your Rights</h3>
        <p>As a user in the Philippines, you have rights to access, rectify, or erase your personal data under the Data Privacy Act. You may update your profile details anytime in your Account page.</p>
      </div>
    </div>

    <div style="margin-top:56px; padding-top:32px; border-top:1px solid var(--border);">
      <a href="../shop.php" class="btn btn-ink">Back to Shop</a>
    </div>
  </section>
</div>

<?php require __DIR__.'/includes/footer.php'; ?>