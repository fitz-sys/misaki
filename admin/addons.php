<?php
// admin/addons.php — loaded by admin/index.php
require_once __DIR__.'/../includes/db.php';
$action = $_POST['action'] ?? '';
$msg    = '';

if ($action === 'create') {
  db()->prepare('INSERT INTO addon (name,price,is_active) VALUES (?,?,?)')
     ->execute([trim($_POST['name']),(float)$_POST['price'],isset($_POST['is_active'])?1:0]);
  $msg = 'Add-on created.';
} elseif ($action === 'update') {
  db()->prepare('UPDATE addon SET name=?,price=?,is_active=? WHERE addon_id=?')
     ->execute([trim($_POST['name']),(float)$_POST['price'],isset($_POST['is_active'])?1:0,(int)$_POST['addon_id']]);
  $msg = 'Add-on updated.';
} elseif ($action === 'delete') {
  db()->prepare('DELETE FROM addon WHERE addon_id=?')->execute([(int)$_POST['addon_id']]);
  $msg = 'Add-on deleted.';
}

$rows = db()->query('SELECT * FROM addon ORDER BY addon_id')->fetchAll();
?>

<?php if ($msg): ?><div class="adm-flash success">✓ <?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="adm-section-head">
  <h2>Add-ons</h2>
  <span class="count"><?= count($rows) ?></span>
</div>

<details class="adm-expand adm-card" style="margin-bottom:20px">
  <summary>+ New add-on</summary>
  <form method="post" class="adm-form" style="margin-top:20px">
    <input type="hidden" name="action" value="create">
    <label class="adm-label">Name<input name="name" required placeholder="e.g. Ribbon Wrap"></label>
    <label class="adm-label">Price (₱)<input type="number" step="0.01" min="0" name="price" required></label>
    <label class="adm-label checkbox-label"><input type="checkbox" name="is_active" checked> Active</label>
    <div><button class="adm-btn adm-btn-primary" type="submit">Create add-on</button></div>
  </form>
</details>

<div class="adm-card">
  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead><tr><th>Name</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($rows as $a): ?>
        <tr>
          <td style="font-weight:500"><?= htmlspecialchars($a['name']) ?></td>
          <td>₱<?= number_format($a['price'],2) ?></td>
          <td><span class="pill <?= $a['is_active']?'on':'off' ?>"><?= $a['is_active']?'active':'inactive' ?></span></td>
          <td>
            <details class="adm-expand" style="display:inline">
              <summary>Edit</summary>
              <div class="adm-card" style="margin-top:12px">
                <form method="post" class="adm-form">
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="addon_id" value="<?= $a['addon_id'] ?>">
                  <label class="adm-label">Name<input name="name" value="<?= htmlspecialchars($a['name']) ?>" required></label>
                  <label class="adm-label">Price (₱)<input type="number" step="0.01" min="0" name="price" value="<?= $a['price'] ?>" required></label>
                  <label class="adm-label checkbox-label"><input type="checkbox" name="is_active" <?= $a['is_active']?'checked':'' ?>> Active</label>
                  <div><button class="adm-btn adm-btn-primary" type="submit">Save</button></div>
                </form>
              </div>
            </details>
            <form method="post" style="display:inline;margin-left:8px" onsubmit="return confirm('Delete this add-on?')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="addon_id" value="<?= $a['addon_id'] ?>">
              <button class="adm-btn adm-btn-danger" type="submit">delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>