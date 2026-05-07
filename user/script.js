/* ═══════════════════════════════════════════════
   SOUKFRESHY — Main Script
   Navigation · Animations · Data · Cart · Wishlist
═══════════════════════════════════════════════ */

'use strict';

// ─── State ────────────────────────────────────
let currentCategory = 'all';
let currentProduct  = null;
let currentQty      = 1;
let cart            = [];
let wishlist        = [];
let toastTimer      = null;
let searchQuery     = '';
let _reviewRating   = 0;

// ─── Product Data (loaded from DB) ───────────
let PRODUCTS = [];
let _productsLoaded = false;

async function loadProducts() {
  if (_productsLoaded) { renderProducts(); return; }

  const grid = $('product-grid');
  if (grid) grid.innerHTML = `
    <div class="col-span-full py-16 text-center font-body text-sm" style="color:#1e6b3c;">
      <i class="fas fa-spinner fa-spin text-3xl mb-3 block"></i>Chargement des produits…
    </div>`;

  try {
    const res  = await fetch('api/products.php');
    const data = await res.json();
    if (data.success) {
      PRODUCTS = data.products;
      _productsLoaded = true;
    } else {
      throw new Error(data.message || 'Erreur');
    }
  } catch {
    if (grid) grid.innerHTML = `
      <div class="col-span-full py-16 text-center text-gray-400 font-body text-sm">
        <i class="fas fa-exclamation-circle text-3xl mb-3 block"></i>Impossible de charger les produits.
      </div>`;
    return;
  }

  renderProducts();
}

// ─── Helpers ──────────────────────────────────
const $ = id => document.getElementById(id);
const availClass = p => p.availability === 'available' ? 'badge-ok' : p.availability === 'limited' ? 'badge-low' : 'badge-out';
const availText  = p => p.availability === 'available' ? 'Disponible' : p.availability === 'limited' ? 'Stock limité' : 'Rupture de stock';
const catLabel   = c => ({ vegetables: 'Légumes', fruits: 'Fruits', herbs: 'Herbes aromatiques' }[c] || c);

function stars(r) {
  const full = Math.floor(r), half = (r % 1) >= 0.5;
  let h = '';
  for (let i = 0; i < full; i++) h += '<i class="fas fa-star"></i>';
  if (half) h += '<i class="fas fa-star-half-alt"></i>';
  for (let i = full + (half ? 1 : 0); i < 5; i++) h += '<i class="far fa-star"></i>';
  return h;
}

// ─── Splash ───────────────────────────────────
function initSplash() {
  gsap.set('#sp-title', { y: 16 });
  gsap.to('#sp-logo',  { opacity: 1, scale: 1, duration: 0.7, ease: 'back.out(1.7)', delay: 0.2 });
  gsap.to('#sp-title', { opacity: 1, y: 0,     duration: 0.6, ease: 'power3.out',   delay: 0.75 });

  setTimeout(() => {
    gsap.to('#splash', {
      opacity: 0, duration: 0.5, ease: 'power2.in',
      onComplete() {
        $('splash').style.display = 'none';
        showPage('home');
      }
    });
  }, 5000);
}

// ─── Page navigation ──────────────────────────
function showPage(name) {
  ['home','shop','product','wishlist','panier','checkout'].forEach(p => {
    const el = $('page-' + p);
    if (el) el.classList.add('hidden');
  });

  const target = $('page-' + name);
  if (!target) return;
  target.classList.remove('hidden');
  window.scrollTo({ top: 0, behavior: 'instant' });

  if (name === 'home')     { animateHero(); animateWelcomeSection(); animateServicesSection(); animateFooterSection(); renderFeatured(); loadWeeklyBoxes(); }
  if (name === 'shop')       loadProducts();
  if (name === 'wishlist')   renderWishlist();
  if (name === 'panier')     renderPanier();
  if (name === 'checkout')   renderCheckout();
}

// ─── Animations ───────────────────────────────
function animateIntro() {
  gsap.fromTo('.ia',
    { opacity: 0, y: 22 },
    { opacity: 1, y: 0, duration: 0.65, stagger: 0.1, ease: 'power3.out', delay: 0.05 }
  );
}

function animateHero() {
  gsap.fromTo('.hero-el',
    { opacity: 0, y: 28 },
    { opacity: 1, y: 0, duration: 0.7, stagger: 0.13, ease: 'power3.out', delay: 0.1 }
  );
}

function animateCards() {
  gsap.fromTo('.prod-card',
    { opacity: 0, y: 18, scale: 0.97 },
    { opacity: 1, y: 0, scale: 1, duration: 0.4, stagger: 0.055, ease: 'power2.out' }
  );
}

function animateWelcomeSection() {
  const section = $('welcome-section');
  if (!section) return;

  gsap.fromTo(
    '#welcome-section .welcome-el',
    { opacity: 0, y: 24 },
    {
      opacity: 1,
      y: 0,
      duration: 0.7,
      stagger: 0.14,
      ease: 'power3.out',
      scrollTrigger: {
        trigger: section,
        start: 'top 78%',
        once: true
      }
    }
  );

  section.querySelectorAll('.welcome-stat-value').forEach((statEl) => {
    if (statEl.dataset.animated === 'true') return;

    const target = Number(statEl.dataset.target || 0);
    const prefix = statEl.dataset.prefix || '';
    const suffix = statEl.dataset.suffix || '';
    const counter = { value: 0 };

    gsap.to(counter, {
      value: target,
      duration: 1.8,
      ease: 'power2.out',
      scrollTrigger: {
        trigger: section,
        start: 'top 72%',
        once: true
      },
      onUpdate() {
        const roundedValue = Math.round(counter.value);
        statEl.textContent = `${prefix}${roundedValue}${suffix}`;
      },
      onComplete() {
        statEl.dataset.animated = 'true';
      }
    });
  });
}

function animateServicesSection() {
  const section = $('features-strip');
  if (!section) return;

  gsap.fromTo(
    '#features-strip .service-card',
    { opacity: 0, y: 14 },
    {
      opacity: 1,
      y: 0,
      duration: 0.45,
      stagger: 0.08,
      ease: 'power2.out',
      scrollTrigger: {
        trigger: section,
        start: 'top 80%',
        once: true
      }
    }
  );

  gsap.fromTo(
    '#features-strip .service-index',
    { opacity: 0, scale: 0.72, y: -5 },
    {
      opacity: 0.95,
      scale: 1,
      y: 0,
      duration: 0.55,
      stagger: 0.1,
      ease: 'back.out(1.7)',
      scrollTrigger: {
        trigger: section,
        start: 'top 80%',
        once: true
      }
    }
  );
}

function animateFooterSection() {
  const section = $('footer-section');
  if (!section) return;

  gsap.fromTo(
    '#footer-section .footer-col',
    { opacity: 0, y: 20 },
    {
      opacity: 1,
      y: 0,
      duration: 0.55,
      stagger: 0.11,
      ease: 'power2.out',
      scrollTrigger: {
        trigger: section,
        start: 'top 83%',
        once: true
      }
    }
  );

  gsap.fromTo(
    '#footer-section .footer-social-btn',
    { opacity: 0, y: 12, scale: 0.97 },
    {
      opacity: 1,
      y: 0,
      scale: 1,
      duration: 0.45,
      stagger: 0.1,
      ease: 'power2.out',
      scrollTrigger: {
        trigger: section,
        start: 'top 80%',
        once: true
      }
    }
  );
}

// ─── Search ───────────────────────────────────
function toggleSearch(id) {
  const bar = $(id);
  if (!bar) return;
  bar.classList.toggle('hidden');
  if (!bar.classList.contains('hidden')) bar.querySelector('input')?.focus();
}

function searchProducts(val) {
  searchQuery = val.toLowerCase().trim();
  renderProducts();
}

// ─── Shop helpers ─────────────────────────────
function goShop(cat) {
  currentCategory = cat;
  showPage('shop');
}

function filterCat(cat) {
  currentCategory = cat;
  document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
  event.currentTarget.classList.add('active');

  const titles = { all: 'Tous les produits', vegetables: 'Légumes', fruits: 'Fruits', herbs: 'Herbes aromatiques' };
  const titleEl = $('shop-title');
  if (titleEl) titleEl.textContent = titles[cat] || 'Produits';

  renderProducts();
}

// ─── Render product grid ──────────────────────
function renderProducts() {
  const grid = $('product-grid');
  if (!grid) return;

  let list = currentCategory === 'all' ? PRODUCTS : PRODUCTS.filter(p => p.category === currentCategory);
  if (searchQuery) list = list.filter(p => p.name.toLowerCase().includes(searchQuery) || p.region.toLowerCase().includes(searchQuery));

  const countEl = $('prod-count');
  if (countEl) countEl.textContent = `${list.length} produit${list.length !== 1 ? 's' : ''}`;

  grid.innerHTML = list.length === 0
    ? `<div class="col-span-full py-16 text-center text-gray-400 font-body text-sm">
         <i class="fas fa-search text-3xl mb-3 block"></i>Aucun produit trouvé
       </div>`
    : list.map(p => `
      <div class="prod-card" onclick="openProduct(${p.id})">
        <div class="prod-img">
          <img src="${p.image}" alt="${p.name}" loading="lazy"
               onerror="this.src='https://placehold.co/400x400/f0faf4/1e6b3c?text=${encodeURIComponent(p.name)}'">
          <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,0.18) 0%,transparent 50%);pointer-events:none;"></div>
          <span class="absolute top-2 left-2 text-[10px] font-body font-bold px-2 py-0.5 rounded-full ${availClass(p)}">${availText(p)}</span>
          <button class="wish-btn ${wishlist.includes(p.id) ? 'active' : ''}"
                  onclick="event.stopPropagation(); toggleWishBtn(${p.id}, this)">
            <i class="${wishlist.includes(p.id) ? 'fas' : 'far'} fa-heart" style="color:${wishlist.includes(p.id) ? '#ef4444' : '#9ca3af'};"></i>
          </button>
        </div>
        <div class="prod-body">
          <p class="prod-meta font-body">
            <i class="fas fa-map-marker-alt" style="color:#4dbe87; font-size:9px;"></i>${p.region}
          </p>
          <h3 class="prod-title">${p.name}</h3>
          <div class="stars text-[10px] mb-2.5">${stars(p.rating)}
            <span class="text-gray-400 font-body ml-1" style="font-size:10px;">(${p.reviews})</span>
          </div>
          <div class="flex items-center justify-between">
            <div>
              <span class="prod-price">${p.price} DA</span>
              <span class="text-gray-400 text-[11px] font-body">/${p.unit}</span>
            </div>
            <button class="quick-add ${p.availability === 'out' ? 'disabled' : ''}"
                    onclick="event.stopPropagation(); ${p.availability !== 'out' ? `quickAdd(${p.id})` : ''}">
              <i class="fas fa-plus text-white" style="font-size:11px;"></i>
            </button>
          </div>
        </div>
      </div>
    `).join('');

  animateCards();
}

// ─── Render featured (home) ───────────────────
function renderFeatured() {
  const row = $('featured-row');
  if (!row) return;
  const list = PRODUCTS.slice(0, 6);
  row.innerHTML = list.map(p => `
    <div class="mini-card" onclick="openProduct(${p.id})">
      <div style="height:100px; overflow:hidden; position:relative;">
        <img src="${p.image}" alt="${p.name}" style="width:100%;height:100%;object-fit:cover;"
             onerror="this.src='https://placehold.co/300x200/f0faf4/1e6b3c?text=${encodeURIComponent(p.name)}'">
        <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,0.3),transparent);"></div>
      </div>
      <div class="p-2.5">
        <p style="font-size:10px;color:#9ca3af;font-family:'Nunito',sans-serif;">${p.region}</p>
        <p style="font-size:12px;font-weight:600;color:#111827;font-family:'Nunito',sans-serif;line-height:1.3;" class="mt-0.5">${p.name}</p>
        <p style="font-size:12px;font-weight:700;color:#1e6b3c;font-family:'Nunito',sans-serif;" class="mt-1">${p.price} DA</p>
      </div>
    </div>
  `).join('');
}

// ─── Open product detail ──────────────────────
function openProduct(id) {
  currentProduct = PRODUCTS.find(p => p.id === id);
  if (!currentProduct) return;
  currentQty = 1;
  updateQtyDisplay();
  const qtyUnit = $('qty-unit');
  if (qtyUnit) qtyUnit.textContent = currentProduct.unit || 'kg';

  // Update wishlist nav icon
  const wishNavBtn = $('wish-nav-btn');
  if (wishNavBtn) {
    wishNavBtn.innerHTML = wishlist.includes(id)
      ? '<i class="fas fa-heart" style="color:#ef4444;"></i>'
      : '<i class="far fa-heart"></i>';
  }

  const p     = currentProduct;
  const inWish = wishlist.includes(p.id);
  const dotColor = p.availability === 'available' ? '#22c55e' : p.availability === 'limited' ? '#f59e0b' : '#ef4444';
  const tagBg    = p.availability === 'available' ? '#f0fdf4' : p.availability === 'limited' ? '#fffbeb' : '#fef2f2';
  const tagColor = p.availability === 'available' ? '#15803d' : p.availability === 'limited' ? '#b45309'  : '#dc2626';
  const tagBorder= p.availability === 'available' ? '#bbf7d0' : p.availability === 'limited' ? '#fde68a'  : '#fecaca';

  $('product-content').innerHTML = `
    <div class="pd-wrap">

      <!-- Hero image — full bleed behind nav gradient -->
      <div class="pd-hero">
        <img src="${p.image}" alt="${p.name}" class="pd-image"
             onerror="this.src='https://placehold.co/900x600/f0faf4/1e6b3c?text=${encodeURIComponent(p.name)}'">
        <div style="position:absolute;inset:0;background:linear-gradient(to bottom,rgba(0,0,0,0.08) 0%,transparent 40%,rgba(0,0,0,0.18) 100%);pointer-events:none;"></div>
        <!-- Floating wishlist -->
        <button id="pd-wish-btn" class="pd-wish-float" onclick="toggleWishCurrent()">
          <i id="pd-wish-icon" class="${inWish ? 'fas' : 'far'} fa-heart" style="font-size:15px;color:${inWish ? '#ef4444' : '#9ca3af'};"></i>
        </button>
      </div>

      <!-- Content sheet slides up over image -->
      <div class="pd-sheet">

        <!-- Category + region -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
          <span style="font-family:'Nunito',sans-serif;font-size:11px;font-weight:700;letter-spacing:0.05em;color:#1e6b3c;background:#ecf7f0;padding:5px 13px;border-radius:20px;">${catLabel(p.category)}</span>
          <span style="font-family:'Nunito',sans-serif;font-size:12px;color:#9ca3af;display:flex;align-items:center;gap:5px;">
            <i class="fas fa-map-marker-alt" style="color:#4dbe87;font-size:9px;"></i>${p.region}
          </span>
        </div>

        <!-- Name + price -->
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:14px;">
          <h1 style="font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700;color:#111827;line-height:1.25;flex:1;margin:0;">${p.name}</h1>
          <div style="text-align:right;flex-shrink:0;padding-top:2px;">
            <div style="font-family:'Nunito',sans-serif;font-size:1.4rem;font-weight:800;color:#1e6b3c;line-height:1;">${p.price}</div>
            <div style="font-family:'Nunito',sans-serif;font-size:11px;color:#9ca3af;margin-top:3px;">DA / ${p.unit}</div>
          </div>
        </div>

        <!-- Rating -->
        <div id="pd-rating-row" style="display:flex;align-items:center;gap:7px;margin-bottom:14px;">
          <span class="stars" style="font-size:13px;">${stars(p.rating)}</span>
          <span style="font-family:'Nunito',sans-serif;font-weight:700;font-size:13px;color:#111827;">${p.rating}</span>
          <span style="font-family:'Nunito',sans-serif;font-size:12px;color:#9ca3af;">(${p.reviews} avis)</span>
        </div>

        <!-- Availability pill -->
        <div style="margin-bottom:22px;">
          <span style="display:inline-flex;align-items:center;gap:7px;font-family:'Nunito',sans-serif;font-size:12px;font-weight:600;padding:5px 13px;border-radius:20px;background:${tagBg};color:${tagColor};border:1px solid ${tagBorder};">
            <span style="width:6px;height:6px;border-radius:50%;background:${dotColor};flex-shrink:0;display:inline-block;"></span>
            ${availText(p)}${p.qty > 0 ? ` · ${p.qty} ${p.unit}` : ''}
          </span>
        </div>

        <div class="pd-divider"></div>

        <!-- Description -->
        <p class="pd-label">Description</p>
        <p style="font-family:'Nunito',sans-serif;font-size:14px;color:#374151;line-height:1.8;margin:0 0 22px;">${p.description}</p>

        <div class="pd-divider"></div>

        <!-- Origin -->
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:22px;">
          <div style="width:40px;height:40px;border-radius:14px;background:#ecf7f0;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-leaf" style="color:#1e6b3c;font-size:16px;"></i>
          </div>
          <div>
            <p style="font-family:'Nunito',sans-serif;font-size:13px;font-weight:700;color:#111827;margin:0 0 3px;">Directement du producteur</p>
            <p style="font-family:'Nunito',sans-serif;font-size:12px;color:#6b7280;margin:0;">Région de ${p.region} · Sans intermédiaire</p>
          </div>
        </div>

        <div class="pd-divider"></div>

        <!-- Reviews -->
        <p class="pd-label">Avis clients</p>
        <div id="reviews-section">
          <div style="text-align:center;padding:24px 0;"><i class="fas fa-spinner fa-spin" style="color:#d1d5db;font-size:18px;"></i></div>
        </div>

      </div>
    </div>
  `;

  showPage('product');
  loadReviews(id);
}

// ─── Cart ─────────────────────────────────────
function quickAdd(id) {
  const p = PRODUCTS.find(x => x.id === id);
  if (!p || p.availability === 'out') return;
  showLoginModal(() => { addToCartItem(p, 1); toast(`${p.name} ajouté au panier !`); });
}

function doAddToCart() {
  if (!currentProduct || currentProduct.availability === 'out') return;
  showLoginModal(() => { addToCartItem(currentProduct, currentQty); toast(`${currentProduct.name} ajouté au panier !`); });
}

function addToCartItem(p, qty) {
  const ex = cart.find(i => i.id === p.id);
  ex ? (ex.qty += qty) : cart.push({ ...p, qty });
  const newQty = cart.find(i => i.id === p.id)?.qty ?? qty;
  syncCartItem(p.id, newQty);
  updateBadges();
}

function updateBadges() {
  const total = cart.reduce((s, i) => s + i.qty, 0);
  ['badge-home','badge-shop','badge-product','badge-wishlist-page'].forEach(id => {
    const el = $(id);
    if (!el) return;
    el.textContent = total;
    total > 0 ? el.classList.remove('hidden') : el.classList.add('hidden');
  });
  updateWishBadge();
}

function updateWishBadge() {
  const count = wishlist.length;
  ['badge-wishlist-shop','badge-panier-wish'].forEach(id => {
    const el = $(id);
    if (!el) return;
    el.textContent = count;
    count > 0 ? el.classList.remove('hidden') : el.classList.add('hidden');
  });
}

// ─── Wishlist ─────────────────────────────────
function toggleWishBtn(id, btn) {
  const adding = !wishlist.includes(id);
  if (adding) {
    wishlist.push(id);
    btn.innerHTML = '<i class="fas fa-heart" style="color:#ef4444;"></i>';
    btn.classList.add('active');
  } else {
    wishlist = wishlist.filter(x => x !== id);
    btn.innerHTML = '<i class="far fa-heart" style="color:#9ca3af;"></i>';
    btn.classList.remove('active');
  }
  updateWishBadge();
  syncWishlistItem(id, adding ? 'add' : 'remove');
}

function toggleWishCurrent() {
  if (!currentProduct) return;
  const id = currentProduct.id;
  if (wishlist.includes(id)) {
    wishlist = wishlist.filter(x => x !== id);
  } else {
    wishlist.push(id);
  }
  const inList = wishlist.includes(id);

  // Update floating wish button on product detail
  const pdIcon = $('pd-wish-icon');
  if (pdIcon) {
    pdIcon.className = (inList ? 'fas' : 'far') + ' fa-heart';
    pdIcon.style.color = inList ? '#ef4444' : '#9ca3af';
  }
  // Update nav icon
  const navBtn = $('wish-nav-btn');
  if (navBtn) navBtn.innerHTML = inList ? '<i class="fas fa-heart" style="color:#ef4444;"></i>' : '<i class="far fa-heart"></i>';

  updateWishBadge();
  syncWishlistItem(id, inList ? 'add' : 'remove');
  toast(inList ? 'Ajouté aux favoris !' : 'Retiré des favoris');
}

// ─── Quantity ─────────────────────────────────
function incQty() {
  currentQty = Math.min(currentQty + 1, 99);
  updateQtyDisplay();
}
function decQty() {
  currentQty = Math.max(currentQty - 1, 1);
  updateQtyDisplay();
}
function updateQtyDisplay() {
  const el = $('qty-display');
  if (!el) return;
  el.textContent = currentQty;
  el.classList.remove('qty-pop');
  // force reflow
  void el.offsetWidth;
  el.classList.add('qty-pop');
}

// ─── Toast ────────────────────────────────────
function toast(msg, duration = 2600) {
  clearTimeout(toastTimer);
  const el    = $('toast');
  const msgEl = $('toast-msg');
  if (!el || !msgEl) return;
  msgEl.textContent = msg;
  el.classList.remove('hidden');
  // reset animation
  el.style.animation = 'none';
  void el.offsetWidth;
  el.style.animation = '';

  toastTimer = setTimeout(() => {
    gsap.to(el, {
      opacity: 0, y: -8, duration: 0.22, ease: 'power2.in',
      onComplete() { el.classList.add('hidden'); el.style.opacity = ''; el.style.transform = ''; }
    });
  }, duration);
}

// ─── Wishlist page ────────────────────────────
function renderWishlist() {
  const container = $('wishlist-container');
  if (!container) return;

  const items = PRODUCTS.filter(p => wishlist.includes(p.id));

  const countEl = $('wishlist-header-count');
  if (countEl) countEl.textContent = items.length > 0 ? `(${items.length})` : '';

  if (items.length === 0) {
    container.innerHTML = `
      <div class="flex flex-col items-center justify-center py-24 text-center px-4">
        <div class="w-20 h-20 rounded-full flex items-center justify-center mb-5" style="background:#fef2f2;">
          <i class="far fa-heart text-3xl" style="color:#fca5a5;"></i>
        </div>
        <p class="font-display font-bold text-gray-700 text-lg">Aucun favori</p>
        <p class="font-body text-gray-400 text-sm mt-1 mb-6">Ajoutez des produits à vos favoris depuis la boutique</p>
        <button onclick="showPage('shop')" class="px-7 py-3 rounded-xl font-body font-semibold text-sm text-white" style="background:linear-gradient(135deg,#1e6b3c,#27a163);">
          <i class="fas fa-store mr-2 text-xs"></i>Parcourir la boutique
        </button>
      </div>`;
    return;
  }

  container.innerHTML = `
    <div class="space-y-3">
      ${items.map(p => `
        <div class="bg-white rounded-2xl p-4 flex items-center gap-4" style="border:1px solid #e8f0eb; box-shadow:0 4px 16px rgba(15,58,31,0.07);">
          <div class="w-[72px] h-[72px] rounded-xl overflow-hidden flex-shrink-0">
            <img src="${p.image}" alt="${p.name}" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/200x200/f0faf4/1e6b3c?text=${encodeURIComponent(p.name)}'">
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-body text-[10px] text-gray-400 mb-0.5">
              <i class="fas fa-map-marker-alt" style="color:#4dbe87;font-size:8px;"></i> ${p.region}
            </p>
            <h3 class="font-display font-bold text-gray-900 text-[15px] leading-snug truncate">${p.name}</h3>
            <p class="font-body font-bold mt-1" style="color:#1e6b3c; font-size:15px;">${p.price} <span class="font-normal text-gray-400 text-xs">DA / ${p.unit}</span></p>
          </div>
          <div class="flex flex-col gap-2 flex-shrink-0">
            <button onclick="openProduct(${p.id})" title="Voir le produit"
              class="w-10 h-10 rounded-xl flex items-center justify-center text-white transition-transform active:scale-90"
              style="background:linear-gradient(135deg,#1e6b3c,#27a163); box-shadow:0 4px 12px rgba(30,107,60,0.25);">
              <i class="fas fa-shopping-cart text-xs"></i>
            </button>
            <button onclick="removeFromWishlist(${p.id})" title="Retirer des favoris"
              class="w-10 h-10 rounded-xl flex items-center justify-center transition-transform active:scale-90"
              style="background:#fff0f0; border:1px solid #fecdd3; color:#ef4444;">
              <i class="fas fa-heart text-xs"></i>
            </button>
          </div>
        </div>
      `).join('')}
    </div>`;
}

function removeFromWishlist(id) {
  wishlist = wishlist.filter(x => x !== id);
  renderWishlist();
  updateWishBadge();
  syncWishlistItem(id, 'remove');
}

// ─── Panier page ──────────────────────────────
function renderPanier() {
  const container = $('panier-container');
  if (!container) return;

  const subtotal   = cart.reduce((s, i) => s + i.price * i.qty, 0);
  const itemCount  = cart.reduce((s, i) => s + i.qty, 0);
  const delivery   = subtotal > 0 && subtotal < 2000 ? 200 : 0;
  const grandTotal = subtotal + delivery;

  const countEl = $('panier-header-count');
  if (countEl) countEl.textContent = itemCount > 0 ? `(${itemCount} article${itemCount > 1 ? 's' : ''})` : '';

  const subtotalEl  = $('panier-subtotal');
  const totalEl     = $('panier-total');
  const deliveryEl  = $('panier-delivery-display');
  if (subtotalEl)  subtotalEl.textContent  = subtotal + ' DA';
  if (totalEl)     totalEl.textContent     = grandTotal + ' DA';
  if (deliveryEl) {
    deliveryEl.textContent  = delivery > 0 ? delivery + ' DA' : 'Gratuite';
    deliveryEl.style.color  = delivery > 0 ? '#374151' : '#27a163';
    deliveryEl.style.background = delivery > 0 ? '#f3f4f6' : '#edf9f2';
  }

  if (cart.length === 0) {
    container.innerHTML = `
      <div class="flex flex-col items-center justify-center py-24 text-center px-4">
        <div class="w-20 h-20 rounded-full flex items-center justify-center mb-5" style="background:#f0faf4;">
          <i class="fas fa-shopping-cart text-3xl" style="color:#bbead1;"></i>
        </div>
        <p class="font-display font-bold text-gray-700 text-lg">Panier vide</p>
        <p class="font-body text-gray-400 text-sm mt-1 mb-6">Ajoutez des produits depuis la boutique</p>
        <button onclick="showPage('shop')" class="px-7 py-3 rounded-xl font-body font-semibold text-sm text-white" style="background:linear-gradient(135deg,#1e6b3c,#27a163);">
          <i class="fas fa-store mr-2 text-xs"></i>Commander maintenant
        </button>
      </div>`;
    return;
  }

  container.innerHTML = `
    <div class="space-y-3">
      ${cart.map(item => item.item_type === 'box' ? `
        <div class="bg-white rounded-2xl p-4 flex items-start gap-4" style="border:1px solid #e8f0eb; box-shadow:0 4px 16px rgba(15,58,31,0.07);">
          <div class="w-[72px] h-[72px] rounded-xl overflow-hidden flex-shrink-0 relative" style="background:#fff8ee">
            ${item.image
              ? `<img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">`
              : ''}
            <div style="width:100%;height:100%;display:${item.image ? 'none' : 'flex'};align-items:center;justify-content:center;font-size:1.9rem">📦</div>
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="font-display font-bold text-gray-900 text-[15px] leading-snug">${item.name}</h3>
            <p class="font-body text-gray-400 text-xs mt-0.5">${item.price} DA / boîte</p>
            <div class="flex items-center justify-between mt-2.5">
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg font-body text-xs font-semibold" style="background:#fff3d6;color:#c27a00;border:1px solid #fde68a">
                <i class="fas fa-box-open text-[10px]"></i> 1 boîte
              </span>
              <span class="font-display font-bold text-base" style="color:#1e6b3c;">${item.price} DA</span>
            </div>
          </div>
          <button onclick="removeBoxFromPanier(${item.box_id})" title="Supprimer"
            class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 transition-transform active:scale-90"
            style="background:#fff0f0; border:1px solid #fecdd3; color:#ef4444;">
            <i class="fas fa-trash-alt text-xs"></i>
          </button>
        </div>` : `
        <div class="bg-white rounded-2xl p-4 flex items-start gap-4" style="border:1px solid #e8f0eb; box-shadow:0 4px 16px rgba(15,58,31,0.07);">
          <div class="w-[72px] h-[72px] rounded-xl overflow-hidden flex-shrink-0">
            <img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/200x200/f0faf4/1e6b3c?text=${encodeURIComponent(item.name)}'">
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="font-display font-bold text-gray-900 text-[15px] leading-snug">${item.name}</h3>
            <p class="font-body text-gray-400 text-xs mt-0.5">${item.price} DA / ${item.unit}</p>
            <div class="flex items-center justify-between mt-2.5">
              <div class="flex items-center gap-2 rounded-xl px-2 py-1.5" style="background:#eef5f0; border:1px solid #dcebe2;">
                <button onclick="panierDec(${item.id})" class="w-7 h-7 bg-white rounded-lg flex items-center justify-center font-bold text-lg leading-none shadow-sm transition-transform active:scale-90" style="color:#1e6b3c;">−</button>
                <span class="font-body font-bold text-gray-800 text-sm w-5 text-center">${item.qty}</span>
                <button onclick="panierInc(${item.id})" class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-lg leading-none text-white shadow-sm transition-transform active:scale-90" style="background:#1e6b3c;">+</button>
              </div>
              <span class="font-display font-bold text-base" style="color:#1e6b3c;">${item.price * item.qty} DA</span>
            </div>
          </div>
          <button onclick="removeFromPanier(${item.id})" title="Supprimer"
            class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 transition-transform active:scale-90"
            style="background:#fff0f0; border:1px solid #fecdd3; color:#ef4444;">
            <i class="fas fa-trash-alt text-xs"></i>
          </button>
        </div>`).join('')}
    </div>`;
}

function panierInc(id) {
  const item = cart.find(i => i.id === id);
  if (item) { item.qty++; syncCartItem(id, item.qty); renderPanier(); updateBadges(); }
}

function panierDec(id) {
  const item = cart.find(i => i.id === id);
  if (!item) return;
  if (item.qty <= 1) { removeFromPanier(id); return; }
  item.qty--;
  syncCartItem(id, item.qty);
  renderPanier();
  updateBadges();
}

function removeFromPanier(id) {
  syncCartItem(id, 0);
  cart = cart.filter(i => i.id !== id);
  renderPanier();
  updateBadges();
  toast('Article retiré du panier');
}


// ─── Cart DB sync ─────────────────────────────
async function syncCartItem(productId, qty) {
  if (typeof getSession !== 'function' || !getSession()) return;
  try {
    await fetch('api/cart/update.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ product_id: productId, qty }),
    });
  } catch {}
}

async function loadCartFromDB() {
  if (typeof getSession !== 'function' || !getSession()) return;

  // Upload any in-memory (guest) product items first (boxes always require login)
  const uploads = cart.filter(i => i.qty > 0 && i.item_type !== 'box');
  if (uploads.length > 0) {
    await Promise.all(uploads.map(i => syncCartItem(i.id, i.qty)));
  }

  try {
    const res  = await fetch('api/cart/get.php');
    const data = await res.json();
    if (data.success) {
      cart = data.items;
      updateBadges();
      if (document.getElementById('page-panier') && !document.getElementById('page-panier').classList.contains('hidden')) {
        renderPanier();
      }
    }
  } catch {}
}

// ─── Wishlist DB sync ─────────────────────────
async function syncWishlistItem(productId, action) {
  if (typeof getSession !== 'function' || !getSession()) return;
  try {
    await fetch('api/wishlist/update.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ product_id: productId, action }),
    });
  } catch {}
}

async function loadWishlistFromDB() {
  if (typeof getSession !== 'function' || !getSession()) return;
  try {
    const res  = await fetch('api/wishlist/get.php');
    const data = await res.json();
    if (data.success) {
      wishlist = data.items.map(i => i.id);
      updateWishBadge();
      if (document.getElementById('page-wishlist') && !document.getElementById('page-wishlist').classList.contains('hidden')) {
        renderWishlist();
      }
    }
  } catch {}
}

// ─── Reviews ──────────────────────────────────
function setReviewStar(n) {
  _reviewRating = n;
  for (let i = 1; i <= 5; i++) {
    const btn = $(`rev-star-${i}`);
    if (!btn) continue;
    btn.innerHTML = i <= n
      ? '<i class="fas fa-star" style="color:#f59e0b;"></i>'
      : '<i class="far fa-star" style="color:#d1d5db;"></i>';
  }
}

async function loadReviews(productId) {
  const section = $('reviews-section');
  if (!section) return;
  try {
    const res  = await fetch(`api/reviews/get.php?product_id=${productId}`);
    const data = await res.json();
    if (!data.success) throw new Error();
    renderReviews(productId, data.reviews, data.user_review);
  } catch {
    section.innerHTML = '<p class="text-center font-body text-gray-400 text-sm py-6">Impossible de charger les avis.</p>';
  }
}

function renderReviews(productId, reviews, userReview) {
  const section = $('reviews-section');
  if (!section) return;
  const session = typeof getSession === 'function' ? getSession() : null;

  let formHtml = '';
  if (!session) {
    formHtml = `
      <button onclick="showLoginModal()" class="w-full py-2.5 rounded-xl font-body font-semibold text-sm mb-5 flex items-center justify-center gap-2" style="border:1.5px solid #dceee3; color:#1e6b3c; background:#f0faf4;">
        <i class="fas fa-user-circle text-sm"></i>&nbsp;Connectez-vous pour laisser un avis
      </button>`;
  } else if (userReview) {
    formHtml = `
      <div id="user-review-block" class="mb-5 p-4 rounded-2xl" style="background:#f0faf4; border:1.5px solid #dceee3;" data-rating="${userReview.rating}" data-comment="${escHtml(userReview.comment || '')}">
        <div class="flex items-center justify-between mb-2">
          <p style="font-family:'Nunito',sans-serif; font-weight:700; font-size:13px; color:#155e34;">Votre avis</p>
          <div class="flex gap-2">
            <button onclick="editReview(${productId})" class="text-xs font-body font-semibold px-3 py-1 rounded-lg" style="background:#e8f5ec; color:#1e6b3c;">Modifier</button>
            <button onclick="deleteReview(${productId})" class="text-xs font-body font-semibold px-3 py-1 rounded-lg" style="background:#fff0f0; color:#ef4444;">Supprimer</button>
          </div>
        </div>
        <div class="stars text-sm mb-1">${stars(userReview.rating)}</div>
        ${userReview.comment ? `<p class="font-body text-gray-600 text-sm leading-relaxed mt-1">${escHtml(userReview.comment)}</p>` : ''}
      </div>`;
  } else {
    _reviewRating = 0;
    formHtml = `
      <div id="user-review-block" class="mb-5 p-4 rounded-2xl" style="background:#f0faf4; border:1.5px solid #dceee3;">
        <p style="font-family:'Nunito',sans-serif; font-weight:700; font-size:13px; color:#155e34; margin-bottom:10px;">Donner votre avis</p>
        <div class="flex gap-2 mb-3">
          ${[1,2,3,4,5].map(i => `<button type="button" onclick="setReviewStar(${i})" id="rev-star-${i}" class="text-2xl leading-none transition-transform active:scale-90"><i class="far fa-star" style="color:#d1d5db;"></i></button>`).join('')}
        </div>
        <textarea id="review-comment" rows="3" placeholder="Partagez votre expérience (optionnel)…"
          class="w-full rounded-xl px-3 py-2.5 font-body text-gray-700 resize-none focus:outline-none"
          style="border:1.5px solid #dceee3; background:white; font-size:13px; line-height:1.6;"></textarea>
        <button onclick="submitReview(${productId})" id="review-submit-btn"
          class="w-full mt-3 py-2.5 rounded-xl font-body font-semibold text-sm text-white transition-transform active:scale-95"
          style="background:linear-gradient(135deg,#1e6b3c,#27a163);">
          <i class="fas fa-paper-plane mr-1.5 text-xs"></i>Publier l'avis
        </button>
      </div>`;
  }

  const listHtml = reviews.length === 0
    ? `<p class="text-center font-body text-gray-400 text-sm py-6">Aucun avis pour ce produit.</p>`
    : `<div class="space-y-3">
        ${reviews.map(r => `
          <div class="flex gap-3 p-3 rounded-xl" style="background:#fafafa; border:1px solid #f0f0f0;">
            <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center font-display font-bold text-sm text-white" style="background:linear-gradient(135deg,#1e6b3c,#27a163);">
              ${escHtml(r.full_name.charAt(0).toUpperCase())}
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between gap-2">
                <span class="font-body font-bold text-gray-800 text-sm">${escHtml(r.full_name)}</span>
                <span class="font-body text-gray-400 text-[11px]">${formatReviewDate(r.created_at)}</span>
              </div>
              <div class="stars text-xs mt-0.5 mb-1">${stars(r.rating)}</div>
              ${r.comment ? `<p class="font-body text-gray-600 text-sm leading-relaxed">${escHtml(r.comment)}</p>` : ''}
            </div>
          </div>
        `).join('')}
      </div>`;

  section.innerHTML = formHtml + listHtml;
}

function escHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function formatReviewDate(dateStr) {
  try {
    return new Date(dateStr).toLocaleDateString('fr-DZ', { day: 'numeric', month: 'short', year: 'numeric' });
  } catch { return ''; }
}

function editReview(productId) {
  const block = $('user-review-block');
  if (!block) return;
  const rating  = parseInt(block.dataset.rating, 10) || 0;
  const comment = block.dataset.comment || '';
  _reviewRating = rating;
  block.innerHTML = `
    <p style="font-family:'Nunito',sans-serif; font-weight:700; font-size:13px; color:#155e34; margin-bottom:10px;">Modifier votre avis</p>
    <div class="flex gap-2 mb-3">
      ${[1,2,3,4,5].map(i => `<button type="button" onclick="setReviewStar(${i})" id="rev-star-${i}" class="text-2xl leading-none transition-transform active:scale-90"><i class="${i <= rating ? 'fas' : 'far'} fa-star" style="color:${i <= rating ? '#f59e0b' : '#d1d5db'};"></i></button>`).join('')}
    </div>
    <textarea id="review-comment" rows="3" placeholder="Partagez votre expérience (optionnel)…"
      class="w-full rounded-xl px-3 py-2.5 font-body text-gray-700 resize-none focus:outline-none"
      style="border:1.5px solid #dceee3; background:white; font-size:13px; line-height:1.6;"></textarea>
    <button onclick="submitReview(${productId})" id="review-submit-btn"
      class="w-full mt-3 py-2.5 rounded-xl font-body font-semibold text-sm text-white transition-transform active:scale-95"
      style="background:linear-gradient(135deg,#1e6b3c,#27a163);">
      <i class="fas fa-check mr-1.5 text-xs"></i>Mettre à jour
    </button>`;
  const ta = $('review-comment');
  if (ta) ta.value = comment;
}

async function submitReview(productId) {
  if (_reviewRating === 0) { toast('Veuillez choisir une note (1 à 5 étoiles).'); return; }
  const comment = ($('review-comment') || {}).value || '';
  const btn = $('review-submit-btn');
  if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin text-xs mr-1.5"></i>Publication…'; }

  try {
    const res  = await fetch('api/reviews/submit.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ product_id: productId, rating: _reviewRating, comment }),
    });
    const data = await res.json();
    if (!data.success) {
      toast(data.message || 'Erreur lors de la publication.');
      if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane mr-1.5 text-xs"></i>Publier l\'avis'; }
      return;
    }
    const prod = PRODUCTS.find(p => p.id === productId);
    if (prod) { prod.rating = data.rating; prod.reviews = data.reviews; }
    updateProductRatingDisplay(data.rating, data.reviews);
    toast('Avis publié avec succès !');
    loadReviews(productId);
  } catch {
    toast('Erreur réseau.');
    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane mr-1.5 text-xs"></i>Publier l\'avis'; }
  }
}

async function deleteReview(productId) {
  try {
    const res  = await fetch('api/reviews/delete.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ product_id: productId }),
    });
    const data = await res.json();
    if (!data.success) { toast(data.message || 'Erreur.'); return; }
    const prod = PRODUCTS.find(p => p.id === productId);
    if (prod) { prod.rating = data.rating; prod.reviews = data.reviews; }
    updateProductRatingDisplay(data.rating, data.reviews);
    toast('Avis supprimé.');
    loadReviews(productId);
  } catch {
    toast('Erreur réseau.');
  }
}

function updateProductRatingDisplay(rating, reviewCount) {
  const el = $('pd-rating-row');
  if (!el) return;
  el.innerHTML = `
    <span class="stars" style="font-size:13px;">${stars(rating)}</span>
    <span style="font-family:'Nunito',sans-serif;font-weight:700;font-size:13px;color:#111827;">${rating}</span>
    <span style="font-family:'Nunito',sans-serif;font-size:12px;color:#9ca3af;">(${reviewCount} avis)</span>`;
}

// ─── Checkout ─────────────────────────────────
function doCheckout() {
  showLoginModal(() => {
    if (cart.length === 0) { toast('Votre panier est vide.'); return; }
    showPage('checkout');
  });
}

function renderCheckout() {
  const session  = typeof getSession === 'function' ? getSession() : null;
  const subtotal = cart.reduce((s, i) => s + i.price * i.qty, 0);
  const delivery = subtotal > 0 && subtotal < 2000 ? 200 : 0;
  const total    = subtotal + delivery;
  const count    = cart.reduce((s, i) => s + i.qty, 0);

  const countEl = $('checkout-item-count');
  if (countEl) countEl.textContent = `${count} article${count > 1 ? 's' : ''}`;

  const listEl = $('checkout-items-list');
  if (listEl) {
    listEl.innerHTML = cart.map((item, idx) => `
      <div class="flex items-center justify-between py-2" style="${idx < cart.length - 1 ? 'border-bottom:1px solid #f0f7f2;' : ''}">
        <div class="flex items-center gap-2.5 flex-1 min-w-0">
          <div class="w-9 h-9 rounded-lg overflow-hidden flex-shrink-0">
            <img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover"
              onerror="this.src='https://placehold.co/100x100/f0faf4/1e6b3c?text=${encodeURIComponent(item.name)}'">
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-body font-semibold text-gray-800 text-[13px] leading-tight truncate">${item.name}</p>
            <p class="font-body text-gray-400 text-[11px]">×${item.qty} ${item.unit}</p>
          </div>
        </div>
        <span class="font-display font-bold text-sm flex-shrink-0 ml-2" style="color:#1e6b3c;">${item.price * item.qty} DA</span>
      </div>`).join('');
  }

  const subtotalEl = $('co-subtotal');
  const deliveryEl = $('co-delivery');
  const totalEl    = $('co-total');
  if (subtotalEl) subtotalEl.textContent = subtotal + ' DA';
  if (deliveryEl) {
    deliveryEl.textContent       = delivery > 0 ? delivery + ' DA' : 'Gratuite';
    deliveryEl.style.color       = delivery > 0 ? '#374151' : '#27a163';
    deliveryEl.style.background  = delivery > 0 ? '#f3f4f6' : '#edf9f2';
  }
  if (totalEl) totalEl.textContent = total + ' DA';

  // Pre-fill form only if fields are empty
  if (session) {
    const set = (id, val) => { const el = $(id); if (el && !el.value) el.value = val || ''; };
    set('co-name',    session.full_name);
    set('co-phone',   session.phone);
    set('co-wilaya',  session.wilaya);
    set('co-commune', session.commune);
  }

  // Reset confirm button
  const btn = $('confirm-order-btn');
  if (btn) {
    btn.disabled    = false;
    btn.innerHTML   = '<i class="fas fa-check-circle text-xs"></i><span>Confirmer la commande</span><i class="fas fa-arrow-right text-[11px] opacity-90"></i>';
  }

  // Restore checkout body if it was replaced by success screen
  const body = $('checkout-body');
  if (body && !body.querySelector('#confirm-order-btn')) {
    // body was swapped to success; page will re-render next open — nothing more to do
  }
}

async function confirmOrder() {
  const name    = ($('co-name')?.value    || '').trim();
  const phone   = ($('co-phone')?.value   || '').trim();
  const wilaya  = ($('co-wilaya')?.value  || '').trim();
  const commune = ($('co-commune')?.value || '').trim();
  const address = ($('co-address')?.value || '').trim();

  if (!name)   { toast('Veuillez entrer votre nom.');      $('co-name')?.focus();   return; }
  if (!phone)  { toast('Veuillez entrer votre téléphone.'); $('co-phone')?.focus();  return; }
  if (!wilaya) { toast('Veuillez entrer votre wilaya.');   $('co-wilaya')?.focus(); return; }

  const btn = $('confirm-order-btn');
  if (btn) {
    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i><span>Traitement…</span>';
  }

  try {
    const res  = await fetch('api/orders/checkout.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ name, phone, wilaya, commune, address }),
    });
    const data = await res.json();

    if (data.success) {
      cart = [];
      updateBadges();
      showCheckoutSuccess(data.order_number, data.total, data.delivery_fee);
    } else {
      toast(data.message || 'Erreur lors de la commande.');
      if (btn) {
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-check-circle text-xs"></i><span>Confirmer la commande</span><i class="fas fa-arrow-right text-[11px] opacity-90"></i>';
      }
    }
  } catch {
    toast('Erreur réseau. Veuillez réessayer.');
    if (btn) {
      btn.disabled  = false;
      btn.innerHTML = '<i class="fas fa-check-circle text-xs"></i><span>Confirmer la commande</span><i class="fas fa-arrow-right text-[11px] opacity-90"></i>';
    }
  }
}

function showCheckoutSuccess(orderNumber, total, deliveryFee) {
  const body = $('checkout-body');
  if (!body) return;
  body.innerHTML = `
    <div class="rounded-2xl p-6 text-center mt-8" style="background:#f0faf4; border:1px solid #c6e8d3; box-shadow:0 10px 26px rgba(30,107,60,0.1);">
      <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center" style="background:linear-gradient(135deg,#1e6b3c,#27a163); box-shadow:0 6px 18px rgba(30,107,60,0.35);">
        <i class="fas fa-check text-white text-xl"></i>
      </div>
      <h3 class="font-display font-bold text-xl text-gray-900 mb-1">Commande confirmée !</h3>
      <p class="font-body text-sm text-gray-500 mb-4">Nous préparons votre commande avec soin.</p>
      <div class="rounded-xl px-4 py-3 mb-4" style="background:#ffffff; border:1px solid #d4ebde;">
        <p class="font-body text-xs text-gray-400 mb-0.5">Numéro de commande</p>
        <p class="font-display font-bold text-xl" style="color:#1e6b3c;">${orderNumber}</p>
        <div class="mt-2.5 pt-2.5 flex items-center justify-between" style="border-top:1px dashed #d4ebde;">
          <span class="font-body text-xs text-gray-400">Livraison</span>
          <span class="font-body text-sm font-semibold" style="color:${deliveryFee > 0 ? '#374151' : '#27a163'};">${deliveryFee > 0 ? deliveryFee + ' DA' : 'Gratuite'}</span>
        </div>
        <div class="mt-1.5 flex items-center justify-between">
          <span class="font-body text-xs text-gray-400">Total à payer</span>
          <span class="font-display font-bold text-base" style="color:#1e6b3c;">${total} DA</span>
        </div>
      </div>
      <div class="rounded-xl px-4 py-3 mb-5 flex items-center gap-3" style="background:#fff8e6; border:1px solid #fde68a;">
        <i class="fas fa-money-bill-wave text-amber-500 flex-shrink-0"></i>
        <p class="font-body text-sm text-amber-800 text-left">Paiement en espèces à la livraison (main à main)</p>
      </div>
      <button onclick="showPage('shop')" class="w-full py-3 rounded-xl font-body font-semibold text-sm text-white" style="background:linear-gradient(135deg,#1e6b3c,#27a163); box-shadow:0 6px 18px rgba(30,107,60,0.28);">
        <i class="fas fa-store mr-2 text-xs"></i>Continuer mes achats
      </button>
    </div>`;
}

// ─── User Orders (profile dashboard) ──────────
async function loadUserOrders() {
  const container = document.getElementById('profile-orders');
  if (!container) return;
  container.innerHTML = `<p class="text-sm text-gray-400 font-body text-center py-8">
    <i class="fas fa-spinner fa-spin block text-xl mb-2" style="color:#27a163;"></i>Chargement…</p>`;
  try {
    const res  = await fetch('api/orders/list.php');
    const data = await res.json();
    if (!data.success || !data.orders.length) {
      container.innerHTML = `<div class="text-center py-8">
        <i class="fas fa-box-open text-3xl mb-2" style="color:#c6e8d3;"></i>
        <p class="text-sm text-gray-400 font-body">Aucune commande pour l'instant.</p></div>`;
      return;
    }
    const statusMap = {
      new:       ['Nouvelle',        '#eff6ff', '#1d4ed8'],
      prep:      ['En préparation',  '#fffbeb', '#b45309'],
      delivered: ['Livrée',          '#f0faf4', '#15803d'],
      cancelled: ['Annulée',         '#fef2f2', '#b91c1c'],
    };
    container.innerHTML = data.orders.map(o => {
      const [label, bg, color] = statusMap[o.status] || ['Inconnue', '#f9fafb', '#6b7280'];
      const date = new Date(o.created_at).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
      const itemsText = o.items.map(i => `${i.product_name} ×${i.qty}`).join(', ');
      return `
      <div class="rounded-xl border border-gray-100 px-3 py-2.5" style="background:#fafafa;">
        <div class="flex items-center justify-between mb-1">
          <span class="font-body font-bold text-sm text-gray-800">${o.order_number || '#' + o.id}</span>
          <span class="text-[11px] font-body font-semibold px-2 py-0.5 rounded-full" style="background:${bg};color:${color};">${label}</span>
        </div>
        <p class="text-[11px] text-gray-500 font-body truncate">${itemsText}</p>
        <div class="flex items-center justify-between mt-1.5">
          <span class="text-[11px] text-gray-400 font-body">${date}</span>
          <span class="text-sm font-body font-bold" style="color:#1e6b3c;">${Number(o.total).toLocaleString('fr-FR')} DA</span>
        </div>
      </div>`;
    }).join('');
  } catch {
    container.innerHTML = `<p class="text-sm text-red-400 font-body text-center py-4">Erreur de chargement.</p>`;
  }
}
window.loadUserOrders = loadUserOrders;

// ─── Init ─────────────────────────────────────
// ─── Weekly Boxes ──────────────────────────────
let _weeklyBoxes  = [];
let _currentBoxId = null;

async function loadWeeklyBoxes() {
  try {
    const res  = await fetch('api/weekly-boxes.php');
    const data = await res.json();
    _weeklyBoxes = data.success ? (data.boxes || []) : [];
  } catch {
    _weeklyBoxes = [];
  }
  renderWeeklyBoxes();
}

const _boxTypeLabel = { fruits: '🍊 Fruits', vegetables: '🥬 Légumes', mixed: '🥗 Mixte' };
const _boxTypeColor = { fruits: '#d97a00', vegetables: '#1e6b3c', mixed: '#178a7a' };

function renderWeeklyBoxes() {
  const grid  = $('weekly-boxes-grid-user');
  const empty = $('weekly-boxes-empty');
  if (!grid) return;

  if (!_weeklyBoxes.length) {
    grid.innerHTML = '';
    if (empty) empty.classList.remove('hidden');
    return;
  }
  if (empty) empty.classList.add('hidden');

  grid.innerHTML = _weeklyBoxes.map(b => `
    <div class="weekly-box-card group cursor-pointer rounded-2xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300"
         style="background:#fff;border:1px solid #f0e8d0"
         onclick="openBoxDetail(${b.id})">
      <div style="height:170px;overflow:hidden;position:relative;background:#f5f1eb">
        ${b.image
          ? `<img src="${b.image}" alt="${b.title}" style="width:100%;height:100%;object-fit:cover;transition:transform .4s ease" class="group-hover:scale-105">`
          : `<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:3.5rem">📦</div>`}
        <div style="position:absolute;top:10px;left:10px;font-size:.72rem;font-weight:800;padding:3px 10px;border-radius:20px;background:rgba(255,255,255,0.9);color:${_boxTypeColor[b.box_type] || '#1e6b3c'};box-shadow:0 1px 6px rgba(0,0,0,.12)">${b.box_type_label || _boxTypeLabel[b.box_type] || b.box_type}</div>
        ${b.quantity < 5 && b.quantity > 0 ? `<div style="position:absolute;top:10px;right:10px;font-size:.7rem;font-weight:800;padding:3px 9px;border-radius:20px;background:#fff3d6;color:#b35c00">⚡ Dernières ${b.quantity}</div>` : ''}
        ${b.quantity === 0 ? `<div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.45)"><span style="color:#fff;font-family:Nunito,sans-serif;font-weight:800;font-size:.85rem;letter-spacing:.04em">ÉPUISÉ</span></div>` : ''}
      </div>
      <div style="padding:14px 16px 16px">
        <h4 style="font-family:'Playfair Display',serif;font-weight:700;font-size:1rem;color:#1a3320;margin-bottom:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${b.title}</h4>
        <ul style="margin:0 0 10px;padding-left:16px;list-style:disc;display:flex;flex-direction:column;gap:2px">
          ${b.products.slice(0, 3).map(p => `<li style="font-family:Nunito,sans-serif;font-size:.77rem;color:#5a6b61">${p}</li>`).join('')}
          ${b.products.length > 3 ? `<li style="font-family:Nunito,sans-serif;font-size:.75rem;color:#a08060">+ ${b.products.length - 3} autre(s)…</li>` : ''}
        </ul>
        <div style="display:flex;align-items:center;justify-content:space-between;padding-top:10px;border-top:1px solid #f0ebe0">
          <span style="font-family:'Playfair Display',serif;font-weight:700;font-size:1.15rem;color:#1e6b3c">${Number(b.price).toLocaleString('fr')} DA</span>
          <span style="font-family:Nunito,sans-serif;font-size:.75rem;font-weight:600;padding:3px 10px;border-radius:20px;background:#e8f5ee;color:#1e6b3c">Voir le détail →</span>
        </div>
      </div>
    </div>
  `).join('');

  gsap.fromTo('.weekly-box-card',
    { opacity: 0, y: 18, scale: 0.97 },
    { opacity: 1, y: 0, scale: 1, duration: 0.4, stagger: 0.07, ease: 'power2.out',
      scrollTrigger: { trigger: '#weekly-boxes-section', start: 'top 82%', once: true } }
  );
}

function openBoxDetail(id) {
  const b = _weeklyBoxes.find(x => x.id === id || x.id === String(id));
  if (!b) return;
  _currentBoxId = b.id;

  const img      = $('box-detail-img');
  const imgFb    = $('box-detail-img-fallback');
  const typeBadge= $('box-detail-type-badge');
  const title    = $('box-detail-title');
  const desc     = $('box-detail-desc');
  const products = $('box-detail-products');
  const price    = $('box-detail-price');
  const qty      = $('box-detail-qty');

  if (b.image) {
    img.src                  = b.image;
    img.style.display        = 'block';
    imgFb.style.display      = 'none';
    imgFb.classList.remove('flex');
  } else {
    img.style.display        = 'none';
    imgFb.style.display      = 'flex';
    imgFb.classList.add('flex');
  }

  typeBadge.textContent  = b.box_type_label || _boxTypeLabel[b.box_type] || b.box_type;
  typeBadge.style.color  = _boxTypeColor[b.box_type] || '#1e6b3c';
  title.textContent      = b.title;
  desc.textContent       = b.description || '';
  desc.style.display     = b.description ? '' : 'none';
  price.textContent      = Number(b.price).toLocaleString('fr') + ' DA';

  if (b.quantity === 0) {
    qty.textContent   = 'Épuisé';
    qty.style.color   = '#e53e3e';
  } else if (b.quantity < 5) {
    qty.textContent   = b.quantity + ' restante(s)';
    qty.style.color   = '#d97a00';
  } else {
    qty.textContent   = b.quantity + ' disponibles';
    qty.style.color   = '#1e6b3c';
  }

  products.innerHTML = (b.products && b.products.length)
    ? b.products.map(p => `<li style="display:flex;align-items:flex-start;gap:7px;font-family:Nunito,sans-serif;font-size:.88rem;color:#3a4a3f"><i class="fas fa-check-circle" style="color:#27a163;margin-top:2px;font-size:.72rem;flex-shrink:0"></i><span>${p}</span></li>`).join('')
    : '<li style="font-family:Nunito,sans-serif;font-size:.85rem;color:#a08060">Aucun produit listé.</li>';

  _updateBoxCartBtn(b);

  const backdrop = $('box-detail-backdrop');
  backdrop.classList.remove('hidden');
  gsap.fromTo(backdrop.firstElementChild,
    { opacity: 0, scale: 0.94, y: 14 },
    { opacity: 1, scale: 1,    y: 0, duration: 0.28, ease: 'power2.out' }
  );
}

function closeBoxDetail() {
  const backdrop = $('box-detail-backdrop');
  if (!backdrop) return;
  gsap.to(backdrop.firstElementChild, {
    opacity: 0, scale: 0.94, y: 10, duration: 0.2, ease: 'power2.in',
    onComplete: () => backdrop.classList.add('hidden'),
  });
}

// ─── Box cart helpers ─────────────────────────
function _updateBoxCartBtn(b) {
  const btn   = $('box-cart-btn');
  const label = $('box-cart-btn-label');
  if (!btn || !label) return;
  if (!b || b.quantity === 0) {
    btn.disabled = true;
    btn.style.background = '#9ca3af';
    btn.style.boxShadow  = 'none';
    label.textContent    = 'Épuisé';
    return;
  }
  const inCart = cart.some(i => i.item_type === 'box' && i.box_id === b.id);
  btn.disabled = false;
  if (inCart) {
    btn.style.background = 'linear-gradient(135deg,#f0a500,#d99300)';
    btn.style.boxShadow  = '0 6px 20px rgba(240,165,0,0.3)';
    btn.style.color      = '#0f2a0f';
    label.textContent    = 'Voir le panier →';
    btn.onclick          = () => { closeBoxDetail(); showPage('panier'); };
  } else {
    btn.style.background = 'linear-gradient(135deg,#1e6b3c,#27a163)';
    btn.style.boxShadow  = '0 6px 20px rgba(30,107,60,0.3)';
    btn.style.color      = '#fff';
    label.textContent    = 'Ajouter au panier';
    btn.onclick          = addBoxToCart;
  }
}

function addBoxToCart() {
  const b = _weeklyBoxes.find(x => x.id === _currentBoxId || x.id === String(_currentBoxId));
  if (!b || b.quantity === 0) { toast('Cette boîte est épuisée.'); return; }
  showLoginModal(() => {
    if (cart.some(i => i.item_type === 'box' && i.box_id === b.id)) {
      closeBoxDetail(); showPage('panier'); return;
    }
    cart.push({ box_id: b.id, item_type: 'box', name: b.title, price: b.price, qty: 1, unit: 'boîte', image: b.image || '' });
    syncBoxCartItem(b.id, 1);
    updateBadges();
    _updateBoxCartBtn(b);
    toast(`📦 ${b.title} ajoutée au panier !`);
  });
}

async function syncBoxCartItem(boxId, qty) {
  if (typeof getSession !== 'function' || !getSession()) return;
  try {
    await fetch('api/cart/update.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ box_id: boxId, qty }),
      credentials: 'same-origin',
    });
  } catch {}
}

function removeBoxFromPanier(boxId) {
  syncBoxCartItem(boxId, 0);
  cart = cart.filter(i => !(i.item_type === 'box' && i.box_id === boxId));
  renderPanier();
  updateBadges();
  toast('Boîte retirée du panier');
}

document.addEventListener('DOMContentLoaded', () => {
  gsap.registerPlugin(ScrollTrigger);
  updateBadges();

  // Set initial GSAP states
  gsap.set('#sp-logo', { opacity: 0, scale: 0.75 });

  initSplash();
});