<?php
require_once __DIR__ . '/../../../includes/guards/staff_guard.php';
require_once __DIR__ . '/../../../controller/ReportController.php';

$controller = new ReportController();
$data = $controller->index();

$dailyRows     = $data['dailyRows'];
$dailyTotal    = $data['dailyTotal'];
$monthlyRows   = $data['monthlyRows'];
$monthlyTotal  = $data['monthlyTotal'];
$inventoryRows = $data['inventoryRows'];
?>

<!-- Header -->
<header class="bg-white py-4 px-4 md:px-8 shadow-sm flex justify-between items-center">
    <div>
        <h4 class="text-xl font-extrabold text-petron-blue uppercase tracking-tight">REPORTS</h4>
        <p class="text-sm text-gray-500 font-medium">Sales & Inventory Analytics</p>
    </div>

    <div class="hidden md:flex items-center gap-3 text-gray-500 font-semibold bg-gray-50 px-4 py-2 rounded-full border border-gray-100 shadow-inner">
        <i class="fa-solid fa-calendar-day text-petron-red"></i>
        <span id="live-date" class="text-sm"></span>
    </div>
</header>
    <div class="p-4 md:p-8 space-y-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg p-6">
                <h5 class="font-bold text-petron-blue mb-4">Monthly Sales Performance</h5>
                <canvas id="monthlyTrendChart"></canvas>
            </div>
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h5 class="font-bold text-petron-blue mb-4">Daily Fuel Mix</h5>
                    <canvas id="dailyFuelChart"></canvas>
                </div>
             </div>

             <!-- Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b font-bold text-gray-700">
                Daily Breakdown
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-xs uppercase font-bold text-gray-500">
                        <tr>
                            <th class="px-6 py-4">Fuel</th>
                            <th class="px-6 py-4">Liters</th>
                            <th class="px-6 py-4 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach($dailyRows as $row): ?>
                        <tr>
                            <td class="px-6 py-4 font-bold text-petron-blue">
                                <?= htmlspecialchars($row['fuel_name']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <?= number_format($row['liters_sold'], 2) ?> L
                            </td>
                            <td class="px-6 py-4 text-right font-bold">
                                ₱ <?= number_format($row['total_sales'], 2) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <th colspan="2" class="px-6 py-4 text-left">
                                Daily Revenue
                            </th>
                            <th class="px-6 py-4 text-right text-petron-red text-lg">
                                ₱ <?= number_format($dailyTotal, 2) ?>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Inventory Overview -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b font-bold text-gray-700">
                Current Inventory
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-xs uppercase font-bold text-gray-500">
                        <tr>
                            <th class="px-6 py-4">Product</th>
                            <th class="px-6 py-4">Stock</th>
                            <th class="px-6 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach($inventoryRows as $row): ?>
                        <?php
                        $status = $row['status'];
                        $badgeClass = "bg-emerald-100 text-emerald-700";

                        if($status === "Low Stock"){
                            $badgeClass = "bg-amber-100 text-amber-700";
                        }

                        if($status === "Out of Stock"){
                            $badgeClass = "bg-red-100 text-red-700";
                        }
                        ?>
                        <tr>
                            <td class="px-6 py-4 font-semibold">
                                <?= htmlspecialchars($row['fuel_name']) ?>
                            </td>
                            <td class="px-6 py-4 font-bold">
                                <?= number_format($row['liters'], 2) ?> L
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?= $badgeClass ?>">
                                    <?= htmlspecialchars($status) ?>
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


<!-- Charts Script -->
<?php
$dailyLabels = array_map(fn($r) => $r['fuel_name'], $dailyRows);
$dailySales = array_map(fn($r) => (float)$r['total_sales'], $dailyRows);

$monthlyLabels = array_map(fn($r) => date("M d", strtotime($r['sale_day'])), $monthlyRows);
$monthlySales = array_map(fn($r) => (float)$r['total_sales'], $monthlyRows);
?>

<script>
window.reportsData = {
    dailyLabels: <?= json_encode($dailyLabels) ?>,
    dailySales: <?= json_encode($dailySales) ?>,
    monthlyLabels: <?= json_encode($monthlyLabels) ?>,
    monthlySales: <?= json_encode($monthlySales) ?>
};
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/petron_system/public/assets/js/reports_staff.js"></script>
