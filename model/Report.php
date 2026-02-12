<?php
require_once __DIR__ . '/../database/Database.php';

class Report {

    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getDailyReport(){
        $stmt = $this->conn->prepare("
            SELECT f.fuel_name,
                   SUM(s.liters) as liters_sold,
                   SUM(s.total_price) as total_sales
            FROM sales s
            JOIN fuels f ON s.fuel_id = f.id
            WHERE DATE(s.sale_date)=CURDATE()
            GROUP BY s.fuel_id
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDailyTotal(){
        $stmt = $this->conn->prepare("
            SELECT SUM(total_price) as grand_total 
            FROM sales 
            WHERE DATE(sale_date)=CURDATE()
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['grand_total'] ?? 0;
    }

    public function getMonthlyReport(){
        $stmt = $this->conn->prepare("
            SELECT DATE(s.sale_date) as sale_day,
                   SUM(s.total_price) as total_sales,
                   SUM(s.liters) as liters_sold
            FROM sales s
            WHERE MONTH(s.sale_date)=MONTH(CURDATE()) 
              AND YEAR(s.sale_date)=YEAR(CURDATE())
            GROUP BY DATE(s.sale_date)
            ORDER BY sale_day ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMonthlyTotal(){
        $stmt = $this->conn->prepare("
            SELECT SUM(total_price) as monthly_total 
            FROM sales 
            WHERE MONTH(sale_date)=MONTH(CURDATE()) 
              AND YEAR(sale_date)=YEAR(CURDATE())
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['monthly_total'] ?? 0;
    }

    public function getInventoryReport(){
        $stmt = $this->conn->prepare("SELECT * FROM fuels ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
