<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';

admin_require_login();

$q = $_GET['q'] ?? '';
$q = is_string($q) ? trim($q) : '';
$page = isset($_GET['p']) ? (int) $_GET['p'] : 1;
$page = max(1, $page);
$perPage = 12;
$offset = ($page - 1) * $perPage;

$where = '';
$params = [];
if ($q !== '') {
    $where = 'WHERE n.title LIKE ?';
    $params[] = '%' . $q . '%';
}

$countStmt = db()->prepare("SELECT COUNT(*) AS cnt FROM news n {$where}");
$countStmt->execute($params);
$total = (int) ($countStmt->fetch()['cnt'] ?? 0);
$totalPages = (int) max(1, (int) ceil($total / $perPage));

$sql = "SELECT n.id, n.title, n.slug, n.is_featured, n.published_at, n.created_at,
               c.name AS category_name,
               co.name AS country_name
        FROM news n
        LEFT JOIN categories c ON c.id = n.category_id
        LEFT JOIN countries co ON co.id = n.country_id
        {$where}
        ORDER BY COALESCE(n.published_at, n.created_at) DESC
        LIMIT {$perPage} OFFSET {$offset}";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

$pageTitle = 'Manage News';
require __DIR__ . '/partials/header.php';
?>

<div class="d-flex justify-content-between align-items-end mb-3">
  <div>
    <h1 class="h4 mb-1">News</h1>
    <div class="text-muted small"><?= e((string) $total) ?> total</div>
  </div>
  <a class="btn btn-primary btn-sm" href="<?= e(base_url('admin/news_create.php')) ?>">+ Add news</a>
</div>

<?php if ($msg = flash_get('success')): ?>
  <div class="alert alert-success"><?= e($msg) ?></div>
<?php endif; ?>

<form class="row g-2 mb-3" method="get" action="">
  <div class="col-sm-9">
    <input class="form-control" type="search" name="q" placeholder="Search by title..." value="<?= e($q) ?>">
  </div>
  <div class="col-sm-3 d-grid">
    <button class="btn btn-outline-secondary" type="submit">Search</button>
  </div>
</form>

<div class="table-responsive bg-white border rounded-4 shadow-sm">
  <table class="table align-middle mb-0">
    <thead class="table-light">
      <tr>
        <th>Title</th>
        <th class="text-nowrap">Country</th>
        <th class="text-nowrap">Category</th>
        <th class="text-nowrap">Featured</th>
        <th class="text-nowrap">Published</th>
        <th class="text-end">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($items) === 0): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">No news found.</td></tr>
      <?php endif; ?>
      <?php foreach ($items as $n): ?>
        <tr>
          <td>
            <div class="fw-semibold"><?= e($n['title']) ?></div>
            <div class="text-muted small"><?= e($n['slug']) ?></div>
          </td>
          <td class="text-nowrap"><?= e($n['country_name'] ?? '-') ?></td>
          <td class="text-nowrap"><?= e($n['category_name'] ?? '-') ?></td>
          <td class="text-nowrap">
            <?php if ((int) $n['is_featured'] === 1): ?>
              <span class="badge bg-success">Yes</span>
            <?php else: ?>
              <span class="badge bg-secondary">No</span>
            <?php endif; ?>
          </td>
          <td class="text-nowrap text-muted small">
            <?= e($n['published_at'] ? date('Y-m-d H:i', strtotime((string) $n['published_at'])) : '-') ?>
          </td>
          <td class="text-end text-nowrap">
            <a class="btn btn-sm btn-outline-primary" href="<?= e(base_url('news.php?slug=' . $n['slug'])) ?>" target="_blank" rel="noreferrer">View</a>
            <a class="btn btn-sm btn-outline-secondary" href="<?= e(base_url('admin/news_edit.php?id=' . $n['id'])) ?>">Edit</a>
            <form class="d-inline" method="post" action="<?= e(base_url('admin/news_delete.php')) ?>" onsubmit="return confirm('Delete this news?');">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= e((string) $n['id']) ?>">
              <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php if ($totalPages > 1): ?>
  <nav class="mt-3" aria-label="News pages">
    <ul class="pagination">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php $href = base_url('admin/news_list.php?q=' . urlencode($q) . '&p=' . $i); ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
          <a class="page-link" href="<?= e($href) ?>"><?= e((string) $i) ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>

