<?php
require_once __DIR__ . '../../config/db.php';

class PostController {
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
            SELECT p.* FROM posts p
            JOIN user_categories uc ON uc.category_id = p.category_id
            WHERE uc.user_id = ?
            ORDER BY p.created_at DESC
        ');
        $stmt->execute([$user_id]);
        $posts = $stmt->fetchAll();
        return ['status' => 200, 'body' => $posts];
    }

    public static function updateCount($post_id, $field) {
        $allowed = ['likes', 'shares', 'saved_counts'];
        if (!in_array($field, $allowed)) {
            return ['status' => 400, 'body' => ['error' => 'Invalid field']];
        }
        $pdo = getDB();
        $stmt = $pdo->prepare("UPDATE posts SET $field = $field + 1 WHERE id = ?");
        $stmt->execute([$post_id]);
        return ['status' => 200, 'body' => ['success' => true, 'message' => ucfirst($field) . ' updated']];
    }
}
