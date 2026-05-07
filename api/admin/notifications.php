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

/* ── GET: list notifications ────────────────────────────────── */
if ($method === 'GET') {
    try {
        $db = getDB();
        $rows = $db->query("
            SELECT id, type, message AS msg, is_read AS `read`, created_at
            FROM notifications
            ORDER BY created_at DESC
            LIMIT 60
        ")->fetchAll();

        $now = time();
        foreach ($rows as &$n) {
            $n['read'] = (bool)$n['read'];
            $diff = $now - strtotime($n['created_at']);
            if ($diff < 60)          $n['time'] = "À l'instant";
            elseif ($diff < 3600)    $n['time'] = 'Il y a ' . floor($diff / 60) . ' min';
            elseif ($diff < 86400)   $n['time'] = 'Il y a ' . floor($diff / 3600) . 'h';
            else                     $n['time'] = 'Hier';
            unset($n['created_at']);
        }

        $unread = (int) $db->query('SELECT COUNT(*) FROM notifications WHERE is_read=0')->fetchColumn();

        echo json_encode([
            'success'       => true,
            'notifications' => $rows,
            'unread'        => $unread,
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }

/* ── POST: mark all as read ─────────────────────────────────── */
} elseif ($method === 'POST') {
    try {
        $db = getDB();
        $db->exec('UPDATE notifications SET is_read=1 WHERE is_read=0');
        echo json_encode(['success' => true]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false]);
}