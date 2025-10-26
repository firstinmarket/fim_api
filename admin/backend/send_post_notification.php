<?php
require  '../vendor/autoload.php';
require_once '../resource/conn.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['post_id'])) throw new Exception('Post ID is required');

    $postId = $input['post_id'];
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
    if (!$post) throw new Exception('Post not found');

    $title = '📰 New Article Published';
    $body = strlen($post['title']) > 100 ? substr($post['title'],0,97).'...' : $post['title'];
    $notificationData = [
        'post_id' => $post['id'],
        'type' => 'new_post',
        'image' => $post['image'] ?? '',
        'click_action' => 'OPEN_POST',
        'timestamp' => time()
    ];

    $categoryIds = $post['category_ids'] ? explode(',', $post['category_ids']) : [];

    
    $factory = (new Factory)->withServiceAccount('https://anbinvaram.shop/fimapp.json');
    $messaging = $factory->createMessaging();

    $sentCount = 0;
    $failedCount = 0;

    if (!empty($categoryIds)) {
        foreach ($categoryIds as $catId) {
            $stmt = $pdo->prepare("
                SELECT DISTINCT fcm_token 
                FROM users u
                INNER JOIN user_categories uc ON u.id = uc.user_id
                WHERE uc.category_id = ? AND u.fcm_token IS NOT NULL AND u.fcm_token != ''
            ");
            $stmt->execute([$catId]);
            $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($tokens as $token) {
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification(Notification::create($title, $body))
                    ->withData($notificationData);

                try {
                    $messaging->send($message);
                    $sentCount++;
                } catch (\Throwable $e) {
                    $failedCount++;
                    error_log("FCM send failed: ".$e->getMessage());
                }
            }
        }
    } else {
     
        $stmt = $pdo->prepare("SELECT fcm_token FROM users WHERE fcm_token IS NOT NULL AND fcm_token != ''");
        $stmt->execute();
        $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tokens as $token) {
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create($title, $body))
                ->withData($notificationData);
            try {
                $messaging->send($message);
                $sentCount++;
            } catch (\Throwable $e) {
                $failedCount++;
                error_log("FCM send failed: ".$e->getMessage());
            }
        }
    }

    
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

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to send notification',
        'message' => $e->getMessage()
    ]);
}
?>
