<?php
require_once __DIR__ . '/../controllers/FuelController.php';

$controller = new FuelController();
$fuels = $controller->index();

// update function
$controller->update();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fuel Inventory | Petron Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../src/assets/css/inventory.css">
</head>
<body class="bg-light-grey">

<nav class="navbar navbar-expand-lg navbar-dark bg-petron-blue shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="../src/assets/img/logo3.png" alt="Logo" width="40" class="me-2 rounded bg-white p-1">
            <span class="fw-bold tracking-tight">PETRON INVENTORY</span>
        </a>
        <div class="ms-auto">
            <a href="dashboard.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>
</nav>

<div class="container pb-5">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h2 class="fw-bold text-petron-blue m-0">Fuel Stock Management</h2>
            <p class="text-muted mb-0">Monitor and update real-time fuel levels</p>
        </div>
        <div class="col-auto">
            <button class="btn btn-petron-red text-white fw-bold shadow-sm" onclick="window.location.reload()">
                <i class="fa-solid fa-rotate me-1"></i> Refresh Data
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm inventory-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-petron-blue text-white">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Fuel Product</th>
                            <th style="width: 150px;">Volume (Liters)</th>
                            <th style="width: 150px;">Unit Price (₱)</th>
                            <th>Status Indicator</th>
                            <th class="text-center pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $fuels->fetch(PDO::FETCH_ASSOC)): 
                            // Logical color selection for status
                            $badgeClass = "bg-success";
                            if($row['status'] == "Low Stock") $badgeClass = "bg-warning text-dark";
                            if($row['status'] == "Out of Stock") $badgeClass = "bg-danger";
                        ?>
                            <tr>
                                <form method="POST">
                                    <td class="ps-4 text-muted fw-bold">#<?= $row['id'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="fuel-icon me-2">
                                                <i class="fa-solid fa-gas-pump text-petron-blue"></i>
                                            </div>
                                            <span class="fw-bold text-dark"><?= $row['fuel_name'] ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="0.01" name="liters" value="<?= $row['liters'] ?>" class="form-control fw-bold border-petron">
                                            <span class="input-group-text bg-white border-petron">L</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white border-petron">₱</span>
                                            <input type="number" step="0.01" name="price" value="<?= $row['price'] ?>" class="form-control fw-bold border-petron">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="badge <?= $badgeClass ?> mb-1" style="width: fit-content;"><?= $row['status'] ?></span>
                                            <select name="status" class="form-select form-select-sm border-0 bg-light-blue shadow-none">
                                                <option <?= $row['status']=="Available"?"selected":"" ?>>Available</option>
                                                <option <?= $row['status']=="Low Stock"?"selected":"" ?>>Low Stock</option>
                                                <option <?= $row['status']=="Out of Stock"?"selected":"" ?>>Out of Stock</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td class="text-center pe-4">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit" name="update" class="btn btn-update btn-sm px-3 shadow-sm">
                                            <i class="fa-solid fa-floppy-disk me-1"></i> Update
                                        </button>
                                    </td>
                                </form>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>
