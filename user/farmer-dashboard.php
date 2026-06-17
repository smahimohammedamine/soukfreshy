<?php
session_start();
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    $root = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
    header('Location: ' . $root . '/?login=farmer');
    exit;
}
$farmerName   = htmlspecialchars($_SESSION['full_name']);
$farmerId     = (int) $_SESSION['user_id'];
$farmerWilaya = htmlspecialchars($_SESSION['wilaya'] ?? '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SoukFreshy — Tableau de bord agriculteur</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="farmer-dashboard.css">
</head>
<body>

  <div id="farmer-data"
    data-id="<?= $farmerId ?>"
    data-name="<?= $farmerName ?>"
    data-wilaya="<?= $farmerWilaya ?>"
    style="display:none;"></div>

  <div class="fd-layout">

    <!-- ── Sidebar ── -->
    <aside class="fd-sidebar">
      <div class="fd-sidebar-brand">
        <div class="fd-brand-icon"><img src="../images/logo 1.jpeg" alt="SoukFreshy"></div>
        <div>
          <p class="fd-kicker">SoukFreshy</p>
          <p class="fd-sidebar-name"><?= $farmerName ?></p>
        </div>
      </div>

      <nav class="fd-nav">
        <button class="fd-nav-item" data-page="add">
          <i class="fas fa-plus-circle"></i>
          <span>Ajouter un produit</span>
        </button>
        <button class="fd-nav-item" data-page="products">
          <i class="fas fa-box-open"></i>
          <span>Mes produits</span>
        </button>
        <button class="fd-nav-item" data-page="orders">
          <i class="fas fa-basket-shopping"></i>
          <span>Commandes reçues</span>
        </button>
        <button class="fd-nav-item" data-page="profile">
          <i class="fas fa-user-circle"></i>
          <span>Mon profil</span>
        </button>
      </nav>

      <div class="fd-sidebar-footer">
        <?php if ($farmerWilaya): ?>
        <p class="fd-sidebar-wilaya"><i class="fas fa-location-dot"></i> <?= $farmerWilaya ?></p>
        <?php endif; ?>
        <button id="logout-btn" class="fd-logout-btn">
          <i class="fas fa-arrow-right-from-bracket"></i> Déconnexion
        </button>
      </div>
    </aside>

    <!-- ── Content ── -->
    <main class="fd-content">

      <!-- Page: Ajouter un produit -->
      <div class="fd-page" id="page-add">
        <section class="fd-card">
          <div class="fd-card-head">
            <div class="fd-card-badge"><i class="fas fa-plus"></i> Nouveau produit</div>
            <h2>Ajouter un produit</h2>
            <p>Renseignez les informations ci-dessous et publiez votre offre.</p>
          </div>

          <form id="product-form" class="fd-form" novalidate>
            <input type="hidden" id="edit-id">

            <div class="fd-section">
              <div class="fd-section-title"><i class="fas fa-leaf"></i> Informations produit</div>
              <div class="fd-grid">
                <label class="fd-full">Nom du produit
                  <input id="product-name" type="text" placeholder="Ex : Tomates cerises bio" required>
                </label>
                <label>Catégorie
                  <select id="product-category" required>
                    <option value="vegetables">Légumes</option>
                    <option value="fruits">Fruits</option>
                    <option value="herbs">Herbes aromatiques</option>
                  </select>
                </label>
                <label>Type de tarification
                  <select id="product-pricing-type" required>
                    <option value="kg">Par kg</option>
                    <option value="bouquet">Par bouquet</option>
                    <option value="custom">Personnalisé</option>
                  </select>
                </label>
                <label id="pricing-label-wrap" class="fd-full hidden">Libellé de quantité
                  <input id="product-pricing-label" type="text" placeholder="Ex : caisse de 5 kg">
                </label>
              </div>
            </div>

            <div class="fd-section">
              <div class="fd-section-title"><i class="fas fa-coins"></i> Tarification &amp; stock</div>
              <div class="fd-grid">
                <label>Prix unitaire
                  <div class="fd-input-group">
                    <input id="product-price" type="number" min="1" placeholder="120" required>
                    <span class="fd-input-tag">DA</span>
                  </div>
                </label>
                <label>Quantité disponible
                  <input id="product-qty" type="number" min="0" placeholder="50" required>
                </label>
              </div>
            </div>

            <div class="fd-section">
              <div class="fd-section-title"><i class="fas fa-align-left"></i> Description</div>
              <textarea id="product-description" rows="3" placeholder="Origine, qualité, conseils d'utilisation…"></textarea>
            </div>

            <div class="fd-section">
              <div class="fd-section-title"><i class="fas fa-image"></i> Photo du produit</div>
              <div class="fd-grid">
                <label class="fd-full">URL de l'image
                  <input id="product-image-url" type="url" placeholder="https://example.com/image.jpg">
                </label>
                <label class="fd-upload-zone fd-full" for="product-image-file">
                  <i class="fas fa-cloud-arrow-up"></i>
                  <span id="upload-zone-text">Ou choisir depuis l'appareil</span>
                  <input id="product-image-file" type="file" accept="image/*">
                </label>
                <div id="image-preview-wrap" class="fd-full hidden">
                  <img id="image-preview" src="" alt="aperçu">
                </div>
              </div>
            </div>

            <div class="fd-actions">
              <button type="submit" id="save-btn" class="fd-primary-btn">
                <i class="fas fa-floppy-disk"></i> Ajouter le produit
              </button>
              <button type="button" id="cancel-edit-btn" class="fd-ghost-btn hidden">
                <i class="fas fa-xmark"></i> Annuler
              </button>
            </div>
          </form>

          <p id="fd-form-message" class="fd-message hidden"></p>
        </section>
      </div>

      <!-- Page: Mes produits -->
      <div class="fd-page" id="page-products">
        <section class="fd-card">
          <div class="fd-card-head">
            <div class="fd-card-badge"><i class="fas fa-box-open"></i> Stock</div>
            <h2>Mes produits</h2>
            <p>Modifiez ou supprimez vos offres en un clic.</p>
          </div>
          <div id="farmer-product-list" class="fd-product-list"></div>
        </section>
      </div>

      <!-- Page: Commandes reçues -->
      <div class="fd-page" id="page-orders">
        <section class="fd-card">
          <div class="fd-card-head">
            <div class="fd-card-badge"><i class="fas fa-shopping-basket"></i> Commandes</div>
            <h2>Commandes reçues</h2>
            <p>Détail des achats effectués sur vos produits, avec les coordonnées de chaque client.</p>
          </div>
          <div id="farmer-orders-list" class="fd-orders-list"></div>
        </section>
      </div>

      <!-- Page: Mon profil -->
      <div class="fd-page" id="page-profile">
        <section class="fd-card">
          <div class="fd-card-head">
            <div class="fd-card-badge"><i class="fas fa-user"></i> Profil</div>
            <h2>Mon profil agriculteur</h2>
            <p>Vos informations personnelles et statistiques de vente.</p>
          </div>

          <div class="fd-profile-wrap">
            <div class="fd-profile-hero">
              <div class="fd-profile-avatar"><?= mb_strtoupper(mb_substr($farmerName, 0, 2, 'UTF-8')) ?></div>
              <div class="fd-profile-details">
                <h3 class="fd-profile-name"><?= $farmerName ?></h3>
                <span class="fd-profile-badge"><i class="fas fa-leaf"></i> Agriculteur</span>
                <?php if ($farmerWilaya): ?>
                <p class="fd-profile-loc"><i class="fas fa-location-dot"></i> <?= $farmerWilaya ?></p>
                <?php endif; ?>
                <p class="fd-profile-id">Identifiant : <code>#<?= $farmerId ?></code></p>
              </div>
            </div>

            <div class="fd-stats-grid">
              <div class="fd-stat-card">
                <div class="fd-stat-icon"><i class="fas fa-boxes-stacked"></i></div>
                <span class="fd-stat-value" id="stat-products">—</span>
                <label class="fd-stat-label">Produits actifs</label>
              </div>
              <div class="fd-stat-card">
                <div class="fd-stat-icon"><i class="fas fa-basket-shopping"></i></div>
                <span class="fd-stat-value" id="stat-orders">—</span>
                <label class="fd-stat-label">Commandes reçues</label>
              </div>
              <div class="fd-stat-card">
                <div class="fd-stat-icon"><i class="fas fa-coins"></i></div>
                <span class="fd-stat-value" id="stat-revenue">—</span>
                <label class="fd-stat-label">Chiffre d'affaires</label>
              </div>
            </div>
          </div>
        </section>
      </div>

    </main>
  </div>

  <script src="farmer-dashboard.js?v=<?= filemtime(__DIR__ . '/farmer-dashboard.js') ?>"></script>
  <script src="i18n.js?v=<?= filemtime(__DIR__ . '/i18n.js') ?>"></script>
</body>
</html>
