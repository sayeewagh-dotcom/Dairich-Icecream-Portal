<?php
// ================================================================
//  DAIRICH — Admin: Update Order Status
//  api/admin/orders/update_status.php
//
//  Method  : POST
//  Headers : Authorization: Bearer <token>
//  Body    : { "order_id": 5, "status": "confirmed" }
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

$body     = json_decode(file_get_contents('php://input'), true);
$order_id = isset($body['order_id']) ? (int)$body['order_id'] : 0;
$status   = trim($body['status'] ?? '');
$allowed  = ['pending', 'confirmed', 'processing', 'dispatched', 'delivered', 'cancelled'];

if ($order_id < 1)
    json_response(false, 'order_id is required.', [], 422);

if (!in_array($status, $allowed))
    json_response(false, 'Invalid status. Allowed: ' . implode(', ', $allowed), [], 422);

$pdo  = db();
$stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
$stmt->execute([':status' => $status, ':id' => $order_id]);

if ($stmt->rowCount() === 0)
    json_response(false, 'Order not found.', [], 404);

// If order is marked delivered, also update the delivery record
if ($status === 'delivered') {
    $pdo->prepare("
        UPDATE delivery
        SET    status         = 'delivered',
               delivered_date = NOW()
        WHERE  order_id = :order_id
    ")->execute([':order_id' => $order_id]);
}

json_response(true, 'Order status updated to ' . $status . '.');
