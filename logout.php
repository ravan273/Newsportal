<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/user_auth.php';

user_logout();
redirect(base_url('index.php'));

