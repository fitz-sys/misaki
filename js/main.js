/* ============================================================
   MISAKI HANDCRAFTED — main.js
   Vanilla JS · No dependencies · ES6+
   ============================================================ */

'use strict';

/* ============================================================
   0. INLINE SVG ICON SYSTEM
   Usage: <span data-icon="name"></span>
   ============================================================ */
const ICONS = {
  arrow:  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>`,
  left:   `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>`,
  right:  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>`,
  up:     `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>`,
  user:   `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.582-7 8-7s8 3 8 7"/></svg>`,
  bag:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>`,
  menu:   `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>`,
  x:      `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`,
  eye:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`,
  plus:   `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>`,
  minus:  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>`,
  share:  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>`,
  help:   `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
  zoom:   `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>`,
};

function renderIcons() {
  document.querySelectorAll('[data-icon]').forEach(el => {
    const name = el.getAttribute('data-icon');
    if (ICONS[name]) el.innerHTML = ICONS[name];
  });
}

/* ============================================================
   1. PAGE LOADER
   ============================================================ */
function initPageLoader() {
  const loader = document.querySelector('.page-loader');
  if (!loader) return;

  const done = () => {
    loader.classList.add('out');
    setTimeout(() => loader.remove(), 700);
  };

  if (document.readyState === 'complete') {
    done();
  } else {
    window.addEventListener('load', done);
    // Failsafe: remove after 3s regardless
    setTimeout(done, 3000);
  }
}

/* ============================================================
   2. NAVBAR
   Transparent → solid on scroll when on homepage hero
   Mobile hamburger menu toggle
   ============================================================ */
function initNavbar() {
  const navbar    = document.querySelector('.navbar');
  const menuBtn   = document.querySelector('.menu-btn');
  const mobileNav = document.querySelector('.mobile-nav');
  if (!navbar) return;

  const isHome = document.body.getAttribute('data-page') === 'home';

  if (isHome) navbar.classList.add('hero-mode');

  /* Scroll handler — throttled */
  let scrollTicking = false;
  function onScroll() {
    if (scrollTicking) return;
    scrollTicking = true;
    requestAnimationFrame(() => {
      if (isHome) {
        navbar.classList.toggle('scrolled', window.scrollY > 64);
      }
      scrollTicking = false;
    });
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll(); // run once on load

  /* Mobile menu */
  if (menuBtn && mobileNav) {
    menuBtn.addEventListener('click', () => {
      const isOpen = mobileNav.classList.toggle('open');
      menuBtn.setAttribute('aria-expanded', String(isOpen));
      document.body.classList.toggle('menu-open', isOpen);
    });

    /* Close on outside click */
    document.addEventListener('click', e => {
      if (!navbar.contains(e.target)) {
        mobileNav.classList.remove('open');
        menuBtn.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('menu-open');
      }
    });

    /* Close on Escape */
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && mobileNav.classList.contains('open')) {
        mobileNav.classList.remove('open');
        menuBtn.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('menu-open');
        menuBtn.focus();
      }
    });

    /* Close on mobile nav link click */
    mobileNav.querySelectorAll('a').forEach(a => {
      a.addEventListener('click', () => {
        mobileNav.classList.remove('open');
        menuBtn.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('menu-open');
      });
    });
  }
}

/* ============================================================
   3. SCROLL TO TOP BUTTON
   Shows after user scrolls 300px down
   ============================================================ */
function initScrollTop() {
  const btn = document.querySelector('.scroll-top');
  if (!btn) return;

  let ticking = false;
  window.addEventListener('scroll', () => {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(() => {
      btn.classList.toggle('visible', window.scrollY > 300);
      ticking = false;
    });
  }, { passive: true });

  btn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

/* ============================================================
   4. SCROLL REVEAL  (IntersectionObserver)
   Elements with class .reveal fade + slide up when visible
   ============================================================ */
function initReveal() {
  const els = document.querySelectorAll('.reveal');
  if (!els.length) return;

  if (!('IntersectionObserver' in window)) {
    // Fallback for old browsers
    els.forEach(el => el.classList.add('in'));
    return;
  }

  const obs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('in');
        obs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

  els.forEach(el => obs.observe(el));
}

/* ============================================================
   5. TOAST NOTIFICATION
   ============================================================ */
let _toastTimer = null;

function showToast(msg, duration = 2200) {
  let toast = document.querySelector('.misaki-toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.className = 'misaki-toast';
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');
    document.body.appendChild(toast);
  }
  toast.textContent = msg;
  toast.classList.add('show');
  clearTimeout(_toastTimer);
  _toastTimer = setTimeout(() => toast.classList.remove('show'), duration);
}

/* ============================================================
   6. SHARE LINK
   Uses Web Share API when available on HTTPS.
   Falls back to clipboard copy on localhost / HTTP.
   ============================================================ */
function shareLink(url) {
  // CHANGED: Use URL constructor to properly resolve relative paths based on current location
  const fullUrl = new URL(url, window.location.href).href;

  const tryClipboard = () => {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(fullUrl)
        .then(() => showToast('Link copied to clipboard ✓'))
        .catch(() => legacyCopy(fullUrl));
    } else {
      legacyCopy(fullUrl);
    }
  };

  // Web Share API only reliable on HTTPS (not localhost)
  if (navigator.share && location.protocol === 'https:') {
    navigator.share({ url: fullUrl })
      .catch(err => {
        // User cancelled — do nothing; other errors → clipboard
        if (err.name !== 'AbortError') tryClipboard();
      });
  } else {
    // localhost / HTTP → always clipboard
    tryClipboard();
  }
}

function legacyCopy(text) {
  const ta = document.createElement('textarea');
  ta.value = text;
  ta.style.cssText = 'position:fixed;top:-9999px;left:-9999px;opacity:0';
  document.body.appendChild(ta);
  ta.focus();
  ta.select();
  try {
    document.execCommand('copy');
    showToast('Link copied to clipboard ✓');
  } catch {
    showToast('Copy this link: ' + text, 4000);
  }
  ta.remove();
}

/* ============================================================
   7. CART  — localStorage-backed, addon-aware line items
   Two items are the SAME line only if product ID AND
   sorted addon IDs are identical. Otherwise → new line.
   ============================================================ */
const CART_KEY = 'misaki_cart';

function getCart() {
  try {
    return JSON.parse(localStorage.getItem(CART_KEY) || '[]');
  } catch {
    return [];
  }
}

function saveCart(cart) {
  localStorage.setItem(CART_KEY, JSON.stringify(cart));
}

/**
 * Unique line key: product slug + sorted addon IDs
 * e.g. "lorem-blush__1_3" means product + addons 1 and 3
 */
function makeLineKey(productId, addonIds) {
  const sorted = [...addonIds].map(Number).sort((a, b) => a - b);
  return `${productId}__${sorted.join('_') || 'none'}`;
}

function addToCart(product, qty, addonIds) {
  const addons  = (window.MISAKI_ADDONS || [])
    .filter(a => addonIds.map(Number).includes(Number(a.id)));
  const key     = makeLineKey(product.id, addonIds);
  const cart    = getCart();
  const idx     = cart.findIndex(l => l.key === key);

  if (idx > -1) {
    cart[idx].qty += qty;
  } else {
    cart.push({
      key,
      id:     product.id,
      slug:   product.slug,
      name:   product.name,
      image:  product.image,
      price:  Number(product.price),
      addons,
      qty,
    });
  }

  saveCart(cart);
  updateCartBadge();
  showToast(`${product.name} added to cart ✓`);
}

function removeFromCart(key) {
  saveCart(getCart().filter(l => l.key !== key));
  updateCartBadge();
}

function setLineQty(key, qty) {
  const cart = getCart();
  const idx  = cart.findIndex(l => l.key === key);
  if (idx < 0) return;
  if (qty < 1) {
    cart.splice(idx, 1);
  } else {
    cart[idx].qty = qty;
  }
  saveCart(cart);
}

function deltaLineQty(key, delta) {
  const cart = getCart();
  const idx  = cart.findIndex(l => l.key === key);
  if (idx < 0) return;
  const next = cart[idx].qty + delta;
  if (next < 1) {
    cart.splice(idx, 1);
  } else {
    cart[idx].qty = next;
  }
  saveCart(cart);
}

function lineAddonTotal(line) {
  return (line.addons || []).reduce((s, a) => s + Number(a.price), 0);
}

function lineTotal(line) {
  return (Number(line.price) + lineAddonTotal(line)) * line.qty;
}

function cartGrandTotal() {
  return getCart().reduce((s, l) => s + lineTotal(l), 0);
}

function cartCount() {
  return getCart().reduce((s, l) => s + l.qty, 0);
}

function updateCartBadge() {
  const count = cartCount();
  document.querySelectorAll('.cart-badge').forEach(b => {
    b.textContent = count;
    b.style.display = count > 0 ? 'flex' : 'none';
  });
}

/* ============================================================
   8. MODAL SYSTEM
   All modals share .modal / .modal-bg / .close conventions
   ============================================================ */
function openModal(modal) {
  if (!modal) return;
  modal.classList.add('open');
  document.body.classList.add('modal-open');
  // Focus first focusable element
  const first = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
  if (first) setTimeout(() => first.focus(), 80);
}

function closeModal(modal) {
  if (!modal) return;
  modal.classList.remove('open');
  // Only remove body lock if no other modals open
  if (!document.querySelector('.modal.open, .lightbox.open')) {
    document.body.classList.remove('modal-open');
  }
}

function closeAllModals() {
  document.querySelectorAll('.modal.open').forEach(closeModal);
  closeLightbox();
}

function initModals() {
  document.querySelectorAll('.modal').forEach(modal => {
    modal.querySelector('.modal-bg')?.addEventListener('click', () => closeModal(modal));
    modal.querySelector('.close')?.addEventListener('click',    () => closeModal(modal));
  });

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeAllModals();
  });
}

/* ============================================================
   9. FAQ MODAL
   ============================================================ */
function initFAQ() {
  const modal = document.getElementById('faqModal');
  if (!modal) return;

  document.querySelectorAll('.open-faq').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      openModal(modal);
    });
  });
}

/* ============================================================
   10. QUICK VIEW MODAL
   ============================================================ */
let _qvProduct = null;
let _qvQty     = 1;

function openQuickView(product) {
  const modal = document.getElementById('quickModal');
  if (!modal || !product) return;

  _qvProduct = product;
  _qvQty     = 1;

  // Populate fields
  modal.querySelector('.qv-name').textContent  = product.name || '';
  modal.querySelector('.qv-jp').textContent    = [product.jp, product.type].filter(Boolean).join(' · ');
  modal.querySelector('.qv-price').textContent = '₱' + Number(product.price).toFixed(2);
  modal.querySelector('.qv-desc').textContent  = product.description || '';
  modal.querySelector('.qv-qty').value         = '1'; // Support for typable input

  const img = modal.querySelector('.modal-img img');
  img.src = product.image || '';
  img.alt = product.name  || '';

  const link = modal.querySelector('.qv-link');
  if (link) link.href = 'product.php?slug=' + encodeURIComponent(product.slug || '');

  // Build addon checkboxes dynamically
  const addonsWrap = modal.querySelector('.qv-addons');
  addonsWrap.innerHTML = '';
  const addons = window.MISAKI_ADDONS || [];
  if (addons.length) {
    const head = document.createElement('div');
    head.className   = 'addon-head';
    head.textContent = 'Add-ons';
    head.style.marginTop = '20px';
    addonsWrap.appendChild(head);

    addons.forEach(a => {
      const label       = document.createElement('label');
      label.className   = 'addon-row';
      label.innerHTML   = `
        <input type="checkbox" class="qv-addon" value="${Number(a.id)}"
               data-price="${Number(a.price)}" data-name="${a.name}">
        <span>${a.name}</span>
        <span class="addon-price">+₱${Number(a.price).toFixed(2)}</span>`;
      addonsWrap.appendChild(label);
    });
  }

  openModal(modal);
}

function initQuickView() {
  const modal = document.getElementById('quickModal');
  if (!modal) return;

  const qtyEl = modal.querySelector('.qv-qty');

  modal.querySelector('.qv-minus')?.addEventListener('click', () => {
    if (_qvQty > 1) { 
      _qvQty--; 
      if (qtyEl) qtyEl.value = _qvQty; 
    }
  });

  modal.querySelector('.qv-plus')?.addEventListener('click', () => {
    _qvQty++;
    if (qtyEl) qtyEl.value = _qvQty;
  });

  qtyEl?.addEventListener('change', (e) => {
    let val = parseInt(e.target.value, 10);
    if (isNaN(val) || val < 1) {
      val = 1;
    }
    _qvQty = val;
    e.target.value = _qvQty;
  });

  modal.querySelector('.qv-add')?.addEventListener('click', () => {
    if (!_qvProduct) return;
    const addonIds = [...modal.querySelectorAll('.qv-addon:checked')]
      .map(i => Number(i.value));
    addToCart(_qvProduct, _qvQty, addonIds);
    closeModal(modal);
  });

  modal.querySelector('.qv-share')?.addEventListener('click', () => {
    if (_qvProduct) shareLink('product.php?slug=' + encodeURIComponent(_qvProduct.slug));
  });
}

/* ============================================================
   11. GALLERY LIGHTBOX
   Click gallery items → full-screen lightbox with prev/next
   ============================================================ */
let _lbImages = [];
let _lbIndex  = 0;

function openLightbox(src, allSrcs, idx) {
  _lbImages = allSrcs && allSrcs.length ? allSrcs : [src];
  _lbIndex  = (idx !== undefined) ? idx : _lbImages.indexOf(src);
  if (_lbIndex < 0) _lbIndex = 0;

  const lb  = document.getElementById('lightbox');
  if (!lb) return;

  _lbSetImage(lb);
  lb.classList.add('open');
  lb.removeAttribute('aria-hidden');
  document.body.classList.add('modal-open');

  _lbUpdateNav(lb);
  lb.querySelector('.lb-close')?.focus();
}

function closeLightbox() {
  const lb = document.getElementById('lightbox');
  if (!lb) return;
  lb.classList.remove('open');
  lb.setAttribute('aria-hidden', 'true');
  if (!document.querySelector('.modal.open')) {
    document.body.classList.remove('modal-open');
  }
}

function _lbNav(dir) {
  _lbIndex = (_lbIndex + dir + _lbImages.length) % _lbImages.length;
  const lb  = document.getElementById('lightbox');
  if (!lb) return;
  _lbSetImage(lb);
  _lbUpdateNav(lb);
}

function _lbSetImage(lb) {
  const img = lb.querySelector('img');
  if (!img) return;
  img.style.opacity = '0';
  img.src = _lbImages[_lbIndex];
  img.onload = () => { img.style.opacity = '1'; };
  // Fallback if already cached
  if (img.complete) img.style.opacity = '1';
}

function _lbUpdateNav(lb) {
  const multi = _lbImages.length > 1;
  const prev  = lb.querySelector('.lb-prev');
  const next  = lb.querySelector('.lb-next');
  if (prev) prev.style.display = multi ? '' : 'none';
  if (next) next.style.display = multi ? '' : 'none';
}

function initLightbox() {
  const lb = document.getElementById('lightbox');
  if (!lb) return;

  lb.querySelector('.lb-close')?.addEventListener('click', closeLightbox);
  lb.querySelector('.lb-prev')?.addEventListener('click', () => _lbNav(-1));
  lb.querySelector('.lb-next')?.addEventListener('click', () => _lbNav(1));

  // Click backdrop to close
  lb.addEventListener('click', e => {
    if (e.target === lb || e.target === lb.querySelector('img')) {
      if (e.target.tagName !== 'IMG') closeLightbox();
    }
  });

  document.addEventListener('keydown', e => {
    if (!lb.classList.contains('open')) return;
    if (e.key === 'Escape')      { e.preventDefault(); closeLightbox(); }
    if (e.key === 'ArrowLeft')   { e.preventDefault(); _lbNav(-1); }
    if (e.key === 'ArrowRight')  { e.preventDefault(); _lbNav(1); }
  });

  // Touch swipe support
  let touchStartX = 0;
  lb.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, { passive: true });
  lb.addEventListener('touchend', e => {
    const diff = touchStartX - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 50) _lbNav(diff > 0 ? 1 : -1);
  });
}

function initGallery() {
  const grid = document.querySelector('[data-gallery]');
  if (!grid) return;

  const items = [...grid.querySelectorAll('[data-lightbox]')];
  const srcs  = items.map(i => i.getAttribute('data-lightbox'));

  items.forEach((item, idx) => {
    item.addEventListener('click', () => openLightbox(srcs[idx], srcs, idx));
    // Keyboard accessibility
    item.addEventListener('keydown', e => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        openLightbox(srcs[idx], srcs, idx);
      }
    });
  });
}

/* ============================================================
   12. PRODUCT CARD DELEGATES
   Shared between home page and shop page
   ============================================================ */
function bindProductGrid(container) {
  if (!container) return;

  container.addEventListener('click', e => {
    const quickBtn = e.target.closest('[data-quick]');
    const addBtn   = e.target.closest('[data-add]');
    const shareBtn = e.target.closest('[data-share]');

    if (quickBtn) {
      e.preventDefault();
      try { openQuickView(JSON.parse(quickBtn.getAttribute('data-quick'))); }
      catch (err) { console.warn('Quick view parse error', err); }
    }

    if (addBtn) {
      e.preventDefault();
      try { addToCart(JSON.parse(addBtn.getAttribute('data-add')), 1, []); }
      catch (err) { console.warn('Add to cart parse error', err); }
    }

    if (shareBtn) {
      e.preventDefault();
      shareLink(shareBtn.getAttribute('data-share') || location.href);
    }
  });
}

/* ============================================================
   13. HOME PAGE
   ============================================================ */
function initHome() {
  if (document.body.getAttribute('data-page') !== 'home') return;
  // Bind quick-view / add / share on the featured grid
  bindProductGrid(document.querySelector('.product-grid'));
}

/* ============================================================
   14. SHOP PAGE — filter / sort / search / type chips
   ============================================================ */
function initShop() {
  const shopWrap = document.querySelector('[data-shop]');
  if (!shopWrap) return;

  const grid        = shopWrap.querySelector('.product-grid');
  const emptyMsg    = shopWrap.querySelector('.empty');
  const searchInput = document.getElementById('search');
  const sortSel     = document.getElementById('sort');
  const priceRange  = document.getElementById('price');
  const priceLabel  = document.getElementById('priceLabel');
  const chips       = [...shopWrap.querySelectorAll('.chip')];

  let activeType = 'All';

  // Collect card wrappers (the .reveal divs) and their parsed products
  const wrappers = [...grid.querySelectorAll('.reveal')];
  const cardData = wrappers.map(w => {
    const card = w.querySelector('.product-card');
    try { return JSON.parse(card?.getAttribute('data-product') || '{}'); }
    catch { return {}; }
  });

  function applyFilter() {
    const q    = (searchInput?.value || '').toLowerCase().trim();
    const maxP = Number(priceRange?.value || 99999);
    let visible = 0;

    wrappers.forEach((wrap, i) => {
      const p = cardData[i];
      const matchType   = activeType === 'All' || p.type === activeType;
      const matchSearch = !q
        || (p.name || '').toLowerCase().includes(q)
        || (p.jp   || '').toLowerCase().includes(q)
        || (p.type || '').toLowerCase().includes(q);
      const matchPrice  = Number(p.price) <= maxP;
      const show        = matchType && matchSearch && matchPrice;

      wrap.style.display = show ? '' : 'none';
      if (show) visible++;
    });

    if (emptyMsg) emptyMsg.style.display = visible === 0 ? 'block' : 'none';
  }

  function applySort() {
    const val      = sortSel?.value || '';
    const sorted   = [...wrappers].sort((a, b) => {
      const ia = wrappers.indexOf(a);
      const ib = wrappers.indexOf(b);
      const pa = cardData[ia] || {};
      const pb = cardData[ib] || {};

      if (val === 'Top sales')          return Number(pb.sales || 0) - Number(pa.sales || 0);
      if (val === 'Latest')             return new Date(pb.createdAt || 0) - new Date(pa.createdAt || 0);
      if (val === 'Price: Low to High') return Number(pa.price) - Number(pb.price);
      if (val === 'Price: High to Low') return Number(pb.price) - Number(pa.price);
      return 0;
    });

    sorted.forEach(w => grid.appendChild(w));
    applyFilter();
  }

  // Type chip clicks
  chips.forEach(chip => {
    chip.addEventListener('click', () => {
      chips.forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      activeType = chip.getAttribute('data-type') || 'All';
      applyFilter();
    });
  });

  // Search — debounced
  let searchTimer;
  searchInput?.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilter, 220);
  });

  // Price range
  priceRange?.addEventListener('input', () => {
    if (priceLabel) priceLabel.textContent = '≤ ₱' + priceRange.value;
    applyFilter();
  });

  // Sort
  sortSel?.addEventListener('change', applySort);

  // Delegate clicks on product cards
  bindProductGrid(grid);
}

/* ============================================================
   15. PRODUCT DETAIL PAGE
   Qty control, addon selection, add to cart, share
   ============================================================ */
function initProductPage() {
  const wrap = document.querySelector('[data-product-page]');
  if (!wrap) return;

  let product;
  try { product = JSON.parse(wrap.getAttribute('data-product-page') || '{}'); }
  catch { return; }

  let ppQty = 1;
  const qtyEl   = wrap.querySelector('.pp-qty');
  const minusBtn = wrap.querySelector('.pp-minus');
  const plusBtn  = wrap.querySelector('.pp-plus');
  const addBtn   = wrap.querySelector('.pp-add');
  const shareBtn = wrap.querySelector('.pp-share');

  minusBtn?.addEventListener('click', () => {
    if (ppQty > 1) { 
      ppQty--; 
      if (qtyEl) qtyEl.value = ppQty; 
    }
  });

  plusBtn?.addEventListener('click', () => {
    ppQty++;
    if (qtyEl) qtyEl.value = ppQty;
  });

  qtyEl?.addEventListener('change', (e) => {
    let val = parseInt(e.target.value, 10);
    if (isNaN(val) || val < 1) {
      val = 1;
    }
    ppQty = val;
    e.target.value = ppQty;
  });

  addBtn?.addEventListener('click', () => {
    const addonIds = [...document.querySelectorAll('.pp-addon:checked')]
      .map(i => Number(i.value));
    addToCart(product, ppQty, addonIds);
  });

  shareBtn?.addEventListener('click', () => {
    shareLink('product.php?slug=' + encodeURIComponent(product.slug || ''));
  });
}

/* ============================================================
   16. CART PAGE
   Renders cart from localStorage, handles qty changes & removal
   Checkout button → auth check before navigating
   ============================================================ */
function initCartPage() {
  const wrap = document.querySelector('[data-cart]');
  if (!wrap) return;

  const listEl    = wrap.querySelector('.cart-list');
  const emptyEl   = wrap.querySelector('.cart-empty');
  const totalEl   = wrap.querySelector('.cart-total span:last-child');
  const checkoutBtn = wrap.querySelector('[data-checkout]');

  function renderCart() {
    const cart = getCart();
    listEl.innerHTML = '';

    if (!cart.length) {
      listEl.style.display   = 'none';
      if (emptyEl) emptyEl.style.display = 'block';
      if (totalEl) totalEl.textContent   = '₱0.00';
      return;
    }

    listEl.style.display = '';
    if (emptyEl) emptyEl.style.display = 'none';

    cart.forEach(line => {
      const addonSum   = lineAddonTotal(line);
      const total      = lineTotal(line);
      const addonNames = (line.addons || []).map(a => a.name).join(', ');

      const div = document.createElement('div');
      div.className = 'cart-item';
      div.dataset.key = line.key;
      div.innerHTML = `
        <img src="${escHtml(line.image)}" alt="${escHtml(line.name)}" loading="lazy">
        <div class="cart-item-info">
          <div class="cart-item-name">${escHtml(line.name)}</div>
          ${addonNames
            ? `<div class="cart-item-addons">${escHtml(addonNames)}</div>`
            : ''}
          <div class="cart-item-price">
            ₱${Number(line.price).toFixed(2)}
            ${addonSum ? `<span style="color:var(--muted-fg)"> + ₱${addonSum.toFixed(2)} add-ons</span>` : ''}
          </div>
        </div>
        <div class="cart-item-qty">
          <button class="qty-btn" data-action="minus" data-key="${escHtml(line.key)}" aria-label="Decrease quantity">−</button>
          <input type="number" class="qty-input" value="${line.qty}" min="1" data-key="${escHtml(line.key)}" aria-label="Quantity">
          <button class="qty-btn" data-action="plus"  data-key="${escHtml(line.key)}" aria-label="Increase quantity">+</button>
        </div>
        <div class="cart-item-total">₱${total.toFixed(2)}</div>
        <button class="remove-btn" data-key="${escHtml(line.key)}" aria-label="Remove ${escHtml(line.name)}">×</button>
      `;
      listEl.appendChild(div);
    });

    if (totalEl) totalEl.textContent = '₱' + cartGrandTotal().toFixed(2);
  }

  // Event delegation on list (click for buttons)
  listEl.addEventListener('click', e => {
    const key    = e.target.getAttribute('data-key');
    const action = e.target.getAttribute('data-action');
    if (!key) return;

    if (e.target.classList.contains('remove-btn')) {
      removeFromCart(key);
      renderCart();
      return;
    }
    if (action === 'minus') { deltaLineQty(key, -1); renderCart(); }
    if (action === 'plus')  { deltaLineQty(key, +1); renderCart(); }
  });

  // Event delegation for manual typing in cart input
  listEl.addEventListener('change', e => {
    if (e.target.classList.contains('qty-input')) {
      const key = e.target.getAttribute('data-key');
      let val = parseInt(e.target.value, 10);
      if (isNaN(val) || val < 1) val = 1;
      
      setLineQty(key, val);
      renderCart();
    }
  });

  // Checkout button — intercept if not logged in
  checkoutBtn?.addEventListener('click', () => {
    if (!getCart().length) {
      showToast('Your cart is empty.');
      return;
    }
    if (!window.MISAKI_AUTH) {
      // Redirect to login, come back to checkout
      location.href = 'login.php?next=' + encodeURIComponent('checkout.php');
      return;
    }
    location.href = 'checkout.php';
  });

  renderCart();
}

/* ============================================================
   17. CHECKOUT PAGE
   Populates the order summary table and injects cart JSON
   into the hidden form field before submission
   ============================================================ */
function initCheckoutPage() {
  const page = document.querySelector('[data-checkout-page]');
  if (!page) return;

  const cart        = getCart();
  const summaryEl   = page.querySelector('.checkout-summary');
  const cartInput   = document.getElementById('cartJson');
  const form        = document.getElementById('checkoutForm');

  // If cart is empty and no success message shown → redirect
  const hasSuccess = page.querySelector('.auth-success');
  if (!cart.length && !hasSuccess) {
    location.href = 'cart.php';
    return;
  }

  // Populate summary table
  if (summaryEl && cart.length) {
    let rows = '';
    cart.forEach(line => {
      const addonSum   = lineAddonTotal(line);
      const total      = lineTotal(line);
      const addonNames = (line.addons || []).map(a => a.name).join(', ') || '—';
      rows += `
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:12px">
              <img src="${escHtml(line.image)}" alt="" style="width:44px;height:56px;object-fit:cover;border-radius:4px;flex-shrink:0">
              <div>
                <div style="font-family:'Cormorant Garamond',serif;font-size:1rem">${escHtml(line.name)}</div>
                <div style="font-size:.7rem;color:var(--muted-fg);margin-top:2px">${escHtml(addonNames)}</div>
              </div>
            </div>
          </td>
          <td style="text-align:center">${line.qty}</td>
          <td style="text-align:right;font-weight:500">₱${total.toFixed(2)}</td>
        </tr>`;
    });

    summaryEl.innerHTML = `
      <table style="width:100%;border-collapse:collapse;font-size:.875rem">
        <thead>
          <tr style="border-bottom:1px solid var(--border);text-align:left">
            <th style="padding:10px 0;font-weight:500;color:var(--muted-fg);font-size:.7rem;letter-spacing:.1em;text-transform:uppercase">Item</th>
            <th style="padding:10px 0;font-weight:500;color:var(--muted-fg);font-size:.7rem;letter-spacing:.1em;text-transform:uppercase;text-align:center">Qty</th>
            <th style="padding:10px 0;font-weight:500;color:var(--muted-fg);font-size:.7rem;letter-spacing:.1em;text-transform:uppercase;text-align:right">Subtotal</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
        <tfoot>
          <tr style="border-top:2px solid var(--ink)">
            <td colspan="2" style="padding:14px 0;font-family:'Cormorant Garamond',serif;font-size:1.2rem"><strong>Total</strong></td>
            <td style="padding:14px 0;text-align:right;font-family:'Cormorant Garamond',serif;font-size:1.2rem"><strong>₱${cartGrandTotal().toFixed(2)}</strong></td>
          </tr>
        </tfoot>
      </table>`;
  }

  // Inject cart into hidden field before submit
  if (cartInput) cartInput.value = JSON.stringify(cart);

  form?.addEventListener('submit', () => {
    if (cartInput) cartInput.value = JSON.stringify(getCart());
  });
}

/* ============================================================
   18. STAR RATING INPUT  (account.php review forms)
   CSS handles the reverse-order highlight trick;
   this adds hover preview for better UX
   ============================================================ */
function initStarRating() {
  document.querySelectorAll('.rating-input').forEach(container => {
    const labels = [...container.querySelectorAll('label')];

    labels.forEach((label, i) => {
      label.addEventListener('mouseenter', () => {
        // Highlight this and all stars "above" it (lower index = higher star)
        labels.forEach((l, j) => {
          l.style.color = j >= i ? 'var(--sage-deep)' : 'var(--border)';
        });
      });
      label.addEventListener('mouseleave', () => {
        labels.forEach(l => (l.style.color = ''));
      });
    });
  });
}

/* ============================================================
   19. UTILITY — HTML escape helper
   ============================================================ */
function escHtml(str) {
  return String(str || '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

/* ============================================================
   INIT — runs after DOM is ready
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => {
  renderIcons();       // Must be first so icons appear before other inits
  initPageLoader();
  initNavbar();
  initScrollTop();
  initReveal();
  updateCartBadge();
  initModals();
  initFAQ();
  initLightbox();
  initGallery();
  initQuickView();
  initHome();
  initShop();
  initProductPage();
  initCartPage();
  initCheckoutPage();
  initStarRating();
});