<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../model/Fuel.php';
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

$branchId = (int)($_SESSION['branch_id'] ?? 0);

try {
    $fuelModel = new Fuel();
    $stmt = $fuelModel->getAllFuel($branchId);

    $fuels = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $fuels[] = [
            'id' => (int)$row['id'],
            'fuel_name' => $row['fuel_name'],
            'price' => (float)$row['price'],
            'liters' => (float)$row['liters']
        ];
    }

    echo json_encode([
        'success' => true,
        'fuels' => $fuels
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load fuels.'
    ]);
}

exit;
