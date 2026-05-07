<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé.']);
    exit;
}

require_once dirname(__DIR__) . '/config/db.php';

try {
    $db = getDB();

    // Global stats
    $stats = $db->query("
        SELECT
            COUNT(*) AS total_orders,
            SUM(CASE WHEN status = 'new'  THEN 1 ELSE 0 END) AS pending,
            COALESCE(SUM(subtotal), 0)           AS revenue,
            COALESCE(SUM(commission_amount), 0)  AS commission
        FROM orders
    ")->fetch();

    $farmers   = (int) $db->query("SELECT COUNT(*) FROM users WHERE role='farmer'  AND is_active=1")->fetchColumn();
    $consumers = (int) $db->query("SELECT COUNT(*) FROM users WHERE role='consumer' AND is_active=1")->fetchColumn();

    // Daily sales for current ISO week (Mon–Sun)
    // DAYOFWEEK: 1=Sun,2=Mon,...,7=Sat  →  map to index 0–6 matching JS weekDays array
    $rows = $db->query("
        SELECT DAYOFWEEK(created_at) AS dow, COALESCE(SUM(subtotal), 0) AS total
        FROM orders
        WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
        GROUP BY dow
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    $weekSales = [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0];
    foreach ($rows as $dow => $total) {
        $weekSales[(int)$dow - 1] = (float)$total;
    }

    // Recent 5 orders for dashboard table
    $recent = $db->query("
        SELECT o.id, o.order_number, o.consumer_name AS farmer, o.wilaya,
               o.subtotal, o.status,
               DATE_FORMAT(o.created_at,'%Y-%m-%d') AS date,
               COALESCE(GROUP_CONCAT(oi.product_name ORDER BY oi.id SEPARATOR ', '), '—') AS products
        FROM orders o
        LEFT JOIN order_items oi ON oi.order_id = o.id
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT 5
    ")->fetchAll();

    // Low-stock alerts for notifications
    $lowStock = $db->query("
        SELECT name, available_qty FROM products
        WHERE is_active=1 AND available_qty < 20 AND available_qty > 0
        ORDER BY available_qty ASC LIMIT 5
    ")->fetchAll();

    echo json_encode([
        'success'    => true,
        'stats'      => [
            'total_orders' => (int)$stats['total_orders'],
            'pending'      => (int)$stats['pending'],
            'revenue'      => (float)$stats['revenue'],
            'commission'   => (float)$stats['commission'],
            'farmers'      => $farmers,
            'consumers'    => $consumers,
        ],
        'week_sales'     => $weekSales,
        'recent_orders'  => $recent,
        'low_stock'      => $lowStock,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
}