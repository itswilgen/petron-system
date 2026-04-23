<?php

ob_start(); 
if (session_status() === PHP_SESSION_NONE) session_start();

define('ROOT_PATH', dirname(__DIR__, 2));
require_once __DIR__ . '/../../includes/auth_roles.php';

$role = $_SESSION['role'] ?? '';
$page = $_GET['page'] ?? 'dashboard';
$allowedPages = ['dashboard', 'inventory', 'delivery', 'delivery_history', 'staff_manage'];

if (!canAccessAdminArea($role)) {
    header("Location: /petron_system/public/auth/login.php");
    exit;
}

if (!in_array($page, $allowedPages, true)) {
    $page = 'dashboard';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petron Inventory & Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="/petron_system/public/assets/css/output.css?v=<?= filemtime(__DIR__ . '/../assets/css/output.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-width { width: 280px; }
    </style>
</head>

<body class="bg-[#f8f9fc] antialiased">

<div class="flex h-screen overflow-hidden">


    <aside id="sidebar"
            class="fixed lg:static inset-y-0 left-0 z-40 w-64 bg-petron-blue text-white flex flex-col">

        <div class="p-6 text-center">
            <div class=" p-2 rounded-lg mb-2">
                <img src="../assets/img/logo3.png" alt="Petron" class="h-12 w-auto mx-auto object-contain">
            </div>
            <p class="text-[10px] uppercase tracking-tighter opacity-70">Management System</p>
        </div>
        
            
        <nav class="mt-4 px-4 flex-1 overflow-y-auto flex flex-col">
            <?php
            $currentPage = $page;

            // Helper function to apply highlight classes
            function getActiveClass($pageName, $currentPage) {
                if ($pageName === $currentPage) {
                    // Active Styles: White highlight with the Petron Red border
                    return "bg-white/20 border-l-4 border-petron-red text-white shadow-md";
                }
                // Inactive Styles
                return "hover:bg-white/10 text-white/80 hover:text-white";
            }
            ?>

            <div class="space-y-2">
                <a href="app.php?page=dashboard" class="flex items-center px-4 py-3 rounded-lg transition-all duration-300 <?= getActiveClass('dashboard', $currentPage) ?>">
                    <i class="fa-solid fa-gauge-high w-6"></i>
                    <span class="ml-2 font-semibold">Dashboard</span>
                </a>

                <a href="app.php?page=inventory" class="flex items-center px-4 py-3 rounded-lg transition-all duration-300 <?= getActiveClass('inventory', $currentPage) ?>">
                    <i class="fa-solid fa-boxes-stacked w-6"></i>
                    <span class="ml-2">Inventory</span>
                </a>

                <a href="app.php?page=delivery" class="flex items-center px-4 py-3 rounded-lg transition-all duration-300 <?= getActiveClass('delivery', $currentPage) ?>">
                    <i class="fa-solid fa-truck-fast w-6"></i>
                    <span class="ml-2">Delivery</span>
                </a>

                <a href="app.php?page=delivery_history" class="flex items-center px-4 py-3 rounded-lg transition-all duration-300 <?= getActiveClass('delivery_history', $currentPage) ?>">
                    <i class="fa-solid fa-clock-rotate-left w-6"></i>
                    <span class="ml-2">Delivery History</span>
                </a>

                <a href="app.php?page=staff_manage" class="flex items-center px-4 py-3 rounded-lg transition-all duration-300 <?= getActiveClass('staff_manage', $currentPage) ?>">
                    <i class="fa-solid fa-users w-6"></i>
                    <span class="ml-2">Staff Management</span>
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
                <i class="fa-solid fa-compass-drafting text-petron-red"></i>
                Branch Control Desk
            </div>
            <p class="mt-1 text-white/75">Admin tools for branch operations, inventory, and team oversight.</p>
        </div>
    </aside>


    <div class="lg:hidden bg-petron-blue p-4 flex justify-between items-center shadow-lg w-full fixed top-0 left-0 z-30">
        <img src="../assets/img/logo3.png" alt="Petron" class="h-8 w-auto">
        <button id="sidebarToggle" type="button" class="text-white text-2xl">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>


    <div id="sidebar-backdrop"
        class="fixed inset-0 z-30 hidden bg-black/40 backdrop-blur-sm lg:hidden"></div>

        <main class="flex-1 w-full min-h-screen overflow-hidden">
        <!-- scroll only content -->
        <div class="h-full overflow-y-auto pt-16 lg:pt-0">
            <?php include __DIR__ . "/pages/{$page}.php"; ?>
        </div>
        </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
<script src="/petron_system/public/assets/js/app_admin.js"></script>
</body>
</html>
