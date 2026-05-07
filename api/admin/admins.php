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

/* ── GET: list all admins ───────────────────────────────────── */
if ($method === 'GET') {
    try {
        $db   = getDB();
        $rows = $db->query("
            SELECT id, username, DATE_FORMAT(created_at,'%d/%m/%Y') AS created_at
            FROM admin_users
            ORDER BY id ASC
        ")->fetchAll();
        foreach ($rows as &$r) $r['id'] = (int)$r['id'];
        echo json_encode(['success' => true, 'admins' => $rows]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }

/* ── POST: create / update / delete ────────────────────────── */
} elseif ($method === 'POST') {
    $raw    = file_get_contents('php://input');
    $data   = json_decode($raw, true) ?: $_POST;
    $action = trim($data['action'] ?? '');
    $selfId = (int)$_SESSION['admin_id'];

    /* create */
    if ($action === 'create') {
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';

        if (!$username || strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Nom d\'utilisateur et mot de passe (≥ 6 car.) requis.']);
            exit;
        }

        try {
            $db   = getDB();
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare('INSERT INTO admin_users (username, password) VALUES (:u, :p)');
            $stmt->execute([':u' => $username, ':p' => $hash]);
            $newId = (int)$db->lastInsertId();
            echo json_encode(['success' => true, 'admin' => [
                'id'         => $newId,
                'username'   => $username,
                'created_at' => date('d/m/Y'),
            ]]);
        } catch (Throwable $e) {
            $msg = str_contains($e->getMessage(), 'Duplicate') ? 'Ce nom d\'utilisateur existe déjà.' : 'Erreur serveur.';
            echo json_encode(['success' => false, 'message' => $msg]);
        }

    /* update */
    } elseif ($action === 'update') {
        $id       = (int)($data['id'] ?? 0);
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';

        if (!$id || !$username) {
            echo json_encode(['success' => false, 'message' => 'Données invalides.']);
            exit;
        }

        try {
            $db = getDB();
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $db->prepare('UPDATE admin_users SET username=:u, password=:p WHERE id=:id')
                   ->execute([':u' => $username, ':p' => $hash, ':id' => $id]);
            } else {
                $db->prepare('UPDATE admin_users SET username=:u WHERE id=:id')
                   ->execute([':u' => $username, ':id' => $id]);
            }
            // Keep session name in sync if editing self
            if ($id === $selfId) $_SESSION['admin_name'] = $username;
            echo json_encode(['success' => true]);
        } catch (Throwable $e) {
            $msg = str_contains($e->getMessage(), 'Duplicate') ? 'Ce nom d\'utilisateur existe déjà.' : 'Erreur serveur.';
            echo json_encode(['success' => false, 'message' => $msg]);
        }

    /* delete */
    } elseif ($action === 'delete') {
        $id = (int)($data['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID manquant.']);
            exit;
        }
        if ($id === $selfId) {
            echo json_encode(['success' => false, 'message' => 'Impossible de supprimer votre propre compte.']);
            exit;
        }
        try {
            $db = getDB();
            $db->prepare('DELETE FROM admin_users WHERE id=:id')->execute([':id' => $id]);
            echo json_encode(['success' => true]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
        }

    } else {
        echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false]);
}