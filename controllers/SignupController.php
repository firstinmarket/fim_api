<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/response.php';

$data = json_decode(file_get_contents("php://input"), true);

$name = $data['name'] ?? '';
$phone = $data['phone'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (!$name || !$phone || !$email || !$password) {
    send_json(['error' => 'All fields are required'], 400);
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// user exists checking
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
$stmt->execute([$email, $phone]);
if ($stmt->rowCount() > 0) {
    send_json(['error' => 'Email or phone already registered'], 409);
}

// Insert user
$stmt = $pdo->prepare("INSERT INTO users (name, phone, email, password, created_at) VALUES (?, ?, ?, ?, NOW())");
$success = $stmt->execute([$name, $phone, $email, $hashedPassword]);

if ($success) {
    send_json(['message' => 'User registered successfully']);
} else {
    send_json(['error' => 'Registration failed'], 500);
}
?>
