<?php
require_once '../resource/conn.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

$ONESIGNAL_APP_ID = "48392a9a-9863-4cb1-96ba-3a7820029e4f";
$ONESIGNAL_API_KEY = "os_v2_app_ja4svguymngldfv2hj4caau6j547lhlm3vceb6ngp7bpxkzuu74gfdk63ohh45hsips3j6hvj5qz4tqah42pone4bqinzghsap24zia";

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

    $body = strlen($post['title']) > 100 ? substr($post['title'], 0, 97) . '...' : $post['title'];
    $notificationData = [
        'post_id' => $post['id'],
        'type' => 'new_post',
        'image' => $post['image'] ?? '',
        'click_action' => 'OPEN_POST',
        'timestamp' => time()
    ];

    $categoryIds = $post['category_ids'] ? explode(',', $post['category_ids']) : [];
    $playerIds = [];

    if (!empty($categoryIds)) {
        foreach ($categoryIds as $catId) {
            $stmt = $pdo->prepare("
                SELECT DISTINCT onesignal_player_id 
                FROM users u
                INNER JOIN user_categories uc ON u.id = uc.user_id
                WHERE uc.category_id = ? AND u.onesignal_player_id IS NOT NULL AND u.onesignal_player_id != ''
            ");
            $stmt->execute([$catId]);
            $playerIds = array_merge($playerIds, $stmt->fetchAll(PDO::FETCH_COLUMN));
        }
    } else {
        $stmt = $pdo->query("SELECT onesignal_player_id FROM users WHERE onesignal_player_id IS NOT NULL AND onesignal_player_id != ''");
        $playerIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    if (empty($playerIds)) throw new Exception('No registered devices found');


    $payload = [
        'app_id' => isset($ONESIGNAL_APP_ID) && $ONESIGNAL_APP_ID ? (string)$ONESIGNAL_APP_ID : '',
        'include_player_ids' => array_values(array_unique($playerIds)),
        'contents' => ['en' => $body],
        'data' => $notificationData,
        'big_picture' => !empty($post['image']) ? $post['image'] : null,
        'large_icon' => 'https://firstinmarket.com/assets/img/main/icon.png',
        'android_accent_color' => 'FF9933',
        'priority' => 10
    ];

    $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if (!$jsonPayload) {
        error_log('OneSignal payload debug: JSON encoding failed. Payload: ' . print_r($payload, true));
        throw new Exception('Malformed OneSignal payload: JSON encoding failed');
    }
    if (strpos($jsonPayload, 'app_id') === false) {
        error_log('OneSignal payload debug: app_id missing in JSON. JSON: ' . $jsonPayload);
        throw new Exception('Malformed OneSignal payload: app_id missing in JSON');
    }
    if (empty($payload['app_id'])) {
        error_log('OneSignal payload debug: app_id value is empty. Payload: ' . print_r($payload, true));
        throw new Exception('Malformed OneSignal payload: app_id value is empty');
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://onesignal.com/api/v1/notifications');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Basic ' . $ONESIGNAL_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode < 200 || $httpCode >= 300) {
        throw new Exception('OneSignal API Error: ' . $response);
    }

    $sentCount = count($playerIds);

    $stmt = $pdo->prepare("
        INSERT INTO notification_logs (post_id, sent_count, failed_count, sent_at)
        VALUES (?, ?, 0, NOW())
    ");
    $stmt->execute([$postId, $sentCount]);

    echo json_encode([
        'success' => true,
        'message' => 'Notification sent successfully via OneSignal',
        'data' => [
            'sent_count' => $sentCount,
            'post_title' => $post['title'],
            'categories' => $post['category_names'] ?? 'All Users'
        ]
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to send notification',
        'message' => $e->getMessage()
    ]);
}
?>
