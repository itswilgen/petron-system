<?php
require_once __DIR__ . '/../model/Report.php';

class ReportController {

    public function index(){

        $report = new Report();

        $data = [
            'dailyRows' => $report->getDailyReport(),
            'dailyTotal' => $report->getDailyTotal(),
            'monthlyRows' => $report->getMonthlyReport(),
            'monthlyTotal' => $report->getMonthlyTotal(),
            'inventoryRows' => $report->getInventoryReport()
        ];

        return $data;
    }
}
