<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SoukFreshy — Administration</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<!-- ═══════════ LOGIN ═══════════ -->
<div id="login-screen">
  <div class="login-card">
    <div class="login-logo">
      <img src="../images/logo 1.jpeg" alt="SoukFreshy" onerror="this.style.display='none'">
      <h1>Souk<span>Freshy</span></h1>
      <p>Panneau d'administration</p>
    </div>
    <div class="login-field">
      <label>Identifiant</label>
      <input type="text" id="login-user" placeholder="admin" autocomplete="username">
    </div>
    <div class="login-field">
      <label>Mot de passe</label>
      <input type="password" id="login-pass" placeholder="••••••••" autocomplete="current-password">
    </div>
    <button class="login-btn" onclick="doLogin()">Se connecter</button>
    <div class="login-err" id="login-err">Identifiant ou mot de passe incorrect.</div>
  </div>
</div>

<!-- ═══════════ APP ═══════════ -->
<div id="app" style="display:none;">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <img src="../images/logo 1.jpeg" alt="SoukFreshy" onerror="this.style.display='none'">
      <div class="sidebar-logo-text">
        <h2>Souk<span>Freshy</span></h2>
        <p>Admin Panel</p>
      </div>
    </div>

    <div class="sidebar-section">Principal</div>
    <div class="nav-item active" onclick="showSection('dashboard',this)"><i class="fas fa-chart-pie"></i> Tableau de bord</div>
    <div class="nav-item" onclick="showSection('orders',this)" id="nav-orders"><i class="fas fa-shopping-basket"></i> Commandes <span class="nav-badge" id="new-orders-badge">3</span></div>
    <div class="nav-item" onclick="showSection('products',this)"><i class="fas fa-seedling"></i> Produits</div>
    <div class="nav-item" onclick="showSection('categories',this)"><i class="fas fa-tags"></i> Catégories</div>
    <div class="nav-item" onclick="showSection('customers',this)"><i class="fas fa-users"></i> Utilisateurs</div>
    <div class="nav-item" onclick="showSection('farmers',this)"><i class="fas fa-tractor"></i> Agriculteurs</div>

    <div class="sidebar-section">Finance</div>
    <div class="nav-item" onclick="showSection('commissions',this)"><i class="fas fa-percent"></i> Commissions</div>

    <div class="sidebar-section">Système</div>
    <div class="nav-item" onclick="showSection('admins',this)"><i class="fas fa-user-shield"></i> Administrateurs</div>
    <div class="nav-item" onclick="showSection('notifications',this)" id="nav-notifs"><i class="fas fa-bell"></i> Notifications <span class="nav-badge red" id="notif-badge">5</span></div>
    <div class="nav-item" onclick="showSection('settings',this)"><i class="fas fa-cog"></i> Paramètres</div>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="sidebar-user-avatar">A</div>
        <div class="sidebar-user-info">
          <p>Administrateur</p>
          <span>Super Admin</span>
        </div>
      </div>
      <button class="logout-btn" onclick="doLogout()"><i class="fas fa-sign-out-alt"></i> Déconnexion</button>
    </div>
  </aside>

  <!-- MAIN -->
  <div class="main-wrap">
    <!-- TOPBAR -->
    <div class="topbar">
      <div class="topbar-left">
        <h1 id="page-title">Tableau de bord</h1>
        <p id="page-date"></p>
      </div>
      <div class="topbar-right">
        <div class="topbar-notif" onclick="showSection('notifications',document.querySelector('[onclick*=notifications]'))" title="Notifications">
          <i class="fas fa-bell"></i>
          <div class="notif-dot" id="notif-dot"></div>
        </div>
        <div class="topbar-admin">
          <div class="topbar-admin-avatar">A</div>
          <div>
            <div class="topbar-admin-name">Administrateur</div>
            <div class="topbar-admin-role">Super Admin</div>
          </div>
        </div>
      </div>
    </div>

    <!-- CONTENT -->
    <div class="content">

      <!-- ═══ DASHBOARD ═══ -->
      <div id="sec-dashboard" class="section active">
        <div class="stats-grid">
          <div class="stat-card green">
            <div class="stat-icon green"><i class="fas fa-shopping-basket"></i></div>
            <div class="stat-info">
              <p>Commandes totales</p>
              <h3 id="stat-total-orders">24</h3>
              <small>↑ +4 cette semaine</small>
            </div>
          </div>
          <div class="stat-card orange">
            <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
              <p>En attente</p>
              <h3 id="stat-pending">3</h3>
              <small style="color:var(--orange)">À traiter</small>
            </div>
          </div>
          <div class="stat-card teal">
            <div class="stat-icon teal"><i class="fas fa-money-bill-wave"></i></div>
            <div class="stat-info">
              <p>Chiffre d'affaires</p>
              <h3 id="stat-revenue">48 200 DA</h3>
              <small>↑ +12% vs semaine passée</small>
            </div>
          </div>
          <div class="stat-card accent">
            <div class="stat-icon accent"><i class="fas fa-percentage"></i></div>
            <div class="stat-info">
              <p>Commissions</p>
              <h3 id="stat-commission">4 820 DA</h3>
              <small>Taux: 10%</small>
            </div>
          </div>
          <div class="stat-card purple">
            <div class="stat-icon purple"><i class="fas fa-tractor"></i></div>
            <div class="stat-info">
              <p>Agriculteurs</p>
              <h3 id="stat-farmers">18</h3>
              <small>↑ +2 ce mois</small>
            </div>
          </div>
          <div class="stat-card red">
            <div class="stat-icon red"><i class="fas fa-user-friends"></i></div>
            <div class="stat-info">
              <p>Consommateurs</p>
              <h3 id="stat-consumers">67</h3>
              <small>↑ +9 ce mois</small>
            </div>
          </div>
        </div>

        <div class="grid-2-3">
          <div class="card">
            <div class="card-header">
              <h3><i class="fas fa-chart-bar" style="color:var(--green);margin-right:7px"></i>Ventes hebdomadaires (DA)</h3>
            </div>
            <div class="card-body">
              <div class="chart-bar-wrap">
                <div class="chart-week" id="week-chart"></div>
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-header">
              <h3><i class="fas fa-bell" style="color:var(--orange);margin-right:7px"></i>Notifications récentes</h3>
              <a href="#" onclick="showSection('notifications',document.querySelectorAll('.nav-item')[6]);return false;">Tout voir</a>
            </div>
            <div class="card-body" style="padding-top:6px">
              <div class="notif-list" id="dash-notif-list"></div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3><i class="fas fa-shopping-basket" style="color:var(--green);margin-right:7px"></i>Commandes récentes</h3>
            <a href="#" onclick="showSection('orders',document.querySelectorAll('.nav-item')[1]);return false;">Voir tout</a>
          </div>
          <div class="table-wrap">
            <table>
              <thead><tr>
                <th>#</th><th>Agriculteur</th><th>Wilaya</th><th>Produits</th><th>Montant</th><th>Statut</th><th>Date</th>
              </tr></thead>
              <tbody id="dash-orders-table"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══ ORDERS ═══ -->
      <div id="sec-orders" class="section">
        <div class="filters">
          <input class="search-input" type="text" placeholder="Rechercher agriculteur, wilaya…" oninput="filterOrders()">
          <select class="filter-select" id="order-status-filter" onchange="filterOrders()">
            <option value="">Tous les statuts</option>
            <option value="new">Nouvelle</option>
            <option value="prep">En préparation</option>
            <option value="delivered">Livrée</option>
          </select>
          <select class="filter-select" id="order-wilaya-filter" onchange="filterOrders()">
            <option value="">Toutes wilayas</option>
          </select>
        </div>
        <div class="card">
          <div class="card-header">
            <h3><i class="fas fa-shopping-basket" style="color:var(--green);margin-right:7px"></i>Toutes les commandes</h3>
            <span id="orders-count" style="font-size:.8rem;color:var(--text-muted);font-weight:700;"></span>
          </div>
          <div class="table-wrap">
            <table>
              <thead><tr>
                <th>#ID</th><th>Agriculteur</th><th>Wilaya / Commune</th><th>Produits</th><th>Sous-total</th><th>Commission</th><th>Statut</th><th>Date</th><th>Actions</th>
              </tr></thead>
              <tbody id="orders-table"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══ PRODUCTS ═══ -->
      <div id="sec-products" class="section">
        <div class="filters">
          <input class="search-input" type="text" placeholder="Rechercher produit…" oninput="filterProducts()">
          <select class="filter-select" id="product-cat-filter" onchange="filterProducts()">
            <option value="">Toutes catégories</option>
            <option value="legumes">Légumes</option>
            <option value="fruits">Fruits</option>
            <option value="feuilles">Feuilles</option>
          </select>
        </div>
        <div class="products-grid" id="products-grid"></div>
      </div>

      <!-- ═══ CATEGORIES ═══ -->
      <div id="sec-categories" class="section">
        <div class="card">
          <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
            <h3><i class="fas fa-tags" style="color:var(--accent);margin-right:7px"></i>Catégories</h3>
            <button class="btn btn-primary btn-sm" onclick="openCategoryModal(null)"><i class="fas fa-plus"></i> Ajouter</button>
          </div>
          <div class="table-wrap">
            <table>
              <thead><tr><th>#</th><th>Slug</th><th>Nom (FR)</th><th>Produits actifs</th><th>Actions</th></tr></thead>
              <tbody id="categories-table"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══ CUSTOMERS ═══ -->
      <div id="sec-customers" class="section">
        <div class="stats-grid" style="margin-bottom:20px;grid-template-columns:repeat(auto-fit,minmax(200px,220px))">
          <div class="stat-card teal">
            <div class="stat-icon teal"><i class="fas fa-user"></i></div>
            <div class="stat-info"><p>Consommateurs</p><h3 id="cust-consumers-count">—</h3></div>
          </div>
        </div>
        <div class="card">
          <div class="card-header"><h3><i class="fas fa-trophy" style="color:var(--accent);margin-right:7px"></i>Top clients</h3></div>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Rang</th><th>Client</th><th>Wilaya</th><th>Commandes</th><th>Total</th><th>Actions</th></tr></thead>
              <tbody id="top-customers-table"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══ FARMERS ═══ -->
      <div id="sec-farmers" class="section">
        <div class="stats-grid" style="margin-bottom:20px;grid-template-columns:repeat(auto-fit,minmax(200px,220px))">
          <div class="stat-card purple">
            <div class="stat-icon purple"><i class="fas fa-tractor"></i></div>
            <div class="stat-info"><p>Agriculteurs</p><h3 id="cust-farmers-count">—</h3></div>
          </div>
        </div>
        <div class="card">
          <div class="card-header"><h3><i class="fas fa-tractor" style="color:var(--purple);margin-right:7px"></i>Agriculteurs actifs</h3></div>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Agriculteur</th><th>Wilaya</th><th>Produits</th><th>Statut</th><th>Actions</th></tr></thead>
              <tbody id="farmers-table"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══ COMMISSIONS ═══ -->
      <div id="sec-commissions" class="section">
        <div class="commission-info">
          <i class="fas fa-info-circle"></i>
          <div>
            <strong>Taux de commission actuel : <span id="comm-rate-display">10%</span></strong><br>
            <span>Calculée uniquement sur le sous-total produits — frais de livraison et frais de service exclus.</span>
          </div>
        </div>
        <div class="stats-grid" style="margin-bottom:20px">
          <div class="stat-card accent">
            <div class="stat-icon accent"><i class="fas fa-coins"></i></div>
            <div class="stat-info"><p>Commission totale</p><h3 id="total-comm-stat">4 820 DA</h3><small>Cette semaine</small></div>
          </div>
          <div class="stat-card green">
            <div class="stat-icon green"><i class="fas fa-file-invoice-dollar"></i></div>
            <div class="stat-info"><p>Commandes facturées</p><h3 id="comm-invoiced-count">—</h3><small>Total</small></div>
          </div>
        </div>
        <div class="card">
          <div class="card-header"><h3><i class="fas fa-list" style="color:var(--accent);margin-right:7px"></i>Détail des commissions</h3></div>
          <div class="table-wrap">
            <table>
              <thead><tr><th>#ID</th><th>Agriculteur</th><th>Sous-total</th><th>Commission (10%)</th><th>Livraison</th><th>Service</th><th>Total payé</th><th>Date</th></tr></thead>
              <tbody id="commission-table"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══ NOTIFICATIONS ═══ -->
      <div id="sec-notifications" class="section">
        <div class="grid-2">
          <div class="card">
            <div class="card-header">
              <h3><i class="fas fa-bell" style="color:var(--orange);margin-right:7px"></i>Toutes les notifications</h3>
              <button class="btn btn-outline btn-sm" onclick="markAllRead()">Tout marquer lu</button>
            </div>
            <div class="card-body" id="all-notifs-list"></div>
          </div>
          <div class="card">
            <div class="card-header"><h3><i class="fas fa-chart-line" style="color:var(--teal);margin-right:7px"></i>Résumé</h3></div>
            <div class="card-body">
              <div class="stats-grid" style="grid-template-columns:1fr 1fr;gap:12px">
                <div class="stat-card green" style="padding:14px">
                  <div class="stat-icon green" style="width:36px;height:36px;font-size:.85rem"><i class="fas fa-shopping-basket"></i></div>
                  <div class="stat-info"><p style="font-size:.68rem">Nouvelles cmds</p><h3 id="notif-stat-orders" style="font-size:1.2rem">0</h3></div>
                </div>
                <div class="stat-card teal" style="padding:14px">
                  <div class="stat-icon teal" style="width:36px;height:36px;font-size:.85rem"><i class="fas fa-user-plus"></i></div>
                  <div class="stat-info"><p style="font-size:.68rem">Nvx utilisateurs</p><h3 id="notif-stat-users" style="font-size:1.2rem">0</h3></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══ SETTINGS ═══ -->
      <div id="sec-settings" class="section">
        <div class="settings-grid">
          <div class="settings-card">
            <h3><i class="fas fa-percent"></i> Commission plateforme</h3>
            <div class="form-group">
              <label>Taux de commission (%)</label>
              <div class="input-addon">
                <span>%</span>
                <input type="number" id="set-commission" value="10" min="0" max="100" step="0.5">
              </div>
            </div>
            <p style="font-size:.77rem;color:var(--text-muted);margin-top:-8px;margin-bottom:12px">Appliqué sur le sous-total produits uniquement.</p>
            <button class="save-btn" onclick="saveSetting('commission')"><i class="fas fa-save" style="margin-right:7px"></i>Enregistrer</button>
          </div>

          <div class="settings-card">
            <h3><i class="fas fa-truck"></i> Frais de livraison</h3>
            <div class="form-group">
              <label>Frais fixe (DA)</label>
              <div class="input-addon">
                <span>DA</span>
                <input type="number" id="set-delivery" value="200" min="0">
              </div>
            </div>
            <div class="form-group">
              <label>Livraison gratuite au-dessus de (DA)</label>
              <div class="input-addon">
                <span>DA</span>
                <input type="number" id="set-free-delivery" value="2000" min="0">
              </div>
            </div>
            <button class="save-btn" onclick="saveSetting('delivery')"><i class="fas fa-save" style="margin-right:7px"></i>Enregistrer</button>
          </div>

          <div class="settings-card">
            <h3><i class="fas fa-hand-holding-usd"></i> Frais de service</h3>
            <div class="form-group">
              <label>Frais de service fixe (DA)</label>
              <div class="input-addon">
                <span>DA</span>
                <input type="number" id="set-service" value="50" min="0">
              </div>
            </div>
            <div class="form-group">
              <label>Mode de calcul</label>
              <select id="set-service-mode">
                <option value="fixed">Fixe</option>
                <option value="percent">Pourcentage</option>
              </select>
            </div>
            <button class="save-btn" onclick="saveSetting('service')"><i class="fas fa-save" style="margin-right:7px"></i>Enregistrer</button>
          </div>

          <div class="settings-card">
            <h3><i class="fas fa-shield-alt"></i> Sécurité admin</h3>
            <div class="form-group">
              <label>Nom d'utilisateur admin</label>
              <input type="text" id="set-admin-user" value="admin">
            </div>
            <div class="form-group">
              <label>Nouveau mot de passe</label>
              <input type="password" id="set-admin-pass" placeholder="Laisser vide pour ne pas changer">
            </div>
            <button class="save-btn" onclick="saveSetting('security')"><i class="fas fa-save" style="margin-right:7px"></i>Enregistrer</button>
          </div>
        </div>
      </div>

      <!-- ═══ ADMINS ═══ -->
      <div id="sec-admins" class="section">
        <div class="card">
          <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
            <h3><i class="fas fa-user-shield" style="color:var(--teal);margin-right:7px"></i>Administrateurs</h3>
            <button class="btn btn-primary btn-sm" onclick="openAdminModal(null)"><i class="fas fa-plus"></i> Ajouter</button>
          </div>
          <div class="table-wrap">
            <table>
              <thead><tr><th>#</th><th>Nom d'utilisateur</th><th>Créé le</th><th>Actions</th></tr></thead>
              <tbody id="admins-table"></tbody>
            </table>
          </div>
        </div>
      </div>

    </div><!-- /content -->
  </div><!-- /main-wrap -->
</div><!-- /app -->

<!-- ORDER DETAIL MODAL -->
<div class="modal-backdrop" id="order-modal">
  <div class="modal">
    <h2><i class="fas fa-shopping-basket" style="color:var(--green);margin-right:8px"></i>Détail commande</h2>
    <div id="order-modal-body"></div>
    <div class="modal-actions">
      <button class="btn btn-outline" onclick="closeModal('order-modal')">Fermer</button>
      <button class="btn btn-primary" id="order-modal-next" onclick="advanceOrder()"><i class="fas fa-arrow-right"></i> Avancer le statut</button>
    </div>
  </div>
</div>

<!-- PRODUCT EDIT MODAL -->
<div class="modal-backdrop" id="product-modal">
  <div class="modal">
    <h2 id="product-modal-title">Modifier le produit</h2>
    <div class="form-group"><label>Nom du produit</label><input type="text" id="pm-name"></div>
    <div class="form-group"><label>Catégorie</label>
      <select id="pm-cat">
        <option value="legumes">Légumes</option>
        <option value="fruits">Fruits</option>
        <option value="feuilles">Feuilles</option>
      </select>
    </div>
    <div class="form-group"><label>Prix (DA/kg)</label><input type="number" id="pm-price"></div>
    <div class="form-group"><label>Agriculteur</label><input type="text" id="pm-farmer"></div>
    <div class="form-group"><label>Wilaya</label><input type="text" id="pm-wilaya"></div>
    <div class="form-group"><label>Stock (kg)</label><input type="number" id="pm-stock"></div>
    <div class="modal-actions">
      <button class="btn btn-outline" onclick="closeModal('product-modal')">Annuler</button>
      <button class="btn btn-primary" onclick="saveProduct()"><i class="fas fa-save"></i> Enregistrer</button>
    </div>
  </div>
</div>

<!-- CATEGORY ADD/EDIT MODAL -->
<div class="modal-backdrop" id="category-modal">
  <div class="modal" style="max-width:420px">
    <h2 id="cat-modal-title"><i class="fas fa-tags" style="color:var(--accent);margin-right:8px"></i>Catégorie</h2>
    <div class="form-group">
      <label>Slug <span style="font-size:.75rem;color:var(--text-muted)">(identifiant unique, ex: vegetables)</span></label>
      <input type="text" id="cat-slug" placeholder="ex: vegetables" autocomplete="off">
    </div>
    <div class="form-group"><label>Nom affiché (FR)</label><input type="text" id="cat-name" placeholder="ex: Légumes"></div>
    <div class="modal-actions">
      <button class="btn btn-outline" onclick="closeModal('category-modal')">Annuler</button>
      <button class="btn btn-primary" onclick="saveCategory()"><i class="fas fa-save"></i> Enregistrer</button>
    </div>
  </div>
</div>

<!-- ADMIN ADD/EDIT MODAL -->
<div class="modal-backdrop" id="admin-modal">
  <div class="modal" style="max-width:400px">
    <h2 id="admin-modal-title"><i class="fas fa-user-shield" style="color:var(--teal);margin-right:8px"></i>Administrateur</h2>
    <div class="form-group"><label>Nom d'utilisateur</label><input type="text" id="adm-username" placeholder="ex: superadmin" autocomplete="off"></div>
    <div class="form-group"><label id="adm-pass-label">Mot de passe</label><input type="password" id="adm-password" placeholder="Minimum 6 caractères" autocomplete="new-password"></div>
    <div class="modal-actions">
      <button class="btn btn-outline" onclick="closeModal('admin-modal')">Annuler</button>
      <button class="btn btn-primary" onclick="saveAdmin()"><i class="fas fa-save"></i> Enregistrer</button>
    </div>
  </div>
</div>

<!-- IMAGE UPDATE MODAL -->
<div class="modal-backdrop" id="img-modal">
  <div class="modal" style="max-width:400px">
    <h2><i class="fas fa-camera" style="color:var(--teal);margin-right:8px"></i>Changer l'image</h2>
    <p id="img-modal-name" style="font-weight:700;margin-bottom:12px;color:var(--text-muted)"></p>
    <div style="text-align:center;margin-bottom:16px">
      <img id="img-modal-preview" src="" alt="" style="max-height:180px;max-width:100%;border-radius:10px;object-fit:cover;border:2px solid var(--border);display:none">
      <div id="img-modal-fallback" style="height:120px;display:flex;align-items:center;justify-content:center;font-size:3rem;background:var(--bg-card);border-radius:10px;border:2px dashed var(--border)"></div>
    </div>
    <div class="form-group">
      <label>Nouvelle image (jpg / png / webp — max 3 Mo)</label>
      <input type="file" id="img-file" accept="image/jpeg,image/png,image/webp" onchange="previewImgFile(this)">
    </div>
    <div class="modal-actions">
      <button class="btn btn-outline" onclick="closeModal('img-modal')">Annuler</button>
      <button class="btn btn-primary" onclick="saveProductImage()"><i class="fas fa-upload"></i> Enregistrer</button>
    </div>
  </div>
</div>

<!-- USER DETAIL MODAL -->
<div class="modal-backdrop" id="user-modal">
  <div class="modal">
    <h2 id="user-modal-title"><i class="fas fa-user-circle" style="color:var(--teal);margin-right:8px"></i>Détail utilisateur</h2>
    <div id="user-modal-body"></div>
    <div class="modal-actions">
      <button class="btn btn-outline" onclick="closeModal('user-modal')">Fermer</button>
      <button class="btn" id="user-modal-toggle-btn" onclick="userModalToggle()"></button>
    </div>
  </div>
</div>

<!-- TOAST -->
<div id="toast"><i class="fas fa-check-circle"></i> <span id="toast-msg"></span></div>

<script src="admin.js"></script>
</body>
</html>
