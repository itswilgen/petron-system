<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . '/../../../database/Database.php';

$branch_id = isset($_GET['branch_id']) ? (int)$_GET['branch_id'] : 0;

if ($branch_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid branch ID"
    ]);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Total sales today
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(total_price), 0) AS total_sales
    FROM sales
    WHERE branch_id = ?
    AND DATE(sale_date) = CURDATE()
");
$stmt->execute([$branch_id]);
$salesToday = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total_sales'];

// Liters sold today
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(liters), 0) AS total_liters
    FROM sales
    WHERE branch_id = ?
    AND DATE(sale_date) = CURDATE()
");
$stmt->execute([$branch_id]);
$litersToday = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total_liters'];

// Fuel count
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total_fuels
    FROM fuels
    WHERE branch_id = ?
");
$stmt->execute([$branch_id]);
$totalFuels = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total_fuels'];

// Low stock
$stmt = $conn->prepare("
    SELECT id, fuel_name, liters, capacity, price, status
    FROM fuels
    WHERE branch_id = ?
    AND capacity > 0
    AND (liters / capacity) <= 0.30
    ORDER BY (liters / capacity) ASC
");
$stmt->execute([$branch_id]);
$lowStock = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "data" => [
        "salesToday" => $salesToday,
        "litersToday" => $litersToday,
        "totalFuels" => $totalFuels,
        "lowStockCount" => count($lowStock),
        "lowStock" => $lowStock
    ]
]);