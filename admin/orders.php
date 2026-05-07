<?php
require_once __DIR__.'/../includes/db.php';
if(($_POST['action'] ?? '')==='status'){
  db()->prepare('UPDATE `order` SET status=? WHERE order_id=?')
     ->execute([$_POST['status'],(int)$_POST['order_id']]);
}
$orders = db()->query(
  'SELECT o.*, u.email, u.full_name
     FROM `order` o JOIN user u ON u.user_id=o.user_id
     ORDER BY o.created_at DESC')->fetchAll();
?>
<h2 class="font-display" style="font-size:1.75rem;margin-bottom:16px">Orders</h2>
<?php if(!$orders): ?><p style="opacity:.7">No orders yet.</p><?php endif; ?>
<?php foreach($orders as $o):
  $items = db()->prepare('SELECT oi.*, p.name FROM order_item oi JOIN product p ON p.product_id=oi.product_id WHERE order_id=?');
  $items->execute([$o['order_id']]); $items=$items->fetchAll();
?>
  <div class="admin-card" style="margin-bottom:16px">
    <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px">
      <div>
        <strong>Order #<?= $o['order_id'] ?></strong> · <?= htmlspecialchars($o['created_at']) ?><br>
        <small><?= htmlspecialchars($o['full_name']) ?> &lt;<?= htmlspecialchars($o['email']) ?>&gt;</small>
      </div>
      <div>
        <strong>$<?= number_format($o['total'],2) ?></strong>
        <form method="post" style="display:inline-block;margin-left:12px">
          <input type="hidden" name="action" value="status">
          <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
          <select name="status" onchange="this.form.submit()">
            <?php foreach(['pending','paid','fulfilled','cancelled'] as $s): ?>
              <option <?= $o['status']===$s?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </form>
      </div>
    </div>
    <ul style="margin-top:10px;padding-left:18px">
      <?php foreach($items as $it): ?>
        <li><?= htmlspecialchars($it['name']) ?> × <?= $it['qty'] ?> — $<?= number_format($it['line_total'],2) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endforeach; ?>
