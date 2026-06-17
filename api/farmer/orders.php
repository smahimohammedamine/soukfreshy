<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

require_once dirname(__DIR__) . '/config/db.php';

try {
    $db       = getDB();
    $farmerId = (int) $_SESSION['user_id'];

    $stmt = $db->prepare("
        SELECT
            o.id              AS order_id,
            o.order_number,
            DATE_FORMAT(o.created_at, '%d/%m/%Y %H:%i') AS date,
            o.status,
            o.consumer_name,
            o.consumer_phone,
            o.wilaya,
            o.commune,
            oi.product_name,
            oi.unit_price,
            oi.pricing_label,
            oi.qty,
            oi.line_total
        FROM order_items oi
        JOIN orders o ON o.id = oi.order_id
        WHERE oi.farmer_id = :farmer_id
        ORDER BY o.created_at DESC, oi.id ASC
    ");
    $stmt->execute([':farmer_id' => $farmerId]);
    $rows = $stmt->fetchAll();

    $ordersMap = [];
    foreach ($rows as $row) {
        $oid = $row['order_id'];
        if (!isset($ordersMap[$oid])) {
            $ordersMap[$oid] = [
                'order_id'       => $oid,
                'order_number'   => $row['order_number'],
                'date'           => $row['date'],
                'status'         => $row['status'],
                'consumer_name'  => $row['consumer_name'],
                'consumer_phone' => $row['consumer_phone'],
                'wilaya'         => $row['wilaya'],
                'commune'        => $row['commune'],
                'items'          => [],
                'farmer_total'   => 0.0,
            ];
        }
        $ordersMap[$oid]['items'][] = [
            'product_name'  => $row['product_name'],
            'unit_price'    => (float) $row['unit_price'],
            'pricing_label' => $row['pricing_label'],
            'qty'           => (int)   $row['qty'],
            'line_total'    => (float) $row['line_total'],
        ];
        $ordersMap[$oid]['farmer_total'] = round($ordersMap[$oid]['farmer_total'] + (float) $row['line_total'], 2);
    }

    echo json_encode(['success' => true, 'orders' => array_values($ordersMap)]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
}
