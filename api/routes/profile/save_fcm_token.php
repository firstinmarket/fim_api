<?php
header('Content-Type: application/json');
require_once '../../config/db.php';

$input = $_POST;
$user_id = isset($input['user_id']) ? trim($input['user_id']) : '';
$fcm_token = isset($input['fcm_token']) ? trim($input['fcm_token']) : '';

if (!$user_id || !$fcm_token) {
	echo json_encode(['success' => false, 'message' => 'Missing user_id or fcm_token']);
	exit;
}

try {
	$pdo = getDB();
	$stmt = $pdo->prepare('UPDATE users SET fcm_token = :fcm_token, fcm_updated_at = NOW() WHERE id = :user_id');
	$stmt->execute([
		':fcm_token' => $fcm_token,
		':user_id' => $user_id
	]);
	if ($stmt->rowCount() > 0) {
		echo json_encode(['success' => true, 'message' => 'FCM token saved']);
	} else {
		echo json_encode(['success' => false, 'message' => 'User not found or token not updated']);
	}
} catch (Exception $e) {
	echo json_encode(['success' => false, 'message' => 'Database error', 'error' => $e->getMessage()]);
}
