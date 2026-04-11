<?php
// ================================================================
//  DAIRICH — Distributor: Logout
//  api/distributor/logout.php
//
//  Method  : POST
//  Headers : Authorization: Bearer <token>
//  Deletes the session token from DB.
// ================================================================

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_method('POST');

$token = get_bearer_token();

if (!$token)
    json_response(false, 'No session token provided.', [], 401);

db()->prepare("DELETE FROM distributor_sessions WHERE token = :token")
    ->execute([':token' => $token]);

json_response(true, 'Logged out successfully.');

// ── Helper ───────────────────────────────────────────────────────
function get_bearer_token(): ?string {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer\s+(.+)/i', $header, $m)) return $m[1];
    return null;
}