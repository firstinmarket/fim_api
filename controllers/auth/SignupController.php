<?php

require_once  '../../config/db.php';
require_once  '../../helpers/EmailOTP.php';

class SignupController {
    public static function generateOTP() {
        return rand(100000, 999999);
    }


    // Send OTP to email
    public static function sendOTP($email, $otp) {
        return EmailOTP::send($email, $otp);
    }

    public static function signup($data) {
        $name = $data['name'] ?? '';
        $mobile = $data['mobile'] ?? '';
    $email = strtolower($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (!$name || !$mobile || !$email || !$password) {
            return ['status' => 400, 'body' => ['error' => 'All fields are required.']];
        }

        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT id, is_verified FROM users WHERE mobile = ? OR email = ?');
        $stmt->execute([$mobile, $email]);
        $user = $stmt->fetch();
        if ($user) {
            if ($user['is_verified']) {
                return ['status' => 409, 'body' => ['error' => 'Mobile or email already registered and verified.']];
            } else {
                // Resend OTP for not verified user
                $otp = self::generateOTP();
                $update = $pdo->prepare('UPDATE users SET otp = ?, otp_created_at = NOW() WHERE id = ?');
                $update->execute([$otp, $user['id']]);
                self::sendOTP($email, $otp);
                return ['status' => 200, 'body' => ['success' => true, 'message' => 'OTP resent to email', 'email' => $email]];
            }
        }

        $otp = self::generateOTP();
        $stmt = $pdo->prepare('INSERT INTO users (name, mobile, email, password, otp, otp_created_at, is_verified) VALUES (?, ?, ?, ?, ?, NOW(), 0)');
        $stmt->execute([
            $name,
            $mobile,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $otp
        ]);

        self::sendOTP($email, $otp);
        return ['status' => 200, 'body' => ['success' => true, 'message' => 'OTP sent to email', 'email' => $email]];
    }

    public static function resendOtp($email) {
        $pdo = getDB();
    $email = strtolower($email);
    $stmt = $pdo->prepare('SELECT id, is_verified FROM users WHERE email = ?');
    $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) {
            return ['status' => 404, 'body' => ['error' => 'User not found']];
        }
        if ($user['is_verified']) {
            return ['status' => 409, 'body' => ['error' => 'User already verified']];
        }
        $otp = self::generateOTP();
        $update = $pdo->prepare('UPDATE users SET otp = ?, otp_created_at = NOW() WHERE id = ?');
        $update->execute([$otp, $user['id']]);
        self::sendOTP($email, $otp);
        return ['status' => 200, 'body' => ['success' => true, 'message' => 'OTP resent to email', 'email' => $email]];
    }
}


?>