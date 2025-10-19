<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../resource/conn.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['name'])) {
    echo json_encode(['success' => false, 'error' => 'Category name is required']);
    exit;
}

$pdo = getDB();

try {
    $pdo->beginTransaction();
    $categoryId = $data['id'] ?? null;
    $categoryName = trim($data['name']);
    $subcategories = $data['subcategories'] ?? [];

    if ($categoryId) {
        // Update category
        $stmt = $pdo->prepare('UPDATE categories SET name = ? WHERE id = ?');
        $stmt->execute([$categoryName, $categoryId]);
    } else {
        // Insert new category
        $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
        $stmt->execute([$categoryName]);
        $categoryId = $pdo->lastInsertId();
    }

    // Handle subcategories
    // Remove all old subcategories for update, then re-insert
    $pdo->prepare('DELETE FROM subcategories WHERE category_id = ?')->execute([$categoryId]);
    foreach ($subcategories as $sub) {
        if (!empty($sub['name'])) {
            $stmt = $pdo->prepare('INSERT INTO subcategories (name, category_id) VALUES (?, ?)');
            $stmt->execute([trim($sub['name']), $categoryId]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'id' => $categoryId]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
