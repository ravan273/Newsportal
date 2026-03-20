<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';

admin_require_login();
$admin = admin_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $action = (string) ($_POST['action'] ?? '');
    if ($id > 0 && in_array($action, ['approve', 'reject'], true)) {
        if ($action === 'approve') {
            $stmt = db()->prepare("UPDATE news SET status='published', approved_by=?, approved_at=NOW(), published_at=COALESCE(published_at, NOW()) WHERE id=?");
            $stmt->execute([(int) ($admin['id'] ?? 1), $id]);
            flash_set('success', 'News approved and published.');
        } else {
            $stmt = db()->prepare("UPDATE news SET status='rejected', approved_by=?, approved_at=NOW() WHERE id=?");
            $stmt->execute([(int) ($admin['id'] ?? 1), $id]);
            flash_set('success', 'News rejected.');
        }
    }
    redirect(base_url('admin/pending_news.php'));
}

$items = db()->query(
    "SELECT n.id, n.title, n.slug, n.created_at,
            u.name AS author_name, u.email AS author_email
     FROM news n
     LEFT JOIN users u ON u.id = n.author_user_id
     WHERE n.status = 'pending'
     ORDER BY n.created_at DESC
     LIMIT 50"
)->fetchAll();

$pageTitle = 'Pending News';
require __DIR__ . '/partials/header.php';
?>

<div class="d-flex justify-content-between align-items-end mb-3">
  <div>
    <h1 class="h4 mb-1">Pending news</h1>
    <div class="text-muted small">Approve or reject user-submitted posts.</div>
  </div>
  <a class="btn btn-sm btn-outline-secondary" href="<?= e(base_url('admin/index.php')) ?>">Back</a>
</div>

<?php if ($msg = flash_get('success')): ?>
  <div class="alert alert-success"><?= e($msg) ?></div>
<?php endif; ?>

<div class="table-responsive bg-white border rounded-4 shadow-sm">
  <table class="table align-middle mb-0">
    <thead class="table-light">
      <tr>
        <th>Title</th>
        <th class="text-nowrap">Submitted by</th>
        <th class="text-nowrap">Submitted</th>
        <th class="text-end">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($items) === 0): ?>
        <tr><td colspan="4" class="text-center text-muted py-4">No pending news.</td></tr>
      <?php endif; ?>
      <?php foreach ($items as $n): ?>
        <tr>
          <td>
            <div class="fw-semibold"><?= e($n['title']) ?></div>
            <div class="text-muted small"><?= e($n['slug']) ?></div>
          </td>
          <td class="text-nowrap">
            <div class="small"><?= e($n['author_name'] ?? 'Unknown') ?></div>
            <div class="text-muted small"><?= e($n['author_email'] ?? '-') ?></div>
          </td>
          <td class="text-muted small text-nowrap"><?= e(date('Y-m-d H:i', strtotime((string) $n['created_at']))) ?></td>
          <td class="text-end text-nowrap">
            <form class="d-inline" method="post" action="" onsubmit="return confirm('Approve and publish?');">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= e((string) $n['id']) ?>">
              <input type="hidden" name="action" value="approve">
              <button class="btn btn-sm btn-outline-success" type="submit">Approve</button>
            </form>
            <form class="d-inline" method="post" action="" onsubmit="return confirm('Reject this post?');">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= e((string) $n['id']) ?>">
              <input type="hidden" name="action" value="reject">
              <button class="btn btn-sm btn-outline-danger" type="submit">Reject</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>

