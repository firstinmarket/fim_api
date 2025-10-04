<?php
class UserCategoryController {
    
    public static function saveUserCategories() {
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['user_id']) || !isset($input['categories'])) {
            return ['status' => 400, 'message' => 'Missing required fields'];
        }
        
        $user_id = $input['user_id'];
        $categories = $input['categories']; // Array of category names or IDs
        
        if (!is_array($categories)) {
            return ['status' => 400, 'message' => 'Categories must be an array'];
        }
        
        try {
            $pdo = getDB();
            $pdo->beginTransaction();
            
            // First, delete existing categories for this user
            $deleteStmt = $pdo->prepare('DELETE FROM user_categories WHERE user_id = ?');
            $deleteStmt->execute([$user_id]);
            
            // Then insert new categories
            $insertStmt = $pdo->prepare('
                INSERT INTO user_categories (user_id, subcategory_id, created_at) 
                SELECT ?, s.id, NOW() 
                FROM subcategories s 
                WHERE s.name = ?
            ');
            
            foreach ($categories as $categoryName) {
                $insertStmt->execute([$user_id, $categoryName]);
            }
            
            $pdo->commit();
            
            return [
                'status' => 200, 
                'message' => 'Categories saved successfully',
                'count' => count($categories)
            ];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            return [
                'status' => 500, 
                'message' => 'Failed to save categories: ' . $e->getMessage()
            ];
        }
    }
    
    public static function getUserCategories($user_id) {
        try {
            $pdo = getDB();
            $stmt = $pdo->prepare('
                SELECT s.id, s.name, s.category_id, uc.created_at
                FROM user_categories uc
                JOIN subcategories s ON uc.subcategory_id = s.id
                WHERE uc.user_id = ?
                ORDER BY s.name ASC
            ');
            $stmt->execute([$user_id]);
            $categories = $stmt->fetchAll();
            
            return [
                'status' => 200, 
                'body' => $categories
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 500, 
                'message' => 'Failed to fetch user categories: ' . $e->getMessage()
            ];
        }
    }
}
?>
