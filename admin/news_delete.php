<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';

admin_require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(base_url('admin/news_list.php'));
}

csrf_validate();

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if ($id <= 0) {
    redirect(base_url('admin/news_list.php'));
}

$stmt = db()->prepare('SELECT image_path FROM news WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$row = $stmt->fetch();

$del = db()->prepare('DELETE FROM news WHERE id = ?');
$del->execute([$id]);

// Best-effort file cleanup (ignore failures).
if ($row && !empty($row['image_path'])) {
    $path = rtrim(UPLOAD_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace(['..', '\\'], ['', '/'], (string) $row['image_path']);
    if (is_file($path)) {
        @unlink($path);
    }
}

flash_set('success', 'News deleted.');
redirect(base_url('admin/news_list.php'));

