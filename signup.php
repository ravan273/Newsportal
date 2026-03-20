<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/user_auth.php';

start_session();
if (!empty($_SESSION['user_user_id'])) {
    redirect(base_url('index.php'));
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_validate();
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($name === '' || $email === '' || $password === '') {
            throw new RuntimeException('All fields are required.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Please enter a valid email address.');
        }
        if ($msg = validate_password_strength($password)) {
            throw new RuntimeException($msg);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = db()->prepare("INSERT INTO users (name, email, password_hash, role, is_active) VALUES (?, ?, ?, 'user', 1)");
        $stmt->execute([$name, $email, $hash]);

        $id = (int) db()->lastInsertId();
        session_regenerate_id(true);
        $_SESSION['user_user_id'] = $id;
        redirect(base_url('index.php'));
    } catch (Throwable $t) {
        $error = $t->getMessage();
    }
}

$pageTitle = 'Sign up - AsuraNews';
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
      <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
          <div class="p-4 bg-brand text-white">
            <div class="fw-bold">AsuraNews</div>
            <div class="text-white-75 small">Create your account to submit news.</div>
          </div>
          <div class="card-body p-4">
            <?php if ($error): ?>
              <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>
            <form method="post" action="">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <div class="mb-3">
                <label class="form-label">Full name</label>
                <input class="form-control" name="name" required value="<?= e($_POST['name'] ?? '') ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input class="form-control" type="password" name="password" required>
                <div class="form-text">Min 10 chars, include uppercase + lowercase + number.</div>
              </div>
              <button class="btn btn-primary w-100" type="submit">Create account</button>
            </form>
            <div class="text-muted small mt-3">
              Already have an account? <a href="<?= e(base_url('user/login.php')) ?>">Login</a>
            </div>
          </div>
        </div>
        <div class="text-center mt-3">
          <a class="small link-secondary text-decoration-none" href="<?= e(base_url('index.php')) ?>">← Back to AsuraNews</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

