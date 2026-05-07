/* ============================================================
   MISAKI HANDCRAFTED — main.js (v2)
   Cart with add-ons (separate line items), navbar, scroll reveal,
   FAQ modal, scroll-to-top, gallery lightbox, share-with-clipboard,
   quick view, checkout interception.
   ============================================================ */

/* ---------- inline SVG icons ---------- */
const ICON = {
  bag:  '<svg class="i" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
  user: '<svg class="i" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
  help: '<svg class="i" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.1 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>',
  menu: '<svg class="i" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="18" y2="18"/></svg>',
  x:    '<svg class="i" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>',
  arrow:'<svg class="i-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>',
  up:   '<svg class="i" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>',
  left: '<svg class="i" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>',
  right:'<svg class="i" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>',
  zoom: '<svg class="i-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/><line x1="11" x2="11" y1="8" y2="14"/><line x1="8" x2="14" y1="11" y2="11"/></svg>',
  eye:  '<svg class="i-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>',
  plus: '<svg class="i-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>',
  minus:'<svg class="i-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>',
  share:'<svg class="i-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"/><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"/></svg>',
};
function paintIcons(root){
  (root||document).querySelectorAll('[data-icon]').forEach(el=>{
    if(!el.dataset.iconPainted){ el.innerHTML = ICON[el.dataset.icon] || ''; el.dataset.iconPainted='1'; }
  });
}
paintIcons();

/* ---------- navbar transparency ---------- */
const navbar = document.querySelector('.navbar');
function onScroll(){
  if(!navbar) return;
  const isHome = document.body.dataset.page === 'home';
  if(window.scrollY > 24 || !isHome) navbar.classList.add('scrolled');
  else navbar.classList.remove('scrolled');
  // scroll-to-top visibility
  const st = document.querySelector('.scroll-top');
  if(st){ st.classList.toggle('show', window.scrollY > 400); }
}
window.addEventListener('scroll', onScroll, {passive:true}); onScroll();

/* ---------- mobile menu ---------- */
const menuBtn = document.querySelector('.menu-btn');
const mobileNav = document.querySelector('.mobile-nav');
menuBtn?.addEventListener('click',()=>{
  const open = mobileNav.classList.toggle('open');
  menuBtn.innerHTML = open ? ICON.x : ICON.menu;
});

/* ---------- scroll-to-top ---------- */
document.querySelector('.scroll-top')?.addEventListener('click',()=>{
  window.scrollTo({top:0,behavior:'smooth'});
});

/* ---------- cart store (localStorage) — supports add-ons ---------- */
const CART_KEY = 'misaki_cart';
function getCart(){ try{ return JSON.parse(localStorage.getItem(CART_KEY)||'[]'); }catch{ return []; } }
function setCart(c){ localStorage.setItem(CART_KEY, JSON.stringify(c)); updateCartBadge(true); }
function addonsKey(addonIds){ return (addonIds||[]).slice().sort((a,b)=>a-b).join(','); }
function lineKey(productId, addonIds){ return productId + '|' + addonsKey(addonIds); }

function addToCart(p, qty=1, addonIds=[]){
  const cart = getCart();
  const key = lineKey(p.id, addonIds);
  const existing = cart.find(i=>i.lineKey===key);
  if(existing) existing.qty += qty;
  else cart.push({
    lineKey:key, id:p.id, slug:p.slug, name:p.name,
    price:Number(p.price), image:p.image, qty,
    addons: (window.MISAKI_ADDONS||[]).filter(a=>addonIds.includes(a.id))
                                      .map(a=>({id:a.id,name:a.name,price:Number(a.price)}))
  });
  setCart(cart);
  const note = addonIds.length ? `${p.name} (+${addonIds.length} add-on${addonIds.length>1?'s':''})` : p.name;
  notify('Added to cart', note + (qty>1?` × ${qty}`:''));
}

function lineUnitTotal(line){
  return Number(line.price) + (line.addons||[]).reduce((s,a)=>s+Number(a.price),0);
}

function updateCartBadge(bump){
  const count = getCart().reduce((n,i)=>n+i.qty,0);
  document.querySelectorAll('.cart-badge').forEach(b=>{
    if(count>0){ b.textContent=count; b.style.display='grid'; } else b.style.display='none';
  });
  if(bump){
    document.querySelectorAll('.cart-icon').forEach(el=>{
      el.classList.remove('cart-bump'); void el.offsetWidth; el.classList.add('cart-bump');
    });
    document.querySelectorAll('.cart-link').forEach(el=>{
      const ring = document.createElement('span'); ring.className='cart-ring';
      el.appendChild(ring); setTimeout(()=>ring.remove(), 800);
    });
  }
}
updateCartBadge(false);

/* ---------- notifications ---------- */
let notifStack = document.querySelector('.notif-stack');
if(!notifStack){ notifStack = document.createElement('div'); notifStack.className='notif-stack'; document.body.appendChild(notifStack); }
function notify(title, body){
  const n = document.createElement('div'); n.className='notif';
  n.innerHTML = `<div class="t">${title}</div>${body?`<div class="b">${body}</div>`:''}`;
  notifStack.appendChild(n);
  setTimeout(()=>{ n.style.opacity='0'; n.style.transition='opacity .3s'; setTimeout(()=>n.remove(), 300); }, 2800);
}

/* ---------- share helper (clipboard fallback) ---------- */
async function shareUrl(title, urlPath){
  // build absolute URL relative to current page so XAMPP localhost works
  const url = new URL(urlPath, window.location.href).href;
  try{
    if(navigator.share && window.isSecureContext){
      await navigator.share({title, url}); return;
    }
  }catch{}
  try{
    await navigator.clipboard.writeText(url);
    notify('Link copied!', url);
  }catch{
    // last-ditch: temporary textarea fallback
    const ta = document.createElement('textarea'); ta.value = url; document.body.appendChild(ta);
    ta.select(); try{ document.execCommand('copy'); }catch{}
    ta.remove();
    notify('Link copied!', url);
  }
}

/* ---------- product card buttons (delegation) ---------- */
document.addEventListener('click', e=>{
  const addBtn = e.target.closest('[data-add]');
  if(addBtn){ e.preventDefault(); addToCart(JSON.parse(addBtn.dataset.add), 1, []); return; }
  const shareBtn = e.target.closest('[data-share]');
  if(shareBtn){ e.preventDefault(); shareUrl('Misaki', shareBtn.dataset.share); return; }
  const quickBtn = e.target.closest('[data-quick]');
  if(quickBtn){ e.preventDefault(); openQuickView(JSON.parse(quickBtn.dataset.quick)); return; }
});

/* ---------- quick view modal (with add-ons) ---------- */
function renderAddonOptions(prefix){
  const list = window.MISAKI_ADDONS || [];
  if(!list.length) return '';
  return `<div class="addon-block" style="margin-top:20px">
    <div class="addon-head">Add-ons</div>
    <div class="addon-list">${list.map(a=>`
      <label class="addon-row">
        <input type="checkbox" class="${prefix}-addon" value="${a.id}" data-price="${a.price}">
        <span>${a.name}</span><span class="addon-price">+₱${Number(a.price).toFixed(2)}</span>
      </label>`).join('')}
    </div>
  </div>`;
}
function openQuickView(p){
  const modal = document.getElementById('quickModal');
  if(!modal) return;
  modal.querySelector('.modal-img img').src = p.image;
  modal.querySelector('.modal-img img').alt = p.name;
  modal.querySelector('.qv-name').textContent = p.name;
  modal.querySelector('.qv-jp').textContent = `${p.jp} · ${p.type}`;
  modal.querySelector('.qv-price').textContent = '$'+Number(p.price).toFixed(2);
  modal.querySelector('.qv-desc').textContent = p.description;
  modal.querySelector('.qv-link').href = 'product.php?slug='+encodeURIComponent(p.slug);
  modal.querySelector('.qv-addons').innerHTML = renderAddonOptions('qv');
  let qty = 1;
  const qtyEl = modal.querySelector('.qv-qty'); qtyEl.textContent = qty;
  modal.querySelector('.qv-minus').onclick = ()=>{ qty=Math.max(1,qty-1); qtyEl.textContent=qty; };
  modal.querySelector('.qv-plus').onclick  = ()=>{ qty++; qtyEl.textContent=qty; };
  modal.querySelector('.qv-add').onclick = ()=>{
    const ids = Array.from(modal.querySelectorAll('.qv-addon:checked')).map(i=>+i.value);
    addToCart(p, qty, ids); closeQuickView();
  };
  modal.querySelector('.qv-share').onclick = ()=>shareUrl(p.name, 'product.php?slug='+encodeURIComponent(p.slug));
  modal.classList.add('open');
  document.body.style.overflow='hidden';
}
function closeQuickView(){
  const m = document.getElementById('quickModal');
  if(m){ m.classList.remove('open'); document.body.style.overflow=''; }
}
document.querySelector('#quickModal .modal-bg')?.addEventListener('click', closeQuickView);
document.querySelector('#quickModal .close')?.addEventListener('click', closeQuickView);
document.addEventListener('keydown', e=>{ if(e.key==='Escape'){ closeQuickView(); closeFAQ(); closeLightbox(); } });

/* ---------- FAQ modal ---------- */
const faqModal = document.getElementById('faqModal');
function openFAQ(){ if(!faqModal) return; faqModal.classList.add('open'); document.body.style.overflow='hidden'; }
function closeFAQ(){ if(!faqModal) return; faqModal.classList.remove('open'); document.body.style.overflow=''; }
document.querySelectorAll('.open-faq').forEach(b=>b.addEventListener('click', e=>{e.preventDefault(); openFAQ();}));
faqModal?.querySelector('.modal-bg').addEventListener('click', closeFAQ);
faqModal?.querySelector('.close').addEventListener('click', closeFAQ);

/* ---------- gallery lightbox ---------- */
const lightbox = document.getElementById('lightbox');
let lbItems = [], lbIndex = 0;
function openLightbox(src){
  if(!lightbox) return;
  lbItems = Array.from(document.querySelectorAll('[data-lightbox]')).map(b=>b.dataset.lightbox);
  lbIndex = Math.max(0, lbItems.indexOf(src));
  lightbox.querySelector('img').src = src;
  lightbox.classList.add('open');
  document.body.style.overflow='hidden';
}
function closeLightbox(){ if(!lightbox) return; lightbox.classList.remove('open'); document.body.style.overflow=''; }
function lbStep(d){
  if(!lbItems.length) return;
  lbIndex = (lbIndex + d + lbItems.length) % lbItems.length;
  const img = lightbox.querySelector('img');
  img.style.opacity = '0';
  setTimeout(()=>{ img.src = lbItems[lbIndex]; img.style.opacity='1'; }, 120);
}
document.addEventListener('click', e=>{
  const lb = e.target.closest('[data-lightbox]');
  if(lb){ e.preventDefault(); openLightbox(lb.dataset.lightbox); }
});
lightbox?.querySelector('.lb-close').addEventListener('click', closeLightbox);
lightbox?.querySelector('.lb-prev').addEventListener('click', ()=>lbStep(-1));
lightbox?.querySelector('.lb-next').addEventListener('click', ()=>lbStep(1));
document.addEventListener('keydown', e=>{
  if(!lightbox?.classList.contains('open')) return;
  if(e.key==='ArrowLeft') lbStep(-1);
  if(e.key==='ArrowRight') lbStep(1);
});

/* ---------- scroll reveal ---------- */
const io = new IntersectionObserver(entries=>{
  entries.forEach(e=>{ if(e.isIntersecting){ e.target.classList.add('visible'); io.unobserve(e.target); } });
}, {threshold:.12});
document.querySelectorAll('.reveal').forEach(el=>io.observe(el));

/* ---------- shop filters ---------- */
const shopRoot = document.querySelector('[data-shop]');
if(shopRoot){
  const allCards = Array.from(shopRoot.querySelectorAll('.product-card'));
  const search = shopRoot.querySelector('#search');
  const sort = shopRoot.querySelector('#sort');
  const price = shopRoot.querySelector('#price');
  const priceLabel = shopRoot.querySelector('#priceLabel');
  const chips = shopRoot.querySelectorAll('.chip');
  let activeType = 'All';
  function apply(){
    const q = (search.value||'').toLowerCase();
    const max = +price.value; priceLabel.textContent = '≤ $'+max;
    const data = allCards.map(card=>({card, p: JSON.parse(card.dataset.product)}));
    let visible = data.filter(({p})=>{
      if(activeType!=='All' && p.type!==activeType) return false;
      if(p.price > max) return false;
      if(q && !(p.name.toLowerCase().includes(q) || p.type.toLowerCase().includes(q) || (p.jp||'').includes(q))) return false;
      return true;
    });
    const mode = sort.value;
    if(mode==='Top sales')      visible.sort((a,b)=>(b.p.sales||0)-(a.p.sales||0));
    else if(mode==='Latest')    visible.sort((a,b)=>String(b.p.createdAt||'').localeCompare(String(a.p.createdAt||'')));
    else if(mode.startsWith('Price: Low')) visible.sort((a,b)=>a.p.price-b.p.price);
    else if(mode.startsWith('Price: High')) visible.sort((a,b)=>b.p.price-a.p.price);
    const grid = shopRoot.querySelector('.product-grid');
    allCards.forEach(c=>{ c.parentElement.style.display='none'; });
    visible.forEach(({card})=>{ card.parentElement.style.display=''; grid.appendChild(card.parentElement); });
    shopRoot.querySelector('.empty').style.display = visible.length ? 'none' : 'block';
  }
  search.addEventListener('input', apply);
  sort.addEventListener('change', apply);
  price.addEventListener('input', apply);
  chips.forEach(c=>c.addEventListener('click', ()=>{
    chips.forEach(x=>x.classList.remove('active')); c.classList.add('active');
    activeType = c.dataset.type; apply();
  }));
  apply();
}

/* ---------- cart page ---------- */
const cartRoot = document.querySelector('[data-cart]');
if(cartRoot){
  function renderCart(){
    const cart = getCart();
    const list = cartRoot.querySelector('.cart-list');
    const totalEl = cartRoot.querySelector('.cart-total span:last-child');
    const empty = cartRoot.querySelector('.cart-empty');
    list.innerHTML='';
    if(cart.length===0){ empty.style.display='block'; totalEl.textContent='$0'; return; }
    empty.style.display='none';
    let total=0;
    cart.forEach(i=>{
      const unit = lineUnitTotal(i);
      const sub = unit * i.qty;
      total += sub;
      const row = document.createElement('div'); row.className='cart-row';
      const addonsTxt = (i.addons||[]).length ? `<div class="addons-line">+ ${i.addons.map(a=>a.name).join(', ')}</div>` : '';
      row.innerHTML = `
        <img src="${i.image}" alt="${i.name}">
        <div>
          <div class="font-display" style="font-size:1.125rem">${i.name}</div>
          <div style="font-size:.75rem;color:var(--muted-fg);margin-top:4px">$${Number(i.price).toFixed(2)}${(i.addons||[]).length?` + add-ons $${(unit-i.price).toFixed(2)}`:''}</div>
          ${addonsTxt}
        </div>
        <div class="qty">
          <button data-act="dec" data-key="${i.lineKey}">${ICON.minus}</button>
          <span>${i.qty}</span>
          <button data-act="inc" data-key="${i.lineKey}">${ICON.plus}</button>
        </div>
        <div class="price-cell" style="font-size:.875rem">$${sub.toFixed(2)}</div>
        <button class="rm-cell" data-act="rm" data-key="${i.lineKey}" style="font-size:.75rem;opacity:.6;text-decoration:underline">remove</button>`;
      list.appendChild(row);
    });
    totalEl.textContent = '$'+total.toFixed(2);
  }
  cartRoot.addEventListener('click', e=>{
    const b = e.target.closest('[data-act]');
    if(b){
      const cart = getCart(); const key = b.dataset.key;
      const idx = cart.findIndex(i=>i.lineKey===key); if(idx<0) return;
      if(b.dataset.act==='inc') cart[idx].qty++;
      else if(b.dataset.act==='dec') cart[idx].qty = Math.max(1,cart[idx].qty-1);
      else if(b.dataset.act==='rm') cart.splice(idx,1);
      setCart(cart); renderCart(); return;
    }
    const ck = e.target.closest('[data-checkout]');
    if(ck){
      e.preventDefault();
      if(getCart().length===0){ notify('Cart is empty'); return; }
      if(!window.MISAKI_AUTH){
        notify('Please sign in to checkout');
        setTimeout(()=>{ window.location.href = 'login.php?next='+encodeURIComponent('checkout.php'); }, 600);
      }else{
        window.location.href = 'checkout.php';
      }
    }
  });
  renderCart();
}

/* ---------- product page add-to-cart ---------- */
const productPage = document.querySelector('[data-product-page]');
if(productPage){
  const p = JSON.parse(productPage.dataset.productPage);
  let qty = 1;
  const qtyEl = productPage.querySelector('.pp-qty');
  productPage.querySelector('.pp-minus').onclick = ()=>{ qty=Math.max(1,qty-1); qtyEl.textContent=qty; };
  productPage.querySelector('.pp-plus').onclick  = ()=>{ qty++; qtyEl.textContent=qty; };
  productPage.querySelector('.pp-add').onclick = ()=>{
    const ids = Array.from(productPage.querySelectorAll('.pp-addon:checked')).map(i=>+i.value);
    addToCart(p, qty, ids);
  };
  productPage.querySelector('.pp-share').onclick = ()=>shareUrl(p.name, 'product.php?slug='+encodeURIComponent(p.slug));
}

/* ---------- checkout page: hand cart JSON to the server ---------- */
const checkoutPage = document.querySelector('[data-checkout-page]');
if(checkoutPage){
  const cart = getCart();
  const summary = checkoutPage.querySelector('.checkout-summary');
  const cartInput = checkoutPage.querySelector('#cartJson');
  if(cartInput) cartInput.value = JSON.stringify(cart.map(l=>({
    id:l.id, qty:l.qty, addons:(l.addons||[]).map(a=>a.id)
  })));
  if(summary){
    if(!cart.length){
      summary.innerHTML = '<p style="color:var(--muted-fg)">Your cart is empty. <a href="shop.php" style="text-decoration:underline">Shop blooms</a></p>';
      const f = document.getElementById('checkoutForm'); if(f) f.style.display='none';
    }else{
      let total = 0;
      summary.innerHTML = cart.map(l=>{
        const unit = lineUnitTotal(l), sub = unit*l.qty; total+=sub;
        const ad = (l.addons||[]).map(a=>a.name).join(', ');
        return `<div class="ck-row">
          <div><strong>${l.name}</strong> × ${l.qty}${ad?`<span class="ck-add">+ ${ad}</span>`:''}</div>
          <div>$${sub.toFixed(2)}</div></div>`;
      }).join('') + `<div class="ck-row" style="background:var(--cream)"><strong>Total</strong><strong>$${total.toFixed(2)}</strong></div>`;
    }
  }
}
