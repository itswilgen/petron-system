<?php
require_once __DIR__ . '/../../../includes/guards/superadmin_guard.php';
require_once __DIR__ . '/../../../controller/SuperAdminController.php';

$controller = new SuperAdminController();
$data = $controller->getBranchOperationsData();

$rows = $data['rows'] ?? [];
$totals = $data['totals'] ?? [];

function formatDateParts($value) {
    if (!$value) {
        return ['-', ''];
    }

    $time = strtotime((string)$value);
    if ($time === false) {
        return ['-', ''];
    }

    return [date('M d, Y', $time), date('h:i A', $time)];
}
?>

<header class="bg-white py-4 px-4 md:px-8 shadow-sm flex justify-between items-center">
    <div>
        <h4 class="text-xl font-extrabold text-petron-blue uppercase tracking-tight">BRANCH OPERATIONS</h4>
        <p class="text-sm text-gray-500 font-medium">Live branch-by-branch operations monitor (auto-updates every 5 seconds)</p>
    </div>
    <div class="hidden md:flex items-center gap-3 text-gray-500 font-semibold bg-gray-50 px-4 py-2 rounded-full border border-gray-100 shadow-inner">
        <i class="fa-solid fa-calendar-day text-petron-red"></i>
        <span id="live-date" class="text-sm"></span>
    </div>
</header>

<div class="p-4 md:p-8 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500 font-semibold">Branches Monitored</p>
            <h3 id="opsBranchCountValue" class="text-3xl font-black text-petron-blue mt-1"><?= (int)($totals['branch_count'] ?? 0) ?></h3>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500 font-semibold">Regional Sales Today</p>
            <h3 id="opsSalesTodayValue" class="text-3xl font-black text-petron-blue mt-1">₱ <?= number_format((float)($totals['sales_today'] ?? 0), 2) ?></h3>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500 font-semibold">Regional Transactions Today</p>
            <h3 id="opsTransactionsTodayValue" class="text-3xl font-black text-petron-blue mt-1"><?= number_format((int)($totals['transactions_today'] ?? 0)) ?></h3>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500 font-semibold">Liters Sold Today</p>
            <h3 id="opsLitersTodayValue" class="text-3xl font-black text-petron-blue mt-1"><?= number_format((float)($totals['liters_today'] ?? 0), 2) ?> L</h3>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500 font-semibold">Deliveries Today</p>
            <h3 id="opsDeliveriesTodayValue" class="text-3xl font-black text-petron-blue mt-1"><?= number_format((int)($totals['deliveries_today'] ?? 0)) ?></h3>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm text-gray-500 font-semibold">Low Stock Alerts</p>
            <h3 id="opsLowStockCountValue" class="text-3xl font-black text-petron-red mt-1"><?= number_format((int)($totals['low_stock_count'] ?? 0)) ?></h3>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <i class="fa-solid fa-building-circle-check text-petron-blue"></i>
            <h5 class="font-bold text-gray-800">Operations By Branch (Today)</h5>
            <span id="opsLastUpdated" class="ml-auto text-xs font-semibold text-gray-500"></span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left min-w-260">
                <thead class="bg-gray-50 text-xs uppercase font-extrabold text-gray-600 border-b">
                    <tr>
                        <th class="px-6 py-4 whitespace-nowrap">Branch</th>
                        <th class="px-6 py-4 whitespace-nowrap">Sales</th>
                        <th class="px-6 py-4 whitespace-nowrap">Transactions</th>
                        <th class="px-6 py-4 whitespace-nowrap">Liters Sold</th>
                        <th class="px-6 py-4 whitespace-nowrap">Deliveries</th>
                        <th class="px-6 py-4 whitespace-nowrap">Stock</th>
                        <th class="px-6 py-4 whitespace-nowrap">Low Stock</th>
                        <th class="px-6 py-4 whitespace-nowrap">Users</th>
                        <th class="px-6 py-4 whitespace-nowrap">Last Sale</th>
                        <th class="px-6 py-4 whitespace-nowrap">Last Delivery</th>
                        <th class="px-6 py-4 whitespace-nowrap">Health</th>
                    </tr>
                </thead>
                <tbody id="branchOperationsBody" class="divide-y divide-gray-100">
                    <?php if (count($rows) > 0): ?>
                        <?php foreach ($rows as $row): ?>
                            <?php
                            $lowStock = (int)($row['low_stock_count'] ?? 0);
                            $transactions = (int)($row['transactions_today'] ?? 0);
                            $deliveries = (int)($row['deliveries_today'] ?? 0);
                            [$lastSaleDate, $lastSaleTime] = formatDateParts($row['last_sale_at'] ?? null);
                            [$lastDeliveryDate, $lastDeliveryTime] = formatDateParts($row['last_delivery_at'] ?? null);
                            $healthClass = 'bg-emerald-100 text-emerald-700';
                            $healthLabel = 'Normal';

                            if ($lowStock > 0) {
                                $healthClass = 'bg-amber-100 text-amber-700';
                                $healthLabel = 'Needs Attention';
                            }

                            if ($lowStock >= 2) {
                                $healthClass = 'bg-red-100 text-red-700';
                                $healthLabel = 'Critical';
                            }

                            if ($lowStock === 0 && $transactions === 0 && $deliveries === 0) {
                                $healthClass = 'bg-gray-100 text-gray-700';
                                $healthLabel = 'No Activity';
                            }
                            ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-900"><?= htmlspecialchars((string)($row['branch_name'] ?? '-')) ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars((string)($row['location'] ?? '-')) ?></p>
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">₱ <?= number_format((float)($row['sales_today'] ?? 0), 2) ?></td>
                                <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap"><?= number_format((int)($row['transactions_today'] ?? 0)) ?></td>
                                <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap"><?= number_format((float)($row['liters_today'] ?? 0), 2) ?> L</td>
                                <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">
                                    <?= number_format((int)($row['deliveries_today'] ?? 0)) ?>
                                    <span class="text-xs text-gray-500">(<?= number_format((float)($row['delivered_liters_today'] ?? 0), 2) ?> L)</span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap"><?= number_format((float)($row['total_stock_liters'] ?? 0), 2) ?> L</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($lowStock > 0): ?>
                                        <span class="inline-flex px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-extrabold"><?= $lowStock ?> alert<?= $lowStock > 1 ? 's' : '' ?></span>
                                    <?php else: ?>
                                        <span class="inline-flex px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-extrabold">OK</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <span class="inline-flex w-fit items-center gap-1 px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 text-xs font-bold">
                                            <i class="fa-solid fa-user-shield"></i> Admin: <?= (int)($row['admin_count'] ?? 0) ?>
                                        </span>
                                        <span class="inline-flex w-fit items-center gap-1 px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-700 text-xs font-bold">
                                            <i class="fa-solid fa-user"></i> Staff: <?= (int)($row['staff_count'] ?? 0) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 text-sm whitespace-nowrap">
                                    <?php if ($lastSaleDate === '-'): ?>
                                        <span class="text-gray-400">-</span>
                                    <?php else: ?>
                                        <p class="font-semibold text-gray-700"><?= htmlspecialchars($lastSaleDate) ?></p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($lastSaleTime) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-gray-600 text-sm whitespace-nowrap">
                                    <?php if ($lastDeliveryDate === '-'): ?>
                                        <span class="text-gray-400">-</span>
                                    <?php else: ?>
                                        <p class="font-semibold text-gray-700"><?= htmlspecialchars($lastDeliveryDate) ?></p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($lastDeliveryTime) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-extrabold <?= $healthClass ?>"><?= $healthLabel ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="px-6 py-10 text-center text-gray-400 italic">No branch operation data found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
window.branchOperationsData = <?= json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
</script>
<script src="/petron_system/public/assets/js/branch_operations.js?v=<?= filemtime(__DIR__ . '/../../assets/js/branch_operations.js') ?>"></script>
