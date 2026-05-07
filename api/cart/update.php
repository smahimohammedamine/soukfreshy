<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non connecté.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

$body      = json_decode(file_get_contents('php://input'), true);
$productId = isset($body['product_id']) ? (int) $body['product_id'] : 0;
$qty       = isset($body['qty'])        ? (int) $body['qty']        : 0;

if ($productId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Produit invalide.']);
    exit;
}

require_once dirname(__DIR__) . '/config/db.php';

try {
    $db  = getDB();
    $uid = (int) $_SESSION['user_id'];

    if ($qty <= 0) {
        $stmt = $db->prepare('DELETE FROM cart_items WHERE user_id = :uid AND product_id = :pid');
        $stmt->execute([':uid' => $uid, ':pid' => $productId]);
    } else {
        $stmt = $db->prepare(
            'INSERT INTO cart_items (user_id, product_id, qty)
             VALUES (:uid, :pid, :qty)
             ON DUPLICATE KEY UPDATE qty = :qty2, updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([':uid' => $uid, ':pid' => $productId, ':qty' => $qty, ':qty2' => $qty]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
}
