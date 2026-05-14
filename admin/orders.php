<?php
// admin/orders.php — loaded by admin/index.php
require_once __DIR__.'/../includes/db.php';

if (($_POST['action'] ?? '') === 'status') {
  db()->prepare('UPDATE `order` SET status=? WHERE order_id=?')
     ->execute([$_POST['status'], (int)$_POST['order_id']]);
}

$statusFilter = $_GET['status'] ?? 'all';
$validStatuses = ['all','pending','paid','fulfilled','cancelled'];
if (!in_array($statusFilter, $validStatuses)) $statusFilter = 'all';

$sql = 'SELECT o.*, u.email, u.full_name
        FROM `order` o JOIN user u ON u.user_id=o.user_id';
if ($statusFilter !== 'all') $sql .= " WHERE o.status = " . db()->quote($statusFilter);
$sql .= ' ORDER BY o.created_at DESC';

$orders = db()->query($sql)->fetchAll();

// Count by status for filter badges
$counts = db()->query(
  "SELECT status, COUNT(*) as n FROM `order` GROUP BY status"
)->fetchAll(PDO::FETCH_KEY_PAIR);
$allCount = array_sum($counts);
?>

<div class="adm-section-head">
  <h2>Orders</h2>
  <span class="count"><?= count($orders) ?> shown</span>
</div>

<!-- Status filter pills -->
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px">
  <?php
  $filters = ['all' => 'All', 'pending' => 'Pending', 'paid' => 'Paid', 'fulfilled' => 'Fulfilled', 'cancelled' => 'Cancelled'];
  foreach ($filters as $fk => $fl):
    $n = ($fk === 'all') ? $allCount : ($counts[$fk] ?? 0);
  ?>
    <a href="?tab=orders&status=<?= $fk ?>"
       style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:99px;font-size:.72rem;font-weight:500;letter-spacing:.04em;text-decoration:none;border:1px solid <?= $statusFilter===$fk ? 'var(--adm-sage)' : 'var(--adm-border)' ?>;background:<?= $statusFilter===$fk ? 'var(--adm-sage)' : 'transparent' ?>;color:<?= $statusFilter===$fk ? '#fff' : 'var(--adm-muted)' ?>">
      <?= $fl ?>
      <span style="background:rgba(255,255,255,.2);border-radius:99px;padding:1px 6px;font-size:.65rem"><?= $n ?></span>
    </a>
  <?php endforeach; ?>
</div>

<?php if (!$orders): ?>
  <div class="adm-card" style="text-align:center;padding:48px;color:var(--adm-muted)">
    No orders<?= $statusFilter!=='all' ? " with status $statusFilter" : '' ?> yet.
  </div>
<?php endif; ?>

<?php foreach($orders as $o):
  $st = db()->prepare(
    'SELECT oi.*, p.name FROM order_item oi
     JOIN product p ON p.product_id=oi.product_id WHERE order_id=?'
  );
  $st->execute([$o['order_id']]);
  $items = $st->fetchAll();
?>
  <div class="adm-card" style="margin-bottom:16px">
    <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;align-items:flex-start">
      <div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
          <strong style="font-size:1rem">Order #<?= $o['order_id'] ?></strong>
          <span class="pill <?= $o['status'] ?>"><?= $o['status'] ?></span>
          <span style="font-size:.75rem;color:var(--adm-muted)"><?= date('M j, Y · g:ia', strtotime($o['created_at'])) ?></span>
        </div>
        <div style="margin-top:6px;font-size:.82rem;color:var(--adm-muted)">
          <?= htmlspecialchars($o['full_name']) ?> · <?= htmlspecialchars($o['email']) ?>
        </div>
        <div style="margin-top:6px;font-size:.82rem">
          Payment: <strong style="text-transform:uppercase"><?= htmlspecialchars($o['payment_method'] ?? 'cash') ?></strong>
          <?php if (!empty($o['payment_proof'])): ?>
            · <a href="../<?= htmlspecialchars($o['payment_proof']) ?>" target="_blank"
                 style="color:var(--adm-sage);text-decoration:underline">View GCash receipt ↗</a>
          <?php endif; ?>
        </div>
      </div>

      <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <strong style="font-size:1.2rem;font-family:'Cormorant Garamond',serif">₱<?= number_format($o['total'],2) ?></strong>
        <form method="post" style="display:inline-flex;align-items:center;gap:8px">
          <input type="hidden" name="action" value="status">
          <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
          <select name="status" onchange="this.form.submit()"
                  style="font-size:.78rem;padding:6px 10px;border:1px solid var(--adm-border);border-radius:var(--radius);background:var(--adm-white);color:var(--adm-ink);cursor:pointer">
            <?php foreach(['pending','paid','fulfilled','cancelled'] as $s): ?>
              <option <?= $o['status']===$s?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </form>
      </div>
    </div>

    <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--adm-border)">
      <div style="font-size:.65rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--adm-muted);margin-bottom:8px">Items</div>
      <?php foreach($items as $it): ?>
        <div style="display:flex;justify-content:space-between;font-size:.82rem;padding:4px 0;color:var(--adm-muted)">
          <span><?= htmlspecialchars($it['name']) ?> × <?= $it['qty'] ?></span>
          <span>₱<?= number_format($it['line_total'],2) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endforeach; ?>