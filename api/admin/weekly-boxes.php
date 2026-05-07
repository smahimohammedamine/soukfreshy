<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false]);
    exit;
}

require_once dirname(__DIR__) . '/config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

/* ── GET: list all boxes ──────────────────────────── */
if ($method === 'GET') {
    try {
        $db    = getDB();
        $boxes = $db->query("SELECT * FROM weekly_boxes ORDER BY created_at DESC")->fetchAll();
        foreach ($boxes as &$b) {
            $b['products']  = json_decode($b['products'] ?? '[]', true) ?: [];
            $b['price']     = (float)$b['price'];
            $b['quantity']  = (int)$b['quantity'];
            $b['is_active'] = (bool)$b['is_active'];
        }
        echo json_encode(['success' => true, 'boxes' => $boxes]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }

/* ── POST: create / update / delete / toggle ──────── */
} elseif ($method === 'POST') {
    $isMultipart = isset($_FILES['image']);
    $raw    = $isMultipart ? '' : file_get_contents('php://input');
    $data   = $isMultipart ? $_POST : (json_decode($raw, true) ?: []);
    $action = $data['action'] ?? '';

    /* ── create or update ── */
    if ($action === 'create' || $action === 'update') {
        $id       = (int)($data['id'] ?? 0);
        $title    = trim($data['title']       ?? '');
        $desc     = trim($data['description'] ?? '');
        $price    = (float)($data['price']    ?? 0);
        $qty      = (int)($data['quantity']   ?? 0);
        $boxType  = $data['box_type'] ?? 'mixed';
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        if (!in_array($boxType, ['fruits', 'vegetables', 'mixed'], true)) {
            $boxType = 'mixed';
        }
        if (!$title || $price <= 0) {
            echo json_encode(['success' => false, 'message' => 'Titre et prix requis.']);
            exit;
        }

        // products: sent as JSON string or array
        $rawProducts = $data['products'] ?? '[]';
        if (is_string($rawProducts)) {
            $productsArr = json_decode($rawProducts, true);
            if (!is_array($productsArr)) {
                // fallback: treat as newline-separated text
                $productsArr = array_values(array_filter(array_map('trim', explode("\n", $rawProducts))));
            }
        } else {
            $productsArr = (array)$rawProducts;
        }
        $productsArr  = array_values(array_filter(array_map('trim', $productsArr)));
        $productsJson = json_encode($productsArr, JSON_UNESCAPED_UNICODE);

        // optional image upload
        $imageUrl = null;
        if ($isMultipart && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
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
            $dir      = $projRoot . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'boxes' . DIRECTORY_SEPARATOR;
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $fname = 'box_' . time() . '_' . mt_rand(100, 999) . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], $dir . $fname)) {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la sauvegarde du fichier.']);
                exit;
            }
            $docRoot  = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
            $projPath = rtrim(str_replace('\\', '/', $projRoot), '/');
            $sitePath = substr($projPath, strlen($docRoot));
            $imageUrl = $sitePath . '/images/boxes/' . $fname;
        }

        try {
            $db = getDB();

            if ($action === 'create') {
                $stmt = $db->prepare("
                    INSERT INTO weekly_boxes (title, description, image, price, quantity, products, box_type, is_active)
                    VALUES (:title, :desc, :img, :price, :qty, :products, :type, :active)
                ");
                $stmt->execute([
                    ':title'    => $title,
                    ':desc'     => $desc,
                    ':img'      => $imageUrl,
                    ':price'    => $price,
                    ':qty'      => $qty,
                    ':products' => $productsJson,
                    ':type'     => $boxType,
                    ':active'   => $isActive,
                ]);
                $newId = (int)$db->lastInsertId();
                $row   = $db->prepare('SELECT * FROM weekly_boxes WHERE id = :id');
                $row->execute([':id' => $newId]);
                $box = $row->fetch();
                $box['products']  = json_decode($box['products'] ?? '[]', true) ?: [];
                $box['price']     = (float)$box['price'];
                $box['quantity']  = (int)$box['quantity'];
                $box['is_active'] = (bool)$box['is_active'];
                echo json_encode(['success' => true, 'box' => $box]);

            } else {
                if (!$id) { echo json_encode(['success' => false, 'message' => 'ID manquant.']); exit; }
                if ($imageUrl) {
                    $db->prepare("
                        UPDATE weekly_boxes
                        SET title=:title, description=:desc, image=:img, price=:price,
                            quantity=:qty, products=:products, box_type=:type, is_active=:active, updated_at=NOW()
                        WHERE id=:id
                    ")->execute([
                        ':title' => $title, ':desc' => $desc, ':img' => $imageUrl,
                        ':price' => $price, ':qty' => $qty, ':products' => $productsJson,
                        ':type' => $boxType, ':active' => $isActive, ':id' => $id,
                    ]);
                } else {
                    $db->prepare("
                        UPDATE weekly_boxes
                        SET title=:title, description=:desc, price=:price,
                            quantity=:qty, products=:products, box_type=:type, is_active=:active, updated_at=NOW()
                        WHERE id=:id
                    ")->execute([
                        ':title' => $title, ':desc' => $desc,
                        ':price' => $price, ':qty' => $qty, ':products' => $productsJson,
                        ':type' => $boxType, ':active' => $isActive, ':id' => $id,
                    ]);
                }
                echo json_encode(['success' => true, 'image' => $imageUrl]);
            }
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
        }

    /* ── delete ── */
    } elseif ($action === 'delete') {
        $id = (int)($data['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false, 'message' => 'ID manquant.']); exit; }
        try {
            $db = getDB();
            $db->prepare('DELETE FROM weekly_boxes WHERE id=:id')->execute([':id' => $id]);
            echo json_encode(['success' => true]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
        }

    /* ── toggle active ── */
    } elseif ($action === 'toggle') {
        $id = (int)($data['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false, 'message' => 'ID manquant.']); exit; }
        try {
            $db  = getDB();
            $row = $db->prepare('SELECT is_active FROM weekly_boxes WHERE id=:id');
            $row->execute([':id' => $id]);
            $cur = $row->fetch();
            if (!$cur) { echo json_encode(['success' => false, 'message' => 'Boîte introuvable.']); exit; }
            $newActive = $cur['is_active'] ? 0 : 1;
            $db->prepare('UPDATE weekly_boxes SET is_active=:a, updated_at=NOW() WHERE id=:id')
               ->execute([':a' => $newActive, ':id' => $id]);
            echo json_encode(['success' => true, 'is_active' => (bool)$newActive]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
        }

    } else {
        echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false]);
}