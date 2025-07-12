<?php
header('Content-Type: application/json'); 
session_start();
require_once '../controllers/UserController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new UserController();
    $controller->updateProfile();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
