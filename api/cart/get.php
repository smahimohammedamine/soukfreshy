<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => true, 'items' => []]);
    exit;
}

require_once dirname(__DIR__) . '/config/db.php';

try {
    $db  = getDB();
    $uid = (int) $_SESSION['user_id'];

    // ── Product items ─────────────────────────────────────────
    $stmt = $db->prepare(
        'SELECT ci.product_id AS id, ci.qty, NULL AS box_id, "product" AS item_type,
                p.name, p.price, p.pricing_label AS unit,
                p.image, p.availability, p.available_qty AS stock,
                c.slug AS category, p.region
         FROM cart_items ci
         JOIN products   p ON p.id = ci.product_id
         JOIN categories c ON c.id = p.category_id
         WHERE ci.user_id = :uid AND ci.product_id IS NOT NULL AND p.is_active = 1
         ORDER BY ci.created_at ASC'
    );
    $stmt->execute([':uid' => $uid]);
    $productRows = $stmt->fetchAll();

    // ── Box items ─────────────────────────────────────────────
    $stmtB = $db->prepare(
        'SELECT NULL AS id, ci.qty, ci.box_id, "box" AS item_type,
                wb.title AS name, wb.price, "boîte" AS unit,
                wb.image, "available" AS availability, wb.quantity AS stock,
                NULL AS category, NULL AS region
         FROM cart_items ci
         JOIN weekly_boxes wb ON wb.id = ci.box_id
         WHERE ci.user_id = :uid AND ci.box_id IS NOT NULL AND wb.is_active = 1
         ORDER BY ci.created_at ASC'
    );
    $stmtB->execute([':uid' => $uid]);
    $boxRows = $stmtB->fetchAll();

    $rows = array_merge($productRows, $boxRows);

    foreach ($rows as &$row) {
        $row['qty']   = (int)   $row['qty'];
        $row['price'] = (float) $row['price'];
        $row['stock'] = (int)   $row['stock'];
        if ($row['item_type'] === 'product') {
            $row['id'] = (int) $row['id'];
        } else {
            $row['box_id'] = (int) $row['box_id'];
        }
    }
    unset($row);

    echo json_encode(['success' => true, 'items' => $rows]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
}