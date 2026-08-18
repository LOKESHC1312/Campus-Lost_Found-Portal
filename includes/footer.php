<?php
// ============================================================
// includes/footer.php
// Common footer included on every page
// ============================================================
$nav_base = $base_url ?? '';
?>
    <!-- ===== FOOTER ===== -->
    <footer class="site-footer mt-auto">
        <div class="container">
            <div class="row gy-4">
                <!-- Brand column -->
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="brand-icon-sm"><i class="bi bi-search-heart-fill"></i></span>
                        <span class="fw-bold fs-5 text-white">Campus L&amp;F Portal</span>
                    </div>
                    <p class="text-muted small">
                        A college-based platform to report, search, and recover lost &amp; found items quickly and easily.
                    </p>
                </div>

                <!-- Quick links -->
                <div class="col-lg-2 col-6">
                    <h6 class="footer-heading">Quick Links</h6>
                    <ul class="footer-links">
                        <li><a href="<?= $nav_base ?>index.php"><i class="bi bi-chevron-right"></i> Home</a></li>
                        <li><a href="<?= $nav_base ?>items.php"><i class="bi bi-chevron-right"></i> Browse Items</a></li>
                        <li><a href="<?= $nav_base ?>add_item.php"><i class="bi bi-chevron-right"></i> Report Item</a></li>
                    </ul>
                </div>

                <!-- Account links -->
                <div class="col-lg-2 col-6">
                    <h6 class="footer-heading">Account</h6>
                    <ul class="footer-links">
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="<?= $nav_base ?>dashboard.php"><i class="bi bi-chevron-right"></i> Dashboard</a></li>
                        <li><a href="<?= $nav_base ?>logout.php"><i class="bi bi-chevron-right"></i> Logout</a></li>
                        <?php else: ?>
                        <li><a href="<?= $nav_base ?>login.php"><i class="bi bi-chevron-right"></i> Login</a></li>
                        <li><a href="<?= $nav_base ?>register.php"><i class="bi bi-chevron-right"></i> Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Stats / Info -->
                <div class="col-lg-4">
                    <h6 class="footer-heading">About This Project</h6>
                    <p class="text-muted small mb-1">
                        <i class="bi bi-mortarboard me-1 text-primary"></i>
                        Designed for college students to recover lost belongings.
                    </p>
                    <p class="text-muted small mb-1">
                        <i class="bi bi-shield-check me-1 text-success"></i>
                        Secure login &amp; session management.
                    </p>
                    <p class="text-muted small">
                        <i class="bi bi-code-slash me-1 text-info"></i>
                        Built with PHP, MySQL &amp; Bootstrap.
                    </p>
                </div>
            </div>

            <hr class="footer-divider">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <p class="mb-0 text-muted small">
                    &copy; <?= date('Y') ?> Campus Lost &amp; Found Portal. All rights reserved.
                </p>
                <p class="mb-0 text-muted small">
                    Powered by <span class="text-primary fw-semibold">PHP + MySQL + Bootstrap</span>
                </p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS (inline) -->
    <script>
        // Auto-dismiss alerts after 4 seconds
        document.querySelectorAll('.alert-auto-dismiss').forEach(function(alert) {
            setTimeout(function() {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 4000);
        });

        // Confirm delete dialogs
        document.querySelectorAll('.btn-confirm-delete').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this item? This cannot be undone.')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
