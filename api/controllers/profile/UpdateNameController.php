<?php
require_once '../../config/db.php';

class UpdateNameController {
    public static function updateName($user_id, $new_name) {
        $pdo = getDB();
        $stmt = $pdo->prepare('UPDATE users SET name = ? WHERE id = ?');
        $stmt->execute([$new_name, $user_id]);
        return ['status' => 200, 'body' => ['success' => true, 'message' => 'Name updated successfully']];
    }
}
