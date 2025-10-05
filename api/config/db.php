<?php
$host = 'localhost';
$db   = 'anbinvar_fim';
$user = 'anbinvar_fim';
$pass = 'anbinvar_fim';

// $host = 'localhost';
// $db   = 'FIMNEWS';
// $user = 'root';
// $pass = '';

$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

function getDB() {
    global $pdo;
    return $pdo;
}
