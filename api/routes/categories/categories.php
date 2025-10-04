<?php
include("../../config/cors.php");
require_once __DIR__ . '/../../controllers/categories/CategoryController.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['type']) && $_GET['type'] === 'subcategories') {
        $result = CategoryController::getSubcategories();
        http_response_code($result['status']);
        echo json_encode($result['body']);
        exit;
    } elseif (isset($_GET['type']) && $_GET['type'] === 'with_subcategories') {
        $result = CategoryController::getCategoriesWithSubcategories();
        http_response_code($result['status']);
        echo json_encode($result['body']);
        exit;
    } else {
        $result = CategoryController::getCategories();
        http_response_code($result['status']);
        echo json_encode($result['body']);
        exit;
    }
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
exit;
