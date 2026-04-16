<?php
// ================================================================
//  DAIRICH — Admin: Logout
//  api/admin/logout.php
//
//  Method  : POST
//  Headers : Authorization: Bearer <token>
// ================================================================

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_method('POST');

$header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token  = null;
if (preg_match('/Bearer\s+(.+)/i', $header, $m)) $token = trim($m[1]);

if (!$token)
    json_response(false, 'No token provided.', [], 401);

db()->prepare("DELETE FROM admin_sessions WHERE token = :token")
    ->execute([':token' => $token]);

json_response(true, 'Logged out successfully.');