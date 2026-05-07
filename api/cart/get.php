<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => true, 'items' => []]);
    exit;
}

require_once dirname(__DIR__) . '/config/db.php';

try {
    $db   = getDB();
    $stmt = $db->prepare(
        'SELECT ci.product_id AS id, ci.qty,
                p.name, p.price, p.pricing_label AS unit,
                p.image, p.availability, p.available_qty AS stock,
                c.slug AS category, p.region
         FROM cart_items ci
         JOIN products   p ON p.id = ci.product_id
         JOIN categories c ON c.id = p.category_id
         WHERE ci.user_id = :uid AND p.is_active = 1
         ORDER BY ci.created_at ASC'
    );
    $stmt->execute([':uid' => (int) $_SESSION['user_id']]);
    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        $row['id']    = (int)   $row['id'];
        $row['qty']   = (int)   $row['qty'];
        $row['price'] = (float) $row['price'];
        $row['stock'] = (int)   $row['stock'];
    }
    unset($row);

    echo json_encode(['success' => true, 'items' => $rows]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
}
