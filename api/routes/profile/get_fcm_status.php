<?php
header('Content-Type: application/json');
require_once '../../config/db.php';
$user_id = isset($_GET['user_id']) ? trim($_GET['user_id']) : '';
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Missing user_id']);
    exit;
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT fcm_token FROM users WHERE id = :user_id');
    $stmt->execute([':user_id' => $user_id]);
    $row = $stmt->fetch();
    $enabled = !empty($row['fcm_token']);
    echo json_encode(['success' => true, 'enabled' => $enabled]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error', 'error' => $e->getMessage()]);
}
