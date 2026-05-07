<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../includes/auth_roles.php';
require_once __DIR__ . '/../../../controller/SuperAdminController.php';

header('Content-Type: application/json');

$role = $_SESSION['role'] ?? '';
if (!canAccessSuperAdminArea($role)) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized.'
    ]);
    exit;
}

try {
    $controller = new SuperAdminController();
    $data = $controller->getBusinessHealthData();
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load business health stats.'
    ]);
}

exit;
