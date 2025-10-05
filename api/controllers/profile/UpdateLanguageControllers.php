<?php
require_once '../../config/db.php';

class UpdateLanguageController {
    public static function updateLanguage($data) {
        // Enhanced error logging
        error_log('UpdateLanguageController: Received data: ' . json_encode($data));
        
        $user_id = $data['user_id'] ?? '';
        $new_language = $data['new_language'] ?? '';

        // Validate input
        if (empty($user_id) || empty($new_language)) {
            error_log('UpdateLanguageController: Missing required fields - user_id: ' . $user_id . ', new_language: ' . $new_language);
            return ['status' => 400, 'body' => ['success' => false, 'message' => 'User ID and new language are required.']];
        }

        // Define supported languages
        $supportedLanguages = ['english', 'tamil'];
        $normalizedLanguage = strtolower(trim($new_language));
        
        if (!in_array($normalizedLanguage, $supportedLanguages)) {
            error_log('UpdateLanguageController: Unsupported language: ' . $new_language);
            return ['status' => 400, 'body' => ['success' => false, 'message' => 'Unsupported language selected. Supported languages: ' . implode(', ', $supportedLanguages)]];
        }

        try {
            $pdo = getDB();
            
            if (!$pdo) {
                error_log('UpdateLanguageController: Database connection failed');
                return ['status' => 500, 'body' => ['success' => false, 'message' => 'Database connection failed.']];
            }
            
            // Check if user exists
            $checkStmt = $pdo->prepare('SELECT id, language FROM users WHERE id = ?');
            $checkStmt->execute([$user_id]);
            $user = $checkStmt->fetch();
            
            if (!$user) {
                error_log('UpdateLanguageController: User not found with ID: ' . $user_id);
                return ['status' => 404, 'body' => ['success' => false, 'message' => 'User not found.']];
            }

            // Check if language is already set to this value
            if ($user['language'] === $normalizedLanguage) {
                error_log('UpdateLanguageController: Language already set to: ' . $normalizedLanguage);
                return ['status' => 200, 'body' => ['success' => true, 'message' => 'Language is already set to ' . $new_language . '.']];
            }

            // Update language
            $stmt = $pdo->prepare('UPDATE users SET language = ? WHERE id = ?');
            $result = $stmt->execute([$normalizedLanguage, $user_id]);

            if ($result && $stmt->rowCount() > 0) {
                error_log('UpdateLanguageController: Language updated successfully for user: ' . $user_id);
                return ['status' => 200, 'body' => ['success' => true, 'message' => 'Language updated successfully to ' . $new_language . '.']];
            } else {
                error_log('UpdateLanguageController: Update failed for user: ' . $user_id . ', rowCount: ' . $stmt->rowCount());
                return ['status' => 500, 'body' => ['success' => false, 'message' => 'Failed to update language. Please try again.']];
            }
            
        } catch (PDOException $e) {
            error_log('UpdateLanguageController: Database error: ' . $e->getMessage());
            return ['status' => 500, 'body' => ['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]];
        } catch (Exception $e) {
            error_log('UpdateLanguageController: General error: ' . $e->getMessage());
            return ['status' => 500, 'body' => ['success' => false, 'message' => 'An unexpected error occurred: ' . $e->getMessage()]];
        }
    }
}
