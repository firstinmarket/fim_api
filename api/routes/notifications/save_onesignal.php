<?php
require_once '../../config/cors.php';
require_once '../../config/db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$userId = $input['user_id'] ?? null;
$playerId = $input['onesignal_player_id'] ?? null;

if (!$userId || !$playerId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("UPDATE users SET onesignal_player_id = ? WHERE id = ?");
$stmt->execute([$playerId, $userId]);

echo json_encode(['success' => true, 'message' => 'Player ID updated']);
