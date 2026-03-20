<?php
declare(strict_types=1);

// Update these if your MySQL settings differ.
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'newsportal');
define('DB_USER', 'root');
define('DB_PASS', '');

// Base URL (change if you use a virtual host or subfolder).
// Example: http://localhost/newsportal
define('BASE_URL', 'http://localhost/newsportal');

// Upload settings
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads');
define('UPLOAD_URL', BASE_URL . '/assets/uploads');

// Security
define('SESSION_NAME', 'newsportal_session');

