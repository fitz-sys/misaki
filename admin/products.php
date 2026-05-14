<?php
require_once __DIR__.'/../includes/db.php';
$action = $_POST['action'] ?? '';
$msg    = '';
$err    = ''; 

// Helper function para mag-handle ng image upload
function handleImageUpload($fileInputName, $existingImagePath = '') {
  if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES[$fileInputName]['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif']; 
    
    if (in_array($ext, $allowed)) {
      $uploadDir = __DIR__.'/../images/';
      if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
      
      $filename = 'prod_' . time() . '_' . uniqid() . '.' . $ext;
      $target = $uploadDir . $filename;
      
      if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $target)) {
        return 'images/' . $filename; 
      }
    }
  }
  return $existingImagePath; 
}

if($action==='create'){
  try {
    $imagePath = handleImageUpload('image_file', 'images/default-placeholder.jpg');

    $st = db()->prepare('INSERT INTO product (slug,name,jp_name,type_id,price,image,badge,description,is_visible) VALUES (?,?,?,?,?,?,?,?,?)');
    $st->execute([
      trim($_POST['slug']), trim($_POST['name']), trim($_POST['jp_name']),
      (int)$_POST['type_id'], (float)$_POST['price'],
      $imagePath, null, // Badge is now set to null automatically
      trim($_POST['description']), isset($_POST['is_visible'])?1:0
    ]);
    $msg = 'Product created successfully.';
  } catch (PDOException $e) {
    if ($e->getCode() == 23000 && strpos($e->getMessage(), '1062') !== false) {
      $err = "Cannot create: The slug '" . htmlspecialchars($_POST['slug']) . "' is already in use. Please choose a unique slug.";
    } else {
      $err = "Database error: " . $e->getMessage();
    }
  }

} elseif($action==='update'){
  try {
    $imagePath = handleImageUpload('image_file', $_POST['existing_image'] ?? '');

    $st = db()->prepare('UPDATE product SET slug=?,name=?,jp_name=?,type_id=?,price=?,image=?,badge=?,description=?,is_visible=? WHERE product_id=?');
    $st->execute([
      trim($_POST['slug']), trim($_POST['name']), trim($_POST['jp_name']),
      (int)$_POST['type_id'], (float)$_POST['price'],
      $imagePath, null, // Badge is now set to null automatically
      trim($_POST['description']), isset($_POST['is_visible'])?1:0,
      (int)$_POST['product_id']
    ]);
    $msg = 'Product updated successfully.';
  } catch (PDOException $e) {
    if ($e->getCode() == 23000 && strpos($e->getMessage(), '1062') !== false) {
      $err = "Cannot update: The slug '" . htmlspecialchars($_POST['slug']) . "' is already used by another product.";
    } else {
      $err = "Database error: " . $e->getMessage();
    }
  }

} elseif($action==='delete'){
  try {
    db()->prepare('DELETE FROM product WHERE product_id=?')->execute([(int)$_POST['product_id']]);
    $msg = 'Product deleted successfully.';
  } catch (PDOException $e) {
    if ($e->getCode() == 23000 && strpos($e->getMessage(), '1451') !== false) {
      $err = "Cannot delete this product because it is part of existing customer orders. To hide it from the shop, please click 'edit' and uncheck 'Visible on store'.";
    } else {
      $err = "Database error: " . $e->getMessage();
    }
  }
}

// Fetch all products and types
$products = db()->query('
  SELECT p.*, t.name as type_name 
  FROM product p 
  JOIN product_type t ON p.type_id=t.type_id 
  ORDER BY p.product_id DESC
')->fetchAll();
$types = db()->query('SELECT * FROM product_type ORDER BY type_id')->fetchAll();
?>

<?php if($err): ?>
  <div class="auth-error" style="margin-bottom:16px; background:var(--error-bg); border:1px solid var(--error-bd); color:var(--error-fg); padding:12px; border-radius:4px;">
    <strong>Action Failed:</strong> <?= htmlspecialchars($err) ?>
  </div>
<?php endif; ?>

<?php if($msg): ?>
  <div class="auth-success" style="margin-bottom:16px; background:var(--success-bg); border:1px solid var(--success-bd); color:var(--success-fg); padding:12px; border-radius:4px;">
    <?= htmlspecialchars($msg) ?>
  </div>
<?php endif; ?>

<h2 class="font-display" style="font-size:1.75rem;margin-bottom:16px">Products</h2>

<details class="admin-card">
  <summary>+ New product</summary>
  <form method="post" class="admin-form" enctype="multipart/form-data">
    <input type="hidden" name="action" value="create">
    <label>Slug<input name="slug" required placeholder="e.g. lorem-blush"></label>
    <label>Name<input name="name" required placeholder="e.g. Lorem Blush"></label>
    <label>Japanese Name<input name="jp_name" placeholder="e.g. 桃の夢"></label>
    <label>Type
      <select name="type_id">
        <?php foreach($types as $t): ?>
          <option value="<?= $t['type_id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Price (₱)<input type="number" step="0.01" min="0" name="price" required></label>
    <label>Image Upload<input type="file" name="image_file" accept="image/*" required style="padding: 7px;"></label>
    
    <label class="span2">Description<textarea name="description" rows="3" required></textarea></label>
    <label class="checkbox"><input type="checkbox" name="is_visible" checked> Visible on store</label>
    <button class="btn btn-ink" type="submit">Create product</button>
  </form>
</details>

<table class="admin-table">
  <thead><tr><th>Image</th><th>Name</th><th>Type</th><th>Price</th><th>Visibility</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach($products as $p): ?>
    <tr>
      <td><img src="../<?= htmlspecialchars($p['image']) ?>" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:4px"></td>
      <td>
        <div style="font-weight:500"><?= htmlspecialchars($p['name']) ?></div>
        <div style="font-size:.7rem;color:var(--muted-fg)"><?= htmlspecialchars($p['slug']) ?></div>
      </td>
      <td><?= htmlspecialchars($p['type_name']) ?></td>
      <td>₱<?= number_format($p['price'],2) ?></td>
      <td>
        <span class="pill <?= $p['is_visible']?'on':'off' ?>">
          <?= $p['is_visible']?'visible':'hidden' ?>
        </span>
      </td>
      <td>
        <details>
          <summary style="font-size:.8rem;color:var(--sage-deep);text-decoration:underline;cursor:pointer">edit</summary>
          <form method="post" class="admin-form" style="margin-top:12px" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
            <input type="hidden" name="existing_image" value="<?= htmlspecialchars($p['image']) ?>">
            
            <label>Slug<input name="slug" value="<?= htmlspecialchars($p['slug']) ?>" required></label>
            <label>Name<input name="name" value="<?= htmlspecialchars($p['name']) ?>" required></label>
            <label>Japanese Name<input name="jp_name" value="<?= htmlspecialchars($p['jp_name']) ?>"></label>
            <label>Type
              <select name="type_id">
                <?php foreach($types as $t): ?>
                  <option value="<?= $t['type_id'] ?>" <?= $t['type_id']==$p['type_id']?'selected':'' ?>>
                    <?= htmlspecialchars($t['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </label>
            <label>Price (₱)<input type="number" step="0.01" min="0" name="price" value="<?= $p['price'] ?>"></label>
            
            <label>Change Image <span style="font-size:0.7rem;font-weight:normal;text-transform:none;">(Leave blank to keep current)</span>
              <input type="file" name="image_file" accept="image/*" style="padding: 7px;">
            </label>
            
            <label class="span2">Description<textarea name="description" rows="3"><?= htmlspecialchars($p['description']) ?></textarea></label>
            <label class="checkbox"><input type="checkbox" name="is_visible" <?= $p['is_visible']?'checked':'' ?>> Visible</label>
            <button class="btn btn-ink" type="submit">Save changes</button>
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