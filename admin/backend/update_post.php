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
    
    // Validate required fields
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $subcategory_id = !empty($_POST['subcategory_id']) ? (int)$_POST['subcategory_id'] : null;
    $status = $_POST['status'] ?? 'draft';
    $scheduled_at = $_POST['scheduled_at'] ?? null;
    
    if ($id <= 0) {
        throw new Exception('Valid post ID is required');
    }
    
    if (empty($title)) {
        throw new Exception('Title is required');
    }
    
    if (empty($description)) {
        throw new Exception('Description is required');
    }
    
    if (empty($content)) {
        throw new Exception('Content is required');
    }
    
    if ($category_id <= 0) {
        throw new Exception('Valid category is required');
    }
    
    if (!in_array($status, ['draft', 'published', 'scheduled'])) {
        throw new Exception('Invalid status');
    }
    
    if ($status === 'scheduled' && empty($scheduled_at)) {
        throw new Exception('Schedule date/time is required for scheduled posts');
    }
    
    // Check if post exists
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $existingPost = $stmt->fetch();
    
    if (!$existingPost) {
        throw new Exception('Post not found');
    }
    
    // Handle image upload
    $imagePath = $existingPost['image']; // Keep existing image by default
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
            throw new Exception('Invalid image format. Allowed: JPG, PNG, GIF, WebP');
        }
        
        $fileName = 'post_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $newImagePath = $uploadDir . $fileName;
        
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $newImagePath)) {
            throw new Exception('Failed to upload image');
        }
        
        // Delete old image if exists
        if ($existingPost['image'] && file_exists('../' . $existingPost['image'])) {
            unlink('../' . $existingPost['image']);
        }
        
        // Store relative path for database
        $imagePath = 'uploads/' . $fileName;
    }
    
    // Convert scheduled_at to proper datetime format
    if ($status === 'scheduled' && $scheduled_at) {
        $scheduled_at = date('Y-m-d H:i:s', strtotime($scheduled_at));
    } else {
        $scheduled_at = null;
    }
    
    // Update database
    $stmt = $pdo->prepare("
        UPDATE posts 
        SET title = ?, content = ?, image = ?, category_id = ?, updated_at = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([
        $title,
        $content,
        $imagePath,
        $category_id,
        $id
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Article updated successfully',
        'post_id' => $id,
        'data' => [
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'content' => $content,
            'image' => $imagePath,
            'category_id' => $category_id,
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