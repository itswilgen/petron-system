<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? '';

if ($role !== 'admin') {
    header("Location: /petron_system/public/auth/login.php");
    exit;
}
