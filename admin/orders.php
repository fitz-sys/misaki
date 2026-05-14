<?php
require_once __DIR__.'/../includes/db.php';

if(($_POST['action']??'')==='status'){
  db()->prepare('UPDATE `order` SET status=? WHERE order_id=?')
     ->execute([$_POST['status'],(int)$_POST['order_id']]);
}

$orders = db()->query(
  'SELECT o.*, u.email, u.full_name
     FROM `order` o JOIN user u ON u.user_id=o.user_id
     ORDER BY o.created_at DESC'
)->fetchAll();
?>
<h2 class="font-display" style="font-size:1.75rem;margin-bottom:16px">Orders</h2>
<?php if(!$orders): ?>
  <p style="opacity:.65;font-size:.9rem">No orders yet.</p>
<?php endif; ?>
<?php foreach($orders as $o):
  $st = db()->prepare(
    'SELECT oi.*, p.name FROM order_item oi
     JOIN product p ON p.product_id=oi.product_id WHERE order_id=?'
  );
  $st->execute([$o['order_id']]);
  $items = $st->fetchAll();
?>
  <div class="admin-card" style="margin-bottom:16px">
    <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;align-items:flex-start">
      <div>
        <strong>Order #<?= $o['order_id'] ?></strong>
        <span style="opacity:.55;font-size:.8rem"> · <?= htmlspecialchars($o['created_at']) ?></span><br>
        <small style="color:var(--muted-fg)">
          <?= htmlspecialchars($o['full_name']) ?> &lt;<?= htmlspecialchars($o['email']) ?>&gt;
        </small>
        
        <div style="margin-top:8px; font-size: 0.85rem;">
          Payment Method: <strong style="text-transform:uppercase"><?= htmlspecialchars($o['payment_method'] ?? 'cash') ?></strong>
          <?php if(!empty($o['payment_proof'])): ?>
             <br><a href="../<?= htmlspecialchars($o['payment_proof']) ?>" target="_blank" style="color:var(--sage-deep); text-decoration:underline; font-weight:500;">👁 View GCash Receipt</a>
          <?php endif; ?>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <strong style="font-size:1.1rem">₱<?= number_format($o['total'],2) ?></strong>
        <form method="post" style="display:inline-flex;align-items:center;gap:8px">
          <input type="hidden" name="action" value="status">
          <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
          <select name="status" onchange="this.form.submit()" style="font-size:.8rem;padding:6px 10px">
            <?php foreach(['pending','paid','fulfilled','cancelled'] as $s): ?>
              <option <?= $o['status']===$s?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </form>
      </div>
    </div>
    <ul style="margin-top:12px;padding-left:18px;font-size:.875rem;color:var(--muted-fg)">
      <?php foreach($items as $it): ?>
        <li>
          <?= htmlspecialchars($it['name']) ?> × <?= $it['qty'] ?>
          — ₱<?= number_format($it['line_total'],2) ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endforeach; ?>