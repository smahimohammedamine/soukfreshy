<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

require_once dirname(__DIR__) . '/config/db.php';

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$identifier = trim($data['identifier'] ?? '');
$password   = $data['password'] ?? '';
$role       = trim($data['role'] ?? '');

if (!$identifier || !$password || !in_array($role, ['consumer', 'farmer'], true)) {
    echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis.']);
    exit;
}

try {
    $db   = getDB();
    $stmt = $db->prepare(
        'SELECT id, full_name, phone, email, password, role, wilaya, commune, is_active
         FROM users
         WHERE (phone = :phone OR email = :email) AND role = :role
         LIMIT 1'
    );
    $stmt->execute([':phone' => $identifier, ':email' => $identifier, ':role' => $role]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Identifiants incorrects ou rôle invalide.']);
        exit;
    }

    if (!$user['is_active']) {
        echo json_encode(['success' => false, 'message' => 'Compte désactivé. Contactez le support.']);
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role']      = $user['role'];
    $_SESSION['phone']     = $user['phone'];
    $_SESSION['email']     = $user['email'];
    $_SESSION['wilaya']    = $user['wilaya'];
    $_SESSION['commune']   = $user['commune'];

    echo json_encode([
        'success' => true,
        'user'    => [
            'id'        => $user['id'],
            'full_name' => $user['full_name'],
            'role'      => $user['role'],
            'wilaya'    => $user['wilaya'],
            'commune'   => $user['commune'],
        ],
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur. Réessayez plus tard.']);
}