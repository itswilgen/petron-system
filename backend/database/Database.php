<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $ssl_mode;
    public $conn;

    public function __construct() {
        $this->host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: 'localhost';
        $this->db_name = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'petron_inventory_db';
        $this->username = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: getenv('MYSQLPASSWORD') ?: '';
        $this->port = getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: '3306';
        $this->ssl_mode = strtoupper(getenv('DB_SSL_MODE') ?: getenv('MYSQL_SSL_MODE') ?: '');
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ];

            if ($this->ssl_mode === 'REQUIRED') {
                if (defined('PDO::MYSQL_ATTR_SSL_CA') && file_exists('/etc/ssl/certs/ca-certificates.crt')) {
                    $options[constant('PDO::MYSQL_ATTR_SSL_CA')] = '/etc/ssl/certs/ca-certificates.crt';
                }

                if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
                    $options[constant('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')] = false;
                }
            }

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Database connection failed: " . $exception->getMessage());

            die(json_encode([
                "success" => false,
                "message" => "Database connection failed"
            ]));
        }

        return $this->conn;
    }
}
