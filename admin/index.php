<?php
require_once __DIR__.'/../includes/auth.php';
if(!current_admin_id()){ header('Location: login.php'); exit; }

require_once __DIR__.'/../includes/settings.php';

$tab = $_GET['tab'] ?? 'dashboard';
$validTabs = ['dashboard','products','addons','orders','settings'];
if(!in_array($tab, $validTabs)) $tab = 'dashboard';

// ── Nav items ────────────────────────────────────────────────
$nav = [
  'dashboard' => ['icon' => '◈', 'label' => 'Dashboard'],
  'products'  => ['icon' => '❁', 'label' => 'Products'],
  'addons'    => ['icon' => '✦', 'label' => 'Add-ons'],
  'orders'    => ['icon' => '◎', 'label' => 'Orders'],
  'settings'  => ['icon' => '⚙', 'label' => 'Site Settings'],
];

$brand = setting('brand_name', 'MISAKI');
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($nav[$tab]['label']) ?> — <?= htmlspecialchars($brand) ?> Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/styles.css">
  <style>
    /* ── Admin shell reset ───────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --sidebar-w:    240px;
      --sidebar-bg:   #17211a;
      --sidebar-bdr:  rgba(255,255,255,.06);
      --sidebar-hover:#1f2e24;
      --sidebar-act:  #2a3d2e;
      --sidebar-txt:  rgba(247,242,234,.55);
      --sidebar-atxt: #c4d9c4;
      --topbar-h:     60px;
      --adm-cream:    #f7f3ee;
      --adm-white:    #ffffff;
      --adm-border:   #e5dfd7;
      --adm-card:     #ffffff;
      --adm-muted:    #78716c;
      --adm-ink:      #1c1917;
      --adm-sage:     #3d5a3e;
      --adm-sage-lt:  #c4d9c4;
      --adm-error:    #b91c1c;
      --adm-success:  #166534;
      --radius:       6px;
      --radius-lg:    12px;
      --t:            0.2s ease;
    }

    body {
      font-family: 'Inter', system-ui, sans-serif;
      background:  var(--adm-cream);
      color:       var(--adm-ink);
      min-height:  100vh;
      display:     flex;
      -webkit-font-smoothing: antialiased;
    }

    /* ── Sidebar ─────────────────────────────────────── */
    .adm-sidebar {
      position:   fixed;
      top:        0; left: 0; bottom: 0;
      width:      var(--sidebar-w);
      background: var(--sidebar-bg);
      display:    flex;
      flex-direction: column;
      z-index:    200;
      transition: transform var(--t);
    }

    .adm-sidebar-brand {
      padding:     28px 24px 20px;
      border-bottom: 1px solid var(--sidebar-bdr);
      flex-shrink: 0;
    }

    .adm-sidebar-brand .name {
      font-family:    'Cormorant Garamond', serif;
      font-size:      1.25rem;
      font-weight:    500;
      letter-spacing: .24em;
      color:          #e8e0d4;
      line-height:    1;
    }

    .adm-sidebar-brand .sub {
      font-size:      .6rem;
      letter-spacing: .18em;
      text-transform: uppercase;
      color:          var(--sidebar-txt);
      margin-top:     4px;
    }

    .adm-sidebar-nav {
      flex:       1;
      padding:    16px 12px;
      overflow-y: auto;
      scrollbar-width: none;
    }
    .adm-sidebar-nav::-webkit-scrollbar { display: none; }

    .adm-nav-group {
      margin-bottom: 4px;
    }

    .adm-nav-label {
      font-size:      .6rem;
      font-weight:    600;
      letter-spacing: .14em;
      text-transform: uppercase;
      color:          rgba(255,255,255,.2);
      padding:        8px 12px 6px;
      display:        block;
    }

    .adm-nav-link {
      display:      flex;
      align-items:  center;
      gap:          10px;
      padding:      9px 12px;
      border-radius:var(--radius);
      color:        var(--sidebar-txt);
      font-size:    .82rem;
      font-weight:  400;
      text-decoration: none;
      transition:   background var(--t), color var(--t);
      margin-bottom: 2px;
    }

    .adm-nav-link:hover {
      background: var(--sidebar-hover);
      color:      #e8e0d4;
    }

    .adm-nav-link.active {
      background: var(--sidebar-act);
      color:      var(--sidebar-atxt);
      font-weight: 500;
    }

    .adm-nav-icon {
      font-size: 1rem;
      width:     20px;
      text-align: center;
      flex-shrink: 0;
      opacity:    .8;
    }

    .adm-nav-link.active .adm-nav-icon { opacity: 1; }

    .adm-sidebar-footer {
      padding:      16px 12px;
      border-top:   1px solid var(--sidebar-bdr);
      flex-shrink:  0;
    }

    .adm-sidebar-footer a,
    .adm-sidebar-footer span {
      display:      flex;
      align-items:  center;
      gap:          10px;
      padding:      8px 12px;
      border-radius:var(--radius);
      color:        var(--sidebar-txt);
      font-size:    .78rem;
      text-decoration: none;
      transition:   background var(--t), color var(--t);
      margin-bottom:2px;
    }
    .adm-sidebar-footer a:hover { background: var(--sidebar-hover); color: #e8e0d4; }

    .adm-sidebar-footer .signout {
      color:  #e08080;
      cursor: pointer;
    }
    .adm-sidebar-footer .signout:hover { background: rgba(176,40,40,.15); color: #f87171; }

    /* ── Main content area ───────────────────────────── */
    .adm-main {
      margin-left:  var(--sidebar-w);
      flex:         1;
      min-width:    0;
      display:      flex;
      flex-direction: column;
      min-height:   100vh;
    }

    /* ── Top bar ─────────────────────────────────────── */
    .adm-topbar {
      position:     sticky;
      top:          0;
      z-index:      100;
      height:       var(--topbar-h);
      background:   rgba(247,243,238,.92);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border-bottom:1px solid var(--adm-border);
      display:      flex;
      align-items:  center;
      padding:      0 32px;
      gap:          16px;
    }

    .adm-topbar h1 {
      font-family:  'Cormorant Garamond', serif;
      font-size:    1.35rem;
      font-weight:  400;
      color:        var(--adm-ink);
      flex:         1;
    }

    .adm-topbar-actions { display: flex; align-items: center; gap: 12px; }

    .adm-hamburger {
      display:      none;
      flex-direction: column;
      gap:          5px;
      background:   none;
      border:       none;
      cursor:       pointer;
      padding:      6px;
    }
    .adm-hamburger span {
      display:    block;
      width:      20px; height: 1.5px;
      background: var(--adm-ink);
      transition: var(--t);
    }

    /* ── Content ─────────────────────────────────────── */
    .adm-content {
      padding: 32px;
      flex:    1;
    }

    /* ── Cards ───────────────────────────────────────── */
    .adm-card {
      background:    var(--adm-card);
      border:        1px solid var(--adm-border);
      border-radius: var(--radius-lg);
      padding:       24px;
      margin-bottom: 20px;
      box-shadow:    0 1px 3px rgba(0,0,0,.04);
    }

    /* ── Stat cards ──────────────────────────────────── */
    .adm-stats {
      display:               grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap:                   16px;
      margin-bottom:         28px;
    }

    .adm-stat {
      background:    var(--adm-card);
      border:        1px solid var(--adm-border);
      border-radius: var(--radius-lg);
      padding:       20px 24px;
      box-shadow:    0 1px 3px rgba(0,0,0,.04);
    }

    .adm-stat-label {
      font-size:      .65rem;
      font-weight:    600;
      letter-spacing: .12em;
      text-transform: uppercase;
      color:          var(--adm-muted);
    }

    .adm-stat-value {
      font-family: 'Cormorant Garamond', serif;
      font-size:   2rem;
      font-weight: 400;
      color:       var(--adm-ink);
      line-height: 1.1;
      margin-top:  6px;
    }

    .adm-stat-sub {
      font-size:  .72rem;
      color:      var(--adm-muted);
      margin-top: 4px;
    }

    /* ── Section heading ─────────────────────────────── */
    .adm-section-head {
      display:       flex;
      align-items:   baseline;
      gap:           12px;
      margin-bottom: 20px;
    }

    .adm-section-head h2 {
      font-family: 'Cormorant Garamond', serif;
      font-size:   1.6rem;
      font-weight: 400;
      color:       var(--adm-ink);
    }

    .adm-section-head .count {
      font-size:  .72rem;
      color:      var(--adm-muted);
      background: var(--adm-cream);
      padding:    2px 8px;
      border-radius: 99px;
      border:     1px solid var(--adm-border);
    }

    /* ── Flash messages ──────────────────────────────── */
    .adm-flash {
      padding:       12px 16px;
      border-radius: var(--radius);
      font-size:     .85rem;
      margin-bottom: 20px;
      display:       flex;
      align-items:   center;
      gap:           10px;
    }
    .adm-flash.success { background: #f0fdf4; border: 1px solid #86efac; color: var(--adm-success); }
    .adm-flash.error   { background: #fef2f2; border: 1px solid #fca5a5; color: var(--adm-error); }

    /* ── Table ───────────────────────────────────────── */
    .adm-table-wrap { overflow-x: auto; }

    .adm-table {
      width:           100%;
      border-collapse: collapse;
      font-size:       .84rem;
    }

    .adm-table thead { background: var(--adm-cream); }

    .adm-table th {
      text-align:     left;
      padding:        10px 14px;
      font-size:      .62rem;
      font-weight:    600;
      letter-spacing: .12em;
      text-transform: uppercase;
      color:          var(--adm-muted);
      white-space:    nowrap;
      border-bottom:  1px solid var(--adm-border);
    }

    .adm-table td {
      padding:        11px 14px;
      border-bottom:  1px solid var(--adm-border);
      vertical-align: middle;
    }

    .adm-table tbody tr:last-child td { border-bottom: none; }
    .adm-table tbody tr:hover { background: #faf8f5; }

    /* ── Pills ───────────────────────────────────────── */
    .pill {
      display:        inline-block;
      font-size:      .65rem;
      font-weight:    600;
      letter-spacing: .06em;
      text-transform: uppercase;
      padding:        3px 8px;
      border-radius:  99px;
    }
    .pill.on       { background: #dcfce7; color: #166534; }
    .pill.off      { background: #f1f5f9; color: #64748b; }
    .pill.pending  { background: #fef9c3; color: #854d0e; }
    .pill.paid     { background: #dcfce7; color: #166534; }
    .pill.fulfilled{ background: #dbeafe; color: #1e40af; }
    .pill.cancelled{ background: #fee2e2; color: #991b1b; }

    /* ── Forms ───────────────────────────────────────── */
    .adm-form {
      display:               grid;
      grid-template-columns: 1fr 1fr;
      gap:                   16px;
    }
    .adm-form .span2 { grid-column: 1 / -1; }

    .adm-label {
      display:        flex;
      flex-direction: column;
      gap:            6px;
      font-size:      .68rem;
      font-weight:    600;
      letter-spacing: .1em;
      text-transform: uppercase;
      color:          var(--adm-muted);
    }

    .adm-label input,
    .adm-label select,
    .adm-label textarea {
      font-family:   'Inter', sans-serif;
      font-size:     .875rem;
      font-weight:   400;
      color:         var(--adm-ink);
      border:        1px solid var(--adm-border);
      border-radius: var(--radius);
      padding:       9px 12px;
      background:    var(--adm-white);
      width:         100%;
      outline:       none;
      transition:    border-color var(--t), box-shadow var(--t);
      appearance:    none;
    }

    .adm-label input[type="color"] {
      padding: 4px 6px;
      height:  40px;
      cursor:  pointer;
    }

    .adm-label input:focus,
    .adm-label select:focus,
    .adm-label textarea:focus {
      border-color: var(--adm-sage);
      box-shadow:   0 0 0 3px rgba(61,90,62,.1);
    }

    .adm-label textarea { resize: vertical; min-height: 80px; }

    .adm-label.checkbox-label {
      flex-direction: row;
      align-items:    center;
      gap:            8px;
      cursor:         pointer;
    }
    .adm-label.checkbox-label input {
      width:        auto;
      flex-shrink:  0;
      padding:      0;
      border:       none;
      box-shadow:   none;
      accent-color: var(--adm-sage);
      cursor:       pointer;
    }

    /* ── Buttons ─────────────────────────────────────── */
    .adm-btn {
      display:        inline-flex;
      align-items:    center;
      gap:            6px;
      font-family:    'Inter', sans-serif;
      font-size:      .78rem;
      font-weight:    500;
      letter-spacing: .04em;
      padding:        9px 18px;
      border-radius:  var(--radius);
      border:         none;
      cursor:         pointer;
      transition:     background var(--t), opacity var(--t);
      text-decoration: none;
    }
    .adm-btn-primary  { background: #17211a; color: #e8e0d4; }
    .adm-btn-primary:hover  { background: #1f2e24; }
    .adm-btn-outline  { background: transparent; color: var(--adm-ink); border: 1px solid var(--adm-border); }
    .adm-btn-outline:hover  { background: var(--adm-cream); }
    .adm-btn-danger   { background: transparent; color: var(--adm-error); font-size: .73rem; text-decoration: underline; }
    .adm-btn-danger:hover   { opacity: .7; }

    /* ── Collapsible panel ───────────────────────────── */
    .adm-expand summary {
      cursor:         pointer;
      list-style:     none;
      font-size:      .78rem;
      font-weight:    500;
      color:          var(--adm-sage);
      letter-spacing: .04em;
    }
    .adm-expand summary::-webkit-details-marker { display: none; }
    .adm-expand summary:hover { opacity: .75; }

    /* ── Settings tabs ───────────────────────────────── */
    .adm-stabs {
      display:       flex;
      gap:           4px;
      border-bottom: 2px solid var(--adm-border);
      margin-bottom: 28px;
      overflow-x:    auto;
      scrollbar-width: none;
    }
    .adm-stabs::-webkit-scrollbar { display: none; }

    .adm-stab {
      padding:        9px 18px;
      font-size:      .72rem;
      font-weight:    500;
      letter-spacing: .1em;
      text-transform: uppercase;
      color:          var(--adm-muted);
      border:         none;
      background:     none;
      cursor:         pointer;
      border-bottom:  2px solid transparent;
      margin-bottom:  -2px;
      white-space:    nowrap;
      transition:     color var(--t), border-color var(--t);
    }
    .adm-stab:hover  { color: var(--adm-ink); }
    .adm-stab.active { color: var(--adm-sage); border-bottom-color: var(--adm-sage); }

    /* ── Color swatch preview ────────────────────────── */
    .color-row {
      display:       grid;
      grid-template-columns: 1fr auto;
      align-items:   center;
      gap:           12px;
    }
    .color-swatch {
      width:         32px; height: 32px;
      border-radius: var(--radius);
      border:        1px solid var(--adm-border);
      flex-shrink:   0;
    }

    /* ── Overlay for mobile sidebar ──────────────────── */
    .adm-overlay {
      display:    none;
      position:   fixed;
      inset:      0;
      background: rgba(0,0,0,.45);
      z-index:    190;
    }

    /* ── Responsive ──────────────────────────────────── */
    @media (max-width: 900px) {
      .adm-sidebar {
        transform: translateX(-100%);
      }
      .adm-sidebar.open {
        transform: translateX(0);
      }
      .adm-overlay.open { display: block; }
      .adm-main         { margin-left: 0; }
      .adm-hamburger    { display: flex; }
      .adm-content      { padding: 20px 16px; }
      .adm-form         { grid-template-columns: 1fr; }
      .adm-topbar       { padding: 0 16px; }
    }

    @media (max-width: 480px) {
      .adm-stats { grid-template-columns: 1fr 1fr; }
    }
  </style>
</head>
<body>

<!-- Sidebar overlay (mobile) -->
<div class="adm-overlay" id="adm-overlay"></div>

<!-- ── Sidebar ─────────────────────────────────────────── -->
<aside class="adm-sidebar" id="adm-sidebar">
  <div class="adm-sidebar-brand">
    <div class="name"><?= htmlspecialchars($brand) ?></div>
    <div class="sub">Admin Panel</div>
  </div>

  <nav class="adm-sidebar-nav">
    <span class="adm-nav-label">Main</span>
    <?php foreach($nav as $key => $item): ?>
      <?php if($key === 'settings'): ?>
        <span class="adm-nav-label" style="margin-top:12px">System</span>
      <?php endif; ?>
      <a href="?tab=<?= $key ?>"
         class="adm-nav-link <?= $tab === $key ? 'active' : '' ?>">
        <span class="adm-nav-icon"><?= $item['icon'] ?></span>
        <?= htmlspecialchars($item['label']) ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="adm-sidebar-footer">
    <a href="../index.php" target="_blank">
      <span>↗</span> View site
    </a>
    <a href="logout.php" class="signout">
      <span>⏻</span> Sign out
    </a>
  </div>
</aside>

<!-- ── Main ───────────────────────────────────────────── -->
<div class="adm-main">

  <!-- Top bar -->
  <div class="adm-topbar">
    <button class="adm-hamburger" id="adm-hamburger" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </button>
    <h1><?= htmlspecialchars($nav[$tab]['label']) ?></h1>
    <div class="adm-topbar-actions">
      <a href="../index.php" target="_blank" class="adm-btn adm-btn-outline" style="font-size:.72rem;padding:7px 14px;">
        ↗ View site
      </a>
    </div>
  </div>

  <!-- Content -->
  <div class="adm-content">
    <?php
    if     ($tab === 'dashboard') require __DIR__.'/dashboard.php';
    elseif ($tab === 'products')  require __DIR__.'/products.php';
    elseif ($tab === 'addons')    require __DIR__.'/addons.php';
    elseif ($tab === 'orders')    require __DIR__.'/orders.php';
    elseif ($tab === 'settings')  require __DIR__.'/settings.php';
    ?>
  </div>
</div>

<script>
  const sidebar  = document.getElementById('adm-sidebar');
  const overlay  = document.getElementById('adm-overlay');
  const hamburger= document.getElementById('adm-hamburger');

  function openSidebar()  { sidebar.classList.add('open'); overlay.classList.add('open'); }
  function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('open'); }

  hamburger.addEventListener('click', () => {
    sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
  });
  overlay.addEventListener('click', closeSidebar);
</script>
</body>
</html>