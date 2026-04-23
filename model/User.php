<?php

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../includes/auth_roles.php';

class User {
    private $conn;
    private $table_name = "users";
    private $hasAdminUidColumn = null;
    private $hasStaffUidColumn = null;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    private function hasAdminUidColumn() {
        if ($this->hasAdminUidColumn !== null) {
            return $this->hasAdminUidColumn;
        }

        $stmt = $this->conn->prepare("SHOW COLUMNS FROM {$this->table_name} LIKE 'admin_uid'");
        $stmt->execute();
        $this->hasAdminUidColumn = (bool)$stmt->fetch(PDO::FETCH_ASSOC);
        return $this->hasAdminUidColumn;
    }

    private function hasStaffUidColumn() {
        if ($this->hasStaffUidColumn !== null) {
            return $this->hasStaffUidColumn;
        }

        $stmt = $this->conn->prepare("SHOW COLUMNS FROM {$this->table_name} LIKE 'staff_uid'");
        $stmt->execute();
        $this->hasStaffUidColumn = (bool)$stmt->fetch(PDO::FETCH_ASSOC);
        return $this->hasStaffUidColumn;
    }

    private function buildAdminUidFromId($userId) {
        return 'ADM-' . str_pad((string)((int)$userId), 6, '0', STR_PAD_LEFT);
    }

    private function buildStaffUidFromId($userId) {
        return 'STF-' . str_pad((string)((int)$userId), 6, '0', STR_PAD_LEFT);
    }

    private function insertUserRecord($username, $hashedPassword, $role, $branch_id) {
        $columns = ['username', 'password', 'role', 'branch_id'];
        $placeholders = [':username', ':password', ':role', ':branch_id'];
        $params = [
            ':username' => $username,
            ':password' => $hashedPassword,
            ':role' => $role,
            ':branch_id' => $branch_id
        ];

        if ($this->hasAdminUidColumn()) {
            $columns[] = 'admin_uid';
            $placeholders[] = ':admin_uid';
            $params[':admin_uid'] = null;
        }

        if ($this->hasStaffUidColumn()) {
            $columns[] = 'staff_uid';
            $placeholders[] = ':staff_uid';
            $params[':staff_uid'] = null;
        }

        $query = "INSERT INTO " . $this->table_name .
                 " (" . implode(', ', $columns) . ") " .
                 "VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return (int)$this->conn->lastInsertId();
    }

    // Login with branch info using account ID number
    public function login($loginIdentifier, $password) {
        $identifier = trim((string)$loginIdentifier);
        if ($identifier === '') {
            return false;
        }

        if ($this->hasAdminUidColumn() && $this->hasStaffUidColumn()) {
            $query = "
                SELECT u.*, b.branch_name
                FROM users u
                JOIN branches b ON u.branch_id = b.id
                WHERE (
                    u.admin_uid = :identifier_admin
                    OR u.staff_uid = :identifier_staff
                    OR CAST(u.id AS CHAR) = :identifier_id
                )
                LIMIT 1
            ";
            $params = [
                ':identifier_admin' => $identifier,
                ':identifier_staff' => $identifier,
                ':identifier_id' => $identifier
            ];
        } elseif ($this->hasAdminUidColumn()) {
            $query = "
                SELECT u.*, b.branch_name
                FROM users u
                JOIN branches b ON u.branch_id = b.id
                WHERE (u.admin_uid = :identifier_uid OR CAST(u.id AS CHAR) = :identifier_id)
                LIMIT 1
            ";
            $params = [
                ':identifier_uid' => $identifier,
                ':identifier_id' => $identifier
            ];
        } elseif ($this->hasStaffUidColumn()) {
            $query = "
                SELECT u.*, b.branch_name
                FROM users u
                JOIN branches b ON u.branch_id = b.id
                WHERE (u.staff_uid = :identifier_uid OR CAST(u.id AS CHAR) = :identifier_id)
                LIMIT 1
            ";
            $params = [
                ':identifier_uid' => $identifier,
                ':identifier_id' => $identifier
            ];
        } else {
            $query = "
                SELECT u.*, b.branch_name
                FROM users u
                JOIN branches b ON u.branch_id = b.id
                WHERE CAST(u.id AS CHAR) = :identifier
                LIMIT 1
            ";
            $params = [':identifier' => $identifier];
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    // Create user with branch
    public function createUser($username, $hashedPassword, $role = ROLE_STAFF, $branch_id = 1) {
        try {
            $this->insertUserRecord($username, $hashedPassword, $role, $branch_id);
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function createAdminWithUniqueId($username, $hashedPassword, $branch_id) {
        $this->conn->beginTransaction();

        try {
            $newId = $this->insertUserRecord($username, $hashedPassword, ROLE_ADMIN, $branch_id);
            $adminUid = $this->buildAdminUidFromId($newId);

            if ($this->hasAdminUidColumn()) {
                $update = $this->conn->prepare("
                    UPDATE {$this->table_name}
                    SET admin_uid = :admin_uid
                    WHERE id = :id
                    LIMIT 1
                ");
                $update->execute([
                    ':admin_uid' => $adminUid,
                    ':id' => $newId
                ]);
            }

            $this->conn->commit();

            return [
                'ok' => true,
                'id' => $newId,
                'admin_uid' => $adminUid
            ];
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            return [
                'ok' => false,
                'id' => 0,
                'admin_uid' => null
            ];
        }
    }

    public function createStaffWithUniqueId($username, $hashedPassword, $branch_id) {
        $this->conn->beginTransaction();

        try {
            $newId = $this->insertUserRecord($username, $hashedPassword, ROLE_STAFF, $branch_id);
            $staffUid = $this->buildStaffUidFromId($newId);

            if ($this->hasStaffUidColumn()) {
                $update = $this->conn->prepare("
                    UPDATE {$this->table_name}
                    SET staff_uid = :staff_uid
                    WHERE id = :id
                    LIMIT 1
                ");
                $update->execute([
                    ':staff_uid' => $staffUid,
                    ':id' => $newId
                ]);
            }

            $this->conn->commit();

            return [
                'ok' => true,
                'id' => $newId,
                'staff_uid' => $staffUid
            ];
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            return [
                'ok' => false,
                'id' => 0,
                'staff_uid' => null
            ];
        }
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
        if ($this->hasStaffUidColumn()) {
            $query = "SELECT id, staff_uid, username, role, branch_id
                      FROM " . $this->table_name . "
                      WHERE role = :role AND branch_id = :branch_id
                      ORDER BY id DESC";
        } else {
            $query = "SELECT id, CONCAT('STF-', LPAD(id, 6, '0')) AS staff_uid, username, role, branch_id
                      FROM " . $this->table_name . "
                      WHERE role = :role AND branch_id = :branch_id
                      ORDER BY id DESC";
        }

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
        if ($this->hasAdminUidColumn()) {
            $query = "SELECT
                        u.id,
                        u.admin_uid,
                        u.username,
                        u.role,
                        u.branch_id,
                        b.branch_name
                      FROM " . $this->table_name . " u
                      JOIN branches b ON b.id = u.branch_id
                      WHERE u.role = :role
                      ORDER BY u.branch_id ASC, u.id DESC";
        } else {
            $query = "SELECT
                        u.id,
                        CONCAT('ADM-', LPAD(u.id, 6, '0')) AS admin_uid,
                        u.username,
                        u.role,
                        u.branch_id,
                        b.branch_name
                      FROM " . $this->table_name . " u
                      JOIN branches b ON b.id = u.branch_id
                      WHERE u.role = :role
                      ORDER BY u.branch_id ASC, u.id DESC";
        }

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
