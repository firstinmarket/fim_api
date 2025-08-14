<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/response.php';


$sql = "SELECT id, name, `desc`, content, likes, shares, image, views, created_at FROM posts ORDER BY created_at DESC";
$stmt = $pdo->query($sql);

$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

send_json([
    'message' => 'Posts fetched successfully',
    'posts' => $posts
]);
?>
