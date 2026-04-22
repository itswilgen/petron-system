<?php
// Enable error reporting for debugging
echo file_exists('/Applications/XAMPP/xamppfiles/htdocs/petron_system/controller/DashboardController.php')
    ? 'FOUND'
    : 'NOT FOUND';
exit;

//error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);