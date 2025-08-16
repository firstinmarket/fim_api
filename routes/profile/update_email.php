<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../controllers/profile/UpdateEmailController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $data['user_id'] ?? '';
    $new_email = $data['new_email'] ?? '';
    $result = UpdateEmailController::requestEmailUpdate($user_id, $new_email);
    http_response_code($result['status']);
    echo json_encode($result['body']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $data['user_id'] ?? '';
    $new_email = $data['new_email'] ?? '';
    $otp = $data['otp'] ?? '';
    $result = UpdateEmailController::verifyEmailUpdate($user_id, $new_email, $otp);
    http_response_code($result['status']);
    echo json_encode($result['body']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
exit;
