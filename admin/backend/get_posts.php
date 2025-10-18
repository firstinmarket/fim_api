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
    $stmt = $pdo->prepare("SELECT id, title, image, content, likes_count, shares_count, saves_count, views_count, category_id, created_at, updated_at, status, scheduled_time FROM posts WHERE 1 ORDER BY created_at DESC");
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