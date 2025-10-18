<?php

include 'cors.php';

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../resource/conn.php';

try {
    $pdo = getDB();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $subcategory_id = !empty($_POST['subcategory_id']) ? (int)$_POST['subcategory_id'] : null;
    $status = $_POST['status'] ?? 'draft';
    $scheduled_at = $_POST['scheduled_at'] ?? null;

    if (empty($title)) {
        throw new Exception('Title is required');
    }

    if (empty($content)) {
        throw new Exception('Content is required');
    }

    if ($subcategory_id <= 0) {
        throw new Exception('Valid category is required');
    }

    if (!in_array($status, ['draft', 'published', 'scheduled'])) {
        throw new Exception('Invalid status');
    }

    if ($status === 'scheduled' && empty($scheduled_at)) {
        throw new Exception('Schedule date/time is required for scheduled posts');
    }

    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../api/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
            throw new Exception('Invalid image format. Allowed: JPG, PNG, GIF, WebP');
        }

        $fileName = 'post_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $imagePath = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            throw new Exception('Failed to upload image');
        }

        $imagePath = $fileName;
    }

    if ($status === 'scheduled' && $scheduled_at) {
        $scheduled_at = date('Y-m-d H:i:s', strtotime($scheduled_at));
    } else {
        $scheduled_at = null;
    }

    
    $stmt = $pdo->prepare("
        INSERT INTO posts 
        (title, content, image, category_id, status, scheduled_time, likes_count, shares_count, saves_count, views_count, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, 0, 0, 0, 0, NOW(), NOW())
    ");

    $stmt->execute([
        $title,
        $content,
        $imagePath,
        $subcategory_id,
        $status,
        $scheduled_at
    ]);

    $postId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Article created successfully',
        'post_id' => $postId,
        'data' => [
            'id' => $postId,
            'title' => $title,
            'content' => $content,
            'image' => $imagePath,
           
            'subcategory_id' => $subcategory_id,
            'status' => $status,
            'scheduled_at' => $scheduled_at
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
