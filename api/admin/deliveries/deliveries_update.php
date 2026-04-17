<?php
// ================================================================
//  DAIRICH — Admin: Update Delivery
//  api/admin/delivery/update.php
//
//  Method  : POST
//  Headers : Authorization: Bearer <token>
//  Body    : {
//              "order_id": 5,
//              "status": "in_transit",
//              "expected_date": "2024-12-25",   (optional)
//              "delivered_date": "2024-12-24",  (optional)
//              "tracking_notes": "Out for delivery via BlueDart" (optional)
//            }
//
//  Admin uses this to update shipping progress.
//  Distributor can see these updates via delivery/track.php
// ================================================================

require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../config/helpers.php';
require_once __DIR__ . '/../../../config/admin_auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_method('POST');
require_admin_auth();

$body           = json_decode(file_get_contents('php://input'), true);
$order_id       = isset($body['order_id'])       ? (int)$body['order_id']          : 0;
$status         = trim($body['status']           ?? '');
$expected_date  = trim($body['expected_date']    ?? '');
$delivered_date = trim($body['delivered_date']   ?? '');
$tracking_notes = trim($body['tracking_notes']   ?? '');

$allowed = ['pending', 'in_transit', 'out_for_delivery', 'delivered', 'failed'];

if ($order_id < 1)
    json_response(false, 'order_id is required.', [], 422);

if (!in_array($status, $allowed))
    json_response(false, 'Invalid status. Allowed: ' . implode(', ', $allowed), [], 422);

// Validate date formats if provided
if ($expected_date !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $expected_date))
    json_response(false, 'expected_date must be in YYYY-MM-DD format.', [], 422);

if ($delivered_date !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $delivered_date))
    json_response(false, 'delivered_date must be in YYYY-MM-DD format.', [], 422);

$pdo = db();

// Check delivery record exists
$chk = $pdo->prepare("SELECT id FROM delivery WHERE order_id = :order_id");
$chk->execute([':order_id' => $order_id]);
if (!$chk->fetch())
    json_response(false, 'No delivery record found for this order.', [], 404);

try {
    $pdo->beginTransaction();

    // Update delivery
    $stmt = $pdo->prepare("
        UPDATE delivery
        SET    status         = :status,
               expected_date  = :expected_date,
               delivered_date = :delivered_date,
               tracking_notes = :tracking_notes
        WHERE  order_id = :order_id
    ");
    $stmt->execute([
        ':order_id'       => $order_id,
        ':status'         => $status,
        ':expected_date'  => $expected_date  ?: null,
        ':delivered_date' => $delivered_date ?: null,
        ':tracking_notes' => $tracking_notes ?: null,
    ]);

    // Keep order status in sync
    if ($status === 'in_transit' || $status === 'out_for_delivery') {
        $pdo->prepare("UPDATE orders SET status = 'dispatched' WHERE id = :id")
            ->execute([':id' => $order_id]);
    }

    if ($status === 'delivered') {
        $pdo->prepare("UPDATE orders SET status = 'delivered' WHERE id = :id")
            ->execute([':id' => $order_id]);
    }

    if ($status === 'failed') {
        $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = :id")
            ->execute([':id' => $order_id]);
    }

    $pdo->commit();

    json_response(true, 'Delivery updated successfully.');

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('[Dairich] admin/delivery/update.php: ' . $e->getMessage());
    json_response(false, 'Failed to update delivery. Please try again.', [], 500);
}