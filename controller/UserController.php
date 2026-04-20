<?php

require_once __DIR__ . '/../model/User.php';

class UserController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

// Handles user login and redirects based on role
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            $user_data = $this->user->login($username, $password);

            if ($user_data) {
                $_SESSION['user_id'] = $user_data['id'];
                $_SESSION['username'] = $user_data['username'];
                $_SESSION['role'] = $user_data['role'];
                $_SESSION['branch_id'] = $user_data['branch_id'];
                $_SESSION['branch_name'] = $user_data['branch_name'];

  
                if ($_SESSION['role'] === 'super_admin') {
                    header("Location: /petron_system/public/superadmin/app.php?page=dashboard");
                    exit;
                } elseif ($_SESSION['role'] === 'admin') {
                    header("Location: /petron_system/public/admin/app.php?page=dashboard");
                    exit;
                } elseif ($_SESSION['role'] === 'staff') {
                    header("Location: /petron_system/public/staff/app.php?page=dashboard");
                    exit;
                }
            } else {
                return "Invalid username or password";
            }
        }

        return null;
    }

// For admin to create staff accounts
    public function createStaff() {
        if (isset($_POST['create_staff'])) {
            
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($username === '' || $password === '') {
                return "Please fill in all fields.";
            }

            if (strlen($password) < 6) {
                return "Password must be at least 6 characters.";
            }

            if ($this->user->usernameExists($username)) {
                return "Username already exists.";
            }

            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $branchId = $_SESSION['branch_id'];
            $ok = $this->user->createUser($username,$hashed,'staff',$branchId);

            if ($ok) {
                header("Location: /petron_system/public/admin/app.php?page=staff_manage&created=1");
                exit;
            }

            return "Failed to create staff account.";
        }

        return null;
    }

    public function listStaff() {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();}

            $branchId = $_SESSION['branch_id'];
            return $this->user->getStaffUsers($branchId);
    }

// For deleting staff accounts (admin only)
    public function deleteStaff() {
        if (isset($_GET['delete_staff_id'])) {

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $id = (int)($_GET['delete_staff_id']);

            // extra safety: only delete if staff
            $branchId = $_SESSION['branch_id'];
            $staffList = $this->user->getStaffUsers($branchId);
            $isStaff = false;
            foreach ($staffList as $s) {
                if ((int)$s['id'] === $id) { $isStaff = true; break; }
            }

            if (!$isStaff) {
                header("Location: /petron_system/public/admin/app.php?page=staff_manage&denied=1");
                exit;
                }
                
            $this->user->deleteUser($id,$branchId);

            // ✅ add flag so UI can show success message
            header("Location: /petron_system/public/admin/app.php?page=staff_manage&deleted=1");
            exit;
        }

        return null;
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        
        session_destroy();
        header("Location: /petron_system/public/auth/login.php");
        exit;
    }
}