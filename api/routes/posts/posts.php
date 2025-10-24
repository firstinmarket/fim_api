<?php
include("../../config/cors.php");
require_once '../../controllers/posts/PostController.php';


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['post_id']) && isset($_GET['user_id'])) {
        $post_id = $_GET['post_id'];
        $user_id = $_GET['user_id'];
        $status_type = $_GET['status'] ?? 'save'; 
        
        if ($status_type === 'like') {
            $result = PostController::getLikeStatus($post_id, $user_id);
        } else {
            $result = PostController::getSaveStatus($post_id, $user_id);
        }
        http_response_code($result['status']);
        echo json_encode($result['body']);
        exit;
    }
    
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
    if (empty($user_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing user_id parameter']);
        exit;
    }
    $result = PostController::getPostsByUserCategories($user_id);
    http_response_code($result['status']);
    echo json_encode($result['body']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $post_id = $data['post_id'] ?? '';
    $field = $data['field'] ?? '';
    $user_id = $data['user_id'] ?? '';
    $result = PostController::updateCount($post_id, $field, $user_id);
    http_response_code($result['status']);
    echo json_encode($result['body']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
exit;
