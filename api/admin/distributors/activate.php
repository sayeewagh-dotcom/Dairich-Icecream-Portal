<?php
// ================================================================
//  DAIRICH — Admin: Activate Distributor
//  api/admin/distributors/activate.php
//
//  Method  : POST
//  Headers : Authorization: Bearer <token>
//  Body    : { "distributor_id": 2 }
//
//  Activates a pending distributor so they can log in.
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
$distributor_id = isset($body['distributor_id']) ? (int)$body['distributor_id'] : 0;

if ($distributor_id < 1)
    json_response(false, 'distributor_id is required.', [], 422);

$stmt = db()->prepare("UPDATE distributors SET is_active = TRUE WHERE id = :id");
$stmt->execute([':id' => $distributor_id]);

if ($stmt->rowCount() === 0)
    json_response(false, 'Distributor not found.', [], 404);

json_response(true, 'Distributor account activated. They can now log in.');