<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../../resource/conn.php';

$data = $_POST;
if (!isset($data['id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Missing user ID.'
    ]);
    exit;
}

$userId = intval($data['id']);

try {
    $pdo = getDB();
    $stmt = $pdo->prepare('UPDATE users SET name = ?, mobile = ?, email = ?, password = ?, otp = ?, otp_created_at = ?, is_verified = ?, language = ?, bio = ?, remove = ? WHERE id = ?');
    $stmt->execute([
        $data['name'],
        $data['mobile'],
        $data['email'],
        $data['password'],
        $data['otp'],
        $data['otp_created_at'],
        $data['is_verified'],
        $data['language'],
        $data['bio'],
        $data['remove'],
        $userId
    ]);
    echo json_encode([
        'success' => true,
        'message' => 'User updated successfully.'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
}
?>
