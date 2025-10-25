

<?php
require_once '../../config/db.php';

class PostController {

    public static function getLikeStatus($post_id, $user_id) {
        $pdo = getDB();
        // Get likes_count
        $stmt = $pdo->prepare('SELECT likes_count FROM posts WHERE id = ?');
        $stmt->execute([$post_id]);
        $post = $stmt->fetch();
        $likes_count = $post ? $post['likes_count'] : 0;
        // Check if user has liked
        $stmt = $pdo->prepare('SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?');
        $stmt->execute([$post_id, $user_id]);
        $is_liked = $stmt->fetch() ? true : false;
        return [
            'status' => 200,
            'body' => [
                'likes_count' => $likes_count,
                'is_liked' => $is_liked
            ]
        ];
    }

    public static function getSaveStatus($post_id, $user_id) {
        $pdo = getDB();
        // Get saves_count
        $stmt = $pdo->prepare('SELECT saves_count FROM posts WHERE id = ?');
        $stmt->execute([$post_id]);
        $post = $stmt->fetch();
        $saves_count = $post ? $post['saves_count'] : 0;
        // Check if user has saved
        $stmt = $pdo->prepare('SELECT id FROM saved_counts WHERE post_id = ? AND user_id = ?');
        $stmt->execute([$post_id, $user_id]);
        $is_saved = $stmt->fetch() ? true : false;
        return [
            'status' => 200,
            'body' => [
                'saves_count' => $saves_count,
                'is_saved' => $is_saved
            ]
        ];
    }
    public static function toggleSave($post_id, $user_id, $add = true) {
        $pdo = getDB();
        if ($add) {
            $stmt = $pdo->prepare('SELECT id FROM saved_counts WHERE post_id = ? AND user_id = ?');
            $stmt->execute([$post_id, $user_id]);
            if (!$stmt->fetch()) {
                $stmt = $pdo->prepare('INSERT INTO saved_counts (post_id, user_id, saved_at) VALUES (?, ?, NOW())');
                $stmt->execute([$post_id, $user_id]);
                $update = $pdo->prepare('UPDATE posts SET saves_count = saves_count + 1 WHERE id = ?');
                $update->execute([$post_id]);
                return ['status' => 200, 'body' => ['success' => true, 'saved' => true, 'message' => 'Post saved']];
            } else {
                return ['status' => 200, 'body' => ['success' => true, 'saved' => true, 'message' => 'Already saved']];
            }
        } else {
            $stmt = $pdo->prepare('SELECT id FROM saved_counts WHERE post_id = ? AND user_id = ?');
            $stmt->execute([$post_id, $user_id]);
            if ($stmt->fetch()) {
                $stmt = $pdo->prepare('DELETE FROM saved_counts WHERE post_id = ? AND user_id = ?');
                $stmt->execute([$post_id, $user_id]);
                $update = $pdo->prepare('UPDATE posts SET saves_count = saves_count - 1 WHERE id = ?');
                $update->execute([$post_id]);
                return ['status' => 200, 'body' => ['success' => true, 'saved' => false, 'message' => 'Post unsaved']];
            } else {
                return ['status' => 200, 'body' => ['success' => true, 'saved' => false, 'message' => 'Not saved yet']];
            }
        }
    }
  

    public static function getPosts($user_id = null) {
        $pdo = getDB();
        
        try {
            if ($user_id) {
                $userStmt = $pdo->prepare('SELECT language FROM users WHERE id = ?');
                $userStmt->execute([$user_id]);
                $userData = $userStmt->fetch();
                $userLanguage = $userData ? ($userData['language'] ?? 'english') : 'english';
                
                $stmt = $pdo->prepare('
                    SELECT p.*,
                           GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ", ") AS category_names,
                           GROUP_CONCAT(DISTINCT c.id ORDER BY c.id) AS category_ids,
                           CASE WHEN pl.id IS NOT NULL THEN 1 ELSE 0 END AS is_liked,
                           CASE WHEN sc.id IS NOT NULL THEN 1 ELSE 0 END AS is_saved,
                           ? AS user_language
                    FROM posts p 
                    LEFT JOIN post_categories pc ON pc.post_id = p.id
                    LEFT JOIN categories c ON pc.category_id = c.id
                    LEFT JOIN post_likes pl ON pl.post_id = p.id AND pl.user_id = ?
                    LEFT JOIN saved_counts sc ON sc.post_id = p.id AND sc.user_id = ?
                    WHERE p.language = ?
                        AND p.status = "published"
                    GROUP BY p.id
                    ORDER BY p.created_at DESC
                ');
                $stmt->execute([$userLanguage, $user_id, $user_id, $userLanguage]);
                
                error_log("PostController: Fetching all PUBLISHED posts for user $user_id with language: $userLanguage");
            } else {
                $stmt = $pdo->prepare('
                    SELECT p.*,
                           GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ", ") AS category_names,
                           GROUP_CONCAT(DISTINCT c.id ORDER BY c.id) AS category_ids
                    FROM posts p
                    LEFT JOIN post_categories pc ON pc.post_id = p.id
                    LEFT JOIN categories c ON pc.category_id = c.id
                    WHERE p.status = "published"
                    GROUP BY p.id
                    ORDER BY p.created_at DESC
                ');
                $stmt->execute();
                
                error_log("PostController: Fetching all PUBLISHED posts (no user filter)");
            }
            
            $posts = $stmt->fetchAll();
            error_log("PostController: Found " . count($posts) . " published posts");
            
            return ['status' => 200, 'body' => $posts];
            
        } catch (PDOException $e) {
            error_log("PostController: Database error in getPosts: " . $e->getMessage());
            return ['status' => 500, 'body' => ['error' => 'Database error occurred']];
        } catch (Exception $e) {
            error_log("PostController: General error in getPosts: " . $e->getMessage());
            return ['status' => 500, 'body' => ['error' => 'An error occurred while fetching posts']];
        }
    }

    public static function getPostsByUserCategories($user_id) {
        $pdo = getDB();
        
        try {
            $userStmt = $pdo->prepare('SELECT language FROM users WHERE id = ?');
            $userStmt->execute([$user_id]);
            $userData = $userStmt->fetch();
            $userLanguage = $userData ? ($userData['language'] ?? 'english') : 'english';
            
            $stmt = $pdo->prepare('
                SELECT p.*,
                       GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ", ") AS category_names,
                       GROUP_CONCAT(DISTINCT c.id ORDER BY c.id) AS category_ids,
                       CASE WHEN pl.id IS NOT NULL THEN 1 ELSE 0 END AS is_liked,
                       CASE WHEN sc.id IS NOT NULL THEN 1 ELSE 0 END AS is_saved,
                       ? AS user_language
                FROM posts p
                INNER JOIN post_categories pc ON pc.post_id = p.id
                INNER JOIN user_categories uc ON uc.category_id = pc.category_id AND uc.user_id = ?
                LEFT JOIN post_categories pc_all ON pc_all.post_id = p.id
                LEFT JOIN categories c ON pc_all.category_id = c.id
                LEFT JOIN post_likes pl ON pl.post_id = p.id AND pl.user_id = ?
                LEFT JOIN saved_counts sc ON sc.post_id = p.id AND sc.user_id = ?
                WHERE p.language = ?
                    AND p.status = "published"
                GROUP BY p.id
                ORDER BY p.created_at DESC
            ');
            $stmt->execute([$userLanguage, $user_id, $user_id, $user_id, $userLanguage]);
            $posts = $stmt->fetchAll();
            
            error_log("PostController: Fetching PUBLISHED posts for user $user_id with language: $userLanguage");
            error_log("PostController: Found " . count($posts) . " published posts matching user categories and language");
            
            return ['status' => 200, 'body' => $posts];
            
        } catch (PDOException $e) {
            error_log("PostController: Database error in getPostsByUserCategories: " . $e->getMessage());
            return ['status' => 500, 'body' => ['error' => 'Database error occurred']];
        } catch (Exception $e) {
            error_log("PostController: General error in getPostsByUserCategories: " . $e->getMessage());
            return ['status' => 500, 'body' => ['error' => 'An error occurred while fetching posts']];
        }
    }

    public static function getSavedPostsByUser($user_id) {
        $pdo = getDB();
        
        try {
            // First, get the user's language preference
            $userStmt = $pdo->prepare('SELECT language FROM users WHERE id = ?');
            $userStmt->execute([$user_id]);
            $userData = $userStmt->fetch();
            $userLanguage = $userData ? ($userData['language'] ?? 'english') : 'english';
            
            $stmt = $pdo->prepare('
                SELECT p.*, 
                       GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ", ") AS category_names,
                       GROUP_CONCAT(DISTINCT c.id ORDER BY c.id) AS category_ids,
                       sc.saved_at,
                       CASE WHEN pl.id IS NOT NULL THEN 1 ELSE 0 END AS is_liked,
                       1 AS is_saved,
                       ? AS user_language
                FROM saved_counts sc
                JOIN posts p ON sc.post_id = p.id
                LEFT JOIN post_categories pc ON pc.post_id = p.id
                LEFT JOIN categories c ON pc.category_id = c.id
                LEFT JOIN post_likes pl ON pl.post_id = p.id AND pl.user_id = ?
                WHERE sc.user_id = ?
                    AND p.language = ?
                    AND p.status = "published"
                GROUP BY p.id, sc.saved_at
                ORDER BY sc.saved_at DESC
            ');
            $stmt->execute([$userLanguage, $user_id, $user_id, $userLanguage]);
            $posts = $stmt->fetchAll();
            
            // Log the language filtering for debugging
            error_log("PostController: Fetching saved PUBLISHED posts for user $user_id with language: $userLanguage");
            error_log("PostController: Found " . count($posts) . " saved published posts matching language filter");
            
            return ['status' => 200, 'body' => $posts];
            
        } catch (PDOException $e) {
            error_log("PostController: Database error in getSavedPostsByUser: " . $e->getMessage());
            return ['status' => 500, 'body' => ['error' => 'Database error occurred']];
        } catch (Exception $e) {
            error_log("PostController: General error in getSavedPostsByUser: " . $e->getMessage());
            return ['status' => 500, 'body' => ['error' => 'An error occurred while fetching saved posts']];
        }
    }

    public static function updateCount($post_id, $field, $user_id = null) {
        $allowed = ['shares_count', 'saves_count', 'views_count'];
        if (in_array($field, ['like', 'unlike'])) {
            if (empty($user_id)) {
                return ['status' => 400, 'body' => ['error' => 'Missing user_id for like/unlike']];
            }
            if ($field === 'like') {
                return self::toggleLike($post_id, $user_id, true);
            } else {
                return self::toggleLike($post_id, $user_id, false);
            }
        }
        if (in_array($field, ['save', 'unsave'])) {
            if (empty($user_id)) {
                return ['status' => 400, 'body' => ['error' => 'Missing user_id for save/unsave']];
            }
            if ($field === 'save') {
                return self::toggleSave($post_id, $user_id, true);
            } else {
                return self::toggleSave($post_id, $user_id, false);
            }
        }
        if (!in_array($field, $allowed)) {
            return ['status' => 400, 'body' => ['error' => 'Invalid field']];
        }
        $pdo = getDB();
        $stmt = $pdo->prepare("UPDATE posts SET $field = $field + 1 WHERE id = ?");
        $stmt->execute([$post_id]);
        return ['status' => 200, 'body' => ['success' => true, 'message' => ucfirst($field) . ' updated']];
    }

    public static function toggleLike($post_id, $user_id, $add = true) {
        $pdo = getDB();
        if ($add) {
            // Add like if not already liked
            $stmt = $pdo->prepare('SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?');
            $stmt->execute([$post_id, $user_id]);
            if (!$stmt->fetch()) {
                $stmt = $pdo->prepare('INSERT INTO post_likes (post_id, user_id, saved_at) VALUES (?, ?, NOW())');
                $stmt->execute([$post_id, $user_id]);
                $update = $pdo->prepare('UPDATE posts SET likes_count = likes_count + 1 WHERE id = ?');
                $update->execute([$post_id]);
                return ['status' => 200, 'body' => ['success' => true, 'liked' => true, 'message' => 'Like added']];
            } else {
                return ['status' => 200, 'body' => ['success' => true, 'liked' => true, 'message' => 'Already liked']];
            }
        } else {
            // Remove like if exists
            $stmt = $pdo->prepare('SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?');
            $stmt->execute([$post_id, $user_id]);
            if ($stmt->fetch()) {
                $stmt = $pdo->prepare('DELETE FROM post_likes WHERE post_id = ? AND user_id = ?');
                $stmt->execute([$post_id, $user_id]);
                $update = $pdo->prepare('UPDATE posts SET likes_count = likes_count - 1 WHERE id = ?');
                $update->execute([$post_id]);
                return ['status' => 200, 'body' => ['success' => true, 'liked' => false, 'message' => 'Like removed']];
            } else {
                return ['status' => 200, 'body' => ['success' => true, 'liked' => false, 'message' => 'Not liked yet']];
            }
        }
    }
}
