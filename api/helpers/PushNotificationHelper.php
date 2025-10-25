<?php
require_once __DIR__ . '/../config/db.php';

class PushNotificationHelper {
    
    private static $serviceAccountPath = __DIR__ . '/../config/fimapp-3b671-firebase-adminsdk-fbsvc-88244f2ef9.json';
    private static $projectId = 'fimapp-3b671'; // Replace with your Firebase Project ID from Firebase Console
    
    /**
     * Get OAuth 2.0 Access Token from Service Account
     */
    private static function getAccessToken() {
        if (!file_exists(self::$serviceAccountPath)) {
            error_log("Service account file not found at: " . self::$serviceAccountPath);
            return null;
        }
        
        $serviceAccount = json_decode(file_get_contents(self::$serviceAccountPath), true);
        
        $now = time();
        $payload = [
            'iss' => $serviceAccount['client_email'],
            'sub' => $serviceAccount['client_email'],
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging'
        ];
        
        // Create JWT
        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $payload = json_encode($payload);
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = '';
        openssl_sign(
            $base64UrlHeader . "." . $base64UrlPayload,
            $signature,
            $serviceAccount['private_key'],
            OPENSSL_ALGO_SHA256
        );
        
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        
        // Exchange JWT for access token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
    }
    
    /**
     * Send push notification to specific user
     */
    public static function sendToUser($userId, $title, $body, $data = []) {
        try {
            $pdo = getDB();
            
            // Get user's FCM token
            $stmt = $pdo->prepare('SELECT fcm_token FROM users WHERE id = ? AND fcm_token IS NOT NULL');
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user || empty($user['fcm_token'])) {
                error_log("No FCM token found for user: $userId");
                return false;
            }
            
            return self::sendNotification($user['fcm_token'], $title, $body, $data);
            
        } catch (Exception $e) {
            error_log("Error sending push notification to user: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send push notification to multiple users
     */
    public static function sendToMultipleUsers($userIds, $title, $body, $data = []) {
        try {
            $pdo = getDB();
            
            // Get FCM tokens for all users
            $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
            $stmt = $pdo->prepare("SELECT fcm_token FROM users WHERE id IN ($placeholders) AND fcm_token IS NOT NULL");
            $stmt->execute($userIds);
            $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($tokens)) {
                error_log("No FCM tokens found for provided users");
                return false;
            }
            
            $success = true;
            foreach ($tokens as $token) {
                if (!self::sendNotification($token, $title, $body, $data)) {
                    $success = false;
                }
            }
            
            return $success;
            
        } catch (Exception $e) {
            error_log("Error sending push notification to multiple users: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send push notification to all users
     */
    public static function sendToAllUsers($title, $body, $data = []) {
        try {
            $pdo = getDB();
            
            // Get all FCM tokens
            $stmt = $pdo->query('SELECT fcm_token FROM users WHERE fcm_token IS NOT NULL');
            $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($tokens)) {
                error_log("No FCM tokens found");
                return false;
            }
            
            $success = true;
            foreach ($tokens as $token) {
                if (!self::sendNotification($token, $title, $body, $data)) {
                    $success = false;
                }
            }
            
            return $success;
            
        } catch (Exception $e) {
            error_log("Error sending push notification to all users: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send push notification to users by category
     */
    public static function sendToCategory($categoryId, $title, $body, $data = []) {
        try {
            $pdo = getDB();
            
            // Get FCM tokens for users interested in this category
            $stmt = $pdo->prepare('
                SELECT DISTINCT u.fcm_token 
                FROM users u
                INNER JOIN user_categories uc ON u.id = uc.user_id
                WHERE uc.category_id = ? AND u.fcm_token IS NOT NULL
            ');
            $stmt->execute([$categoryId]);
            $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($tokens)) {
                error_log("No FCM tokens found for category: $categoryId");
                return false;
            }
            
            $success = true;
            foreach ($tokens as $token) {
                if (!self::sendNotification($token, $title, $body, $data)) {
                    $success = false;
                }
            }
            
            return $success;
            
        } catch (Exception $e) {
            error_log("Error sending push notification to category: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send notification via FCM V1 API
     */
    private static function sendNotification($token, $title, $body, $data = []) {
        $accessToken = self::getAccessToken();
        
        if (!$accessToken) {
            error_log("Failed to get FCM access token");
            return false;
        }
        
        $url = "https://fcm.googleapis.com/v1/projects/" . self::$projectId . "/messages:send";
        
        $message = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'sound' => 'default',
                        'color' => '#5F8DFF',
                    ]
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 1,
                        ]
                    ]
                ]
            ]
        ];
        
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200) {
            error_log("Push notification sent successfully to token: " . substr($token, 0, 20) . "...");
            return true;
        } else {
            error_log("Failed to send push notification. HTTP Code: $httpCode, Response: $result");
            return false;
        }
    }
}
