<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../resource/conn.php';

try {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT 
            posts.id, 
            posts.title, 
            posts.image, 
            posts.content, 
            posts.likes_count, 
            posts.shares_count, 
            posts.saves_count, 
            posts.views_count, 
            posts.category_id, 
            posts.category_id, 
            subcategories.name AS subcategory_name, 
            posts.created_at, 
            posts.updated_at, 
            posts.language, 
            posts.status, 
            posts.scheduled_time,
            CASE WHEN nl.id IS NOT NULL THEN 1 ELSE 0 END as notification_sent,
            nl.sent_count,
            nl.sent_at as notification_sent_at
        FROM posts 
        LEFT JOIN subcategories ON posts.category_id = subcategories.id 
        LEFT JOIN notification_logs nl ON posts.id = nl.post_id
        ORDER BY posts.created_at DESC
    ");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'success' => true,
        'data' => $posts,
        'message' => 'Posts fetched successfully',
        'timestamp' => date('Y-m-d H:i:s'),
        'total_posts' => count($posts)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
}
?>