<?php
require_once __DIR__ . '/../model/User.php';

session_start();

class UserController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    public function login() {
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $username = trim($_POST['username']);
            $password = $_POST['password'];

            $user_data = $this->user->login($username, $password);
            if($user_data){
                $_SESSION['user_id'] = $user_data['id'];
                $_SESSION['username'] = $user_data['username'];
                $_SESSION['role'] = $user_data['role'];
                header("Location: ../dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password";
                return $error;
            }
        }
    }

    public function logout() {
        session_destroy();
        header("Location: login.php");
        exit();
    }
}
?>
