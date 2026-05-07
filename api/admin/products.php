<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false]);
    exit;
}

require_once dirname(__DIR__) . '/config/db.php';

// JS slug  →  DB slug
const CAT_TO_DB = ['legumes' => 'vegetables', 'fruits' => 'fruits', 'feuilles' => 'herbs'];
// DB slug  →  JS slug
const CAT_TO_JS = ['vegetables' => 'legumes', 'fruits' => 'fruits', 'herbs' => 'feuilles'];

$method = $_SERVER['REQUEST_METHOD'];

/* ── GET: list all active products ─────────────────────────── */
if ($method === 'GET') {
    try {
        $db = getDB();
        $products = $db->query("
            SELECT p.id, p.name, c.slug AS cat, p.price, p.available_qty AS stock,
                   p.region AS wilaya, p.image AS img, p.availability, p.is_active,
                   COALESCE(u.full_name, 'Plateforme') AS farmer
            FROM products p
            JOIN categories c ON c.id = p.category_id
            LEFT JOIN users u ON u.id = p.farmer_id
            WHERE p.is_active = 1
            ORDER BY p.created_at DESC
        ")->fetchAll();

        $catEmojis = ['vegetables' => '🥬', 'fruits' => '🍊', 'herbs' => '🌿'];
        foreach ($products as &$p) {
            $p['emoji'] = $catEmojis[$p['cat']] ?? '🌿';
            $p['cat']   = CAT_TO_JS[$p['cat']] ?? $p['cat'];
            $p['price'] = (float)$p['price'];
            $p['stock'] = (int)$p['stock'];
        }

        echo json_encode(['success' => true, 'products' => $products]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }

/* ── POST: update, delete, or update_image ──────────────────── */
} elseif ($method === 'POST') {
    // image upload uses multipart, others use JSON
    $isMultipart = isset($_FILES['image']);
    $raw    = $isMultipart ? '' : file_get_contents('php://input');
    $data   = $isMultipart ? $_POST : (json_decode($raw, true) ?: $_POST);
    $action = $data['action'] ?? '';

    /* update */
    if ($action === 'update') {
        $id     = (int)($data['id']    ?? 0);
        $name   = trim($data['name']   ?? '');
        $cat    = $data['cat']         ?? '';
        $price  = (float)($data['price'] ?? 0);
        $stock  = (int)($data['stock']   ?? 0);
        $wilaya = trim($data['wilaya'] ?? '');

        if (!$id || !$name || $price <= 0) {
            echo json_encode(['success' => false, 'message' => 'Données invalides.']);
            exit;
        }

        $dbSlug = CAT_TO_DB[$cat] ?? 'vegetables';
        $avail  = $stock === 0 ? 'out' : ($stock < 20 ? 'limited' : 'available');

        try {
            $db = getDB();
            $catId = $db->prepare('SELECT id FROM categories WHERE slug = :s LIMIT 1');
            $catId->execute([':s' => $dbSlug]);
            $catRow = $catId->fetch();

            if (!$catRow) {
                echo json_encode(['success' => false, 'message' => 'Catégorie invalide.']);
                exit;
            }

            $db->prepare("
                UPDATE products
                SET name=:name, category_id=:cid, price=:price,
                    available_qty=:stock, region=:region, availability=:avail, updated_at=NOW()
                WHERE id=:id
            ")->execute([
                ':name'   => $name,
                ':cid'    => $catRow['id'],
                ':price'  => $price,
                ':stock'  => $stock,
                ':region' => $wilaya,
                ':avail'  => $avail,
                ':id'     => $id,
            ]);

            echo json_encode(['success' => true]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
        }

    /* soft-delete */
    } elseif ($action === 'delete') {
        $id = (int)($data['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID manquant.']);
            exit;
        }
        try {
            $db = getDB();
            $db->prepare('UPDATE products SET is_active=0 WHERE id=:id')->execute([':id' => $id]);
            echo json_encode(['success' => true]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
        }

    /* update_image */
    } elseif ($action === 'update_image') {
        $id   = (int)($data['id'] ?? 0);
        $file = $_FILES['image'] ?? null;

        if (!$id || !$file || $file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Données manquantes ou erreur upload.']);
            exit;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext === 'jpg') $ext = 'jpg';
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            echo json_encode(['success' => false, 'message' => 'Format non supporté (jpg/png/webp).']);
            exit;
        }
        if ($file['size'] > 3 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Image trop grande (max 3 Mo).']);
            exit;
        }

        $projRoot = dirname(__DIR__, 2);
        $dir      = $projRoot . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $fname = 'product_' . $id . '_' . time() . '.' . $ext;
        $dest  = $dir . $fname;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la sauvegarde du fichier.']);
            exit;
        }

        // Build URL relative to document root (works for any folder name)
        $docRoot  = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
        $projPath = rtrim(str_replace('\\', '/', $projRoot), '/');
        $sitePath = substr($projPath, strlen($docRoot)); // e.g. /soukfreshyy
        $imgUrl   = $sitePath . '/images/products/' . $fname;

        try {
            $db = getDB();
            $db->prepare('UPDATE products SET image=:img, updated_at=NOW() WHERE id=:id')
               ->execute([':img' => $imgUrl, ':id' => $id]);
            echo json_encode(['success' => true, 'img' => $imgUrl]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur BD: ' . $e->getMessage()]);
        }

    } else {
        echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false]);
}