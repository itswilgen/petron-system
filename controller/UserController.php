<?php

require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../includes/auth_roles.php';

class UserController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    private function ensureSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function redirectDenied() {
        header("Location: /petron_system/public/admin/app.php?page=staff_manage&denied=1");
        exit;
    }

    private function currentRole() {
        return $_SESSION['role'] ?? '';
    }

    // Handles user login and redirects based on role
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->ensureSession();

            $loginId = trim((string)($_POST['id_number'] ?? ($_POST['username'] ?? '')));
            $password = $_POST['password'] ?? '';

            $user_data = $this->user->login($loginId, $password);

            if ($user_data) {
                $_SESSION['user_id'] = $user_data['id'];
                $_SESSION['username'] = $user_data['username'];
                $_SESSION['role'] = $user_data['role'];
                $_SESSION['branch_id'] = $user_data['branch_id'];
                $_SESSION['branch_name'] = $user_data['branch_name'];

                if ($_SESSION['role'] === ROLE_SUPER_ADMIN) {
                    header("Location: /petron_system/public/superadmin/app.php?page=dashboard");
                    exit;
                }

                if (canAccessAdminArea($_SESSION['role'])) {
                    header("Location: /petron_system/public/admin/app.php?page=dashboard");
                    exit;
                }

                if (canAccessStaffArea($_SESSION['role'])) {
                    header("Location: /petron_system/public/staff/app.php?page=dashboard");
                    exit;
                }

                header("Location: /petron_system/public/auth/login.php");
                exit;
            } else {
                return "Invalid ID number or password";
            }
        }

        return null;
    }

    // For admin to create staff accounts
    public function createStaff() {
        if (!isset($_POST['create_staff'])) {
            return null;
        }

        $this->ensureSession();

        $currentRole = $this->currentRole();
        if (!canAccessAdminArea($currentRole)) {
            return "Action denied.";
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $requestedRole = trim($_POST['role'] ?? ROLE_STAFF);

        if ($username === '' || $password === '') {
            return "Please fill in all fields.";
        }

        if (strlen($password) < 6) {
            return "Password must be at least 6 characters.";
        }

        $allowedRoles = allowedCreationRolesFor($currentRole);
        if (!in_array($requestedRole, $allowedRoles, true)) {
            return "You are not allowed to create this role.";
        }

        if ($this->user->usernameExists($username)) {
            return "Username already exists.";
        }

        $branchId = (int)($_SESSION['branch_id'] ?? 0);
        if ($branchId <= 0) {
            return "Invalid branch assignment.";
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $created = $this->user->createStaffWithUniqueId($username, $hashed, $branchId);

        if (($created['ok'] ?? false) === true) {
            $query = http_build_query([
                'created' => 1,
                'staff_uid' => (string)($created['staff_uid'] ?? '')
            ]);
            header("Location: /petron_system/public/admin/app.php?page=staff_manage&{$query}");
            exit;
        }

        return "Failed to create account.";
    }

    public function listStaff() {
        $this->ensureSession();

        $currentRole = $this->currentRole();
        if (!canAccessAdminArea($currentRole)) {
            return [];
        }

        $branchId = (int)($_SESSION['branch_id'] ?? 0);
        if ($branchId <= 0) {
            return [];
        }

        return $this->user->getStaffUsers($branchId);
    }

    // For deleting staff accounts (admin only)
    public function deleteStaff() {
        if (!isset($_GET['delete_staff_id'])) {
            return null;
        }

        $this->ensureSession();

        $currentRole = $this->currentRole();
        if (!canAccessAdminArea($currentRole)) {
            $this->redirectDenied();
        }

        $id = (int)($_GET['delete_staff_id'] ?? 0);
        if ($id <= 0) {
            $this->redirectDenied();
        }

        $branchId = (int)($_SESSION['branch_id'] ?? 0);
        if ($branchId <= 0) {
            $this->redirectDenied();
        }

        // extra safety: regular admin can only delete staff in same branch
        $staffList = $this->user->getStaffUsers($branchId);
        $isStaff = false;
        foreach ($staffList as $staff) {
            if ((int)$staff['id'] === $id) {
                $isStaff = true;
                break;
            }
        }

        if (!$isStaff) {
            $this->redirectDenied();
        }

        $this->user->deleteUser($id, $branchId);
        header("Location: /petron_system/public/admin/app.php?page=staff_manage&deleted=1");
        exit;
    }

    public function logout() {
        $this->ensureSession();

        session_destroy();
        header("Location: /petron_system/public/auth/login.php");
        exit;
    }
}
