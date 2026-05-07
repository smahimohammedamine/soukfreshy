<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

$category = $_GET['category'] ?? 'all';
$search   = trim($_GET['search'] ?? '');

// Whitelist category values
$validCategories = ['vegetables', 'fruits', 'herbs'];
if ($category !== 'all' && !in_array($category, $validCategories, true)) {
    $category = 'all';
}

try {
    $db = getDB();

    $sql = 'SELECT p.id, c.slug AS category,
                   p.name, p.description,
                   p.price, p.pricing_label AS unit,
                   p.available_qty AS qty, p.region,
                   p.image, p.availability,
                   p.rating, p.review_count AS reviews
            FROM products p
            JOIN categories c ON c.id = p.category_id
            WHERE p.is_active = 1';

    $params = [];

    if ($category !== 'all') {
        $sql .= ' AND c.slug = :category';
        $params[':category'] = $category;
    }

    if ($search !== '') {
        $sql .= ' AND (p.name LIKE :search OR p.region LIKE :search2)';
        $params[':search']  = '%' . $search . '%';
        $params[':search2'] = '%' . $search . '%';
    }

    $sql .= ' ORDER BY p.created_at DESC';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        $row['id']      = (int)   $row['id'];
        $row['price']   = (float) $row['price'];
        $row['qty']     = (int)   $row['qty'];
        $row['rating']  = (float) $row['rating'];
        $row['reviews'] = (int)   $row['reviews'];
    }
    unset($row);

    echo json_encode(['success' => true, 'products' => $rows]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
}
