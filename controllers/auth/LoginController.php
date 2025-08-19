<?php
require_once  '../../config/db.php';
require_once  '../../helpers/EmailOTP.php';

class LoginController {
    public static function login($data) {
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (!$email || !$password) {
            return ['status' => 400, 'body' => ['error' => 'Email and password are required.']];
        }

        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT id, password, is_verified FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['status' => 401, 'body' => ['error' => 'Invalid email or password.']];
        }

        if (!password_verify($password, $user['password'])) {
            return ['status' => 401, 'body' => ['error' => 'Invalid email or password.']];
        }


        // Set session for user
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $email;
        // Session will persist until logout
        return ['status' => 200, 'body' => ['success' => true, 'message' => 'Login successful', 'email' => $email,'user_id' => $user['id']]];
    }
}
