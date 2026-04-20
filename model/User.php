<?php

require_once __DIR__ . '/../database/Database.php';

class User {
    private $conn;
    private $table_name = "users";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

        
    // Check login
    public function login($username, $password) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username=:username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user && password_verify($password, $user['password'])){
            return $user;
        }
        return false;
    }
}
?>
