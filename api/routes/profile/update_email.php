<?php
include("../../config/cors.php");
header('Content-Type: application/json');
require_once '../../controllers/profile/UpdateEmailController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $data['user_id'] ?? '';
    $email = $data['new_email'] ?? '';
    $result = UpdateEmailController::requestEmailUpdate($user_id, $email);
    http_response_code($result['status']);
    echo json_encode($result['body']);
    exit;
}



http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
exit;
