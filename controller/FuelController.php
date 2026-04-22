<?php
require_once __DIR__ . '/../model/Fuel.php';

class FuelController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $fuel = new Fuel();
        $branchId = (int)($_SESSION['branch_id'] ?? 0);
        return $fuel->getAllFuel($branchId);
    }
    
    // Handles form submission for updating fuel details
    public function update() {
        if (isset($_POST['update'])) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $id = (int)($_POST['id'] ?? 0);
            $price = (float)($_POST['price'] ?? 0);
            $status = trim((string)($_POST['status'] ?? ''));
            $branchId = (int)($_SESSION['branch_id'] ?? 0);
            $allowedStatuses = ['Available', 'Low Stock', 'Out of Stock'];

            if ($id <= 0 || $branchId <= 0 || $price < 0 || !in_array($status, $allowedStatuses, true)) {
                $_SESSION['error'] = "Invalid fuel update request.";
                return;
            }

            $fuel = new Fuel();
            $ok = $fuel->updateFuelAdminFields($id, $price, $status, $branchId);

            if ($ok) {
                $_SESSION['success'] = "Fuel updated successfully!";
                return;
            }

            $_SESSION['error'] = "Failed to update fuel details.";
        }
    }
}
?>
