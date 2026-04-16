<?php
// ================================================================
//  DAIRICH — Admin: Delete Product
//  api/admin/products/delete.php
//
//  Method  : POST
//  Headers : Authorization: Bearer <token>
//  Body    : { "id": 3 }
//
//  Soft delete only — sets is_active = FALSE.
//  We never hard delete products as they may be linked to orders.
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

$body = json_decode(file_get_contents('php://input'), true);
$id   = isset($body['id']) ? (int)$body['id'] : 0;

if ($id < 1)
    json_response(false, 'Product id is required.', [], 422);

$stmt = db()->prepare("UPDATE products SET is_active = FALSE WHERE id = :id");
$stmt->execute([':id' => $id]);

if ($stmt->rowCount() === 0)
    json_response(false, 'Product not found.', [], 404);

json_response(true, 'Product deactivated (soft deleted) successfully.');