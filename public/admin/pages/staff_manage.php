<?php
require_once __DIR__ . '/../../../includes/guards/admin_guard.php';
require_once __DIR__ . '/../../../controller/UserController.php';

$controller = new UserController();

// handle create + delete
$error = $controller->createStaff();
$controller->deleteStaff();

$staffUsers = $controller->listStaff();
?>

<!-- Header -->
<header class="bg-white py-4 px-4 md:px-8 shadow-sm flex justify-between items-center">
  <div>
    <h4 class="text-xl font-extrabold text-petron-blue uppercase tracking-tight">STAFF MANAGEMENT</h4>
    <p class="text-sm text-gray-500 font-medium">Create and manage staff accounts</p>
  </div>

    <div class="hidden md:flex items-center gap-3 text-gray-500 font-semibold bg-gray-50 px-4 py-2 rounded-full border border-gray-100 shadow-inner">
        <i class="fa-solid fa-calendar-day text-petron-red"></i>
        <span id="live-date" class="text-sm"></span>
    </div>
</header>

<div class="p-4 md:p-8 space-y-6">

  <?php if(isset($_GET['created'])): ?>
    <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 px-6 py-4 rounded-xl shadow flex justify-between items-center">
      <span class="font-bold">
        Account created successfully.
        <?php if (!empty($_GET['staff_uid'])): ?>
          ID: <?= htmlspecialchars((string)$_GET['staff_uid']) ?>
        <?php endif; ?>
      </span>
      <button onclick="this.parentElement.remove()" class="font-bold text-lg">&times;</button>
    </div>
  <?php endif; ?>

  <?php if(isset($_GET['deleted'])): ?>
    <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 px-6 py-4 rounded-xl shadow flex justify-between items-center">
      <span class="font-bold">Account deleted.</span>
      <button onclick="this.parentElement.remove()" class="font-bold text-lg">&times;</button>
    </div>
  <?php endif; ?>

  <?php if(isset($_GET['denied'])): ?>
    <div class="bg-red-100 border border-red-300 text-red-800 px-6 py-4 rounded-xl shadow flex justify-between items-center">
      <span class="font-bold"> Action denied.</span>
      <button onclick="this.parentElement.remove()" class="font-bold text-lg">&times;</button>
    </div>
  <?php endif; ?>

  <?php if($error): ?>
    <div class="bg-red-100 border border-red-300 text-red-800 px-6 py-4 rounded-xl shadow flex justify-between items-center">
      <span class="font-bold"> <?= htmlspecialchars($error) ?></span>
      <button onclick="this.parentElement.remove()" class="font-bold text-lg">&times;</button>
    </div>
  <?php endif; ?>

  <!-- Create Staff Form -->
  <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
      <i class="fa-solid fa-user-plus text-petron-blue"></i>
      <h5 class="font-bold text-gray-800">Create New Staff Account</h5>
    </div>

    <div class="p-6">
      <form method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4">
        <div class="md:col-span-4">
          <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Username</label>
          <input
            type="text"
            name="username"
            class="w-full rounded-xl border border-gray-300 px-4 py-3 font-semibold focus:outline-none focus:ring-2 focus:ring-petron-blue/30"
            required
          >
        </div>

        <div class="md:col-span-4">
          <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Password</label>
          <input
            type="password"
            name="password"
            class="w-full rounded-xl border border-gray-300 px-4 py-3 font-semibold focus:outline-none focus:ring-2 focus:ring-petron-blue/30"
            required
          >
          <p class="text-xs text-gray-400 mt-2 italic">Min 6 characters</p>
        </div>

        <input type="hidden" name="role" value="staff">

        <div class="md:col-span-2 flex items-end">
          <button
            type="submit"
            name="create_staff"
            class="w-full rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold px-5 py-3 shadow flex items-center justify-center gap-2"
          >
            <i class="fa-solid fa-plus"></i>
            Create
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Staff List -->
  <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
      <i class="fa-solid fa-users text-petron-red"></i>
      <h5 class="font-bold text-gray-800">Staff Accounts</h5>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left min-w-175">
        <thead class="bg-gray-50 text-xs uppercase font-extrabold text-gray-600 border-b">
          <tr>
            <th class="px-6 py-4 w-32">Staff ID</th>
            <th class="px-6 py-4">Username</th>
            <th class="px-6 py-4">Role</th>
            <th class="px-6 py-4 w-40 text-center">Action</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-100">
          <?php if(count($staffUsers) > 0): ?>
            <?php foreach($staffUsers as $s): ?>
              <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                  <span class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 font-black text-xs tracking-wide text-gray-700">
                    <i class="fa-solid fa-id-card text-petron-blue"></i>
                    <?= htmlspecialchars((string)($s['staff_uid'] ?? ('STF-' . str_pad((string)((int)$s['id']), 6, '0', STR_PAD_LEFT)))) ?>
                  </span>
                </td>
                <td class="px-6 py-4 font-bold text-gray-900">
                  <?= htmlspecialchars($s['username']) ?>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-extrabold">
                    <?= htmlspecialchars($s['role']) ?>
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <a
                    href="app.php?page=staff_manage&delete_staff_id=<?= (int)$s['id'] ?>"
                    onclick="return confirm('Delete this account?')"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-extrabold px-5 py-2 shadow"
                  >
                    <i class="fa-solid fa-trash"></i>
                    Delete
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic">
                No staff accounts yet.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
