<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: auth/login.php");
    exit();
}

require_once __DIR__ . '/../controllers/ReportController.php';

$controller = new ReportController();
$data = $controller->index();

$dailyRows = $data['dailyRows'];
$dailyTotal = $data['dailyTotal'];
$monthlyRows = $data['monthlyRows'];
$monthlyTotal = $data['monthlyTotal'];
$inventoryRows = $data['inventoryRows'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petron Analytics - Sales Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../src/assets/css/reports.css">
</head>
<body class="bg-light">

<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-center mb-5 print-hide">
        <div>
            <h2 class="fw-bold text-petron-blue m-0">📊 Sales Analytics</h2>
            <p class="text-muted">Inventory & Revenue Overview</p>
        </div>
        <div class="btn-group shadow-sm">
            <a href="dashboard.php" class="btn btn-outline-secondary px-4"><i class="fa-solid fa-arrow-left me-2"></i>Dashboard</a>
            <button onclick="window.print()" class="btn btn-petron-red px-4 text-white"><i class="fa-solid fa-print me-2"></i>Print Report</button>
        </div>
    </div>

    <div class="row g-4 mb-5 print-hide">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold mb-4 text-petron-blue">Monthly Sales Performance</h5>
                <canvas id="monthlyTrendChart" height="120"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold mb-4 text-petron-blue">Daily Fuel Mix</h5>
                <canvas id="dailyFuelChart"></canvas>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3"><h5 class="m-0 fw-bold">Daily Breakdown</h5></div>
                <div class="card-body">
                    <table class="table align-middle">
                        <thead class="table-light small text-uppercase">
                            <tr><th>Fuel</th><th>Liters</th><th class="text-end">Total</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($dailyRows as $row): ?>
                            <tr>
                                <td class="fw-bold text-petron-blue"><?= $row['fuel_name'] ?></td>
                                <td><?= number_format($row['liters_sold'], 2) ?> L</td>
                                <td class="text-end fw-bold">₱ <?= number_format($row['total_sales'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="border-top-2">
                            <tr class="table-light"><th colspan="2">Daily Revenue</th><th class="text-end text-petron-red fs-5">₱ <?= number_format($dailyTotal, 2) ?></th></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3"><h5 class="m-0 fw-bold">Current Inventory</h5></div>
                <div class="card-body">
                    <table class="table align-middle">
                        <thead class="table-light small text-uppercase">
                            <tr><th>Product</th><th>Stock</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($inventoryRows as $row): ?>
                            <tr>
                                <td><?= $row['fuel_name'] ?></td>
                                <td class="fw-bold"><?= number_format($row['liters'], 2) ?> L</td>
                                <td>
                                    <span class="badge rounded-pill <?= $row['status'] == 'Low Stock' ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?> px-3">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Prepare Data from PHP to JS
    const dailyLabels = [<?php foreach($dailyRows as $r) echo '"'.$r['fuel_name'].'",'; ?>];
    const dailySales = [<?php foreach($dailyRows as $r) echo $r['total_sales'].','; ?>];
    
    const monthlyLabels = [<?php foreach($monthlyRows as $r) echo '"'.date("M d", strtotime($r['sale_day'])).'",'; ?>];
    const monthlySales = [<?php foreach($monthlyRows as $r) echo $r['total_sales'].','; ?>];

    // 1. DAILY PIE CHART
    new Chart(document.getElementById('dailyFuelChart'), {
        type: 'doughnut',
        data: {
            labels: dailyLabels,
            datasets: [{
                data: dailySales,
                backgroundColor: ['#004289', '#ed1c24', '#ffc107', '#198754'],
                hoverOffset: 10
            }]
        },
        options: { cutout: '65%', plugins: { legend: { position: 'bottom' } } }
    });

    // 2. MONTHLY LINE CHART
    new Chart(document.getElementById('monthlyTrendChart'), {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Revenue (₱)',
                data: monthlySales,
                borderColor: '#004289',
                backgroundColor: 'rgba(0, 66, 137, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#ed1c24'
            }]
        },
        options: {
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { display: false } }
        }
    });
</script>
</body>
</html>