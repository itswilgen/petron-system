<?php

ob_start(); 
if (session_status() === PHP_SESSION_NONE) session_start();

$role = $_SESSION['role'] ?? '';
$page = $_GET['page'] ?? 'dashboard';
$allowedPages = ['dashboard', 'pos', 'reports', 'sales_history'];

if ($role !== 'staff' && $role !== 'admin') {
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
    <title>Petron Command Center</title>

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
            class="fixed lg:static inset-y-0 left-0 z-40 w-64 bg-petron-blue text-white">

        <div class="p-6 text-center">
            <div class=" p-2 rounded-lg mb-2">
                <img src="../assets/img/logo3.png" alt="Petron" class="h-12 w-auto mx-auto object-contain">
            </div>
            <p class="text-[10px] uppercase tracking-tighter opacity-70">Management System</p>
        </div>
        
            
        <nav class="mt-4 px-4 space-y-2">
            <?php
            $currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

            function getActiveClass($pageName, $currentPage) {
                if ($pageName === $currentPage) {

                    return "bg-white/20 border-l-4 border-petron-red text-white shadow-md";
                }
                return "hover:bg-white/10 text-white/80 hover:text-white";
            }
            ?>

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

            <div class="pt-4 mt-4 border-t border-white/10">
                <a href="../auth/logout.php" class="flex items-center px-4 py-3 text-red-300 hover:text-red-200 font-bold transition-all">
                    <i class="fa-solid fa-right-from-bracket w-6"></i>
                    <span class="ml-2">Logout</span>
                </a>
            </div>
        </nav>
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
<script src="/petron_system/public/assets/js/app_staff.js"></script>

</body>
</html>
