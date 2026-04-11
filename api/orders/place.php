<?php
// ================================================================
//  DAIRICH — Distributor: Get My Profile
//  api/distributor/profile.php
//
//  Method  : GET
//  Headers : Authorization: Bearer <token>
// ================================================================

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization');

require_method('GET');

$distributor = require_distributor_auth();

$stmt = db()->prepare("
    SELECT
        d.id,
        d.company_name,
        d.email,
        d.is_active,
        d.created_at,
        e.contact_person,
        e.phone,
        e.business_type
    FROM  distributors d
    LEFT  JOIN enquiries e ON e.id = d.enquiry_id
    WHERE d.id = :id
");
$stmt->execute([':id' => $distributor['id']]);
$profile = $stmt->fetch();

json_response(true, 'OK', ['profile' => $profile]);