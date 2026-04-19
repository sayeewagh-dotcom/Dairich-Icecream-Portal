<?php
// ================================================================
//  DAIRICH — Distributor: Register
//  api/distributor/register.php
//
//  Method : POST
//  Fields : enquiry_id (optional), company_name, email,
//           password, confirm_password
//
//  A distributor can self-register OR be linked to an approved
//  enquiry (enquiry_id). Admin will later approve from panel.
// ================================================================

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_method('POST');

$company_name    = post('company_name');
$email           = post('email');
$password        = post('password');
$confirm         = post('confirm_password');
$enquiry_id      = !empty($_POST['enquiry_id']) ? (int)$_POST['enquiry_id'] : null;

// ── Validate ─────────────────────────────────────────────────────
if ($company_name === '')
    json_response(false, 'Company name is required.', [], 422);

if (!valid_email($email))
    json_response(false, 'A valid email is required.', [], 422);

if (strlen($password) < 8)
    json_response(false, 'Password must be at least 8 characters.', [], 422);

if ($password !== $confirm)
    json_response(false, 'Passwords do not match.', [], 422);

$pdo = db();

// Check duplicate email
$chk = $pdo->prepare("SELECT id FROM distributors WHERE email = :email");
$chk->execute([':email' => $email]);
if ($chk->fetch())
    json_response(false, 'An account with this email already exists.', [], 409);

// If enquiry_id provided, verify it exists
if ($enquiry_id) {
    $eq = $pdo->prepare("SELECT id FROM enquiries WHERE id = :id");
    $eq->execute([':id' => $enquiry_id]);
    if (!$eq->fetch())
        json_response(false, 'Invalid enquiry reference.', [], 422);
}

$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

try {
    $stmt = $pdo->prepare("
        INSERT INTO distributors (enquiry_id, company_name, email, password_hash, is_active, created_at)
        VALUES (:enquiry_id, :company_name, :email, :password_hash, FALSE, NOW())
        RETURNING id
    ");
    $stmt->execute([
        ':enquiry_id'    => $enquiry_id,
        ':company_name'  => $company_name,
        ':email'         => $email,
        ':password_hash' => $hash,
    ]);

    $id = (int) $stmt->fetchColumn();

    json_response(true, 'Registration successful. You will be notified once your account is approved.', [
        'distributor_id' => $id
    ], 201);

} catch (PDOException $e) {
    error_log('[Dairich] distributor/register.php: ' . $e->getMessage());
    json_response(false, 'Registration failed. Please try again.', [], 500);
}