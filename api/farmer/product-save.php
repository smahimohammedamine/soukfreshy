<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

require_once dirname(__DIR__) . '/config/db.php';

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$editId       = isset($data['id']) ? (int) $data['id'] : 0;
$category     = trim($data['category']     ?? '');
$name         = trim($data['name']         ?? '');
$price        = (float) ($data['price']    ?? 0);
$pricingType  = trim($data['pricingType']  ?? 'kg');
$pricingLabel = trim($data['pricingLabel'] ?? 'kg');
$availableQty = (int)  ($data['availableQty'] ?? 0);
$image        = trim($data['image']        ?? '');
$description  = trim($data['description'] ?? '');

if (!$name || $price <= 0 || $availableQty < 0) {
    echo json_encode(['success' => false, 'message' => 'Nom, prix et quantité sont obligatoires.']);
    exit;
}
if (!in_array($category, ['vegetables', 'fruits', 'herbs'], true)) {
    echo json_encode(['success' => false, 'message' => 'Catégorie invalide.']);
    exit;
}
if (!in_array($pricingType, ['kg', 'bouquet', 'custom'], true)) {
    echo json_encode(['success' => false, 'message' => 'Type de tarification invalide.']);
    exit;
}

// 'bouquet' is a UI shortcut — stored as custom + bouquet label in the DB
if ($pricingType === 'bouquet') {
    $pricingType  = 'custom';
    $pricingLabel = 'bouquet';
}

$availability = $availableQty > 10 ? 'available' : ($availableQty > 0 ? 'limited' : 'out');

try {
    $db = getDB();

    $catStmt = $db->prepare('SELECT id FROM categories WHERE slug = :slug');
    $catStmt->execute([':slug' => $category]);
    $cat = $catStmt->fetch();
    if (!$cat) {
        echo json_encode(['success' => false, 'message' => 'Catégorie introuvable.']);
        exit;
    }
    $categoryId = (int) $cat['id'];
    $farmerId   = (int) $_SESSION['user_id'];

    if ($editId) {
        $check = $db->prepare('SELECT id FROM products WHERE id = :id AND farmer_id = :fid AND is_active = 1');
        $check->execute([':id' => $editId, ':fid' => $farmerId]);
        if (!$check->fetch()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Produit introuvable.']);
            exit;
        }

        $stmt = $db->prepare(
            'UPDATE products
             SET category_id = :cat, name = :name, description = :desc, price = :price,
                 pricing_type = :pt, pricing_label = :pl,
                 available_qty = :qty, image = :image, availability = :avail
             WHERE id = :id AND farmer_id = :fid'
        );
        $stmt->execute([
            ':cat'   => $categoryId,
            ':name'  => $name,
            ':desc'  => $description ?: null,
            ':price' => $price,
            ':pt'    => $pricingType,
            ':pl'    => $pricingLabel,
            ':qty'   => $availableQty,
            ':image' => $image ?: null,
            ':avail' => $availability,
            ':id'    => $editId,
            ':fid'   => $farmerId,
        ]);

        echo json_encode(['success' => true, 'id' => $editId]);
    } else {
        $stmt = $db->prepare(
            'INSERT INTO products
             (farmer_id, category_id, name, description, price, pricing_type, pricing_label, available_qty, region, image, availability)
             VALUES (:fid, :cat, :name, :desc, :price, :pt, :pl, :qty, :region, :image, :avail)'
        );
        $stmt->execute([
            ':fid'    => $farmerId,
            ':cat'    => $categoryId,
            ':name'   => $name,
            ':desc'   => $description ?: null,
            ':price'  => $price,
            ':pt'     => $pricingType,
            ':pl'     => $pricingLabel,
            ':qty'    => $availableQty,
            ':region' => $_SESSION['wilaya'] ?? null,
            ':image'  => $image ?: null,
            ':avail'  => $availability,
        ]);

        echo json_encode(['success' => true, 'id' => (int) $db->lastInsertId()]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
}
