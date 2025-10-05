<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../../config/cors.php");

try {
    require_once '../../controllers/profile/updateLanguageController.php';
} catch (Exception $e) {
    error_log('Route error: Failed to include controller: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server configuration error']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        $input = file_get_contents('php://input');
        error_log('Update language route: Received input: ' . $input);
        
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Update language route: JSON decode error: ' . json_last_error_msg());
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
            exit;
        }
        
        if (!class_exists('UpdateLanguageController')) {
            error_log('Update language route: Controller class not found');
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Controller not available']);
            exit;
        }
        
        $result = UpdateLanguageController::updateLanguage($data);
        http_response_code($result['status']);
        echo json_encode($result['body']);
        exit;
        
    } catch (Exception $e) {
        error_log('Update language route: Exception: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        exit;
    }
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed. Only PUT requests are supported.']);
exit;
