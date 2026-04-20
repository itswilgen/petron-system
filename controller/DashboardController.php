<?php
require_once __DIR__ . '/../model/Dashboard.php';

class DashboardController {

    public function getStats() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $branchId = (int)($_SESSION['branch_id'] ?? 0);
        if ($branchId <= 0) {
            return [
                'salesToday'  => 0,
                'litersToday' => 0,
                'totalFuels'  => 0,
                'lowStock'    => [],
                'fuelLevels'  => [],
                'salesTrend'  => []
            ];
        }

        $dashboard = new Dashboard();

        $salesToday  = $dashboard->getSalesToday($branchId);
        $litersToday = $dashboard->getLitersToday($branchId);
        $totalFuels  = $dashboard->getFuelCount($branchId);
        $lowStock    = $dashboard->getLowStock($branchId);
        $fuelLevels  = $dashboard->getFuelLevels($branchId);
        $salesTrend  = $dashboard->getSalesTrend($branchId);

        return [
            'salesToday'  => $salesToday,
            'litersToday' => $litersToday,
            'totalFuels'  => $totalFuels,
            'lowStock'    => $lowStock,
            'fuelLevels'  => $fuelLevels,
            'salesTrend'  => $salesTrend
        ];
    }
}
