<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

require_once dirname(__DIR__) . '/config/db.php';

// Accept delivery info from the checkout form (JSON body)
$body    = json_decode(file_get_contents('php://input'), true) ?? [];
$name    = trim($body['name']    ?? $_SESSION['full_name'] ?? '');
$phone   = trim($body['phone']   ?? $_SESSION['phone']     ?? '');
$wilaya  = trim($body['wilaya']  ?? $_SESSION['wilaya']    ?? '');
$commune = trim($body['commune'] ?? $_SESSION['commune']   ?? '');
$address = trim($body['address'] ?? '');

if ($name === '' || $phone === '' || $wilaya === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nom, téléphone et wilaya sont obligatoires.']);
    exit;
}

try {
    $db  = getDB();
    $uid = (int) $_SESSION['user_id'];

    // Load product cart items
    $stmt = $db->prepare(
        'SELECT ci.product_id, ci.qty,
                p.name, p.price, p.pricing_label, p.farmer_id, p.availability
         FROM cart_items ci
         JOIN products p ON p.id = ci.product_id
         WHERE ci.user_id = :uid AND ci.product_id IS NOT NULL AND p.is_active = 1'
    );
    $stmt->execute([':uid' => $uid]);
    $cartItems = $stmt->fetchAll();

    // Load box cart items
    $stmtB = $db->prepare(
        'SELECT ci.box_id, wb.title AS name, wb.price, wb.quantity AS stock, wb.free_delivery
         FROM cart_items ci
         JOIN weekly_boxes wb ON wb.id = ci.box_id
         WHERE ci.user_id = :uid AND ci.box_id IS NOT NULL AND wb.is_active = 1'
    );
    $stmtB->execute([':uid' => $uid]);
    $boxItems = $stmtB->fetchAll();

    if (empty($cartItems) && empty($boxItems)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Votre panier est vide.']);
        exit;
    }

    foreach ($cartItems as $item) {
        if ($item['availability'] === 'out') {
            echo json_encode(['success' => false, 'message' => "\"{$item['name']}\" est en rupture de stock."]);
            exit;
        }
    }
    foreach ($boxItems as $box) {
        if ((int)$box['stock'] <= 0) {
            echo json_encode(['success' => false, 'message' => "La boîte \"{$box['name']}\" est épuisée."]);
            exit;
        }
    }

    // Load platform settings
    $settings = [];
    foreach ($db->query('SELECT setting_key, setting_value FROM settings')->fetchAll() as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    $deliveryFee    = (float) ($settings['delivery_fee']          ?? 200);
    $freeMinimum    = (float) ($settings['free_delivery_minimum'] ?? 5000);
    $commissionRate = (float) ($settings['commission_rate']       ?? 10);
    $serviceFee     = (float) ($settings['service_fee']           ?? 50);

    // Calculate totals
    $subtotal = 0;
    foreach ($cartItems as $item) {
        $subtotal += (float) $item['price'] * (int) $item['qty'];
    }
    foreach ($boxItems as $box) {
        $subtotal += (float) $box['price'];
    }
    $hasFreeDeliveryBox = !empty(array_filter($boxItems, fn($b) => !empty($b['free_delivery'])));
    $actualDelivery = ($subtotal >= $freeMinimum || $hasFreeDeliveryBox) ? 0.00 : $deliveryFee;
    $commissionAmt  = round($subtotal * $commissionRate / 100, 2);
    $total          = $subtotal + $actualDelivery + $serviceFee;

    $db->beginTransaction();

    // Insert order
    $oStmt = $db->prepare(
        'INSERT INTO orders
           (consumer_id, consumer_name, consumer_phone, wilaya, commune, delivery_address,
            subtotal, delivery_fee, service_fee,
            commission_rate, commission_amount, total)
         VALUES
           (:uid, :name, :phone, :wilaya, :commune, :address,
            :subtotal, :delivery, :sfee,
            :crate, :camt, :total)'
    );
    $oStmt->execute([
        ':uid'      => $uid,
        ':name'     => $name,
        ':phone'    => $phone ?: null,
        ':wilaya'   => $wilaya,
        ':commune'  => $commune,
        ':address'  => $address ?: null,
        ':subtotal' => $subtotal,
        ':delivery' => $actualDelivery,
        ':sfee'     => $serviceFee,
        ':crate'    => $commissionRate,
        ':camt'     => $commissionAmt,
        ':total'    => $total,
    ]);
    $orderId = (int) $db->lastInsertId();

    // Insert product order items (price snapshots)
    $iStmt = $db->prepare(
        'INSERT INTO order_items
           (order_id, product_id, farmer_id, product_name, unit_price, pricing_label, qty, line_total)
         VALUES
           (:oid, :pid, :fid, :pname, :price, :plabel, :qty, :line)'
    );
    foreach ($cartItems as $item) {
        $iStmt->execute([
            ':oid'    => $orderId,
            ':pid'    => (int)   $item['product_id'],
            ':fid'    => $item['farmer_id'] !== null ? (int) $item['farmer_id'] : null,
            ':pname'  => $item['name'],
            ':price'  => (float) $item['price'],
            ':plabel' => $item['pricing_label'],
            ':qty'    => (int)   $item['qty'],
            ':line'   => round((float) $item['price'] * (int) $item['qty'], 2),
        ]);
    }

    // Insert box order items
    $bStmt = $db->prepare(
        'INSERT INTO order_items
           (order_id, box_id, product_name, unit_price, pricing_label, qty, line_total)
         VALUES
           (:oid, :bid, :pname, :price, "boîte", 1, :price2)'
    );
    $decStmt = $db->prepare(
        'UPDATE weekly_boxes SET quantity = GREATEST(0, quantity - 1), orders_count = orders_count + 1 WHERE id = :id'
    );
    foreach ($boxItems as $box) {
        $bStmt->execute([
            ':oid'    => $orderId,
            ':bid'    => (int)   $box['box_id'],
            ':pname'  => $box['name'],
            ':price'  => (float) $box['price'],
            ':price2' => (float) $box['price'],
        ]);
        $decStmt->execute([':id' => (int) $box['box_id']]);
    }

    // Clear the user's cart
    $db->prepare('DELETE FROM cart_items WHERE user_id = :uid')->execute([':uid' => $uid]);

    // Set order_number inside the transaction (trigger can't UPDATE same table in MariaDB)
    $orderNumber = 'SF-' . str_pad($orderId, 3, '0', STR_PAD_LEFT);
    $db->prepare('UPDATE orders SET order_number = :num WHERE id = :id')
       ->execute([':num' => $orderNumber, ':id' => $orderId]);

    $db->commit();

    echo json_encode([
        'success'      => true,
        'order_number' => $orderNumber,
        'subtotal'     => $subtotal,
        'delivery_fee' => $actualDelivery,
        'service_fee'  => $serviceFee,
        'total'        => $total,
    ]);
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => get_class($e) . ': ' . $e->getMessage() . ' (line ' . $e->getLine() . ')']);
}