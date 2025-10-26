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
            p.id, 
            p.title, 
            p.image, 
            p.content, 
            p.likes_count, 
            p.shares_count, 
            p.saves_count, 
            p.views_count, 
            p.language, 
            p.status, 
            p.scheduled_time,
            p.created_at, 
            p.updated_at,
            pc.category_id,
            CASE WHEN nl.id IS NOT NULL THEN 1 ELSE 0 END AS notification_sent,
            nl.sent_count,
            nl.sent_at AS notification_sent_at
        FROM posts p
        LEFT JOIN post_categories pc ON p.id = pc.post_id
        LEFT JOIN notification_logs nl ON p.id = nl.post_id
        ORDER BY p.created_at DESC
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
