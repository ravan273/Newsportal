<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';

admin_require_login();
$me = admin_user();
if (!$me) {
    redirect(base_url('admin/logout.php'));
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_validate();
        $current = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        if ($new === '' || $confirm === '' || $current === '') {
            throw new RuntimeException('All fields are required.');
        }
        if ($new !== $confirm) {
            throw new RuntimeException('New passwords do not match.');
        }
        if (strlen($new) < 8) {
            throw new RuntimeException('New password must be at least 8 characters.');
        }

        $stmt = db()->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([(int) $me['id']]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($current, (string) $row['password_hash'])) {
            throw new RuntimeException('Current password is incorrect.');
        }

        $hash = password_hash($new, PASSWORD_DEFAULT);
        $upd = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $upd->execute([$hash, (int) $me['id']]);

        session_regenerate_id(true);
        flash_set('success', 'Password changed successfully.');
        redirect(base_url('admin/index.php'));
    } catch (Throwable $t) {
        $error = $t->getMessage();
    }
}

$pageTitle = 'Change Password';
require __DIR__ . '/partials/header.php';
?>

<div class="d-flex justify-content-between align-items-end mb-3">
  <div>
    <h1 class="h4 mb-1">Change Password</h1>
    <div class="text-muted small">Update your admin password.</div>
  </div>
  <a class="btn btn-sm btn-outline-secondary" href="<?= e(base_url('admin/index.php')) ?>">Back</a>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form class="bg-white border rounded-4 p-3 p-md-4 shadow-sm" method="post" action="">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

  <div class="mb-3">
    <label class="form-label">Current password</label>
    <input class="form-control" type="password" name="current_password" required>
  </div>

  <div class="mb-3">
    <label class="form-label">New password</label>
    <input class="form-control" type="password" name="new_password" required>
    <div class="form-text">Minimum 8 characters.</div>
  </div>

  <div class="mb-3">
    <label class="form-label">Confirm new password</label>
    <input class="form-control" type="password" name="confirm_password" required>
  </div>

  <div class="d-grid d-md-flex gap-2">
    <button class="btn btn-primary" type="submit">Change password</button>
    <a class="btn btn-outline-secondary" href="<?= e(base_url('admin/index.php')) ?>">Cancel</a>
  </div>
</form>

<?php require __DIR__ . '/partials/footer.php'; ?>

