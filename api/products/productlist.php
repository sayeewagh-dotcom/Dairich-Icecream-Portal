<?php
// ================================================================
//  DAIRICH ICE CREAM — Get Active Products
//  api/products/list.php
//
//  Method : GET
//  Returns: Array of active products for the frontend carousel
// ================================================================

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_method('GET');

try {
    $stmt = db()->query("
        SELECT id, name, description, tag, image_path
        FROM   products
        WHERE  is_active = TRUE
        ORDER  BY id ASC
    ");

    $products = $stmt->fetchAll();

    json_response(true, 'OK', ['products' => $products]);

} catch (PDOException $e) {
    error_log('[Dairich] products/list.php PDO error: ' . $e->getMessage());
    json_response(false, 'Could not load products.', [], 500);
}