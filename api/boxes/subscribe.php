<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Connexion requise.']);
    exit;
}

require_once dirname(__DIR__) . '/config/db.php';

$uid    = (int)$_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

/* ── GET: check subscription status ─────────────────────── */
if ($method === 'GET') {
    $boxId = (int)($_GET['box_id'] ?? 0);
    if (!$boxId) { echo json_encode(['success' => false, 'message' => 'box_id manquant.']); exit; }
    try {
        $db   = getDB();
        $stmt = $db->prepare("
            SELECT id, status, frequency, next_delivery
            FROM box_subscriptions
            WHERE user_id=:uid AND box_id=:bid
        ");
        $stmt->execute([':uid' => $uid, ':bid' => $boxId]);
        $sub  = $stmt->fetch();
        echo json_encode(['success' => true, 'subscription' => $sub ?: null]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }

/* ── POST: subscribe / pause / cancel ───────────────────── */
} elseif ($method === 'POST') {
    $body      = json_decode(file_get_contents('php://input'), true) ?: [];
    $action    = $body['action'] ?? '';
    $boxId     = (int)($body['box_id'] ?? 0);

    if (!$boxId) { echo json_encode(['success' => false, 'message' => 'box_id manquant.']); exit; }

    try {
        $db = getDB();

        if ($action === 'subscribe') {
            // Verify box exists and is weekly + active
            $box = $db->prepare("SELECT id, title, category FROM weekly_boxes WHERE id=:id AND is_active=1");
            $box->execute([':id' => $boxId]);
            $b = $box->fetch();
            if (!$b) {
                echo json_encode(['success' => false, 'message' => 'Boîte indisponible.']); exit;
            }
            if ($b['category'] !== 'weekly') {
                echo json_encode(['success' => false, 'message' => 'Les abonnements ne sont disponibles que pour les boîtes hebdomadaires.']); exit;
            }

            $wilaya    = trim($body['wilaya']    ?? $_SESSION['wilaya']    ?? '');
            $commune   = trim($body['commune']   ?? $_SESSION['commune']   ?? '');
            $address   = trim($body['address']   ?? '');
            $frequency = in_array($body['frequency'] ?? '', ['weekly','biweekly']) ? $body['frequency'] : 'weekly';

            if (!$wilaya) {
                echo json_encode(['success' => false, 'message' => 'Wilaya de livraison requise.']); exit;
            }

            // Next delivery = next Monday
            $nextDelivery = (new DateTime('next Monday'))->format('Y-m-d');

            $db->prepare("
                INSERT INTO box_subscriptions
                  (box_id, user_id, delivery_wilaya, delivery_commune, delivery_address, frequency, status, next_delivery)
                VALUES
                  (:bid,:uid,:wilaya,:commune,:address,:freq,'active',:next)
                ON DUPLICATE KEY UPDATE
                  status='active', frequency=:freq2, delivery_wilaya=:wilaya2,
                  delivery_commune=:commune2, delivery_address=:address2,
                  next_delivery=:next2, updated_at=NOW()
            ")->execute([
                ':bid'      => $boxId,
                ':uid'      => $uid,
                ':wilaya'   => $wilaya,
                ':commune'  => $commune,
                ':address'  => $address,
                ':freq'     => $frequency,
                ':next'     => $nextDelivery,
                ':freq2'    => $frequency,
                ':wilaya2'  => $wilaya,
                ':commune2' => $commune,
                ':address2' => $address,
                ':next2'    => $nextDelivery,
            ]);
            echo json_encode(['success' => true, 'message' => 'Abonnement activé !', 'next_delivery' => $nextDelivery]);

        } elseif ($action === 'pause') {
            $db->prepare("UPDATE box_subscriptions SET status='paused', updated_at=NOW() WHERE user_id=:uid AND box_id=:bid")
               ->execute([':uid' => $uid, ':bid' => $boxId]);
            echo json_encode(['success' => true, 'message' => 'Abonnement mis en pause.']);

        } elseif ($action === 'cancel') {
            $db->prepare("UPDATE box_subscriptions SET status='cancelled', updated_at=NOW() WHERE user_id=:uid AND box_id=:bid")
               ->execute([':uid' => $uid, ':bid' => $boxId]);
            echo json_encode(['success' => true, 'message' => 'Abonnement annulé.']);

        } else {
            echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
        }
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false]);
}
