<?php
// ================================================================
//  DAIRICH — Orders: List My Orders
//  api/orders/list.php
//
//  Method  : GET
//  Headers : Authorization: Bearer <token>
//  Returns : All orders for the authenticated distributor,
//            with items and delivery status.
// ================================================================

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization');

require_method('GET');

$distributor = require_distributor_auth();

$pdo = db();

// Fetch orders
$orders = $pdo->prepare("
    SELECT
        o.id,
        o.total_amount,
        o.status,
        o.order_date,
        o.notes,
        d.status         AS delivery_status,
        d.expected_date,
        d.delivered_date,
        d.tracking_notes
    FROM   orders o
    LEFT   JOIN delivery d ON d.order_id = o.id
    WHERE  o.distributor_id = :did
    ORDER  BY o.order_date DESC
");
$orders->execute([':did' => $distributor['id']]);
$orderRows = $orders->fetchAll();

if (empty($orderRows)) {
    json_response(true, 'No orders found.', ['orders' => []]);
}

// Fetch items for each order
$orderIds    = array_column($orderRows, 'id');
$ph          = implode(',', array_fill(0, count($orderIds), '?'));
$itemsStmt   = $pdo->prepare("
    SELECT
        oi.order_id,
        oi.quantity_boxes,
        oi.price_per_box,
        p.name  AS product_name,
        p.image_path
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id IN ($ph)
");
$itemsStmt->execute($orderIds);
$allItems = $itemsStmt->fetchAll();

// Group items by order_id
$itemsByOrder = [];
foreach ($allItems as $item)
    $itemsByOrder[$item['order_id']][] = $item;

// Merge
foreach ($orderRows as &$order)
    $order['items'] = $itemsByOrder[$order['id']] ?? [];

json_response(true, 'OK', ['orders' => $orderRows]);