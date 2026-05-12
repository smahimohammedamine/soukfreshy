<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=60');

require_once __DIR__ . '/config/db.php';

// Optional filter: ?category=weekly|season|all
$category = $_GET['category'] ?? 'all';

// Current season by month
$m = (int)date('n');
if ($m >= 3 && $m <= 5)      $currentSeason = 'spring';
elseif ($m >= 6 && $m <= 8)  $currentSeason = 'summer';
elseif ($m >= 9 && $m <= 11) $currentSeason = 'autumn';
else                          $currentSeason = 'winter';

try {
    $db = getDB();

    $where  = "wb.is_active = 1";
    $params = [];

    if ($category === 'weekly') {
        $where .= " AND wb.category = 'weekly'";
    } elseif ($category === 'season') {
        $where .= " AND wb.category = 'season'";
        $where .= " AND (wb.available_from IS NULL OR wb.available_from <= CURDATE())";
        $where .= " AND (wb.available_until IS NULL OR wb.available_until >= CURDATE())";
    } elseif ($category === 'all') {
        // weekly always shown; season boxes only if within date window
        $where .= " AND (
            wb.category = 'weekly'
            OR (
                wb.category = 'season'
                AND (wb.available_from IS NULL OR wb.available_from <= CURDATE())
                AND (wb.available_until IS NULL OR wb.available_until >= CURDATE())
            )
        )";
    }

    $boxes = $db->query("
        SELECT wb.id, wb.category, wb.title, wb.description, wb.image,
               wb.price, wb.original_price, wb.quantity, wb.products,
               wb.box_type, wb.free_delivery, wb.badge, wb.season,
               wb.available_from, wb.available_until, wb.orders_count,
               CASE WHEN wb.box_type = 'mixed' THEN 'Mixte' ELSE COALESCE(c.name_fr, wb.box_type) END AS box_type_label
        FROM weekly_boxes wb
        LEFT JOIN categories c ON c.slug = wb.box_type
        WHERE $where
        ORDER BY wb.category ASC, wb.created_at DESC
    ")->fetchAll();

    foreach ($boxes as &$b) {
        $b['products']       = json_decode($b['products'] ?? '[]', true) ?: [];
        $b['price']          = (float)$b['price'];
        $b['original_price'] = $b['original_price'] !== null ? (float)$b['original_price'] : null;
        $b['quantity']       = (int)$b['quantity'];
        $b['orders_count']   = (int)($b['orders_count'] ?? 0);
        $b['free_delivery']  = (bool)$b['free_delivery'];

        // Compute discount_pct
        if ($b['original_price'] && $b['original_price'] > $b['price']) {
            $b['discount_pct'] = (int)round(($b['original_price'] - $b['price']) / $b['original_price'] * 100);
        } else {
            $b['discount_pct'] = 0;
        }

        // Days remaining for season boxes
        if ($b['category'] === 'season' && !empty($b['available_until'])) {
            $until = new DateTime($b['available_until']);
            $now   = new DateTime('today');
            $diff  = $now->diff($until);
            $b['days_remaining'] = $diff->invert ? 0 : (int)$diff->days;
        } else {
            $b['days_remaining'] = null;
        }
    }
    unset($b);

    echo json_encode([
        'success'        => true,
        'boxes'          => $boxes,
        'current_season' => $currentSeason,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'boxes' => [], 'current_season' => $currentSeason ?? 'winter']);
}
