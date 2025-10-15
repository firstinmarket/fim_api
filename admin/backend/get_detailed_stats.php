<?php

 include 'cors.php';

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../resource/conn.php';

try {
    $pdo = getDB();
    
    $response = array(
        'overview' => array(
            'totalPosts' => 0,
            'totalViews' => 0,
            'totalLikes' => 0,
            'totalShares' => 0,
            'totalSaves' => 0,
            'totalUsers' => 0,
            'verifiedUsers' => 0
        ),
        'recent_stats' => array(
            'postsThisMonth' => 0,
            'postsToday' => 0,
            'newUsersThisMonth' => 0,
            'newUsersToday' => 0
        ),
        'top_posts' => array(),
        'recent_posts' => array()
    );
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM `posts`");
        $stmt->execute();
        $result = $stmt->fetch();
        $response['overview']['totalPosts'] = (int)$result['total'];
        
        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(SUM(`likes_count`), 0) as total_likes,
                COALESCE(SUM(`shares_count`), 0) as total_shares,
                COALESCE(SUM(`saves_count`), 0) as total_saves,
                COALESCE(SUM(`views_count`), 0) as total_views
            FROM `posts`
        ");
        $stmt->execute();
        $result = $stmt->fetch();
        
        $response['overview']['totalLikes'] = (int)$result['total_likes'];
        $response['overview']['totalShares'] = (int)$result['total_shares'];
        $response['overview']['totalSaves'] = (int)$result['total_saves'];
        $response['overview']['totalViews'] = (int)$result['total_views'];
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM `posts` WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");
        $stmt->execute();
        $result = $stmt->fetch();
        $response['recent_stats']['postsThisMonth'] = (int)$result['total'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM `posts` WHERE DATE(created_at) = CURDATE()");
        $stmt->execute();
        $result = $stmt->fetch();
        $response['recent_stats']['postsToday'] = (int)$result['total'];
        
        $stmt = $pdo->prepare("
            SELECT `id`, `title`, `likes_count`, `views_count`, `shares_count` 
            FROM `posts` 
            ORDER BY `likes_count` DESC 
            LIMIT 5
        ");
        $stmt->execute();
        $response['top_posts'] = $stmt->fetchAll();
        
        $stmt = $pdo->prepare("
            SELECT `id`, `title`, `likes_count`, `views_count`, `created_at` 
            FROM `posts` 
            ORDER BY `created_at` DESC 
            LIMIT 5
        ");
        $stmt->execute();
        $response['recent_posts'] = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error fetching posts data: " . $e->getMessage());
    }
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM `users`");
        $stmt->execute();
        $result = $stmt->fetch();
        $response['overview']['totalUsers'] = (int)$result['total'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM `users` WHERE `is_verified` = 1");
        $stmt->execute();
        $result = $stmt->fetch();
        $response['overview']['verifiedUsers'] = (int)$result['total'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM `users` WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");
        $stmt->execute();
        $result = $stmt->fetch();
        $response['recent_stats']['newUsersThisMonth'] = (int)$result['total'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM `users` WHERE DATE(created_at) = CURDATE()");
        $stmt->execute();
        $result = $stmt->fetch();
        $response['recent_stats']['newUsersToday'] = (int)$result['total'];
        
    } catch (PDOException $e) {
        error_log("Error fetching users data: " . $e->getMessage());
    }
    $response['overview']['avgLikesPerPost'] = $response['overview']['totalPosts'] > 0 ? 
        round($response['overview']['totalLikes'] / $response['overview']['totalPosts'], 2) : 0;
    
    $response['overview']['avgViewsPerPost'] = $response['overview']['totalPosts'] > 0 ? 
        round($response['overview']['totalViews'] / $response['overview']['totalPosts'], 2) : 0;
    
    $response['overview']['verificationRate'] = $response['overview']['totalUsers'] > 0 ? 
        round(($response['overview']['verifiedUsers'] / $response['overview']['totalUsers']) * 100, 2) : 0;
    
    echo json_encode([
        'success' => true,
        'data' => $response,
        'message' => 'Detailed statistics fetched successfully',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed',
        'message' => $e->getMessage()
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error occurred',
        'message' => $e->getMessage()
    ]);
}
?>