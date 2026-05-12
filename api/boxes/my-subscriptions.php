<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    echo json_encode(['success' => true, 'subscriptions' => []]);
    exit;
}

require_once dirname(__DIR__) . '/config/db.php';
$uid = (int)$_SESSION['user_id'];

try {
    $db   = getDB();
    $stmt = $db->prepare("
        SELECT bs.id, bs.box_id, bs.frequency, bs.status, bs.next_delivery, bs.created_at,
               wb.title, wb.image, wb.price, wb.category
        FROM box_subscriptions bs
        JOIN weekly_boxes wb ON wb.id = bs.box_id
        WHERE bs.user_id = :uid AND bs.status != 'cancelled'
        ORDER BY bs.created_at DESC
    ");
    $stmt->execute([':uid' => $uid]);
    $subs = $stmt->fetchAll();
    foreach ($subs as &$s) {
        $s['box_id'] = (int)$s['box_id'];
        $s['price']  = (float)$s['price'];
    }
    echo json_encode(['success' => true, 'subscriptions' => $subs]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'subscriptions' => []]);
}
