<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../controller/SaleController.php';
require_once __DIR__ . '/../../../includes/auth_roles.php';

header('Content-Type: application/json');

$role = $_SESSION['role'] ?? '';
if (!canAccessStaffArea($role)) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized.'
    ]);
    exit;
}

$controller = new SaleController();

try {
    echo json_encode($controller->processSaleAjax());
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Sale processing failed.'
    ]);
}

exit;
