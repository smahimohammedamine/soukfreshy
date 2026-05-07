<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/db.php';

try {
    $db    = getDB();
    $boxes = $db->query("
        SELECT wb.id, wb.title, wb.description, wb.image, wb.price, wb.quantity, wb.products, wb.box_type,
               CASE WHEN wb.box_type = 'mixed' THEN '🥗 Mixte' ELSE COALESCE(c.name_fr, wb.box_type) END AS box_type_label
        FROM weekly_boxes wb
        LEFT JOIN categories c ON c.slug = wb.box_type
        WHERE wb.is_active = 1
        ORDER BY wb.created_at DESC
    ")->fetchAll();

    foreach ($boxes as &$b) {
        $b['products'] = json_decode($b['products'] ?? '[]', true) ?: [];
        $b['price']    = (float)$b['price'];
        $b['quantity'] = (int)$b['quantity'];
    }

    echo json_encode(['success' => true, 'boxes' => $boxes]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'boxes' => []]);
}