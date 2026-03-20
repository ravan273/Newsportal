<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/helpers.php';

start_session();
unset($_SESSION['admin_user_id']);
session_regenerate_id(true);
redirect(base_url('admin/login.php'));

