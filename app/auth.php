<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function admin_require_login(): void
{
    start_session();
    if (empty($_SESSION['admin_user_id'])) {
        redirect(base_url('admin/login.php'));
    }
}

function admin_user(): ?array
{
    start_session();
    $id = $_SESSION['admin_user_id'] ?? null;
    if (!is_int($id) && !ctype_digit((string) $id)) {
        return null;
    }
    $stmt = db()->prepare('SELECT id, name, email, role FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([(int) $id]);
    $u = $stmt->fetch();
    return is_array($u) ? $u : null;
}

