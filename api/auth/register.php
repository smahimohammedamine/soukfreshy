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

$fullName = trim($data['full_name'] ?? '');
$phone    = trim($data['phone'] ?? '');
$email    = trim(strtolower($data['email'] ?? '')) ?: null;
$password = $data['password'] ?? '';
$role     = trim($data['role'] ?? '');
$wilaya   = trim($data['wilaya'] ?? '') ?: null;
$commune  = trim($data['commune'] ?? '') ?: null;

if (!$fullName || !$phone || !$password || !in_array($role, ['consumer', 'farmer'], true)) {
    echo json_encode(['success' => false, 'message' => 'Nom, téléphone et mot de passe sont obligatoires.']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caractères.']);
    exit;
}

if ($role === 'farmer' && !$wilaya) {
    echo json_encode(['success' => false, 'message' => 'La wilaya est obligatoire pour les agriculteurs.']);
    exit;
}

try {
    $db = getDB();

    $chk = $db->prepare('SELECT id FROM users WHERE phone = :phone LIMIT 1');
    $chk->execute([':phone' => $phone]);
    if ($chk->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Ce numéro de téléphone est déjà utilisé.']);
        exit;
    }

    if ($email) {
        $chk2 = $db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $chk2->execute([':email' => $email]);
        if ($chk2->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé.']);
            exit;
        }
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $db->prepare(
        'INSERT INTO users (full_name, phone, email, password, role, wilaya, commune)
         VALUES (:full_name, :phone, :email, :password, :role, :wilaya, :commune)'
    );
    $stmt->execute([
        ':full_name' => $fullName,
        ':phone'     => $phone,
        ':email'     => $email,
        ':password'  => $hash,
        ':role'      => $role,
        ':wilaya'    => $wilaya,
        ':commune'   => $commune,
    ]);
    $userId = (int) $db->lastInsertId();

    session_regenerate_id(true);
    $_SESSION['user_id']   = $userId;
    $_SESSION['full_name'] = $fullName;
    $_SESSION['role']      = $role;
    $_SESSION['phone']     = $phone;
    $_SESSION['email']     = $email;
    $_SESSION['wilaya']    = $wilaya;
    $_SESSION['commune']   = $commune;

    echo json_encode([
        'success' => true,
        'user'    => [
            'id'        => $userId,
            'full_name' => $fullName,
            'role'      => $role,
            'wilaya'    => $wilaya,
            'commune'   => $commune,
        ],
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur. Réessayez plus tard.']);
}