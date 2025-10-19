<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../../resource/conn.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Missing user ID.'
    ]);
    exit;
}

$userId = intval($data['id']);

try {
    $pdo = getDB();
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'User deleted successfully.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'User not found or already deleted.'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
}
?>
