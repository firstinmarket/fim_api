<?php
require_once '../../config/db.php';

class ProfileController {
    public static function getProfile($user_id) {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT 
    u.`name`, 
    u.`mobile`, 
    u.`email`, 
    u.`bio`,
    u.`language`,
    uc.`id` AS user_category_id,
    uc.`category_id`, 
    c.`name` AS category_name,
    uc.`created_at`
FROM `users` u
LEFT JOIN `user_categories` uc ON u.`id` = uc.`user_id`
LEFT JOIN `categories` c ON uc.`category_id` = c.`id`
WHERE u.`id` = ?');
        $stmt->execute([$user_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) {
            return ['status' => 404, 'body' => ['success' => false, 'message' => 'User not found']];
        }
        $user = [
            'name' => $rows[0]['name'],
            'mobile' => $rows[0]['mobile'],
            'email' => $rows[0]['email'],
            'bio' => $rows[0]['bio'],
            'language' => $rows[0]['language'] ,
        ];
        $categories = [];
        foreach ($rows as $row) {
            if (!empty($row['category_id'])) {
                $categories[] = [
                    'id' => $row['category_id'],
                    'category_name' => $row['category_name'] ?? null,
                    'created_at' => $row['created_at'],
                ];
            }
        }
        return ['status' => 200, 'body' => ['success' => true, 'profile' => ['user' => $user, 'categories' => $categories]]];
    }
}