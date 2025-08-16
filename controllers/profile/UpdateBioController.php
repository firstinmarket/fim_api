<?php
require_once __DIR__ . '/../../config/db.php';

class UpdateBioController {
    public static function updateBio($user_id, $bio) {
        $pdo = getDB();
        $stmt = $pdo->prepare('UPDATE users SET bio = ? WHERE id = ?');
        $stmt->execute([$bio, $user_id]);
        return ['status' => 200, 'body' => ['success' => true, 'message' => 'Bio updated successfully']];
    }
}
