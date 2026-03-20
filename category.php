<?php
declare(strict_types=1);

require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';

$slug = $_GET['cat'] ?? '';
$slug = is_string($slug) ? trim($slug) : '';

$page = isset($_GET['p']) ? (int) $_GET['p'] : 1;
$page = max(1, $page);
$perPage = 9;
$offset = ($page - 1) * $perPage;

if ($slug === '') {
    redirect(base_url('index.php'));
}

$catStmt = db()->prepare("SELECT id, name, slug FROM categories WHERE slug = ? AND is_active = 1 LIMIT 1");
$catStmt->execute([$slug]);
$cat = $catStmt->fetch();
if (!$cat) {
    http_response_code(404);
    echo 'Category not found';
    exit;
}

$pageTitle = 'Category: ' . $cat['name'];

$countStmt = db()->prepare("SELECT COUNT(*) AS cnt FROM news n WHERE n.category_id = ? AND n.status = 'published'");
$countStmt->execute([(int) $cat['id']]);
$total = (int) ($countStmt->fetch()['cnt'] ?? 0);

$stmt = db()->prepare(
    "SELECT n.id, n.title, n.slug, n.summary, n.image_path, n.published_at,
            c.name AS category_name, c.slug AS category_slug,
            co.name AS country_name, co.slug AS country_slug
     FROM news n
     LEFT JOIN categories c ON c.id = n.category_id
     LEFT JOIN countries co ON co.id = n.country_id
     WHERE n.category_id = ? AND n.status = 'published'
     ORDER BY COALESCE(n.published_at, n.created_at) DESC
     LIMIT {$perPage} OFFSET {$offset}"
);
$stmt->execute([(int) $cat['id']]);
$items = $stmt->fetchAll();

$totalPages = (int) max(1, (int) ceil($total / $perPage));

require __DIR__ . '/partials/header.php';
?>

<div class="d-flex justify-content-between align-items-end mb-3">
  <div>
    <h1 class="h4 mb-1"><?= e($pageTitle) ?></h1>
    <div class="text-muted small"><?= e((string) $total) ?> articles</div>
  </div>
  <a class="btn btn-sm btn-outline-secondary" href="<?= e(base_url('index.php')) ?>">Home</a>
</div>

<div class="row g-3">
  <?php if (count($items) === 0): ?>
    <div class="col-12">
      <div class="alert alert-info mb-0">No news in this category yet.</div>
    </div>
  <?php endif; ?>

  <?php foreach ($items as $item): ?>
    <div class="col-md-6 col-lg-4">
      <div class="card card-news h-100">
        <?php if (!empty($item['image_path'])): ?>
          <img class="news-thumb" src="<?= e(UPLOAD_URL . '/' . ltrim((string) $item['image_path'], '/')) ?>" alt="">
        <?php else: ?>
          <div class="news-thumb d-flex align-items-center justify-content-center text-muted">No image</div>
        <?php endif; ?>
        <div class="card-body">
          <div class="d-flex flex-wrap gap-2 mb-2">
            <?php if (!empty($item['country_name'])): ?>
              <a class="badge rounded-pill text-decoration-none badge-soft" href="<?= e(base_url('country.php?c=' . ($item['country_slug'] ?? ''))) ?>"><?= e($item['country_name']) ?></a>
            <?php endif; ?>
            <span class="badge rounded-pill bg-light text-dark border"><?= e($cat['name']) ?></span>
          </div>
          <h3 class="h6 fw-semibold">
            <a class="text-decoration-none text-dark" href="<?= e(base_url('news.php?slug=' . $item['slug'])) ?>"><?= e($item['title']) ?></a>
          </h3>
          <?php if (!empty($item['summary'])): ?>
            <p class="text-muted small mb-0"><?= e(mb_strimwidth((string) $item['summary'], 0, 140, '…', 'UTF-8')) ?></p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php if ($totalPages > 1): ?>
  <nav class="mt-4" aria-label="Category pages">
    <ul class="pagination">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php $href = base_url('category.php?cat=' . urlencode($slug) . '&p=' . $i); ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
          <a class="page-link" href="<?= e($href) ?>"><?= e((string) $i) ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>

