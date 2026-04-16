<?php
// ================================================================
//  DAIRICH — Admin: List All Enquiries
//  api/admin/enquiries/list.php
//
//  Method  : GET
//  Headers : Authorization: Bearer <token>
//  Params  : ?status=new  (optional filter)
// ================================================================

require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../config/helpers.php';
require_once __DIR__ . '/../../../config/admin_auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization');

require_method('GET');
require_admin_auth();

$status = $_GET['status'] ?? '';
$allowed = ['new', 'reviewed', 'contacted', 'closed'];

$pdo = db();

if ($status !== '' && in_array($status, $allowed)) {
    $stmt = $pdo->prepare("
        SELECT e.*,
               d.id AS distributor_id
        FROM   enquiries e
        LEFT   JOIN distributors d ON d.enquiry_id = e.id
        WHERE  e.status = :status
        ORDER  BY e.submitted_at DESC
    ");
    $stmt->execute([':status' => $status]);
} else {
    $stmt = $pdo->query("
        SELECT e.*,
               d.id AS distributor_id
        FROM   enquiries e
        LEFT   JOIN distributors d ON d.enquiry_id = e.id
        ORDER  BY e.submitted_at DESC
    ");
}

$enquiries = $stmt->fetchAll();

// Attach interested flavours for each enquiry
foreach ($enquiries as &$enq) {
    $fl = $pdo->prepare("
        SELECT p.id, p.name
        FROM   enquiry_flavours ef
        JOIN   products p ON p.id = ef.product_id
        WHERE  ef.enquiry_id = :eid
    ");
    $fl->execute([':eid' => $enq['id']]);
    $enq['flavours'] = $fl->fetchAll();
}

json_response(true, 'OK', ['enquiries' => $enquiries]);