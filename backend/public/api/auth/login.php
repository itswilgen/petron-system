<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Accept");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
    exit;
}

require_once __DIR__ . '/../../../model/User.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    $input = $_POST;
}

$username = trim((string)($input['username'] ?? $input['id_number'] ?? ''));
$password = (string)($input['password'] ?? '');

if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Username and password are required"
    ]);
    exit;
}

$userModel = new User();
$user = $userModel->login($username, $password);

if (!$user) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Invalid ID number or password"
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "user" => [
        "id" => (int)$user["id"],
        "admin_uid" => $user["admin_uid"] ?? null,
        "staff_uid" => $user["staff_uid"] ?? null,
        "username" => $user["username"],
        "role" => $user["role"],
        "branch_id" => (int)$user["branch_id"],
        "branch_name" => $user["branch_name"] ?? null
    ]
]);
