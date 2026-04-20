<?php
require_once __DIR__ . '/../../../controller/SuperAdminController.php';

$controller = new SuperAdminController();
$data = $controller->dashboard();
?>

<header class="bg-white py-4 px-6 shadow-sm flex justify-between items-center">
    <h1 class="text-xl font-extrabold text-petron-blue">WELCOME, SUPER ADMIN</h1>
    <span class="text-sm text-gray-500"><?= date('F d, Y') ?></span>
</header>

<div class="p-6">

    <!-- KPI CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">

        <!-- Sales -->
        <div class="bg-green-500 text-white p-5 rounded-xl shadow">
            <p class="text-sm font-bold">TOTAL REGIONAL SALE TODAY</p>
            <h2 class="text-2xl font-black">
                ₱ <?= number_format($data['salesToday'], 2) ?>
            </h2>
        </div>

        <!-- Liters -->
        <div class="bg-blue-500 text-white p-5 rounded-xl shadow">
            <p class="text-sm font-bold">REGIONAL LITERS SOLD TODAY</p>
            <h2 class="text-2xl font-black">
                <?= number_format($data['litersToday'], 2) ?> L
            </h2>
        </div>

        <!-- Fuel Count -->
        <div class="bg-orange-500 text-white p-5 rounded-xl shadow">
            <p class="text-sm font-bold">FUEL TYPE</p>
            <h2 class="text-2xl font-black">
                <?= $data['fuelCount'] ?>
            </h2>
        </div>

        <!-- Low Stock -->
        <div class="bg-red-500 text-white p-5 rounded-xl shadow">
            <p class="text-sm font-bold">LOW STOCK WARNING</p>
            <h2 class="text-2xl font-black">
                <?= count($data['lowStock']) ?>
            </h2>
        </div>

    </div>

    <!-- STOCK ALERTS -->
    <div class="bg-white rounded-xl shadow p-5 border">
        <h2 class="font-bold text-gray-700 mb-4">⚠ Stock Alerts</h2>

        <table class="w-full text-left">
            <thead class="text-xs text-gray-500 uppercase">
                <tr>
                    <th>Fuel</th>
                    <th>Remaining</th>
                    <th>Status</th>
                    <th>Branch</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($data['lowStock'] as $row): ?>
                    <tr class="border-t">
                        <td><?= $row['fuel_name'] ?></td>
                        <td><?= number_format($row['liters'],2) ?> L</td>
                        <td class="text-red-600 font-bold">CRITICAL</td>
                        <td><?= $row['branch_name'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>