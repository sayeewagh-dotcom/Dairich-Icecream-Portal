<?php
// ================================================================
//  DAIRICH — Distributor: Place Order
//  api/orders/place.php
//
//  Method  : POST
//  Headers : Authorization: Bearer <token>
//  Body    : { "items": [{"product_id": 1, "quantity_boxes": 10}], "notes": "..." }
// ================================================================

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_method('POST');

$distributor = require_distributor_auth();

$body  = json_decode(file_get_contents('php://input'), true);
$items = $body['items'] ?? [];
$notes = trim($body['notes'] ?? '');

if (empty($items))
    json_response(false, 'At least one item is required.', [], 422);

foreach ($items as $item) {
    if (empty($item['product_id']) || empty($item['quantity_boxes']) || $item['quantity_boxes'] < 1)
        json_response(false, 'Each item must have a valid product_id and quantity_boxes.', [], 422);
}

$pdo = db();

try {
    $pdo->beginTransaction();

    // Get prices for each product
    $ids   = array_column($items, 'product_id');
    $ph    = implode(',', array_fill(0, count($ids), '?'));
    $prods = $pdo->prepare("SELECT id, name, price_per_box FROM products WHERE id IN ($ph) AND is_active = TRUE");
    $prods->execute($ids);
    $productMap = [];
    foreach ($prods->fetchAll() as $p) $productMap[$p['id']] = $p;

    if (count($productMap) !== count($ids))
        json_response(false, 'One or more products are invalid or inactive.', [], 422);

    // Calculate total
    $total = 0;
    foreach ($items as $item) {
        $price  = $productMap[$item['product_id']]['price_per_box'] ?? 0;
        $total += $item['quantity_boxes'] * $price;
    }

    // Insert order
    $stmt = $pdo->prepare("
        INSERT INTO orders (distributor_id, total_amount, status, order_date, notes)
        VALUES (:distributor_id, :total_amount, 'pending', NOW(), :notes)
        RETURNING id
    ");
    $stmt->execute([
        ':distributor_id' => $distributor['id'],
        ':total_amount'   => $total,
        ':notes'          => $notes ?: null,
    ]);
    $order_id = (int) $stmt->fetchColumn();

    // Insert order items with real prices
    $itemStmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity_boxes, price_per_box)
        VALUES (:order_id, :product_id, :quantity_boxes, :price_per_box)
    ");
    foreach ($items as $item) {
        $price = $productMap[$item['product_id']]['price_per_box'] ?? 0;
        $itemStmt->execute([
            ':order_id'       => $order_id,
            ':product_id'     => $item['product_id'],
            ':quantity_boxes' => $item['quantity_boxes'],
            ':price_per_box'  => $price,
        ]);
    }

    // Create delivery record
    $pdo->prepare("
        INSERT INTO delivery (order_id, status)
        VALUES (:order_id, 'pending')
    ")->execute([':order_id' => $order_id]);

    $pdo->commit();

    json_response(true, 'Order placed successfully!', [
        'order_id'     => $order_id,
        'total_amount' => $total
    ], 201);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('[Dairich] orders/place.php: ' . $e->getMessage());
    json_response(false, 'Failed to place order. Please try again.', [], 500);
}