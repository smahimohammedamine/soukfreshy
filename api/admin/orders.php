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

/* ── GET: order detail (id=X) or list all orders ───────────── */
if ($method === 'GET') {
    $orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    /* ── Full detail for one order ── */
    if ($orderId) {
        try {
            $db   = getDB();
            $stmt = $db->prepare("
                SELECT o.id, o.order_number,
                       o.consumer_id, o.consumer_name, o.consumer_phone,
                       o.wilaya, o.commune, o.delivery_address,
                       o.subtotal, o.delivery_fee, o.service_fee,
                       o.commission_rate, o.commission_amount, o.total, o.status,
                       DATE_FORMAT(o.created_at,'%d/%m/%Y %H:%i') AS date,
                       u.email  AS consumer_email,
                       u.wilaya AS consumer_user_wilaya
                FROM orders o
                LEFT JOIN users u ON u.id = o.consumer_id
                WHERE o.id = :id
                LIMIT 1
            ");
            $stmt->execute([':id' => $orderId]);
            $order = $stmt->fetch();
            if (!$order) {
                echo json_encode(['success' => false, 'message' => 'Commande introuvable.']);
                exit;
            }

            $items = $db->prepare("
                SELECT oi.product_name, oi.unit_price, oi.qty, oi.line_total, oi.pricing_label,
                       oi.farmer_id,
                       f.full_name AS farmer_name, f.phone AS farmer_phone,
                       f.wilaya   AS farmer_wilaya, f.commune AS farmer_commune
                FROM order_items oi
                LEFT JOIN users f ON f.id = oi.farmer_id
                WHERE oi.order_id = :id
                ORDER BY oi.id
            ");
            $items->execute([':id' => $orderId]);
            $order['items'] = $items->fetchAll();

            $seenFarmers = [];
            $farmers     = [];
            foreach ($order['items'] as $item) {
                if ($item['farmer_id'] && !isset($seenFarmers[$item['farmer_id']])) {
                    $seenFarmers[$item['farmer_id']] = true;
                    $farmers[] = [
                        'id'      => $item['farmer_id'],
                        'name'    => $item['farmer_name'],
                        'phone'   => $item['farmer_phone'],
                        'wilaya'  => $item['farmer_wilaya'],
                        'commune' => $item['farmer_commune'],
                    ];
                }
            }
            $order['farmers'] = $farmers;

            foreach (['subtotal','delivery_fee','service_fee','commission_amount','total'] as $f) {
                $order[$f] = (float)$order[$f];
            }

            echo json_encode(['success' => true, 'order' => $order]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
        }
        exit;
    }

    /* ── List all orders ── */
    try {
        $db = getDB();
        $orders = $db->query("
            SELECT o.id, o.order_number,
                   o.consumer_name, o.consumer_phone,
                   o.wilaya, o.commune,
                   o.subtotal, o.delivery_fee AS delivery, o.service_fee AS service,
                   o.commission_amount, o.commission_rate, o.total, o.status,
                   DATE_FORMAT(o.created_at,'%d/%m/%Y %H:%i') AS date,
                   COALESCE(GROUP_CONCAT(DISTINCT CONCAT(f.full_name, ' (', COALESCE(f.wilaya,'?'), ')') ORDER BY f.id SEPARATOR ' / '), NULL) AS farmers,
                   COALESCE(GROUP_CONCAT(oi.product_name ORDER BY oi.id SEPARATOR ', '), '—') AS products,
                   COALESCE(CONCAT(SUM(oi.qty), ' ', MAX(oi.pricing_label)), '—') AS qty
            FROM orders o
            LEFT JOIN order_items oi ON oi.order_id = o.id
            LEFT JOIN users f ON f.id = oi.farmer_id
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