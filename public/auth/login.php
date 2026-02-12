<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);


require_once   '../../src/UserController.php';
$controller = new UserController();
$error = $controller->login();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petron Login - Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/assets/css/login.css">
</head>
<body class="login-page">

<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card login-card shadow-lg border-0">
        <div class="row g-0">
            <div class="col-md-6 d-none d-md-block login-overlay">
                <div class="overlay-content text-white p-5 d-flex flex-column justify-content-center h-100">
                    <h1 class="display-5 fw-bold">PETRON</h1>
                    <p class="lead">Inventory & Sales Management System</p>
                </div>
            </div>
            
            <div class="col-md-6 p-4 p-lg-5 ">
                <div class="text-center mb-4">
                    <img src="../../public/assets/img/logo3.png"  alt="Petron Logo" 
                         class="img-fluid mb-4 logo-main" 
                         style="max-height: 150px; width: auto; filter: drop-shadow(0px 4px 6px rgba(0,0,0,0.1));">
                    
                    <h2 class="fw-bold text-petron-blue mb-1">Sign In</h2>
                    <p class="text-muted small">Access the Inventory Control Panel</p>
                </div>

                <?php if($error): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i>
                        <div><?php echo $error; ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user text-muted"></i></span>
                            <input type="text" name="username" class="form-control bg-light border-start-0 custom-input" placeholder="Enter username" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                            <input type="password" name="password" class="form-control bg-light border-start-0 custom-input" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-petron-red w-100 fw-bold py-2 shadow-sm">
                        LOG IN <i class="fa-solid fa-right-to-bracket ms-2"></i>
                    </button>
                </form>

                <div class="mt-4 text-center">
                    <small class="text-muted">© 2026 Petron Corporation</small>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
