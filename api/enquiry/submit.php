<?php
// ================================================================
//  DAIRICH — Enquiry: Submit
//  api/enquiry/submit.php
//
//  Method : POST
//  Fields : company_name, contact_person, email, phone, message, flavours
//  Public endpoint — no auth required
// ================================================================

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/helpers.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_method('POST');

$company_name   = post('company_name');
$contact_person = post('contact_person');
$email          = post('email');
$phone          = post('phone');
$message        = post('message');
$flavours       = post('flavours');

// ── Validate ──
if ($company_name === '')
    json_response(false, 'Company name is required.', [], 422);

if ($contact_person === '')
    json_response(false, 'Contact person is required.', [], 422);

if (!valid_email($email))
    json_response(false, 'A valid email is required.', [], 422);

$pdo = db();

try {
    $stmt = $pdo->prepare("
        INSERT INTO enquiries (company_name, contact_person, email, phone, message, status, submitted_at)
        VALUES (:company_name, :contact_person, :email, :phone, :message, 'new', NOW())
        RETURNING id
    ");
    $stmt->execute([
        ':company_name'   => $company_name,
        ':contact_person' => $contact_person,
        ':email'          => $email,
        ':phone'          => $phone   ?: null,
        ':message'        => $message ?: null,
    ]);

    $enquiry_id = (int) $stmt->fetchColumn();

    json_response(true, 'Thank you for your enquiry! We will be in touch soon.', [
        'enquiry_id' => $enquiry_id
    ], 201);

} catch (PDOException $e) {
    error_log('[Dairich] enquiry/submit.php: ' . $e->getMessage());
    json_response(false, 'Failed to submit enquiry. Please try again.', [], 500);
}