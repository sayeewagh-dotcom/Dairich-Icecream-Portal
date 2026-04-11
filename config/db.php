<?php
// ================================================================
//  DAIRICH ICE CREAM — Shared Helpers
//  config/helpers.php
// ================================================================
 
/**
 * Send a JSON response and exit.
 */
function json_response(bool $success, string $message, array $data = [], int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}
 
/**
 * Allow only specific HTTP methods; send 405 otherwise.
 */
function require_method(string ...$methods): void {
    if (!in_array($_SERVER['REQUEST_METHOD'], $methods, true)) {
        json_response(false, 'Method not allowed.', [], 405);
    }
}
 
/**
 * Sanitise a plain string: trim + strip tags.
 */
function clean(string $value): string {
    return trim(strip_tags($value));
}
 
/**
 * Return a field from $_POST, cleaned. Returns '' if missing.
 */
function post(string $key): string {
    return clean($_POST[$key] ?? '');
}
 
/**
 * Validate an email address.
 */
function valid_email(string $email): bool {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}
 
/**
 * Get the real client IP (basic; adjust if behind proxy).
 */
function client_ip(): string {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}