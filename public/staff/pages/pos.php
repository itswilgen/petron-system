<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../includes/guards/staff_guard.php';
require_once __DIR__ . '/../../../controller/SaleController.php';

$controller = new SaleController();
$viewData = $controller->posPageData();

$fuels = $viewData['fuels'];
$rows = $viewData['rows'];
$total = $viewData['total'];
$pageNow = $viewData['pageNow'];
$limit = $viewData['limit'];
$totalPages = $viewData['totalPages'];
$search = $viewData['search'];
$dateFrom = $viewData['dateFrom'];
$dateTo = $viewData['dateTo'];
?>


<header class="bg-white py-4 px-4 md:px-8 shadow-sm flex justify-between items-center">
    <div class="flex items-center gap-3">
        <i class="fa-solid fa-gas-pump text-petron-blue text-xl"></i>
        <h4 class="text-lg md:text-xl font-extrabold text-petron-blue uppercase tracking-tight">
            Petron Terminal (POS)
        </h4>
    </div>
    <div class="hidden md:flex items-center gap-3 text-gray-500 font-semibold bg-gray-50 px-4 py-2 rounded-full border border-gray-100 shadow-inner">
        <i class="fa-solid fa-calendar-day text-petron-red"></i>
        <span id="live-date" class="text-sm"></span>
    </div>
</header>



<div class="p-4 md:p-8">
        <div id="posMessage" class="hidden mb-4 px-4 py-3 rounded-xl font-bold"></div>
    <form id="posForm" method="POST">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-7">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h5 class="font-extrabold text-petron-blue mb-6">Dispense Details</h5>
                    <div class="mb-5">
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Fuel Product</label>
                            <select name="fuel_id" id="fuel"
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-blue-200"
                                required>
                                    <option value="">Select Fuel Type</option>
                                        <?php while($row = $fuels->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option
                                    value="<?= $row['id'] ?>"
                                    data-price="<?= $row['price'] ?>"
                                    data-name="<?= htmlspecialchars($row['fuel_name']) ?>"
                                    data-liters="<?= (float)$row['liters'] ?>">
                                    <?= htmlspecialchars($row['fuel_name']) ?> (₱<?= number_format($row['price'], 2) ?>/L)
                                </option>
                            <?php endwhile; ?>
                        </select>
                            <p id="fuel-stock-info" class="mt-2 text-sm text-gray-500 font-semibold">Current Stock: -</p>
                    </div>

                    <div class="mb-5">
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Amount (PHP)</label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="amount"
                               class="w-full rounded-xl border border-gray-200 px-4 py-3 text-lg font-black focus:outline-none focus:ring-2 focus:ring-blue-200"
                               placeholder="0.00" required>
                        <input type="hidden" name="liters" id="liters" value="">
                        <p class="mt-2 text-sm text-gray-500 font-semibold">Liters are auto-calculated from amount and fuel price.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Quick Amount Presets</label>
                        <div class="grid grid-cols-3 gap-3">
                            <button type="button"
                                    class="py-3 rounded-xl border border-gray-200 font-black hover:bg-gray-50 transition"
                                    onclick="setAmount(100)">
                                ₱100
                            </button>
                            <button type="button"
                                    class="py-3 rounded-xl border border-gray-200 font-black hover:bg-gray-50 transition"
                                    onclick="setAmount(500)">
                                ₱500
                            </button>
                            <button type="button"
                                    class="py-3 rounded-xl border border-gray-200 font-black hover:bg-gray-50 transition"
                                    onclick="setAmount(1000)">
                                ₱1000
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <!-- RIGHT -->
            <div class="lg:col-span-5">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 h-full flex flex-col">
                    <h6 class="text-xs font-black uppercase text-gray-400 border-b pb-3 mb-5">Order Summary</h6>

                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Product:</span>
                            <span id="sum-name" class="font-extrabold text-gray-900">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Quantity:</span>
                            <span id="sum-liters" class="font-extrabold text-gray-900">0.00 L</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Amount:</span>
                            <span id="sum-amount" class="font-extrabold text-gray-900">₱ 0.00</span>
                        </div>
                    </div>

                    <div class="text-center bg-gray-50 rounded-2xl p-6 border border-gray-100 mb-6">
                        <div class="text-xs font-bold uppercase text-gray-400 mb-2">Total Amount Due</div>
                        <div class="text-4xl font-black text-red-600">
                            ₱ <span id="total">0.00</span>
                        </div>
                    </div>

                    <div class="mt-auto space-y-3">
                        <button type="submit" name="pay"
                                class="w-full py-4 rounded-2xl bg-red-600 text-white font-black hover:bg-red-700 transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-receipt"></i> COMPLETE TRANSACTION
                        </button>
                        <button type="button" id="printReceiptBtn" disabled
                                class="w-full py-3 rounded-2xl border border-gray-200 text-gray-400 font-black bg-white cursor-not-allowed transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-print"></i> PRINT LAST RECEIPT
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
<div class="p-4 md:p-8 space-y-6">
            <p id="totalTransactionsText" class="text-sm text-gray-500 font-semibold">
            Total Transactions: <?= $total ?>
            </p>
    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left min-w-200">
                <thead class="bg-gray-50 text-xs uppercase font-extrabold text-gray-600 border-b">
                    <tr>
                        <th class="px-6 py-4">Fuel Type</th>
                        <th class="px-6 py-4">Liters</th>
                        <th class="px-6 py-4">Total Price</th>
                        <th class="px-6 py-4">Date</th>
                    </tr>
                </thead>
            <tbody id="salesHistoryBody" class="divide-y divide-gray-100">
                    <?php if(count($rows) > 0): ?>
                        <?php foreach($rows as $sale): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-bold text-petron-blue">
                                    <?= htmlspecialchars($sale['fuel_name']) ?>
                                </td>
                                <td class="px-6 py-4 font-bold">
                                    <?= number_format((float)$sale['liters'], 2) ?> L
                                </td>
                                <td class="px-6 py-4 font-bold text-green-700">
                                    ₱ <?= number_format((float)$sale['total_price'], 2) ?>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    <?= htmlspecialchars($sale['sale_date']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic">
                                No sales records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="/petron_system/public/assets/js/pos.js?v=<?= filemtime(__DIR__ . '/../../assets/js/pos.js') ?>"></script>
