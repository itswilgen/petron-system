<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth_roles.php';

$role = $_SESSION['role'] ?? '';

if (!canAccessAdminArea($role)) {
    header("Location: /public/auth/login.php");
    exit;
}
