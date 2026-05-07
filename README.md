# MISAKI HANDCRAFTED — v2 (PHP + MySQL/MariaDB)

A flower shop site with full admin CMS. Vanilla HTML/CSS/JS + PHP/PDO. Works on XAMPP (Apache + MariaDB).

## Quick start

1. Install **XAMPP** (PHP 7.4+, MariaDB/MySQL).
2. Copy this `misaki/` folder into `xampp/htdocs/`.
3. Start Apache + MySQL from the XAMPP control panel.
4. Open phpMyAdmin → **Import** → choose `sql/schema.sql` → Go.
   (creates the `misaki` database, tables, seed products & add-ons, default admin)
5. Open <http://localhost/misaki/>.

## Logins

- **Customer**: register on the site (`/register.php`).
- **Admin**: <http://localhost/misaki/admin/login.php> — `admin` / `admin123`
  (change in phpMyAdmin → `admin_user` table; use `password_hash('newpass', PASSWORD_BCRYPT)`).

## What's included

### Frontend (UI/UX)
- Scroll-to-top button (replaces previous chat FAB; only shows after scrolling).
- FAQ modal (replaces in-navbar chat assistant).
- Gallery click-to-zoom **lightbox** with prev/next + keyboard arrows.
- Smoother navbar transitions + animated underline on hover/active.
- Share button uses **clipboard fallback** (works on `localhost` where Web Share API is blocked).
- 100% responsive layouts.

### E-commerce
- **Cart with add-ons** — same product + different add-ons = separate line items (different qty totals).
- **Checkout interception** — anonymous browsing is fine, but `Checkout` redirects to `/login.php?next=checkout.php` until you sign in.
- Server-side order placement re-computes totals (never trusts client prices).
- **Customer reviews** — after a successful order, the customer goes to `/account.php` and submits a 1–5 star review per product. Reviews appear on the product page and the home page review section dynamically.

### Backend (PHP/PDO + MariaDB) — 3NF schema
Tables: `user`, `admin_user`, `product_type`, `product`, `addon`, `order`, `order_item`, `order_item_addon`, `review`. See `sql/schema.sql`.

Seed add-ons (per spec):
- Printed Photo  +₱5
- Acrylic Dedication  +₱5
- Fairy Light  +₱20
- Letter  +₱25

### Admin CMS — `/admin/`
- Products: create / edit / delete / show-or-hide on the storefront.
- Add-ons: create / edit / delete / activate.
- Orders: view all orders + items, change status (`pending` → `paid` → `fulfilled` / `cancelled`).

## File map
```
misaki/
├── index.php  shop.php  product.php  gallery.php  about.php  cart.php
├── login.php  register.php  logout.php  account.php  checkout.php
├── admin/         (full CMS)
│   ├── index.php  login.php  logout.php
│   ├── products.php  addons.php  orders.php
├── includes/
│   ├── db.php      auth.php   header.php   footer.php   products.php
├── css/styles.css   js/main.js   images/   sql/schema.sql
```

## Notes
- The site keeps the v1 visual design (cream palette, Cormorant + Shippori Mincho fonts, slow image hover, scroll reveal, page loader, navbar fade-from-transparent on home, etc.). All new pieces add on top, no design changes.
- The cart lives in `localStorage` while browsing. On checkout, it is sent to PHP, validated against DB prices, and persisted as an `order` + `order_item` + `order_item_addon` record.
