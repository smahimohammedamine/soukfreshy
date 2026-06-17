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
<div id="login-screen" style="display:none;">
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
    <div class="nav-item" onclick="showSection('weekly-boxes',this)"><i class="fas fa-box-open"></i> Boîtes <span class="nav-badge" id="boxes-badge" style="display:none"></span></div>
    <div class="nav-item" onclick="showSection('client-of-week',this)"><i class="fas fa-star"></i> Client de la semaine</div>
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
                <th>#</th><th>Client</th><th>Agriculteur(s)</th><th>Wilaya</th><th>Produits</th><th>Montant</th><th>Statut</th><th>Date</th>
              </tr></thead>
              <tbody id="dash-orders-table"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══ ORDERS ═══ -->
      <div id="sec-orders" class="section">
        <div class="filters">
          <input class="search-input" type="text" placeholder="Rechercher client, agriculteur, wilaya…" oninput="filterOrders()">
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
            <div style="display:flex;align-items:center;gap:12px">
              <span id="orders-count" style="font-size:.8rem;color:var(--text-muted);font-weight:700;"></span>
              <button id="orders-refresh-btn" class="btn btn-outline btn-sm" onclick="refreshOrders()">
                <i class="fas fa-rotate-right"></i> Actualiser
              </button>
            </div>
          </div>
          <div class="table-wrap">
            <table>
              <thead><tr>
                <th>#ID</th><th>Client</th><th>Agriculteur(s)</th><th>Wilaya / Commune</th><th>Produits</th><th>Sous-total</th><th>Commission</th><th>Statut</th><th>Date / Heure</th><th>Actions</th>
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

      <!-- ═══ BOÎTES (Weekly + Season) ═══ -->
      <div id="sec-weekly-boxes" class="section">

        <!-- Stats row -->
        <div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));margin-bottom:18px" id="boxes-stats-row">
          <div class="stat-card orange">
            <div class="stat-icon orange"><i class="fas fa-box"></i></div>
            <div class="stat-info"><p>Total boîtes</p><h3 id="bstat-total">—</h3></div>
          </div>
          <div class="stat-card green">
            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info"><p>Actives</p><h3 id="bstat-active">—</h3></div>
          </div>
          <div class="stat-card teal">
            <div class="stat-icon teal"><i class="fas fa-calendar-week"></i></div>
            <div class="stat-info"><p>Hebdo</p><h3 id="bstat-weekly">—</h3></div>
          </div>
          <div class="stat-card purple">
            <div class="stat-icon purple"><i class="fas fa-leaf"></i></div>
            <div class="stat-info"><p>Saisonnières</p><h3 id="bstat-season">—</h3></div>
          </div>
          <div class="stat-card accent">
            <div class="stat-icon accent"><i class="fas fa-shopping-bag"></i></div>
            <div class="stat-info"><p>Commandes boîtes</p><h3 id="bstat-orders">—</h3></div>
          </div>
          <div class="stat-card green">
            <div class="stat-icon green"><i class="fas fa-coins"></i></div>
            <div class="stat-info"><p>Revenu boîtes</p><h3 id="bstat-revenue">—</h3></div>
          </div>
        </div>

        <!-- Tab bar -->
        <div class="card">
          <div class="card-header" style="flex-wrap:wrap;gap:10px">
            <div style="display:flex;gap:6px">
              <button class="btn btn-primary btn-sm" id="box-tab-weekly" onclick="switchBoxTab('weekly')">
                <i class="fas fa-calendar-week"></i> Hebdomadaires
              </button>
              <button class="btn btn-outline btn-sm" id="box-tab-season" onclick="switchBoxTab('season')">
                <i class="fas fa-leaf"></i> Saisonnières
              </button>
              
            </div>
            <div style="display:flex;gap:6px;margin-left:auto">
              <button class="btn btn-outline btn-sm" id="box-bulk-on"  onclick="bulkToggleBoxes(1)" title="Tout activer"><i class="fas fa-eye"></i></button>
              <button class="btn btn-outline btn-sm" id="box-bulk-off" onclick="bulkToggleBoxes(0)" title="Tout désactiver"><i class="fas fa-eye-slash"></i></button>
              <button class="btn btn-primary btn-sm" onclick="openBoxModal(null)"><i class="fas fa-plus"></i> Nouvelle boîte</button>
            </div>
          </div>

          <!-- Weekly boxes grid -->
          <div id="weekly-boxes-grid" class="products-grid"></div>

          <!-- Season boxes grid -->
          <div id="season-boxes-grid" class="products-grid" style="display:none"></div>

        </div>
      </div>

      <!-- ═══ CLIENT DE LA SEMAINE ═══ -->
      <div id="sec-client-of-week" class="section">

        <!-- Current week card -->
        <div class="card" style="margin-bottom:20px">
          <div class="card-header" style="justify-content:space-between">
            <h3><i class="fas fa-star" style="color:var(--accent);margin-right:7px"></i>Client de la semaine en cours</h3>
            <div style="display:flex;gap:8px">
              <button class="btn btn-outline btn-sm" onclick="openCowHistoryModal()">
                <i class="fas fa-history"></i> Historique
              </button>
              <button class="btn btn-primary btn-sm" onclick="openCowModal()">
                <i class="fas fa-plus"></i> Désigner
              </button>
            </div>
          </div>
          <div class="card-body" id="cow-current-body" style="padding:20px">
            <p style="color:var(--text-muted);font-size:.9rem">Chargement…</p>
          </div>
        </div>

        <!-- Weekly ranking card -->
        <div class="card">
          <div class="card-header">
            <h3><i class="fas fa-trophy" style="color:var(--accent);margin-right:7px"></i>Classement clients — Semaine en cours</h3>
          </div>
          <div id="cow-ranking-body" style="padding:0 4px 8px">
            <p style="color:var(--text-muted);font-size:.9rem;padding:20px">Chargement…</p>
          </div>
        </div>
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
              <thead><tr><th>Agriculteur</th><th>Wilaya / Commune</th><th>Produits</th><th>Statut</th><th>Actions</th></tr></thead>
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
          <div class="settings-card" style="grid-column:1/-1">
            <h3><i class="fas fa-palette"></i> Apparence du site</h3>

            <div class="form-group">
              <label>Logo du site</label>
              <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
                <img id="set-logo-preview" src="../images/logo 1.jpeg" alt="logo"
                     style="width:60px;height:60px;border-radius:50%;object-fit:cover;border:2px solid var(--border,#e5e7eb);background:#f3f4f6">
                <div style="flex:1;min-width:220px">
                  <input type="url" id="set-logo-url" placeholder="https://… (URL de l'image)" oninput="previewLogo(this.value)">
                  <input type="file" id="set-logo-file" accept="image/*" style="margin-top:8px" onchange="onLogoFile(this)">
                </div>
              </div>
            </div>

            <hr style="border:none;border-top:1px solid var(--border,#eef0f3);margin:16px 0">

            <div class="form-group">
              <label>Section « Bienvenue » — type de média</label>
              <select id="set-welcome-type" onchange="renderWelcomePreview()">
                <option value="video">Vidéo</option>
                <option value="image">Image</option>
              </select>
            </div>
            <div class="form-group">
              <label>Média « Bienvenue »</label>
              <input type="url" id="set-welcome-url" placeholder="https://… (URL de l'image ou de la vidéo)" oninput="renderWelcomePreview()">
              <input type="file" id="set-welcome-file" accept="image/*,video/*" style="margin-top:8px" onchange="onWelcomeFile(this)">
              <div id="set-welcome-preview" style="margin-top:12px;border-radius:12px;overflow:hidden;max-width:360px"></div>
            </div>
            <p style="font-size:.77rem;color:var(--text-muted);margin-top:-4px;margin-bottom:12px">
              Choisissez un fichier <em>ou</em> collez une URL. Le nom « SoukFreshy » reste inchangé.
              Les vidéos volumineuses peuvent dépasser la limite d'upload du serveur — préférez alors une URL.
            </p>
            <button class="save-btn" onclick="saveSetting('branding')"><i class="fas fa-save" style="margin-right:7px"></i>Enregistrer l'apparence</button>
          </div>

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
                <input type="number" id="set-free-delivery" value="5000" min="0">
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
  <div class="modal" style="max-width:720px">
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

<!-- BOX CREATE / EDIT MODAL -->
<div class="modal-backdrop" id="box-modal">
  <div class="modal" style="max-width:600px">
    <h2 id="box-modal-title"><i class="fas fa-box-open" style="color:var(--accent);margin-right:8px"></i>Nouvelle Boîte</h2>

    <!-- Image + Category row -->
    <div style="display:flex;gap:14px;align-items:flex-start;margin-bottom:16px">
      <div style="flex-shrink:0;width:90px;height:90px;border-radius:10px;overflow:hidden;border:2px solid var(--border);background:var(--bg-card);display:flex;align-items:center;justify-content:center">
        <img id="box-img-preview" src="" alt="" style="width:100%;height:100%;object-fit:cover;display:none">
        <i id="box-img-fallback" class="fas fa-box-open" style="font-size:2rem;color:var(--text-muted)"></i>
      </div>
      <div style="flex:1;display:flex;flex-direction:column;gap:8px">
        <div class="form-group" style="margin-bottom:0">
          <label>Image (jpg/png/webp — max 3 Mo)</label>
          <input type="file" id="box-img-file" accept="image/jpeg,image/png,image/webp" onchange="previewBoxImg(this)">
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label>Catégorie <span style="color:var(--red)">*</span></label>
          <select id="box-category" onchange="onBoxCategoryChange()" style="width:100%">
            <option value="weekly">Hebdomadaire</option>
            <option value="season">Saisonnière</option>
          </select>
        </div>
      </div>
    </div>

    <div class="form-group"><label>Titre <span style="color:var(--red)">*</span></label><input type="text" id="box-title" placeholder="ex: Boîte Fraîcheur du Marché"></div>
    <div class="form-group"><label>Description</label><textarea id="box-desc" rows="2" style="resize:vertical" placeholder="Courte description…"></textarea></div>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px">
      <div class="form-group">
        <label>Type <span style="color:var(--red)">*</span></label>
        <select id="box-type"><!-- populated --></select>
      </div>
      <div class="form-group">
        <label>Prix (DA) <span style="color:var(--red)">*</span></label>
        <input type="number" id="box-price" min="0" step="50" placeholder="1500">
      </div>
      <div class="form-group">
        <label>Prix barré (DA)</label>
        <input type="number" id="box-original-price" min="0" step="50" placeholder="2000">
      </div>
      <div class="form-group">
        <label>Quantité dispo</label>
        <input type="number" id="box-qty" min="0" placeholder="20">
      </div>
    </div>

    <div class="form-group">
      <label>Badge <span style="font-size:.75rem;color:var(--text-muted)">(affiché sur la carte)</span></label>
      <input type="text" id="box-badge" placeholder="ex: Nouveau, Populaire, Fin de série…" maxlength="50">
    </div>

    <!-- Season-specific fields (hidden for weekly) -->
    <div id="box-season-fields" style="border:1px solid var(--border);border-radius:8px;padding:12px;background:var(--bg-card);margin-bottom:14px;display:none">
      <p style="font-size:.78rem;font-weight:700;color:var(--purple);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px"><i class="fas fa-leaf" style="margin-right:5px"></i>Options saisonnières</p>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
        <div class="form-group" style="margin-bottom:0">
          <label>Saison</label>
          <select id="box-season">
            <option value="">— Toutes saisons —</option>
            <option value="spring">Printemps</option>
            <option value="summer">Été</option>
            <option value="autumn">Automne</option>
            <option value="winter">Hiver</option>
          </select>
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label>Disponible à partir du</label>
          <input type="date" id="box-available-from">
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label>Disponible jusqu'au</label>
          <input type="date" id="box-available-until">
        </div>
      </div>
    </div>

    <div class="form-group">
      <label>Produits inclus <span style="font-size:.75rem;color:var(--text-muted)">(sélectionner dans la liste)</span></label>
      <div style="border:1px solid var(--border);border-radius:8px;background:var(--bg-card)">
        <div style="padding:8px 8px 4px">
          <input type="text" id="box-products-search" placeholder="Filtrer les produits…"
                 style="width:100%;padding:6px 10px;border:1px solid var(--border);border-radius:6px;background:var(--bg);font-size:.82rem;box-sizing:border-box"
                 oninput="filterBoxProducts()">
        </div>
        <div id="box-products-list" style="max-height:180px;overflow-y:auto;padding:4px 8px 8px;display:flex;flex-direction:column;gap:2px">
          <!-- populated dynamically -->
        </div>
      </div>
    </div>

    <div style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:16px">
      <div style="display:flex;align-items:center;gap:10px">
        <label style="margin:0;font-size:.85rem;font-weight:600">Disponible à la vente</label>
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
          <input type="checkbox" id="box-active" checked style="width:18px;height:18px;accent-color:var(--green)">
          <span id="box-active-label" style="font-size:.82rem;color:var(--text-muted)">Active</span>
        </label>
      </div>
      <div style="display:flex;align-items:center;gap:10px">
        <label style="margin:0;font-size:.85rem;font-weight:600">Livraison gratuite</label>
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
          <input type="checkbox" id="box-free-delivery" style="width:18px;height:18px;accent-color:var(--teal)">
          <span id="box-free-delivery-label" style="font-size:.82rem;color:var(--text-muted)">Non</span>
        </label>
      </div>
    </div>

    <div class="modal-actions">
      <button class="btn btn-outline" onclick="closeModal('box-modal')">Annuler</button>
      <button class="btn btn-primary" onclick="saveBox()"><i class="fas fa-save"></i> Enregistrer</button>
    </div>
  </div>
</div>

<!-- BOX DETAIL VIEW MODAL (admin) -->
<div class="modal-backdrop" id="box-view-modal">
  <div class="modal" style="max-width:520px">
    <div id="box-view-body"></div>
    <div class="modal-actions" style="justify-content:space-between">
      <div style="display:flex;gap:8px" id="box-view-actions"></div>
      <button class="btn btn-outline" onclick="closeModal('box-view-modal')">Fermer</button>
    </div>
  </div>
</div>

<!-- BOX ORDERS MODAL -->
<div class="modal-backdrop" id="box-orders-modal">
  <div class="modal" style="max-width:700px">
    <h2 id="box-orders-title"><i class="fas fa-shopping-bag" style="color:var(--green);margin-right:8px"></i>Commandes</h2>
    <div class="table-wrap" style="max-height:400px;overflow-y:auto">
      <table>
        <thead>
          <tr>
            <th>Commande</th>
            <th>Client</th>
            <th>Téléphone</th>
            <th>Wilaya</th>
            <th>Statut</th>
            <th>Prix</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody id="box-orders-table"></tbody>
      </table>
    </div>
    <div class="modal-actions">
      <button class="btn btn-outline" onclick="closeModal('box-orders-modal')">Fermer</button>
    </div>
  </div>
</div>


<!-- CLIENT OF WEEK MODAL -->
<div class="modal-backdrop" id="cow-modal">
  <div class="modal" style="max-width:480px">
    <h2><i class="fas fa-star" style="color:var(--accent);margin-right:8px"></i>Désigner le client de la semaine</h2>
    <div class="form-group">
      <label>Client <span style="color:var(--red)">*</span></label>
      <select id="cow-user-select" style="width:100%">
        <option value="">— Sélectionner un client —</option>
      </select>
    </div>
    <div class="form-group">
      <label>Pack à offrir <span style="color:var(--red)">*</span></label>
      <select id="cow-box-select" style="width:100%">
        <option value="">— Sélectionner un pack —</option>
      </select>
    </div>
    <div class="form-group">
      <label>Note <span style="font-size:.75rem;color:var(--text-muted)">(optionnel)</span></label>
      <textarea id="cow-note" rows="2" placeholder="Message pour le client…" style="width:100%;resize:vertical"></textarea>
    </div>
    <div class="modal-actions">
      <button class="btn btn-outline" onclick="closeModal('cow-modal')">Annuler</button>
      <button class="btn btn-primary" onclick="saveCow()"><i class="fas fa-check"></i> Confirmer</button>
    </div>
  </div>
</div>

<!-- CLIENT OF WEEK — HISTORY MODAL -->
<div class="modal-backdrop" id="cow-history-modal">
  <div class="modal" style="max-width:740px">
    <h2><i class="fas fa-history" style="color:var(--text-muted);margin-right:8px"></i>Historique — Client de la semaine</h2>
    <div class="table-wrap" style="max-height:60vh;overflow-y:auto">
      <table>
        <thead><tr>
          <th>Semaine</th><th>Client</th><th>Pack attribué</th><th>Note</th><th>Statut</th><th>Actions</th>
        </tr></thead>
        <tbody id="cow-history-table"></tbody>
      </table>
    </div>
    <div class="modal-actions">
      <button class="btn btn-outline" onclick="closeModal('cow-history-modal')">Fermer</button>
    </div>
  </div>
</div>

<!-- TOAST -->
<div id="toast"><i class="fas fa-check-circle"></i> <span id="toast-msg"></span></div>

<script src="admin.js"></script>
</body>
</html>
