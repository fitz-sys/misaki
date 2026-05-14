<?php
// admin/settings.php — loaded by admin/index.php
// Handles saving all site_settings rows

require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/settings.php';

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_settings') {
    try {
        $pdo  = db();
        $stmt = $pdo->prepare('INSERT INTO site_settings (`key`, `value`, `label`, `group`, `type`, `sort_order`)
                               VALUES (?, ?, ?, ?, ?, ?)
                               ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)');

        foreach ($_POST['settings'] as $key => $value) {
            // Fetch meta to preserve label/group/type/sort — only value changes
            $meta = $pdo->prepare('SELECT `label`,`group`,`type`,`sort_order` FROM site_settings WHERE `key`=?');
            $meta->execute([$key]);
            $row  = $meta->fetch();
            if ($row) {
                $stmt->execute([
                    $key,
                    trim($value),
                    $row['label'],
                    $row['group'],
                    $row['type'],
                    $row['sort_order'],
                ]);
            }
        }
        $msg = 'Settings saved successfully.';
        // Bust the static cache so next render_color_vars() call picks up fresh data
        // (the page reload after redirect will do this naturally)
        header('Location: ?tab=settings&saved=1');
        exit;
    } catch (Throwable $e) {
        $err = 'Save failed: ' . $e->getMessage();
    }
}

if (isset($_GET['saved'])) $msg = 'Settings saved successfully.';

// Load all settings grouped
$pdo  = db();
$rows = $pdo->query('SELECT * FROM site_settings ORDER BY `group`, sort_order')->fetchAll();

$groups = [];
foreach ($rows as $r) {
    $groups[$r['group']][] = $r;
}

$groupLabels = [
    'branding' => 'Branding & Text',
    'colors'   => 'Brand Colors',
    'contact'  => 'Contact & Social',
    'homepage' => 'Homepage Content',
    'pages'    => 'Page Content',
    'footer'   => 'Footer',
    'seo'      => 'SEO / Meta',
];

$activeGroup = $_GET['sg'] ?? array_key_first($groups);
if (!isset($groups[$activeGroup])) $activeGroup = array_key_first($groups);
?>

<?php if ($msg): ?>
  <div class="adm-flash success">✓ <?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if ($err): ?>
  <div class="adm-flash error">✗ <?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<div style="margin-bottom:24px">
  <p style="font-size:.875rem;color:var(--adm-muted);max-width:580px">
    All text, colors, and contact details shown on the public storefront are controlled here.
    Changes take effect immediately after saving.
  </p>
</div>

<!-- Group tabs -->
<div class="adm-stabs">
  <?php foreach ($groupLabels as $gkey => $glabel): ?>
    <?php if (!isset($groups[$gkey])) continue; ?>
    <button
      class="adm-stab <?= $activeGroup === $gkey ? 'active' : '' ?>"
      onclick="switchGroup('<?= $gkey ?>')"
      data-group="<?= $gkey ?>">
      <?= htmlspecialchars($glabel) ?>
    </button>
  <?php endforeach; ?>
</div>

<form method="post">
  <input type="hidden" name="action" value="save_settings">

  <?php foreach ($groups as $gkey => $fields): ?>
    <div class="adm-settings-group adm-card" id="sgrp-<?= $gkey ?>" style="<?= $activeGroup !== $gkey ? 'display:none' : '' ?>">
      <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;font-weight:400;margin-bottom:20px;color:var(--adm-ink)">
        <?= htmlspecialchars($groupLabels[$gkey] ?? ucfirst($gkey)) ?>
      </h3>

      <?php if ($gkey === 'colors'): ?>
        <!-- Color group: special 2-col with swatch preview -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <?php foreach ($fields as $f): ?>
            <label class="adm-label" style="gap:8px">
              <?= htmlspecialchars($f['label']) ?>
              <div class="color-row">
                <input
                  type="color"
                  name="settings[<?= htmlspecialchars($f['key']) ?>]"
                  value="<?= htmlspecialchars($f['value']) ?>"
                  oninput="updateSwatch('<?= $f['key'] ?>', this.value)"
                  style="width:100%;height:42px;padding:3px 6px;border-radius:var(--radius);border:1px solid var(--adm-border);">
              </div>
              <div style="display:flex;align-items:center;gap:8px;margin-top:4px">
                <div class="color-swatch" id="sw-<?= $f['key'] ?>" style="background:<?= htmlspecialchars($f['value']) ?>"></div>
                <span style="font-size:.72rem;color:var(--adm-muted);text-transform:none;letter-spacing:0" id="sw-val-<?= $f['key'] ?>"><?= htmlspecialchars($f['value']) ?></span>
              </div>
            </label>
          <?php endforeach; ?>
        </div>

        <!-- Live preview strip -->
        <div style="margin-top:24px;padding:20px;border-radius:var(--radius-lg);border:1px solid var(--adm-border);background:var(--adm-cream)">
          <div style="font-size:.65rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--adm-muted);margin-bottom:12px">Live Preview</div>
          <div id="color-preview-strip" style="display:flex;gap:8px;flex-wrap:wrap"></div>
          <div id="color-preview-text" style="margin-top:16px;padding:16px;border-radius:8px;font-family:'Cormorant Garamond',serif;font-size:1.1rem">
            Handcrafted floral studio — <span style="font-size:.8rem;font-family:Inter,sans-serif">preview of your palette</span>
          </div>
        </div>

      <?php else: ?>
        <!-- Default group: list of fields -->
        <div class="adm-form">
          <?php foreach ($fields as $f): ?>
            <label class="adm-label <?= $f['type'] === 'textarea' ? 'span2' : '' ?>">
              <?= htmlspecialchars($f['label']) ?>
              <?php if ($f['type'] === 'textarea'): ?>
                <textarea name="settings[<?= htmlspecialchars($f['key']) ?>]"
                          rows="3"><?= htmlspecialchars($f['value']) ?></textarea>
              <?php else: ?>
                <input
                  type="<?= htmlspecialchars($f['type']) ?>"
                  name="settings[<?= htmlspecialchars($f['key']) ?>]"
                  value="<?= htmlspecialchars($f['value']) ?>">
              <?php endif; ?>
            </label>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>

  <div style="margin-top:24px;display:flex;gap:12px;align-items:center">
    <button type="submit" class="adm-btn adm-btn-primary" style="padding:11px 28px;font-size:.85rem">
      ✓ Save All Settings
    </button>
    <span style="font-size:.75rem;color:var(--adm-muted)">Changes apply immediately to the storefront.</span>
  </div>
</form>

<script>
  // ── Tab switching ──────────────────────────────────────
  function switchGroup(key) {
    document.querySelectorAll('.adm-settings-group').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.adm-stab').forEach(el => el.classList.remove('active'));
    const grp = document.getElementById('sgrp-' + key);
    if (grp) grp.style.display = '';
    const btn = document.querySelector('[data-group="' + key + '"]');
    if (btn) btn.classList.add('active');
    // Update URL without reload
    const url = new URL(window.location);
    url.searchParams.set('sg', key);
    history.replaceState({}, '', url);
  }

  // ── Color swatch live update ───────────────────────────
  function updateSwatch(key, val) {
    const sw  = document.getElementById('sw-' + key);
    const txt = document.getElementById('sw-val-' + key);
    if (sw)  sw.style.background = val;
    if (txt) txt.textContent = val;
    // Also update CSS variable live on this admin page for preview
    const varMap = {
      'color_cream':     '--adm-preview-cream',
      'color_ink':       '--adm-preview-ink',
      'color_sage_deep': '--adm-preview-sage',
    };
    buildPreview();
  }

  function buildPreview() {
    const strip = document.getElementById('color-preview-strip');
    const text  = document.getElementById('color-preview-text');
    if (!strip) return;

    const inputs = document.querySelectorAll('#sgrp-colors input[type="color"]');
    strip.innerHTML = '';

    let cream = '#f7f2ea', ink = '#1c1917', sage = '#3d5a3e';

    inputs.forEach(inp => {
      const key = inp.name.match(/\[([^\]]+)\]/)[1];
      const val = inp.value;
      const div = document.createElement('div');
      div.style.cssText = `width:36px;height:36px;border-radius:6px;background:${val};border:1px solid rgba(0,0,0,.1);title="${key}"`;
      div.title = key + ': ' + val;
      strip.appendChild(div);
      if (key === 'color_cream')     cream = val;
      if (key === 'color_ink')       ink   = val;
      if (key === 'color_sage_deep') sage  = val;
    });

    if (text) {
      text.style.background = cream;
      text.style.color       = ink;
      text.querySelector('span').style.color = sage;
    }
  }

  // Build on page load
  document.addEventListener('DOMContentLoaded', buildPreview);
</script>