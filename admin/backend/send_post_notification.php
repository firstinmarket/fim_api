<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../resource/conn.php';

if (!class_exists('PushNotificationHelper')) {
  
    class PushNotificationHelper {
        public static function sendToCategory(int $categoryId, string $title, string $body, array $data): bool {
            return false;
        }

        public static function sendToUser(int $userId, string $title, string $body, array $data): bool {
            return false;
        }
    }
}

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['post_id'])) {
        throw new Exception('Post ID is required');
    }
    
    $postId = $data['post_id'];
    
    // Fetch post details
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT p.id, p.title, p.content, p.image, 
               GROUP_CONCAT(DISTINCT c.id) as category_ids,
               GROUP_CONCAT(DISTINCT c.name) as category_names
        FROM posts p
        LEFT JOIN post_categories pc ON p.id = pc.post_id
        LEFT JOIN categories c ON pc.category_id = c.id
        WHERE p.id = ?
        GROUP BY p.id
    ");
    $stmt->execute([$postId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        throw new Exception('Post not found');
    }
    
    // Prepare notification
    $title = '📰 New Article Published';
    $body = strlen($post['title']) > 100 ? substr($post['title'], 0, 97) . '...' : $post['title'];
    
    // Additional data to send with notification
    $notificationData = [
        'post_id' => $post['id'],
        'type' => 'new_post',
        'image' => $post['image'] ?? '',
        'click_action' => 'OPEN_POST',
        'timestamp' => time()
    ];
    
    // Send notification to all users or by categories
    $categoryIds = $post['category_ids'] ? explode(',', $post['category_ids']) : [];
    
    $sentCount = 0;
    $failedCount = 0;
    
    if (!empty($categoryIds)) {
        // Send to users who follow these categories
        foreach ($categoryIds as $categoryId) {
            $result = PushNotificationHelper::sendToCategory(
                (int)$categoryId,
                $title,
                $body,
                $notificationData
            );
            
            if ($result) {
                $sentCount++;
            } else {
                $failedCount++;
            }
        }
    } else {
        // Send to all users if no categories
        $stmt = $pdo->prepare("
            SELECT id FROM users 
            WHERE fcm_token IS NOT NULL 
            AND fcm_token != ''
        ");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($users as $user) {
            $result = PushNotificationHelper::sendToUser(
                $user['id'],
                $title,
                $body,
                $notificationData
            );
            
            if ($result) {
                $sentCount++;
            } else {
                $failedCount++;
            }
        }
    }
    
    // Log notification
    $stmt = $pdo->prepare("
        INSERT INTO notification_logs (post_id, sent_count, failed_count, sent_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$postId, $sentCount, $failedCount]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Notification sent successfully',
        'data' => [
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'post_title' => $post['title'],
            'categories' => $post['category_names'] ?? 'All Users'
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to send notification',
        'message' => $e->getMessage()
    ]);
}
?>
