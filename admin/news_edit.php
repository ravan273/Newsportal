<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';

admin_require_login();

function unique_news_slug(string $base, int $excludeId): string
{
    $slug = $base;
    $i = 2;
    while (true) {
        $stmt = db()->prepare('SELECT id FROM news WHERE slug = ? AND id <> ? LIMIT 1');
        $stmt->execute([$slug, $excludeId]);
        $found = $stmt->fetch();
        if (!$found) {
            return $slug;
        }
        $slug = $base . '-' . $i;
        $i++;
    }
}

function handle_upload(?array $file): ?string
{
    if (!$file || !isset($file['error']) || (int) $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ((int) $file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed.');
    }

    $tmp = (string) $file['tmp_name'];
    $original = (string) ($file['name'] ?? 'image');
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($ext, $allowed, true)) {
        throw new RuntimeException('Invalid image type.');
    }

    $subDir = date('Y/m');
    $targetDir = rtrim(UPLOAD_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $subDir;
    if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
        throw new RuntimeException('Failed to create upload directory.');
    }

    $safeBase = preg_replace('~[^a-zA-Z0-9_-]+~', '-', pathinfo($original, PATHINFO_FILENAME)) ?? 'image';
    $safeBase = trim($safeBase, '-');
    if ($safeBase === '') {
        $safeBase = 'image';
    }

    $filename = $safeBase . '-' . bin2hex(random_bytes(8)) . '.' . $ext;
    $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($tmp, $targetPath)) {
        throw new RuntimeException('Failed to move upload.');
    }

    return $subDir . '/' . $filename;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    redirect(base_url('admin/news_list.php'));
}

$stmt = db()->prepare('SELECT * FROM news WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$news = $stmt->fetch();
if (!$news) {
    http_response_code(404);
    echo 'News not found';
    exit;
}

$categories = db()->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY sort_order, name")->fetchAll();
$countries = db()->query("SELECT id, name FROM countries WHERE is_active = 1 ORDER BY CASE WHEN slug='nepal' THEN 0 ELSE 1 END, name")->fetchAll();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_validate();

        $title = trim((string) ($_POST['title'] ?? ''));
        $slug = trim((string) ($_POST['slug'] ?? ''));
        $summary = trim((string) ($_POST['summary'] ?? ''));
        $content = trim((string) ($_POST['content'] ?? ''));
        $categoryId = ($_POST['category_id'] ?? '') !== '' ? (int) $_POST['category_id'] : null;
        $countryId = ($_POST['country_id'] ?? '') !== '' ? (int) $_POST['country_id'] : null;
        $sourceUrl = trim((string) ($_POST['source_url'] ?? ''));
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $publishedAt = trim((string) ($_POST['published_at'] ?? ''));
        $removeImage = isset($_POST['remove_image']) ? 1 : 0;

        if ($title === '' || $content === '') {
            throw new RuntimeException('Title and content are required.');
        }

        $slug = $slug !== '' ? slugify($slug) : slugify($title);
        $slug = unique_news_slug($slug, $id);

        $newImagePath = handle_upload($_FILES['image'] ?? null);
        $imagePath = $news['image_path'];
        if ($removeImage === 1) {
            $imagePath = null;
        }
        if ($newImagePath !== null) {
            $imagePath = $newImagePath;
        }

        $upd = db()->prepare(
            "UPDATE news
             SET title = ?, slug = ?, summary = ?, content = ?, image_path = ?,
                 category_id = ?, country_id = ?, source_url = ?, is_featured = ?, published_at = ?
             WHERE id = ?"
        );
        $upd->execute([
            $title,
            $slug,
            $summary !== '' ? $summary : null,
            $content,
            $imagePath,
            $categoryId ?: null,
            $countryId ?: null,
            $sourceUrl !== '' ? $sourceUrl : null,
            $isFeatured,
            $publishedAt !== '' ? $publishedAt : null,
            $id,
        ]);

        flash_set('success', 'News updated.');
        redirect(base_url('admin/news_list.php'));
    } catch (Throwable $t) {
        $error = $t->getMessage();
    }
}

$pageTitle = 'Edit News';
require __DIR__ . '/partials/header.php';
?>

<div class="d-flex justify-content-between align-items-end mb-3">
  <div>
    <h1 class="h4 mb-1">Edit News</h1>
    <div class="text-muted small">Update article details and image.</div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-sm btn-outline-primary" href="<?= e(base_url('news.php?slug=' . $news['slug'])) ?>" target="_blank" rel="noreferrer">View</a>
    <a class="btn btn-sm btn-outline-secondary" href="<?= e(base_url('admin/news_list.php')) ?>">Back</a>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form class="bg-white border rounded-4 p-3 p-md-4 shadow-sm" method="post" enctype="multipart/form-data">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

  <div class="mb-3">
    <label class="form-label">Title *</label>
    <input class="form-control" name="title" required value="<?= e($_POST['title'] ?? $news['title']) ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Slug</label>
    <input class="form-control" name="slug" value="<?= e($_POST['slug'] ?? $news['slug']) ?>">
    <div class="form-text">URL friendly. Will be auto-fixed if duplicate.</div>
  </div>

  <div class="mb-3">
    <label class="form-label">Summary</label>
    <textarea class="form-control" name="summary" rows="2"><?= e($_POST['summary'] ?? ($news['summary'] ?? '')) ?></textarea>
  </div>

  <div class="mb-3">
    <label class="form-label">Content *</label>
    <textarea class="form-control" id="contentEditor" name="content" rows="10" required><?= e($_POST['content'] ?? $news['content']) ?></textarea>
  </div>

  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Country</label>
      <select class="form-select" name="country_id">
        <option value="">— Select —</option>
        <?php foreach ($countries as $co): ?>
          <?php $selected = (string) ($co['id']) === (string) ($_POST['country_id'] ?? ($news['country_id'] ?? '')) ? 'selected' : ''; ?>
          <option value="<?= e((string) $co['id']) ?>" <?= $selected ?>><?= e($co['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Category</label>
      <select class="form-select" name="category_id">
        <option value="">— Select —</option>
        <?php foreach ($categories as $cat): ?>
          <?php $selected = (string) ($cat['id']) === (string) ($_POST['category_id'] ?? ($news['category_id'] ?? '')) ? 'selected' : ''; ?>
          <option value="<?= e((string) $cat['id']) ?>" <?= $selected ?>><?= e($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="row g-3 mt-0">
    <div class="col-md-6">
      <label class="form-label">Publish date/time</label>
      <input class="form-control" name="published_at" placeholder="YYYY-MM-DD HH:MM:SS" value="<?= e($_POST['published_at'] ?? ($news['published_at'] ?? '')) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Replace image</label>
      <input class="form-control" type="file" name="image" accept="image/*">
    </div>
  </div>

  <?php if (!empty($news['image_path'])): ?>
    <div class="mt-3 d-flex align-items-center gap-3">
      <img class="rounded-3 border" style="width:120px;height:80px;object-fit:cover;" src="<?= e(UPLOAD_URL . '/' . ltrim((string) $news['image_path'], '/')) ?>" alt="">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="remove_image" id="removeImage" <?= isset($_POST['remove_image']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="removeImage">Remove current image</label>
      </div>
    </div>
  <?php endif; ?>

  <div class="row g-3 mt-0">
    <div class="col-md-8">
      <label class="form-label">Source URL</label>
      <input class="form-control" name="source_url" value="<?= e($_POST['source_url'] ?? ($news['source_url'] ?? '')) ?>">
    </div>
    <div class="col-md-4 d-flex align-items-center">
      <div class="form-check mt-4">
        <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured" <?= (isset($_POST['is_featured']) || (int) ($news['is_featured'] ?? 0) === 1) ? 'checked' : '' ?>>
        <label class="form-check-label" for="isFeatured">Featured</label>
      </div>
    </div>
  </div>

  <div class="d-grid d-md-flex gap-2 mt-4">
    <button class="btn btn-primary" type="submit">Save changes</button>
    <a class="btn btn-outline-secondary" href="<?= e(base_url('admin/news_list.php')) ?>">Cancel</a>
  </div>
</form>

<?php require __DIR__ . '/partials/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/tinymce@7.10.0/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  tinymce.init({
    selector: '#contentEditor',
    height: 420,
    menubar: false,
    plugins: 'link lists table code autoresize',
    toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | alignleft aligncenter alignright | link table | removeformat | code',
    branding: false
  });
</script>

