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

/* ── GET: single user detail ── */
if ($method === 'GET' && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $db   = getDB();
        $stmt = $db->prepare("
            SELECT u.full_name, u.phone,
                   COALESCE(NULLIF(TRIM(u.email),   ''), '—') AS email,
                   COALESCE(NULLIF(TRIM(u.wilaya),  ''), '—') AS wilaya,
                   COALESCE(NULLIF(TRIM(u.commune), ''), '—') AS commune,
                   u.role, u.is_active,
                   DATE_FORMAT(u.created_at, '%d/%m/%Y') AS joined
            FROM users u WHERE u.id = :id LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        if (!$user) { http_response_code(404); echo json_encode(['success'=>false]); exit; }
        $user['is_active'] = (bool)$user['is_active'];
        echo json_encode(['success' => true, 'user' => $user]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }
    exit;
}

/* ── GET: all consumers + farmers ── */
if ($method === 'GET') {
    try {
        $db = getDB();

        $consumers = $db->query("
            SELECT u.id, u.full_name AS name,
                   COALESCE(NULLIF(TRIM(u.wilaya), ''), '—') AS wilaya,
                   COUNT(o.id) AS orders, COALESCE(SUM(o.total), 0) AS total,
                   u.is_active AS active
            FROM users u
            LEFT JOIN orders o ON o.consumer_id = u.id
            WHERE u.role = 'consumer'
            GROUP BY u.id
            ORDER BY total DESC
        ")->fetchAll();

        $farmers = $db->query("
            SELECT u.id, u.full_name AS name,
                   COALESCE(NULLIF(TRIM(u.wilaya), ''), '—') AS wilaya,
                   COUNT(p.id) AS products, u.is_active AS active
            FROM users u
            LEFT JOIN products p ON p.farmer_id = u.id AND p.is_active = 1
            WHERE u.role = 'farmer'
            GROUP BY u.id
            ORDER BY products DESC, u.full_name ASC
        ")->fetchAll();

        foreach ($consumers as &$c) {
            $c['id']     = (int)$c['id'];
            $c['orders'] = (int)$c['orders'];
            $c['total']  = (float)$c['total'];
            $c['active'] = (bool)$c['active'];
        }
        foreach ($farmers as &$f) {
            $f['id']       = (int)$f['id'];
            $f['products'] = (int)$f['products'];
            $f['active']   = (bool)$f['active'];
        }

        echo json_encode([
            'success'   => true,
            'consumers' => $consumers,
            'farmers'   => $farmers,
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }

/* ── POST: toggle user is_active ── */
} elseif ($method === 'POST') {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: $_POST;

    $id     = (int)($data['id']     ?? 0);
    $action = trim($data['action'] ?? '');

    if ($action !== 'toggle' || $id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Requête invalide.']);
        exit;
    }

    try {
        $db = getDB();
        $db->prepare('UPDATE users SET is_active = 1 - is_active WHERE id = :id')
           ->execute([':id' => $id]);
        $stmt = $db->prepare('SELECT is_active FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $newActive = (bool)$stmt->fetchColumn();
        echo json_encode(['success' => true, 'active' => $newActive]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false]);
}