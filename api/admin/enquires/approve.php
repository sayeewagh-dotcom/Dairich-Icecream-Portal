<?php
// ================================================================
//  DAIRICH — Admin: Approve Enquiry → Create Distributor
//  api/admin/enquiries/approve.php
//
//  Method  : POST
//  Headers : Authorization: Bearer <token>
//  Body    : { "enquiry_id": 3, "password": "TempPass@123" }
//
//  This is the KEY link between Phase 1 and Phase 2.
//  Admin reviews an enquiry and converts it into a distributor account.
//  Distributor gets an account but must log in and change their password.
// ================================================================

require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../config/helpers.php';
require_once __DIR__ . '/../../../config/admin_auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_method('POST');
require_admin_auth();

$body       = json_decode(file_get_contents('php://input'), true);
$enquiry_id = isset($body['enquiry_id']) ? (int)$body['enquiry_id'] : 0;
$password   = trim($body['password'] ?? '');

if ($enquiry_id < 1)
    json_response(false, 'enquiry_id is required.', [], 422);

if (strlen($password) < 8)
    json_response(false, 'Temporary password must be at least 8 characters.', [], 422);

$pdo = db();

// Fetch the enquiry
$enq = $pdo->prepare("SELECT * FROM enquiries WHERE id = :id");
$enq->execute([':id' => $enquiry_id]);
$enquiry = $enq->fetch();

if (!$enquiry)
    json_response(false, 'Enquiry not found.', [], 404);

// Check if distributor already exists for this enquiry
$dup = $pdo->prepare("SELECT id FROM distributors WHERE enquiry_id = :eid");
$dup->execute([':eid' => $enquiry_id]);
if ($dup->fetch())
    json_response(false, 'A distributor account already exists for this enquiry.', [], 409);

// Check email not already taken
$emailChk = $pdo->prepare("SELECT id FROM distributors WHERE email = :email");
$emailChk->execute([':email' => $enquiry['email']]);
if ($emailChk->fetch())
    json_response(false, 'A distributor with this email already exists.', [], 409);

$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

try {
    $pdo->beginTransaction();

    // Create distributor account (active = TRUE since admin is approving)
    $stmt = $pdo->prepare("
        INSERT INTO distributors (enquiry_id, company_name, email, password_hash, is_active, created_at)
        VALUES (:enquiry_id, :company_name, :email, :password_hash, TRUE, NOW())
        RETURNING id
    ");
    $stmt->execute([
        ':enquiry_id'    => $enquiry_id,
        ':company_name'  => $enquiry['company_name'],
        ':email'         => $enquiry['email'],
        ':password_hash' => $hash,
    ]);
    $distributor_id = (int) $stmt->fetchColumn();

    // Update enquiry status to closed
    $pdo->prepare("UPDATE enquiries SET status = 'closed' WHERE id = :id")
        ->execute([':id' => $enquiry_id]);

    $pdo->commit();

    json_response(true, 'Enquiry approved. Distributor account created successfully.', [
        'distributor_id'   => $distributor_id,
        'company_name'     => $enquiry['company_name'],
        'email'            => $enquiry['email'],
        'temp_password'    => $password,  // send this to distributor via email
    ], 201);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('[Dairich] admin/enquiries/approve.php: ' . $e->getMessage());
    json_response(false, 'Failed to approve enquiry. Please try again.', [], 500);
}