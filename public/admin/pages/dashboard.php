<?php

require_once __DIR__ . '/../../../controller/DashboardController.php';

$controller = new DashboardController();
$stats = $controller->getStats();

$salesToday  = $stats['salesToday'];
$litersToday = $stats['litersToday'];
$totalFuels  = $stats['totalFuels'];
$lowStock    = $stats['lowStock'];
$fuelLevels = $stats['fuelLevels'];
$salesTrend = $stats['salesTrend'];

?>

<header class="bg-white/80 backdrop-blur-md py-4 px-4 md:px-8 shadow-sm flex justify-between items-center sticky top-0 z-20 border-b border-gray-100">
    <div class="flex items-center gap-3">
        <div class="relative">
            <h4 class="text-xl font-extrabold text-petron-blue uppercase tracking-tight">Welcome, Admin
                <?= htmlspecialchars($_SESSION['branch_name']) ?></h4>
                <span class="absolute -right-4 top-1 flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
            </span>
        </div>
    </div>
    <div class="hidden md:flex items-center gap-3 text-gray-500 font-semibold bg-gray-50 px-4 py-2 rounded-full border border-gray-100 shadow-inner">
        <i class="fa-solid fa-calendar-day text-petron-red"></i>
        <span id="live-date" class="text-sm"></span>
    </div>
</header>

<div class="p-4 md:p-8">

    <!-- ✅ Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">

        <div class="bg-linear-to-br from-[#1d976c] to-[#93f9b9] p-6 rounded-2xl shadow-lg transform hover:-translate-y-2 transition-all duration-300 relative overflow-hidden group">
            <div class="relative z-10">
                <h6 class="text-white/70 text-xs font-bold uppercase mb-1">Total Sales Today</h6>
                <h2 id="salesTodayValue" class="text-white text-2xl md:text-3xl font-black">₱ <?= number_format($salesToday,2) ?></h2>
            </div>
            <i class="fa-solid fa-coins absolute right-2.5 bottom-1.5 text-6xl md:text-7xl text-white/20 group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <div class="bg-linear-to-br from-[#004289] to-[#0072ff] p-6 rounded-2xl shadow-lg transform hover:-translate-y-2 transition-all duration-300 relative overflow-hidden group">
            <div class="relative z-10">
                <h6 class="text-white/70 text-xs font-bold uppercase mb-1">Liters Sold Today</h6>
                <h2 id="litersTodayValue" class="text-white text-2xl md:text-3xl font-black"><?= number_format($litersToday,2) ?> <span class="text-sm font-light">L</span></h2>
            </div>
            <i class="fa-solid fa-droplet absolute right-2.5 bottom-1.5 text-6xl md:text-7xl text-white/20 group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <div class="bg-linear-to-br from-[#f09819] to-[#edde5d] p-6 rounded-2xl shadow-lg transform hover:-translate-y-2 transition-all duration-300 relative overflow-hidden group">
            <div class="relative z-10">
                <h6 class="text-white/70 text-xs font-bold uppercase mb-1">Fuel Types</h6>
                <h2 id="totalFuelsValue" class="text-white text-2xl md:text-3xl font-black"><?= $totalFuels ?></h2>
            </div>
            <i class="fa-solid fa-oil-can absolute right-2.5 bottom-1.5 text-6xl md:text-7xl text-white/20 group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <div class="bg-linear-to-br from-[#ed1c24] to-[#f66161] p-6 rounded-2xl shadow-lg transform hover:-translate-y-2 transition-all duration-300 relative overflow-hidden group">
            <div class="relative z-10">
                <h6 class="text-white/70 text-xs font-bold uppercase mb-1">Low Stock Warnings</h6>
                <h2 id="lowStockValue" class="text-white text-2xl md:text-3xl font-black"><?= count($lowStock) ?></h2>
            </div>
            <i class="fa-solid fa-triangle-exclamation absolute right-2.5 bottom-1.5 text-6xl md:text-7xl text-white/20 group-hover:scale-110 transition-transform duration-500"></i>
        </div>

    </div>

    <!-- ✅ Stock Alerts -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center">
            <i class="fa-solid fa-triangle-exclamation text-petron-red mr-3 animate-pulse"></i>
            <h5 class="font-bold text-gray-800">Stock Alerts</h5>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left min-w-150">
                <thead class="bg-gray-50 text-gray-400 text-xs uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-4">Fuel Product</th>
                        <th class="px-6 py-4">Remaining Liters</th>
                        <th class="px-6 py-4 text-center">Critical Status</th>
                    </tr>
                </thead>
                <tbody id="stockAlertsBody" class="divide-y divide-gray-100">
                    <?php if(count($lowStock) > 0): ?>
                        <?php foreach($lowStock as $stock):
                            $percentage = ($stock['capacity'] > 0) ? ($stock['liters'] / $stock['capacity']) * 100 : 0;
                        ?>
                        <tr class="hover:bg-gray-50/80 transition-colors duration-200">
                            <td class="px-6 py-4 font-bold text-gray-700"><?= htmlspecialchars($stock['fuel_name']) ?></td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold <?= $percentage <= 10 ? 'text-red-600' : 'text-gray-700' ?>">
                                        <?= number_format($stock['liters'],2) ?> L
                                    </span>
                                    <span class="text-xs text-gray-400"><?= number_format($percentage,1) ?>% Capacity</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if($percentage <= 10): ?>
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-red-100 text-red-700 text-xs font-black ring-2 ring-red-500/20 animate-pulse">
                                        <span class="w-2 h-2 rounded-full bg-red-600 mr-2"></span> CRITICAL
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-amber-100 text-amber-700 text-xs font-black">
                                        WARNING
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-gray-400 italic text-sm">
                                All stock levels are optimal.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
        </div>
    </div>
    
</div>

    <!-- Charts Section -->
    <div class="p-4 md:p-8 space-y-6">
        <!-- Fuel Stock Chart -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center">
                <i class="fa-solid fa-chart-column text-petron-blue mr-3"></i>
                <h5 class="font-bold text-gray-800">Fuel Stock Level Chart</h5>
            </div>
            <div class="p-6">
                <div id="fuel-stock-chart"></div>
            </div>
        </div>

        <!-- Sales Graph -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center">
                <i class="fa-solid fa-chart-line text-petron-red mr-3"></i>
                <h5 class="font-bold text-gray-800">Sales Min / Max Graph</h5>
            </div>
            <div class="p-6">
                <div id="sales-trend-graph"></div>
            </div>
        </div>

    </div>
    
<?php
$fuelNames = [];
$fuelLiters = [];
$fuelPercentages = [];

foreach ($fuelLevels as $fuel) {
    $fuelNames[] = $fuel['fuel_name'];
    $fuelLiters[] = (float)$fuel['liters'];

    $percentage = ($fuel['capacity'] > 0)
        ? round(($fuel['liters'] / $fuel['capacity']) * 100, 2)
        : 0;

    $fuelPercentages[] = $percentage;
}

$salesDates = [];
$salesValues = [];

foreach ($salesTrend as $sale) {
    $salesDates[] = date('M d', strtotime($sale['sale_day']));
    $salesValues[] = (float)$sale['total_sales'];
}

$minSales = !empty($salesValues) ? min($salesValues) : 0;
$maxSales = !empty($salesValues) ? max($salesValues) : 0;
?>

<script>
window.dashboardData = {
    fuelNames: <?= json_encode($fuelNames) ?>,
    fuelLiters: <?= json_encode($fuelLiters) ?>,
    fuelPercentages: <?= json_encode($fuelPercentages) ?>,
    salesDates: <?= json_encode($salesDates) ?>,
    salesValues: <?= json_encode($salesValues) ?>,
    minSales: <?= json_encode($minSales) ?>,
    maxSales: <?= json_encode($maxSales) ?>,
    minSalesLabel: "Min Sales: ₱<?= number_format($minSales, 2) ?>",
    maxSalesLabel: "Max Sales: ₱<?= number_format($maxSales, 2) ?>"
};
</script>

<script src="/petron_system/public/assets/vendor/apexcharts/apexcharts.min.js?v=<?= filemtime(__DIR__ . '/../../assets/vendor/apexcharts/apexcharts.min.js') ?>"></script>
<script src="/petron_system/public/assets/js/dashboard.js"></script>
