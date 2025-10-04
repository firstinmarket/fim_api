<?php

include("../../config/cors.php") ; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'] ?? '';
    $otp = $data['otp'] ?? '';

        if (!$email || !$otp) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and OTP are required.']);
            exit;
        }

        require_once __DIR__ . '/../../controllers/auth/VerifyOtpController.php';
        $result = VerifyOtpController::verify($email, $otp);
        http_response_code($result['status']);
        echo json_encode($result['body']);
        exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
exit;


?>