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

/* ── GET: return all settings ───────────────────────────────── */
if ($method === 'GET') {
    try {
        $db   = getDB();
        $rows = $db->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
        $map  = [];
        foreach ($rows as $r) $map[$r['setting_key']] = $r['setting_value'];
        echo json_encode(['success' => true, 'settings' => $map]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }

/* ── POST: save settings ─────────────────────────────────────── */
} elseif ($method === 'POST') {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: $_POST;

    $allowed = ['commission_rate','delivery_fee','free_delivery_minimum','service_fee','service_fee_mode'];

    try {
        $db   = getDB();
        $stmt = $db->prepare(
            'INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v)
             ON DUPLICATE KEY UPDATE setting_value = :v2'
        );
        foreach ($data as $key => $val) {
            if (!in_array($key, $allowed, true)) continue;
            $stmt->execute([':k' => $key, ':v' => (string)$val, ':v2' => (string)$val]);
        }

        // Admin credential update
        $adminId = (int)$_SESSION['admin_id'];
        if (!empty($data['admin_password'])) {
            $hash = password_hash($data['admin_password'], PASSWORD_BCRYPT);
            $db->prepare('UPDATE admin_users SET password=:p WHERE id=:id')
               ->execute([':p' => $hash, ':id' => $adminId]);
        }
        if (!empty($data['admin_username'])) {
            $db->prepare('UPDATE admin_users SET username=:u WHERE id=:id')
               ->execute([':u' => $data['admin_username'], ':id' => $adminId]);
            $_SESSION['admin_name'] = $data['admin_username'];
        }

        echo json_encode(['success' => true]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false]);
}