<?php
require_once __DIR__ . '/../../config/db.php';

class UpdateMobileController {
    public static function updateMobile($user_id, $new_mobile) {
        $pdo = getDB();
        $stmt = $pdo->prepare('UPDATE users SET mobile = ? WHERE id = ?');
        $stmt->execute([$new_mobile, $user_id]);
        return ['status' => 200, 'body' => ['success' => true, 'message' => 'Mobile number updated successfully']];
    }
}
