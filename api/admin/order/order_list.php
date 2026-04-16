<?php
// ================================================================
//  DAIRICH — Admin: List All Orders
//  api/admin/orders/list.php
//
//  Method  : GET
//  Headers : Authorization: Bearer <token>
//  Params  : ?status=pending  (optional filter)
//            ?distributor_id=2 (optional filter)
// ================================================================

require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../config/helpers.php';
require_once __DIR__ . '/../../../config/admin_auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization');

require_method('GET');
require_admin_auth();

$pdo            = db();
$status         = $_GET['status']         ?? '';
$distributor_id = isset($_GET['distributor_id']) ? (int)$_GET['distributor_id'] : 0;

$where  = [];
$params = [];

if ($status !== '') {
    $where[]            = "o.status = :status";
    $params[':status']  = $status;
}
if ($distributor_id > 0) {
    $where[]                  = "o.distributor_id = :did";
    $params[':did']           = $distributor_id;
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare("
    SELECT
        o.id, o.total_amount, o.status, o.order_date, o.notes,
        d.company_name AS distributor_name, d.email AS distributor_email,
        del.status AS delivery_status, del.expected_date, del.delivered_date
    FROM   orders o
    JOIN   distributors d ON d.id = o.distributor_id
    LEFT   JOIN delivery del ON del.order_id = o.id
    $whereSQL
    ORDER  BY o.order_date DESC
");
$stmt->execute($params);
$orders = $stmt->fetchAll();

json_response(true, 'OK', ['orders' => $orders]);