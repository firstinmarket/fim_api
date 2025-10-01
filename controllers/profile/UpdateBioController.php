<?php
require_once '../../config/db.php';

class UpdateBioController {
    public static function updateBio($user_id, $bio) {
        $pdo = getDB();
        $stmt = $pdo->prepare('UPDATE users SET bio = ? WHERE id = ?');
        $stmt->execute([$bio, $user_id]);
        if ($stmt->rowCount() === 0) {
            return ['status' => 404, 'body' => ['success' => false, 'message' => 'User not found or bio unchanged']];
        }
        return ['status' => 200, 'body' => ['success' => true, 'message' => 'Bio updated successfully']];
    }
}
