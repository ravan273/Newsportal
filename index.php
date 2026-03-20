<?php
declare(strict_types=1);

require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';

$pageTitle = 'AsuraNews';

$featured = db()->query(
    "SELECT n.id, n.title, n.slug, n.summary, n.image_path, n.published_at,
            c.name AS category_name, c.slug AS category_slug,
            co.name AS country_name, co.slug AS country_slug
     FROM news n
     LEFT JOIN categories c ON c.id = n.category_id
     LEFT JOIN countries co ON co.id = n.country_id
     WHERE n.is_featured = 1 AND n.status = 'published'
     ORDER BY COALESCE(n.published_at, n.created_at) DESC
     LIMIT 5"
)->fetchAll();

$nepalNews = db()->query(
    "SELECT n.id, n.title, n.slug, n.summary, n.image_path, n.published_at,
            c.name AS category_name, c.slug AS category_slug
     FROM news n
     LEFT JOIN categories c ON c.id = n.category_id
     LEFT JOIN countries co ON co.id = n.country_id
     WHERE co.slug = 'nepal' AND n.status = 'published'
     ORDER BY COALESCE(n.published_at, n.created_at) DESC
     LIMIT 9"
)->fetchAll();

$worldNews = db()->query(
    "SELECT n.id, n.title, n.slug, n.summary, n.image_path, n.published_at,
            c.name AS category_name, c.slug AS category_slug,
            co.name AS country_name, co.slug AS country_slug
     FROM news n
     LEFT JOIN categories c ON c.id = n.category_id
     LEFT JOIN countries co ON co.id = n.country_id
     WHERE (co.slug IS NULL OR co.slug <> 'nepal') AND n.status = 'published'
     ORDER BY COALESCE(n.published_at, n.created_at) DESC
     LIMIT 9"
)->fetchAll();

require __DIR__ . '/partials/header.php';
?>

<div class="hero mb-4">
  <div class="row align-items-center g-3">
    <div class="col-lg-8">
      <h1 class="display-6 fw-bold mb-2">Nepal + World News, in one place</h1>
      <p class="mb-0 text-white-75">Browse the latest headlines, filter by country, and search instantly.</p>
    </div>
    <div class="col-lg-4">
      <div class="p-3 bg-white bg-opacity-10 rounded-4">
        <div class="small text-white-75">Quick links</div>
        <div class="d-flex flex-wrap gap-2 mt-2">
          <a class="btn btn-sm btn-light" href="<?= e(base_url('country.php?c=nepal')) ?>">Nepal</a>
          <a class="btn btn-sm btn-outline-light" href="<?= e(base_url('country.php')) ?>">World</a>
          <a class="btn btn-sm btn-outline-light" href="<?= e(base_url('search.php?q=election')) ?>">Try search</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if (count($featured) > 0): ?>
  <div class="d-flex justify-content-between align-items-end mb-2">
    <h2 class="h4 mb-0">Featured</h2>
    <a class="small link-secondary text-decoration-none" href="<?= e(base_url('search.php?q=')) ?>">View all</a>
  </div>
  <div class="row g-3 mb-4">
    <?php foreach ($featured as $item): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card card-news h-100">
          <?php if (!empty($item['image_path'])): ?>
            <img class="news-thumb" src="<?= e(UPLOAD_URL . '/' . ltrim($item['image_path'], '/')) ?>" alt="">
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
              <p class="text-muted small mb-0"><?= e(mb_strimwidth($item['summary'], 0, 140, '…', 'UTF-8')) ?></p>
            <?php endif; ?>
          </div>
          <div class="card-footer bg-white border-0 pt-0">
            <div class="text-muted small">
              <?= e($item['published_at'] ? date('M d, Y H:i', strtotime($item['published_at'])) : '') ?>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-end mb-2">
  <h2 class="h4 mb-0">Latest from Nepal</h2>
  <a class="small link-secondary text-decoration-none" href="<?= e(base_url('country.php?c=nepal')) ?>">More Nepal news</a>
</div>
<div class="row g-3 mb-4">
  <?php if (count($nepalNews) === 0): ?>
    <div class="col-12">
      <div class="alert alert-info mb-0">No Nepal news yet. Login to Admin and add some news.</div>
    </div>
  <?php endif; ?>
  <?php foreach ($nepalNews as $item): ?>
    <div class="col-md-6 col-lg-4">
      <div class="card card-news h-100">
        <?php if (!empty($item['image_path'])): ?>
          <img class="news-thumb" src="<?= e(UPLOAD_URL . '/' . ltrim($item['image_path'], '/')) ?>" alt="">
        <?php else: ?>
          <div class="news-thumb d-flex align-items-center justify-content-center text-muted">No image</div>
        <?php endif; ?>
        <div class="card-body">
          <?php if (!empty($item['category_name'])): ?>
            <a class="badge rounded-pill text-decoration-none bg-light text-dark border mb-2" href="<?= e(base_url('category.php?cat=' . ($item['category_slug'] ?? ''))) ?>"><?= e($item['category_name']) ?></a>
          <?php endif; ?>
          <h3 class="h6 fw-semibold mb-0">
            <a class="text-decoration-none text-dark" href="<?= e(base_url('news.php?slug=' . $item['slug'])) ?>"><?= e($item['title']) ?></a>
          </h3>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="d-flex justify-content-between align-items-end mb-2">
  <h2 class="h4 mb-0">World</h2>
  <a class="small link-secondary text-decoration-none" href="<?= e(base_url('country.php')) ?>">Browse by country</a>
</div>
<div class="row g-3">
  <?php if (count($worldNews) === 0): ?>
    <div class="col-12">
      <div class="alert alert-secondary mb-0">No world news yet.</div>
    </div>
  <?php endif; ?>
  <?php foreach ($worldNews as $item): ?>
    <div class="col-md-6 col-lg-4">
      <div class="card card-news h-100">
        <?php if (!empty($item['image_path'])): ?>
          <img class="news-thumb" src="<?= e(UPLOAD_URL . '/' . ltrim($item['image_path'], '/')) ?>" alt="">
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
          <h3 class="h6 fw-semibold mb-0">
            <a class="text-decoration-none text-dark" href="<?= e(base_url('news.php?slug=' . $item['slug'])) ?>"><?= e($item['title']) ?></a>
          </h3>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>

