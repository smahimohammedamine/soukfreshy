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

try {
    $db = getDB();

    /* ── GET ── */
    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'history';

        if ($action === 'current') {
            // Current week's client (week_start = last Monday)
            $monday = date('Y-m-d', strtotime('monday this week'));
            $row = $db->prepare("
                SELECT c.*, u.full_name AS user_name, u.phone AS user_phone, u.email AS user_email,
                       b.title AS box_title, b.image AS box_image, b.price AS box_price
                FROM client_of_week c
                JOIN users u ON u.id = c.user_id
                JOIN weekly_boxes b ON b.id = c.box_id
                WHERE c.week_start = ?
                ORDER BY c.created_at DESC LIMIT 1
            ");
            $row->execute([$monday]);
            $current = $row->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'current' => $current ?: null, 'week_start' => $monday]);
            exit;
        }

        // history — all entries newest first
        $rows = $db->query("
            SELECT c.*, u.full_name AS user_name, u.phone AS user_phone,
                   b.title AS box_title, b.price AS box_price
            FROM client_of_week c
            JOIN users u ON u.id = c.user_id
            JOIN weekly_boxes b ON b.id = c.box_id
            ORDER BY c.week_start DESC, c.created_at DESC
            LIMIT 50
        ")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'history' => $rows]);
        exit;
    }

    /* ── POST ── */
    if ($method === 'POST') {
        $body   = json_decode(file_get_contents('php://input'), true) ?? [];
        $action = $body['action'] ?? '';

        if ($action === 'assign') {
            $userId = (int)($body['user_id'] ?? 0);
            $boxId  = (int)($body['box_id']  ?? 0);
            $note   = trim($body['note'] ?? '');
            $monday = date('Y-m-d', strtotime('monday this week'));

            if (!$userId || !$boxId) {
                echo json_encode(['success' => false, 'message' => 'Client et pack requis.']);
                exit;
            }

            // Replace any existing entry for this week
            $db->prepare("DELETE FROM client_of_week WHERE week_start = ?")->execute([$monday]);

            $stmt = $db->prepare("
                INSERT INTO client_of_week (user_id, box_id, week_start, note, status)
                VALUES (?, ?, ?, ?, 'active')
            ");
            $stmt->execute([$userId, $boxId, $monday, $note ?: null]);
            $newId = $db->lastInsertId();

            $row = $db->prepare("
                SELECT c.*, u.full_name AS user_name, u.phone AS user_phone, u.email AS user_email,
                       b.title AS box_title, b.image AS box_image, b.price AS box_price
                FROM client_of_week c
                JOIN users u ON u.id = c.user_id
                JOIN weekly_boxes b ON b.id = c.box_id
                WHERE c.id = ?
            ");
            $row->execute([$newId]);
            $entry = $row->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'entry' => $entry]);
            exit;
        }

        if ($action === 'update_status') {
            $id     = (int)($body['id']     ?? 0);
            $status = $body['status'] ?? '';
            if (!$id || !in_array($status, ['active','delivered','cancelled'])) {
                echo json_encode(['success' => false, 'message' => 'Paramètres invalides.']);
                exit;
            }
            $db->prepare("UPDATE client_of_week SET status = ? WHERE id = ?")->execute([$status, $id]);
            echo json_encode(['success' => true]);
            exit;
        }

        if ($action === 'delete') {
            $id = (int)($body['id'] ?? 0);
            if (!$id) { echo json_encode(['success' => false]); exit; }
            $db->prepare("DELETE FROM client_of_week WHERE id = ?")->execute([$id]);
            echo json_encode(['success' => true]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
