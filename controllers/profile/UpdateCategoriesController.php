<?php
require_once __DIR__ . '/../../config/db.php';

class UpdateCategoriesController {
    public static function updateCategories($user_id, $category_ids) {
        $pdo = getDB();
        // Remove old categories
        $stmt = $pdo->prepare('DELETE FROM user_categories WHERE user_id = ?');
        $stmt->execute([$user_id]);
        // Add new categories
        $stmt = $pdo->prepare('INSERT INTO user_categories (user_id, subcategory_id) VALUES (?, ?)');
        foreach ($category_ids as $cat_id) {
            $stmt->execute([$user_id, $cat_id]);
        }
    
      
        return ['status' => 200, 'body' => ['success' => true, 'message' => 'Categories updated successfully']];
    }
}
