<?php
// ================================================================
//  DAIRICH — Distributor: Login
//  api/distributor/login.php
//
//  Method : POST
//  Fields : email, password
//  Returns: session token stored in distributor_sessions table
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
    SELECT id, company_name, password_hash, is_active
    FROM   distributors
    WHERE  email = :email
");
$stmt->execute([':email' => $email]);
$distributor = $stmt->fetch();

if (!$distributor || !password_verify($password, $distributor['password_hash']))
    json_response(false, 'Invalid email or password.', [], 401);

if (!$distributor['is_active'])
    json_response(false, 'Your account is pending approval. Please wait for admin confirmation.', [], 403);

// Generate a secure session token
$token     = bin2hex(random_bytes(32));
$expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

// Store session in DB
$pdo->prepare("
    INSERT INTO distributor_sessions (distributor_id, token, expires_at, created_at)
    VALUES (:distributor_id, :token, :expires_at, NOW())
")->execute([
    ':distributor_id' => $distributor['id'],
    ':token'          => $token,
    ':expires_at'     => $expiresAt,
]);

json_response(true, 'Login successful.', [
    'token'        => $token,
    'expires_at'   => $expiresAt,
    'distributor'  => [
        'id'           => $distributor['id'],
        'company_name' => $distributor['company_name'],
    ]
]);