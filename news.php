<?php
declare(strict_types=1);

require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';

$slug = $_GET['slug'] ?? '';
if (!is_string($slug) || $slug === '') {
    http_response_code(404);
    echo 'Not found';
    exit;
}

$stmt = db()->prepare(
    "SELECT n.*, c.name AS category_name, c.slug AS category_slug,
            co.name AS country_name, co.slug AS country_slug
     FROM news n
     LEFT JOIN categories c ON c.id = n.category_id
     LEFT JOIN countries co ON co.id = n.country_id
     WHERE n.slug = ? AND n.status = 'published'
     LIMIT 1"
);
$stmt->execute([$slug]);
$news = $stmt->fetch();
if (!$news) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

$pageTitle = (string) $news['title'];
require __DIR__ . '/partials/header.php';
?>

<div class="row g-4">
  <div class="col-lg-8">
    <article class="bg-white border rounded-4 p-3 p-md-4 shadow-sm">
      <div class="d-flex flex-wrap gap-2 mb-3">
        <?php if (!empty($news['country_name'])): ?>
          <a class="badge rounded-pill text-decoration-none badge-soft" href="<?= e(base_url('country.php?c=' . ($news['country_slug'] ?? ''))) ?>"><?= e($news['country_name']) ?></a>
        <?php endif; ?>
        <?php if (!empty($news['category_name'])): ?>
          <a class="badge rounded-pill text-decoration-none bg-light text-dark border" href="<?= e(base_url('category.php?cat=' . ($news['category_slug'] ?? ''))) ?>"><?= e($news['category_name']) ?></a>
        <?php endif; ?>
        <?php if (!empty($news['published_at'])): ?>
          <span class="badge rounded-pill bg-dark-subtle text-dark border"><?= e(date('M d, Y H:i', strtotime((string) $news['published_at']))) ?></span>
        <?php endif; ?>
      </div>

      <h1 class="h3 fw-bold mb-3"><?= e($news['title']) ?></h1>

      <?php if (!empty($news['image_path'])): ?>
        <img class="w-100 rounded-4 mb-3" style="max-height:420px;object-fit:cover;" src="<?= e(UPLOAD_URL . '/' . ltrim((string) $news['image_path'], '/')) ?>" alt="">
      <?php endif; ?>

      <?php if (!empty($news['summary'])): ?>
        <p class="lead text-secondary mb-3"><?= e((string) $news['summary']) ?></p>
      <?php endif; ?>

      <div class="prose">
        <?= sanitize_rich_html((string) $news['content']) ?>
      </div>

      <?php if (!empty($news['source_url'])): ?>
        <div class="mt-4">
          <a class="btn btn-sm btn-outline-secondary" href="<?= e((string) $news['source_url']) ?>" target="_blank" rel="noreferrer">Source</a>
        </div>
      <?php endif; ?>
    </article>
  </div>

  <div class="col-lg-4">
    <?php
      $relatedStmt = db()->prepare(
          "SELECT id, title, slug, image_path, published_at
           FROM news
           WHERE id <> ? AND status = 'published' AND (country_id <=> ? OR category_id <=> ?)
           ORDER BY COALESCE(published_at, created_at) DESC
           LIMIT 6"
      );
      $relatedStmt->execute([(int) $news['id'], $news['country_id'], $news['category_id']]);
      $related = $relatedStmt->fetchAll();
    ?>

    <div class="bg-white border rounded-4 p-3 shadow-sm">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="fw-semibold">Related</div>
        <a class="small link-secondary text-decoration-none" href="<?= e(base_url('search.php')) ?>">Search</a>
      </div>
      <?php if (count($related) === 0): ?>
        <div class="text-muted small">No related news yet.</div>
      <?php endif; ?>
      <div class="list-group list-group-flush">
        <?php foreach ($related as $r): ?>
          <a class="list-group-item list-group-item-action d-flex gap-3 py-3" href="<?= e(base_url('news.php?slug=' . $r['slug'])) ?>">
            <div class="flex-shrink-0" style="width:64px;">
              <?php if (!empty($r['image_path'])): ?>
                <img class="rounded-3" style="width:64px;height:48px;object-fit:cover;" src="<?= e(UPLOAD_URL . '/' . ltrim((string) $r['image_path'], '/')) ?>" alt="">
              <?php else: ?>
                <div class="rounded-3 bg-light border" style="width:64px;height:48px;"></div>
              <?php endif; ?>
            </div>
            <div class="w-100">
              <div class="fw-semibold small"><?= e((string) $r['title']) ?></div>
              <?php if (!empty($r['published_at'])): ?>
                <div class="text-muted small"><?= e(date('M d, Y', strtotime((string) $r['published_at']))) ?></div>
              <?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>

