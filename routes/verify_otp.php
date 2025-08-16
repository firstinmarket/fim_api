<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'] ?? '';
    $otp = $data['otp'] ?? '';

    if (!$email || !$otp) {
        http_response_code(400);
        echo json_encode(['error' => 'Email and OTP are required.']);
        exit;
    }

    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT id, is_verified, otp_created_at FROM users WHERE email = ? AND otp = ?');
    $stmt->execute([$email, $otp]);
    $user = $stmt->fetch();

    if ($user) {
        if ($user['is_verified']) {
            echo json_encode(['success' => false, 'message' => 'User already verified.']);
            exit;
        }
        // Check OTP expiry (10 min)
        $otpTime = strtotime($user['otp_created_at']);
        if (time() - $otpTime > 600) {
            http_response_code(410);
            echo json_encode(['success' => false, 'message' => 'OTP expired. Please signup again.']);
            exit;
        }
        // Mark user as verified
        $update = $pdo->prepare('UPDATE users SET is_verified = 1 WHERE id = ?');
        $update->execute([$user['id']]);
        echo json_encode(['success' => true, 'message' => 'Signup successful!']);
        exit;
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid OTP or email.']);
        exit;
    }
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
exit;


?>