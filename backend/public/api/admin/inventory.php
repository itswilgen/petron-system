<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . '/../../../model/Fuel.php';

$branch_id = isset($_GET['branch_id']) ? (int)$_GET['branch_id'] : 0;

if ($branch_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid branch ID"
    ]);
    exit;
}

$fuelModel = new Fuel();
$stmt = $fuelModel->getAllFuel($branch_id);

$fuels = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $fuels[] = [
        "id" => (int)$row["id"],
        "fuel_name" => $row["fuel_name"],
        "liters" => (float)$row["liters"],
        "capacity" => (float)$row["capacity"],
        "price" => (float)$row["price"],
        "status" => $row["status"]
    ];
}

echo json_encode([
    "success" => true,
    "fuels" => $fuels
]);