<?php
require_once  '../../config/db.php';

class ForgotPasswordController {
    public static function resetPassword($data) {
        $email = $data['email'] ?? '';
        $newPassword = $data['new_password'] ?? '';

        // Validate input
        if (!$email || !$newPassword) {
            return ['status' => 400, 'body' => ['error' => 'Email and new password are required.']];
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['status' => 400, 'body' => ['error' => 'Invalid email format.']];
        }

        // Validate password strength
        if (strlen($newPassword) < 6) {
            return ['status' => 400, 'body' => ['error' => 'Password must be at least 6 characters long.']];
        }

        try {
            $pdo = getDB();
            
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                return ['status' => 404, 'body' => ['error' => 'Email not found.']];
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $updateStmt = $pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
            $updateResult = $updateStmt->execute([$hashedPassword, $email]);

            if (!$updateResult) {
                return ['status' => 500, 'body' => ['error' => 'Failed to update password.']];
            }

            return ['status' => 200, 'body' => ['success' => true, 'message' => 'Password updated successfully.']];

        } catch (PDOException $e) {
            error_log("Database error in ForgotPasswordController: " . $e->getMessage());
            return ['status' => 500, 'body' => ['error' => 'Database error occurred.']];
        } catch (Exception $e) {
            error_log("General error in ForgotPasswordController: " . $e->getMessage());
            return ['status' => 500, 'body' => ['error' => 'An error occurred while resetting password.']];
        }
    }
}