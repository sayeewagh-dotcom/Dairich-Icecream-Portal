<?php
// ================================================================
//  DAIRICH — Delivery: Track
//  api/delivery/track.php
//
//  Method  : GET
//  Headers : Authorization: Bearer <token>
//  Params  : ?order_id=5
//
//  Returns delivery status + tracking notes for a given order.
// ================================================================

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization');

require_method('GET');

$distributor = require_distributor_auth();

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($order_id < 1)
    json_response(false, 'order_id is required.', [], 422);

$pdo = db();

// Verify the order belongs to this distributor
$chk = $pdo->prepare("SELECT id FROM orders WHERE id = :id AND distributor_id = :did");
$chk->execute([':id' => $order_id, ':did' => $distributor['id']]);
if (!$chk->fetch())
    json_response(false, 'Order not found.', [], 404);

// Fetch delivery
$stmt = $pdo->prepare("
    SELECT
        id,
        order_id,
        status,
        expected_date,
        delivered_date,
        tracking_notes
    FROM delivery
    WHERE order_id = :order_id
");
$stmt->execute([':order_id' => $order_id]);
$delivery = $stmt->fetch();

if (!$delivery)
    json_response(false, 'No delivery record found for this order.', [], 404);

json_response(true, 'OK', ['delivery' => $delivery]);