<?php
// ============================================================
//  includes/settings.php
//  Global CMS settings helper — fetches from site_settings table
//  with in-request static cache. Safe fallback if table missing.
// ============================================================

require_once __DIR__.'/db.php';

function get_settings(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    try {
        $rows  = db()->query('SELECT `key`, `value` FROM site_settings')->fetchAll();
        $cache = array_column($rows, 'value', 'key');
    } catch (Throwable $e) {
        $cache = [];
    }
    return $cache;
}

/**
 * Get a single setting value with an optional fallback default.
 */
function setting(string $key, string $default = ''): string {
    $s = get_settings();
    return $s[$key] ?? $default;
}

/**
 * Outputs CSS custom-property overrides from the colors stored in DB.
 * Call once inside <head> after styles.css.
 */
function render_color_vars(): void {
    $map = [
        'color_cream'     => '--cream',
        'color_ink'       => '--ink',
        'color_sage_deep' => '--sage-deep',
        'color_sage'      => '--sage',
        'color_sage_lt'   => '--sage-lt',
        'color_muted_fg'  => '--muted-fg',
        'color_border'    => '--border',
        'color_card_bg'   => '--card-bg',
        'color_cream_dk'  => '--cream-dk',
    ];
    $s   = get_settings();
    $css = ':root{';
    foreach ($map as $key => $var) {
        if (!empty($s[$key])) {
            $css .= $var . ':' . htmlspecialchars($s[$key], ENT_QUOTES) . ';';
        }
    }
    $css .= '}';
    echo "<style id=\"cms-colors\">$css</style>\n";
}