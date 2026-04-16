<?php
// ================================================================
//  DAIRICH — Admin: Update Enquiry Status
//  api/admin/enquiries/update_status.php
//
//  Method  : POST
//  Headers : Authorization: Bearer <token>
//  Body    : { "enquiry_id": 3, "status": "reviewed" }
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

$body       = json_decode(file_get_contents('php://input'), true);
$enquiry_id = isset($body['enquiry_id']) ? (int)$body['enquiry_id'] : 0;
$status     = trim($body['status'] ?? '');
$allowed    = ['new', 'reviewed', 'contacted', 'closed'];

if ($enquiry_id < 1)
    json_response(false, 'enquiry_id is required.', [], 422);

if (!in_array($status, $allowed))
    json_response(false, 'Invalid status. Must be: ' . implode(', ', $allowed), [], 422);

$pdo  = db();
$stmt = $pdo->prepare("UPDATE enquiries SET status = :status WHERE id = :id");
$stmt->execute([':status' => $status, ':id' => $enquiry_id]);

if ($stmt->rowCount() === 0)
    json_response(false, 'Enquiry not found.', [], 404);

json_response(true, 'Enquiry status updated to ' . $status . '.');