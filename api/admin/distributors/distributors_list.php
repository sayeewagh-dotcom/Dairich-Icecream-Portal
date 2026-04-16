<?php
// ================================================================
//  DAIRICH — Admin: List All Distributors
//  api/admin/distributors/list.php
//
//  Method  : GET
//  Headers : Authorization: Bearer <token>
//  Params  : ?active=true OR ?active=false  (optional filter)
// ================================================================

require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../config/helpers.php';
require_once __DIR__ . '/../../../config/admin_auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization');

require_method('GET');
require_admin_auth();

$pdo    = db();
$filter = $_GET['active'] ?? '';

if ($filter === 'true') {
    $stmt = $pdo->query("
        SELECT d.*, e.contact_person, e.phone, e.business_type,
               COUNT(o.id) AS total_orders
        FROM   distributors d
        LEFT   JOIN enquiries e ON e.id = d.enquiry_id
        LEFT   JOIN orders o    ON o.distributor_id = d.id
        WHERE  d.is_active = TRUE
        GROUP  BY d.id, e.contact_person, e.phone, e.business_type
        ORDER  BY d.created_at DESC
    ");
} elseif ($filter === 'false') {
    $stmt = $pdo->query("
        SELECT d.*, e.contact_person, e.phone, e.business_type,
               COUNT(o.id) AS total_orders
        FROM   distributors d
        LEFT   JOIN enquiries e ON e.id = d.enquiry_id
        LEFT   JOIN orders o    ON o.distributor_id = d.id
        WHERE  d.is_active = FALSE
        GROUP  BY d.id, e.contact_person, e.phone, e.business_type
        ORDER  BY d.created_at DESC
    ");
} else {
    $stmt = $pdo->query("
        SELECT d.*, e.contact_person, e.phone, e.business_type,
               COUNT(o.id) AS total_orders
        FROM   distributors d
        LEFT   JOIN enquiries e ON e.id = d.enquiry_id
        LEFT   JOIN orders o    ON o.distributor_id = d.id
        GROUP  BY d.id, e.contact_person, e.phone, e.business_type
        ORDER  BY d.created_at DESC
    ");
}

$distributors = $stmt->fetchAll();

json_response(true, 'OK', ['distributors' => $distributors]);