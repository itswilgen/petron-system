<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../includes/guards/admin_guard.php';
require_once __DIR__ . '/../../../controller/DeliveryController.php';

$controller = new DeliveryController();
$viewData = $controller->pageData();

$fuelsStmt    = $viewData['fuelsStmt'];
$deliveries   = $viewData['deliveries'];
$totalHistory = $viewData['totalHistory'];
$pageNow      = $viewData['pageNow'];
$limit        = $viewData['limit'];
$hasMore      = $viewData['hasMore'];
?>



<!-- Header -->
<header class="bg-white py-4 px-4 md:px-8 shadow-sm flex justify-between items-center">
  <div>
    <h4 class="text-xl font-extrabold text-petron-blue uppercase tracking-tight">DELIVERY</h4>
    <p class="text-sm text-gray-500 font-medium">Fuel Restocking & Delivery History</p>
  </div>

    <div class="hidden md:flex items-center gap-3 text-gray-500 font-semibold bg-gray-50 px-4 py-2 rounded-full border border-gray-100 shadow-inner">
        <i class="fa-solid fa-calendar-day text-petron-red"></i>
        <span id="live-date" class="text-sm"></span>
    </div>
</header>

<div class="p-4 md:p-8 space-y-6">

    <?php if (!empty($_SESSION['success'])): ?>
    <div class="bg-emerald-100 ..."><?= $_SESSION['success'] ?></div>
    <?php unset($_SESSION['success']); endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
    <div class="bg-red-100 ..."><?= $_SESSION['error'] ?></div>
    <?php unset($_SESSION['error']); endif; ?>

  <!-- Add Delivery -->
  <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
      <i class="fa-solid fa-truck-fast text-petron-blue"></i>
      <h5 class="font-bold text-gray-800">Add New Delivery</h5>
    </div>

    <div class="p-6">
      <form method="POST" class="flex flex-col lg:flex-row lg:items-end gap-3">

        <!-- Fuel -->
        <div class="lg:flex-[1.8]">
          <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Fuel Type</label>
          <select name="fuel_id" class="w-full rounded-xl border border-gray-300 px-4 py-3 font-semibold focus:outline-none focus:ring-2 focus:ring-petron-blue/30" required>
            <option value="">Select Fuel</option>
            <?php while($f = $fuelsStmt->fetch(PDO::FETCH_ASSOC)): ?>
            <?php
                $isFull = ((float)$f['capacity'] > 0) && ((float)$f['liters'] >= (float)$f['capacity']);
                $remaining = ((float)$f['capacity'] > 0) ? ((float)$f['capacity'] - (float)$f['liters']) : 0;
            ?>
            <option value="<?= (int)$f['id'] ?>" <?= $isFull ? 'disabled' : '' ?>>
                <?= htmlspecialchars($f['fuel_name']) ?>
                (Current: <?= number_format((float)$f['liters'],2) ?> L
                <?php if((float)$f['capacity'] > 0): ?>
                | Remaining: <?= number_format(max(0,$remaining),2) ?> L <?= $isFull ? '| FULL' : '' ?>
                <?php endif; ?>
                )
            </option>
            <?php endwhile; ?>
          </select>
        </div>

        <!-- Liters Added -->
        <div class="lg:flex-1">
          <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Liters Added</label>
          <input
            type="number"
            step="0.01"
            name="liters_added"
            class="w-full rounded-xl border border-gray-300 px-4 py-3 font-bold focus:outline-none focus:ring-2 focus:ring-petron-blue/30"
            placeholder="0.00"
            required
          >
        </div>

        <!-- Button -->
        <div class="lg:w-44 lg:flex-shrink-0">
          <label class="hidden lg:block text-xs font-bold text-transparent uppercase mb-2 select-none">Add</label>
          <button
            type="submit"
            name="add_delivery"
            class="w-full rounded-xl bg-red-600 hover:bg-emerald-700 text-white font-extrabold px-5 py-3 shadow flex items-center justify-center gap-2"
          >
            <i class="fa-solid fa-plus"></i>
            Add
          </button>
        </div>

      </form>

      <p class="text-xs text-gray-400 mt-3 italic">
        Tip: Delivery will be blocked automatically if it exceeds the fuel capacity (overfill protection).
      </p>
    </div>
  </div>


  <!-- Delivery History -->
  <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
      <i class="fa-solid fa-clock-rotate-left text-petron-red"></i>
      <h5 class="font-bold text-gray-800">Delivery History</h5>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left min-w-175">
        <thead class="bg-gray-50 text-xs uppercase font-extrabold text-gray-600 border-b">
          <tr>
            <th class="px-6 py-4">Fuel Type</th>
            <th class="px-6 py-4">Liters Added</th>
            <th class="px-6 py-4">Date</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-100">
          <?php if(count($deliveries) > 0): ?>
            <?php foreach($deliveries as $d): ?>
              <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4 font-bold text-petron-blue">
                  <?= htmlspecialchars($d['fuel_name']) ?>
                </td>
                <td class="px-6 py-4 font-bold">
                  <?= number_format((float)$d['liters_added'],2) ?> L
                </td>
                <td class="px-6 py-4 text-gray-600">
                  <?= htmlspecialchars($d['delivery_date']) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
                <div class="p-6 border-t border-gray-100 text-center">
                </div>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>

    </div>
  </div>

</div>
