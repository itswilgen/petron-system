<?php
require_once __DIR__ . '/../database/Database.php';

class Delivery {
    private $conn;
    private $table = "deliveries";

    public function __construct(?PDO $conn = null) {
        if ($conn instanceof PDO) {
            $this->conn = $conn;
            return;
        }

        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getConnection() {
        return $this->conn;
    }

    // Add delivery record with branch
    public function addDelivery($fuel_id, $liters_added, $branch_id) {
        $stmt = $this->conn->prepare("
            INSERT INTO {$this->table} (fuel_id, liters_added, delivery_date, branch_id)
            VALUES (?, ?, NOW(), ?)
        ");
        return $stmt->execute([$fuel_id, $liters_added, $branch_id]);
    }

    // Optional full list by branch
    public function getAllDeliveries($branch_id) {
        $stmt = $this->conn->prepare("
            SELECT d.id, f.fuel_name, d.liters_added, d.delivery_date
            FROM deliveries d
            JOIN fuels f ON d.fuel_id = f.id
            WHERE d.branch_id = ?
            ORDER BY d.delivery_date DESC
        ");
        $stmt->execute([$branch_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Count deliveries by branch
    public function countDeliveries($branch_id) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*)
            FROM deliveries
            WHERE branch_id = ?
        ");
        $stmt->execute([$branch_id]);
        return (int)$stmt->fetchColumn();
    }

    // Paginated deliveries by branch
    public function getDeliveriesPaginated($branch_id, $limit = 3, $offset = 0) {
        $limit  = (int)$limit;
        $offset = (int)$offset;

        $sql = "
            SELECT d.id, d.fuel_id, d.liters_added, d.delivery_date, f.fuel_name
            FROM deliveries d
            JOIN fuels f ON d.fuel_id = f.id
            WHERE d.branch_id = ?
            ORDER BY d.delivery_date DESC
            LIMIT $limit OFFSET $offset
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$branch_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Count filtered deliveries by branch
    public function countFilteredDeliveries($branch_id, $search = '', $dateFrom = '', $dateTo = '') {
        $sql = "
            SELECT COUNT(*)
            FROM deliveries d
            JOIN fuels f ON d.fuel_id = f.id
            WHERE d.branch_id = ?
        ";

        $params = [$branch_id];

        if ($search !== '') {
            $sql .= " AND f.fuel_name LIKE ?";
            $params[] = "%{$search}%";
        }

        if ($dateFrom !== '') {
            $sql .= " AND DATE(d.delivery_date) >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo !== '') {
            $sql .= " AND DATE(d.delivery_date) <= ?";
            $params[] = $dateTo;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn();
    }

    // Filtered + paginated deliveries by branch
    public function getFilteredDeliveries($branch_id, $limit = 10, $offset = 0, $search = '', $dateFrom = '', $dateTo = '') {
        $limit = (int)$limit;
        $offset = (int)$offset;

        $sql = "
            SELECT d.id, d.liters_added, d.delivery_date, f.fuel_name
            FROM deliveries d
            JOIN fuels f ON d.fuel_id = f.id
            WHERE d.branch_id = ?
        ";

        $params = [$branch_id];

        if ($search !== '') {
            $sql .= " AND f.fuel_name LIKE ?";
            $params[] = "%{$search}%";
        }

        if ($dateFrom !== '') {
            $sql .= " AND DATE(d.delivery_date) >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo !== '') {
            $sql .= " AND DATE(d.delivery_date) <= ?";
            $params[] = $dateTo;
        }

        $sql .= " ORDER BY d.delivery_date DESC LIMIT $limit OFFSET $offset";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
