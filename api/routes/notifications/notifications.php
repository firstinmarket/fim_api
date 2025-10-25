<?php


header('Content-Type: application/json');
require_once '../../config/cors.php';
require_once '../../config/db.php';

try {
    $pdo = getDB();
    
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
    
    // Only show notifications from last 24 hours
    $hours = isset($_GET['hours']) ? (int)$_GET['hours'] : 24;
    
    $query = "
        SELECT 
            nl.id,
            nl.post_id,
            nl.sent_count,
            nl.failed_count,
            nl.sent_at,
            nl.sent_by,
            nl.notification_type,
            p.title as post_title,
            p.image as post_image,
            p.status as post_status,
            p.content as post_content,
            GROUP_CONCAT(DISTINCT c.name) as categories
        FROM notification_logs nl
        INNER JOIN posts p ON nl.post_id = p.id
        LEFT JOIN post_categories pc ON p.id = pc.post_id
        LEFT JOIN categories c ON pc.category_id = c.id
        WHERE nl.sent_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
    ";
    
    $params = [$hours];
    
    if ($user_id) {
        $query .= " AND nl.user_id = ?";
        $params[] = $user_id;
    }
    
    $query .= " GROUP BY nl.id ORDER BY nl.sent_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $countQuery = "SELECT COUNT(DISTINCT nl.id) as total FROM notification_logs nl";
    if ($user_id) {
        $countQuery .= " WHERE nl.user_id = ?";
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute([$user_id]);
    } else {
        $countStmt = $pdo->query($countQuery);
    }
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $formattedNotifications = [];
    foreach ($notifications as $notification) {
        $formattedNotifications[] = [
            'id' => (int)$notification['id'],
            'post_id' => (int)$notification['post_id'],
            'sent_count' => (int)$notification['sent_count'],
            'failed_count' => (int)$notification['failed_count'],
            'sent_at' => $notification['sent_at'],
            'sent_by' => $notification['sent_by'],
            'notification_type' => $notification['notification_type'],
            'post' => [
                'title' => $notification['post_title'],
                'image' => $notification['post_image'] ? 'https://www.firstinmarket.com/app/api/uploads/' . $notification['post_image'] : null,
                'status' => $notification['post_status'],
                'content' => $notification['post_content'] ?? null,
                'categories' => $notification['categories']
            ]
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $formattedNotifications,
        'pagination' => [
            'total' => (int)$totalCount,
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => ($offset + $limit) < $totalCount
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch notifications',
        'message' => $e->getMessage()
    ]);
}
