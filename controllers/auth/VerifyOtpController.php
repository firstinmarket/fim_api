<?php
require_once  '../../config/db.php';

class VerifyOtpController {
    public static function verify($email, $otp) {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT id, is_verified, otp_created_at FROM users WHERE email = ? AND otp = ?');
        $stmt->execute([$email, $otp]);
        $user = $stmt->fetch();
        if ($user) {
            if ($user['is_verified']) {
                return ['status' => 409, 'body' => ['success' => false, 'message' => 'User already verified.']];
            }
            $otpTime = strtotime($user['otp_created_at']);
            if (time() - $otpTime > 600) {
                return ['status' => 410, 'body' => ['success' => false, 'message' => 'OTP expired. Please request a new OTP.']];
            }
            $update = $pdo->prepare('UPDATE users SET is_verified = 1 WHERE id = ?');
            $update->execute([$user['id']]);
            return ['status' => 200, 'body' => ['success' => true, 'message' => 'Signup successful!']];
        } else {
            return ['status' => 401, 'body' => ['success' => false, 'message' => 'Invalid OTP or email.']];
        }
    }
}
