<?php
require_once __DIR__ . '/../../../includes/guards/superadmin_guard.php';
require_once __DIR__ . '/../../../controller/SuperAdminController.php';

$controller = new SuperAdminController();
$error = $controller->createAdmin();
$controller->deleteAdmin();

$branches = $controller->getBranches();
$admins = $controller->getAdmins();
?>

<header class="bg-white py-4 px-4 md:px-8 shadow-sm flex justify-between items-center">
    <div>
        <h4 class="text-xl font-extrabold text-petron-blue uppercase tracking-tight">ADMIN ACCOUNT CONTROL</h4>
        <p class="text-sm text-gray-500 font-medium">Manage admin users for every branch</p>
    </div>
    <div class="hidden md:flex items-center gap-3 text-gray-500 font-semibold bg-gray-50 px-4 py-2 rounded-full border border-gray-100 shadow-inner">
        <i class="fa-solid fa-calendar-day text-petron-red"></i>
        <span id="live-date" class="text-sm"></span>
    </div>
</header>

<div class="p-4 md:p-8 space-y-6">
    <?php if (isset($_GET['created'])): ?>
        <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 px-6 py-4 rounded-xl shadow flex justify-between items-center">
            <span class="font-bold">Admin account created successfully.</span>
            <button onclick="this.parentElement.remove()" class="font-bold text-lg">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 px-6 py-4 rounded-xl shadow flex justify-between items-center">
            <span class="font-bold">Admin account deleted.</span>
            <button onclick="this.parentElement.remove()" class="font-bold text-lg">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['denied'])): ?>
        <div class="bg-red-100 border border-red-300 text-red-800 px-6 py-4 rounded-xl shadow flex justify-between items-center">
            <span class="font-bold">Action denied.</span>
            <button onclick="this.parentElement.remove()" class="font-bold text-lg">&times;</button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-300 text-red-800 px-6 py-4 rounded-xl shadow flex justify-between items-center">
            <span class="font-bold"><?= htmlspecialchars($error) ?></span>
            <button onclick="this.parentElement.remove()" class="font-bold text-lg">&times;</button>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <i class="fa-solid fa-user-shield text-petron-blue"></i>
            <h5 class="font-bold text-gray-800">Create Admin Account</h5>
        </div>
        <div class="p-6">
            <form method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Username</label>
                    <input
                        type="text"
                        name="username"
                        required
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 font-semibold focus:outline-none focus:ring-2 focus:ring-petron-blue/30"
                    >
                </div>

                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Password</label>
                    <input
                        type="password"
                        name="password"
                        required
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 font-semibold focus:outline-none focus:ring-2 focus:ring-petron-blue/30"
                    >
                    <p class="text-xs text-gray-400 mt-2 italic">Min 6 characters</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Branch</label>
                    <select
                        name="branch_id"
                        required
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 font-semibold focus:outline-none focus:ring-2 focus:ring-petron-blue/30"
                    >
                        <option value="">Select branch</option>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?= (int)$branch['id'] ?>"><?= htmlspecialchars($branch['branch_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-2 flex items-end">
                    <button
                        type="submit"
                        name="create_admin"
                        class="w-full rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold px-5 py-3 shadow flex items-center justify-center gap-2"
                    >
                        <i class="fa-solid fa-plus"></i>
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <i class="fa-solid fa-users-gear text-petron-red"></i>
            <h5 class="font-bold text-gray-800">Admin Accounts by Branch</h5>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left min-w-175">
                <thead class="bg-gray-50 text-xs uppercase font-extrabold text-gray-600 border-b">
                    <tr>
                        <th class="px-6 py-4 w-24">ID</th>
                        <th class="px-6 py-4">Username</th>
                        <th class="px-6 py-4">Branch</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4 w-40 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (count($admins) > 0): ?>
                        <?php foreach ($admins as $admin): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-extrabold text-gray-700"><?= (int)$admin['id'] ?></td>
                                <td class="px-6 py-4 font-bold text-gray-900"><?= htmlspecialchars($admin['username']) ?></td>
                                <td class="px-6 py-4 text-gray-700"><?= htmlspecialchars($admin['branch_name']) ?></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-extrabold">
                                        Admin
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a
                                        href="app.php?page=admin_accounts&delete_admin_id=<?= (int)$admin['id'] ?>"
                                        onclick="return confirm('Delete this admin account?')"
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
                            <td colspan="5" class="px-6 py-10 text-center text-gray-400 italic">No admin accounts found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

