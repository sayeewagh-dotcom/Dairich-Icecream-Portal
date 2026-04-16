<?php
// ================================================================
//  DAIRICH — Admin: Dashboard Stats
//  api/admin/dashboard.php
//
//  Method  : GET
//  Headers : Authorization: Bearer <token>
//  Returns : Summary counts for the admin dashboard.
// ================================================================

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/admin_auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization');

require_method('GET');
require_admin_auth();

$pdo = db();

$stats = [];

// Total enquiries
$stats['total_enquiries']     = (int) $pdo->query("SELECT COUNT(*) FROM enquiries")->fetchColumn();
$stats['new_enquiries']       = (int) $pdo->query("SELECT COUNT(*) FROM enquiries WHERE status = 'new'")->fetchColumn();

// Distributors
$stats['total_distributors']  = (int) $pdo->query("SELECT COUNT(*) FROM distributors")->fetchColumn();
$stats['pending_distributors']= (int) $pdo->query("SELECT COUNT(*) FROM distributors WHERE is_active = FALSE")->fetchColumn();

// Orders
$stats['total_orders']        = (int) $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$stats['pending_orders']      = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();

// Revenue
$stats['total_revenue']       = (float) $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status != 'cancelled'")->fetchColumn();

// Deliveries in transit
$stats['active_deliveries']   = (int) $pdo->query("SELECT COUNT(*) FROM delivery WHERE status IN ('in_transit','out_for_delivery')")->fetchColumn();

// Products
$stats['total_products']      = (int) $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = TRUE")->fetchColumn();

json_response(true, 'OK', ['stats' => $stats]);