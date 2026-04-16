<?php
// ================================================================
//  DAIRICH — Admin: Deactivate Distributor
//  api/admin/distributors/deactivate.php
//
//  Method  : POST
//  Headers : Authorization: Bearer <token>
//  Body    : { "distributor_id": 2 }
//
//  Deactivates a distributor. Kills all their active sessions too.
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

$body           = json_decode(file_get_contents('php://input'), true);
$distributor_id = isset($body['distributor_id']) ? (int)$body['distributor_id'] : 0;

if ($distributor_id < 1)
    json_response(false, 'distributor_id is required.', [], 422);

$pdo = db();

try {
    $pdo->beginTransaction();

    // Deactivate account
    $stmt = $pdo->prepare("UPDATE distributors SET is_active = FALSE WHERE id = :id");
    $stmt->execute([':id' => $distributor_id]);

    if ($stmt->rowCount() === 0) {
        $pdo->rollBack();
        json_response(false, 'Distributor not found.', [], 404);
    }

    // Kill all active sessions
    $pdo->prepare("DELETE FROM distributor_sessions WHERE distributor_id = :id")
        ->execute([':id' => $distributor_id]);

    $pdo->commit();

    json_response(true, 'Distributor account deactivated and sessions terminated.');

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('[Dairich] admin/distributors/deactivate.php: ' . $e->getMessage());
    json_response(false, 'Failed to deactivate. Please try again.', [], 500);
}