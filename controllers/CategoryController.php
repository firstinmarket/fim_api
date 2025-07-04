<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/response.php';


$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data['user_id'] ?? null;
$category_ids = $data['category_ids'] ?? [];

if (!is_numeric($user_id) || !is_array($category_ids) || empty($category_ids)) {
    send_json(['error' => 'Invalid input. Expected user_id and non-empty category_ids array.'], 400);
}


$placeholders = [];
$values = [];

foreach ($category_ids as $category_id) {
    $placeholders[] = "(?, ?)";
    $values[] = $user_id;
    $values[] = $category_id;
}

$sql = "INSERT INTO user_topics (user_id, topic_id) VALUES " . implode(", ", $placeholders);

$stmt = $pdo->prepare($sql);

if ($stmt->execute($values)) {
    send_json([
        'message' => 'Categories saved successfully',
        'affectedRows' => $stmt->rowCount()
    ]);
} else {
    send_json(['error' => 'Failed to insert categories'], 500);
}
?>
