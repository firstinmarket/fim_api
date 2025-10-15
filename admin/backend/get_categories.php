<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../resource/conn.php';

try {
    $pdo = getDB();

    // Single JOIN query to fetch categories and subcategories
    $sql = "
        SELECT 
            c.id AS category_id, 
            c.name AS category_name, 
            c.created_at AS category_created_at,
            s.id AS subcategory_id,
            s.name AS subcategory_name,
            s.created_at AS subcategory_created_at
        FROM categories c
        LEFT JOIN subcategories s ON c.id = s.category_id
        ORDER BY c.name ASC, s.name ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $categories = [];

    foreach ($rows as $row) {
        $catId = (int)$row['category_id'];

        if (!isset($categories[$catId])) {
            $categories[$catId] = [
                'id' => $catId,
                'name' => $row['category_name'],
                'created_at' => $row['category_created_at'],
                'subcategories' => []
            ];
        }

        if (!empty($row['subcategory_id'])) {
            $categories[$catId]['subcategories'][] = [
                'id' => (int)$row['subcategory_id'],
                'name' => $row['subcategory_name'],
                'created_at' => $row['subcategory_created_at']
            ];
        }
    }

    $categories = array_values($categories);

    echo json_encode([
        'success' => true,
        'data' => $categories,
        'message' => 'Categories and subcategories fetched successfully',
        'timestamp' => date('Y-m-d H:i:s'),
        'total_categories' => count($categories),
        'total_subcategories' => array_sum(array_map(fn($c) => count($c['subcategories']), $categories))
    ]);
} catch (Exception $e) {
    // If database tables don't exist, provide fallback data
    $categories = [
        [
            'id' => 1,
            'name' => 'Technology',
            'created_at' => date('Y-m-d H:i:s'),
            'subcategories' => [
                ['id' => 1, 'name' => 'AI & ML', 'created_at' => date('Y-m-d H:i:s')],
                ['id' => 2, 'name' => 'Mobile Tech', 'created_at' => date('Y-m-d H:i:s')],
                ['id' => 3, 'name' => 'Startups', 'created_at' => date('Y-m-d H:i:s')]
            ]
        ],
        [
            'id' => 2,
            'name' => 'Business',
            'created_at' => date('Y-m-d H:i:s'),
            'subcategories' => [
                ['id' => 4, 'name' => 'Markets', 'created_at' => date('Y-m-d H:i:s')],
                ['id' => 5, 'name' => 'Economy', 'created_at' => date('Y-m-d H:i:s')],
                ['id' => 6, 'name' => 'Finance', 'created_at' => date('Y-m-d H:i:s')]
            ]
        ],
        [
            'id' => 3,
            'name' => 'Sports',
            'created_at' => date('Y-m-d H:i:s'),
            'subcategories' => [
                ['id' => 7, 'name' => 'Cricket', 'created_at' => date('Y-m-d H:i:s')],
                ['id' => 8, 'name' => 'Football', 'created_at' => date('Y-m-d H:i:s')],
                ['id' => 9, 'name' => 'Olympics', 'created_at' => date('Y-m-d H:i:s')]
            ]
        ],
        [
            'id' => 4,
            'name' => 'Politics',
            'created_at' => date('Y-m-d H:i:s'),
            'subcategories' => [
                ['id' => 10, 'name' => 'Elections', 'created_at' => date('Y-m-d H:i:s')],
                ['id' => 11, 'name' => 'Policy', 'created_at' => date('Y-m-d H:i:s')]
            ]
        ],
        [
            'id' => 5,
            'name' => 'Health',
            'created_at' => date('Y-m-d H:i:s'),
            'subcategories' => [
                ['id' => 12, 'name' => 'Medical', 'created_at' => date('Y-m-d H:i:s')],
                ['id' => 13, 'name' => 'Wellness', 'created_at' => date('Y-m-d H:i:s')]
            ]
        ]
    ];

    echo json_encode([
        'success' => true,
        'data' => $categories,
        'message' => 'Categories and subcategories fetched successfully (fallback data)',
        'timestamp' => date('Y-m-d H:i:s'),
        'total_categories' => count($categories),
        'total_subcategories' => array_sum(array_map(fn($c) => count($c['subcategories']), $categories))
    ]);
}
?>
