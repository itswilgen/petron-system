<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../includes/guards/staff_guard.php';
require_once __DIR__ . '/../../../controller/SaleController.php';

header('Content-Type: application/json');

$controller = new SaleController();
$data = $controller->history(1, 10, '', '', '');

echo json_encode([
    'success' => true,
    'rows' => $data['rows'],
    'total' => $data['total']
]);
exit;