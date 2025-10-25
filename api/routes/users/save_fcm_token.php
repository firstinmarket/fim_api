<?php
include("../../config/cors.php");
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$user_id = $input['user_id'] ?? null;
$fcm_token = $input['fcm_token'] ?? null;

if (empty($user_id) || empty($fcm_token)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing user_id or fcm_token']);
    exit;
}

try {
    $pdo = getDB();
    
    // Update FCM token for user
    $stmt = $pdo->prepare('
        UPDATE users 
        SET fcm_token = ?, fcm_updated_at = NOW() 
        WHERE id = ?
    ');
    $stmt->execute([$fcm_token, $user_id]);
    
    if ($stmt->rowCount() > 0) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'FCM token saved successfully'
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
