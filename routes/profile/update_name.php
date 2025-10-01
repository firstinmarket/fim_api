<?php

require_once '../../controllers/profile/UpdateNameController.php';
include("../../config/cors.php");

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $data['user_id'] ?? '';
    $new_name = $data['new_name'] ?? '';

    if (empty($user_id) || empty($new_name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User ID and new name are required']);
        exit;
    }

    $result = UpdateNameController::updateName($user_id, $new_name);
    http_response_code($result['status']);
    echo json_encode($result['body']);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
exit;
?>