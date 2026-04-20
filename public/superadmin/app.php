<?php
require_once __DIR__ . '/../../includes/guards/superadmin_guard.php';

$page = $_GET['page'] ?? 'dashboard';

$allowedPages = ['dashboard'];

if (!in_array($page, $allowedPages, true)) {
    $page = 'dashboard';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Panel</title>
    <link rel="stylesheet" href="/petron_system/public/assets/css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <aside class="w-64 bg-petron-blue text-white p-6">
            <h2 class="text-xl font-bold mb-6">Super Admin</h2>

            <nav class="space-y-3">
                <a href="app.php?page=dashboard" class="block px-4 py-3 rounded-lg bg-white/10 hover:bg-white/20">
                    <i class="fa-solid fa-gauge-high mr-2"></i> Dashboard
                </a>

                <a href="/petron_system/public/auth/logout.php" class="block px-4 py-3 rounded-lg text-red-300 hover:text-red-200">
                    <i class="fa-solid fa-right-from-bracket mr-2"></i> Logout
                </a>
            </nav>
        </aside>

        <main class="flex-1">
            <?php include __DIR__ . "/pages/{$page}.php"; ?>
        </main>
    </div>
</body>
</html>