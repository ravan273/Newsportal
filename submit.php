<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/user_auth.php';

user_require_login();
$u = current_user();
if (!$u) {
    redirect(base_url('user/logout.php'));
}

function safe_image_upload(?array $file): ?string
{
    if (!$file || !isset($file['error']) || (int) $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ((int) $file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed.');
    }
    if (!isset($file['size']) || (int) $file['size'] > 3 * 1024 * 1024) {
        throw new RuntimeException('Image must be <= 3MB.');
    }

    $tmp = (string) $file['tmp_name'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file($tmp);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Invalid image type.');
    }

    $subDir = 'user/' . date('Y/m');
    $targetDir = rtrim(UPLOAD_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $subDir;
    if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
        throw new RuntimeException('Failed to create upload directory.');
    }

    $filename = 'photo-' . bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
    $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($tmp, $targetPath)) {
        throw new RuntimeException('Failed to save upload.');
    }

    return $subDir . '/' . $filename;
}

$categories = db()->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY sort_order, name")->fetchAll();
$countries = db()->query("SELECT id, name FROM countries WHERE is_active = 1 ORDER BY CASE WHEN slug='nepal' THEN 0 ELSE 1 END, name")->fetchAll();

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_validate();
        $title = trim((string) ($_POST['title'] ?? ''));
        $summary = trim((string) ($_POST['summary'] ?? ''));
        $content = trim((string) ($_POST['content'] ?? ''));
        $categoryId = ($_POST['category_id'] ?? '') !== '' ? (int) $_POST['category_id'] : null;
        $countryId = ($_POST['country_id'] ?? '') !== '' ? (int) $_POST['country_id'] : null;
        $sourceUrl = trim((string) ($_POST['source_url'] ?? ''));

        if ($title === '' || $content === '') {
            throw new RuntimeException('Title and content are required.');
        }

        $imagePath = safe_image_upload($_FILES['image'] ?? null);

        // Unique slug
        $base = slugify($title);
        $slug = $base;
        $i = 2;
        while (true) {
            $check = db()->prepare('SELECT id FROM news WHERE slug = ? LIMIT 1');
            $check->execute([$slug]);
            if (!$check->fetch()) {
                break;
            }
            $slug = $base . '-' . $i;
            $i++;
        }

        $stmt = db()->prepare(
            "INSERT INTO news (title, slug, summary, content, image_path, category_id, country_id, author_user_id, status, published_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NULL)"
        );
        $stmt->execute([
            $title,
            $slug,
            $summary !== '' ? $summary : null,
            $content,
            $imagePath,
            $categoryId ?: null,
            $countryId ?: null,
            (int) $u['id'],
        ]);

        flash_set('success', 'Submitted! Your news is pending admin approval.');
        redirect(base_url('index.php'));
    } catch (Throwable $t) {
        $error = $t->getMessage();
    }
}

$pageTitle = 'Submit News - AsuraNews';
require __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-end mb-3">
  <div>
    <h1 class="h4 mb-1">Submit news</h1>
    <div class="text-muted small">Your post will appear after admin approval.</div>
  </div>
  <a class="btn btn-sm btn-outline-secondary" href="<?= e(base_url('index.php')) ?>">Home</a>
</div>

<?php if ($msg = flash_get('success')): ?>
  <div class="alert alert-success"><?= e($msg) ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form class="bg-white border rounded-4 p-3 p-md-4 shadow-sm" method="post" enctype="multipart/form-data">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

  <div class="mb-3">
    <label class="form-label">Title *</label>
    <input class="form-control" name="title" required value="<?= e($_POST['title'] ?? '') ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Summary</label>
    <textarea class="form-control" name="summary" rows="2"><?= e($_POST['summary'] ?? '') ?></textarea>
  </div>

  <div class="mb-3">
    <label class="form-label">Content *</label>
    <textarea class="form-control" id="contentEditor" name="content" rows="10" required><?= e($_POST['content'] ?? '') ?></textarea>
  </div>

  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Country</label>
      <select class="form-select" name="country_id">
        <option value="">— Select —</option>
        <?php foreach ($countries as $co): ?>
          <option value="<?= e((string) $co['id']) ?>" <?= (string) ($co['id']) === (string) ($_POST['country_id'] ?? '') ? 'selected' : '' ?>>
            <?= e($co['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Category</label>
      <select class="form-select" name="category_id">
        <option value="">— Select —</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= e((string) $cat['id']) ?>" <?= (string) ($cat['id']) === (string) ($_POST['category_id'] ?? '') ? 'selected' : '' ?>>
            <?= e($cat['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="row g-3 mt-0">
    <div class="col-md-8">
      <label class="form-label">Source URL (optional)</label>
      <input class="form-control" name="source_url" value="<?= e($_POST['source_url'] ?? '') ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Photo (optional)</label>
      <input class="form-control" type="file" name="image" accept="image/*">
      <div class="form-text">Max 3MB. JPG/PNG/WEBP/GIF.</div>
    </div>
  </div>

  <div class="d-grid d-md-flex gap-2 mt-4">
    <button class="btn btn-primary" type="submit">Submit</button>
    <a class="btn btn-outline-secondary" href="<?= e(base_url('index.php')) ?>">Cancel</a>
  </div>
</form>

<?php require __DIR__ . '/../partials/footer.php'; ?>

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

