<?php
// ================================================================
//  DAIRICH — Admin: Update Product
//  api/admin/products/update.php
//
//  Method  : POST
//  Headers : Authorization: Bearer <token>
//  Body    : { "id": 1, "name": "...", "description": "...",
//              "tag": "...", "image_path": "...", "is_active": true }
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

$body        = json_decode(file_get_contents('php://input'), true);
$id          = isset($body['id'])          ? (int)$body['id']          : 0;
$name        = trim($body['name']          ?? '');
$description = trim($body['description']   ?? '');
$tag         = trim($body['tag']           ?? '');
$image_path  = trim($body['image_path']    ?? '');
$is_active   = isset($body['is_active'])   ? (bool)$body['is_active']  : true;

if ($id < 1)   json_response(false, 'Product id is required.', [], 422);
if ($name === '') json_response(false, 'Product name is required.', [], 422);

$stmt = db()->prepare("
    UPDATE products
    SET    name        = :name,
           description = :description,
           tag         = :tag,
           image_path  = :image_path,
           is_active   = :is_active
    WHERE  id = :id
");
$stmt->execute([
    ':id'          => $id,
    ':name'        => $name,
    ':description' => $description ?: null,
    ':tag'         => $tag         ?: null,
    ':image_path'  => $image_path  ?: null,
    ':is_active'   => $is_active   ? 'TRUE' : 'FALSE',
]);

if ($stmt->rowCount() === 0)
    json_response(false, 'Product not found.', [], 404);

json_response(true, 'Product updated successfully.');