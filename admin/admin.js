/* ═══════════════════════════════════════════
   STATE
═══════════════════════════════════════════ */
let settings     = { commission:10, delivery:200, freeDelivery:2000, service:50, serviceMode:'fixed' };
let orders       = [];
let products     = [];
let notifications= [];
let consumers    = [];
let farmers      = [];
let weekSales    = [0,0,0,0,0,0,0];
let currentOrderId   = null;
let editProductId    = null;
let editImgProductId = null;
let admins           = [];
let editAdminId      = null;
let categories       = [];
let editCategoryId   = null;

const weekDays = ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'];

/* ═══════════════════════════════════════════
   API ROUTES
═══════════════════════════════════════════ */
const API = {
  login:         '../api/admin/auth/login.php',
  logout:        '../api/admin/auth/logout.php',
  session:       '../api/admin/auth/session.php',
  dashboard:     '../api/admin/dashboard.php',
  orders:        '../api/admin/orders.php',
  products:      '../api/admin/products.php',
  users:         '../api/admin/users.php',
  notifications: '../api/admin/notifications.php',
  settings:      '../api/admin/settings.php',
  admins:        '../api/admin/admins.php',
  categories:    '../api/admin/categories.php',
};

async function apiFetch(url, opts = {}) {
  const res = await fetch(url, { credentials: 'same-origin', ...opts });
  return res.json();
}

/* ═══════════════════════════════════════════
   AUTH
═══════════════════════════════════════════ */
async function doLogin() {
  const u    = document.getElementById('login-user').value.trim();
  const p    = document.getElementById('login-pass').value;
  const errEl= document.getElementById('login-err');
  errEl.style.display = 'none';

  const res = await apiFetch(API.login, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify({ username: u, password: p }),
  });

  if (res.success) {
    document.getElementById('login-screen').style.display = 'none';
    document.getElementById('app').style.display = 'flex';
    await initApp();
  } else {
    errEl.textContent   = res.message || 'Identifiant ou mot de passe incorrect.';
    errEl.style.display = 'block';
  }
}

document.addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); });

async function doLogout() {
  await apiFetch(API.logout, { method: 'POST' });
  orders = []; products = []; notifications = []; topCustomers = []; farmers = [];
  document.getElementById('app').style.display          = 'none';
  document.getElementById('login-screen').style.display = 'flex';
  document.getElementById('login-user').value = '';
  document.getElementById('login-pass').value = '';
}

/* Auto-restore session on page load */
(async function checkSession() {
  const res = await apiFetch(API.session);
  if (res.success) {
    document.getElementById('login-screen').style.display = 'none';
    document.getElementById('app').style.display          = 'flex';
    updateAdminName(res.username);
    await initApp();
  }
})();

function updateAdminName(name) {
  document.querySelectorAll('.topbar-admin-name, .sidebar-user-info p')
    .forEach(el => { el.textContent = name; });
  document.querySelectorAll('.topbar-admin-avatar, .sidebar-user-avatar')
    .forEach(el => { el.textContent = name.charAt(0).toUpperCase(); });
}

/* ═══════════════════════════════════════════
   INIT — load everything from API in parallel
═══════════════════════════════════════════ */
async function initApp() {
  const now = new Date();
  document.getElementById('page-date').textContent =
    now.toLocaleDateString('fr-DZ', { weekday:'long', year:'numeric', month:'long', day:'numeric' });

  const [dashRes, ordersRes, productsRes, usersRes, notifsRes, settingsRes, adminsRes, catsRes] = await Promise.all([
    apiFetch(API.dashboard),
    apiFetch(API.orders),
    apiFetch(API.products),
    apiFetch(API.users),
    apiFetch(API.notifications),
    apiFetch(API.settings),
    apiFetch(API.admins),
    apiFetch(API.categories),
  ]);

  /* Settings */
  if (settingsRes.success) {
    const s = settingsRes.settings;
    settings.commission   = parseFloat(s.commission_rate)       || 10;
    settings.delivery     = parseFloat(s.delivery_fee)          || 200;
    settings.freeDelivery = parseFloat(s.free_delivery_minimum) || 2000;
    settings.service      = parseFloat(s.service_fee)           || 50;
    settings.serviceMode  = s.service_fee_mode                  || 'fixed';
    document.getElementById('set-commission').value    = settings.commission;
    document.getElementById('set-delivery').value      = settings.delivery;
    document.getElementById('set-free-delivery').value = settings.freeDelivery;
    document.getElementById('set-service').value       = settings.service;
    document.getElementById('set-service-mode').value  = settings.serviceMode;
  }

  /* Dashboard stats */
  if (dashRes.success) {
    weekSales = dashRes.week_sales;
    const s   = dashRes.stats;
    document.getElementById('stat-total-orders').textContent = s.total_orders;
    document.getElementById('stat-pending').textContent      = s.pending;
    document.getElementById('stat-revenue').textContent      = formatDA(s.revenue);
    document.getElementById('stat-commission').textContent   = formatDA(s.commission);
    document.getElementById('stat-farmers').textContent      = s.farmers;
    document.getElementById('stat-consumers').textContent    = s.consumers;
    const cfc = document.getElementById('cust-farmers-count');
    const ccc = document.getElementById('cust-consumers-count');
    if (cfc) cfc.textContent = s.farmers;
    if (ccc) ccc.textContent = s.consumers;
  }

  /* Orders */
  if (ordersRes.success) {
    orders = ordersRes.orders;
    renderDashOrders();
    renderOrdersTable(orders);
    renderCommissions();
    populateWilayaFilter();
    const cic = document.getElementById('comm-invoiced-count');
    if (cic) cic.textContent = orders.length;
  }

  /* Products */
  if (productsRes.success) {
    products = productsRes.products;
    renderProducts(products);
  }

  /* Users */
  if (usersRes.success) {
    consumers = usersRes.consumers;
    farmers   = usersRes.farmers;
    renderConsumers();
    renderFarmers();
  }

  /* Admins */
  if (adminsRes.success) {
    admins = adminsRes.admins;
    renderAdmins();
  }

  /* Categories */
  if (catsRes.success) {
    categories = catsRes.categories;
    renderCategories();
  }

  /* Notifications */
  if (notifsRes.success) {
    notifications = notifsRes.notifications;
    renderDashNotifs();
    renderAllNotifs();
  }

  renderWeekChart();
  updateBadges();

  /* Refresh notifications every 45 s */
  setInterval(refreshNotifications, 45000);
}

async function refreshNotifications() {
  const res = await apiFetch(API.notifications);
  if (res.success) {
    notifications = res.notifications;
    renderDashNotifs();
    renderAllNotifs();
    updateBadges();
  }
}

/* ═══════════════════════════════════════════
   NAVIGATION
═══════════════════════════════════════════ */
const sectionTitles = {
  dashboard:'Tableau de bord', orders:'Gestion des commandes',
  products:'Gestion des produits', customers:'Utilisateurs',
  farmers:'Agriculteurs', commissions:'Commissions',
  notifications:'Notifications', settings:'Paramètres',
  admins:'Administrateurs', categories:'Catégories'
};

function showSection(name, el) {
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  document.getElementById('sec-' + name).classList.add('active');
  if (el) el.classList.add('active');
  document.getElementById('page-title').textContent = sectionTitles[name] || name;
  if (name === 'notifications') markAllRead();
}

/* ═══════════════════════════════════════════
   FORMAT
═══════════════════════════════════════════ */
function formatDA(n) {
  return Number(n).toLocaleString('fr') + ' DA';
}

/* ═══════════════════════════════════════════
   WEEK CHART
═══════════════════════════════════════════ */
function renderWeekChart() {
  const today = new Date().getDay();
  const max   = Math.max(...weekSales, 1);
  const wrap  = document.getElementById('week-chart');
  wrap.innerHTML = weekDays.map((d, i) => {
    const h       = Math.round((weekSales[i] / max) * 100);
    const isToday = i === today;
    return `<div class="chart-bar-col">
      <div class="chart-bar${isToday ? ' accent' : ''}"
           style="height:${h}%;opacity:${weekSales[i] > 0 ? 1 : 0.15}"
           title="${Number(weekSales[i]).toLocaleString('fr')} DA"></div>
      <span style="color:${isToday ? 'var(--accent)' : 'var(--text-muted)'};font-weight:${isToday ? 800 : 700}">${d}</span>
    </div>`;
  }).join('');
}

/* ═══════════════════════════════════════════
   BADGES
═══════════════════════════════════════════ */
function statusBadge(s) {
  if (s === 'new')       return '<span class="badge badge-new"><span class="dot dot-green"></span>Nouvelle</span>';
  if (s === 'prep')      return '<span class="badge badge-prep"><span class="dot dot-orange"></span>En préparation</span>';
  if (s === 'delivered') return '<span class="badge badge-delivered"><span class="dot dot-blue"></span>Livrée</span>';
  return s;
}

function updateBadges() {
  const newCount = orders.filter(o => o.status === 'new').length;
  const unread   = notifications.filter(n => !n.read).length;
  const nb = document.getElementById('new-orders-badge');
  const ob = document.getElementById('notif-badge');
  nb.textContent = newCount;
  ob.textContent = unread;
  document.getElementById('notif-dot').style.display = unread  > 0 ? 'block' : 'none';
  nb.style.display = newCount > 0 ? '' : 'none';
  ob.style.display = unread   > 0 ? '' : 'none';

  const nos = document.getElementById('notif-stat-orders');
  const nus = document.getElementById('notif-stat-users');
  if (nos) nos.textContent = notifications.filter(n => n.type === 'order' && !n.read).length;
  if (nus) nus.textContent = notifications.filter(n => n.type === 'user'  && !n.read).length;
}

/* ═══════════════════════════════════════════
   DASHBOARD
═══════════════════════════════════════════ */
function renderDashOrders() {
  const tbody = document.getElementById('dash-orders-table');
  tbody.innerHTML = orders.slice(0, 5).map(o => `<tr>
    <td style="font-weight:800;color:var(--green-dark)">${o.order_number || o.id}</td>
    <td>${o.farmer || ''}</td>
    <td>${o.wilaya || ''}</td>
    <td>${o.products || ''}</td>
    <td style="font-weight:700">${formatDA(o.subtotal)}</td>
    <td>${statusBadge(o.status)}</td>
    <td style="color:var(--text-muted);font-size:.8rem">${o.date || ''}</td>
  </tr>`).join('');
}

function renderDashNotifs() {
  document.getElementById('dash-notif-list').innerHTML =
    notifications.slice(0, 4).map(n => notifHTML(n)).join('');
}

function notifHTML(n) {
  const icons = {
    order: 'ni-order fas fa-shopping-basket',
    user:  'ni-user fas fa-user-plus',
    alert: 'ni-alert fas fa-exclamation-triangle',
  };
  const cls = icons[n.type] || 'ni-order fas fa-info-circle';
  const [iconCls, ...faCls] = cls.split(' ');
  return `<div class="notif-item${n.read ? '' : ' notif-unread'}">
    <div class="notif-item-icon ${iconCls}"><i class="${faCls.join(' ')}"></i></div>
    <div class="notif-item-body"><p>${n.msg}</p><span>${n.time}</span></div>
  </div>`;
}

/* ═══════════════════════════════════════════
   ORDERS
═══════════════════════════════════════════ */
function renderOrdersTable(list) {
  const tbody = document.getElementById('orders-table');
  const comm  = settings.commission / 100;
  tbody.innerHTML = list.map(o => `<tr>
    <td style="font-weight:800;color:var(--green-dark)">${o.order_number || o.id}</td>
    <td>${o.farmer || ''}</td>
    <td>${o.wilaya || ''}<br><span style="font-size:.73rem;color:var(--text-muted)">${o.commune || ''}</span></td>
    <td style="max-width:160px">${o.products || ''}<br><span style="font-size:.73rem;color:var(--text-muted)">${o.qty || ''}</span></td>
    <td style="font-weight:700">${formatDA(o.subtotal)}</td>
    <td style="color:var(--accent-dark);font-weight:700">${formatDA(Math.round(o.subtotal * comm))}</td>
    <td>${statusBadge(o.status)}</td>
    <td style="color:var(--text-muted);font-size:.8rem">${o.date || ''}</td>
    <td>
      <button class="btn btn-outline btn-sm btn-icon" onclick="viewOrder(${o.id})" title="Voir détail"><i class="fas fa-eye"></i></button>
      <button class="btn btn-outline btn-sm btn-icon" style="margin-left:4px" onclick="advanceOrderId(${o.id})" title="Avancer statut" ${o.status === 'delivered' ? 'disabled' : ''}><i class="fas fa-arrow-right"></i></button>
    </td>
  </tr>`).join('');
  document.getElementById('orders-count').textContent =
    `${list.length} commande${list.length > 1 ? 's' : ''}`;
}

function filterOrders() {
  const q      = document.querySelector('#sec-orders .search-input').value.toLowerCase();
  const status = document.getElementById('order-status-filter').value;
  const wilaya = document.getElementById('order-wilaya-filter').value;
  renderOrdersTable(orders.filter(o => {
    const matchQ = !q || (o.farmer||'').toLowerCase().includes(q)
                       || (o.wilaya||'').toLowerCase().includes(q)
                       || (o.order_number||String(o.id)).toLowerCase().includes(q);
    return matchQ && (!status || o.status === status) && (!wilaya || o.wilaya === wilaya);
  }));
}

function populateWilayaFilter() {
  const sel = document.getElementById('order-wilaya-filter');
  while (sel.options.length > 1) sel.remove(1);
  [...new Set(orders.map(o => o.wilaya).filter(Boolean))].sort().forEach(w => {
    const opt = document.createElement('option');
    opt.value = w; opt.textContent = w;
    sel.appendChild(opt);
  });
}

function viewOrder(id) {
  const o = orders.find(x => x.id == id);
  if (!o) return;
  currentOrderId = id;
  const comm  = Math.round(o.subtotal * (settings.commission / 100));
  const total = parseFloat(o.subtotal) + parseFloat(o.delivery || 0) + parseFloat(o.service || 0);
  document.getElementById('order-modal-body').innerHTML = `
    <div class="order-detail-row"><span>ID commande</span><strong>${o.order_number || o.id}</strong></div>
    <div class="order-detail-row"><span>Client</span><strong>${o.farmer || ''}</strong></div>
    <div class="order-detail-row"><span>Wilaya / Commune</span><strong>${o.wilaya || ''} / ${o.commune || ''}</strong></div>
    <div class="order-detail-row"><span>Produits</span><strong>${o.products || ''}</strong></div>
    <div class="order-detail-row"><span>Quantité</span><strong>${o.qty || ''}</strong></div>
    <div class="order-detail-row"><span>Sous-total produits</span><strong>${formatDA(o.subtotal)}</strong></div>
    <div class="order-detail-row"><span>Commission (${settings.commission}%)</span><strong style="color:var(--accent-dark)">${formatDA(comm)}</strong></div>
    <div class="order-detail-row"><span>Frais de livraison</span><strong>${parseFloat(o.delivery) === 0 ? 'Gratuit' : formatDA(o.delivery)}</strong></div>
    <div class="order-detail-row"><span>Frais de service</span><strong>${formatDA(o.service || 0)}</strong></div>
    <div class="order-detail-row" style="border-top:2px solid var(--green);margin-top:6px">
      <span style="font-weight:800">Total payé</span>
      <strong style="color:var(--green-dark);font-size:1.05rem">${formatDA(total)}</strong>
    </div>
    <div class="order-detail-row"><span>Statut</span>${statusBadge(o.status)}</div>
    <div class="order-detail-row"><span>Date</span><strong>${o.date || ''}</strong></div>
  `;
  document.getElementById('order-modal-next').disabled = o.status === 'delivered';
  openModal('order-modal');
}

function advanceOrder() {
  if (!currentOrderId) return;
  advanceOrderId(currentOrderId);
  closeModal('order-modal');
}

async function advanceOrderId(id) {
  const o = orders.find(x => x.id == id);
  if (!o || o.status === 'delivered') return;

  const res = await apiFetch(API.orders, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify({ id: o.id, action: 'advance' }),
  });

  if (res.success) {
    o.status = res.new_status;
    renderOrdersTable(orders);
    renderDashOrders();
    updateBadges();
    showToast('Statut commande ' + (o.order_number || o.id) + ' mis à jour');
  } else {
    showToast(res.message || 'Erreur lors de la mise à jour');
  }
}

/* ═══════════════════════════════════════════
   PRODUCTS
═══════════════════════════════════════════ */
const catEmoji = { legumes:'🥬', fruits:'🍊', feuilles:'🌿' };

function renderProducts(list) {
  const grid = document.getElementById('products-grid');
  if (!list.length) {
    grid.innerHTML = '<div class="empty-state"><i class="fas fa-seedling"></i><p>Aucun produit trouvé</p></div>';
    return;
  }
  grid.innerHTML = list.map(p => `
    <div class="product-card" id="pc-${p.id}">
      <div class="product-card-img">
        <img src="${p.img || ''}" alt="${p.name}"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <span class="img-fallback">${p.emoji || catEmoji[p.cat] || '🌿'}</span>
      </div>
      <div class="product-card-body">
        <h4>${p.name}</h4>
        <p><i class="fas fa-tractor" style="margin-right:4px;color:var(--green)"></i>${p.farmer || ''}</p>
        <p><i class="fas fa-map-marker-alt" style="margin-right:4px;color:var(--text-muted)"></i>${p.wilaya || ''}</p>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px">
          <span class="product-price">${p.price} DA/kg</span>
          <span style="font-size:.75rem;color:${p.stock < 20 ? 'var(--red)' : 'var(--green)'};font-weight:700">${p.stock} kg</span>
        </div>
        <div class="product-actions">
          <button class="btn btn-outline btn-sm" style="flex:1" onclick="editProduct(${p.id})"><i class="fas fa-edit"></i> Modifier</button>
          <button class="btn btn-outline btn-sm btn-icon" onclick="openImgModal(${p.id})" title="Changer image"><i class="fas fa-camera"></i></button>
          <button class="btn btn-danger btn-sm" onclick="deleteProduct(${p.id})"><i class="fas fa-trash"></i></button>
        </div>
      </div>
    </div>
  `).join('');
}

function filterProducts() {
  const q   = document.querySelector('#sec-products .search-input').value.toLowerCase();
  const cat = document.getElementById('product-cat-filter').value;
  renderProducts(products.filter(p => {
    const matchQ = !q || p.name.toLowerCase().includes(q)
                       || (p.farmer || '').toLowerCase().includes(q)
                       || (p.wilaya || '').toLowerCase().includes(q);
    return matchQ && (!cat || p.cat === cat);
  }));
}

function editProduct(id) {
  const p = products.find(x => x.id == id);
  if (!p) return;
  editProductId = id;
  document.getElementById('product-modal-title').textContent = 'Modifier — ' + p.name;
  document.getElementById('pm-name').value   = p.name;
  document.getElementById('pm-cat').value    = p.cat;
  document.getElementById('pm-price').value  = p.price;
  document.getElementById('pm-farmer').value = p.farmer || '';
  document.getElementById('pm-wilaya').value = p.wilaya || '';
  document.getElementById('pm-stock').value  = p.stock;
  openModal('product-modal');
}

function openImgModal(id) {
  const p = products.find(x => x.id == id);
  if (!p) return;
  editImgProductId = id;
  document.getElementById('img-modal-name').textContent = p.name;
  document.getElementById('img-file').value = '';

  const preview  = document.getElementById('img-modal-preview');
  const fallback = document.getElementById('img-modal-fallback');
  if (p.img) {
    preview.src = p.img;
    preview.style.display = 'block';
    fallback.style.display = 'none';
  } else {
    preview.style.display = 'none';
    fallback.style.display = 'flex';
    fallback.textContent = p.emoji || '🌿';
  }
  openModal('img-modal');
}

function previewImgFile(input) {
  if (!input.files || !input.files[0]) return;
  const reader = new FileReader();
  reader.onload = e => {
    const preview  = document.getElementById('img-modal-preview');
    const fallback = document.getElementById('img-modal-fallback');
    preview.src = e.target.result;
    preview.style.display = 'block';
    fallback.style.display = 'none';
  };
  reader.readAsDataURL(input.files[0]);
}

async function saveProductImage() {
  const file = document.getElementById('img-file').files[0];
  if (!file) { showToast('Veuillez choisir une image.'); return; }

  const form = new FormData();
  form.append('action', 'update_image');
  form.append('id', editImgProductId);
  form.append('image', file);

  let res;
  try {
    res = await apiFetch(API.products, { method: 'POST', body: form });
  } catch (e) {
    showToast('Erreur réseau.');
    return;
  }

  if (res.success) {
    const p = products.find(x => x.id == editImgProductId);
    if (p) {
      p.img = res.img;
      // Update the card img directly without waiting for full re-render
      const card = document.getElementById('pc-' + editImgProductId);
      if (card) {
        const img = card.querySelector('.product-card-img img');
        const fallback = card.querySelector('.product-card-img .img-fallback');
        if (img) { img.src = res.img; img.style.display = ''; }
        if (fallback) fallback.style.display = 'none';
      }
    }
    closeModal('img-modal');
    showToast('Image mise à jour.');
  } else {
    showToast(res.message || 'Erreur lors de l\'upload.');
  }
}

async function saveProduct() {
  const p = products.find(x => x.id == editProductId);
  if (!p) return;

  const payload = {
    action: 'update',
    id:     p.id,
    name:   document.getElementById('pm-name').value,
    cat:    document.getElementById('pm-cat').value,
    price:  parseFloat(document.getElementById('pm-price').value)  || 0,
    wilaya: document.getElementById('pm-wilaya').value,
    stock:  parseInt(document.getElementById('pm-stock').value, 10) || 0,
  };

  const res = await apiFetch(API.products, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify(payload),
  });

  if (res.success) {
    Object.assign(p, { name: payload.name, cat: payload.cat, price: payload.price, wilaya: payload.wilaya, stock: payload.stock });
    closeModal('product-modal');
    filterProducts();
    showToast('Produit "' + p.name + '" modifié avec succès');
  } else {
    showToast(res.message || 'Erreur lors de la modification');
  }
}

async function deleteProduct(id) {
  const p = products.find(x => x.id == id);
  if (!p) return;
  if (!confirm('Supprimer le produit "' + p.name + '" ?')) return;

  const res = await apiFetch(API.products, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify({ action: 'delete', id: p.id }),
  });

  if (res.success) {
    products = products.filter(x => x.id != id);
    filterProducts();
    showToast('Produit supprimé');
  } else {
    showToast(res.message || 'Erreur lors de la suppression');
  }
}

/* ═══════════════════════════════════════════
   CUSTOMERS
═══════════════════════════════════════════ */
function renderConsumers() {
  const medals   = ['🥇','🥈','🥉'];
  const maxTotal = consumers.length > 0 ? consumers[0].total : 1;
  const tbody    = document.getElementById('top-customers-table');
  if (!consumers.length) {
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:20px">Aucun client pour l\'instant</td></tr>';
    return;
  }
  tbody.innerHTML = consumers.map((c, i) => `<tr style="opacity:${c.active ? 1 : 0.55}">
    <td class="rank-medal">${medals[i] || i + 1}</td>
    <td style="font-weight:700">${c.name}</td>
    <td>${c.wilaya || '—'}</td>
    <td style="text-align:center;font-weight:700">${c.orders}</td>
    <td><span style="font-weight:800;color:var(--green-dark)">${formatDA(c.total)}</span></td>
    <td style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
      <button class="btn btn-outline btn-sm btn-icon" onclick="viewUser(${c.id},'consumer')" title="Voir détails"><i class="fas fa-eye"></i></button>
      <button class="btn ${c.active ? 'btn-danger' : 'btn-primary'} btn-sm"
              onclick="toggleUser(${c.id},'consumer')">
        <i class="fas ${c.active ? 'fa-ban' : 'fa-check'}"></i>
        ${c.active ? 'Bloquer' : 'Activer'}
      </button>
    </td>
  </tr>`).join('');
}

function renderFarmers() {
  const tbody = document.getElementById('farmers-table');
  if (!farmers.length) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:20px">Aucun agriculteur inscrit</td></tr>';
    return;
  }
  tbody.innerHTML = farmers.map(f => `<tr style="opacity:${f.active ? 1 : 0.55}">
    <td style="font-weight:700">${f.name}</td>
    <td>${f.wilaya}</td>
    <td style="text-align:center">${f.products}</td>
    <td><span class="badge ${f.active ? 'badge-active' : 'badge-cancelled'}">${f.active ? 'Actif' : 'Inactif'}</span></td>
    <td style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
      <button class="btn btn-outline btn-sm btn-icon" onclick="viewUser(${f.id},'farmer')" title="Voir détails"><i class="fas fa-eye"></i></button>
      <button class="btn ${f.active ? 'btn-danger' : 'btn-primary'} btn-sm"
              onclick="toggleUser(${f.id},'farmer')">
        <i class="fas ${f.active ? 'fa-ban' : 'fa-check'}"></i>
        ${f.active ? 'Bloquer' : 'Activer'}
      </button>
    </td>
  </tr>`).join('');
}

let currentUserModal = null; // { id, role }

async function viewUser(id, role) {
  const res = await apiFetch(API.users + '?id=' + id);
  if (!res.success) { showToast('Impossible de charger les détails'); return; }

  const u     = res.user;
  const local = (role === 'farmer' ? farmers : consumers).find(x => x.id == id);

  currentUserModal = { id, role };

  document.getElementById('user-modal-title').innerHTML =
    `<i class="fas fa-${role === 'farmer' ? 'tractor' : 'user-circle'}" style="color:var(--${role === 'farmer' ? 'purple' : 'teal'});margin-right:8px"></i>${u.full_name}`;

  const rows = [
    ['Téléphone',    u.phone],
    ['Email',        u.email],
    ['Rôle',         u.role === 'farmer' ? 'Agriculteur' : 'Consommateur'],
    ['Wilaya',       u.wilaya],
    ...(u.role === 'farmer' ? [['Commune', u.commune]] : []),
    ...(local?.orders  !== undefined ? [['Commandes',      local.orders]] : []),
    ...(local?.total   !== undefined ? [['Total dépensé',  formatDA(local.total)]] : []),
    ...(local?.products !== undefined ? [['Produits actifs', local.products]] : []),
    ['Statut',       `<strong style="color:var(--${u.is_active ? 'green' : 'red'})">${u.is_active ? 'Actif' : 'Bloqué'}</strong>`],
    ['Inscrit le',   u.joined],
  ];

  document.getElementById('user-modal-body').innerHTML =
    rows.map(([label, val]) =>
      `<div class="order-detail-row"><span>${label}</span><strong>${val}</strong></div>`
    ).join('');

  const btn = document.getElementById('user-modal-toggle-btn');
  btn.className = `btn ${u.is_active ? 'btn-danger' : 'btn-primary'}`;
  btn.innerHTML = `<i class="fas ${u.is_active ? 'fa-ban' : 'fa-check'}"></i> ${u.is_active ? 'Bloquer' : 'Activer'}`;

  openModal('user-modal');
}

async function userModalToggle() {
  if (!currentUserModal) return;
  await toggleUser(currentUserModal.id, currentUserModal.role);
  closeModal('user-modal');
}

async function toggleUser(id, role) {
  const list = role === 'farmer' ? farmers : consumers;
  const u    = list.find(x => x.id == id);
  if (!u) return;

  const res = await apiFetch(API.users, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify({ action: 'toggle', id }),
  });

  if (res.success) {
    u.active = res.active;
    role === 'farmer' ? renderFarmers() : renderConsumers();
    showToast(`${u.name} — ${res.active ? 'compte activé' : 'compte bloqué'}`);
  } else {
    showToast(res.message || 'Erreur lors de la mise à jour');
  }
}

/* ═══════════════════════════════════════════
   CATEGORIES
═══════════════════════════════════════════ */
const catEmojis = { vegetables:'🥬', fruits:'🍊', herbs:'🌿' };

function renderCategories() {
  const tbody = document.getElementById('categories-table');
  if (!tbody) return;
  if (!categories.length) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-muted)">Aucune catégorie</td></tr>';
    return;
  }
  tbody.innerHTML = categories.map(c => `
    <tr>
      <td style="font-weight:700;color:var(--text-muted)">#${c.id}</td>
      <td><code style="background:var(--bg-card);padding:2px 7px;border-radius:5px;font-size:.8rem">${c.slug}</code></td>
      <td style="font-weight:700">${catEmojis[c.slug] || '🏷️'} ${c.name_fr}</td>
      <td style="text-align:center">
        <span style="font-weight:700;color:${c.product_count > 0 ? 'var(--green-dark)' : 'var(--text-muted)'}">${c.product_count}</span>
      </td>
      <td style="display:flex;gap:6px;align-items:center">
        <button class="btn btn-outline btn-sm" onclick="openCategoryModal(${c.id})">
          <i class="fas fa-edit"></i> Modifier
        </button>
        <button class="btn btn-danger btn-sm" onclick="deleteCategory(${c.id})" ${c.product_count > 0 ? 'title="Contient des produits actifs"' : ''}>
          <i class="fas fa-trash"></i>
        </button>
      </td>
    </tr>
  `).join('');
}

function openCategoryModal(id) {
  editCategoryId = id;
  const c = id ? categories.find(x => x.id === id) : null;
  document.getElementById('cat-modal-title').innerHTML =
    `<i class="fas fa-tags" style="color:var(--accent);margin-right:8px"></i>${c ? 'Modifier — ' + c.name_fr : 'Nouvelle catégorie'}`;
  document.getElementById('cat-slug').value = c ? c.slug : '';
  document.getElementById('cat-name').value = c ? c.name_fr : '';
  openModal('category-modal');
}

async function saveCategory() {
  const slug    = document.getElementById('cat-slug').value.trim();
  const name_fr = document.getElementById('cat-name').value.trim();

  if (!slug || !name_fr) { showToast('Slug et nom requis.'); return; }

  const payload = { action: editCategoryId ? 'update' : 'create', slug, name_fr };
  if (editCategoryId) payload.id = editCategoryId;

  const res = await apiFetch(API.categories, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });

  if (res.success) {
    if (editCategoryId) {
      const c = categories.find(x => x.id === editCategoryId);
      if (c) { c.slug = res.slug; c.name_fr = res.name_fr; }
    } else {
      categories.push(res.category);
    }
    renderCategories();
    closeModal('category-modal');
    showToast(editCategoryId ? 'Catégorie mise à jour.' : 'Catégorie créée.');
  } else {
    showToast(res.message || 'Erreur.');
  }
}

async function deleteCategory(id) {
  const c = categories.find(x => x.id === id);
  if (!c) return;
  if (c.product_count > 0) {
    showToast(`Impossible : ${c.product_count} produit(s) utilisent cette catégorie.`);
    return;
  }
  if (!confirm(`Supprimer la catégorie "${c.name_fr}" ?`)) return;

  const res = await apiFetch(API.categories, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'delete', id }),
  });

  if (res.success) {
    categories = categories.filter(x => x.id !== id);
    renderCategories();
    showToast(`"${c.name_fr}" supprimée.`);
  } else {
    showToast(res.message || 'Erreur lors de la suppression.');
  }
}

/* ═══════════════════════════════════════════
   ADMINS
═══════════════════════════════════════════ */
function renderAdmins() {
  const tbody = document.getElementById('admins-table');
  if (!tbody) return;
  const selfId = null; // we don't expose session id to JS — delete button is always shown but backend guards it
  tbody.innerHTML = admins.map(a => `
    <tr>
      <td style="font-weight:700;color:var(--text-muted)">#${a.id}</td>
      <td style="font-weight:700">${a.username}</td>
      <td>${a.created_at}</td>
      <td style="display:flex;gap:6px;align-items:center">
        <button class="btn btn-outline btn-sm" onclick="openAdminModal(${a.id})">
          <i class="fas fa-edit"></i> Modifier
        </button>
        <button class="btn btn-danger btn-sm" onclick="deleteAdmin(${a.id})">
          <i class="fas fa-trash"></i>
        </button>
      </td>
    </tr>
  `).join('');
}

function openAdminModal(id) {
  editAdminId = id;
  const a = id ? admins.find(x => x.id === id) : null;
  document.getElementById('admin-modal-title').innerHTML =
    `<i class="fas fa-user-shield" style="color:var(--teal);margin-right:8px"></i>${a ? 'Modifier — ' + a.username : 'Nouvel administrateur'}`;
  document.getElementById('adm-username').value = a ? a.username : '';
  document.getElementById('adm-password').value = '';
  document.getElementById('adm-pass-label').textContent = a ? 'Nouveau mot de passe (laisser vide pour ne pas changer)' : 'Mot de passe';
  openModal('admin-modal');
}

async function saveAdmin() {
  const username = document.getElementById('adm-username').value.trim();
  const password = document.getElementById('adm-password').value;

  if (!username) { showToast('Le nom d\'utilisateur est requis.'); return; }
  if (!editAdminId && password.length < 6) { showToast('Mot de passe trop court (min 6 car.).'); return; }

  const payload = {
    action:   editAdminId ? 'update' : 'create',
    username,
    password,
  };
  if (editAdminId) payload.id = editAdminId;

  const res = await apiFetch(API.admins, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });

  if (res.success) {
    if (editAdminId) {
      const a = admins.find(x => x.id === editAdminId);
      if (a) a.username = username;
    } else {
      admins.push(res.admin);
    }
    renderAdmins();
    closeModal('admin-modal');
    showToast(editAdminId ? 'Administrateur mis à jour.' : 'Administrateur créé.');
  } else {
    showToast(res.message || 'Erreur.');
  }
}

async function deleteAdmin(id) {
  const a = admins.find(x => x.id === id);
  if (!a) return;
  if (!confirm(`Supprimer l'administrateur "${a.username}" ?`)) return;

  const res = await apiFetch(API.admins, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'delete', id }),
  });

  if (res.success) {
    admins = admins.filter(x => x.id !== id);
    renderAdmins();
    showToast(`"${a.username}" supprimé.`);
  } else {
    showToast(res.message || 'Erreur lors de la suppression.');
  }
}

/* ═══════════════════════════════════════════
   COMMISSIONS
═══════════════════════════════════════════ */
function renderCommissions() {
  const comm  = settings.commission / 100;
  const tbody = document.getElementById('commission-table');
  tbody.innerHTML = orders.map(o => {
    const c     = Math.round(o.subtotal * comm);
    const total = parseFloat(o.subtotal) + parseFloat(o.delivery || 0) + parseFloat(o.service || 0);
    return `<tr>
      <td style="font-weight:800;color:var(--green-dark)">${o.order_number || o.id}</td>
      <td>${o.farmer || ''}</td>
      <td>${formatDA(o.subtotal)}</td>
      <td style="font-weight:700;color:var(--accent-dark)">${formatDA(c)}</td>
      <td>${parseFloat(o.delivery) === 0 ? '<span style="color:var(--green);font-weight:700">Gratuit</span>' : formatDA(o.delivery)}</td>
      <td>${formatDA(o.service || 0)}</td>
      <td style="font-weight:800">${formatDA(total)}</td>
      <td style="color:var(--text-muted);font-size:.8rem">${o.date || ''}</td>
    </tr>`;
  }).join('');

  const totalComm = orders.reduce((s, o) => s + Math.round(o.subtotal * comm), 0);
  document.getElementById('total-comm-stat').textContent  = formatDA(totalComm);
  document.getElementById('stat-commission').textContent  = formatDA(totalComm);
  document.getElementById('comm-rate-display').textContent= settings.commission + '%';
}

/* ═══════════════════════════════════════════
   NOTIFICATIONS
═══════════════════════════════════════════ */
function renderAllNotifs() {
  document.getElementById('all-notifs-list').innerHTML =
    notifications.map(n => notifHTML(n)).join('') ||
    '<p style="text-align:center;color:var(--text-muted);padding:20px">Aucune notification</p>';
}

async function markAllRead() {
  const hadUnread = notifications.some(n => !n.read);
  if (!hadUnread) return;
  await apiFetch(API.notifications, { method: 'POST' });
  notifications.forEach(n => { n.read = true; });
  renderAllNotifs();
  renderDashNotifs();
  updateBadges();
}

/* ═══════════════════════════════════════════
   SETTINGS
═══════════════════════════════════════════ */
async function saveSetting(type) {
  let payload = {};

  if (type === 'commission') {
    settings.commission = parseFloat(document.getElementById('set-commission').value) || 10;
    payload = { commission_rate: settings.commission };
    renderCommissions();
    renderOrdersTable(orders);
  } else if (type === 'delivery') {
    settings.delivery     = parseInt(document.getElementById('set-delivery').value, 10)      || 0;
    settings.freeDelivery = parseInt(document.getElementById('set-free-delivery').value, 10) || 0;
    payload = { delivery_fee: settings.delivery, free_delivery_minimum: settings.freeDelivery };
  } else if (type === 'service') {
    settings.service     = parseInt(document.getElementById('set-service').value, 10) || 0;
    settings.serviceMode = document.getElementById('set-service-mode').value;
    payload = { service_fee: settings.service, service_fee_mode: settings.serviceMode };
  } else if (type === 'security') {
    payload = {
      admin_username: document.getElementById('set-admin-user').value,
      admin_password: document.getElementById('set-admin-pass').value,
    };
  }

  const res = await apiFetch(API.settings, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify(payload),
  });

  const messages = {
    commission: 'Commission mise à jour : ' + settings.commission + '%',
    delivery:   'Frais de livraison enregistrés',
    service:    'Frais de service enregistrés',
    security:   'Paramètres de sécurité enregistrés',
  };
  showToast(res.success ? (messages[type] || 'Enregistré') : (res.message || 'Erreur'));
}

/* ═══════════════════════════════════════════
   MODAL & TOAST
═══════════════════════════════════════════ */
function openModal(id)  { document.getElementById(id).classList.add('open');    }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-backdrop').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
});

let toastTimer;
function showToast(msg) {
  document.getElementById('toast-msg').textContent = msg;
  const t = document.getElementById('toast');
  t.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => t.classList.remove('show'), 3000);
}