<?php
require_once __DIR__.'/auth.php';
require_once __DIR__.'/products.php';
require_once __DIR__.'/settings.php';

if(!isset($page))        $page        = '';
if(!isset($title))       $title       = setting('meta_og_title', 'Misaki Handcrafted — Floral Studio');
if(!isset($description)) $description = setting('meta_description', 'Handcrafted floral arrangements with quiet ritual.');

$me = current_user();

$brand_name = setting('brand_name', 'MISAKI');
$brand_jp   = setting('brand_jp',   'handcrafted · 美咲');
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($title) ?></title>
  <meta name="description" content="<?= htmlspecialchars($description) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Inter:wght@300;400;500&family=Shippori+Mincho:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
  <?php render_color_vars(); // inject dynamic CSS variables from DB ?>
</head>
<body data-page="<?= htmlspecialchars($page) ?>"<?= $me?' data-auth="1"':'' ?>>

<div class="page-loader"><div class="petal">美咲</div></div>

<header class="navbar">
  <div class="nav-inner">
    <a href="index.php" class="brand">
      <span class="brand-logo"><?= htmlspecialchars($brand_name) ?></span>
      <span class="brand-jp"><?= htmlspecialchars($brand_jp) ?></span>
    </a>
    <nav class="nav-links">
      <a href="index.php"   class="<?= $page==='home'?'active':'' ?>">Home</a>
      <a href="shop.php"    class="<?= $page==='shop'?'active':'' ?>">Shop</a>
      <a href="gallery.php" class="<?= $page==='gallery'?'active':'' ?>">Gallery</a>
      <a href="about.php"   class="<?= $page==='about'?'active':'' ?>">About</a>
    </nav>
    <div class="nav-actions">
      <button class="icon-btn open-faq" aria-label="FAQ" title="FAQ">
        <span data-icon="help"></span>
      </button>
      <?php if($me): ?>
        <a href="account.php" class="icon-btn" aria-label="Account">
          <span data-icon="user"></span>
        </a>
      <?php else: ?>
        <a href="login.php" class="icon-btn" aria-label="Sign in">
          <span data-icon="user"></span>
        </a>
      <?php endif; ?>
      <a href="cart.php" class="icon-btn cart-link" aria-label="Cart">
        <span data-icon="bag"></span>
        <span class="cart-badge" style="display:none">0</span>
      </a>
      <button class="icon-btn menu-btn" aria-label="Menu" aria-expanded="false">
        <span data-icon="menu"></span>
      </button>
    </div>
  </div>
  <div class="mobile-nav">
    <nav>
      <a href="index.php">Home</a>
      <a href="shop.php">Shop</a>
      <a href="gallery.php">Gallery</a>
      <a href="about.php">About</a>
      <a href="#" class="open-faq">FAQ</a>
      <?php if($me): ?>
        <a href="account.php">Account</a>
        <a href="logout.php">Sign out</a>
      <?php else: ?>
        <a href="login.php">Sign in</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main>