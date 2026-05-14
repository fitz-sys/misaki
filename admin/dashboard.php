<?php
// admin/dashboard.php — loaded by index.php
require_once __DIR__.'/../includes/db.php';

$pdo = db();

// Stats
$totalOrders   = $pdo->query("SELECT COUNT(*) FROM `order`")->fetchColumn();
$totalRevenue  = $pdo->query("SELECT COALESCE(SUM(total),0) FROM `order` WHERE status NOT IN ('cancelled')")->fetchColumn();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM product WHERE is_visible=1")->fetchColumn();
$totalUsers    = $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM `order` WHERE status='pending'")->fetchColumn();

// Recent orders
$recentOrders = $pdo->query(
  "SELECT o.order_id, o.total, o.status, o.created_at, u.full_name, u.email
   FROM `order` o JOIN user u ON u.user_id=o.user_id
   ORDER BY o.created_at DESC LIMIT 8"
)->fetchAll();

// Top products by sales
$topProducts = $pdo->query(
  "SELECT name, sales, price FROM product WHERE is_visible=1 ORDER BY sales DESC LIMIT 5"
)->fetchAll();
?>

<div class="adm-stats">
  <div class="adm-stat">
    <div class="adm-stat-label">Total Revenue</div>
    <div class="adm-stat-value">₱<?= number_format($totalRevenue, 0) ?></div>
    <div class="adm-stat-sub">Excluding cancelled</div>
  </div>
  <div class="adm-stat">
    <div class="adm-stat-label">Orders</div>
    <div class="adm-stat-value"><?= $totalOrders ?></div>
    <div class="adm-stat-sub"><?= $pendingOrders ?> pending</div>
  </div>
  <div class="adm-stat">
    <div class="adm-stat-label">Products</div>
    <div class="adm-stat-value"><?= $totalProducts ?></div>
    <div class="adm-stat-sub">Visible on store</div>
  </div>
  <div class="adm-stat">
    <div class="adm-stat-label">Customers</div>
    <div class="adm-stat-value"><?= $totalUsers ?></div>
    <div class="adm-stat-sub">Registered accounts</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;flex-wrap:wrap">

  <!-- Recent Orders -->
  <div class="adm-card" style="grid-column:1/-1">
    <div class="adm-section-head">
      <h2>Recent Orders</h2>
      <?php if($pendingOrders): ?>
        <span class="pill pending"><?= $pendingOrders ?> pending</span>
      <?php endif; ?>
    </div>
    <div class="adm-table-wrap">
      <table class="adm-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Customer</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if(!$recentOrders): ?>
            <tr><td colspan="6" style="text-align:center;color:var(--adm-muted);padding:32px">No orders yet.</td></tr>
          <?php endif; ?>
          <?php foreach($recentOrders as $o): ?>
            <tr>
              <td style="font-weight:500;color:var(--adm-muted)">#<?= $o['order_id'] ?></td>
              <td>
                <div style="font-weight:500"><?= htmlspecialchars($o['full_name']) ?></div>
                <div style="font-size:.72rem;color:var(--adm-muted)"><?= htmlspecialchars($o['email']) ?></div>
              </td>
              <td style="font-weight:500">₱<?= number_format($o['total'],2) ?></td>
              <td><span class="pill <?= $o['status'] ?>"><?= $o['status'] ?></span></td>
              <td style="color:var(--adm-muted);font-size:.78rem"><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
              <td>
                <a href="?tab=orders" class="adm-btn adm-btn-outline" style="padding:5px 10px;font-size:.7rem">View</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Top Products -->
  <div class="adm-card">
    <div class="adm-section-head"><h2>Top Products</h2></div>
    <?php if(!$topProducts): ?>
      <p style="color:var(--adm-muted);font-size:.875rem">No products yet.</p>
    <?php endif; ?>
    <?php foreach($topProducts as $i => $p): ?>
      <div style="display:flex;align-items:center;gap:12px;padding:10px 0;<?= $i ? 'border-top:1px solid var(--adm-border)' : '' ?>">
        <div style="font-family:'Cormorant Garamond',serif;font-size:1.25rem;color:var(--adm-muted);width:20px;text-align:center"><?= $i+1 ?></div>
        <div style="flex:1;min-width:0">
          <div style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($p['name']) ?></div>
          <div style="font-size:.72rem;color:var(--adm-muted)">₱<?= number_format($p['price'],2) ?></div>
        </div>
        <div style="font-size:.78rem;font-weight:600;color:var(--adm-sage)"><?= $p['sales'] ?> sold</div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Quick actions -->
  <div class="adm-card">
    <div class="adm-section-head"><h2>Quick Actions</h2></div>
    <div style="display:flex;flex-direction:column;gap:10px">
      <a href="?tab=products" class="adm-btn adm-btn-outline" style="justify-content:center">❁ Add New Product</a>
      <a href="?tab=addons"   class="adm-btn adm-btn-outline" style="justify-content:center">✦ Manage Add-ons</a>
      <a href="?tab=orders"   class="adm-btn adm-btn-outline" style="justify-content:center">◎ View All Orders</a>
      <a href="?tab=settings" class="adm-btn adm-btn-outline" style="justify-content:center">⚙ Site Settings</a>
      <a href="../index.php" target="_blank" class="adm-btn adm-btn-outline" style="justify-content:center">↗ View Storefront</a>
    </div>
  </div>

</div>

@media (max-width: 700px) {
  .adm-dashboard-grid { grid-template-columns: 1fr !important; }
}