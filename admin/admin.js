/* ═══════════════════════════════════════════
   STATE
═══════════════════════════════════════════ */
let settings     = { commission:10, delivery:200, freeDelivery:5000, service:50, serviceMode:'fixed' };
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
let weeklyBoxes      = [];
let editBoxId        = null;
let cowHistory       = [];

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
  weeklyBoxes:   '../api/admin/weekly-boxes.php',
  clientOfWeek:  '../api/admin/client-of-week.php',
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

  const [dashRes, ordersRes, productsRes, usersRes, notifsRes, settingsRes, adminsRes, catsRes, boxesRes, cowRes] = await Promise.all([
    apiFetch(API.dashboard),
    apiFetch(API.orders),
    apiFetch(API.products),
    apiFetch(API.users),
    apiFetch(API.notifications),
    apiFetch(API.settings),
    apiFetch(API.admins),
    apiFetch(API.categories),
    apiFetch(API.weeklyBoxes),
    apiFetch(API.clientOfWeek + '?action=history'),
  ]);

  /* Settings */
  if (settingsRes.success) {
    const s = settingsRes.settings;
    settings.commission   = parseFloat(s.commission_rate)       || 10;
    settings.delivery     = parseFloat(s.delivery_fee)          || 200;
    settings.freeDelivery = parseFloat(s.free_delivery_minimum) || 5000;
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

  /* Boxes (Weekly + Season) */
  if (boxesRes.success) {
    weeklyBoxes = boxesRes.boxes;
    renderWeeklyBoxes();
    renderSeasonBoxes();
    loadBoxStats();
  }

  /* Client of the week */
  if (cowRes.success) {
    cowHistory = cowRes.history;
    renderCowHistory();
  }
  loadCowCurrent();

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
  admins:'Administrateurs', categories:'Catégories',
  'weekly-boxes':'Boîtes Hebdomadaires',
  'client-of-week':'Client de la semaine',
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
const catEmoji = { legumes:'<i class="fas fa-carrot"></i>', fruits:'<i class="fas fa-apple-alt"></i>', feuilles:'<i class="fas fa-seedling"></i>' };

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
        <span class="img-fallback">${catEmoji[p.cat] || '<i class="fas fa-leaf"></i>'}</span>
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
    fallback.innerHTML = catEmoji[p.cat] || '<i class="fas fa-leaf"></i>';
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
  const medals   = ['<i class="fas fa-medal" style="color:#f0a500"></i>','<i class="fas fa-medal" style="color:#9ca3af"></i>','<i class="fas fa-medal" style="color:#b45309"></i>'];
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
const catEmojis = { vegetables:'<i class="fas fa-carrot"></i>', fruits:'<i class="fas fa-apple-alt"></i>', herbs:'<i class="fas fa-seedling"></i>' };

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
      <td style="font-weight:700">${catEmojis[c.slug] || '<i class="fas fa-tag"></i>'} ${c.name_fr}</td>
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

/* ═══════════════════════════════════════════
   BOXES  (Weekly + Season)
═══════════════════════════════════════════ */
let activeBoxTab = 'weekly'; // 'weekly' | 'season'

const _boxCatColors = ['var(--orange)','var(--green)','var(--teal)','var(--purple)','var(--accent)'];
function getBoxTypeLabel(slug) {
  if (slug === 'mixed') return '<i class="fas fa-layer-group"></i> Mixte';
  const cat = categories.find(c => c.slug === slug);
  return cat ? cat.name_fr : slug;
}
function getBoxTypeColor(slug) {
  const idx = categories.findIndex(c => c.slug === slug);
  return _boxCatColors[Math.max(idx, 0) % _boxCatColors.length];
}

const _seasonLabels = { spring:'<i class="fas fa-seedling"></i> Printemps', summer:'<i class="fas fa-sun"></i> Été', autumn:'<i class="fas fa-wind"></i> Automne', winter:'<i class="fas fa-snowflake"></i> Hiver' };
const _seasonColors = { spring:'#27a163', summer:'#d97a00', autumn:'#b85c00', winter:'#3b6da7' };

function switchBoxTab(tab) {
  activeBoxTab = tab;
  document.getElementById('box-tab-weekly').className = tab === 'weekly' ? 'btn btn-primary btn-sm' : 'btn btn-outline btn-sm';
  document.getElementById('box-tab-season').className = tab === 'season' ? 'btn btn-primary btn-sm' : 'btn btn-outline btn-sm';
  document.getElementById('weekly-boxes-grid').style.display = tab === 'weekly' ? '' : 'none';
  document.getElementById('season-boxes-grid').style.display = tab === 'season' ? '' : 'none';
}

async function loadBoxStats() {
  try {
    const res = await apiFetch(API.weeklyBoxes + '?action=stats');
    if (!res.success) return;
    const s = res.stats;
    const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
    set('bstat-total',   s.total);
    set('bstat-active',  s.active);
    set('bstat-weekly',  s.total_weekly);
    set('bstat-season',  s.total_season);
    set('bstat-orders',  s.total_orders);
    set('bstat-revenue', formatDA(s.total_revenue));
    const badge = document.getElementById('boxes-badge');
    if (badge && s.out_of_stock > 0) {
      badge.textContent     = s.out_of_stock + ' épuisée' + (s.out_of_stock > 1 ? 's' : '');
      badge.style.display   = '';
    }
  } catch {}
}

async function bulkToggleBoxes(state) {
  const label = state ? 'Activer toutes les boîtes' : 'Désactiver toutes les boîtes';
  const tab   = activeBoxTab;
  if (!confirm(label + (tab ? ` (${tab === 'weekly' ? 'hebdomadaires' : 'saisonnières'})` : ' (toutes)') + ' ?')) return;
  const res = await apiFetch(API.weeklyBoxes, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'bulk_toggle', is_active: state, category: tab }),
  });
  if (res.success) {
    const fresh = await apiFetch(API.weeklyBoxes);
    if (fresh.success) { weeklyBoxes = fresh.boxes; renderWeeklyBoxes(); renderSeasonBoxes(); }
    loadBoxStats();
    showToast(state ? 'Toutes les boîtes activées.' : 'Toutes les boîtes désactivées.');
  } else showToast(res.message || 'Erreur.');
}

function _renderBoxCard(b) {
  const discountHtml = b.discount_pct > 0
    ? `<span style="font-size:.7rem;font-weight:800;background:var(--red);color:#fff;border-radius:20px;padding:1px 7px">-${b.discount_pct}%</span>`
    : '';
  const badgeHtml = b.badge
    ? `<span style="position:absolute;top:8px;right:8px;background:var(--accent);color:#fff;font-size:.62rem;font-weight:800;padding:2px 8px;border-radius:20px;text-transform:uppercase;letter-spacing:.04em">${b.badge}</span>`
    : '';
  const inactiveHtml = !b.is_active
    ? `<div style="position:absolute;top:8px;left:8px;background:var(--red);color:#fff;font-size:.65rem;font-weight:800;padding:2px 7px;border-radius:20px;text-transform:uppercase">Inactif</div>`
    : '';
  const seasonHtml = b.category === 'season' && b.season
    ? `<p style="font-size:.7rem;font-weight:700;color:${_seasonColors[b.season] || '#888'}">${_seasonLabels[b.season] || b.season}</p>`
    : '';
  const datesHtml = b.category === 'season' && (b.available_from || b.available_until)
    ? `<p style="font-size:.68rem;color:var(--text-muted)">${b.available_from || '?'} → ${b.available_until || '?'}</p>`
    : '';
  const ordersHtml = `<span style="font-size:.72rem;color:var(--text-muted)"><i class="fas fa-shopping-bag" style="margin-right:2px"></i>${b.orders_count || 0} cmd</span>`;

  return `
    <div class="product-card" id="box-card-${b.id}" style="opacity:${b.is_active ? 1 : 0.55}">
      <div class="product-card-img">
        <img src="${b.image || ''}" alt="${b.title}"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <span class="img-fallback"><i class="fas fa-box"></i></span>
        ${inactiveHtml}${badgeHtml}
      </div>
      <div class="product-card-body">
        <h4 style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="${b.title}">${b.title}</h4>
        <p style="font-size:.75rem;color:${getBoxTypeColor(b.box_type)};font-weight:700">${getBoxTypeLabel(b.box_type)}</p>
        ${seasonHtml}${datesHtml}
        <p style="font-size:.73rem;color:var(--text-muted)"><i class="fas fa-list-ul" style="margin-right:3px"></i>${b.products.length} produit${b.products.length !== 1 ? 's' : ''}</p>
        ${b.free_delivery ? '<p style="font-size:.7rem;font-weight:700;color:var(--teal)"><i class="fas fa-truck" style="margin-right:3px"></i>Livraison gratuite</p>' : ''}
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px">
          <div>
            <span class="product-price">${formatDA(b.price)}</span>
            ${b.original_price ? `<span style="font-size:.72rem;color:var(--text-muted);text-decoration:line-through;margin-left:4px">${formatDA(b.original_price)}</span>` : ''}
            ${discountHtml}
          </div>
          <span style="font-size:.75rem;color:${b.quantity < 5 ? 'var(--red)' : 'var(--green)'};font-weight:700">${b.quantity} dispo</span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px">${ordersHtml}</div>
        <div style="margin-top:8px;display:flex;flex-direction:column;gap:5px">
          <div style="display:flex;gap:4px">
            <button class="btn btn-outline btn-sm btn-icon" onclick="viewBox(${b.id})" title="Voir détail"><i class="fas fa-eye"></i></button>
            <button class="btn btn-outline btn-sm" style="flex:1" onclick="openBoxModal(${b.id})"><i class="fas fa-edit"></i> Modifier</button>
          </div>
          <div style="display:flex;gap:4px">
            <button class="btn btn-outline btn-sm" style="flex:1" onclick="duplicateBox(${b.id})" title="Dupliquer"><i class="fas fa-copy"></i> Dupliquer</button>
            <button class="btn ${b.is_active ? 'btn-warning' : 'btn-primary'} btn-sm btn-icon" onclick="toggleBox(${b.id})" title="${b.is_active ? 'Désactiver' : 'Activer'}">
              <i class="fas ${b.is_active ? 'fa-eye-slash' : 'fa-eye'}"></i>
            </button>
            <button class="btn btn-danger btn-sm btn-icon" onclick="deleteBox(${b.id})" title="Supprimer"><i class="fas fa-trash"></i></button>
          </div>
        </div>
      </div>
    </div>
  `;
}

function renderWeeklyBoxes() {
  const grid = document.getElementById('weekly-boxes-grid');
  if (!grid) return;
  const boxes = weeklyBoxes.filter(b => (b.category || 'weekly') === 'weekly');
  if (!boxes.length) {
    grid.innerHTML = '<div class="empty-state"><i class="fas fa-calendar-week"></i><p>Aucune boîte hebdomadaire</p></div>';
    return;
  }
  grid.innerHTML = boxes.map(_renderBoxCard).join('');
}

function renderSeasonBoxes() {
  const grid = document.getElementById('season-boxes-grid');
  if (!grid) return;
  const boxes = weeklyBoxes.filter(b => b.category === 'season');
  if (!boxes.length) {
    grid.innerHTML = '<div class="empty-state"><i class="fas fa-leaf"></i><p>Aucune boîte saisonnière</p></div>';
    return;
  }
  grid.innerHTML = boxes.map(_renderBoxCard).join('');
}

function onBoxCategoryChange() {
  const cat     = document.getElementById('box-category').value;
  const sfEl    = document.getElementById('box-season-fields');
  sfEl.style.display = cat === 'season' ? '' : 'none';
}

function openBoxModal(id) {
  editBoxId = id;
  const b = id ? weeklyBoxes.find(x => x.id === id) : null;

  document.getElementById('box-modal-title').innerHTML =
    `<i class="fas fa-box-open" style="color:var(--accent);margin-right:8px"></i>${b ? 'Modifier — ' + b.title : 'Nouvelle boîte'}`;

  // Category
  const catSel = document.getElementById('box-category');
  catSel.value = b ? (b.category || 'weekly') : 'weekly';
  onBoxCategoryChange();

  document.getElementById('box-title').value          = b ? b.title : '';
  document.getElementById('box-desc').value           = b ? (b.description || '') : '';
  document.getElementById('box-price').value          = b ? b.price : '';
  document.getElementById('box-original-price').value = b && b.original_price ? b.original_price : '';
  document.getElementById('box-qty').value            = b ? b.quantity : '';
  document.getElementById('box-badge').value          = b ? (b.badge || '') : '';

  // Season-specific
  document.getElementById('box-season').value          = b ? (b.season || '') : '';
  document.getElementById('box-available-from').value  = b ? (b.available_from || '') : '';
  document.getElementById('box-available-until').value = b ? (b.available_until || '') : '';

  // Populate type select from categories
  const typeSelect = document.getElementById('box-type');
  typeSelect.innerHTML = categories.map(c => `<option value="${c.slug}">${c.name_fr}</option>`).join('')
    + '<option value="mixed">Mixte</option>';
  const defaultType = categories[0]?.slug || 'mixed';
  typeSelect.value = b ? (b.box_type || defaultType) : defaultType;

  // Populate product checkboxes
  const selectedProducts = new Set(b ? (b.products || []) : []);
  document.getElementById('box-products-search').value = '';
  const activeProducts = products.filter(p => p.is_active == 1 || p.is_active === true);
  document.getElementById('box-products-list').innerHTML = activeProducts.length
    ? activeProducts.map(p => `<label class="box-product-item" data-name="${p.name.toLowerCase()}" style="display:flex;align-items:center;gap:7px;padding:5px 8px;border-radius:6px;cursor:pointer;font-size:.82rem">
        <input type="checkbox" class="box-product-cb" value="${p.name}"
               style="width:15px;height:15px;accent-color:var(--green);flex-shrink:0"
               ${selectedProducts.has(p.name) ? 'checked' : ''}>
        <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${p.name}</span>
        <span style="font-size:.73rem;color:var(--text-muted);flex-shrink:0">${p.price} DA/kg</span>
      </label>`).join('')
    : '<p style="text-align:center;color:var(--text-muted);padding:10px 0;font-size:.82rem">Aucun produit disponible</p>';

  document.getElementById('box-active').checked = b ? b.is_active : true;
  document.getElementById('box-active-label').textContent = (b && !b.is_active) ? 'Inactive' : 'Active';
  document.getElementById('box-free-delivery').checked = b ? !!b.free_delivery : false;
  document.getElementById('box-free-delivery-label').textContent = (b && b.free_delivery) ? 'Oui' : 'Non';
  document.getElementById('box-img-file').value = '';

  const preview  = document.getElementById('box-img-preview');
  const fallback = document.getElementById('box-img-fallback');
  if (b && b.image) {
    preview.src           = b.image;
    preview.style.display = 'block';
    fallback.style.display= 'none';
  } else {
    preview.style.display = 'none';
    fallback.style.display= '';
  }
  openModal('box-modal');
}

function previewBoxImg(input) {
  if (!input.files || !input.files[0]) return;
  const reader = new FileReader();
  reader.onload = e => {
    const preview  = document.getElementById('box-img-preview');
    const fallback = document.getElementById('box-img-fallback');
    preview.src           = e.target.result;
    preview.style.display = 'block';
    fallback.style.display= 'none';
  };
  reader.readAsDataURL(input.files[0]);
}

function filterBoxProducts() {
  const q = document.getElementById('box-products-search').value.toLowerCase();
  document.querySelectorAll('#box-products-list .box-product-item').forEach(item => {
    item.style.display = (!q || item.dataset.name.includes(q)) ? '' : 'none';
  });
}

document.getElementById('box-active').addEventListener('change', function () {
  document.getElementById('box-active-label').textContent = this.checked ? 'Active' : 'Inactive';
});
document.getElementById('box-free-delivery').addEventListener('change', function () {
  document.getElementById('box-free-delivery-label').textContent = this.checked ? 'Oui' : 'Non';
});

async function saveBox() {
  const title    = document.getElementById('box-title').value.trim();
  const price    = parseFloat(document.getElementById('box-price').value) || 0;
  const category = document.getElementById('box-category').value;
  if (!title || price <= 0) { showToast('Titre et prix sont requis.'); return; }

  const origPrice    = document.getElementById('box-original-price').value;
  const productsArr  = [...document.querySelectorAll('#box-products-list .box-product-cb:checked')].map(cb => cb.value);
  const isActive     = document.getElementById('box-active').checked ? 1 : 0;
  const freeDelivery = document.getElementById('box-free-delivery').checked ? 1 : 0;

  const form = new FormData();
  form.append('action',         editBoxId ? 'update' : 'create');
  if (editBoxId) form.append('id', editBoxId);
  form.append('category',       category);
  form.append('title',          title);
  form.append('description',    document.getElementById('box-desc').value.trim());
  form.append('price',          price);
  form.append('original_price', origPrice ? parseFloat(origPrice) : '');
  form.append('quantity',       parseInt(document.getElementById('box-qty').value, 10) || 0);
  form.append('box_type',       document.getElementById('box-type').value);
  form.append('badge',          document.getElementById('box-badge').value.trim());
  form.append('products',       JSON.stringify(productsArr));
  form.append('is_active',      isActive);
  form.append('free_delivery',  freeDelivery);

  if (category === 'season') {
    form.append('season',          document.getElementById('box-season').value);
    form.append('available_from',  document.getElementById('box-available-from').value);
    form.append('available_until', document.getElementById('box-available-until').value);
  }

  const file = document.getElementById('box-img-file').files[0];
  if (file) form.append('image', file);

  let res;
  try {
    res = await apiFetch(API.weeklyBoxes, { method: 'POST', body: form });
  } catch { showToast('Erreur réseau.'); return; }

  if (res.success) {
    closeModal('box-modal');
    const fresh = await apiFetch(API.weeklyBoxes);
    if (fresh.success) { weeklyBoxes = fresh.boxes; renderWeeklyBoxes(); renderSeasonBoxes(); }
    loadBoxStats();
    showToast(editBoxId ? 'Boîte mise à jour.' : 'Boîte créée avec succès.');
  } else {
    showToast(res.message || 'Erreur lors de l\'enregistrement.');
  }
}

async function deleteBox(id) {
  const b = weeklyBoxes.find(x => x.id === id);
  if (!b) return;
  if (!confirm(`Supprimer la boîte "${b.title}" ?`)) return;
  const res = await apiFetch(API.weeklyBoxes, {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'delete', id }),
  });
  if (res.success) {
    weeklyBoxes = weeklyBoxes.filter(x => x.id !== id);
    renderWeeklyBoxes(); renderSeasonBoxes();
    loadBoxStats();
    showToast('Boîte supprimée.');
  } else showToast(res.message || 'Erreur lors de la suppression.');
}

async function toggleBox(id) {
  const res = await apiFetch(API.weeklyBoxes, {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'toggle', id }),
  });
  if (res.success) {
    const b = weeklyBoxes.find(x => x.id === id);
    if (b) b.is_active = res.is_active;
    renderWeeklyBoxes(); renderSeasonBoxes();
    loadBoxStats();
    showToast(res.is_active ? 'Boîte activée.' : 'Boîte désactivée.');
  } else showToast(res.message || 'Erreur.');
}

async function duplicateBox(id) {
  const b = weeklyBoxes.find(x => x.id === id);
  if (!b) return;
  const res = await apiFetch(API.weeklyBoxes, {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'duplicate', id }),
  });
  if (res.success) {
    weeklyBoxes.push(res.box);
    renderWeeklyBoxes(); renderSeasonBoxes();
    loadBoxStats();
    showToast('Boîte dupliquée (inactive).');
  } else showToast(res.message || 'Erreur.');
}

function viewBox(id) {
  const b = weeklyBoxes.find(x => x.id === id);
  if (!b) return;
  const statusBadge = b.is_active
    ? '<span class="badge badge-active" style="background:#e3f5eb;color:var(--green-dark)"><span class="dot dot-green"></span>Active</span>'
    : '<span class="badge badge-cancelled"><span class="dot dot-red"></span>Inactive</span>';
  const catBadge = b.category === 'season'
    ? '<span style="font-size:.75rem;font-weight:700;color:var(--purple);background:#f2ebff;padding:2px 8px;border-radius:20px"><i class="fas fa-leaf" style="margin-right:3px"></i>Saisonnière</span>'
    : '<span style="font-size:.75rem;font-weight:700;color:var(--teal);background:#e6f7f7;padding:2px 8px;border-radius:20px"><i class="fas fa-calendar-week" style="margin-right:3px"></i>Hebdomadaire</span>';

  const discountRow = b.original_price
    ? `<div class="order-detail-row"><span>Prix barré</span><strong style="text-decoration:line-through;color:var(--text-muted)">${formatDA(b.original_price)}</strong></div>
       <div class="order-detail-row"><span>Réduction</span><strong style="color:var(--red)">${b.discount_pct || 0}%</strong></div>`
    : '';
  const seasonRow = b.category === 'season' ? `
    ${b.season ? `<div class="order-detail-row"><span>Saison</span><strong style="color:${_seasonColors[b.season]}">${_seasonLabels[b.season]}</strong></div>` : ''}
    ${b.available_from ? `<div class="order-detail-row"><span>Disponible du</span><strong>${b.available_from} au ${b.available_until || '?'}</strong></div>` : ''}
  ` : '';
  const badgeRow = b.badge ? `<div class="order-detail-row"><span>Badge</span><strong style="color:var(--accent)">${b.badge}</strong></div>` : '';

  document.getElementById('box-view-body').innerHTML = `
    ${b.image ? `<img src="${b.image}" alt="${b.title}" style="width:100%;max-height:200px;object-fit:cover;border-radius:10px;margin-bottom:14px">` : ''}
    <h2 style="margin-bottom:6px;font-size:1.15rem">${b.title}</h2>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px">
      <span style="font-size:.8rem;font-weight:700;color:${getBoxTypeColor(b.box_type)}">${getBoxTypeLabel(b.box_type)}</span>
      ${catBadge} ${statusBadge}
    </div>
    ${b.description ? `<p style="font-size:.85rem;color:var(--text-muted);margin-bottom:12px">${b.description}</p>` : ''}
    <div class="order-detail-row"><span>Prix</span><strong style="color:var(--green-dark);font-size:1.05rem">${formatDA(b.price)}</strong></div>
    ${discountRow}
    <div class="order-detail-row"><span>Quantité disponible</span><strong style="color:${b.quantity < 5 ? 'var(--red)' : 'var(--green)'}">${b.quantity}</strong></div>
    <div class="order-detail-row"><span>Commandes passées</span><strong>${b.orders_count || 0}</strong></div>
    <div class="order-detail-row"><span>Livraison</span><strong style="color:${b.free_delivery ? 'var(--teal)' : 'var(--text-muted)'}">${b.free_delivery ? '<i class="fas fa-truck" style="margin-right:3px"></i>Gratuite' : 'Standard'}</strong></div>
    ${seasonRow}${badgeRow}
    <div style="margin-top:12px">
      <p style="font-weight:700;font-size:.83rem;color:var(--text-muted);margin-bottom:7px;text-transform:uppercase;letter-spacing:.04em">Produits inclus (${b.products.length})</p>
      <ul style="list-style:disc;padding-left:18px;display:flex;flex-direction:column;gap:4px">
        ${b.products.length ? b.products.map(p => `<li style="font-size:.88rem">${p}</li>`).join('') : '<li style="color:var(--text-muted)">Aucun produit listé</li>'}
      </ul>
    </div>
  `;

  // Action buttons
  const actionsDiv = document.getElementById('box-view-actions');
  actionsDiv.innerHTML = `
    <button class="btn btn-outline btn-sm" onclick="openBoxOrders(${b.id})">
      <i class="fas fa-shopping-bag"></i> Commandes
    </button>
    <button class="btn btn-outline btn-sm" onclick="closeModal('box-view-modal');openBoxModal(${b.id})">
      <i class="fas fa-edit"></i> Modifier
    </button>
  `;
  openModal('box-view-modal');
}

async function openBoxOrders(boxId) {
  const b = weeklyBoxes.find(x => x.id === boxId);
  closeModal('box-view-modal');
  document.getElementById('box-orders-title').innerHTML =
    `<i class="fas fa-shopping-bag" style="color:var(--green);margin-right:8px"></i>Commandes — ${b ? b.title : ''}`;
  const tbody = document.getElementById('box-orders-table');
  tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px"><i class="fas fa-spinner fa-spin"></i></td></tr>';
  openModal('box-orders-modal');

  try {
    const res = await apiFetch(API.weeklyBoxes + `?action=get_orders&id=${boxId}`);
    if (!res.success || !res.orders.length) {
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px;color:var(--text-muted)">Aucune commande pour cette boîte.</td></tr>';
      return;
    }
    const statusColors = { new:'var(--orange)', prep:'var(--teal)', delivered:'var(--green)', cancelled:'var(--red)' };
    const statusLabels = { new:'Nouveau', prep:'En préparation', delivered:'Livré', cancelled:'Annulé' };
    tbody.innerHTML = res.orders.map(o => `
      <tr>
        <td><strong>${o.order_number}</strong></td>
        <td>${o.consumer_name}</td>
        <td>${o.consumer_phone || '—'}</td>
        <td>${o.wilaya}</td>
        <td><span style="font-weight:700;color:${statusColors[o.status] || '#888'}">${statusLabels[o.status] || o.status}</span></td>
        <td>${formatDA(o.unit_price)}</td>
        <td style="font-size:.78rem">${(o.created_at || '').slice(0,10)}</td>
      </tr>
    `).join('');
  } catch {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--red)">Erreur de chargement.</td></tr>';
  }
}

/* ═══════════════════════════════════════════
   CLIENT DE LA SEMAINE
═══════════════════════════════════════════ */
const cowStatusLabels = { active:'Désigné', delivered:'Pack livré', cancelled:'Annulé' };
const cowStatusColors = { active:'var(--accent)', delivered:'var(--green)', cancelled:'var(--red)' };

async function loadCowCurrent() {
  const body = document.getElementById('cow-current-body');
  if (!body) return;
  try {
    const res = await apiFetch(API.clientOfWeek + '?action=current');
    if (!res.success) { body.innerHTML = '<p style="color:var(--red)">Erreur de chargement.</p>'; return; }

    if (!res.current) {
      body.innerHTML = `
        <div style="text-align:center;padding:24px 0;color:var(--text-muted)">
          <i class="fas fa-star" style="font-size:2rem;color:#e0e0e0;margin-bottom:10px;display:block"></i>
          <p>Aucun client désigné pour la semaine du <strong>${formatWeekStart(res.week_start)}</strong>.</p>
          <p style="font-size:.82rem;margin-top:4px">Cliquez sur <em>Désigner</em> pour choisir un client.</p>
        </div>`;
      return;
    }

    const c = res.current;
    const statusColor = cowStatusColors[c.status] || '#888';
    const statusLabel = cowStatusLabels[c.status] || c.status;
    body.innerHTML = `
      <div style="display:flex;align-items:flex-start;gap:20px;flex-wrap:wrap">
        ${c.box_image ? `<img src="${c.box_image}" alt="${c.box_title}" style="width:90px;height:90px;object-fit:cover;border-radius:10px;flex-shrink:0">` : ''}
        <div style="flex:1;min-width:200px">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;flex-wrap:wrap">
            <span style="font-size:1.05rem;font-weight:700">${c.user_name}</span>
            <span style="font-size:.78rem;font-weight:700;padding:3px 10px;border-radius:20px;background:${statusColor}20;color:${statusColor}">${statusLabel}</span>
          </div>
          <p style="font-size:.84rem;color:var(--text-muted);margin-bottom:4px"><i class="fas fa-phone" style="width:14px"></i> ${c.user_phone || '—'}</p>
          <p style="font-size:.84rem;color:var(--text-muted);margin-bottom:8px"><i class="fas fa-box" style="width:14px"></i> Pack : <strong style="color:var(--green-dark)">${c.box_title}</strong> — ${formatDA(c.box_price)}</p>
          ${c.note ? `<p style="font-size:.83rem;font-style:italic;color:var(--text-muted);background:var(--bg);padding:6px 10px;border-radius:6px;border-left:3px solid var(--accent)">"${c.note}"</p>` : ''}
          <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap">
            ${c.status === 'active' ? `<button class="btn btn-outline btn-sm" onclick="updateCowStatus(${c.id},'delivered')"><i class="fas fa-check"></i> Marquer livré</button>` : ''}
            ${c.status !== 'cancelled' ? `<button class="btn btn-outline btn-sm" style="color:var(--red);border-color:var(--red)" onclick="updateCowStatus(${c.id},'cancelled')"><i class="fas fa-times"></i> Annuler</button>` : ''}
          </div>
        </div>
      </div>`;
  } catch {
    body.innerHTML = '<p style="color:var(--red)">Erreur de chargement.</p>';
  }
}

function renderCowHistory() {
  const tbody = document.getElementById('cow-history-table');
  if (!tbody) return;
  if (!cowHistory.length) {
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:20px;color:var(--text-muted)">Aucun historique.</td></tr>';
    return;
  }
  tbody.innerHTML = cowHistory.map(c => {
    const sc = cowStatusColors[c.status] || '#888';
    const sl = cowStatusLabels[c.status] || c.status;
    return `<tr>
      <td style="font-size:.82rem">${formatWeekStart(c.week_start)}</td>
      <td><strong>${c.user_name}</strong><br><span style="font-size:.78rem;color:var(--text-muted)">${c.user_phone || ''}</span></td>
      <td>${c.box_title}<br><span style="font-size:.78rem;color:var(--green-dark)">${formatDA(c.box_price)}</span></td>
      <td style="font-size:.82rem;color:var(--text-muted);max-width:160px">${c.note ? `<em>${c.note}</em>` : '—'}</td>
      <td><span style="font-size:.78rem;font-weight:700;padding:2px 9px;border-radius:20px;background:${sc}20;color:${sc}">${sl}</span></td>
      <td>
        <button class="btn btn-outline btn-sm" style="color:var(--red);border-color:var(--red)" onclick="deleteCowEntry(${c.id})" title="Supprimer"><i class="fas fa-trash"></i></button>
      </td>
    </tr>`;
  }).join('');
}

function openCowModal() {
  // Populate consumers
  const uSel = document.getElementById('cow-user-select');
  uSel.innerHTML = '<option value="">— Sélectionner un client —</option>' +
    consumers.map(u => `<option value="${u.id}">${u.name}${u.phone ? ' · ' + u.phone : ''}</option>`).join('');

  // Populate active boxes
  const bSel = document.getElementById('cow-box-select');
  const activeBoxes = weeklyBoxes.filter(b => b.is_active);
  bSel.innerHTML = '<option value="">— Sélectionner un pack —</option>' +
    activeBoxes.map(b => `<option value="${b.id}">${b.title} — ${formatDA(b.price)}</option>`).join('');

  document.getElementById('cow-note').value = '';
  openModal('cow-modal');
}

async function saveCow() {
  const userId = parseInt(document.getElementById('cow-user-select').value);
  const boxId  = parseInt(document.getElementById('cow-box-select').value);
  const note   = document.getElementById('cow-note').value.trim();

  if (!userId || !boxId) { showToast('Veuillez sélectionner un client et un pack.'); return; }

  const res = await apiFetch(API.clientOfWeek, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'assign', user_id: userId, box_id: boxId, note }),
  });

  if (res.success) {
    closeModal('cow-modal');
    // Refresh current display and prepend to history
    await loadCowCurrent();
    const histRes = await apiFetch(API.clientOfWeek + '?action=history');
    if (histRes.success) { cowHistory = histRes.history; renderCowHistory(); }
    showToast('Client de la semaine désigné !');
  } else {
    showToast(res.message || 'Erreur lors de l\'enregistrement.');
  }
}

async function updateCowStatus(id, status) {
  const res = await apiFetch(API.clientOfWeek, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'update_status', id, status }),
  });
  if (res.success) {
    await loadCowCurrent();
    const histRes = await apiFetch(API.clientOfWeek + '?action=history');
    if (histRes.success) { cowHistory = histRes.history; renderCowHistory(); }
    showToast('Statut mis à jour.');
  } else {
    showToast('Erreur lors de la mise à jour.');
  }
}

async function deleteCowEntry(id) {
  if (!confirm('Supprimer cet enregistrement ?')) return;
  const res = await apiFetch(API.clientOfWeek, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'delete', id }),
  });
  if (res.success) {
    cowHistory = cowHistory.filter(c => c.id !== id);
    renderCowHistory();
    showToast('Enregistrement supprimé.');
  }
}

function formatWeekStart(dateStr) {
  if (!dateStr) return '—';
  const d = new Date(dateStr + 'T00:00:00');
  return d.toLocaleDateString('fr-DZ', { day:'numeric', month:'long', year:'numeric' });
}

