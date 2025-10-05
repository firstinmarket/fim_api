

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
    public static function addPost($data) {
        $title = $data['title'] ?? '';
        $image = $data['image'] ?? '';
        $description = $data['description'] ?? '';
        $content = $data['content'] ?? '';
        if (!$title || !$content) {
            return ['status' => 400, 'body' => ['error' => 'Title and content are required.']];
        }
        $pdo = getDB();
        $stmt = $pdo->prepare('INSERT INTO posts (title, image, description, content) VALUES (?, ?, ?, ?)');
        $stmt->execute([$title, $image, $description, $content]);
        return ['status' => 201, 'body' => ['success' => true, 'message' => 'Post added successfully']];
    }

    public static function getPosts() {
        $pdo = getDB();
        $stmt = $pdo->query('SELECT * FROM posts ORDER BY created_at DESC');
        $posts = $stmt->fetchAll();
        return ['status' => 200, 'body' => $posts];
    }

    public static function getPostsByUserCategories($user_id) {
        $pdo = getDB();
        $stmt = $pdo->prepare('
            SELECT p.*, s.name AS category_name,
                   CASE WHEN pl.id IS NOT NULL THEN 1 ELSE 0 END AS is_liked,
                   CASE WHEN sc.id IS NOT NULL THEN 1 ELSE 0 END AS is_saved
            FROM posts p
            JOIN user_categories uc ON uc.subcategory_id = p.category_id
            JOIN subcategories s ON p.category_id = s.id
            LEFT JOIN post_likes pl ON pl.post_id = p.id AND pl.user_id = ?
            LEFT JOIN saved_counts sc ON sc.post_id = p.id AND sc.user_id = ?
            WHERE uc.user_id = ?
            ORDER BY p.created_at DESC
        ');
        $stmt->execute([$user_id, $user_id, $user_id]);
        $posts = $stmt->fetchAll();
        return ['status' => 200, 'body' => $posts];
    }

    public static function getSavedPostsByUser($user_id) {
        $pdo = getDB();
        $stmt = $pdo->prepare('
            SELECT p.*, s.name AS category_name, sc.saved_at,
                   CASE WHEN pl.id IS NOT NULL THEN 1 ELSE 0 END AS is_liked,
                   1 AS is_saved
            FROM saved_counts sc
            JOIN posts p ON sc.post_id = p.id
            JOIN subcategories s ON p.category_id = s.id
            LEFT JOIN post_likes pl ON pl.post_id = p.id AND pl.user_id = ?
            WHERE sc.user_id = ?
            ORDER BY sc.saved_at DESC
        ');
        $stmt->execute([$user_id, $user_id]);
        $posts = $stmt->fetchAll();
        return ['status' => 200, 'body' => $posts];
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
