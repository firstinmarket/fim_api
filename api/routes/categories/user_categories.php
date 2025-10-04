<?php

include("../../config/cors.php");
require_once __DIR__ . '/../../controllers/categories/UserCategoryController.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
} 
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get user categories
    if (isset($_GET['user_id'])) {
        $result = UserCategoryController::getUserCategories($_GET['user_id']);
        http_response_code($result['status']);
        echo json_encode($result['body']);
        exit;
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Missing user_id parameter']);
        exit;
    }
    
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
    exit;
}
?>
