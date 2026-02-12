<?php
require_once __DIR__ . '/../database/Database.php';

class Fuel {
    private $conn;
    private $table = "fuels";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Get all fuel
    public function getAllFuel() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Update liters (for delivery or edit)
    public function updateFuel($id, $liters, $price, $status) {
        $query = "UPDATE " . $this->table . " 
                  SET liters=?, price=?, status=? 
                  WHERE id=?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$liters, $price, $status, $id]);
    }

    // Get single fuel
    public function getFuelById($id){
        $query = "SELECT * FROM " . $this->table . " WHERE id=?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
