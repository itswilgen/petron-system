<?php
require_once __DIR__ . '/../controllers/SaleController.php';
require_once __DIR__ . '/../models/Fuel.php';

$controller = new SaleController();
$controller->processSale();

$fuelModel = new Fuel();
$fuels = $fuelModel->getAllFuel();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petron POS - Terminal</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../src/assets/css/pos.css">
</head>
<body>

<nav class="navbar navbar-dark bg-petron-blue shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <i class="fa-solid fa-gas-pump me-2"></i> PETRON TERMINAL
        </a>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm">Exit to Dashboard</a>
    </div>
</nav>

<div class="container pos-container">
    <form method="POST">
        <div class="row g-4">
            
            <div class="col-lg-7">
                <div class="card shadow-sm p-4 h-100">
                    <h5 class="fw-bold mb-4 text-petron-blue">Dispense Details</h5>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-muted">Fuel Product</label>
                        <select name="fuel_id" id="fuel" class="form-select form-select-lg" required>
                            <option value="">Select Fuel Type</option>
                            <?php while($row = $fuels->fetch(PDO::FETCH_ASSOC)): ?>
                                <option 
                                    value="<?= $row['id'] ?>" 
                                    data-price="<?= $row['price'] ?>"
                                    data-name="<?= $row['fuel_name'] ?>">
                                    <?= $row['fuel_name'] ?> (₱<?= number_format($row['price'], 2) ?>/L)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-muted">Liters</label>
                        <input type="number" step="0.01" name="liters" id="liters" 
                               class="form-control form-control-lg fw-bold" 
                               placeholder="0.00" required>
                    </div>

                    <div class="row g-2 mb-4">
                        <label class="form-label fw-bold small text-uppercase text-muted">Quick Presets</label>
                        <div class="col-4"><button type="button" class="quick-btn w-100" onclick="setLiters(10)">10L</button></div>
                        <div class="col-4"><button type="button" class="quick-btn w-100" onclick="setLiters(20)">20L</button></div>
                        <div class="col-4"><button type="button" class="quick-btn w-100" onclick="setLiters(50)">50L</button></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-lg receipt-card p-4 h-100">
                    <h6 class="fw-bold text-muted border-bottom pb-2 mb-4">ORDER SUMMARY</h6>

                    <div class="py-2">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Product:</span>
                            <span id="sum-name" class="fw-bold">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-4">
                            <span>Quantity:</span>
                            <span id="sum-liters" class="fw-bold">0.00 L</span>
                        </div>
                        
                        <div class="text-center bg-light p-4 rounded-4 shadow-inner">
                            <label class="small text-muted text-uppercase d-block mb-1">Total Amount Due</label>
                            <div class="total-display">₱ <span id="total">0.00</span></div>
                        </div>
                    </div>

                    <button type="submit" name="pay" class="btn btn-petron-red w-100 mt-auto shadow-sm">
                        <i class="fa-solid fa-receipt me-2"></i> COMPLETE TRANSACTION
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
const fuel = document.getElementById("fuel");
const liters = document.getElementById("liters");
const total = document.getElementById("total");
const sumName = document.getElementById("sum-name");
const sumLiters = document.getElementById("sum-liters");

function compute(){
    let selected = fuel.options[fuel.selectedIndex];
    let price = selected?.getAttribute("data-price") || 0;
    let name = selected?.getAttribute("data-name") || "-";
    let l = liters.value || 0;
    
    total.innerText = (price * l).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    sumName.innerText = name;
    sumLiters.innerText = l > 0 ? parseFloat(l).toFixed(2) + " L" : "0.00 L";
}

function setLiters(val) {
    liters.value = val;
    compute();
}

fuel.addEventListener("change", compute);
liters.addEventListener("input", compute);
</script>

</body>
</html>