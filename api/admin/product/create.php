<?php
// ================================================================
//  DAIRICH — Admin: Create Product
//  api/admin/products/create.php
//
//  Method  : POST
//  Headers : Authorization: Bearer <token>
//  Fields  : name, description (opt), tag (opt), image_path (opt)
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
$name        = trim($body['name']        ?? '');
$description = trim($body['description'] ?? '');
$tag         = trim($body['tag']         ?? '');
$image_path  = trim($body['image_path']  ?? '');

if ($name === '')
    json_response(false, 'Product name is required.', [], 422);

try {
    $stmt = db()->prepare("
        INSERT INTO products (name, description, tag, image_path, is_active, created_at)
        VALUES (:name, :description, :tag, :image_path, TRUE, NOW())
        RETURNING id
    ");
    $stmt->execute([
        ':name'        => $name,
        ':description' => $description ?: null,
        ':tag'         => $tag         ?: null,
        ':image_path'  => $image_path  ?: null,
    ]);

    $id = (int) $stmt->fetchColumn();

    json_response(true, 'Product created successfully.', ['product_id' => $id], 201);

} catch (PDOException $e) {
    error_log('[Dairich] admin/products/create.php: ' . $e->getMessage());
    json_response(false, 'Failed to create product.', [], 500);
}