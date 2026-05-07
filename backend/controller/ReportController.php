<?php
require_once __DIR__ . '/../model/Report.php';

class ReportController {

    public function index(){
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $branchId = (int)($_SESSION['branch_id'] ?? 0);
        $report = new Report();

        $data = [
            'dailyRows' => $report->getDailyReport($branchId),
            'dailyTotal' => $report->getDailyTotal($branchId),
            'monthlyRows' => $report->getMonthlyReport($branchId),
            'monthlyTotal' => $report->getMonthlyTotal($branchId),
            'inventoryRows' => $report->getInventoryReport($branchId)
        ];

        return $data;
    }
}
