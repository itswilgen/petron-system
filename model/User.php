<?php

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../includes/auth_roles.php';

class User {
    private $conn;
    private $table_name = "users";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Login with branch info
    public function login($username, $password) {
        $query = "
            SELECT u.*, b.branch_name
            FROM users u
            JOIN branches b ON u.branch_id = b.id
            WHERE u.username = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    // Create user with branch
    public function createUser($username, $hashedPassword, $role = ROLE_STAFF, $branch_id = 1) {
        $query = "INSERT INTO " . $this->table_name . " (username, password, role, branch_id)
                  VALUES (:username, :password, :role, :branch_id)";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':username' => $username,
            ':password' => $hashedPassword,
            ':role' => $role,
            ':branch_id' => $branch_id
        ]);
    }

    // Check if username exists
    public function usernameExists($username) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }

    // Get staff users by branch
    public function getStaffUsers($branch_id) {
        $query = "SELECT id, username, role, branch_id
                  FROM " . $this->table_name . "
                  WHERE role = :role AND branch_id = :branch_id
                  ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':role' => ROLE_STAFF,
            ':branch_id' => $branch_id
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBranches() {
        $query = "SELECT id, branch_name, location
                  FROM branches
                  ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function branchExists($branchId) {
        $query = "SELECT id
                  FROM branches
                  WHERE id = :id
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $branchId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }

    public function getAdminsAllBranches() {
        $query = "SELECT u.id, u.username, u.role, u.branch_id, b.branch_name
                  FROM " . $this->table_name . " u
                  JOIN branches b ON b.id = u.branch_id
                  WHERE u.role = :role
                  ORDER BY u.branch_id ASC, u.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':role' => ROLE_ADMIN
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUsersByRole($role) {
        $query = "SELECT COUNT(*) AS total
                  FROM " . $this->table_name . "
                  WHERE role = :role";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':role' => $role]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    public function countBranches() {
        $query = "SELECT COUNT(*) AS total FROM branches";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    public function getManagedUsersForSuperAdmin($excludeUserId) {
        $query = "SELECT u.id, u.username, u.role, u.branch_id, b.branch_name
                  FROM " . $this->table_name . " u
                  JOIN branches b ON u.branch_id = b.id
                  WHERE u.role IN (:admin_role, :staff_role)
                    AND u.id <> :exclude_user_id
                  ORDER BY 
                    CASE 
                        WHEN u.role = :admin_first THEN 1
                        WHEN u.role = :staff_second THEN 2
                        ELSE 3
                    END,
                    u.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':admin_role' => ROLE_ADMIN,
            ':staff_role' => ROLE_STAFF,
            ':exclude_user_id' => $excludeUserId,
            ':admin_first' => ROLE_ADMIN,
            ':staff_second' => ROLE_STAFF
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById($id) {
        $query = "SELECT id, username, role, branch_id
                  FROM " . $this->table_name . "
                  WHERE id = :id
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // Delete user by id + branch
    public function deleteUser($id, $branch_id) {
        $query = "DELETE FROM " . $this->table_name . "
                  WHERE id = :id AND branch_id = :branch_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':id' => $id,
            ':branch_id' => $branch_id
        ]);
    }

    public function deleteUserById($id) {
        $query = "DELETE FROM " . $this->table_name . "
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':id' => $id
        ]);
    }
}
?>
