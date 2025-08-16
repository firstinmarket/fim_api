<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../controllers/profile/UpdateNameController.php';

if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $data['user_id'] ?? '';
    $new_name = $data['new_name'] ?? '';
    $result = UpdateNameController::updateName($user_id, $new_name);
    http_response_code($result['status']);
    echo json_encode($result['body']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
exit;
