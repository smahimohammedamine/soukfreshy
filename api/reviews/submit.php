<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non connecté.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

$body      = json_decode(file_get_contents('php://input'), true);
$productId = isset($body['product_id']) ? (int) $body['product_id']    : 0;
$rating    = isset($body['rating'])     ? (int) $body['rating']        : 0;
$comment   = isset($body['comment'])    ? trim((string) $body['comment']) : '';

if ($productId <= 0 || $rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

if (mb_strlen($comment) > 1000) {
    $comment = mb_substr($comment, 0, 1000);
}

require_once dirname(__DIR__) . '/config/db.php';

try {
    $db  = getDB();
    $uid = (int) $_SESSION['user_id'];

    // Upsert: insert or update if user already reviewed this product
    $stmt = $db->prepare(
        'INSERT INTO product_reviews (product_id, user_id, rating, comment)
         VALUES (:pid, :uid, :rating, :comment)
         ON DUPLICATE KEY UPDATE rating = :rating2, comment = :comment2, created_at = CURRENT_TIMESTAMP'
    );
    $stmt->execute([
        ':pid'      => $productId,
        ':uid'      => $uid,
        ':rating'   => $rating,
        ':comment'  => $comment !== '' ? $comment : null,
        ':rating2'  => $rating,
        ':comment2' => $comment !== '' ? $comment : null,
    ]);

    // Recalculate product aggregate rating and review count
    $db->prepare(
        'UPDATE products
         SET review_count = (SELECT COUNT(*) FROM product_reviews WHERE product_id = :pid),
             rating = COALESCE((SELECT ROUND(AVG(rating), 1) FROM product_reviews WHERE product_id = :pid2), 0)
         WHERE id = :pid3'
    )->execute([':pid' => $productId, ':pid2' => $productId, ':pid3' => $productId]);

    $stats = $db->prepare('SELECT rating, review_count AS reviews FROM products WHERE id = :pid');
    $stats->execute([':pid' => $productId]);
    $row = $stats->fetch();

    echo json_encode([
        'success' => true,
        'rating'  => (float) $row['rating'],
        'reviews' => (int)   $row['reviews'],
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
}
