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

/* ── GET: list categories with product count ──────────────── */
if ($method === 'GET') {
    try {
        $db   = getDB();
        $rows = $db->query("
            SELECT c.id, c.slug, c.name_fr,
                   COUNT(p.id) AS product_count
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
            GROUP BY c.id
            ORDER BY c.id ASC
        ")->fetchAll();
        foreach ($rows as &$r) {
            $r['id']            = (int)$r['id'];
            $r['product_count'] = (int)$r['product_count'];
        }
        echo json_encode(['success' => true, 'categories' => $rows]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }

/* ── POST: create / update / delete ────────────────────────── */
} elseif ($method === 'POST') {
    $raw    = file_get_contents('php://input');
    $data   = json_decode($raw, true) ?: $_POST;
    $action = trim($data['action'] ?? '');

    /* create */
    if ($action === 'create') {
        $slug    = trim($data['slug']    ?? '');
        $name_fr = trim($data['name_fr'] ?? '');

        if (!$slug || !$name_fr) {
            echo json_encode(['success' => false, 'message' => 'Slug et nom requis.']);
            exit;
        }
        // slug: lowercase, only letters/numbers/hyphens
        $slug = strtolower(preg_replace('/[^a-z0-9\-]/', '', $slug));

        try {
            $db   = getDB();
            $stmt = $db->prepare('INSERT INTO categories (slug, name_fr) VALUES (:s, :n)');
            $stmt->execute([':s' => $slug, ':n' => $name_fr]);
            $newId = (int)$db->lastInsertId();
            echo json_encode(['success' => true, 'category' => [
                'id'            => $newId,
                'slug'          => $slug,
                'name_fr'       => $name_fr,
                'product_count' => 0,
            ]]);
        } catch (Throwable $e) {
            $msg = str_contains($e->getMessage(), 'Duplicate') ? 'Ce slug existe déjà.' : 'Erreur serveur.';
            echo json_encode(['success' => false, 'message' => $msg]);
        }

    /* update */
    } elseif ($action === 'update') {
        $id      = (int)($data['id']      ?? 0);
        $name_fr = trim($data['name_fr']  ?? '');
        $slug    = trim($data['slug']     ?? '');

        if (!$id || !$name_fr || !$slug) {
            echo json_encode(['success' => false, 'message' => 'Données invalides.']);
            exit;
        }
        $slug = strtolower(preg_replace('/[^a-z0-9\-]/', '', $slug));

        try {
            $db = getDB();
            $db->prepare('UPDATE categories SET slug=:s, name_fr=:n WHERE id=:id')
               ->execute([':s' => $slug, ':n' => $name_fr, ':id' => $id]);
            echo json_encode(['success' => true, 'slug' => $slug, 'name_fr' => $name_fr]);
        } catch (Throwable $e) {
            $msg = str_contains($e->getMessage(), 'Duplicate') ? 'Ce slug existe déjà.' : 'Erreur serveur.';
            echo json_encode(['success' => false, 'message' => $msg]);
        }

    /* delete */
    } elseif ($action === 'delete') {
        $id = (int)($data['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID manquant.']);
            exit;
        }
        try {
            $db = getDB();
            $db->prepare('DELETE FROM categories WHERE id=:id')->execute([':id' => $id]);
            echo json_encode(['success' => true]);
        } catch (Throwable $e) {
            // FK RESTRICT fires when products are linked
            $msg = str_contains($e->getMessage(), '1451') || str_contains($e->getMessage(), 'foreign key')
                ? 'Impossible de supprimer : des produits utilisent cette catégorie.'
                : 'Erreur serveur.';
            echo json_encode(['success' => false, 'message' => $msg]);
        }

    } else {
        echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
    }

} else {
    http_response_code(405);
    echo json_encode(['success' => false]);
}