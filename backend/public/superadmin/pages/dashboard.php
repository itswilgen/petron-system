<?php
require_once __DIR__ . '/../../../includes/guards/superadmin_guard.php';
require_once __DIR__ . '/../../../controller/SuperAdminController.php';

$controller = new SuperAdminController();
$data = $controller->getDashboardData();

$salesToday = (float)($data['salesToday'] ?? 0);
$litersToday = (float)($data['litersToday'] ?? 0);
$fuelCount = (int)($data['fuelCount'] ?? 0);
$totalBranches = (int)($data['totalBranches'] ?? 0);
$adminCount = (int)($data['adminCount'] ?? 0);
$staffCount = (int)($data['staffCount'] ?? 0);
$lowStock = $data['lowStock'] ?? [];
$branchSummary = $data['branchSummary'] ?? [];
?>

<header class="bg-white py-4 px-4 md:px-8 shadow-sm flex justify-between items-center">
    <div>
        <h4 class="text-xl font-extrabold text-petron-blue uppercase tracking-tight">SUPER ADMIN DASHBOARD</h4>
        <p class="text-sm text-gray-500 font-medium">Regional overview across all branches</p>
    </div>
    <div class="hidden md:flex items-center gap-3 text-gray-500 font-semibold bg-gray-50 px-4 py-2 rounded-full border border-gray-100 shadow-inner">
        <i class="fa-solid fa-calendar-day text-petron-red"></i>
        <span id="live-date" class="text-sm"></span>
    </div>
</header>

<div class="p-4 md:p-8 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-3 gap-6">
        <div class="bg-linear-to-br from-[#1d976c] to-[#93f9b9] p-6 rounded-2xl shadow-lg text-white">
            <p class="text-xs font-bold uppercase opacity-85">Regional Sales Today</p>
            <h2 id="salesTodayValue" class="text-3xl font-black mt-1">₱ <?= number_format($salesToday, 2) ?></h2>
        </div>

        <div class="bg-linear-to-br from-[#004289] to-[#0072ff] p-6 rounded-2xl shadow-lg text-white">
            <p class="text-xs font-bold uppercase opacity-85">Regional Liters Today</p>
            <h2 id="litersTodayValue" class="text-3xl font-black mt-1"><?= number_format($litersToday, 2) ?> L</h2>
        </div>

        <div class="bg-linear-to-br from-[#ed1c24] to-[#f66161] p-6 rounded-2xl shadow-lg text-white">
            <p class="text-xs font-bold uppercase opacity-85">Total Fuel Types</p>
            <h2 id="fuelCountValue" class="text-3xl font-black mt-1"><?= $fuelCount ?></h2>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500 font-semibold">Branches</p>
            <h3 id="totalBranchesValue" class="text-3xl font-black text-petron-blue mt-1"><?= $totalBranches ?></h3>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500 font-semibold">Admin Accounts</p>
            <h3 id="adminCountValue" class="text-3xl font-black text-petron-blue mt-1"><?= $adminCount ?></h3>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500 font-semibold">Staff Accounts</p>
            <h3 id="staffCountValue" class="text-3xl font-black text-petron-blue mt-1"><?= $staffCount ?></h3>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <i class="fa-solid fa-chart-column text-petron-blue"></i>
                <h5 class="font-bold text-gray-800">Branch Performance Chart</h5>
            </div>
            <div class="p-4 md:p-6">
                <div id="branchPerformanceChart"></div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <i class="fa-solid fa-chart-line text-petron-blue"></i>
            <h5 class="font-bold text-gray-800">Regional Sales Trend (Last 7 Days)</h5>
        </div>
        <div class="p-4 md:p-6">
            <div id="salesTrendChart"></div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <i class="fa-solid fa-building text-petron-blue"></i>
            <h5 class="font-bold text-gray-800">Branch Performance Today</h5>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left min-w-190">
                <thead class="bg-gray-50 text-xs uppercase font-extrabold text-gray-600 border-b">
                    <tr>
                        <th class="px-6 py-4">Branch</th>
                        <th class="px-6 py-4">Sales Today</th>
                        <th class="px-6 py-4">Liters Today</th>
                        <th class="px-6 py-4">Fuel Types</th>
                        <th class="px-6 py-4">Assigned Admins</th>
                    </tr>
                </thead>
                <tbody id="branchSummaryBody" class="divide-y divide-gray-100">
                    <?php foreach ($branchSummary as $branch): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-bold text-gray-900"><?= htmlspecialchars($branch['branch_name']) ?></td>
                            <td class="px-6 py-4 font-semibold text-gray-700">₱ <?= number_format((float)$branch['sales_today'], 2) ?></td>
                            <td class="px-6 py-4 font-semibold text-gray-700"><?= number_format((float)$branch['liters_today'], 2) ?> L</td>
                            <td class="px-6 py-4 font-semibold text-gray-700"><?= (int)$branch['total_fuels'] ?></td>
                            <td class="px-6 py-4 text-gray-700"><?= htmlspecialchars($branch['admin_usernames']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <i class="fa-solid fa-triangle-exclamation text-petron-red"></i>
            <h5 class="font-bold text-gray-800">Low Stock Alerts (All Branches)</h5>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left min-w-170">
                <thead class="bg-gray-50 text-xs uppercase font-extrabold text-gray-600 border-b">
                    <tr>
                        <th class="px-6 py-4">Branch</th>
                        <th class="px-6 py-4">Fuel</th>
                        <th class="px-6 py-4">Remaining</th>
                        <th class="px-6 py-4">Status</th>
                    </tr>
                </thead>
                <tbody id="lowStockBody" class="divide-y divide-gray-100">
                    <?php if (count($lowStock) > 0): ?>
                        <?php foreach ($lowStock as $stock): ?>
                            <?php
                                $capacity = (float)($stock['capacity'] ?? 0);
                                $liters = (float)($stock['liters'] ?? 0);
                                $percentage = $capacity > 0 ? ($liters / $capacity) * 100 : 0;
                            ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-semibold text-gray-700"><?= htmlspecialchars($stock['branch_name']) ?></td>
                                <td class="px-6 py-4 font-semibold text-gray-700"><?= htmlspecialchars($stock['fuel_name']) ?></td>
                                <td class="px-6 py-4 font-semibold text-gray-700"><?= number_format($liters, 2) ?> L (<?= number_format($percentage, 1) ?>%)</td>
                                <td class="px-6 py-4">
                                    <?php if ($percentage <= 10): ?>
                                        <span class="inline-flex px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-extrabold">CRITICAL</span>
                                    <?php else: ?>
                                        <span class="inline-flex px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-extrabold">WARNING</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic">No low stock alerts right now.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
window.superAdminDashboardData = <?= json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
</script>
<script src="/public/assets/vendor/apexcharts/apexcharts.min.js?v=<?= filemtime(__DIR__ . '/../../assets/vendor/apexcharts/apexcharts.min.js') ?>"></script>
<script src="/public/assets/js/superadmin_dashboard.js"></script>
