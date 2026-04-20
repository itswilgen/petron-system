<?php
require_once __DIR__ . '/../database/Database.php';

class Sale {

    private $conn;
    private $table = "sales";

    public function __construct(?PDO $conn = null) {
        if ($conn instanceof PDO) {
            $this->conn = $conn;
            return;
        }

        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getConnection() {
        return $this->conn;
    }

    // Create sale
    public function createSale($fuel_id, $liters, $price, $total, $branch_id){

        $query = "INSERT INTO {$this->table} 
                  (fuel_id, liters, price, total_price, branch_id) 
                  VALUES (?,?,?,?,?)";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            $fuel_id,
            $liters,
            $price,
            $total,
            $branch_id
        ]);
    }

    // Count sales for pagination
    public function countFilteredSales($branch_id, $search = '', $dateFrom = '', $dateTo = '') {

        $sql = "
            SELECT COUNT(*)
            FROM sales s
            JOIN fuels f ON s.fuel_id = f.id
            WHERE s.branch_id = ?
        ";

        $params = [$branch_id];

        if ($search !== '') {
            $sql .= " AND f.fuel_name LIKE ?";
            $params[] = "%{$search}%";
        }

        if ($dateFrom !== '') {
            $sql .= " AND DATE(s.sale_date) >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo !== '') {
            $sql .= " AND DATE(s.sale_date) <= ?";
            $params[] = $dateTo;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn();
    }

    // Get sales with pagination
    public function getFilteredSales($branch_id, $search = '', $dateFrom = '', $dateTo = '', $limit = 10, $offset = 0) {

        $limit = (int)$limit;
        $offset = (int)$offset;

        $sql = "
            SELECT s.id, s.liters, s.price, s.total_price, s.sale_date, f.fuel_name
            FROM sales s
            JOIN fuels f ON s.fuel_id = f.id
            WHERE s.branch_id = ?
        ";

        $params = [$branch_id];

        if ($search !== '') {
            $sql .= " AND f.fuel_name LIKE ?";
            $params[] = "%{$search}%";
        }

        if ($dateFrom !== '') {
            $sql .= " AND DATE(s.sale_date) >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo !== '') {
            $sql .= " AND DATE(s.sale_date) <= ?";
            $params[] = $dateTo;
        }

        $sql .= " ORDER BY s.sale_date DESC LIMIT $limit OFFSET $offset";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
?>
