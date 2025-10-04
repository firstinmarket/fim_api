<?php
require_once '../../config/db.php';

class UpdateEmailController {
    public static function requestEmailUpdate($user_id, $email) {
        $pdo = getDB();
        $stmt = $pdo->prepare('UPDATE users SET email = ? WHERE id = ?');
        $stmt->execute([$email, $user_id]);
        if ($stmt->rowCount() === 0) {
            return ['status' => 404, 'body' => ['success' => false, 'message' => 'User not found or bio unchanged']];
        }
        return ['status' => 200, 'body' => ['success' => true, 'message' => 'Bio updated successfully']];
    }
}
