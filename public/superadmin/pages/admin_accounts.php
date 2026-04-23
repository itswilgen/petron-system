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
            <span class="font-bold">
                Admin account created successfully.
                <?php if (!empty($_GET['admin_uid'])): ?>
                    ID: <?= htmlspecialchars((string)$_GET['admin_uid']) ?>
                <?php endif; ?>
            </span>
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
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-users-gear text-petron-red"></i>
                <h5 class="font-bold text-gray-800">Admin Accounts by Branch</h5>
            </div>
            <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-xs font-extrabold text-gray-700">
                <i class="fa-solid fa-hashtag text-gray-500"></i>
                <?= count($admins) ?> total
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left min-w-175">
                <thead class="bg-gray-50 text-xs uppercase font-extrabold text-gray-500 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 w-32">Admin ID</th>
                        <th class="px-6 py-4">Username</th>
                        <th class="px-6 py-4">Branch</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4 w-40 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (count($admins) > 0): ?>
                        <?php foreach ($admins as $admin): ?>
                            <?php $adminUid = (string)($admin['admin_uid'] ?? ('ADM-' . str_pad((string)((int)$admin['id']), 6, '0', STR_PAD_LEFT))); ?>
                            <tr class="odd:bg-white even:bg-gray-50/60 hover:bg-blue-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 font-black text-xs tracking-wide text-gray-700">
                                        <i class="fa-solid fa-fingerprint text-petron-blue"></i>
                                        <?= htmlspecialchars($adminUid) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 text-petron-blue">
                                            <i class="fa-solid fa-user-shield text-sm"></i>
                                        </span>
                                        <div class="leading-tight">
                                            <p class="font-bold text-gray-900"><?= htmlspecialchars($admin['username']) ?></p>
                                            <p class="text-xs text-gray-500">Administrator account</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-lg bg-sky-50 px-3 py-1.5 text-sm font-bold text-sky-700">
                                        <i class="fa-solid fa-building"></i>
                                        <?= htmlspecialchars($admin['branch_name']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-100 text-amber-700 text-xs font-extrabold uppercase tracking-wide">
                                        <span class="inline-block h-2 w-2 rounded-full bg-amber-500"></span>
                                        Admin Access
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a
                                        href="app.php?page=admin_accounts&delete_admin_id=<?= (int)$admin['id'] ?>"
                                        onclick="return confirm('Delete this admin account?')"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2 font-extrabold text-red-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-red-600 hover:text-white hover:border-red-600"
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-2 text-gray-400">
                                    <i class="fa-solid fa-users-slash text-xl"></i>
                                    <p class="italic">No admin accounts found.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
