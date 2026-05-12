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

/* ── GET: list all boxes (with optional category filter) ────────── */
if ($method === 'GET') {
    $action   = $_GET['action']   ?? 'list';
    $boxId    = (int)($_GET['id'] ?? 0);
    $category = $_GET['category'] ?? 'all';

    try {
        $db = getDB();

        /* ── stats summary ── */
        if ($action === 'stats') {
            $stats = $db->query("
                SELECT
                  COUNT(*)                                   AS total,
                  SUM(is_active = 1)                         AS active,
                  SUM(is_active = 0)                         AS inactive,
                  SUM(quantity = 0)                          AS out_of_stock,
                  SUM(category = 'weekly')                   AS total_weekly,
                  SUM(category = 'season')                   AS total_season,
                  COALESCE(SUM(orders_count * price), 0)     AS total_revenue,
                  COALESCE(SUM(orders_count), 0)             AS total_orders
                FROM weekly_boxes
            ")->fetch();
            $stats['total_revenue'] = (float)$stats['total_revenue'];
            $stats['total_orders']  = (int)$stats['total_orders'];
            echo json_encode(['success' => true, 'stats' => $stats]);
            exit;
        }

        /* ── orders for a specific box ── */
        if ($action === 'get_orders' && $boxId) {
            $rows = $db->prepare("
                SELECT o.order_number, o.consumer_name, o.consumer_phone,
                       o.wilaya, o.commune, o.status, o.created_at,
                       oi.unit_price, oi.qty
                FROM order_items oi
                JOIN orders o ON o.id = oi.order_id
                WHERE oi.box_id = :bid
                ORDER BY o.created_at DESC
                LIMIT 100
            ");
            $rows->execute([':bid' => $boxId]);
            echo json_encode(['success' => true, 'orders' => $rows->fetchAll()]);
            exit;
        }

        /* ── subscriptions for a specific box ── */
        if ($action === 'get_subscriptions' && $boxId) {
            $rows = $db->prepare("
                SELECT bs.id, bs.frequency, bs.status, bs.next_delivery, bs.created_at,
                       u.full_name, u.phone, u.wilaya, u.commune
                FROM box_subscriptions bs
                JOIN users u ON u.id = bs.user_id
                WHERE bs.box_id = :bid
                ORDER BY bs.created_at DESC
            ");
            $rows->execute([':bid' => $boxId]);
            echo json_encode(['success' => true, 'subscriptions' => $rows->fetchAll()]);
            exit;
        }

        /* ── default: list all boxes ── */
        $where = '1=1';
        if ($category === 'weekly') $where = "wb.category = 'weekly'";
        if ($category === 'season') $where = "wb.category = 'season'";

        $boxes = $db->query("
            SELECT wb.*,
                   CASE WHEN wb.box_type = 'mixed' THEN 'Mixte' ELSE COALESCE(c.name_fr, wb.box_type) END AS box_type_label
            FROM weekly_boxes wb
            LEFT JOIN categories c ON c.slug = wb.box_type
            WHERE $where
            ORDER BY wb.category ASC, wb.created_at DESC
        ")->fetchAll();

        foreach ($boxes as &$b) {
            $b['products']       = json_decode($b['products'] ?? '[]', true) ?: [];
            $b['price']          = (float)$b['price'];
            $b['original_price'] = $b['original_price'] !== null ? (float)$b['original_price'] : null;
            $b['quantity']       = (int)$b['quantity'];
            $b['orders_count']   = (int)($b['orders_count'] ?? 0);
            $b['is_active']      = (bool)$b['is_active'];
            $b['free_delivery']  = (bool)$b['free_delivery'];
            if ($b['original_price'] && $b['original_price'] > $b['price']) {
                $b['discount_pct'] = (int)round(($b['original_price'] - $b['price']) / $b['original_price'] * 100);
            } else {
                $b['discount_pct'] = 0;
            }
        }
        unset($b);

        echo json_encode(['success' => true, 'boxes' => $boxes]);

    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
    }

/* ── POST: create / update / delete / toggle / duplicate ─────────── */
} elseif ($method === 'POST') {
    $isMultipart = !empty($_POST) || isset($_FILES['image']);
    $raw    = $isMultipart ? '' : file_get_contents('php://input');
    $data   = $isMultipart ? $_POST : (json_decode($raw, true) ?: []);
    $action = $data['action'] ?? '';

    /* ── create or update ── */
    if ($action === 'create' || $action === 'update') {
        $id            = (int)($data['id'] ?? 0);
        $category      = in_array($data['category'] ?? '', ['weekly','season']) ? $data['category'] : 'weekly';
        $title         = trim($data['title']          ?? '');
        $desc          = trim($data['description']    ?? '');
        $price         = (float)($data['price']       ?? 0);
        $originalPrice = strlen($data['original_price'] ?? '') ? (float)$data['original_price'] : null;
        $qty           = (int)($data['quantity']      ?? 0);
        $boxType       = trim($data['box_type']       ?? 'mixed') ?: 'mixed';
        $season        = in_array($data['season'] ?? '', ['spring','summer','autumn','winter']) ? $data['season'] : null;
        $availFrom     = trim($data['available_from']  ?? '') ?: null;
        $availUntil    = trim($data['available_until'] ?? '') ?: null;
        $badge         = trim($data['badge']          ?? '') ?: null;
        $isActive      = isset($data['is_active'])     ? (int)$data['is_active']     : 1;
        $freeDelivery  = isset($data['free_delivery']) ? (int)$data['free_delivery'] : 0;

        // Season boxes don't have season/dates unless it's category=season
        if ($category !== 'season') {
            $season     = null;
            $availFrom  = null;
            $availUntil = null;
        }

        if (!$title || $price <= 0) {
            echo json_encode(['success' => false, 'message' => 'Titre et prix requis.']);
            exit;
        }

        // products: JSON string or array
        $rawProducts = $data['products'] ?? '[]';
        if (is_string($rawProducts)) {
            $productsArr = json_decode($rawProducts, true);
            if (!is_array($productsArr)) {
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
                    INSERT INTO weekly_boxes
                      (category, title, description, image, price, original_price, quantity,
                       products, box_type, season, available_from, available_until,
                       badge, is_active, free_delivery)
                    VALUES
                      (:cat, :title, :desc, :img, :price, :origprice, :qty,
                       :products, :type, :season, :afrom, :auntil,
                       :badge, :active, :fdelivery)
                ");
                $stmt->execute([
                    ':cat'       => $category,
                    ':title'     => $title,
                    ':desc'      => $desc,
                    ':img'       => $imageUrl,
                    ':price'     => $price,
                    ':origprice' => $originalPrice,
                    ':qty'       => $qty,
                    ':products'  => $productsJson,
                    ':type'      => $boxType,
                    ':season'    => $season,
                    ':afrom'     => $availFrom,
                    ':auntil'    => $availUntil,
                    ':badge'     => $badge,
                    ':active'    => $isActive,
                    ':fdelivery' => $freeDelivery,
                ]);
                $newId = (int)$db->lastInsertId();
                $row   = $db->prepare('SELECT * FROM weekly_boxes WHERE id = :id');
                $row->execute([':id' => $newId]);
                $box = $row->fetch();
                $box['products']       = json_decode($box['products'] ?? '[]', true) ?: [];
                $box['price']          = (float)$box['price'];
                $box['original_price'] = $box['original_price'] !== null ? (float)$box['original_price'] : null;
                $box['quantity']       = (int)$box['quantity'];
                $box['orders_count']   = 0;
                $box['is_active']      = (bool)$box['is_active'];
                $box['free_delivery']  = (bool)$box['free_delivery'];
                $box['discount_pct']   = ($box['original_price'] && $box['original_price'] > $box['price'])
                    ? (int)round(($box['original_price'] - $box['price']) / $box['original_price'] * 100) : 0;
                echo json_encode(['success' => true, 'box' => $box]);

            } else { /* update */
                if (!$id) { echo json_encode(['success' => false, 'message' => 'ID manquant.']); exit; }

                $imgSql = $imageUrl ? ', image=:img' : '';
                $sql = "UPDATE weekly_boxes SET
                    category=:cat, title=:title, description=:desc,
                    price=:price, original_price=:origprice, quantity=:qty,
                    products=:products, box_type=:type,
                    season=:season, available_from=:afrom, available_until=:auntil,
                    badge=:badge, is_active=:active, free_delivery=:fdelivery,
                    updated_at=NOW() $imgSql
                  WHERE id=:id";
                $params = [
                    ':cat'       => $category,
                    ':title'     => $title,
                    ':desc'      => $desc,
                    ':price'     => $price,
                    ':origprice' => $originalPrice,
                    ':qty'       => $qty,
                    ':products'  => $productsJson,
                    ':type'      => $boxType,
                    ':season'    => $season,
                    ':afrom'     => $availFrom,
                    ':auntil'    => $availUntil,
                    ':badge'     => $badge,
                    ':active'    => $isActive,
                    ':fdelivery' => $freeDelivery,
                    ':id'        => $id,
                ];
                if ($imageUrl) $params[':img'] = $imageUrl;
                $db->prepare($sql)->execute($params);
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

    /* ── duplicate ── */
    } elseif ($action === 'duplicate') {
        $id = (int)($data['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false, 'message' => 'ID manquant.']); exit; }
        try {
            $db  = getDB();
            $row = $db->prepare('SELECT * FROM weekly_boxes WHERE id=:id');
            $row->execute([':id' => $id]);
            $b = $row->fetch();
            if (!$b) { echo json_encode(['success' => false, 'message' => 'Boîte introuvable.']); exit; }

            $db->prepare("
                INSERT INTO weekly_boxes
                  (category, title, description, image, price, original_price, quantity,
                   products, box_type, season, available_from, available_until,
                   badge, is_active, free_delivery)
                VALUES
                  (:cat,:title,:desc,:img,:price,:origprice,:qty,
                   :products,:type,:season,:afrom,:auntil,:badge,0,:fdelivery)
            ")->execute([
                ':cat'       => $b['category'] ?? 'weekly',
                ':title'     => 'Copie — ' . $b['title'],
                ':desc'      => $b['description'],
                ':img'       => $b['image'],
                ':price'     => $b['price'],
                ':origprice' => $b['original_price'],
                ':qty'       => $b['quantity'],
                ':products'  => $b['products'],
                ':type'      => $b['box_type'],
                ':season'    => $b['season'],
                ':afrom'     => $b['available_from'],
                ':auntil'    => $b['available_until'],
                ':badge'     => $b['badge'],
                ':fdelivery' => $b['free_delivery'],
            ]);
            $newId  = (int)$db->lastInsertId();
            $newRow = $db->prepare('SELECT * FROM weekly_boxes WHERE id=:id');
            $newRow->execute([':id' => $newId]);
            $newBox = $newRow->fetch();
            $newBox['products']      = json_decode($newBox['products'] ?? '[]', true) ?: [];
            $newBox['price']         = (float)$newBox['price'];
            $newBox['original_price']= $newBox['original_price'] !== null ? (float)$newBox['original_price'] : null;
            $newBox['quantity']      = (int)$newBox['quantity'];
            $newBox['orders_count']  = 0;
            $newBox['is_active']     = false;
            $newBox['free_delivery'] = (bool)$newBox['free_delivery'];
            echo json_encode(['success' => true, 'box' => $newBox]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
        }

    /* ── bulk toggle ── */
    } elseif ($action === 'bulk_toggle') {
        $category = in_array($data['category'] ?? '', ['weekly','season']) ? $data['category'] : null;
        $newState  = (int)($data['is_active'] ?? 1);
        try {
            $db = getDB();
            $sql = $category
                ? 'UPDATE weekly_boxes SET is_active=:a WHERE category=:cat'
                : 'UPDATE weekly_boxes SET is_active=:a';
            $params = [':a' => $newState];
            if ($category) $params[':cat'] = $category;
            $db->prepare($sql)->execute($params);
            echo json_encode(['success' => true]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
        }

    /* ── cancel subscription (admin) ── */
    } elseif ($action === 'cancel_subscription') {
        $subId = (int)($data['id'] ?? 0);
        if (!$subId) { echo json_encode(['success' => false, 'message' => 'ID manquant.']); exit; }
        try {
            $db = getDB();
            $db->prepare("UPDATE box_subscriptions SET status='cancelled', updated_at=NOW() WHERE id=:id")
               ->execute([':id' => $subId]);
            echo json_encode(['success' => true]);
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
