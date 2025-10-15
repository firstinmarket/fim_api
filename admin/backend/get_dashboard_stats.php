<?php

 include 'cors.php';

 
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../resource/conn.php';

try {
    $pdo = getDB();
    
    $stats = array(
        'totalPosts' => 0,
        'totalViews' => 0,
        'totalLikes' => 0,
        'totalShares' => 0,
        'totalSaves' => 0,
        'totalUsers' => 0,
        'verifiedUsers' => 0
    );
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM `posts`");
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['totalPosts'] = (int)$result['total'];
        
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(`likes_count`), 0) as total FROM `posts`");
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['totalLikes'] = (int)$result['total'];
        
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(`shares_count`), 0) as total FROM `posts`");
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['totalShares'] = (int)$result['total'];
        
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(`saves_count`), 0) as total FROM `posts`");
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['totalSaves'] = (int)$result['total'];
        
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(`views_count`), 0) as total FROM `posts`");
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['totalViews'] = (int)$result['total'];
        
    } catch (PDOException $e) {
        error_log("Error fetching posts stats: " . $e->getMessage());
    }
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM `users`");
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['totalUsers'] = (int)$result['total'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM `users` WHERE `is_verified` = 1");
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['verifiedUsers'] = (int)$result['total'];
        
    } catch (PDOException $e) {
        error_log("Error fetching users stats: " . $e->getMessage());
    }
    $stats['avgLikesPerPost'] = $stats['totalPosts'] > 0 ? round($stats['totalLikes'] / $stats['totalPosts'], 2) : 0;
    $stats['avgViewsPerPost'] = $stats['totalPosts'] > 0 ? round($stats['totalViews'] / $stats['totalPosts'], 2) : 0;
    $stats['verificationRate'] = $stats['totalUsers'] > 0 ? round(($stats['verifiedUsers'] / $stats['totalUsers']) * 100, 2) : 0;
    echo json_encode([
        'success' => true,
        'data' => $stats,
        'message' => 'Statistics fetched successfully',
        'timestamp' => date('Y-m-d H:i:s'),
        'query_info' => [
            'posts_table_accessed' => true,
            'users_table_accessed' => true
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed',
        'message' => $e->getMessage(),
        'data' => array(
            'totalPosts' => 0,
            'totalViews' => 0,
            'totalLikes' => 0,
            'totalShares' => 0,
            'totalSaves' => 0,
            'totalUsers' => 0,
            'verifiedUsers' => 0
        )
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while fetching statistics',
        'message' => $e->getMessage(),
        'data' => array(
            'totalPosts' => 0,
            'totalViews' => 0,
            'totalLikes' => 0,
            'totalShares' => 0,
            'totalSaves' => 0,
            'totalUsers' => 0,
            'verifiedUsers' => 0
        )
    ]);
}
?>