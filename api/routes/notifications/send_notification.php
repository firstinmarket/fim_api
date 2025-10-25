<?php
include("../../config/cors.php");
require_once __DIR__ . '/../../helpers/PushNotificationHelper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$title = $input['title'] ?? null;
$body = $input['body'] ?? null;
$type = $input['type'] ?? 'all'; // all, user, category
$targetId = $input['target_id'] ?? null; // user_id or category_id
$data = $input['data'] ?? []; // Additional data (post_id, etc.)

if (empty($title) || empty($body)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Title and body are required']);
    exit;
}

try {
    $success = false;
    
    switch ($type) {
        case 'user':
            if (empty($targetId)) {
                throw new Exception('user_id is required for user notification');
            }
            $success = PushNotificationHelper::sendToUser($targetId, $title, $body, $data);
            break;
            
        case 'category':
            if (empty($targetId)) {
                throw new Exception('category_id is required for category notification');
            }
            $success = PushNotificationHelper::sendToCategory($targetId, $title, $body, $data);
            break;
            
        case 'all':
        default:
            $success = PushNotificationHelper::sendToAllUsers($title, $body, $data);
            break;
    }
    
    if ($success) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Notification sent successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed t[=677k] send notification'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
