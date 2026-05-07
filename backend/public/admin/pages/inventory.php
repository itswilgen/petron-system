<?php
require_once __DIR__ . '/../../../includes/guards/admin_guard.php';
require_once __DIR__ . '/../../../controller/FuelController.php';

$controller = new FuelController();

// Handle form submission for updating fuel details
$controller->update();
$fuels = $controller->index();
?>


<header class="bg-white py-4 px-4 md:px-8 shadow-sm flex justify-between items-center">
    <div>
        <h4 class="text-xl font-extrabold text-petron-blue uppercase tracking-tight">INVENTORY</h4>
        <p class="text-sm text-gray-500 font-medium">Fuel Stock Management</p>
    </div>
    <div class="hidden md:flex items-center gap-3 text-gray-500 font-semibold bg-gray-50 px-4 py-2 rounded-full border border-gray-100 shadow-inner">
        <i class="fa-solid fa-calendar-day text-petron-red"></i>
        <span id="live-date" class="text-sm"></span>
    </div>
</header>

<div class="p-4 md:p-8">

            <?php if(isset($_SESSION['success'])): ?>
            <div class="mb-6 bg-green-100 border border-green-400 text-green-800 px-6 py-4 rounded-xl shadow flex justify-between items-center">
                <span class="font-bold">
                    <?= $_SESSION['success']; ?>
                </span>
                <button onclick="this.parentElement.remove()" class="font-bold text-lg">&times;</button>
            </div>
        <?php unset($_SESSION['success']); endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="mb-6 bg-red-100 border border-red-400 text-red-800 px-6 py-4 rounded-xl shadow flex justify-between items-center">
                <span class="font-bold">
                    <?= $_SESSION['error']; ?>
                </span>
                <button onclick="this.parentElement.remove()" class="font-bold text-lg">&times;</button>
            </div>
        <?php unset($_SESSION['error']); endif; ?>

    <div class="mb-4 flex justify-end">
        <span id="inventoryLiveSyncText" class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-4 py-2 text-xs font-bold text-blue-700">
            <i class="fa-solid fa-rotate"></i>
            Auto-sync every 5 seconds
        </span>
    </div>

    <!-- Card container -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left min-w-245">
                <thead class="bg-gray-50 text-gray-700 text-xs uppercase font-extrabold border-b">
                    <tr>
                        <th class="px-6 py-4">Fuel Product</th>
                        <th class="px-6 py-4 w-55">Volume (Liters)</th>
                        <th class="px-6 py-4 w-55">Unit Price (₱)</th>
                        <th class="px-6 py-4 w-55">Status Indicator</th>
                        <th class="px-6 py-4 text-center w-40">Action</th>
                    </tr>
                </thead>

                <tbody id="inventoryTableBody" class="divide-y divide-gray-100">

                    <?php foreach ($fuels as $row): ?>
                    <?php
                    $status = $row['status'];

                    $badgeClass = "bg-emerald-600 text-white    "; // default = Available
                    if ($status === "Low Stock") {
                        $badgeClass = "bg-amber-400 text-black";
                    }
                    if ($status === "Out of Stock") {
                        $badgeClass = "bg-red-600 text-white";
                    }
                    ?>
                        <tr data-fuel-id="<?= (int)$row['id'] ?>">

                            <!-- Fuel name with icon -->
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center">
                                        <i class="fa-solid fa-gas-pump text-petron-blue"></i>
                                    </div>
                                    <span class="font-extrabold text-gray-900">
                                        <?= htmlspecialchars($row['fuel_name']) ?>
                                    </span>
                                </div>
                            </td>

                            <!-- Volume -->
                            <td class="px-6 py-5">
                                <form method="POST" class="flex items-center gap-2">
                                    <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden w-full max-w-50">
                                        <input
                                            type="number"
                                            step="0.01"
                                            value="<?= htmlspecialchars($row['liters']) ?>"
                                            class="inventory-liters-input px-3 py-2 w-full font-bold border-0 bg-gray-100 text-gray-600 cursor-not-allowed focus:outline-none focus:ring-0 focus:border-0"
                                            readonly
                                            disabled
                                        >
                                        <span class="px-3 py-2 bg-gray-50 border-l border-gray-300 font-bold text-gray-600">L</span>
                                    </div>
                            </td>

                            <!-- Price -->
                            <td class="px-6 py-5">
                                    <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                                        <span class="px-3 py-2 bg-gray-50 border-r border-gray-300 font-bold text-gray-600">₱</span>
                                        <input
                                            type="number"
                                            step="0.01"
                                            name="price"
                                            value="<?= htmlspecialchars($row['price']) ?>"
                                            class="inventory-price-input px-3 py-2 w-full font-bold border-0 focus:outline-none focus:ring-0 focus:border-0"
                                            required
                                        >
                                    </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-5">
                                <div class="flex flex-col gap-2">
                                    <!-- Badge -->
                                    <span 
                                        id="badge-<?= $row['id'] ?>" 
                                        class="inventory-status-badge inline-flex w-fit px-3 py-1 rounded-md text-xs font-extrabold transition-all duration-300 <?= $badgeClass ?>">
                                        <?= htmlspecialchars($status) ?>
                                    </span>

                                    <!-- Select -->
                                    <select name="status"
                                            onchange="updateBadgeColor(this, <?= $row['id'] ?>)"
                                            class="inventory-status-select w-full rounded-md border border-gray-300 px-3 py-2 font-semibold">
                                        <option value="Available" <?= $row['status']=="Available" ? "selected" : "" ?>>Available</option>
                                        <option value="Low Stock" <?= $row['status']=="Low Stock" ? "selected" : "" ?>>Low Stock</option>
                                        <option value="Out of Stock" <?= $row['status']=="Out of Stock" ? "selected" : "" ?>>Out of Stock</option>
                                    </select>
                                </div>
                            </td>

                            <!-- Action -->
                            <td class="px-6 py-5 text-center">
                                    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                    <button
                                        type="submit"
                                        name="update"
                                        class="bg-green-600 hover:bg-green-700 text-white font-extrabold px-8 py-2 rounded-lg shadow">Update</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>    
                </tbody>
            </table>    
        </div>
    </div>
</div>

<script src="/public/assets/js/inventory_live.js?v=<?= filemtime(__DIR__ . '/../../assets/js/inventory_live.js') ?>"></script>
