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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    @keyframes bgMotion {
    0% {
        transform: scale(1) translate(0px,0px);
    }
    50% {
        transform: scale(1.08) translate(-20px,-10px);
    }
    100% {
        transform: scale(1) translate(0px,0px);
    }
    }
</style>
</head>

<body class="min-h-screen">

  <!-- Background -->
    <div class="fixed inset-0 -z-10 overflow-hidden">

    <div
        class="absolute inset-0 bg-cover bg-center"
        style="
        background-image:url('../../public/assets/img/bg2.png');
        animation: bgMotion 25s ease-in-out infinite;
        ">
    </div>

    </div>
    <!-- Dark overlay -->
    <div class="absolute inset-0 bg-black/30"></div>

    <!-- Center container -->
    <div class="relative z-10 min-h-screen flex items-center justify-center p-4">

      <!-- Main Card -->
      <div class="w-full max-w-5xl rounded-[28px] overflow-hidden shadow-2xl
                  bg-white/20  border border-white/20">

        <div class="grid md:grid-cols-2">

          <!-- LEFT BLUE PANEL -->
          <div class="flex bg-petron-blue p-10 text-white items-center opacity-90">
            <div>
              <h1 class="text-5xl font-black tracking-wide">PETRON</h1>
              <p class="mt-3 text-white/85 text-lg">
                Inventory & Sales Management System
              </p>
            </div>
          </div>

          <!-- RIGHT FORM PANEL -->
          <div class="bg-white/95 p-8 sm:p-10">
            <div class="text-center">
              <img src="../../public/assets/img/logo3.png"
                   alt="Petron Logo"
                   class="mx-auto h-20 w-auto drop-shadow-sm" />

              <h2 class="mt-4 text-2xl font-extrabold text-petron-blue">Sign In</h2>
              <p class="mt-1 text-sm text-gray-500">Access the Inventory Control Panel</p>
            </div>

            <?php if ($error): ?>
              <div class="mt-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <div class="text-sm font-semibold"><?= htmlspecialchars($error) ?></div>
              </div>
            <?php endif; ?>

            <form method="POST" action="" class="mt-6 space-y-4">

              <!-- ID Number -->
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">ID Number</label>
                <div class="flex items-center rounded-lg border border-gray-300 bg-gray-200 overflow-hidden">
                  <div class="w-12 h-12 flex items-center justify-center text-gray-700">
                    <i class="fa-solid fa-id-card"></i>
                  </div>
                  <input
                    type="text"
                    name="id_number"
                    required
                    placeholder="Enter ID number (e.g., ADM-000037, STF-000044, or 37)"
                    class="w-full bg-gray-200 px-3 py-3 text-sm border-0 outline-none focus:ring-0 focus:outline-none placeholder:text-gray-500"    
                  />
                </div>
                <p class="mt-2 text-xs text-gray-500">Use your account ID number (Admin or Staff), not username.</p>
              </div>


              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Password</label>
                <div class="flex items-center rounded-lg border border-gray-300 bg-gray-200 overflow-hidden">
                  <div class="w-12 h-12 flex items-center justify-center text-gray-700">
                    <i class="fa-solid fa-lock"></i>
                  </div>            
                  <input
                    type="password"
                    name="password"
                    required
                    placeholder="••••••••"
                    class="w-full bg-gray-200 px-3 py-3 text-sm border-0 outline-none focus:ring-0 focus:outline-none placeholder:text-gray-500"
                  />
                </div>
              </div>
              <button
                type="submit"
                class="mt-2 w-full rounded-lg bg-petron-red py-3 font-extrabold text-white
                       shadow-lg shadow-red-500/20 hover:opacity-95 active:scale-[0.99]
                       flex items-center justify-center gap-3">LOG IN <i class="fa-solid fa-right-to-bracket"></i>
              </button>
            </form>

            <div class="mt-6 text-center text-xs text-gray-500">
              © 2026 Petron Corporation
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>

</body>
</html>
