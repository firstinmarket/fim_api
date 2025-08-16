<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/EmailOTP.php';

class UpdateEmailController {
    public static function requestEmailUpdate($user_id, $new_email) {
        $pdo = getDB();
        $otp = rand(100000, 999999);
        $stmt = $pdo->prepare('UPDATE users SET otp = ?, otp_created_at = NOW() WHERE id = ?');
        $stmt->execute([$otp, $user_id]);
        EmailOTP::send($new_email, $otp);
        return ['status' => 200, 'body' => ['success' => true, 'message' => 'OTP sent to new email', 'email' => $new_email]];
    }
    public static function verifyEmailUpdate($user_id, $new_email, $otp) {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT otp, otp_created_at FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        if ($user && $user['otp'] == $otp && (time() - strtotime($user['otp_created_at']) <= 600)) {
            $update = $pdo->prepare('UPDATE users SET email = ? WHERE id = ?');
            $update->execute([$new_email, $user_id]);
            return ['status' => 200, 'body' => ['success' => true, 'message' => 'Email updated successfully']];
        }
        return ['status' => 400, 'body' => ['error' => 'Invalid or expired OTP']];
    }
}
