<?php
// ================================================================
//  DAIRICH — Admin Auth Middleware
//  config/admin_auth.php
//
//  Include at the top of every protected admin endpoint.
//  Returns the authenticated admin row.
// ================================================================

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function require_admin_auth(): array {
    $header = $_SERVER['HTTP_AUTHORIZATION'] 
       ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] 
       ?? (getallheaders()['Authorization'] ?? '')
       ?? (getallheaders()['authorization'] ?? '')
       ?? '';
    $token  = null;

    if (preg_match('/Bearer\s+(.+)/i', $header, $m))
        $token = trim($m[1]);

    if (!$token)
        json_response(false, 'Unauthorised. Admin login required.', [], 401);

    $stmt = db()->prepare("
        SELECT a.id, a.name, a.email, a.role
        FROM   admin_sessions s
        JOIN   admin_users a ON a.id = s.admin_id
        WHERE  s.token = :token
          AND  s.expires_at > NOW()
    ");
    $stmt->execute([':token' => $token]);
    $admin = $stmt->fetch();

    if (!$admin)
        json_response(false, 'Session expired or invalid. Please log in again.', [], 401);

    return $admin;
}

// Only superadmin can perform certain actions
function require_superadmin(array $admin): void {
    if ($admin['role'] !== 'superadmin')
        json_response(false, 'Access denied. Superadmin only.', [], 403);
}