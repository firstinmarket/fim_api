<?php
require_once '../../config/db.php';

class UpdateLanguageController {
    public static function updateLanguage($data) {
        $user_id = $data['user_id'] ?? '';
        $new_language = $data['new_language'] ?? '';

        // Validate input
        if (!$user_id || !$new_language) {
            return ['status' => 400, 'body' => ['success' => false, 'message' => 'User ID and new language are required.']];
        }

        // Define supported languages
        $supportedLanguages = ['english', 'tamil'];
        
        if (!in_array(strtolower($new_language), $supportedLanguages)) {
            return ['status' => 400, 'body' => ['success' => false, 'message' => 'Unsupported language selected.']];
        }

        try {
            $pdo = getDB();
            
            // Check if user exists
            $checkStmt = $pdo->prepare('SELECT id FROM users WHERE id = ?');
            $checkStmt->execute([$user_id]);
            if (!$checkStmt->fetch()) {
                return ['status' => 404, 'body' => ['success' => false, 'message' => 'User not found.']];
            }

            // Update language
            $stmt = $pdo->prepare('UPDATE users SET language = ? WHERE id = ?');
            $stmt->execute([strtolower($new_language), $user_id]);

            if ($stmt->rowCount() > 0) {
                return ['status' => 200, 'body' => ['success' => true, 'message' => 'Language updated successfully.']];
            } else {
                return ['status' => 400, 'body' => ['success' => false, 'message' => 'Language is already set to this value or update failed.']];
            }
        } catch (PDOException $e) {
            error_log('Update language error: ' . $e->getMessage());
            return ['status' => 500, 'body' => ['success' => false, 'message' => 'Database error occurred.']];
        }
    }
}
