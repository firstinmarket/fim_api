<?php
require_once __DIR__ . '/../../config/db.php';

class CommentController {
    public static function addComment($data) {
        $post_id = $data['post_id'] ?? '';
        $name = $data['name'] ?? '';
        $text = $data['text'] ?? '';
        if (!$post_id || !$name || !$text) {
            return ['status' => 400, 'body' => ['error' => 'Post ID, name, and text are required.']];
        }
        $pdo = getDB();
        $stmt = $pdo->prepare('INSERT INTO comments (post_id, name, text) VALUES (?, ?, ?)');
        $stmt->execute([$post_id, $name, $text]);
        // Update comments_count in posts
        $update = $pdo->prepare('UPDATE posts SET comments_count = comments_count + 1 WHERE id = ?');
        $update->execute([$post_id]);
        return ['status' => 201, 'body' => ['success' => true, 'message' => 'Comment added successfully']];
    }

    public static function getComments($post_id) {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT * FROM comments WHERE post_id = ? ORDER BY created_at DESC');
        $stmt->execute([$post_id]);
        $comments = $stmt->fetchAll();
        return ['status' => 200, 'body' => $comments];
    }
}
