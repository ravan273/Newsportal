<?php
declare(strict_types=1);

require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';

$q = $_GET['q'] ?? '';
$q = is_string($q) ? trim($q) : '';
$countrySlug = $_GET['c'] ?? '';
$countrySlug = is_string($countrySlug) ? trim($countrySlug) : '';
$categorySlug = $_GET['cat'] ?? '';
$categorySlug = is_string($categorySlug) ? trim($categorySlug) : '';
$page = isset($_GET['p']) ? (int) $_GET['p'] : 1;
$page = max(1, $page);
$perPage = 9;
$offset = ($page - 1) * $perPage;

$pageTitle = $q === '' ? 'Search' : 'Search: ' . $q;

$countries = db()->query("SELECT id, name, slug FROM countries WHERE is_active = 1 ORDER BY CASE WHEN slug='nepal' THEN 0 ELSE 1 END, name")->fetchAll();
$categories = db()->query("SELECT id, name, slug FROM categories WHERE is_active = 1 ORDER BY sort_order, name")->fetchAll();

// Build filters
$where = [];
$params = [];

if ($countrySlug !== '') {
    $where[] = 'co.slug = ?';
    $params[] = $countrySlug;
}
if ($categorySlug !== '') {
    $where[] = 'c.slug = ?';
    $params[] = $categorySlug;
}

$where[] = "n.status = 'published'";

$orderBy = 'ORDER BY COALESCE(n.published_at, n.created_at) DESC';

if ($q !== '') {
    // Prefer FULLTEXT ranking if available. Fall back to LIKE if FULLTEXT errors.
    $whereQ = 'MATCH(n.title, n.content) AGAINST (? IN NATURAL LANGUAGE MODE)';
    $where[] = $whereQ;
    $params[] = $q;
    $orderBy = 'ORDER BY score DESC, COALESCE(n.published_at, n.created_at) DESC';
}

$whereSql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

try {
    if ($q !== '') {
        $countStmt = db()->prepare(
            "SELECT COUNT(*) AS cnt
             FROM news n
             LEFT JOIN categories c ON c.id = n.category_id
             LEFT JOIN countries co ON co.id = n.country_id
             {$whereSql}"
        );
        $countStmt->execute($params);
        $total = (int) ($countStmt->fetch()['cnt'] ?? 0);

        $stmt = db()->prepare(
            "SELECT n.id, n.title, n.slug, n.summary, n.image_path, n.published_at,
                    c.name AS category_name, c.slug AS category_slug,
                    co.name AS country_name, co.slug AS country_slug,
                    MATCH(n.title, n.content) AGAINST (? IN NATURAL LANGUAGE MODE) AS score
             FROM news n
             LEFT JOIN categories c ON c.id = n.category_id
             LEFT JOIN countries co ON co.id = n.country_id
             {$whereSql}
             {$orderBy}
             LIMIT {$perPage} OFFSET {$offset}"
        );

        // Need params again but score param must be first in SELECT expression.
        $selectParams = $params;
        // The last param is $q; also used for SELECT score. Put it first.
        $selectParams = array_values($selectParams);
        array_unshift($selectParams, $q);
        $stmt->execute($selectParams);
        $items = $stmt->fetchAll();
    } else {
        $countStmt = db()->prepare(
            "SELECT COUNT(*) AS cnt
             FROM news n
             LEFT JOIN categories c ON c.id = n.category_id
             LEFT JOIN countries co ON co.id = n.country_id
             {$whereSql}"
        );
        $countStmt->execute($params);
        $total = (int) ($countStmt->fetch()['cnt'] ?? 0);

        $stmt = db()->prepare(
            "SELECT n.id, n.title, n.slug, n.summary, n.image_path, n.published_at,
                    c.name AS category_name, c.slug AS category_slug,
                    co.name AS country_name, co.slug AS country_slug
             FROM news n
             LEFT JOIN categories c ON c.id = n.category_id
             LEFT JOIN countries co ON co.id = n.country_id
             {$whereSql}
             {$orderBy}
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $stmt->execute($params);
        $items = $stmt->fetchAll();
    }
} catch (Throwable $t) {
    // Fallback to LIKE search if FULLTEXT is unavailable.
    $where = [];
    $params = [];
    if ($countrySlug !== '') {
        $where[] = 'co.slug = ?';
        $params[] = $countrySlug;
    }
    if ($categorySlug !== '') {
        $where[] = 'c.slug = ?';
        $params[] = $categorySlug;
    }
    if ($q !== '') {
        $where[] = '(n.title LIKE ? OR n.content LIKE ?)';
        $like = '%' . $q . '%';
        $params[] = $like;
        $params[] = $like;
    }
    $whereSql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

    $countStmt = db()->prepare(
        "SELECT COUNT(*) AS cnt
         FROM news n
         LEFT JOIN categories c ON c.id = n.category_id
         LEFT JOIN countries co ON co.id = n.country_id
         {$whereSql}"
    );
    $countStmt->execute($params);
    $total = (int) ($countStmt->fetch()['cnt'] ?? 0);

    $stmt = db()->prepare(
        "SELECT n.id, n.title, n.slug, n.summary, n.image_path, n.published_at,
                c.name AS category_name, c.slug AS category_slug,
                co.name AS country_name, co.slug AS country_slug
         FROM news n
         LEFT JOIN categories c ON c.id = n.category_id
         LEFT JOIN countries co ON co.id = n.country_id
         {$whereSql}
         ORDER BY COALESCE(n.published_at, n.created_at) DESC
         LIMIT {$perPage} OFFSET {$offset}"
    );
    $stmt->execute($params);
    $items = $stmt->fetchAll();
}

$totalPages = (int) max(1, (int) ceil($total / $perPage));

require __DIR__ . '/partials/header.php';
?>

<div class="d-flex justify-content-between align-items-end mb-3">
  <div>
    <h1 class="h4 mb-1">Search</h1>
    <div class="text-muted small">
      <?php if ($q !== ''): ?>
        Results for <span class="fw-semibold"><?= e($q) ?></span> (<?= e((string) $total) ?>)
      <?php else: ?>
        Type something in the search box to find news.
      <?php endif; ?>
    </div>
  </div>
</div>

<form class="row g-2 align-items-end mb-3" method="get" action="<?= e(base_url('search.php')) ?>">
  <div class="col-lg-6">
    <label class="form-label small mb-1">Keyword</label>
    <input class="form-control" type="search" name="q" placeholder="Search news..." value="<?= e($q) ?>">
  </div>
  <div class="col-sm-6 col-lg-3">
    <label class="form-label small mb-1">Country</label>
    <select class="form-select" name="c">
      <option value="">All</option>
      <?php foreach ($countries as $co): ?>
        <option value="<?= e($co['slug']) ?>" <?= $co['slug'] === $countrySlug ? 'selected' : '' ?>><?= e($co['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-sm-6 col-lg-3">
    <label class="form-label small mb-1">Category</label>
    <select class="form-select" name="cat">
      <option value="">All</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= e($cat['slug']) ?>" <?= $cat['slug'] === $categorySlug ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-12 d-grid d-md-flex gap-2">
    <button class="btn btn-primary" type="submit">Search</button>
    <a class="btn btn-outline-secondary" href="<?= e(base_url('search.php')) ?>">Reset</a>
  </div>
</form>

<div class="row g-3">
  <?php if (count($items) === 0): ?>
    <div class="col-12">
      <div class="alert alert-warning mb-0">No results found.</div>
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
            <?php if (!empty($item['category_name'])): ?>
              <a class="badge rounded-pill text-decoration-none bg-light text-dark border" href="<?= e(base_url('category.php?cat=' . ($item['category_slug'] ?? ''))) ?>"><?= e($item['category_name']) ?></a>
            <?php endif; ?>
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
  <nav class="mt-4" aria-label="Search pages">
    <ul class="pagination">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php
          $href = base_url('search.php?q=' . urlencode($q) . '&c=' . urlencode($countrySlug) . '&cat=' . urlencode($categorySlug) . '&p=' . $i);
        ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
          <a class="page-link" href="<?= e($href) ?>"><?= e((string) $i) ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>

