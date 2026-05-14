<?php
require_once __DIR__.'/includes/auth.php';
require_login('login.php');
require_once __DIR__.'/includes/products.php';

$me = current_user();
$st = db()->prepare('SELECT * FROM user WHERE user_id=?');
$st->execute([$me['user_id']]);
$me = $st->fetch();

// ─── NCR city list (shared constant) ──────────────────────────────────────────
$NCR_CITIES = [
  'Caloocan','Las Piñas','Makati','Malabon','Mandaluyong','Manila',
  'Marikina','Muntinlupa','Navotas','Parañaque','Pasay','Pasig',
  'Pateros','Quezon City','San Juan','Taguig','Valenzuela'
];

$maxAddresses = 5;

// ─── Fetch all saved addresses ─────────────────────────────────────────────────
function fetchAddresses($userId) {
  $st = db()->prepare('SELECT * FROM user_address WHERE user_id=? ORDER BY is_default DESC, address_id ASC');
  $st->execute([$userId]);
  return $st->fetchAll();
}

$addresses    = fetchAddresses($me['user_id']);
$addressCount = count($addresses);
$flashMsg     = '';
$flashType    = 'success'; // 'success' | 'error'
$keepAddPanelOpen = false;  // keep add panel open after validation error

// ─── POST handlers ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  // ── Add new address ───────────────────────────────────────────────────────
  if ($action === 'add_address') {
    $addresses    = fetchAddresses($me['user_id']); // re-fetch for accurate count
    $addressCount = count($addresses);

    if ($addressCount >= $maxAddresses) {
      $flashMsg  = 'You have hit the maximum limit of 5 addresses.';
      $flashType = 'error';
    } else {
      $label   = in_array($_POST['label'] ?? '', ['Home','Someone Else']) ? $_POST['label'] : 'Home';
      $name    = trim($_POST['full_name']    ?? '');
      $phone   = trim($_POST['phone']        ?? '');
      $addrTxt = trim($_POST['address_text'] ?? '');
      $city    = trim($_POST['city']         ?? '');

      if (!$name || !$phone || !$addrTxt || !$city) {
        $flashMsg         = 'All fields are required. Please fill in every field.';
        $flashType        = 'error';
        $keepAddPanelOpen = true;
      } else {
        $isFirst   = ($addressCount === 0) ? 1 : 0;
        $isDefault = isset($_POST['is_default']) ? 1 : $isFirst;

        try {
          if ($isDefault) {
            db()->prepare('UPDATE user_address SET is_default=0 WHERE user_id=?')->execute([$me['user_id']]);
          }
          db()->prepare('INSERT INTO user_address (user_id,label,full_name,phone,address_text,city,is_default) VALUES (?,?,?,?,?,?,?)')
              ->execute([$me['user_id'], $label, $name, $phone, $addrTxt, $city, $isDefault]);
          $flashMsg = 'New address saved successfully!';
        } catch (Throwable $e) {
          $flashMsg         = 'Could not save the address. Please try again.';
          $flashType        = 'error';
          $keepAddPanelOpen = true;
        }
      }
    }
    $addresses    = fetchAddresses($me['user_id']);
    $addressCount = count($addresses);
  }

  // ── Edit/update an address ────────────────────────────────────────────────
  if ($action === 'edit_address') {
    $addrId  = (int)($_POST['address_id'] ?? 0);
    $label   = in_array($_POST['label'] ?? '', ['Home','Someone Else']) ? $_POST['label'] : 'Home';
    $name    = trim($_POST['full_name']    ?? '');
    $phone   = trim($_POST['phone']        ?? '');
    $addrTxt = trim($_POST['address_text'] ?? '');
    $city    = trim($_POST['city']         ?? '');

    if (!$name || !$phone || !$addrTxt || !$city) {
      $flashMsg  = 'All fields are required.';
      $flashType = 'error';
    } else {
      // Verify this address belongs to the current user
      $chk = db()->prepare('SELECT address_id FROM user_address WHERE address_id=? AND user_id=?');
      $chk->execute([$addrId, $me['user_id']]);
      if ($chk->fetch()) {
        $isDefault = isset($_POST['is_default']) ? 1 : 0;
        if ($isDefault) {
          db()->prepare('UPDATE user_address SET is_default=0 WHERE user_id=?')->execute([$me['user_id']]);
        }
        db()->prepare('UPDATE user_address SET label=?,full_name=?,phone=?,address_text=?,city=?,is_default=? WHERE address_id=? AND user_id=?')
            ->execute([$label, $name, $phone, $addrTxt, $city, $isDefault, $addrId, $me['user_id']]);
        $flashMsg = 'Address updated successfully!';
      }
    }
    $addresses    = fetchAddresses($me['user_id']);
    $addressCount = count($addresses);
  }

  // ── Delete an address ─────────────────────────────────────────────────────
  if ($action === 'delete_address') {
    $addrId = (int)($_POST['address_id'] ?? 0);
    $chk    = db()->prepare('SELECT address_id, is_default FROM user_address WHERE address_id=? AND user_id=?');
    $chk->execute([$addrId, $me['user_id']]);
    $row = $chk->fetch();
    if ($row) {
      db()->prepare('DELETE FROM user_address WHERE address_id=? AND user_id=?')->execute([$addrId, $me['user_id']]);
      // If we deleted the default, promote the next one
      if ($row['is_default']) {
        $next = db()->prepare('SELECT address_id FROM user_address WHERE user_id=? ORDER BY address_id ASC LIMIT 1');
        $next->execute([$me['user_id']]);
        $nrow = $next->fetch();
        if ($nrow) {
          db()->prepare('UPDATE user_address SET is_default=1 WHERE address_id=?')->execute([$nrow['address_id']]);
        }
      }
      $flashMsg = 'Address deleted.';
    }
    $addresses    = fetchAddresses($me['user_id']);
    $addressCount = count($addresses);
  }

  // ── Set default address ───────────────────────────────────────────────────
  if ($action === 'set_default') {
    $addrId = (int)($_POST['address_id'] ?? 0);
    $chk    = db()->prepare('SELECT address_id FROM user_address WHERE address_id=? AND user_id=?');
    $chk->execute([$addrId, $me['user_id']]);
    if ($chk->fetch()) {
      db()->prepare('UPDATE user_address SET is_default=0 WHERE user_id=?')->execute([$me['user_id']]);
      db()->prepare('UPDATE user_address SET is_default=1 WHERE address_id=?')->execute([$addrId]);
      $flashMsg = 'Default address updated.';
    }
    $addresses    = fetchAddresses($me['user_id']);
    $addressCount = count($addresses);
  }
}

// ─── Orders ────────────────────────────────────────────────────────────────────
$st = db()->prepare('SELECT o.order_id,o.total,o.status,o.created_at FROM `order` o WHERE o.user_id=? ORDER BY o.created_at DESC');
$st->execute([$me['user_id']]);
$orders = $st->fetchAll();

$itemsByOrder = [];
foreach($orders as $o){
  $st = db()->prepare('SELECT oi.order_item_id, oi.product_id, p.name, p.image, p.slug, oi.qty, oi.line_total, (SELECT review_id FROM review WHERE order_id=oi.order_id AND product_id=oi.product_id) AS review_id FROM order_item oi JOIN product p ON p.product_id=oi.product_id WHERE oi.order_id=?');
  $st->execute([$o['order_id']]);
  $itemsByOrder[$o['order_id']] = $st->fetchAll();
}

$msg = '';
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='review'){
  $orderId   = (int)$_POST['order_id'];
  $productId = (int)$_POST['product_id'];
  $rating    = max(1,min(5,(int)$_POST['rating']));
  $body      = trim($_POST['body']??'');
  try{
    $st = db()->prepare('INSERT INTO review (order_id,product_id,user_id,rating,body) VALUES (?,?,?,?,?)');
    $st->execute([$orderId,$productId,$me['user_id'],$rating,$body]);
    $msg = 'Thank you for your review.';
  }catch(Throwable $e){
    $msg = 'You already reviewed this item.';
  }
}

$page='account'; $title='Account — Misaki';
require __DIR__.'/includes/header.php';
?>

<style>
  /* ── Account Layout ── */
  .acct-wrap {
    max-width: 1100px;
    margin: 0 auto;
    padding: calc(var(--nav-h) + 40px) var(--gutter) 80px;
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 28px;
    align-items: start;
  }
  @media (max-width: 768px) {
    .acct-wrap { grid-template-columns: 1fr; padding-top: calc(var(--nav-h) + 20px); }
    .acct-sidebar { display: flex; flex-direction: row; gap: 0; border-radius: var(--radius-lg); overflow: hidden; }
    .acct-menu-item { border-left: none !important; border-bottom: 3px solid transparent; padding: 12px 16px; flex: 1; text-align: center; justify-content: center; }
    .acct-menu-item.active { border-bottom-color: var(--sage-deep) !important; border-left: none !important; }
  }

  /* ── Sidebar ── */
  .acct-sidebar {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
  }
  .acct-sidebar-top {
    padding: 28px 24px 24px;
    border-bottom: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 10px;
  }
  .acct-avatar {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: var(--cream-dk);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--muted-fg);
  }
  .acct-name {
    font-family: var(--ff-display);
    font-size: 1.2rem;
    color: var(--ink);
    font-weight: 600;
    line-height: 1.3;
  }
  .acct-email {
    font-size: 0.78rem;
    color: var(--muted-fg);
  }
  .acct-menu {
    padding: 8px 0;
    list-style: none;
  }
  .acct-menu-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 13px 24px;
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--ink-lt);
    cursor: pointer;
    border-left: 3px solid transparent;
    transition: background 0.15s, color 0.15s, border-color 0.15s;
    text-decoration: none;
    user-select: none;
  }
  .acct-menu-item:hover {
    background: var(--cream);
    color: var(--ink);
  }
  .acct-menu-item.active {
    border-left-color: var(--sage-deep);
    background: var(--success-bg);
    color: var(--sage-deep);
    font-weight: 600;
  }
  .acct-menu-item svg { flex-shrink: 0; opacity: 0.7; }
  .acct-menu-item.active svg { opacity: 1; }
  .acct-menu-divider {
    height: 1px;
    background: var(--border);
    margin: 6px 0;
  }

  /* ── Main Content Panel ── */
  .acct-panel {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
  }
  .acct-panel-header {
    padding: 22px 28px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .acct-panel-title {
    font-family: var(--ff-display);
    font-size: 1.5rem;
    color: var(--ink);
  }
  .acct-panel-body {
    padding: 28px;
  }

  /* ── Tab content (JS toggled) ── */
  .acct-tab { display: none; }
  .acct-tab.active { display: block; }

  /* ── Shared form styles ── */
  .checkout-form-input {
    width: 100%;
    padding: 12px 14px;
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
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--muted-fg);
    margin-bottom: 7px;
  }
  .label-group { display: flex; gap: 10px; margin-bottom: 18px; }
  .label-chip { padding: 7px 18px; border: 1px solid var(--border); border-radius: 4px; font-size: 0.83rem; cursor: pointer; transition: all 0.2s; background: var(--white); color: var(--muted-fg); }
  .label-input { display: none; }
  .label-input:checked + .label-chip { border-color: var(--sage-deep); color: var(--sage-deep); background: var(--success-bg); font-weight: 600; }

  /* ── Address cards ── */
  .addr-card {
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 16px 18px;
    margin-bottom: 12px;
    background: var(--card-bg);
    transition: border-color 0.2s;
  }
  .addr-card.is-default {
    border-color: var(--sage);
    background: var(--success-bg);
  }
  .addr-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
  }
  .addr-card-actions {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
  }
  .addr-action-btn {
    background: none;
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 5px 11px;
    font-size: 0.76rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
    color: var(--muted-fg);
    font-family: inherit;
  }
  .addr-action-btn:hover { border-color: var(--sage); color: var(--sage-deep); }
  .addr-action-btn.edit-btn { color: var(--sage-deep); border-color: var(--sage); }
  .addr-action-btn.delete-btn { color: var(--error-fg); border-color: var(--error-bd); }
  .addr-action-btn.delete-btn:hover { background: var(--error-bg); }
  .addr-edit-panel { display: none; margin-top: 16px; padding-top: 16px; border-top: 1px dashed var(--border); }
  .addr-edit-panel.open { display: block; }
  .addr-limit-msg {
    font-size: 0.85rem;
    color: var(--error-fg);
    background: var(--error-bg);
    border: 1px solid var(--error-bd);
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 14px;
    font-weight: 500;
  }
  .add-addr-panel {
    border: 1px dashed var(--border);
    border-radius: 10px;
    padding: 20px;
    margin-top: 8px;
    display: none;
  }
  .add-addr-panel.open { display: block; }

  /* ── Orders / Purchase History ── */
  .purchase-tabs {
    display: flex;
    gap: 0;
    border-bottom: 1px solid var(--border);
    overflow-x: auto;
    scrollbar-width: none;
  }
  .purchase-tabs::-webkit-scrollbar { display: none; }
  .ptab {
    padding: 12px 20px;
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--muted-fg);
    cursor: pointer;
    border-bottom: 2px solid transparent;
    white-space: nowrap;
    transition: color 0.2s, border-color 0.2s;
    user-select: none;
  }
  .ptab:hover { color: var(--ink); }
  .ptab.active { color: var(--sage-deep); border-bottom-color: var(--sage-deep); font-weight: 600; }

  .order-card {
    border: 1px solid var(--border);
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 14px;
    background: var(--white);
  }
  .order-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 18px;
    background: var(--cream);
    border-bottom: 1px solid var(--border);
    font-size: 0.87rem;
    flex-wrap: wrap;
    gap: 8px;
  }
  .order-items { padding: 0; }
  .order-item {
    display: flex;
    gap: 14px;
    padding: 16px 18px;
    border-bottom: 1px solid var(--border);
    align-items: flex-start;
  }
  .order-item:last-child { border-bottom: none; }
  .order-item img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
    flex-shrink: 0;
  }
  .no-data-box {
    text-align: center;
    padding: 48px 24px;
    color: var(--muted-fg);
    font-size: 0.9rem;
    background: var(--cream);
    border-radius: 8px;
  }

  /* ── Manage Profile ── */
  .profile-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-bottom: 18px;
  }
  @media (max-width: 600px) { .profile-form-grid { grid-template-columns: 1fr; } }

  .review-form summary {
    font-size: 0.8rem;
    color: var(--sage-deep);
    cursor: pointer;
    margin-top: 6px;
  }
</style>

<?php
// Determine which tab to show based on URL
$activeTab = $_GET['tab'] ?? 'addresses';
if (!in_array($activeTab, ['addresses','orders','profile'])) $activeTab = 'addresses';
?>

<div class="acct-wrap">

  <!-- ══ SIDEBAR ══ -->
  <aside class="acct-sidebar">
    <div class="acct-sidebar-top">
      <div class="acct-avatar">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
      </div>
      <div>
        <div class="acct-name"><?= htmlspecialchars($me['full_name']) ?></div>
        <div class="acct-email"><?= htmlspecialchars($me['email']) ?></div>
      </div>
    </div>
    <nav>
      <ul class="acct-menu">
        <li>
          <a href="?tab=addresses" class="acct-menu-item <?= $activeTab==='addresses'?'active':'' ?>">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            My Account
          </a>
        </li>
        <li>
          <a href="?tab=orders" class="acct-menu-item <?= $activeTab==='orders'?'active':'' ?>">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            Purchase History
          </a>
        </li>
        <li><div class="acct-menu-divider"></div></li>
        <li>
          <a href="?tab=profile" class="acct-menu-item <?= $activeTab==='profile'?'active':'' ?>">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            Manage Profile
          </a>
        </li>
        <li><div class="acct-menu-divider"></div></li>
        <li>
          <a href="logout.php" class="acct-menu-item" style="color:var(--error-fg);">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Sign Out
          </a>
        </li>
      </ul>
    </nav>
  </aside>

  <!-- ══ MAIN PANEL ══ -->
  <main class="acct-panel reveal">

    <!-- ══ TAB: MY ACCOUNT (Addresses) ══ -->
    <?php if ($activeTab === 'addresses'): ?>
    <div class="acct-panel-header">
      <h2 class="acct-panel-title">My Account</h2>
      <?php if ($addressCount < $maxAddresses): ?>
        <button type="button" class="btn btn-ink" style="padding:9px 18px;font-size:.83rem;" onclick="toggleAddPanel()">+ Add New Shipping Address</button>
      <?php endif; ?>
    </div>
    <div class="acct-panel-body">

      <?php if ($flashMsg): ?>
        <div class="<?= $flashType === 'error' ? 'auth-error' : 'auth-success' ?>" style="margin-bottom:18px;font-size:0.85rem;padding:12px;">
          <?= htmlspecialchars($flashMsg) ?>
        </div>
      <?php endif; ?>

      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <h3 style="font-size:0.9rem;font-weight:600;color:var(--muted-fg);text-transform:uppercase;letter-spacing:.05em;">Delivery Address</h3>
        <span style="font-size:0.8rem;color:var(--muted-fg);"><?= $addressCount ?>/<?= $maxAddresses ?></span>
      </div>

      <?php if (empty($addresses)): ?>
        <div class="no-data-box">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 12px;opacity:.4;display:block;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          No saved addresses yet. Add one below.
        </div>
      <?php else: foreach ($addresses as $addr): ?>
        <div class="addr-card <?= $addr['is_default'] ? 'is-default' : '' ?>" id="addr-card-<?= $addr['address_id'] ?>">
          <div class="addr-card-top">
            <div style="flex:1;">
              <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap;">
                <span class="pill on" style="font-size:0.6rem;"><?= htmlspecialchars($addr['label']) ?></span>
                <?php if ($addr['is_default']): ?>
                  <span style="font-size:0.7rem;color:var(--sage-deep);font-weight:700;background:var(--white);border:1px solid var(--sage);border-radius:20px;padding:2px 8px;">Default</span>
                <?php endif; ?>
                <strong style="color:var(--ink);font-size:.92rem;"><?= htmlspecialchars($addr['full_name']) ?></strong>
                <span style="color:var(--muted-fg);font-size:.83rem;">(+63) <?= htmlspecialchars($addr['phone']) ?></span>
              </div>
              <div style="color:var(--ink-lt);font-size:.875rem;line-height:1.6;">
                <?= htmlspecialchars($addr['address_text']) ?><br>
                <span style="color:var(--muted-fg);"><?= htmlspecialchars($addr['city']) ?>, Metro Manila</span>
              </div>
            </div>
            <div class="addr-card-actions">
              <?php if (!$addr['is_default']): ?>
                <form method="post" style="display:inline;">
                  <input type="hidden" name="action" value="set_default">
                  <input type="hidden" name="address_id" value="<?= $addr['address_id'] ?>">
                  <button type="submit" class="addr-action-btn">Set Default</button>
                </form>
              <?php endif; ?>
              <button type="button" class="addr-action-btn edit-btn" onclick="toggleEditPanel(<?= $addr['address_id'] ?>)">Edit</button>
              <form method="post" style="display:inline;" onsubmit="return confirm('Delete this address?');">
                <input type="hidden" name="action" value="delete_address">
                <input type="hidden" name="address_id" value="<?= $addr['address_id'] ?>">
                <button type="submit" class="addr-action-btn delete-btn">Delete</button>
              </form>
            </div>
          </div>

          <!-- Edit Panel -->
          <div class="addr-edit-panel" id="edit-panel-<?= $addr['address_id'] ?>">
            <form method="post">
              <input type="hidden" name="action" value="edit_address">
              <input type="hidden" name="address_id" value="<?= $addr['address_id'] ?>">

              <span class="checkout-label">Address Label</span>
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

              <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                <div>
                  <span class="checkout-label">Recipient Name</span>
                  <input type="text" name="full_name" class="checkout-form-input" value="<?= htmlspecialchars($addr['full_name']) ?>" placeholder="Full Name" required>
                </div>
                <div>
                  <span class="checkout-label">Phone Number</span>
                  <input type="text" name="phone" class="checkout-form-input" value="<?= htmlspecialchars($addr['phone']) ?>" placeholder="09XX XXX XXXX" required>
                </div>
              </div>
              <div style="margin-bottom:14px;">
                <span class="checkout-label">Street / Barangay / Complete Address</span>
                <textarea name="address_text" class="checkout-form-input" rows="3" required placeholder="House No., Street, Barangay..."><?= htmlspecialchars($addr['address_text']) ?></textarea>
              </div>
              <div style="margin-bottom:14px;">
                <span class="checkout-label">City (Metro Manila only)</span>
                <select name="city" class="checkout-form-input" required>
                  <?php foreach ($NCR_CITIES as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>" <?= $addr['city']===$c?'selected':'' ?>><?= htmlspecialchars($c) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div style="margin-bottom:14px;">
                <label style="cursor:pointer;display:inline-flex;align-items:center;gap:8px;font-size:.84rem;color:var(--ink);font-weight:500;">
                  <input type="checkbox" name="is_default" value="1" <?= $addr['is_default']?'checked':'' ?> style="width:15px;height:15px;accent-color:var(--sage-deep);cursor:pointer;">
                  Set as default address
                </label>
              </div>
              <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-ink" style="padding:9px 18px;font-size:.85rem;">Save Changes</button>
                <button type="button" class="btn btn-outline" style="padding:9px 18px;font-size:.85rem;" onclick="toggleEditPanel(<?= $addr['address_id'] ?>)">Cancel</button>
              </div>
            </form>
          </div>
        </div>
      <?php endforeach; endif; ?>

      <!-- Add New Address panel -->
      <div class="add-addr-panel <?= (empty($addresses) || $keepAddPanelOpen) ? 'open' : '' ?>" id="add-addr-panel" style="margin-top:12px;">
        <form method="post">
          <input type="hidden" name="action" value="add_address">
          <h4 style="font-size:0.9rem;font-weight:600;color:var(--ink);margin-bottom:16px;">New Shipping Address</h4>

          <span class="checkout-label">Address Label</span>
          <div class="label-group">
            <label><input type="radio" name="label" value="Home" class="label-input" checked><span class="label-chip">Home</span></label>
            <label><input type="radio" name="label" value="Someone Else" class="label-input"><span class="label-chip">Someone Else</span></label>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
            <div>
              <span class="checkout-label">Recipient Name</span>
              <input type="text" name="full_name" class="checkout-form-input" placeholder="Full Name" required>
            </div>
            <div>
              <span class="checkout-label">Phone Number</span>
              <input type="text" name="phone" class="checkout-form-input" placeholder="09XX XXX XXXX" required>
            </div>
          </div>
          <div style="margin-bottom:14px;">
            <span class="checkout-label">Street / Barangay / Complete Address</span>
            <textarea name="address_text" class="checkout-form-input" rows="3" required placeholder="House No., Street, Barangay..."></textarea>
          </div>
          <div style="margin-bottom:14px;">
            <span class="checkout-label">City (Metro Manila only)</span>
            <select name="city" class="checkout-form-input" required>
              <option value="" disabled selected>— Select City —</option>
              <?php foreach ($NCR_CITIES as $c): ?>
                <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div style="margin-bottom:14px;">
            <label style="cursor:pointer;display:inline-flex;align-items:center;gap:8px;font-size:.84rem;color:var(--ink);font-weight:500;">
              <input type="checkbox" name="is_default" value="1" <?= empty($addresses)?'checked':'' ?> style="width:15px;height:15px;accent-color:var(--sage-deep);cursor:pointer;">
              Set as default address
            </label>
          </div>
          <p style="font-size:0.73rem;color:var(--muted-fg);margin-bottom:14px;">
            By clicking Save Address, you acknowledge that you have read the <a href="legal/privacy.php" target="_blank" style="color:var(--sage-deep);text-decoration:underline;">Privacy Policy</a>.
          </p>
          <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-ink" style="padding:9px 18px;font-size:.85rem;">Save Address</button>
            <button type="button" class="btn btn-outline" style="padding:9px 18px;font-size:.85rem;" onclick="toggleAddPanel()">Cancel</button>
          </div>
        </form>
      </div>

    </div><!-- /acct-panel-body addresses -->

    <?php if(isset($msg) && $msg): ?>
      <div class="auth-success" style="margin:16px 28px;font-size:.85rem;"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- ══ TAB: PURCHASE HISTORY ══ -->
    <?php elseif ($activeTab === 'orders'): ?>
    <div class="acct-panel-header">
      <h2 class="acct-panel-title">Purchase History</h2>
    </div>

    <!-- Status filter tabs -->
    <?php
      $filterStatus = $_GET['status'] ?? 'all';
      $validStatuses = ['all','pending','paid','fulfilled','cancelled'];
      if (!in_array($filterStatus, $validStatuses)) $filterStatus = 'all';
      $statusLabels = ['all'=>'All','pending'=>'To Pay','paid'=>'Processing','fulfilled'=>'Delivered','cancelled'=>'Failed'];
    ?>
    <div class="purchase-tabs">
      <?php foreach ($statusLabels as $s => $label): ?>
        <a href="?tab=orders&status=<?= $s ?>" class="ptab <?= $filterStatus===$s?'active':'' ?>"><?= $label ?></a>
      <?php endforeach; ?>
    </div>

    <div style="padding:20px;">
      <?php
        $filteredOrders = $filterStatus === 'all' ? $orders : array_filter($orders, fn($o) => $o['status'] === $filterStatus);
      ?>
      <?php if(empty($filteredOrders)): ?>
        <div class="no-data-box">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 12px;opacity:.4;display:block;"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          No Data
        </div>
      <?php else: foreach($filteredOrders as $o): ?>
        <div class="order-card">
          <div class="order-head">
            <div>
              <strong style="font-size:.88rem;">Order #<?= $o['order_id'] ?></strong>
              <span style="color:var(--muted-fg);margin-left:10px;font-size:.83rem;"><?= date('M j, Y', strtotime($o['created_at'])) ?></span>
            </div>
            <div style="display:flex;gap:12px;align-items:center;">
              <strong style="font-size:.9rem;">₱<?= number_format($o['total'],2) ?></strong>
              <span class="pill <?= in_array($o['status'],['paid','fulfilled'])?'on':'off' ?>"><?= $o['status'] ?></span>
            </div>
          </div>
          <div class="order-items">
            <?php foreach($itemsByOrder[$o['order_id']] as $it): ?>
              <div class="order-item">
                <a href="product.php?slug=<?= urlencode($it['slug']) ?>">
                  <img src="<?= htmlspecialchars($it['image']) ?>" alt="" loading="lazy">
                </a>
                <div style="flex:1">
                  <div style="font-family:var(--ff-display);font-size:1.05rem;">
                    <a href="product.php?slug=<?= urlencode($it['slug']) ?>" style="color:var(--ink);"><?= htmlspecialchars($it['name']) ?></a>
                  </div>
                  <div style="font-size:.8rem;color:var(--muted-fg);margin-top:2px;">
                    Qty: <?= $it['qty'] ?> &mdash; ₱<?= number_format($it['line_total'],2) ?>
                  </div>
                  <?php if($o['status']==='fulfilled' && !$it['review_id']): ?>
                    <details class="review-form">
                      <summary>Write a review</summary>
                      <form method="post" style="margin-top:10px;">
                        <input type="hidden" name="action" value="review">
                        <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                        <input type="hidden" name="product_id" value="<?= $it['product_id'] ?>">
                        <div class="rating-input" style="margin-bottom:8px;">
                          <?php for($r=5;$r>=1;$r--): ?>
                            <input type="radio" id="r<?= $o['order_id'].'_'.$it['product_id'].'_'.$r ?>" name="rating" value="<?= $r ?>" <?= $r===5?'checked':'' ?>>
                            <label for="r<?= $o['order_id'].'_'.$it['product_id'].'_'.$r ?>">★</label>
                          <?php endfor; ?>
                        </div>
                        <textarea name="body" rows="3" placeholder="How was your bloom?" required style="width:100%;padding:10px;border:1px solid var(--border);border-radius:6px;font-family:inherit;font-size:.85rem;min-height:68px;box-sizing:border-box;"></textarea>
                        <button class="btn btn-ink" type="submit" style="margin-top:8px;padding:8px 18px;font-size:.83rem;">Submit review</button>
                      </form>
                    </details>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <!-- ══ TAB: MANAGE PROFILE ══ -->
    <?php elseif ($activeTab === 'profile'): ?>
    <div class="acct-panel-header">
      <h2 class="acct-panel-title">Manage Profile</h2>
    </div>
    <div class="acct-panel-body">
      <?php if(isset($msg) && $msg): ?>
        <div class="auth-success" style="margin-bottom:18px;font-size:.85rem;padding:12px;"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

      <h3 style="font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted-fg);margin-bottom:20px;">Basic Information</h3>

      <form method="post">
        <input type="hidden" name="action" value="update_profile">
        <?php
          $nameParts = explode(' ', $me['full_name'], 2);
          $firstName = $nameParts[0] ?? '';
          $lastName  = $nameParts[1] ?? '';
        ?>
        <div class="profile-form-grid">
          <div>
            <span class="checkout-label">First Name</span>
            <input type="text" name="first_name" class="checkout-form-input" value="<?= htmlspecialchars($firstName) ?>" placeholder="First Name">
          </div>
          <div>
            <span class="checkout-label">Last Name</span>
            <input type="text" name="last_name" class="checkout-form-input" value="<?= htmlspecialchars($lastName) ?>" placeholder="Last Name">
          </div>
        </div>

        <div class="profile-form-grid">
          <div>
            <span class="checkout-label">Email</span>
            <input type="email" name="email" class="checkout-form-input" value="<?= htmlspecialchars($me['email']) ?>" placeholder="Email" style="background:var(--cream);" readonly>
          </div>
          <div>
            <span class="checkout-label">Phone Number</span>
            <div style="display:flex;gap:0;">
              <span style="display:flex;align-items:center;padding:0 12px;border:1px solid var(--border);border-right:none;border-radius:8px 0 0 8px;background:var(--cream);font-size:.88rem;color:var(--muted-fg);white-space:nowrap;">🇵🇭 +63</span>
              <input type="text" name="phone" class="checkout-form-input" value="<?= htmlspecialchars($me['phone'] ?? '') ?>" placeholder="9XX XXX XXXX" style="border-radius:0 8px 8px 0;">
            </div>
          </div>
        </div>

        <div style="margin-top:4px;padding-top:22px;border-top:1px solid var(--border);">
          <h3 style="font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted-fg);margin-bottom:18px;">Change Password</h3>
          <div class="profile-form-grid">
            <div>
              <span class="checkout-label">New Password</span>
              <input type="password" name="password" class="checkout-form-input" placeholder="Leave blank to keep current">
            </div>
            <div>
              <span class="checkout-label">Confirm Password</span>
              <input type="password" name="password2" class="checkout-form-input" placeholder="Repeat new password">
            </div>
          </div>
        </div>

        <div style="text-align:right;margin-top:8px;">
          <button type="submit" class="btn btn-ink" style="padding:11px 28px;">Update Information</button>
        </div>
      </form>
    </div>

    <?php endif; ?>

  </main><!-- /acct-panel -->
</div><!-- /acct-wrap -->

<script>
  function toggleEditPanel(id) {
    const panel = document.getElementById('edit-panel-' + id);
    if (panel) panel.classList.toggle('open');
  }
  function toggleAddPanel() {
    const panel = document.getElementById('add-addr-panel');
    if (panel) panel.classList.toggle('open');
  }
</script>

<?php require __DIR__.'/includes/footer.php'; ?>