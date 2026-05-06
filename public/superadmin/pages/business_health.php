<?php
require_once __DIR__ . '/../../../includes/guards/superadmin_guard.php';
require_once __DIR__ . '/../../../controller/SuperAdminController.php';

$controller = new SuperAdminController();
$data = $controller->getBusinessHealthData();

$rows = $data['rows'] ?? [];
$summary = $data['summary'] ?? [];

$trendAmount = (float)($summary['trend_amount'] ?? 0);
$trendPercent = (float)($summary['trend_percent'] ?? 0);
$trendPositive = $trendAmount >= 0;
?>

<header class="bg-white py-4 px-4 md:px-8 shadow-sm flex justify-between items-center">
    <div>
        <h4 class="text-xl font-extrabold text-petron-blue uppercase tracking-tight">BUSINESS HEALTH</h4>
        <p class="text-sm text-gray-500 font-medium">Profit/Loss trend and operational health by branch</p>
    </div>
    <div class="hidden md:flex items-center gap-3 text-gray-500 font-semibold bg-gray-50 px-4 py-2 rounded-full border border-gray-100 shadow-inner">
        <i class="fa-solid fa-calendar-day text-petron-red"></i>
        <span id="live-date" class="text-sm"></span>
    </div>
</header>

<div class="p-4 md:p-8 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500 font-semibold">Revenue (Last 7 Days)</p>
            <h3 id="bizRevenue7dValue" class="text-3xl font-black text-petron-blue mt-1">₱ <?= number_format((float)($summary['revenue_7d'] ?? 0), 2) ?></h3>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500 font-semibold">Profit/Loss Trend vs Previous 7 Days</p>
            <h3 id="bizTrendAmountValue" class="text-3xl font-black mt-1 <?= $trendPositive ? 'text-emerald-600' : 'text-red-600' ?>">
                <?= $trendPositive ? '+' : '-' ?>₱ <?= number_format(abs($trendAmount), 2) ?>
            </h3>
            <p id="bizTrendPercentValue" class="text-sm font-semibold <?= $trendPositive ? 'text-emerald-600' : 'text-red-600' ?>">
                <?= $trendPositive ? '+' : '' ?><?= number_format($trendPercent, 1) ?>%
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500 font-semibold">Healthy Branches</p>
            <h3 id="bizHealthyBranchesValue" class="text-3xl font-black text-emerald-600 mt-1">
                <?= (int)($summary['good_count'] ?? 0) ?>/<?= (int)($summary['branch_count'] ?? 0) ?>
            </h3>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500 font-semibold">Average Health Score</p>
            <h3 id="bizAvgScoreValue" class="text-3xl font-black text-petron-blue mt-1"><?= number_format((float)($summary['avg_health_score'] ?? 0), 0) ?>/100</h3>
            <p id="bizRiskCountValue" class="text-sm text-gray-500 font-semibold">
                At Risk: <?= (int)($summary['warning_count'] ?? 0) + (int)($summary['bad_count'] ?? 0) ?> branch(es)
            </p>
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-sm text-blue-700 font-semibold">
        Profit/Loss here is based on revenue movement between the last 7 days and previous 7 days. True net profit requires expense/cost tracking.
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <i class="fa-solid fa-stethoscope text-petron-blue"></i>
            <h5 class="font-bold text-gray-800">Branch Business Health Monitor</h5>
            <span id="bizLastUpdated" class="ml-auto text-xs font-semibold text-gray-500"></span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left min-w-240">
                <thead class="bg-gray-50 text-xs uppercase font-extrabold text-gray-600 border-b">
                    <tr>
                        <th class="px-6 py-4 whitespace-nowrap">Branch</th>
                        <th class="px-6 py-4 whitespace-nowrap">Revenue 7D</th>
                        <th class="px-6 py-4 whitespace-nowrap">Prev 7D</th>
                        <th class="px-6 py-4 whitespace-nowrap">Profit/Loss</th>
                        <th class="px-6 py-4 whitespace-nowrap">Transactions</th>
                        <th class="px-6 py-4 whitespace-nowrap">Avg Ticket</th>
                        <th class="px-6 py-4 whitespace-nowrap">Stock Coverage</th>
                        <th class="px-6 py-4 whitespace-nowrap">Low Stock</th>
                        <th class="px-6 py-4 whitespace-nowrap">Deliveries</th>
                        <th class="px-6 py-4 whitespace-nowrap">Health Score</th>
                        <th class="px-6 py-4 whitespace-nowrap">Health</th>
                        <th class="px-6 py-4">Insight</th>
                    </tr>
                </thead>
                <tbody id="businessHealthBody" class="divide-y divide-gray-100">
                    <?php if (count($rows) > 0): ?>
                        <?php foreach ($rows as $row): ?>
                            <?php
                            $trend = (float)($row['trend_amount'] ?? 0);
                            $trendIsPositive = $trend >= 0;
                            $health = (string)($row['health'] ?? 'Warning');
                            $healthClass = 'bg-amber-100 text-amber-700';
                            if ($health === 'Good') {
                                $healthClass = 'bg-emerald-100 text-emerald-700';
                            } elseif ($health === 'Bad') {
                                $healthClass = 'bg-red-100 text-red-700';
                            }
                            $coverage = $row['stock_coverage_days'];
                            ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-900"><?= htmlspecialchars((string)($row['branch_name'] ?? '-')) ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars((string)($row['location'] ?? '-')) ?></p>
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">₱ <?= number_format((float)($row['revenue_7d'] ?? 0), 2) ?></td>
                                <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">₱ <?= number_format((float)($row['revenue_prev_7d'] ?? 0), 2) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="font-bold <?= $trendIsPositive ? 'text-emerald-600' : 'text-red-600' ?>">
                                        <?= $trendIsPositive ? '+' : '-' ?>₱ <?= number_format(abs($trend), 2) ?>
                                    </p>
                                    <p class="text-xs font-semibold <?= $trendIsPositive ? 'text-emerald-600' : 'text-red-600' ?>">
                                        <?= $trendIsPositive ? '+' : '' ?><?= number_format((float)($row['trend_percent'] ?? 0), 1) ?>%
                                    </p>
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">
                                    <?= number_format((int)($row['transactions_7d'] ?? 0)) ?>
                                    <span class="text-xs text-gray-500"> (Today: <?= (int)($row['transactions_today'] ?? 0) ?>)</span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">₱ <?= number_format((float)($row['avg_ticket_7d'] ?? 0), 2) ?></td>
                                <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">
                                    <?= $coverage === null ? '-' : number_format((float)$coverage, 1) . ' days' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php $lowStock = (int)($row['low_stock_count'] ?? 0); ?>
                                    <?php if ($lowStock > 0): ?>
                                        <span class="inline-flex px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-extrabold"><?= $lowStock ?> alert<?= $lowStock > 1 ? 's' : '' ?></span>
                                    <?php else: ?>
                                        <span class="inline-flex px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-extrabold">OK</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">
                                    <?= number_format((int)($row['deliveries_7d'] ?? 0)) ?>
                                    <span class="text-xs text-gray-500">(<?= number_format((float)($row['delivered_liters_7d'] ?? 0), 2) ?> L)</span>
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-900 whitespace-nowrap"><?= (int)($row['health_score'] ?? 0) ?>/100</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-extrabold <?= $healthClass ?>"><?= htmlspecialchars($health) ?></span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 min-w-90"><?= htmlspecialchars((string)($row['note'] ?? '-')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="12" class="px-6 py-10 text-center text-gray-400 italic">No business health data available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
window.businessHealthData = <?= json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
</script>
<script src="/petron_system/public/assets/js/business_health.js?v=<?= filemtime(__DIR__ . '/../../assets/js/business_health.js') ?>"></script>
