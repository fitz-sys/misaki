<?php
require_once __DIR__.'/../includes/db.php';
$action=$_POST['action'] ?? ''; $msg='';
if($action==='create'){
  db()->prepare('INSERT INTO addon (name,price,is_active) VALUES (?,?,?)')
     ->execute([trim($_POST['name']),(float)$_POST['price'],isset($_POST['is_active'])?1:0]);
  $msg='Add-on created.';
}elseif($action==='update'){
  db()->prepare('UPDATE addon SET name=?,price=?,is_active=? WHERE addon_id=?')
     ->execute([trim($_POST['name']),(float)$_POST['price'],isset($_POST['is_active'])?1:0,(int)$_POST['addon_id']]);
  $msg='Add-on updated.';
}elseif($action==='delete'){
  db()->prepare('DELETE FROM addon WHERE addon_id=?')->execute([(int)$_POST['addon_id']]);
  $msg='Add-on deleted.';
}
$rows = db()->query('SELECT * FROM addon ORDER BY addon_id')->fetchAll();
?>
<?php if($msg): ?><div class="auth-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<h2 class="font-display" style="font-size:1.75rem;margin-bottom:16px">Add-ons</h2>

<details class="admin-card">
  <summary>+ New add-on</summary>
  <form method="post" class="admin-form">
    <input type="hidden" name="action" value="create">
    <label>Name<input name="name" required></label>
    <label>Price<input type="number" step="0.01" name="price" required></label>
    <label class="checkbox"><input type="checkbox" name="is_active" checked> Active</label>
    <button class="btn btn-ink" type="submit">Create</button>
  </form>
</details>

<table class="admin-table">
  <thead><tr><th>Name</th><th>Price</th><th>Active</th><th></th></tr></thead>
  <tbody>
  <?php foreach($rows as $a): ?>
    <tr>
      <td><?= htmlspecialchars($a['name']) ?></td>
      <td>₱<?= number_format($a['price'],2) ?></td>
      <td><?= $a['is_active']?'yes':'no' ?></td>
      <td>
        <details><summary>edit</summary>
          <form method="post" class="admin-form" style="margin-top:8px">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="addon_id" value="<?= $a['addon_id'] ?>">
            <label>Name<input name="name" value="<?= htmlspecialchars($a['name']) ?>"></label>
            <label>Price<input type="number" step="0.01" name="price" value="<?= $a['price'] ?>"></label>
            <label class="checkbox"><input type="checkbox" name="is_active" <?= $a['is_active']?'checked':'' ?>> Active</label>
            <button class="btn btn-ink" type="submit">Save</button>
          </form>
        </details>
        <form method="post" style="display:inline" onsubmit="return confirm('Delete?')">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="addon_id" value="<?= $a['addon_id'] ?>">
          <button class="link-danger" type="submit">delete</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
