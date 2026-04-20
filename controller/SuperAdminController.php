<?php

require_once __DIR__ . '/../includes/guards/superadmin_guard.php';
require_once __DIR__ . '/../model/Dashboard.php';

class SuperAdminController {

    public function dashboard() {
        $dashboard = new Dashboard();

        return [
            'salesToday' => $dashboard->getTotalSalesTodayAllBranches(),
            'litersToday' => $dashboard->getLitersTodayAllBranches(),
            'fuelCount' => $dashboard->getFuelCountAllBranches(),
            'lowStock' => $dashboard->getLowStockAllBranches()
        ];
    }
}