<?php
require_once __DIR__ . '/../../../includes/guards/superadmin_guard.php';
require_once __DIR__ . '/../../../controller/SuperAdminController.php';

$controller = new SuperAdminController();
$error = $controller->updateGlobalPricing();
$rows = $controller->getGlobalPricingData();
?>

<header class="bg-white py-4 px-4 md:px-8 shadow-sm flex justify-between items-center">
    <div>
        <h4 class="text-xl font-extrabold text-petron-blue uppercase tracking-tight">GLOBAL FUEL PRICING</h4>
        <p class="text-sm text-gray-500 font-medium">Change price per liter once and apply to all branches automatically</p>
    </div>
    <div class="hidden md:flex items-center gap-3 text-gray-500 font-semibold bg-gray-50 px-4 py-2 rounded-full border border-gray-100 shadow-inner">
        <i class="fa-solid fa-calendar-day text-petron-red"></i>
        <span id="live-date" class="text-sm"></span>
    </div>
</header>

<div class="p-4 md:p-8 space-y-6">
    <?php if (isset($_GET['updated'])): ?>
        <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 px-6 py-4 rounded-xl shadow flex justify-between items-center">
            <span class="font-bold">
                Updated <?= htmlspecialchars((string)($_GET['fuel'] ?? 'fuel')) ?> to ₱<?= number_format((float)($_GET['price'] ?? 0), 2) ?>/L across <?= (int)($_GET['rows'] ?? 0) ?> row(s).
            </span>
            <button onclick="this.parentElement.remove()" class="font-bold text-lg">&times;</button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-300 text-red-800 px-6 py-4 rounded-xl shadow flex justify-between items-center">
            <span class="font-bold"><?= htmlspecialchars($error) ?></span>
            <button onclick="this.parentElement.remove()" class="font-bold text-lg">&times;</button>
        </div>
    <?php endif; ?>

    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-sm text-blue-700 font-semibold">
        This page applies one fuel price across every branch using the same fuel product name.
        Editing a price field triggers automatic apply after confirmation.
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <i class="fa-solid fa-gas-pump text-petron-blue"></i>
            <h5 class="font-bold text-gray-800">Auto Apply Price/L Across All Branches</h5>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left min-w-200">
                <thead class="bg-gray-50 text-xs uppercase font-extrabold text-gray-600 border-b">
                    <tr>
                        <th class="px-6 py-4 whitespace-nowrap">Fuel Product</th>
                        <th class="px-6 py-4 whitespace-nowrap">Branches Affected</th>
                        <th class="px-6 py-4 whitespace-nowrap">Current Price Range</th>
                        <th class="px-6 py-4 whitespace-nowrap">New Global Price/L</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (count($rows) > 0): ?>
                        <?php foreach ($rows as $row): ?>
                            <?php
                            $fuelName = (string)($row['fuel_name'] ?? '');
                            $branchCount = (int)($row['branch_count'] ?? 0);
                            $minPrice = (float)($row['min_price'] ?? 0);
                            $maxPrice = (float)($row['max_price'] ?? 0);
                            $avgPrice = (float)($row['avg_price'] ?? 0);
                            $hasDiff = abs($maxPrice - $minPrice) > 0.0001;
                            ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-bold text-gray-900"><?= htmlspecialchars($fuelName) ?></td>
                                <td class="px-6 py-4 font-semibold text-gray-700"><?= $branchCount ?></td>
                                <td class="px-6 py-4 font-semibold text-gray-700">
                                    <?php if ($hasDiff): ?>
                                        ₱ <?= number_format($minPrice, 2) ?> - ₱ <?= number_format($maxPrice, 2) ?>
                                    <?php else: ?>
                                        ₱ <?= number_format($avgPrice, 2) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <form method="POST" class="global-price-form">
                                        <input type="hidden" name="fuel_name" value="<?= htmlspecialchars($fuelName) ?>">
                                        <input type="hidden" name="apply_global_price" value="1">
                                        <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden max-w-56">
                                            <span class="px-3 py-2 bg-gray-50 border-r border-gray-300 font-bold text-gray-600">₱</span>
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                name="price"
                                                value="<?= number_format($avgPrice, 2, '.', '') ?>"
                                                class="global-price-input px-3 py-2 w-full font-bold border-0 focus:outline-none focus:ring-0 focus:border-0"
                                                required
                                            >
                                        </div>
                                    </form>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button
                                        type="button"
                                        class="apply-global-btn inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold px-5 py-2 shadow"
                                        data-fuel-name="<?= htmlspecialchars($fuelName) ?>"
                                    >
                                        <i class="fa-solid fa-arrows-rotate"></i>
                                        Apply All Branches
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-400 italic">No fuel pricing data found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="/petron_system/public/assets/js/global_pricing.js?v=<?= filemtime(__DIR__ . '/../../assets/js/global_pricing.js') ?>"></script>
