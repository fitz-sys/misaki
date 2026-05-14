<?php
require_once __DIR__.'/includes/auth.php';
require_login('login.php?next='.urlencode('checkout.php'));
require_once __DIR__.'/includes/products.php';

// Kukunin natin yung latest profile data para sa default address block
$st = db()->prepare('SELECT * FROM user WHERE user_id=?');
$st->execute([current_user_id()]);
$me = $st->fetch();

// ─── NCR city list (shared constant) ──────────────────────────────────────────
$NCR_CITIES = [
  'Caloocan','Las Piñas','Makati','Malabon','Mandaluyong','Manila',
  'Marikina','Muntinlupa','Navotas','Parañaque','Pasay','Pasig',
  'Pateros','Quezon City','San Juan','Taguig','Valenzuela'
];

const MAX_ADDRESSES = 5;

// ─── Fetch saved addresses ─────────────────────────────────────────────────────
function fetchAddresses($userId) {
  $st = db()->prepare('SELECT * FROM user_address WHERE user_id=? ORDER BY is_default DESC, address_id ASC');
  $st->execute([$userId]);
  return $st->fetchAll();
}

$addresses    = fetchAddresses(current_user_id());
$addressCount = count($addresses);

// Find default address
$defaultAddr = null;
foreach ($addresses as $a) {
  if ($a['is_default']) { $defaultAddr = $a; break; }
}
if (!$defaultAddr && !empty($addresses)) $defaultAddr = $addresses[0];

$msg            = '';
$createdOrderId = null;
$addAddrMsg     = '';
$addAddrType    = 'success';

// ─── Handle Add New Address from checkout page ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_address_checkout') {
  $addresses    = fetchAddresses(current_user_id());
  $addressCount = count($addresses);

  if ($addressCount >= MAX_ADDRESSES) {
    $addAddrMsg  = 'You have hit the maximum limit of 5 addresses.';
    $addAddrType = 'error';
  } else {
    $label   = in_array($_POST['label'] ?? '', ['Home','Someone Else']) ? $_POST['label'] : 'Home';
    $name    = trim($_POST['full_name']    ?? '');
    $phone   = trim($_POST['phone']        ?? '');
    $addrTxt = trim($_POST['address_text'] ?? '');
    $city    = trim($_POST['city']         ?? '');

    if (!$name || !$phone || !$addrTxt || !$city) {
      $addAddrMsg  = 'All fields are required.';
      $addAddrType = 'error';
    } else {
      $isFirst   = ($addressCount === 0) ? 1 : 0;
      $isDefault = isset($_POST['is_default']) ? 1 : $isFirst;
      if ($isDefault) {
        db()->prepare('UPDATE user_address SET is_default=0 WHERE user_id=?')->execute([current_user_id()]);
      }
      db()->prepare('INSERT INTO user_address (user_id,label,full_name,phone,address_text,city,is_default) VALUES (?,?,?,?,?,?,?)')
          ->execute([current_user_id(), $label, $name, $phone, $addrTxt, $city, $isDefault]);
      $addAddrMsg = 'New address saved!';
    }
  }
  $addresses    = fetchAddresses(current_user_id());
  $addressCount = count($addresses);
  $defaultAddr  = null;
  foreach ($addresses as $a) { if ($a['is_default']) { $defaultAddr = $a; break; } }
  if (!$defaultAddr && !empty($addresses)) $defaultAddr = $addresses[0];
}

// ─── Handle Edit Address from checkout page ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit_address_checkout') {
  $addrId  = (int)($_POST['address_id'] ?? 0);
  $label   = in_array($_POST['label'] ?? '', ['Home','Someone Else']) ? $_POST['label'] : 'Home';
  $name    = trim($_POST['full_name']    ?? '');
  $phone   = trim($_POST['phone']        ?? '');
  $addrTxt = trim($_POST['address_text'] ?? '');
  $city    = trim($_POST['city']         ?? '');

  if (!$name || !$phone || !$addrTxt || !$city) {
    $addAddrMsg  = 'All fields are required.';
    $addAddrType = 'error';
  } else {
    $chk = db()->prepare('SELECT address_id FROM user_address WHERE address_id=? AND user_id=?');
    $chk->execute([$addrId, current_user_id()]);
    if ($chk->fetch()) {
      $isDefault = isset($_POST['is_default']) ? 1 : 0;
      if ($isDefault) {
        db()->prepare('UPDATE user_address SET is_default=0 WHERE user_id=?')->execute([current_user_id()]);
      }
      db()->prepare('UPDATE user_address SET label=?,full_name=?,phone=?,address_text=?,city=?,is_default=? WHERE address_id=? AND user_id=?')
          ->execute([$label, $name, $phone, $addrTxt, $city, $isDefault, $addrId, current_user_id()]);
      $addAddrMsg = 'Address updated!';
    }
  }
  $addresses    = fetchAddresses(current_user_id());
  $addressCount = count($addresses);
  $defaultAddr  = null;
  foreach ($addresses as $a) { if ($a['is_default']) { $defaultAddr = $a; break; } }
  if (!$defaultAddr && !empty($addresses)) $defaultAddr = $addresses[0];
}

// ─── Handle Place Order ────────────────────────────────────────────────────────
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '') === 'place_order'){
  $raw  = $_POST['cart'] ?? '[]';
  $cart = json_decode($raw, true) ?: [];
  if(!$cart){ header('Location: cart.php'); exit; }

  $paymentMethod   = $_POST['payment_method'] ?? 'cash';
  $paymentProof    = null;

  // Resolve address: from address book or inline fields
  $selectedAddrId = (int)($_POST['selected_address_id'] ?? 0);
  if ($selectedAddrId) {
    $sa = db()->prepare('SELECT * FROM user_address WHERE address_id=? AND user_id=?');
    $sa->execute([$selectedAddrId, current_user_id()]);
    $saRow = $sa->fetch();
    if ($saRow) {
      $dLabel   = $saRow['label'];
      $dName    = $saRow['full_name'];
      $dPhone   = $saRow['phone'];
      $dAddress = $saRow['address_text'] . ', ' . $saRow['city'] . ', Metro Manila';
    } else {
      $msg = 'Selected address not found. Please choose again.';
    }
  } else {
    $dLabel   = trim($_POST['address_label'] ?? 'Home');
    $dName    = trim($_POST['delivery_name'] ?? '');
    $dPhone   = trim($_POST['delivery_phone'] ?? '');
    $dAddress = trim($_POST['delivery_address'] ?? '');
  }

  if (empty($msg) && (empty($dAddress) || empty($dName) || empty($dPhone))) {
      $msg = "Complete delivery details (Name, Phone, Address) are required.";
  } elseif (empty($msg)) {
      $pdo = db();
      $pdo->beginTransaction();
      try{
        if ($paymentMethod === 'gcash') {
          if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png'];
            if (in_array(strtolower($ext), $allowed)) {
              $uploadDir = __DIR__.'/images/receipts/';
              if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
              $filename = 'receipt_' . time() . '_' . uniqid() . '.' . $ext;
              $target = $uploadDir . $filename;
              if (move_uploaded_file($_FILES['receipt']['tmp_name'], $target)) {
                $paymentProof = 'images/receipts/' . $filename;
              } else {
                throw new Exception('Failed to save receipt image.');
              }
            } else {
              throw new Exception('Invalid receipt format. Only JPG and PNG are allowed.');
            }
          } else {
            throw new Exception('GCash receipt screenshot is required.');
          }
        }

        $allProducts = [];
        foreach(fetch_products(false) as $p) $allProducts[$p['id']] = $p;
        $allAddons = [];
        foreach(fetch_addons(false) as $a) $allAddons[$a['id']] = $a;

        $total = 0;
        foreach($cart as &$line){
          $prod = $allProducts[(string)$line['id']] ?? null;
          if(!$prod) throw new Exception('Unknown product');
          $unit      = (float)$prod['price'];
          $addonSum  = 0;
          $addonRows = [];
          foreach(($line['addons'] ?? []) as $addon){
            $aid = is_array($addon) ? (int)$addon['id'] : (int)$addon;
            $a   = $allAddons[$aid] ?? null;
            if(!$a) continue;
            $addonSum   += (float)$a['price'];
            $addonRows[] = $a;
          }
          $line['_unit']      = $unit;
          $line['_addonRows'] = $addonRows;
          $line['_lineTotal'] = ($unit + $addonSum) * (int)$line['qty'];
          $total += $line['_lineTotal'];
        }
        unset($line);

        $status = 'pending'; 
        $st = $pdo->prepare('INSERT INTO `order` (user_id, delivery_name, delivery_phone, delivery_address, address_label, status, payment_method, payment_proof, total) VALUES (?,?,?,?,?,?,?,?,?)');
        $st->execute([current_user_id(), $dName, $dPhone, $dAddress, $dLabel, $status, $paymentMethod, $paymentProof, $total]);
        $orderId = (int)$pdo->lastInsertId();

        foreach($cart as $line){
          $st = $pdo->prepare('INSERT INTO order_item (order_id,product_id,qty,unit_price,line_total) VALUES (?,?,?,?,?)');
          $st->execute([$orderId,(int)$line['id'],(int)$line['qty'],$line['_unit'],$line['_lineTotal']]);
          $itemId = (int)$pdo->lastInsertId();
          foreach($line['_addonRows'] as $a){
            $st = $pdo->prepare('INSERT INTO order_item_addon (order_item_id,addon_id,unit_price) VALUES (?,?,?)');
            $st->execute([$itemId,(int)$a['id'],(float)$a['price']]);
          }
          $pdo->prepare('UPDATE product SET sales=sales+? WHERE product_id=?')->execute([(int)$line['qty'],(int)$line['id']]);
        }
        $pdo->commit();
        $createdOrderId = $orderId;
      } catch(Throwable $e){
        $pdo->rollBack();
        $msg = 'Order failed: '.$e->getMessage();
      }
  }
}

$page  = 'cart';
$title = 'Checkout — Misaki';
require __DIR__.'/includes/header.php';
?>

<style>
  .checkout-form-input {
    width: 100%;
    padding: 14px 16px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--white);
    font-family: inherit;
    font-size: 0.9rem;
    color: var(--ink);
    box-sizing: border-box; 
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  .checkout-form-input:focus {
    outline: none;
    border-color: var(--sage);
    box-shadow: 0 0 0 3px var(--sage-lt);
  }
  .checkout-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--muted-fg);
    margin-bottom: 8px;
  }
  .address-box-header {
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    margin-bottom: 20px; 
    margin-top: 4px;
  }
  /* Shopee Style Labels */
  .label-group { display: flex; gap: 12px; margin-bottom: 20px; }
  .label-chip { padding: 8px 20px; border: 1px solid var(--border); border-radius: 4px; font-size: 0.85rem; cursor: pointer; transition: all 0.2s; background: var(--white); color: var(--muted-fg); }
  .label-input { display: none; }
  .label-input:checked + .label-chip { border-color: var(--sage-deep); color: var(--sage-deep); background: var(--success-bg); font-weight: 600; }

  /* ── Address selection cards ── */
  .addr-select-card {
    border: 2px solid var(--border);
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s;
    display: flex;
    align-items: flex-start;
    gap: 12px;
  }
  .addr-select-card:hover { border-color: var(--sage); }
  .addr-select-card.selected { border-color: var(--sage-deep); background: var(--success-bg); }
  .addr-select-card input[type="radio"] { margin-top: 3px; accent-color: var(--sage-deep); width:16px; height:16px; flex-shrink:0; }
  .addr-card-body { flex: 1; }
  .addr-edit-panel { display: none; margin-top: 14px; padding-top: 14px; border-top: 1px dashed var(--border); }
  .addr-edit-panel.open { display: block; }
  .addr-action-btn {
    background: none;
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 4px 10px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
    color: var(--sage-deep);
    border-color: var(--sage);
    font-family: inherit;
  }
  .addr-action-btn:hover { background: var(--success-bg); }
  .addr-limit-msg {
    font-size: 0.85rem;
    color: var(--error-fg);
    background: var(--error-bg);
    border: 1px solid var(--error-bd);
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 12px;
    font-weight: 500;
  }
  .add-addr-panel {
    border: 1px dashed var(--border);
    border-radius: 10px;
    padding: 20px;
    margin-top: 10px;
    display: none;
  }
  .add-addr-panel.open { display: block; }
</style>

<div class="page-pad">
  <section class="container reveal" style="max-width:700px" data-checkout-page>
    <div class="eyebrow">お支払い</div>
    <h1 style="font-size:clamp(2rem,4vw,2.75rem);margin-top:6px">Checkout</h1>

    <?php if($createdOrderId): ?>
      <div class="auth-success" style="margin-top:24px;font-size:1rem">
        ✓ Order #<?= $createdOrderId ?> placed — thank you!
      </div>
      <p style="margin-top:24px;color:var(--muted-fg);font-size:.9rem">
        Your blooms are being prepared. You can track your order from your account.
      </p>
      <div style="margin-top:28px;display:flex;gap:12px;flex-wrap:wrap">
        <a class="btn btn-ink" href="account.php">View my orders →</a>
        <a class="btn btn-outline" href="shop.php">Continue shopping</a>
      </div>
      <script>localStorage.removeItem('misaki_cart');</script>

    <?php elseif($msg && !$createdOrderId): ?>
      <div class="auth-error" style="margin-top:24px"><?= htmlspecialchars($msg) ?></div>
      <p style="margin-top:16px"><a href="cart.php" class="btn btn-ink">Back to cart</a></p>

    <?php else: ?>
      <p style="color:var(--muted-fg);margin-top:8px;font-size:.9rem">Review your order and confirm.</p>
      <div class="checkout-summary" style="margin-top:28px"></div>
      
      <div style="margin-top:28px">
        <input type="hidden" name="action" value="place_order" form="checkoutForm">
        <input type="hidden" name="cart" id="cartJson" form="checkoutForm">
        
        <div style="margin-bottom:24px;padding:28px;border:1px solid var(--border);border-radius:var(--radius-lg);background:var(--white);position:relative;overflow:hidden;">
          <div style="position:absolute;top:0;left:0;right:0;height:5px;background:repeating-linear-gradient(45deg,var(--sage) 0,var(--sage) 20px,transparent 20px,transparent 40px,var(--cream-xdk) 40px,var(--cream-xdk) 60px,transparent 60px,transparent 80px);"></div>

          <div class="address-box-header">
            <h3 style="font-family:var(--ff-display);font-size:1.4rem;display:flex;align-items:center;gap:10px;color:var(--sage-deep);">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
              Delivery Address
            </h3>
            <?php if (!empty($addresses)): ?>
              <span style="font-size:0.8rem;color:var(--muted-fg);"><?= $addressCount ?>/<?= MAX_ADDRESSES ?></span>
            <?php endif; ?>
          </div>

          <?php if ($addAddrMsg): ?>
            <div class="<?= $addAddrType==='error'?'auth-error':'auth-success' ?>" style="margin-bottom:14px;font-size:0.85rem;padding:10px;">
              <?= htmlspecialchars($addAddrMsg) ?>
            </div>
          <?php endif; ?>

          <?php if (empty($addresses)): ?>
            <p style="font-size:.85rem;color:var(--muted-fg);margin-bottom:16px;">No saved addresses found. Please enter your delivery details below.</p>
            <input type="hidden" name="selected_address_id" value="0" form="checkoutForm">

            <span class="checkout-label">Address Label</span>
            <div class="label-group">
              <label>
                <input type="radio" name="address_label" value="Home" class="label-input" onchange="handleLabelChange(this.value)" checked form="checkoutForm">
                <span class="label-chip">Home</span>
              </label>
              <label>
                <input type="radio" name="address_label" value="Someone Else" class="label-input" onchange="handleLabelChange(this.value)" form="checkoutForm">
                <span class="label-chip">Someone Else</span>
              </label>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
              <div>
                <span class="checkout-label">Recipient Name</span>
                <input type="text" name="delivery_name" class="checkout-form-input" placeholder="Full Name" required form="checkoutForm">
              </div>
              <div>
                <span class="checkout-label">Phone Number</span>
                <input type="text" name="delivery_phone" class="checkout-form-input" placeholder="09XX XXX XXXX" required form="checkoutForm">
              </div>
            </div>
            <div style="margin-bottom:16px;">
              <span class="checkout-label">Street / Barangay / Complete Address</span>
              <textarea name="delivery_address" class="checkout-form-input" rows="3" required placeholder="House No., Street, Barangay..." form="checkoutForm"></textarea>
            </div>

          <?php else: ?>
            <input type="hidden" name="selected_address_id" id="selectedAddrId" value="<?= $defaultAddr ? $defaultAddr['address_id'] : $addresses[0]['address_id'] ?>" form="checkoutForm">

            <?php foreach ($addresses as $addr): 
              $isSel = ($defaultAddr && $addr['address_id'] === $defaultAddr['address_id']);
            ?>
              <div class="addr-select-card <?= $isSel ? 'selected' : '' ?>"
                   id="addr-select-<?= $addr['address_id'] ?>"
                   onclick="selectAddress(<?= $addr['address_id'] ?>, '<?= addslashes($addr['label']) ?>')">
                <input type="radio" name="_addr_radio" value="<?= $addr['address_id'] ?>" <?= $isSel?'checked':'' ?> onclick="event.stopPropagation(); selectAddress(<?= $addr['address_id'] ?>, '<?= addslashes($addr['label']) ?>')">
                <div class="addr-card-body">
                  <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px;">
                    <span class="pill on" style="font-size:0.6rem;"><?= htmlspecialchars($addr['label']) ?></span>
                    <?php if ($addr['is_default']): ?>
                      <span style="font-size:0.7rem;color:var(--sage-deep);font-weight:700;background:var(--white);border:1px solid var(--sage);border-radius:20px;padding:2px 8px;">Default</span>
                    <?php endif; ?>
                    <strong style="color:var(--ink);font-size:.9rem;"><?= htmlspecialchars($addr['full_name']) ?></strong>
                    <span style="color:var(--muted-fg);font-size:.82rem;">(+63) <?= htmlspecialchars($addr['phone']) ?></span>
                    <button type="button" class="addr-action-btn" style="margin-left:auto;" onclick="event.stopPropagation(); toggleCoEditPanel(<?= $addr['address_id'] ?>)">Edit</button>
                  </div>
                  <div style="color:var(--ink-lt);font-size:.85rem;line-height:1.6;">
                    <?= htmlspecialchars($addr['address_text']) ?>, <?= htmlspecialchars($addr['city']) ?>, Metro Manila
                  </div>

                  <div class="addr-edit-panel" id="co-edit-panel-<?= $addr['address_id'] ?>">
                    <form method="post" style="margin-top:4px;">
                      <input type="hidden" name="action" value="edit_address_checkout">
                      <input type="hidden" name="address_id" value="<?= $addr['address_id'] ?>">
                      <input type="hidden" name="cart" value=""><span class="checkout-label">Address Label</span>
                      <div class="label-group">
                        <label>
                          <input type="radio" name="label" value="Home" class="label-input" <?= $addr['label']==='Home'?'checked':'' ?>>
                          <span class="label-chip">Home</span>
                        </label>
                        <label>
                          <input type="radio" name="label" value="Someone Else" class="label-input" <?= $addr['label']==='Someone Else'?'checked':'' ?>>
                          <span class="label-chip">Someone Else</span>
                        </label>
                      </div>
                      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                        <div>
                          <span class="checkout-label">Recipient Name</span>
                          <input type="text" name="full_name" class="checkout-form-input" value="<?= htmlspecialchars($addr['full_name']) ?>" required>
                        </div>
                        <div>
                          <span class="checkout-label">Phone</span>
                          <input type="text" name="phone" class="checkout-form-input" value="<?= htmlspecialchars($addr['phone']) ?>" required>
                        </div>
                      </div>
                      <div style="margin-bottom:12px;">
                        <span class="checkout-label">Street / Barangay / Complete Address</span>
                        <textarea name="address_text" class="checkout-form-input" rows="2" required><?= htmlspecialchars($addr['address_text']) ?></textarea>
                      </div>
                      <div style="margin-bottom:12px;">
                        <span class="checkout-label">City (Metro Manila only)</span>
                        <select name="city" class="checkout-form-input" required>
                          <?php foreach ($NCR_CITIES as $c): ?>
                            <option value="<?= htmlspecialchars($c) ?>" <?= $addr['city']===$c?'selected':'' ?>><?= htmlspecialchars($c) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div style="margin-bottom:12px;">
                        <label style="cursor:pointer;display:inline-flex;align-items:center;gap:8px;font-size:.82rem;color:var(--ink);font-weight:500;">
                          <input type="checkbox" name="is_default" value="1" <?= $addr['is_default']?'checked':'' ?> style="width:15px;height:15px;accent-color:var(--sage-deep);cursor:pointer;">
                          Set as default
                        </label>
                      </div>
                      <div style="display:flex;gap:8px;">
                        <button type="submit" class="btn btn-ink" style="padding:9px 18px;font-size:.85rem;">Save</button>
                        <button type="button" class="btn btn-outline" style="padding:9px 18px;font-size:.85rem;" onclick="event.stopPropagation(); toggleCoEditPanel(<?= $addr['address_id'] ?>)">Cancel</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>

          <?php endif; ?>

          <div style="margin-top:12px;">
            <?php if ($addressCount >= MAX_ADDRESSES): ?>
              <div class="addr-limit-msg">
                You have hit the maximum limit of 5 addresses. Please delete one from your <a href="account.php" style="color:var(--sage-deep);text-decoration:underline;">account page</a> to add a new address.
              </div>
            <?php else: ?>
              <button type="button" class="btn btn-outline" style="width:100%;justify-content:center;" onclick="toggleCoAddPanel()">
                + Add New Shipping Address
              </button>
              <div class="add-addr-panel" id="co-add-panel">
                <form method="post">
                  <input type="hidden" name="action" value="add_address_checkout">

                  <span class="checkout-label">Address Label</span>
                  <div class="label-group">
                    <label>
                      <input type="radio" name="label" value="Home" class="label-input" checked>
                      <span class="label-chip">Home</span>
                    </label>
                    <label>
                      <input type="radio" name="label" value="Someone Else" class="label-input">
                      <span class="label-chip">Someone Else</span>
                    </label>
                  </div>
                  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                    <div>
                      <span class="checkout-label">Recipient Name</span>
                      <input type="text" name="full_name" class="checkout-form-input" placeholder="Full Name" required>
                    </div>
                    <div>
                      <span class="checkout-label">Phone</span>
                      <input type="text" name="phone" class="checkout-form-input" placeholder="09XX XXX XXXX" required>
                    </div>
                  </div>
                  <div style="margin-bottom:12px;">
                    <span class="checkout-label">Street / Barangay / Complete Address</span>
                    <textarea name="address_text" class="checkout-form-input" rows="2" required placeholder="House No., Street, Barangay..."></textarea>
                  </div>
                  <div style="margin-bottom:12px;">
                    <span class="checkout-label">City (Metro Manila only)</span>
                    <select name="city" class="checkout-form-input" required>
                      <option value="" disabled selected>— Select City —</option>
                      <?php foreach ($NCR_CITIES as $c): ?>
                        <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div style="margin-bottom:12px;">
                    <label style="cursor:pointer;display:inline-flex;align-items:center;gap:8px;font-size:.82rem;color:var(--ink);font-weight:500;">
                      <input type="checkbox" name="is_default" value="1" <?= empty($addresses)?'checked':'' ?> style="width:15px;height:15px;accent-color:var(--sage-deep);cursor:pointer;">
                      Set as default address
                    </label>
                  </div>
                  <div style="display:flex;gap:8px;">
                    <button type="submit" class="btn btn-ink" style="padding:9px 18px;font-size:.85rem;">Save Address</button>
                    <button type="button" class="btn btn-outline" style="padding:9px 18px;font-size:.85rem;" onclick="toggleCoAddPanel()">Cancel</button>
                  </div>
                </form>
              </div>
            <?php endif; ?>
          </div>
        </div>
        
        <form method="post" id="checkoutForm" enctype="multipart/form-data">
        <div style="margin-bottom: 24px; padding: 28px; border: 1px solid var(--border); border-radius: var(--radius-lg); background: var(--white);">
          <h3 style="font-family: var(--ff-display); font-size: 1.4rem; margin-bottom: 20px; color: var(--ink);">Payment Method</h3>
          <div style="display: flex; gap: 32px; flex-wrap: wrap; margin-bottom: 20px;">
            <label id="cod-label" style="cursor: pointer; display: flex; align-items: center; gap: 10px; font-size: .95rem; font-weight: 500;">
              <input type="radio" name="payment_method" value="cash" id="radio-cod" checked onchange="toggleReceipt(false)" style="width: 18px; height: 18px; accent-color: var(--sage-deep);"> 
              Cash on Delivery
            </label>
            <label style="cursor: pointer; display: flex; align-items: center; gap: 10px; font-size: .95rem; font-weight: 500;">
              <input type="radio" name="payment_method" value="gcash" id="radio-gcash" onchange="toggleReceipt(true)" style="width: 18px; height: 18px; accent-color: var(--sage-deep);"> 
              GCash
            </label>
          </div>

          <div id="gift-warning" style="display:none; color:var(--error-fg); font-size:0.85rem; margin-bottom:16px; background:var(--error-bg); padding:10px; border-radius:4px; border:1px solid var(--error-bd);">
            * <strong>Note:</strong> Cash on Delivery is disabled for "Someone Else" deliveries to ensure the recipient doesn't pay for the gift.
          </div>

          <div id="gcash-upload" style="display: none; margin-top: 16px; padding: 20px; border: 1px solid var(--sage-lt); border-radius: var(--radius); background: var(--success-bg);">
            <p style="font-size: 0.9rem; margin-bottom: 16px; color: var(--success-fg); line-height: 1.5;">
              Please send payment to GCash: <strong style="text-decoration: underline;">0912 345 6789</strong> (Misaki Floral).<br>
              Upload your screenshot below to confirm.
            </p>
            <input type="file" name="receipt" id="receiptFile" class="checkout-form-input" accept="image/png, image/jpeg" style="background: var(--white); padding: 10px;">
          </div>
        </div>

        <p style="font-size: 0.8rem; color: var(--muted-fg); margin-bottom: 16px; text-align: center;">
          By clicking PLACE ORDER, you acknowledge that you have read the <a href="legal/privacy.php" target="_blank" style="color: var(--sage-deep); text-decoration: underline;">Privacy Policy</a>.
        </p>

        <button class="btn btn-ink" type="submit" style="width:100%; justify-content:center; padding: 16px; font-size: 1rem; letter-spacing: 0.05em;">
          PLACE ORDER
        </button>
      </form>
      </div>

      <script>
        // ── Address selection ──────────────────────────────────────────────────
        let currentLabel = '<?= $defaultAddr ? addslashes($defaultAddr['label']) : 'Home' ?>';

        function selectAddress(id, label) {
          document.getElementById('selectedAddrId').value = id;
          currentLabel = label;

          document.querySelectorAll('.addr-select-card').forEach(function(card) {
            card.classList.remove('selected');
            var radio = card.querySelector('input[type="radio"]');
            if (radio) radio.checked = false;
          });

          var chosen = document.getElementById('addr-select-' + id);
          if (chosen) {
            chosen.classList.add('selected');
            var radio = chosen.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
          }
          handleLabelChange(label);
        }

        function toggleCoEditPanel(id) {
          var panel = document.getElementById('co-edit-panel-' + id);
          if (panel) panel.classList.toggle('open');
        }

        function toggleCoAddPanel() {
          var panel = document.getElementById('co-add-panel');
          if (panel) panel.classList.toggle('open');
        }

        // ── Payment / receipt ──────────────────────────────────────────────────
        function toggleReceipt(show) {
          const uploadDiv = document.getElementById('gcash-upload');
          uploadDiv.style.display = show ? 'block' : 'none';
          document.getElementById('receiptFile').required = show;
        }

        function handleLabelChange(val) {
          const codLabel  = document.getElementById('cod-label');
          const radioCod  = document.getElementById('radio-cod');
          const warning   = document.getElementById('gift-warning');
          const gcashRadio = document.getElementById('radio-gcash');

          if (val === 'Someone Else') {
            codLabel.style.opacity = '0.4';
            codLabel.style.pointerEvents = 'none';
            radioCod.disabled = true;
            gcashRadio.checked = true;
            toggleReceipt(true);
            warning.style.display = 'block';
          } else {
            codLabel.style.opacity = '1';
            codLabel.style.pointerEvents = 'auto';
            radioCod.disabled = false;
            warning.style.display = 'none';
          }
        }

        // Initialize payment state based on the selected/default address label
        document.addEventListener('DOMContentLoaded', function() {
          handleLabelChange(currentLabel);
        });
      </script>
    <?php endif; ?>
  </section>
</div>

<?php require __DIR__.'/includes/footer.php'; ?>