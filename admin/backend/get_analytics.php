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

    // Most viewed post
    $stmt = $pdo->prepare("SELECT id, title, views_count as views FROM posts ORDER BY views_count DESC LIMIT 1");
    $stmt->execute();
    $mostViewed = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$mostViewed) {
        $mostViewed = ['id' => null, 'title' => 'N/A', 'views' => 0];
    }

    // Most liked post
    $stmt = $pdo->prepare("SELECT id, title, likes_count as likes FROM posts ORDER BY likes_count DESC LIMIT 1");
    $stmt->execute();
    $mostLiked = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$mostLiked) {
        $mostLiked = ['id' => null, 'title' => 'N/A', 'likes' => 0];
    }

    try {
        $stmt = $pdo->prepare("SELECT u.id, u.name, COUNT(p.id) as posts FROM users u LEFT JOIN posts p ON u.id = p.user_id GROUP BY u.id, u.name ORDER BY posts DESC LIMIT 1");
        $stmt->execute();
        $topAuthor = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $stmt = $pdo->prepare("SELECT u.id, u.name, COUNT(p.id) as posts FROM users u LEFT JOIN posts p ON u.id = p.id GROUP BY u.id, u.name ORDER BY posts DESC LIMIT 1");
        $stmt->execute();
        $topAuthor = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    if (!$topAuthor) {
        $topAuthor = ['id' => null, 'name' => 'N/A', 'posts' => 0];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'mostViewed' => $mostViewed,
            'mostLiked' => $mostLiked,
            'topAuthor' => $topAuthor
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
