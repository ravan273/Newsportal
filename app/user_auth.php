<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function user_require_login(): void
{
    start_session();
    if (empty($_SESSION['user_user_id'])) {
        redirect(base_url('user/login.php'));
    }
}

function current_user(): ?array
{
    start_session();
    $id = $_SESSION['user_user_id'] ?? null;
    if (!is_int($id) && !ctype_digit((string) $id)) {
        return null;
    }
    $stmt = db()->prepare("SELECT id, name, email, role, is_active FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([(int) $id]);
    $u = $stmt->fetch();
    if (!$u || (int) ($u['is_active'] ?? 0) !== 1) {
        return null;
    }
    return $u;
}

function user_logout(): void
{
    start_session();
    unset($_SESSION['user_user_id']);
    session_regenerate_id(true);
}

