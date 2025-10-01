<?php
include("../../config/cors.php");

require_once '../../controllers/profile/UpdateBioController.php';

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $data['user_id'] ?? '';
    $bio = $data['new_bio'] ?? '';
    $result = UpdateBioController::updateBio($user_id, $bio);
    http_response_code($result['status']);
    echo json_encode($result['body']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
exit;
