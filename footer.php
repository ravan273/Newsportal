  </main>

  <footer class="border-top py-4">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-start gap-2">
      <div class="text-muted small">
        © <?= date('Y') ?> AsuraNews. Nepal + World news.
      </div>
      <div class="text-muted small">
        <a class="link-secondary text-decoration-none" href="<?= e(base_url('admin/login.php')) ?>">Admin</a>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= e(base_url('assets/js/main.js')) ?>"></script>
</body>
</html>

