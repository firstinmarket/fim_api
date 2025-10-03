<?php
include("../../config/cors.php");
require_once '../../controllers/posts/PostController.php';


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
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
