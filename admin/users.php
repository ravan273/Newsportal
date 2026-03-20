<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';

admin_require_login();
$me = admin_user();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_validate();
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'create') {
            $name = trim((string) ($_POST['name'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');

            if ($name === '' || $email === '' || $password === '') {
                throw new RuntimeException('Name, email and password are required.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Invalid email.');
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $email, $hash, 'admin']);

            flash_set('success', 'Admin user created.');
            redirect(base_url('admin/users.php'));
        }

        if ($action === 'reset_password') {
            $id = (int) ($_POST['id'] ?? 0);
            $password = (string) ($_POST['password'] ?? '');
            if ($id <= 0 || $password === '') {
                throw new RuntimeException('Invalid password reset.');
            }
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $stmt->execute([$hash, $id]);
            flash_set('success', 'Password updated.');
            redirect(base_url('admin/users.php'));
        }

        if ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new RuntimeException('Invalid delete.');
            }
            if ($me && (int) $me['id'] === $id) {
                throw new RuntimeException('You cannot delete your own account.');
            }
            $stmt = db()->prepare('DELETE FROM users WHERE id = ?');
            $stmt->execute([$id]);
            flash_set('success', 'User deleted.');
            redirect(base_url('admin/users.php'));
        }
    } catch (Throwable $t) {
        $error = $t->getMessage();
    }
}

$users = db()->query('SELECT id, name, email, role, created_at FROM users ORDER BY id ASC')->fetchAll();

$pageTitle = 'Admin Users';
require __DIR__ . '/partials/header.php';
?>

<div class="d-flex justify-content-between align-items-end mb-3">
  <div>
    <h1 class="h4 mb-1">Admin Users</h1>
    <div class="text-muted small">Create admins, reset passwords, and manage access.</div>
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
      <div class="fw-semibold mb-2">Create admin</div>
      <form method="post" action="">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="create">
        <div class="mb-2">
          <label class="form-label small">Name</label>
          <input class="form-control" name="name" required>
        </div>
        <div class="mb-2">
          <label class="form-label small">Email</label>
          <input class="form-control" type="email" name="email" required>
        </div>
        <div class="mb-2">
          <label class="form-label small">Password</label>
          <input class="form-control" type="password" name="password" required>
        </div>
        <button class="btn btn-primary w-100" type="submit">Create</button>
      </form>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="table-responsive bg-white border rounded-4 shadow-sm">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>User</th>
            <th class="text-nowrap">Role</th>
            <th class="text-nowrap">Created</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td>
                <div class="fw-semibold"><?= e($u['name']) ?></div>
                <div class="text-muted small"><?= e($u['email']) ?></div>
              </td>
              <td class="text-nowrap"><span class="badge bg-dark"><?= e($u['role']) ?></span></td>
              <td class="text-nowrap text-muted small"><?= e(date('Y-m-d', strtotime((string) $u['created_at']))) ?></td>
              <td class="text-end">
                <form class="d-inline" method="post" action="" onsubmit="return confirm('Reset password for this user?');">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="reset_password">
                  <input type="hidden" name="id" value="<?= e((string) $u['id']) ?>">
                  <input class="form-control form-control-sm d-inline-block" style="width:180px" type="password" name="password" placeholder="New password" required>
                  <button class="btn btn-sm btn-outline-primary" type="submit">Reset</button>
                </form>

                <form class="d-inline" method="post" action="" onsubmit="return confirm('Delete this user?');">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= e((string) $u['id']) ?>">
                  <button class="btn btn-sm btn-outline-danger" type="submit" <?= ($me && (int) $me['id'] === (int) $u['id']) ? 'disabled' : '' ?>>Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="text-muted small mt-2">Tip: keep at least one admin account active.</div>
  </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>

