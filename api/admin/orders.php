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

/* ── GET: list all orders ───────────────────────────────────── */
if ($method === 'GET') {
    try {
        $db = getDB();
        $orders = $db->query("
            SELECT o.id, o.order_number, o.consumer_name AS farmer,
                   o.wilaya, o.commune,
                   o.subtotal, o.delivery_fee AS delivery, o.service_fee AS service,
                   o.commission_amount, o.commission_rate, o.total, o.status,
                   DATE_FORMAT(o.created_at,'%Y-%m-%d') AS date,
                   COALESCE(GROUP_CONCAT(oi.product_name ORDER BY oi.id SEPARATOR ', '), '—') AS products,
                   COALESCE(CONCAT(SUM(oi.qty), ' ', MAX(oi.pricing_label)), '—') AS qty
            FROM orders o
            LEFT JOIN order_items oi ON oi.order_id = o.id
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ")->fetchAll();

        foreach ($orders as &$o) {
            $o['subtotal']          = (float)$o['subtotal'];
            $o['delivery']          = (float)$o['delivery'];
            $o['service']           = (float)$o['service'];
            $o['commission_amount'] = (float)$o['commission_amount'];
            $o['total']             = (float)$o['total'];
        }

        echo json_encode(['success' => true, 'orders' => $orders]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }

/* ── POST: advance order status ─────────────────────────────── */
} elseif ($method === 'POST') {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: $_POST;

    $orderId = (int)($data['id'] ?? 0);
    $action  = $data['action'] ?? '';

    if (!$orderId || $action !== 'advance') {
        echo json_encode(['success' => false, 'message' => 'Paramètres invalides.']);
        exit;
    }

    try {
        $db   = getDB();
        $stmt = $db->prepare('SELECT id, order_number, status FROM orders WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $orderId]);
        $order = $stmt->fetch();

        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Commande introuvable.']);
            exit;
        }

        $next = null;
        if ($order['status'] === 'new')  $next = 'prep';
        if ($order['status'] === 'prep') $next = 'delivered';

        if (!$next) {
            echo json_encode(['success' => false, 'message' => 'Statut déjà final.']);
            exit;
        }

        $db->prepare('UPDATE orders SET status = :s WHERE id = :id')
           ->execute([':s' => $next, ':id' => $orderId]);

        // Insert notification for new prep/delivered status
        $label = $next === 'delivered' ? 'livrée' : 'en préparation';
        $db->prepare("
            INSERT INTO notifications (type, message, related_id)
            VALUES ('order', :msg, :ref)
        ")->execute([
            ':msg' => 'Commande ' . $order['order_number'] . ' passée ' . $label,
            ':ref' => $order['order_number'],
        ]);

        echo json_encode(['success' => true, 'new_status' => $next]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false]);
}