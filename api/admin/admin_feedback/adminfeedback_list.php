<?php
// ================================================================
//  DAIRICH — Admin: List All Feedback
//  api/admin/feedback/list.php
//
//  Method  : GET
//  Headers : Authorization: Bearer <token>
//  Params  : ?distributor_id=2  (optional filter)
//            ?rating=5          (optional filter)
// ================================================================

require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../config/helpers.php';
require_once __DIR__ . '/../../../config/admin_auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization');

require_method('GET');
require_admin_auth();

$pdo            = db();
$distributor_id = isset($_GET['distributor_id']) ? (int)$_GET['distributor_id'] : 0;
$rating         = isset($_GET['rating'])         ? (int)$_GET['rating']         : 0;

$where  = [];
$params = [];

if ($distributor_id > 0) {
    $where[]                   = "f.distributor_id = :did";
    $params[':did']            = $distributor_id;
}
if ($rating >= 1 && $rating <= 5) {
    $where[]                   = "f.rating = :rating";
    $params[':rating']         = $rating;
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare("
    SELECT
        f.id,
        f.rating,
        f.message,
        f.submitted_at,
        d.company_name  AS distributor_name,
        d.email         AS distributor_email,
        o.id            AS order_id,
        o.order_date,
        o.total_amount
    FROM   feedback f
    JOIN   distributors d ON d.id = f.distributor_id
    JOIN   orders o       ON o.id = f.order_id
    $whereSQL
    ORDER  BY f.submitted_at DESC
");
$stmt->execute($params);
$feedback = $stmt->fetchAll();

// Summary stats
$avg_rating = 0;
if (!empty($feedback)) {
    $avg_rating = round(array_sum(array_column($feedback, 'rating')) / count($feedback), 1);
}

json_response(true, 'OK', [
    'feedback'   => $feedback,
    'total'      => count($feedback),
    'avg_rating' => $avg_rating,
]);