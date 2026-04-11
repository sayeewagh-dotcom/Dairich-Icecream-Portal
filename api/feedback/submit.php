<?php
// ================================================================
//  DAIRICH — Feedback: Submit
//  api/feedback/submit.php
//
//  Method  : POST
//  Headers : Authorization: Bearer <token>
//  Body    : order_id, rating (1-5), message (optional)
//
//  Only allows feedback on delivered orders.
//  One feedback per order per distributor.
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

$body     = json_decode(file_get_contents('php://input'), true);
$order_id = isset($body['order_id']) ? (int)$body['order_id'] : 0;
$rating   = isset($body['rating'])   ? (int)$body['rating']   : 0;
$message  = trim($body['message'] ?? '');

// ── Validate ─────────────────────────────────────────────────────
if ($order_id < 1)
    json_response(false, 'order_id is required.', [], 422);

if ($rating < 1 || $rating > 5)
    json_response(false, 'Rating must be between 1 and 5.', [], 422);

$pdo = db();

// Verify order belongs to distributor and is delivered
$order = $pdo->prepare("
    SELECT o.id, d.status AS delivery_status
    FROM   orders o
    JOIN   delivery d ON d.order_id = o.id
    WHERE  o.id = :order_id
      AND  o.distributor_id = :did
");
$order->execute([':order_id' => $order_id, ':did' => $distributor['id']]);
$row = $order->fetch();

if (!$row)
    json_response(false, 'Order not found.', [], 404);

if ($row['delivery_status'] !== 'delivered')
    json_response(false, 'Feedback can only be submitted after the order is delivered.', [], 403);

// Check for duplicate feedback
$dup = $pdo->prepare("
    SELECT id FROM feedback
    WHERE order_id = :order_id AND distributor_id = :did
");
$dup->execute([':order_id' => $order_id, ':did' => $distributor['id']]);
if ($dup->fetch())
    json_response(false, 'You have already submitted feedback for this order.', [], 409);

try {
    $pdo->prepare("
        INSERT INTO feedback (distributor_id, order_id, rating, message, submitted_at)
        VALUES (:distributor_id, :order_id, :rating, :message, NOW())
    ")->execute([
        ':distributor_id' => $distributor['id'],
        ':order_id'       => $order_id,
        ':rating'         => $rating,
        ':message'        => $message ?: null,
    ]);

    json_response(true, 'Thank you for your feedback!', [], 201);

} catch (PDOException $e) {
    error_log('[Dairich] feedback/submit.php: ' . $e->getMessage());
    json_response(false, 'Failed to submit feedback. Please try again.', [], 500);
}