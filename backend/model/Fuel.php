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

    // Update only admin-editable fields (price + status)
    public function updateFuelAdminFields($id, $price, $status, $branch_id) {
        $query = "UPDATE {$this->table}
                  SET price = ?, status = ?
                  WHERE id = ? AND branch_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$price, $status, $id, $branch_id]);
    }

    // Get single fuel by id + branch
    public function getFuelById($id, $branch_id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ? AND branch_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id, $branch_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get single fuel by name + branch (used as fallback when fuel_id is missing)
    public function getFuelByName($fuel_name, $branch_id) {
        $query = "SELECT * FROM {$this->table}
                  WHERE fuel_name = ? AND branch_id = ?
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fuel_name, $branch_id]);
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

    // Super admin: grouped price view of fuel products across all branches
    public function getGlobalPriceSummary() {
        $stmt = $this->conn->prepare("
            SELECT
                fuel_name,
                COUNT(*) AS branch_count,
                MIN(price) AS min_price,
                MAX(price) AS max_price,
                AVG(price) AS avg_price
            FROM {$this->table}
            GROUP BY fuel_name
            ORDER BY fuel_name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Super admin: update one fuel price across all branches
    public function updateGlobalPriceByFuelName($fuel_name, $price) {
        $stmt = $this->conn->prepare("
            UPDATE {$this->table}
            SET price = ?
            WHERE fuel_name = ?
        ");
        $stmt->execute([(float)$price, (string)$fuel_name]);
        return (int)$stmt->rowCount();
    }

    public function fuelNameExistsGlobally($fuel_name) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*)
            FROM {$this->table}
            WHERE fuel_name = ?
        ");
        $stmt->execute([(string)$fuel_name]);
        return ((int)$stmt->fetchColumn()) > 0;
    }
}
?>
