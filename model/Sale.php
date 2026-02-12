<?php
require_once __DIR__ . '/../database/Database.php';

class Sale {
    private $conn;
    private $table = "sales";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createSale($fuel_id, $liters, $price, $total){
        $query = "INSERT INTO " . $this->table . " 
                  (fuel_id, liters, price, total_price) 
                  VALUES (?,?,?,?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$fuel_id,$liters,$price,$total]);
    }
}
?>
