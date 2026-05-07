<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

require_once dirname(__DIR__) . '/config/db.php';

try {
    $db   = getDB();
    $stmt = $db->prepare(
        'SELECT p.id, c.slug AS category, c.name_fr AS category_name,
                p.name, p.description, p.price, p.pricing_type, p.pricing_label,
                p.available_qty, p.image, p.availability
         FROM products p
         JOIN categories c ON c.id = p.category_id
         WHERE p.farmer_id = :farmer_id AND p.is_active = 1
         ORDER BY p.created_at DESC'
    );
    $stmt->execute([':farmer_id' => (int) $_SESSION['user_id']]);
    $products = $stmt->fetchAll();

    echo json_encode(['success' => true, 'products' => $products]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
}
