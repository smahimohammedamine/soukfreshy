<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé.']);
    exit;
}

require_once dirname(__DIR__) . '/config/db.php';

try {
    $db  = getDB();
    $uid = (int) $_SESSION['user_id'];

    $stmt = $db->prepare(
        'SELECT o.id, o.order_number, o.status, o.subtotal, o.delivery_fee, o.service_fee, o.total, o.created_at
         FROM orders o
         WHERE o.consumer_id = :uid
         ORDER BY o.created_at DESC
         LIMIT 30'
    );
    $stmt->execute([':uid' => $uid]);
    $orders = $stmt->fetchAll();

    $itemStmt = $db->prepare(
        'SELECT oi.product_name, oi.qty, oi.pricing_label, oi.unit_price, oi.line_total,
                u.full_name  AS farmer_name,
                u.wilaya     AS farmer_wilaya,
                u.phone      AS farmer_phone
         FROM order_items oi
         LEFT JOIN users u ON u.id = oi.farmer_id
         WHERE oi.order_id = :oid ORDER BY oi.id'
    );

    $result = [];
    foreach ($orders as $o) {
        $itemStmt->execute([':oid' => $o['id']]);
        $o['items'] = $itemStmt->fetchAll();
        $result[] = $o;
    }

    echo json_encode(['success' => true, 'orders' => $result]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
}
