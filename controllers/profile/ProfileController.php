<?php
require_once '../../config/db.php';

class ProfileController {
    public static function getProfile($user_id) {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT `name`, `mobile`, `email`,`bio` FROM `users` WHERE id = ?');
        $stmt->execute([$user_id]);
        $profile = $stmt->fetch();
        if ($profile) {
            return ['status' => 200, 'body' => ['success' => true, 'profile' => $profile]];
        } else {
            return ['status' => 404, 'body' => ['success' => false, 'message' => 'User not found']];
        }
    }
}
