<?php

ob_start(); 
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../includes/auth_roles.php';

$role = $_SESSION['role'] ?? '';
$page = $_GET['page'] ?? 'dashboard';
$allowedPages = ['dashboard', 'pos', 'reports', 'sales_history', 'policies'];

if (!canAccessStaffArea($role)) {
    header("Location: /public/auth/login.php");
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
    <title>Petron Command Center</title>

    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="/public/assets/css/output.css?v=<?= filemtime(__DIR__ . '/../assets/css/output.css') ?>">
    <link rel="stylesheet" href="/public/assets/css/browser-compat.css?v=<?= filemtime(__DIR__ . '/../assets/css/browser-compat.css') ?>">
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

            function getActiveClass($pageName, $currentPage) {
                if ($pageName === $currentPage) {

                    return "bg-white/20 border-l-4 border-petron-red text-white shadow-md";
                }
                return "hover:bg-white/10 text-white/80 hover:text-white";
            }
            ?>

            <div class="space-y-2">
                <a href="app.php?page=dashboard" class="flex items-center px-4 py-3 rounded-lg transition-all duration-300 <?= getActiveClass('dashboard', $currentPage) ?>">
                    <i class="fa-solid fa-gauge-high w-6"></i>
                    <span class="ml-2 font-semibold">Dashboard</span>
                </a>

                <a href="app.php?page=pos" class="flex items-center px-4 py-3 rounded-lg transition-all duration-300 <?= getActiveClass('pos', $currentPage) ?>">
                    <i class="fa-solid fa-gas-pump w-6"></i>
                    <span class="ml-2">POS</span>
                </a>

                <a href="app.php?page=reports" class="flex items-center px-4 py-3 rounded-lg transition-all duration-300 <?= getActiveClass('reports', $currentPage) ?>">
                    <i class="fa-solid fa-chart-line w-6"></i>
                    <span class="ml-2">Sales Reports</span>
                </a>

                <a href="app.php?page=sales_history" class="flex items-center px-4 py-3 rounded-lg transition-all duration-300 <?= getActiveClass('sales_history', $currentPage) ?>">
                    <i class="fa-solid fa-receipt w-6"></i>
                    <span class="ml-2">Sales History</span>
                </a>

                <a href="app.php?page=policies" class="flex items-center px-4 py-3 rounded-lg transition-all duration-300 <?= getActiveClass('policies', $currentPage) ?>">
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
                <i class="fa-solid fa-bolt text-petron-red"></i>
                Pump Floor Console
            </div>
            <p class="mt-1 text-white/75">Staff workspace for real-time sales, POS actions, and service flow.</p>
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


    <div id="sidebar-backdrop"
        class="fixed inset-0 z-30 hidden bg-black/40 backdrop-blur-sm lg:hidden"></div>

        <main class="flex-1 w-full min-h-screen overflow-hidden">
        <!-- scroll only content -->
        <div class="h-full overflow-y-auto pt-16 lg:pt-0">
            <?php include __DIR__ . "/pages/{$page}.php"; ?>
        </div>
        </main>

</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
