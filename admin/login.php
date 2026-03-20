<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

start_session();
if (!empty($_SESSION['admin_user_id'])) {
    redirect(base_url('admin/index.php'));
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = db()->prepare('SELECT id, email, password_hash FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if ($u && password_verify($password, (string) $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_user_id'] = (int) $u['id'];
        redirect(base_url('admin/index.php'));
    }

    $error = 'Invalid email or password.';
}

$pageTitle = 'Admin Login';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= e(base_url('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-7 col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
          <div class="p-4 bg-brand text-white">
            <div class="fw-bold">NewsPortal Admin</div>
            <div class="text-white-75 small">Login to manage news, countries, and categories.</div>
          </div>
          <div class="card-body p-4">
            <?php if ($error): ?>
              <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>
            <form method="post" action="">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="email" required value="<?= e($_POST['email'] ?? 'admin@newsportal.local') ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input class="form-control" type="password" name="password" required value="<?= e($_POST['password'] ?? '') ?>">
              </div>
              <button class="btn btn-primary w-100" type="submit">Login</button>
            </form>
          </div>
        </div>
        <div class="text-center mt-3">
          <a class="small link-secondary text-decoration-none" href="<?= e(base_url('index.php')) ?>">← Back to site</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

