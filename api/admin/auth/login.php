<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

require_once dirname(__DIR__, 2) . '/config/db.php';

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

if (!$username || !$password) {
    echo json_encode(['success' => false, 'message' => 'Identifiants manquants.']);
    exit;
}

try {
    $db   = getDB();
    $stmt = $db->prepare('SELECT id, username, password FROM admin_users WHERE username = :u LIMIT 1');
    $stmt->execute([':u' => $username]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password'])) {
        echo json_encode(['success' => false, 'message' => 'Identifiant ou mot de passe incorrect.']);
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['admin_id']   = $admin['id'];
    $_SESSION['admin_name'] = $admin['username'];

    echo json_encode(['success' => true, 'username' => $admin['username']]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
}