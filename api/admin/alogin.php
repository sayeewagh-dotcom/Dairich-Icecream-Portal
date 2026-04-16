<?php
// ================================================================
//  DAIRICH — Admin: Login
//  api/admin/login.php
//
//  Method : POST
//  Fields : email, password
//  Returns: Bearer token
// ================================================================

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_method('POST');

$email    = post('email');
$password = post('password');

if (!valid_email($email) || $password === '')
    json_response(false, 'Email and password are required.', [], 422);

$pdo = db();

$stmt = $pdo->prepare("
    SELECT id, name, email, password_hash, role
    FROM   admin_users
    WHERE  email = :email
");
$stmt->execute([':email' => $email]);
$admin = $stmt->fetch();

if (!$admin || !password_verify($password, $admin['password_hash']))
    json_response(false, 'Invalid email or password.', [], 401);

// Generate session token
$token     = bin2hex(random_bytes(32));
$expiresAt = date('Y-m-d H:i:s', strtotime('+8 hours'));

$pdo->prepare("
    INSERT INTO admin_sessions (admin_id, token, expires_at, created_at)
    VALUES (:admin_id, :token, :expires_at, NOW())
")->execute([
    ':admin_id'   => $admin['id'],
    ':token'      => $token,
    ':expires_at' => $expiresAt,
]);

// Update last login
$pdo->prepare("UPDATE admin_users SET created_at = created_at WHERE id = :id")
    ->execute([':id' => $admin['id']]);

json_response(true, 'Login successful.', [
    'token'      => $token,
    'expires_at' => $expiresAt,
    'admin'      => [
        'id'    => $admin['id'],
        'name'  => $admin['name'],
        'email' => $admin['email'],
        'role'  => $admin['role'],
    ]
]);