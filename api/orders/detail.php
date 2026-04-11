<?php
// ================================================================
//  DAIRICH — Orders: Get Single Order
//  api/orders/detail.php
//
//  Method  : GET
//  Headers : Authorization: Bearer <token>
//  Params  : ?order_id=5
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

// Fetch order — must belong to this distributor
$stmt = $pdo->prepare("
    SELECT
        o.id, o.total_amount, o.status, o.order_date, o.notes,
        d.status         AS delivery_status,
        d.expected_date,
        d.delivered_date,
        d.tracking_notes
    FROM   orders o
    LEFT   JOIN delivery d ON d.order_id = o.id
    WHERE  o.id = :order_id
      AND  o.distributor_id = :did
");
$stmt->execute([':order_id' => $order_id, ':did' => $distributor['id']]);
$order = $stmt->fetch();

if (!$order)
    json_response(false, 'Order not found.', [], 404);

// Fetch items
$items = $pdo->prepare("
    SELECT
        oi.quantity_boxes,
        oi.price_per_box,
        p.id   AS product_id,
        p.name AS product_name,
        p.image_path
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = :order_id
");
$items->execute([':order_id' => $order_id]);
$order['items'] = $items->fetchAll();

json_response(true, 'OK', ['order' => $order]);