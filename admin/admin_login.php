<?php
// ============================================================
// admin/admin_login.php — Admin Login Page
// ============================================================

session_start();

// Redirect if already logged in as admin
if (isset($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$base_url = '../';
require '../includes/db.php';

$page_title = 'Admin Login';
require '../includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password']      ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id']   = $admin['id'];
                $_SESSION['admin_name'] = $admin['username'];
                header('Location: admin_dashboard.php');
                exit;
            } else {
                $error = 'Incorrect password.';
            }
        } else {
            $error = 'Admin account not found.';
        }
        $stmt->close();
    }
}
?>

<!-- Simple admin login — no navbar to keep it separate from student portal -->
<div class="d-flex justify-content-center align-items-center" style="min-height:100vh;background:var(--dark-bg);">
<div class="col-md-5 col-lg-4 px-4">

    <?php if ($error): ?>
    <div class="alert alert-danger alert-auto-dismiss">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <div class="form-card">
        <div class="text-center mb-4">
            <div class="mx-auto mb-3" style="width:60px;height:60px;background:linear-gradient(135deg,#ef4444,#b91c1c);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;color:#fff;">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h1 class="h3 fw-800">Admin Portal</h1>
            <p class="text-muted small">Campus Lost &amp; Found — Administrator</p>
        </div>

        <form method="POST" action="admin_login.php" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control"
                       placeholder="admin" autocomplete="username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="••••••••" autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn btn-danger w-100 py-2 fw-semibold">
                <i class="bi bi-shield-check me-2"></i>Login as Admin
            </button>
        </form>

        <div class="divider-text mt-3">OR</div>
        <p class="text-center small text-muted mb-0">
            <a href="../login.php" class="text-primary-custom">← Back to Student Portal</a>
        </p>

        <!-- Demo credentials -->
        <div class="mt-3 p-3" style="background:var(--surface);border-radius:var(--radius);border:1px solid var(--border);">
            <p class="small text-muted mb-1 fw-semibold"><i class="bi bi-info-circle me-1"></i>Demo Admin Credentials:</p>
            <p class="small mb-1 text-muted">Username: <code class="text-info">admin</code></p>
            <p class="small mb-0 text-muted">Password: <code class="text-info">Admin@123</code></p>
        </div>
    </div>
</div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.alert-auto-dismiss').forEach(function(a) {
    setTimeout(() => { new bootstrap.Alert(a).close(); }, 4000);
});
</script>
</body>
</html>
