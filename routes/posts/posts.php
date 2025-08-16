<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../controllers/PostController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $result = PostController::addPost($data);
    http_response_code($result['status']);
    echo json_encode($result['body']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = PostController::getPosts();
    http_response_code($result['status']);
    echo json_encode($result['body']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $data = json_decode(file_get_contents('php://input'), true);
    $post_id = $data['post_id'] ?? '';
    $field = $data['field'] ?? '';
    $result = PostController::updateCount($post_id, $field);
    http_response_code($result['status']);
    echo json_encode($result['body']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
exit;
