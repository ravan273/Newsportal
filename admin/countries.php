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
            $iso2 = strtoupper(trim((string) ($_POST['iso2'] ?? '')));
            if ($name === '') {
                throw new RuntimeException('Country name is required.');
            }
            if ($iso2 !== '' && !preg_match('~^[A-Z]{2}$~', $iso2)) {
                throw new RuntimeException('ISO2 must be 2 letters.');
            }
            $slug = slugify($name);
            $stmt = db()->prepare('INSERT INTO countries (name, slug, iso2, is_active) VALUES (?, ?, ?, 1)');
            $stmt->execute([$name, $slug, $iso2 !== '' ? $iso2 : null]);
            flash_set('success', 'Country added.');
            redirect(base_url('admin/countries.php'));
        }

        if ($action === 'update') {
            $id = (int) ($_POST['id'] ?? 0);
            $name = trim((string) ($_POST['name'] ?? ''));
            $slugIn = trim((string) ($_POST['slug'] ?? ''));
            $iso2 = strtoupper(trim((string) ($_POST['iso2'] ?? '')));
            $active = isset($_POST['is_active']) ? 1 : 0;
            if ($id <= 0 || $name === '') {
                throw new RuntimeException('Invalid update.');
            }
            if ($iso2 !== '' && !preg_match('~^[A-Z]{2}$~', $iso2)) {
                throw new RuntimeException('ISO2 must be 2 letters.');
            }
            $slug = slugify($slugIn !== '' ? $slugIn : $name);
            $stmt = db()->prepare('UPDATE countries SET name = ?, slug = ?, iso2 = ?, is_active = ? WHERE id = ?');
            $stmt->execute([$name, $slug, $iso2 !== '' ? $iso2 : null, $active, $id]);
            flash_set('success', 'Country updated.');
            redirect(base_url('admin/countries.php'));
        }
    } catch (Throwable $t) {
        $error = $t->getMessage();
    }
}

$countries = db()->query("SELECT id, name, slug, iso2, is_active FROM countries ORDER BY CASE WHEN slug='nepal' THEN 0 ELSE 1 END, name")->fetchAll();

$pageTitle = 'Countries';
require __DIR__ . '/partials/header.php';
?>

<div class="d-flex justify-content-between align-items-end mb-3">
  <div>
    <h1 class="h4 mb-1">Countries</h1>
    <div class="text-muted small">Manage country list used for filtering.</div>
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
      <div class="fw-semibold mb-2">Add country</div>
      <form method="post" action="">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="add">
        <div class="mb-2">
          <label class="form-label small">Name</label>
          <input class="form-control" name="name" required>
        </div>
        <div class="mb-2">
          <label class="form-label small">ISO2 (optional)</label>
          <input class="form-control" name="iso2" placeholder="NP">
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
            <th class="text-nowrap">ISO2</th>
            <th class="text-nowrap">Active</th>
            <th class="text-end">Save</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($countries as $co): ?>
            <tr>
              <form method="post" action="">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= e((string) $co['id']) ?>">
                <td>
                  <input class="form-control form-control-sm" name="name" value="<?= e($co['name']) ?>" required>
                </td>
                <td>
                  <input class="form-control form-control-sm" name="slug" value="<?= e($co['slug']) ?>">
                </td>
                <td style="max-width:110px;">
                  <input class="form-control form-control-sm" name="iso2" value="<?= e((string) ($co['iso2'] ?? '')) ?>">
                </td>
                <td>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" <?= (int) $co['is_active'] === 1 ? 'checked' : '' ?>>
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

