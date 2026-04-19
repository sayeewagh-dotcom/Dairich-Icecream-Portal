<?php
// ================================================================
//  DAIRICH — Distributor Auth Middleware
//  config/auth.php
//
//  Include this at the top of any protected distributor endpoint.
//  Sets $GLOBALS['auth_distributor'] with the authenticated row.
// ================================================================

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function require_distributor_auth(): array {
    $header = $_SERVER['HTTP_AUTHORIZATION'] 
       ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] 
       ?? (getallheaders()['Authorization'] ?? '')
       ?? (getallheaders()['authorization'] ?? '')
       ?? '';
    $token  = null;

    if (preg_match('/Bearer\s+(.+)/i', $header, $m))
        $token = trim($m[1]);

    if (!$token)
        json_response(false, 'Unauthorised. Please log in.', [], 401);

    $stmt = db()->prepare("
        SELECT d.id, d.company_name, d.email, d.is_active
        FROM   distributor_sessions s
        JOIN   distributors d ON d.id = s.distributor_id
        WHERE  s.token = :token
          AND  s.expires_at > NOW()
    ");
    $stmt->execute([':token' => $token]);
    $distributor = $stmt->fetch();

    if (!$distributor)
        json_response(false, 'Session expired or invalid. Please log in again.', [], 401);

    if (!$distributor['is_active'])
        json_response(false, 'Your account has been deactivated.', [], 403);

    return $distributor;
}


