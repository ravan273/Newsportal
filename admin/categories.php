<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';

admin_require_login();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_validate();
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'add') {
            $name = trim((string) ($_POST['name'] ?? ''));
            $sort = (int) ($_POST['sort_order'] ?? 0);
            if ($name === '') {
                throw new RuntimeException('Category name is required.');
            }
            $slug = slugify($name);
            $stmt = db()->prepare('INSERT INTO categories (name, slug, sort_order, is_active) VALUES (?, ?, ?, 1)');
            $stmt->execute([$name, $slug, $sort]);
            flash_set('success', 'Category added.');
            redirect(base_url('admin/categories.php'));
        }

        if ($action === 'update') {
            $id = (int) ($_POST['id'] ?? 0);
            $name = trim((string) ($_POST['name'] ?? ''));
            $sort = (int) ($_POST['sort_order'] ?? 0);
            $active = isset($_POST['is_active']) ? 1 : 0;
            if ($id <= 0 || $name === '') {
                throw new RuntimeException('Invalid update.');
            }
            $slug = slugify((string) ($_POST['slug'] ?? $name));
            $stmt = db()->prepare('UPDATE categories SET name = ?, slug = ?, sort_order = ?, is_active = ? WHERE id = ?');
            $stmt->execute([$name, $slug, $sort, $active, $id]);
            flash_set('success', 'Category updated.');
            redirect(base_url('admin/categories.php'));
        }
    } catch (Throwable $t) {
        $error = $t->getMessage();
    }
}

$categories = db()->query("SELECT id, name, slug, sort_order, is_active FROM categories ORDER BY sort_order, name")->fetchAll();

$pageTitle = 'Categories';
require __DIR__ . '/partials/header.php';
?>

<div class="d-flex justify-content-between align-items-end mb-3">
  <div>
    <h1 class="h4 mb-1">Categories</h1>
    <div class="text-muted small">Manage category list shown on the site.</div>
  </div>
  <a class="btn btn-sm btn-outline-secondary" href="<?= e(base_url('admin/index.php')) ?>">Back</a>
</div>

<?php if ($msg = flash_get('success')): ?>
  <div class="alert alert-success"><?= e($msg) ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="bg-white border rounded-4 p-3 shadow-sm">
      <div class="fw-semibold mb-2">Add category</div>
      <form method="post" action="">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="add">
        <div class="mb-2">
          <label class="form-label small">Name</label>
          <input class="form-control" name="name" required>
        </div>
        <div class="mb-2">
          <label class="form-label small">Sort order</label>
          <input class="form-control" name="sort_order" type="number" value="0">
        </div>
        <button class="btn btn-primary w-100" type="submit">Add</button>
      </form>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="table-responsive bg-white border rounded-4 shadow-sm">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Name</th>
            <th>Slug</th>
            <th class="text-nowrap">Sort</th>
            <th class="text-nowrap">Active</th>
            <th class="text-end">Save</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($categories as $cat): ?>
            <tr>
              <form method="post" action="">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= e((string) $cat['id']) ?>">
                <td>
                  <input class="form-control form-control-sm" name="name" value="<?= e($cat['name']) ?>" required>
                </td>
                <td>
                  <input class="form-control form-control-sm" name="slug" value="<?= e($cat['slug']) ?>">
                </td>
                <td style="max-width:120px;">
                  <input class="form-control form-control-sm" name="sort_order" type="number" value="<?= e((string) $cat['sort_order']) ?>">
                </td>
                <td>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" <?= (int) $cat['is_active'] === 1 ? 'checked' : '' ?>>
                  </div>
                </td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-primary" type="submit">Update</button>
                </td>
              </form>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>

