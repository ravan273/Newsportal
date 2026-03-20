<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/db.php';

header('Content-Type: application/json; charset=utf-8');

$q = $_GET['q'] ?? '';
$q = is_string($q) ? trim($q) : '';

if ($q === '' || mb_strlen($q, 'UTF-8') < 2) {
    echo json_encode(['items' => []], JSON_UNESCAPED_UNICODE);
    exit;
}

$like = $q . '%';
$stmt = db()->prepare(
    "SELECT title, slug
     FROM news
     WHERE status = 'published' AND title LIKE ?
     ORDER BY COALESCE(published_at, created_at) DESC
     LIMIT 8"
);
$stmt->execute([$like]);
$items = $stmt->fetchAll();

echo json_encode(['items' => $items], JSON_UNESCAPED_UNICODE);

