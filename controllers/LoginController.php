<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/response.php';

$data = json_decode(file_get_contents("php://input"), true);

$identifier = $data['identifier'] ?? '';
$password = $data['password'] ?? '';

if (!$identifier || !$password) {
    send_json(['error' => 'Email/Phone and password are required'], 400);
}


$stmt = $pdo->prepare("SELECT id, name, phone, email, password, created_at FROM users WHERE email = ? OR phone = ?");
$stmt->execute([$identifier, $identifier]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password'])) {
    send_json(['error' => 'Invalid credentials'], 401);
}

unset($user['password']);

send_json([
    'message' => 'Login successful', 
]);
?>
