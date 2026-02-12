<?php
// ... [Your PHP Logic stays exactly at the top] ...
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: auth/login.php");
    exit();
}
require_once __DIR__ . '/../Database/Database.php';
$db = new Database();
$conn = $db->getConnection();

// [Stat Queries stay here]
$stmt = $conn->prepare("SELECT SUM(total_price) as total_sales FROM sales WHERE DATE(sale_date)=CURDATE()");
$stmt->execute();
$salesToday = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales'] ?? 0;

$stmt = $conn->prepare("SELECT SUM(liters) as total_liters FROM sales WHERE DATE(sale_date)=CURDATE()");
$stmt->execute();
$litersToday = $stmt->fetch(PDO::FETCH_ASSOC)['total_liters'] ?? 0;

$stmt = $conn->prepare("SELECT COUNT(*) as total_fuels FROM fuels");
$stmt->execute();
$totalFuels = $stmt->fetch(PDO::FETCH_ASSOC)['total_fuels'];

$stmt = $conn->prepare("SELECT fuel_name, liters FROM fuels WHERE liters < 500");
$stmt->execute();
$lowStock = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Petron Command Center</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../src/assets/css/dashboard.css">
</head>
<body>

<div class="d-flex" id="wrapper">
    <div class="bg-petron-blue border-end" id="sidebar-wrapper">
        <div class="sidebar-heading text-center py-4 text-white">
            <img src="../src/assets/img/logo3.png" alt="Logo" class="img-fluid mb-2 px-4" style="max-height: 80px;">
            <div class="small fw-light">Management System</div>
        </div>
        <div class="list-group list-group-flush px-3">
            <a href="dashboard.php" class="list-group-item list-group-item-action bg-transparent text-white active-link">
                <i class="fa-solid fa-gauge-high me-2"></i> Dashboard
            </a>
            <a href="pos.php" class="list-group-item list-group-item-action bg-transparent text-white border-0 py-3">
                <i class="fa-solid fa-gas-pump me-2"></i> POS (Point of Sale)
            </a>
            <a href="inventory_list.php" class="list-group-item list-group-item-action bg-transparent text-white border-0 py-3">
                <i class="fa-solid fa-boxes-stacked me-2"></i> Inventory
            </a>
            <a href="reports.php" class="list-group-item list-group-item-action bg-transparent text-white border-0 py-3">
                <i class="fa-solid fa-chart-line me-2"></i> Sales Reports
            </a>
            <hr class="text-white opacity-25">
            <a href="auth/logout.php" class="list-group-item list-group-item-action bg-transparent text-danger border-0 py-3 fw-bold">
                <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
            </a>
        </div>
    </div>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-white py-3 px-4 shadow-sm">
            <div class="d-flex align-items-center">
                <h4 class="m-0 fw-bold text-petron-blue">Welcome, Admin</h4>
            </div>
            <div class="ms-auto text-muted small">
                <i class="fa-solid fa-calendar-day me-1"></i> <?= date('F d, Y') ?>
            </div>
        </nav>

        <div class="container-fluid px-4 py-5">
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="card stat-card sales-card border-0 shadow-sm p-4 h-100">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-white-50 text-uppercase small">Total Sales Today</h6>
                                <h2 class="text-white fw-bold">₱ <?= number_format($salesToday,2) ?></h2>
                            </div>
                            <div class="stat-icon"><i class="fa-solid fa-coins"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card liters-card border-0 shadow-sm p-4 h-100">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-white-50 text-uppercase small">Liters Sold Today</h6>
                                <h2 class="text-white fw-bold"><?= number_format($litersToday,2) ?> <small>L</small></h2>
                            </div>
                            <div class="stat-icon"><i class="fa-solid fa-droplet"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card fuels-card border-0 shadow-sm p-4 h-100">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-white-50 text-uppercase small">Fuel Types</h6>
                                <h2 class="text-white fw-bold"><?= $totalFuels ?></h2>
                            </div>
                            <div class="stat-icon"><i class="fa-solid fa-oil-can"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card alert-card border-0 shadow-sm p-4 h-100">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-white-50 text-uppercase small">Low Stock Warnings</h6>
                                <h2 class="text-white fw-bold"><?= count($lowStock) ?></h2>
                            </div>
                            <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center">
                            <h5 class="fw-bold m-0 text-dark"><i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>Stock Alerts</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-muted small text-uppercase">
                                        <tr>
                                            <th class="ps-4">Fuel Product</th>
                                            <th>Remaining Liters</th>
                                            <th>Critical Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(count($lowStock) > 0): ?>
                                            <?php foreach($lowStock as $stock): ?>
                                            <tr>
                                                <td class="ps-4 fw-bold"><?= $stock['fuel_name'] ?></td>
                                                <td class="text-danger fw-bold"><?= number_format($stock['liters'],2) ?> L</td>
                                                <td><span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill">Urgent Restock</span></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center py-4 text-muted small italic">All stock levels are optimal.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>