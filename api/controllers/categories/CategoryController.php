<?php
require_once __DIR__ . '/../../config/db.php';

class CategoryController {
    public static function getCategories() {
        $pdo = getDB();
        $stmt = $pdo->query('SELECT id, name, created_at FROM categories ORDER BY name ASC');
        $categories = $stmt->fetchAll();
        return ['status' => 200, 'body' => $categories];
    }

    public static function getSubcategories() {
        $pdo = getDB();
        $stmt = $pdo->query('SELECT id, category_id, name, created_at FROM subcategories ORDER BY name ASC');
        $subcategories = $stmt->fetchAll();
        return ['status' => 200, 'body' => $subcategories];
    }

    public static function getCategoriesWithSubcategories() {
        $pdo = getDB();
        $categories = $pdo->query('SELECT id, name, created_at FROM categories ORDER BY name ASC')->fetchAll();
        $subcategories = $pdo->query('SELECT id, category_id, name, created_at FROM subcategories ORDER BY name ASC')->fetchAll();
        $catMap = [];
        foreach ($categories as $cat) {
            $cat['subcategories'] = [];
            $catMap[$cat['id']] = $cat;
        }
        foreach ($subcategories as $sub) {
            if (isset($catMap[$sub['category_id']])) {
                $catMap[$sub['category_id']]['subcategories'][] = $sub;
            }
        }
        return ['status' => 200, 'body' => array_values($catMap)];
    }
}
