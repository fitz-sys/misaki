<?php
// included from admin/index.php
require_once __DIR__.'/../includes/db.php';
$action = $_POST['action'] ?? '';
$msg='';
if($action==='create'){
  $st = db()->prepare('INSERT INTO product (slug,name,jp_name,type_id,price,image,badge,description,is_visible) VALUES (?,?,?,?,?,?,?,?,?)');
  $st->execute([
    trim($_POST['slug']), trim($_POST['name']), trim($_POST['jp_name']),
    (int)$_POST['type_id'], (float)$_POST['price'],
    trim($_POST['image']), $_POST['badge'] ?: null,
    trim($_POST['description']), isset($_POST['is_visible'])?1:0
  ]);
  $msg='Product created.';
}elseif($action==='update'){
  $st = db()->prepare('UPDATE product SET slug=?,name=?,jp_name=?,type_id=?,price=?,image=?,badge=?,description=?,is_visible=? WHERE product_id=?');
  $st->execute([
    trim($_POST['slug']), trim($_POST['name']), trim($_POST['jp_name']),
    (int)$_POST['type_id'], (float)$_POST['price'],
    trim($_POST['image']), $_POST['badge'] ?: null,
    trim($_POST['description']), isset($_POST['is_visible'])?1:0,
    (int)$_POST['product_id']
  ]);
  $msg='Product updated.';
}elseif($action==='delete'){
  db()->prepare('DELETE FROM product WHERE product_id=?')->execute([(int)$_POST['product_id']]);
  $msg='Product deleted.';
}elseif($action==='toggle'){
  db()->prepare('UPDATE product SET is_visible=1-is_visible WHERE product_id=?')
      ->execute([(int)$_POST['product_id']]);
}

$products = db()->query('SELECT p.*, t.name AS type_name FROM product p JOIN product_type t ON t.type_id=p.type_id ORDER BY p.product_id')->fetchAll();
$types = db()->query('SELECT * FROM product_type ORDER BY type_id')->fetchAll();
?>
<?php if($msg): ?><div class="auth-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<h2 class="font-display" style="font-size:1.75rem;margin-bottom:16px">Products</h2>

<details class="admin-card" <?= isset($_GET['new'])?'open':'' ?>>
  <summary>+ New product</summary>
  <form method="post" class="admin-form">
    <input type="hidden" name="action" value="create">
    <label>Slug<input name="slug" required></label>
    <label>Name<input name="name" required></label>
    <label>Japanese name<input name="jp_name"></label>
    <label>Type
      <select name="type_id"><?php foreach($types as $t): ?><option value="<?= $t['type_id'] ?>"><?= htmlspecialchars($t['name']) ?></option><?php endforeach; ?></select>
    </label>
    <label>Price<input type="number" step="0.01" name="price" required></label>
    <label>Image (e.g. images/product-1.jpg)<input name="image" required></label>
    <label>Badge<input name="badge" placeholder="New / Bestseller / Limited"></label>
    <label class="span2">Description<textarea name="description" rows="3" required></textarea></label>
    <label class="checkbox"><input type="checkbox" name="is_visible" checked> Visible on site</label>
    <button class="btn btn-ink" type="submit">Create</button>
  </form>
</details>

<table class="admin-table">
  <thead><tr><th></th><th>Name</th><th>Type</th><th>Price</th><th>Sales</th><th>Visible</th><th></th></tr></thead>
  <tbody>
  <?php foreach($products as $p): ?>
    <tr>
      <td><img src="../<?= htmlspecialchars($p['image']) ?>" style="width:48px;height:60px;object-fit:cover"></td>
      <td><strong><?= htmlspecialchars($p['name']) ?></strong><br><small><?= htmlspecialchars($p['slug']) ?></small></td>
      <td><?= htmlspecialchars($p['type_name']) ?></td>
      <td>$<?= number_format($p['price'],2) ?></td>
      <td><?= $p['sales'] ?></td>
      <td>
        <form method="post" style="display:inline">
          <input type="hidden" name="action" value="toggle">
          <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
          <button class="pill <?= $p['is_visible']?'on':'off' ?>"><?= $p['is_visible']?'visible':'hidden' ?></button>
        </form>
      </td>
      <td>
        <details>
          <summary>edit</summary>
          <form method="post" class="admin-form" style="margin-top:8px">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
            <label>Slug<input name="slug" value="<?= htmlspecialchars($p['slug']) ?>" required></label>
            <label>Name<input name="name" value="<?= htmlspecialchars($p['name']) ?>" required></label>
            <label>JP<input name="jp_name" value="<?= htmlspecialchars($p['jp_name']) ?>"></label>
            <label>Type<select name="type_id"><?php foreach($types as $t): ?><option value="<?= $t['type_id'] ?>" <?= $t['type_id']==$p['type_id']?'selected':'' ?>><?= htmlspecialchars($t['name']) ?></option><?php endforeach; ?></select></label>
            <label>Price<input type="number" step="0.01" name="price" value="<?= $p['price'] ?>"></label>
            <label>Image<input name="image" value="<?= htmlspecialchars($p['image']) ?>"></label>
            <label>Badge<input name="badge" value="<?= htmlspecialchars($p['badge'] ?? '') ?>"></label>
            <label class="span2">Description<textarea name="description" rows="3"><?= htmlspecialchars($p['description']) ?></textarea></label>
            <label class="checkbox"><input type="checkbox" name="is_visible" <?= $p['is_visible']?'checked':'' ?>> Visible</label>
            <button class="btn btn-ink" type="submit">Save</button>
          </form>
        </details>
        <form method="post" style="display:inline" onsubmit="return confirm('Delete this product?')">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
          <button class="link-danger" type="submit">delete</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
