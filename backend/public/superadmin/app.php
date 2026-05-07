<?php

ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/guards/superadmin_guard.php';

$page = $_GET['page'] ?? 'dashboard';
$allowedPages = ['dashboard', 'branch_operations', 'global_recent_sales', 'business_health', 'global_pricing', 'admin_accounts', 'policies'];

if (!in_array($page, $allowedPages, true)) {
    $page = 'dashboard';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petron Super Admin Panel</title>

    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="/public/assets/css/output.css?v=<?= filemtime(__DIR__ . '/../assets/css/output.css') ?>">
    <link rel="stylesheet" href="/public/assets/css/browser-compat.css?v=<?= filemtime(__DIR__ . '/../assets/css/browser-compat.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#f8f9fc] antialiased">

<div class="flex h-screen overflow-hidden">
    <aside id="sidebar" class="fixed lg:static inset-y-0 left-0 z-40 w-64 bg-petron-blue text-white -translate-x-full lg:translate-x-0 flex flex-col">
        <div class="p-6 text-center border-b border-white/10">
            <img src="../assets/img/logo3.png" alt="Petron" class="h-12 w-auto mx-auto object-contain mb-2">
            <p class="text-[10px] uppercase tracking-widest opacity-70">Super Admin Panel</p>
        </div>

        <?php
            $currentPage = $page;
            function getSuperAdminActiveClass($pageName, $currentPage) {
                if ($pageName === $currentPage) {
                    return "bg-white/20 border-l-4 border-petron-red text-white shadow-md";
                }
                return "hover:bg-white/10 text-white/80 hover:text-white";
            }
        ?>

        <nav class="mt-4 px-4 flex-1 overflow-y-auto flex flex-col">
            <div class="space-y-2">
                <a href="app.php?page=dashboard" class="flex items-center px-4 py-3 rounded-lg transition-all duration-300 <?= getSuperAdminActiveClass('dashboard', $currentPage) ?>">
                    <i class="fa-solid fa-chart-pie w-6"></i>
                    <span class="ml-2">Global Dashboard</span>
                </a>

                <a href="app.php?page=branch_operations" class="flex items-center px-4 py-3 rounded-lg transition-all duration-300 <?= getSuperAdminActiveClass('branch_operations', $currentPage) ?>">
                    <i class="fa-solid fa-network-wired w-6"></i>
                    <span class="ml-2">Branch Operations</span>
                </a>

                <a href="app.php?page=global_recent_sales" class="flex items-center px-4 py-3 rounded-lg transition-all duration-300 <?= getSuperAdminActiveClass('global_recent_sales', $currentPage) ?>">
                    <i class="fa-solid fa-receipt w-6"></i>
                    <span class="ml-2">Global Recent Sales</span>
                </a>

                <a href="app.php?page=business_health" class="flex items-center px-4 py-3 rounded-lg transition-all duration-300 <?= getSuperAdminActiveClass('business_health', $currentPage) ?>">
                    <i class="fa-solid fa-heart-pulse w-6"></i>
                    <span class="ml-2">Business Monitoring</span>
                </a>

                <a href="app.php?page=global_pricing" class="flex items-center px-4 py-3 rounded-lg transition-all duration-300 <?= getSuperAdminActiveClass('global_pricing', $currentPage) ?>">
                    <i class="fa-solid fa-tags w-6"></i>
                    <span class="ml-2">Global Pricing</span>
                </a>

                <a href="app.php?page=admin_accounts" class="flex items-center px-4 py-3 rounded-lg transition-all duration-300 <?= getSuperAdminActiveClass('admin_accounts', $currentPage) ?>">
                    <i class="fa-solid fa-user-shield w-6"></i>
                    <span class="ml-2">Admin Accounts</span>
                </a>

                <a href="app.php?page=policies" class="flex items-center px-4 py-3 rounded-lg transition-all duration-300 <?= getSuperAdminActiveClass('policies', $currentPage) ?>">
                    <i class="fa-solid fa-file-contract w-6"></i>
                    <span class="ml-2">Policies</span>
                </a>
            </div>

            <div class="pt-4 mt-4 border-t border-white/10 mt-auto">
                <a href="../auth/logout.php" class="flex items-center px-4 py-3 text-red-300 hover:text-red-200 font-bold transition-all">
                    <i class="fa-solid fa-right-from-bracket w-6"></i>
                    <span class="ml-2">Logout</span>
                </a>
            </div>
        </nav>

        <div class="mx-4 mb-4 mt-3 rounded-xl border border-white/15 bg-white/10 p-3 text-[11px] leading-5">
            <div class="flex items-center gap-2 font-extrabold text-white">
                <i class="fa-solid fa-satellite-dish text-petron-red"></i>
                Regional Command Node
            </div>
            <p class="mt-1 text-white/75">Super Admin operations control across all branches.</p>
            <p class="mt-3 border-t border-white/20 pt-3 text-[10px] leading-4 text-white/80">
                Matthew 16:18 - "And I tell you that you are Peter, and on this rock I will build my church, and the gates of Hades will not overcome it."
            </p>
        </div>
    </aside>

    <div class="lg:hidden bg-petron-blue p-4 flex justify-between items-center shadow-lg w-full fixed top-0 left-0 z-30">
        <img src="../assets/img/logo3.png" alt="Petron" class="h-8 w-auto">
        <button id="sidebarToggle" type="button" class="text-white text-2xl">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>

    <div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-black/40 backdrop-blur-sm lg:hidden"></div>

    <main class="flex-1 w-full min-h-screen overflow-hidden">
        <div class="h-full overflow-y-auto pt-16 lg:pt-0">
            <?php include __DIR__ . "/pages/{$page}.php"; ?>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
<script src="/public/assets/js/app_superadmin.js"></script>
</body>
</html>
