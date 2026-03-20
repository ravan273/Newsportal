<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';

admin_require_login();

$pageTitle = 'Dashboard';

$stats = [
    'news' => (int) (db()->query("SELECT COUNT(*) AS cnt FROM news")->fetch()['cnt'] ?? 0),
    'categories' => (int) (db()->query("SELECT COUNT(*) AS cnt FROM categories")->fetch()['cnt'] ?? 0),
    'countries' => (int) (db()->query("SELECT COUNT(*) AS cnt FROM countries")->fetch()['cnt'] ?? 0),
    'users' => (int) (db()->query("SELECT COUNT(*) AS cnt FROM users")->fetch()['cnt'] ?? 0),
    'pending' => (int) (db()->query("SELECT COUNT(*) AS cnt FROM news WHERE status='pending'")->fetch()['cnt'] ?? 0),
];

require __DIR__ . '/partials/header.php';
?>

<div class="d-flex justify-content-between align-items-end mb-3">
  <div>
    <h1 class="h4 mb-1">Dashboard</h1>
    <div class="text-muted small">Manage your news portal content.</div>
  </div>
  <a class="btn btn-primary btn-sm" href="<?= e(base_url('admin/news_create.php')) ?>">+ Add news</a>
</div>

<?php if ($msg = flash_get('success')): ?>
  <div class="alert alert-success"><?= e($msg) ?></div>
<?php endif; ?>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card card-news">
      <div class="card-body">
        <div class="text-muted small">News</div>
        <div class="display-6 fw-bold"><?= e((string) $stats['news']) ?></div>
        <a class="btn btn-sm btn-outline-secondary mt-2" href="<?= e(base_url('admin/news_list.php')) ?>">Manage</a>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-news">
      <div class="card-body">
        <div class="text-muted small">Categories</div>
        <div class="display-6 fw-bold"><?= e((string) $stats['categories']) ?></div>
        <a class="btn btn-sm btn-outline-secondary mt-2" href="<?= e(base_url('admin/categories.php')) ?>">Manage</a>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-news">
      <div class="card-body">
        <div class="text-muted small">Countries</div>
        <div class="display-6 fw-bold"><?= e((string) $stats['countries']) ?></div>
        <a class="btn btn-sm btn-outline-secondary mt-2" href="<?= e(base_url('admin/countries.php')) ?>">Manage</a>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-news">
      <div class="card-body">
        <div class="text-muted small">Admins</div>
        <div class="display-6 fw-bold"><?= e((string) $stats['users']) ?></div>
        <a class="btn btn-sm btn-outline-secondary mt-2" href="<?= e(base_url('admin/users.php')) ?>">Manage</a>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-news">
      <div class="card-body">
        <div class="text-muted small">Pending</div>
        <div class="display-6 fw-bold"><?= e((string) $stats['pending']) ?></div>
        <a class="btn btn-sm btn-outline-secondary mt-2" href="<?= e(base_url('admin/pending_news.php')) ?>">Review</a>
      </div>
    </div>
  </div>
</div>

<div class="list-group">
  <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="<?= e(base_url('admin/news_list.php')) ?>">
    <span class="fw-semibold">News</span>
    <span class="text-muted small">Create / edit / delete</span>
  </a>
  <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="<?= e(base_url('admin/categories.php')) ?>">
    <span class="fw-semibold">Categories</span>
    <span class="text-muted small">Add / rename / enable-disable</span>
  </a>
  <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="<?= e(base_url('admin/countries.php')) ?>">
    <span class="fw-semibold">Countries</span>
    <span class="text-muted small">Add / enable-disable</span>
  </a>
  <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="<?= e(base_url('admin/users.php')) ?>">
    <span class="fw-semibold">Admin Users</span>
    <span class="text-muted small">Create / reset password</span>
  </a>
  <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="<?= e(base_url('admin/pending_news.php')) ?>">
    <span class="fw-semibold">Pending News</span>
    <span class="text-muted small">Approve / reject</span>
  </a>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>

