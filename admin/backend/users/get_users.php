<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../../resource/conn.php';

try {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, name, mobile, email, password, otp, otp_created_at, is_verified, language, bio, remove, created_at FROM users WHERE 1");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'success' => true,
        'data' => $users,
        'message' => 'Users fetched successfully',
        'timestamp' => date('Y-m-d H:i:s'),
        'total_users' => count($users)
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
