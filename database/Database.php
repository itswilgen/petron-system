<?php
class Database {
    private $host = "127.0.0.1";
    private $port = 3306;
    private $db_name = "petron_inventory_db";
    private $username = "root"; // default XAMPP
    private $password = "";     // default XAMPP
    private $socket = "/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock";
    private $lastError = null;
    public $conn;

    public function getConnection() {
        $this->conn = null;
        $this->lastError = null;

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        $dsnList = [];

        if ($this->socket !== "" && file_exists($this->socket)) {
            $dsnList[] = "mysql:unix_socket=" . $this->socket . ";dbname=" . $this->db_name . ";charset=utf8mb4";
        }

        $dsnList[] = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4";

        if ($this->host !== "127.0.0.1") {
            $dsnList[] = "mysql:host=127.0.0.1;port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4";
        }

        foreach ($dsnList as $dsn) {
            try {
                $this->conn = new PDO($dsn, $this->username, $this->password, $options);
                $this->conn->exec("set names utf8mb4");
                return $this->conn;
            } catch (PDOException $exception) {
                $this->lastError = $exception->getMessage();
            }
        }

        return $this->conn;
    }

    public function getLastError() {
        return $this->lastError;
    }
}
?>
