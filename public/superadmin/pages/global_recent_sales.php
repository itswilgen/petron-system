<?php
require_once __DIR__ . '/../../../includes/guards/superadmin_guard.php';
require_once __DIR__ . '/../../../controller/SuperAdminController.php';

$controller = new SuperAdminController();
$data = $controller->getGlobalRecentSalesData($_GET['date'] ?? null);

$summary = $data['summary'] ?? [];
$recentSales = $data['recentSales'] ?? [];
$branchRows = $data['branchRows'] ?? [];
$fuelRows = $data['fuelRows'] ?? [];
$operatingDate = (string)($data['operatingDate'] ?? date('Y-m-d'));
$isToday = (bool)($data['isToday'] ?? false);
$displayDate = date('F d, Y', strtotime($operatingDate));

function formatGlobalRecentSaleDateParts($value) {
    if (!$value) {
        return ['-', ''];
    }

    $time = strtotime((string)$value);
    if ($time === false) {
        return ['-', ''];
    }

    return [date('M d, Y', $time), date('h:i A', $time)];
}

function formatGlobalRecentSaleReference($id) {
    return 'TXN-' . str_pad((string)(int)$id, 6, '0', STR_PAD_LEFT);
}

[$latestSaleDate, $latestSaleTime] = formatGlobalRecentSaleDateParts($summary['latest_sale_at'] ?? null);
$latestSaleText = $latestSaleDate === '-'
    ? 'No sales recorded for this date'
    : "Latest sale: {$latestSaleDate} {$latestSaleTime}";
?>

<header class="bg-white py-4 px-4 md:px-8 shadow-sm flex justify-between items-center">
    <div>
        <h4 class="text-xl font-extrabold text-petron-blue uppercase tracking-tight">GLOBAL RECENT SALES</h4>
        <p class="text-sm text-gray-500 font-medium">All branch sale activity for <?= htmlspecialchars($displayDate) ?><?= $isToday ? ' ' : '' ?></p>
    </div>
    <div class="hidden md:flex items-center gap-3 text-gray-500 font-semibold bg-gray-50 px-4 py-2 rounded-full border border-gray-100 shadow-inner">
        <i class="fa-solid fa-calendar-day text-petron-red"></i>
        <span id="live-date" class="text-sm"></span>
    </div>
</header>

<div class="p-4 md:p-8 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500 font-semibold">Global Sales</p>
            <h3 id="globalSalesTotalValue" class="text-3xl font-black text-petron-blue mt-1">₱ <?= number_format((float)($summary['total_sales'] ?? 0), 2) ?></h3>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500 font-semibold">Transactions</p>
            <h3 id="globalSalesTransactionsValue" class="text-3xl font-black text-petron-blue mt-1"><?= number_format((int)($summary['transaction_count'] ?? 0)) ?></h3>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500 font-semibold">Liters Sold</p>
            <h3 id="globalSalesLitersValue" class="text-3xl font-black text-petron-blue mt-1"><?= number_format((float)($summary['total_liters'] ?? 0), 2) ?> L</h3>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500 font-semibold">Active Branches</p>
            <h3 id="globalSalesActiveBranchesValue" class="text-3xl font-black text-petron-blue mt-1">
                <?= number_format((int)($summary['active_branch_count'] ?? 0)) ?>/<?= number_format((int)($summary['branch_count'] ?? 0)) ?>
            </h3>
        </div>
    </div>

    <form method="GET" action="app.php" class="bg-white rounded-2xl shadow border border-gray-100 p-5">
        <input type="hidden" name="page" value="global_recent_sales">

        <div class="flex flex-col lg:flex-row lg:items-end gap-3">
            <div class="lg:w-52">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Operating Date</label>
                <input
                    id="globalSalesDateFilter"
                    type="date"
                    name="date"
                    value="<?= htmlspecialchars($operatingDate) ?>"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 font-semibold focus:outline-none focus:ring-2 focus:ring-petron-blue/30"
                >
            </div>

            <div class="lg:w-14 lg:flex-shrink-0">
                <label class="hidden lg:block text-xs font-bold text-transparent uppercase mb-2 select-none">Go</label>
                <button
                    type="submit"
                    title="Load sales for selected date"
                    class="w-full rounded-xl bg-red-600 text-white font-extrabold px-4 py-3 shadow hover:opacity-95"
                >
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>

            <div class="lg:flex-1 flex flex-col gap-3">
                <a href="app.php?page=global_recent_sales" class="text-sm font-bold text-gray-500 hover:text-gray-700 underline">
                    Today
                </a>

                <div class="flex flex-wrap items-center gap-3 text-sm font-semibold text-gray-600">
                    <span id="globalSalesLatestSaleText"><?= htmlspecialchars($latestSaleText) ?></span>
                    <span class="text-gray-300">|</span>
                    <span id="globalSalesAverageTicketText">Average Ticket: ₱ <?= number_format((float)($summary['average_ticket'] ?? 0), 2) ?></span>
                </div>
            </div>
        </div>
    </form>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <i class="fa-solid fa-receipt text-petron-blue"></i>
            <h5 class="font-bold text-gray-800">Recent Transactions</h5>
            <span id="globalRecentSalesLastUpdated" class="ml-auto text-xs font-semibold text-gray-500"></span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left min-w-240">
                <thead class="bg-gray-50 text-xs uppercase font-extrabold text-gray-600 border-b">
                    <tr>
                        <th class="px-6 py-4 whitespace-nowrap">Reference</th>
                        <th class="px-6 py-4 whitespace-nowrap">Branch</th>
                        <th class="px-6 py-4 whitespace-nowrap">Fuel</th>
                        <th class="px-6 py-4 whitespace-nowrap">Liters</th>
                        <th class="px-6 py-4 whitespace-nowrap">Price/L</th>
                        <th class="px-6 py-4 whitespace-nowrap">Total</th>
                        <th class="px-6 py-4 whitespace-nowrap">Date</th>
                    </tr>
                </thead>
                <tbody id="globalRecentSalesBody" class="divide-y divide-gray-100">
                    <?php if (count($recentSales) > 0): ?>
                        <?php foreach ($recentSales as $sale): ?>
                            <?php [$saleDate, $saleTime] = formatGlobalRecentSaleDateParts($sale['sale_date'] ?? null); ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-bold text-petron-blue whitespace-nowrap"><?= htmlspecialchars(formatGlobalRecentSaleReference($sale['id'] ?? 0)) ?></td>
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-900"><?= htmlspecialchars((string)($sale['branch_name'] ?? '-')) ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars((string)($sale['location'] ?? '-')) ?></p>
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap"><?= htmlspecialchars((string)($sale['fuel_name'] ?? '-')) ?></td>
                                <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap"><?= number_format((float)($sale['liters'] ?? 0), 2) ?> L</td>
                                <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">₱ <?= number_format((float)($sale['price'] ?? 0), 2) ?></td>
                                <td class="px-6 py-4 font-bold text-emerald-700 whitespace-nowrap">₱ <?= number_format((float)($sale['total_price'] ?? 0), 2) ?></td>
                                <td class="px-6 py-4 text-gray-600 text-sm whitespace-nowrap">
                                    <p class="font-semibold text-gray-700"><?= htmlspecialchars($saleDate) ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($saleTime) ?></p>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-400 italic">No sales records found for this date.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <i class="fa-solid fa-building text-petron-blue"></i>
                <h5 class="font-bold text-gray-800">Sales By Branch</h5>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left min-w-200">
                    <thead class="bg-gray-50 text-xs uppercase font-extrabold text-gray-600 border-b">
                        <tr>
                            <th class="px-6 py-4 whitespace-nowrap">Branch</th>
                            <th class="px-6 py-4 whitespace-nowrap">Transactions</th>
                            <th class="px-6 py-4 whitespace-nowrap">Liters</th>
                            <th class="px-6 py-4 whitespace-nowrap">Sales</th>
                            <th class="px-6 py-4 whitespace-nowrap">Latest Sale</th>
                        </tr>
                    </thead>
                    <tbody id="globalSalesBranchBody" class="divide-y divide-gray-100">
                        <?php if (count($branchRows) > 0): ?>
                            <?php foreach ($branchRows as $row): ?>
                                <?php [$branchLatestDate, $branchLatestTime] = formatGlobalRecentSaleDateParts($row['latest_sale_at'] ?? null); ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-gray-900"><?= htmlspecialchars((string)($row['branch_name'] ?? '-')) ?></p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars((string)($row['location'] ?? '-')) ?></p>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap"><?= number_format((int)($row['transaction_count'] ?? 0)) ?></td>
                                    <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap"><?= number_format((float)($row['total_liters'] ?? 0), 2) ?> L</td>
                                    <td class="px-6 py-4 font-bold text-emerald-700 whitespace-nowrap">₱ <?= number_format((float)($row['total_sales'] ?? 0), 2) ?></td>
                                    <td class="px-6 py-4 text-gray-600 text-sm whitespace-nowrap">
                                        <?php if ($branchLatestDate === '-'): ?>
                                            <span class="text-gray-400">-</span>
                                        <?php else: ?>
                                            <p class="font-semibold text-gray-700"><?= htmlspecialchars($branchLatestDate) ?></p>
                                            <p class="text-xs text-gray-500"><?= htmlspecialchars($branchLatestTime) ?></p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-400 italic">No branch data found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <i class="fa-solid fa-gas-pump text-petron-blue"></i>
                <h5 class="font-bold text-gray-800">Fuel Mix</h5>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left min-w-190">
                    <thead class="bg-gray-50 text-xs uppercase font-extrabold text-gray-600 border-b">
                        <tr>
                            <th class="px-6 py-4 whitespace-nowrap">Fuel</th>
                            <th class="px-6 py-4 whitespace-nowrap">Transactions</th>
                            <th class="px-6 py-4 whitespace-nowrap">Liters</th>
                            <th class="px-6 py-4 whitespace-nowrap">Sales</th>
                        </tr>
                    </thead>
                    <tbody id="globalSalesFuelBody" class="divide-y divide-gray-100">
                        <?php if (count($fuelRows) > 0): ?>
                            <?php foreach ($fuelRows as $row): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-bold text-gray-900"><?= htmlspecialchars((string)($row['fuel_name'] ?? '-')) ?></td>
                                    <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap"><?= number_format((int)($row['transaction_count'] ?? 0)) ?></td>
                                    <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap"><?= number_format((float)($row['total_liters'] ?? 0), 2) ?> L</td>
                                    <td class="px-6 py-4 font-bold text-emerald-700 whitespace-nowrap">₱ <?= number_format((float)($row['total_sales'] ?? 0), 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic">No fuel sales found for this date.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="application/json" id="globalRecentSalesInitialData">
<?= json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
</script>
<script src="/petron_system/public/assets/js/global_recent_sales.js?v=<?= filemtime(__DIR__ . '/../../assets/js/global_recent_sales.js') ?>"></script>
