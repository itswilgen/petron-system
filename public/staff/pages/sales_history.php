<?php
require_once __DIR__ . '/../../../includes/guards/staff_guard.php';
require_once __DIR__ . '/../../../controller/SaleController.php';

$controller = new SaleController();
$viewData = $controller->salesHistoryPageData();

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
    <div>
        <h4 class="text-xl font-extrabold text-petron-blue uppercase tracking-tight">SALES HISTORY</h4>
        <p class="text-sm text-gray-500 font-medium">View and filter transaction records</p>
    </div>

    <div class="hidden md:flex items-center gap-3 text-gray-500 font-semibold bg-gray-50 px-4 py-2 rounded-full border border-gray-100 shadow-inner">
        <i class="fa-solid fa-calendar-day text-petron-red"></i>
        <span id="live-date" class="text-sm"></span>
    </div>
</header>

<div class="p-4 md:p-8 space-y-6">

    <!-- Filters -->
    <form method="GET" action="app.php" class="bg-white rounded-2xl shadow border border-gray-100 p-5">
        <input type="hidden" name="page" value="sales_history">

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Search Fuel</label>
                <input
                    type="text"
                    name="q"
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="e.g. Diesel"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 font-semibold focus:outline-none focus:ring-2 focus:ring-petron-blue/30"
                >
            </div>

            <div class="md:col-span-4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Date From</label>
                <input
                    type="date"
                    name="from"
                    value="<?= htmlspecialchars($dateFrom) ?>"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 font-semibold focus:outline-none focus:ring-2 focus:ring-petron-blue/30"
                >
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Date To</label>
                <input
                    type="date"
                    name="to"
                    value="<?= htmlspecialchars($dateTo) ?>"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 font-semibold focus:outline-none focus:ring-2 focus:ring-petron-blue/30"
                >
            </div>

            <div class="md:col-span-2">
                <button
                    type="submit"
                    class="w-full rounded-xl bg-red-600 text-white font-extrabold px-4 py-3 shadow hover:opacity-95"
                >
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-2 items-center">
            <a href="app.php?page=sales_history" class="text-sm font-bold text-gray-500 hover:text-gray-700 underline">
                Clear filters
            </a>

            <span class="text-sm text-gray-400">•</span>

            <span class="text-sm font-semibold text-gray-600">
                Showing <?= count($rows) ?> of <?= $total ?> record(s)
            </span>
        </div>
    </form>
            <p class="text-sm text-gray-500 font-semibold">
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

                <tbody class="divide-y divide-gray-100">
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

        <!-- Pagination -->
        <?php if($totalPages > 1): ?>
            <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                <div class="text-sm text-gray-500 font-semibold">
                    Page <?= $pageNow ?> of <?= $totalPages ?>
                </div>

                <div class="flex gap-2">
                    <?php
                    $base = "app.php?page=sales_history&q=" . urlencode($search)
                          . "&from=" . urlencode($dateFrom)
                          . "&to=" . urlencode($dateTo);
                    ?>

                    <a
                        href="<?= $base ?>&p=<?= max(1, $pageNow - 1) ?>"
                        class="px-4 py-2 rounded-lg border font-bold <?= $pageNow <= 1 ? 'text-gray-300 pointer-events-none' : 'text-gray-700 hover:bg-gray-50' ?>">Prev</a>

                    <a
                        href="<?= $base ?>&p=<?= min($totalPages, $pageNow + 1) ?>"
                        class="px-4 py-2 rounded-lg border font-bold <?= $pageNow >= $totalPages ? 'text-gray-300 pointer-events-none' : 'text-gray-700 hover:bg-gray-50' ?>">Next</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>
