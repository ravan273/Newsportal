<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/user_auth.php';

$pageTitle = $pageTitle ?? 'AsuraNews';
$cu = current_user();

$categories = db()->query("SELECT id, name, slug FROM categories WHERE is_active = 1 ORDER BY sort_order, name")->fetchAll();
$countries = db()->query("SELECT id, name, slug FROM countries WHERE is_active = 1 ORDER BY CASE WHEN slug='nepal' THEN 0 ELSE 1 END, name")->fetchAll();
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
  <nav class="navbar navbar-expand-lg navbar-dark bg-brand shadow-sm sticky-top">
    <div class="container">
      <a class="navbar-brand fw-bold" href="<?= e(base_url('index.php')) ?>">AsuraNews</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navMain">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="<?= e(base_url('country.php?c=nepal')) ?>">Nepal</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= e(base_url('country.php')) ?>">World</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Categories</a>
            <ul class="dropdown-menu">
              <?php foreach ($categories as $cat): ?>
                <li><a class="dropdown-item" href="<?= e(base_url('category.php?cat=' . $cat['slug'])) ?>"><?= e($cat['name']) ?></a></li>
              <?php endforeach; ?>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Countries</a>
            <ul class="dropdown-menu dropdown-menu-end">
              <?php foreach ($countries as $co): ?>
                <li><a class="dropdown-item" href="<?= e(base_url('country.php?c=' . $co['slug'])) ?>"><?= e($co['name']) ?></a></li>
              <?php endforeach; ?>
            </ul>
          </li>
        </ul>
        <div class="d-flex align-items-center gap-2 me-2">
          <?php if ($cu): ?>
            <span class="text-white-50 small d-none d-lg-inline">Hi, <?= e($cu['name']) ?></span>
            <a class="btn btn-sm btn-outline-light" href="<?= e(base_url('user/submit.php')) ?>">Submit news</a>
            <a class="btn btn-sm btn-light" href="<?= e(base_url('user/logout.php')) ?>">Logout</a>
          <?php else: ?>
            <a class="btn btn-sm btn-outline-light" href="<?= e(base_url('user/login.php')) ?>">Login</a>
            <a class="btn btn-sm btn-light" href="<?= e(base_url('user/signup.php')) ?>">Sign up</a>
          <?php endif; ?>
        </div>
        <form class="d-flex" role="search" method="get" action="<?= e(base_url('search.php')) ?>">
          <input class="form-control me-2" id="globalSearch" type="search" name="q" placeholder="Search news..." aria-label="Search" autocomplete="off" value="<?= e($_GET['q'] ?? '') ?>">
          <button class="btn btn-light" type="submit">Search</button>
        </form>
      </div>
    </div>
  </nav>

  <main class="container py-4">

