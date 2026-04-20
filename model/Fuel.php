<?php
require_once __DIR__ . '/../database/Database.php';

class Fuel {
    private $conn;
    private $table = "fuels";

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

    // Get all fuel by branch
    public function getAllFuel($branch_id) {
        $query = "SELECT * FROM {$this->table} WHERE branch_id = ? ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$branch_id]);
        return $stmt;
    }

    // Update fuel by id + branch
    public function updateFuel($id, $liters, $price, $status, $branch_id) {
        $query = "UPDATE {$this->table}
                SET liters = ?, price = ?, status = ?
                WHERE id = ? AND branch_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$liters, $price, $status, $id, $branch_id]);
        return $stmt->rowCount();
    }

    // Get single fuel by id + branch
    public function getFuelById($id, $branch_id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ? AND branch_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id, $branch_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get low stock by branch
    public function getLowStock($branch_id, $threshold = 0.30) {
        $stmt = $this->conn->prepare("
            SELECT fuel_name, liters, capacity
            FROM {$this->table}
            WHERE branch_id = :branch_id
              AND capacity > 0
              AND (liters / capacity) <= :threshold
        ");

        $stmt->bindValue(':branch_id', $branch_id, PDO::PARAM_INT);
        $stmt->bindValue(':threshold', $threshold);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
