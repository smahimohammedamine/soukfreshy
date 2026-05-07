<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;

if ($productId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Produit invalide.']);
    exit;
}

require_once dirname(__DIR__) . '/config/db.php';

try {
    $db  = getDB();
    $uid = !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

    // Fetch all reviews except the current user's own (shown separately)
    if ($uid > 0) {
        $stmt = $db->prepare(
            'SELECT r.id, r.rating, r.comment, r.created_at, u.full_name
             FROM product_reviews r
             JOIN users u ON u.id = r.user_id
             WHERE r.product_id = :pid AND r.user_id != :uid
             ORDER BY r.created_at DESC
             LIMIT 50'
        );
        $stmt->execute([':pid' => $productId, ':uid' => $uid]);
    } else {
        $stmt = $db->prepare(
            'SELECT r.id, r.rating, r.comment, r.created_at, u.full_name
             FROM product_reviews r
             JOIN users u ON u.id = r.user_id
             WHERE r.product_id = :pid
             ORDER BY r.created_at DESC
             LIMIT 50'
        );
        $stmt->execute([':pid' => $productId]);
    }

    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        $row['id']     = (int) $row['id'];
        $row['rating'] = (int) $row['rating'];
    }
    unset($row);

    // Fetch the current user's own review
    $userReview = null;
    if ($uid > 0) {
        $stmt2 = $db->prepare(
            'SELECT id, rating, comment
             FROM product_reviews
             WHERE product_id = :pid AND user_id = :uid'
        );
        $stmt2->execute([':pid' => $productId, ':uid' => $uid]);
        $own = $stmt2->fetch();
        if ($own) {
            $userReview = [
                'id'      => (int) $own['id'],
                'rating'  => (int) $own['rating'],
                'comment' => $own['comment'],
            ];
        }
    }

    echo json_encode(['success' => true, 'reviews' => $rows, 'user_review' => $userReview]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
}
