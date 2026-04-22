<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../controller/UserController.php';
$controller = new UserController();
$error = $controller->login();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Petron Login - Inventory System</title>

  <link rel="stylesheet" href="../../public/assets/css/output.css?v=<?= filemtime(__DIR__ . '/../../public/assets/css/output.css') ?>">
  <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="min-h-screen overflow-hidden">

  <!-- Fullscreen Carousel Background -->
  <div id="default-carousel" class="fixed inset-0 z-0 w-full h-screen" data-carousel="slide">
    <div class="relative w-full h-full overflow-hidden">

      <div class="hidden duration-700 ease-in-out" data-carousel-item="active">
        <img src="../../public/assets/img/bg2.png" class="absolute inset-0 w-full h-full object-cover" alt="">
      </div>

      <div class="hidden duration-700 ease-in-out" data-carousel-item>
        <img src="../../public/assets/img/bg3.png" class="absolute inset-0 w-full h-full object-cover" alt="">
      </div>

      <div class="hidden duration-700 ease-in-out" data-carousel-item>
        <img src="../../public/assets/img/bg4.png" class="absolute inset-0 w-full h-full object-cover" alt="">
      </div>

      <div class="hidden duration-700 ease-in-out" data-carousel-item>
        <img src="../../public/assets/img/bg5.png" class="absolute inset-0 w-full h-full object-cover" alt="">
      </div>

      <div class="hidden duration-700 ease-in-out" data-carousel-item>
        <img src="../../public/assets/img/bg6.png" class="absolute inset-0 w-full h-full object-cover" alt="">
      </div>

      <!-- Dark overlay -->
      <div class="absolute inset-0 bg-black/45"></div>
    </div>
  </div>

  <!-- Floating Login Card -->
  <div class="relative z-10 min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md rounded-3xl bg-white/90 backdrop-blur-md shadow-2xl border border-white/30 p-8 sm:p-10">

      <div class="text-center">
        <img src="../../public/assets/img/logo3.png" alt="Petron Logo" class="mx-auto h-20 w-auto">
        <h2 class="mt-4 text-3xl font-extrabold text-petron-blue">Sign In</h2>
        <p class="mt-2 text-gray-600">Access the Inventory Control Panel</p>
      </div>

      <?php if ($error): ?>
        <div class="mt-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
          <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
          <div class="text-sm font-semibold"><?= htmlspecialchars($error) ?></div>
        </div>
      <?php endif; ?>

      <form method="POST" action="" class="mt-6 space-y-5">

        <div>
          <label class="block text-sm font-bold text-gray-700 mb-2">Username</label>
          <div class="flex items-center rounded-xl bg-gray-100 border border-gray-200 overflow-hidden focus-within:ring-2 focus-within:ring-petron-blue/30">
            <div class="w-12 flex justify-center text-gray-500">
              <i class="fa-solid fa-user"></i>
            </div>
            <input
              type="text"
              name="username"
              required
              placeholder="Enter username"
              class="w-full bg-transparent px-3 py-3 text-sm border-0 outline-none focus:ring-0 focus:outline-none placeholder:text-gray-500"
            >
          </div>
        </div>

        <div>
          <label class="block text-sm font-bold text-gray-700 mb-2">Password</label>
          <div class="flex items-center rounded-xl bg-gray-100 border border-gray-200 overflow-hidden focus-within:ring-2 focus-within:ring-petron-blue/30">
            <div class="w-12 flex justify-center text-gray-500">
              <i class="fa-solid fa-lock"></i>
            </div>
            <input
              type="password"
              name="password"
              required
              placeholder="••••••••"
              class="w-full bg-transparent px-3 py-3 text-sm border-0 outline-none focus:ring-0 focus:outline-none placeholder:text-gray-500"
            >
          </div>
        </div>

        <button
          type="submit"
          class="w-full rounded-xl bg-petron-red py-3 text-white font-black shadow-lg shadow-red-500/20 hover:opacity-95 active:scale-[0.99] flex items-center justify-center gap-2"
        >
          LOG IN
          <i class="fa-solid fa-right-to-bracket"></i>
        </button>
      </form>

      <div class="mt-6 text-center text-xs text-gray-500">
        © 2026 Petron Corporation
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</body>
</html>