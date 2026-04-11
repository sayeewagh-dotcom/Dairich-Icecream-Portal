<?php
// ================================================================
//  DAIRICH — Feedback: My Feedback List
//  api/feedback/list.php
//
//  Method  : GET
//  Headers : Authorization: Bearer <token>
//  Returns : All feedback submitted by the logged-in distributor.
// ================================================================

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization');

require_method('GET');

$distributor = require_distributor_auth();

$stmt = db()->prepare("
    SELECT
        f.id,
        f.rating,
        f.message,
        f.submitted_at,
        o.id           AS order_id,
        o.order_date,
        o.total_amount
    FROM   feedback f
    JOIN   orders o ON o.id = f.order_id
    WHERE  f.distributor_id = :did
    ORDER  BY f.submitted_at DESC
");
$stmt->execute([':did' => $distributor['id']]);
$feedback = $stmt->fetchAll();

json_response(true, 'OK', ['feedback' => $feedback]);