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

  <header class="fd-header">
    <div class="fd-brand">
      <div class="fd-brand-icon"><i class="fas fa-seedling"></i></div>
      <div>
        <p class="fd-kicker">SoukFreshy — Farmer</p>
        <h1>Tableau de bord</h1>
        <p id="farmer-welcome" class="fd-sub">Bonjour <strong><?= $farmerName ?></strong>, gérez vos produits du jour.</p>
      </div>
    </div>
    <button id="logout-btn" class="fd-logout-btn">
      <i class="fas fa-arrow-right-from-bracket"></i> Déconnexion
    </button>
  </header>

  <main class="fd-main">

    <!-- ── Add / Edit card ── -->
    <section class="fd-card">
      <div class="fd-card-head">
        <div class="fd-card-badge"><i class="fas fa-plus"></i> Nouveau produit</div>
        <h2>Ajouter un produit</h2>
        <p>Renseignez les informations ci-dessous et publiez votre offre.</p>
      </div>

      <form id="product-form" class="fd-form" novalidate>
        <input type="hidden" id="edit-id">

        <!-- Informations produit -->
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

        <!-- Tarification & stock -->
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

        <!-- Description -->
        <div class="fd-section">
          <div class="fd-section-title"><i class="fas fa-align-left"></i> Description</div>
          <textarea id="product-description" rows="3" placeholder="Origine, qualité, conseils d'utilisation…"></textarea>
        </div>

        <!-- Photo -->
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

    <!-- ── Product list card ── -->
    <section class="fd-card">
      <div class="fd-card-head">
        <div class="fd-card-badge"><i class="fas fa-box-open"></i> Stock</div>
        <h2>Mes produits</h2>
        <p>Modifiez ou supprimez vos offres en un clic.</p>
      </div>
      <div id="farmer-product-list" class="fd-product-list"></div>
    </section>

  </main>

  <script src="farmer-dashboard.js?v=<?= filemtime(__DIR__ . '/farmer-dashboard.js') ?>"></script>
</body>
</html>
