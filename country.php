<?php
declare(strict_types=1);

require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';

// SAFE HELPER (add if not already in helpers.php)
function safe($arr, $key, $default = '') {
    return $arr[$key] ?? $default;
}

$slug = $_GET['c'] ?? '';
$slug = is_string($slug) ? trim($slug) : '';

$page = isset($_GET['p']) ? (int) $_GET['p'] : 1;
$page = max(1, $page);
$perPage = 9;
$offset = ($page - 1) * $perPage;

if ($slug !== '') {

    // GET COUNTRY
    $countryStmt = db()->prepare("SELECT id, name, slug FROM countries WHERE slug = ? AND is_active = 1 LIMIT 1");
    $countryStmt->execute([$slug]);
    $country = $countryStmt->fetch();

    if (!$country) {
        http_response_code(404);
        echo 'Country not found';
        exit;
    }

    $pageTitle = 'News: ' . safe($country, 'name');

    // COUNT NEWS
    $countStmt = db()->prepare(
        "SELECT COUNT(*) AS cnt
         FROM news n
         LEFT JOIN countries co ON co.id = n.country_id
         WHERE co.slug = ? AND n.status = 'published'"
    );
    $countStmt->execute([$slug]);
    $countRow = $countStmt->fetch();
    $total = (int) safe($countRow, 'cnt', 0);

    // FETCH NEWS
    $stmt = db()->prepare(
        "SELECT n.id, n.title, n.slug, n.summary, n.image_path, n.published_at,
                c.name AS category_name, c.slug AS category_slug,
                co.name AS country_name, co.slug AS country_slug
         FROM news n
         LEFT JOIN categories c ON c.id = n.category_id
         LEFT JOIN countries co ON co.id = n.country_id
         WHERE co.slug = ? AND n.status = 'published'
         ORDER BY COALESCE(n.published_at, n.created_at) DESC
         LIMIT {$perPage} OFFSET {$offset}"
    );
    $stmt->execute([$slug]);
    $items = $stmt->fetchAll() ?: [];

} else {

    $pageTitle = 'Countries';

    // ✅ FIXED QUERY (JOIN + COUNT)
    $countries = db()->query(
        "SELECT co.id, co.name, co.slug, COUNT(n.id) AS news_count
         FROM countries co
         LEFT JOIN news n 
           ON n.country_id = co.id 
           AND n.status = 'published'
         WHERE co.is_active = 1
         GROUP BY co.id
         ORDER BY CASE WHEN co.slug='nepal' THEN 0 ELSE 1 END, co.name"
    )->fetchAll() ?: [];

    $total = 0;
    $items = [];
}

$totalPages = $slug !== '' ? (int) max(1, ceil($total / $perPage)) : 1;

require __DIR__ . '/partials/header.php';
?>

<?php if ($slug === ''): ?>
  <h1 class="h4 mb-3">Browse by country</h1>
  <div class="row g-3">
    <?php foreach ($countries as $co): ?>
      <div class="col-sm-6 col-lg-4">
        <a class="text-decoration-none" href="<?= e(base_url('country.php?c=' . safe($co, 'slug'))) ?>">
          <div class="card card-news h-100">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div class="fw-semibold text-dark"><?= e(safe($co, 'name')) ?></div>
                <span class="badge rounded-pill bg-light text-dark border">
                  <?= e((string) safe($co, 'news_count', 0)) ?>
                </span>
              </div>
              <div class="text-muted small mt-2">
                View latest news from <?= e(safe($co, 'name')) ?>.
              </div>
            </div>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>

<?php else: ?>

  <div class="d-flex justify-content-between align-items-end mb-3">
    <div>
      <h1 class="h4 mb-1"><?= e($pageTitle) ?></h1>
      <div class="text-muted small"><?= e((string) $total) ?> articles</div>
    </div>
    <a class="btn btn-sm btn-outline-secondary" href="<?= e(base_url('country.php')) ?>">All countries</a>
  </div>

  <div class="row g-3">
    <?php if (count($items) === 0): ?>
      <div class="col-12">
        <div class="alert alert-info mb-0">No news added for this country yet.</div>
      </div>
    <?php endif; ?>

    <?php foreach ($items as $item): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card card-news h-100">

          <?php if (!empty(safe($item, 'image_path'))): ?>
            <img class="news-thumb" src="<?= e(UPLOAD_URL . '/' . ltrim((string) safe($item, 'image_path'), '/')) ?>" alt="">
          <?php else: ?>
            <div class="news-thumb d-flex align-items-center justify-content-center text-muted">No image</div>
          <?php endif; ?>

          <div class="card-body">

            <?php if (!empty(safe($item, 'category_name'))): ?>
              <a class="badge rounded-pill text-decoration-none bg-light text-dark border mb-2"
                 href="<?= e(base_url('category.php?cat=' . safe($item, 'category_slug'))) ?>">
                <?= e(safe($item, 'category_name')) ?>
              </a>
            <?php endif; ?>

            <h3 class="h6 fw-semibold">
              <a class="text-decoration-none text-dark"
                 href="<?= e(base_url('news.php?slug=' . safe($item, 'slug'))) ?>">
                <?= e(safe($item, 'title')) ?>
              </a>
            </h3>

            <?php if (!empty(safe($item, 'summary'))): ?>
              <p class="text-muted small mb-0">
                <?= e(mb_strimwidth((string) safe($item, 'summary'), 0, 140, '…', 'UTF-8')) ?>
              </p>
            <?php endif; ?>

          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($totalPages > 1): ?>
    <nav class="mt-4">
      <ul class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <?php $href = base_url('country.php?c=' . urlencode($slug) . '&p=' . $i); ?>
          <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="<?= e($href) ?>"><?= e((string) $i) ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  <?php endif; ?>

<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>