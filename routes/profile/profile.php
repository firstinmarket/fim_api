<?php
require_once '../../controllers/profile/ProfileController.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
   
    $user_id = $_GET['user_id'] ?? null;
    if (!$user_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'user_id is required']);
        exit;
    }
    $result = ProfileController::getProfile($user_id);
    http_response_code($result['status']);
    echo json_encode($result['body']);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
