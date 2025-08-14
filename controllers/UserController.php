<?php
require_once __DIR__ . '/../config/db.php';
class UserController {
  public function updateProfile() {
    header('Content-Type: application/json');

    
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid or missing user_id']);
        return;
    }

    $userId = $input['user_id'];

    $name    = $input['name']    ?? null;
    $email   = $input['email']   ?? null;
    $phone   = $input['phone']   ?? null;
    $bio     = $input['bio']     ?? null;
    $profile = $input['profile'] ?? null;

    $updates = [];
    $params = [];

    if ($name !== null)    { $updates[] = "name = ?";    $params[] = $name; }
    if ($email !== null)   { $updates[] = "email = ?";   $params[] = $email; }
    if ($phone !== null)   { $updates[] = "phone = ?";   $params[] = $phone; }
    if ($bio !== null)     { $updates[] = "bio = ?";     $params[] = $bio; }
    if ($profile !== null) { $updates[] = "profile = ?"; $params[] = $profile; }

    if (empty($updates)) {
        echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
        return;
    }

    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
    $params[] = $userId;

    try {
        $conn = getDB();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
    }
}

}
