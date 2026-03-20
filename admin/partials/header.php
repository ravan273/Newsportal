<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/helpers.php';
require_once __DIR__ . '/../../app/auth.php';

$pageTitle = $pageTitle ?? 'Admin';
$u = admin_user();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= e(base_url('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-brand shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold" href="<?= e(base_url('admin/index.php')) ?>">Admin</a>
      <div class="d-flex align-items-center gap-2">
        <a class="btn btn-sm btn-light" href="<?= e(base_url('index.php')) ?>">View site</a>
        <?php if ($u): ?>
          <span class="text-white-50 small d-none d-md-inline"><?= e($u['email']) ?></span>
          <a class="btn btn-sm btn-outline-light" href="<?= e(base_url('admin/change_password.php')) ?>">Change password</a>
          <a class="btn btn-sm btn-outline-light" href="<?= e(base_url('admin/logout.php')) ?>">Logout</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <main class="container py-4">

