<?php
require_once '../../config/db.php';;

class UpdateCategoriesController {
    public static function updateCategories($user_id, $category_ids) {
        if (empty($user_id) || !is_array($category_ids) || empty($category_ids)) {
            return ['status' => 400, 'body' => ['success' => false, 'message' => 'Invalid user or categories', 'debug' => compact('user_id', 'category_ids')]];
        }

        $pdo = getDB();
        try {
            $pdo->beginTransaction();

            // Remove old categories
            $stmt = $pdo->prepare('DELETE FROM user_categories WHERE user_id = ?');
            $stmt->execute([$user_id]);

            // Add new categories
            $stmt = $pdo->prepare('INSERT INTO user_categories (user_id, subcategory_id) VALUES (?, ?)');
            $inserted = 0;
            $not_found = [];
            foreach ($category_ids as $cat_id) {
                // Check if subcategory exists
                $check = $pdo->prepare('SELECT id FROM subcategories WHERE id = ?');
                $check->execute([$cat_id]);
                if ($check->fetch()) {
                    $stmt->execute([$user_id, $cat_id]);
                    $inserted++;
                } else {
                    $not_found[] = $cat_id;
                }
            }

            $pdo->commit();
            return [
                'status' => 200,
                'body' => [
                    'success' => true,
                    'message' => 'Categories updated successfully',
                    'inserted' => $inserted,
                    'not_found' => $not_found,
                    'debug' => compact('user_id', 'category_ids')
                ]
            ];
        } catch (Exception $e) {
            $pdo->rollBack();
            return [
                'status' => 500,
                'body' => [
                    'success' => false,
                    'message' => 'DB error: ' . $e->getMessage(),
                    'debug' => compact('user_id', 'category_ids')
                ]
            ];
        }
    }
}
