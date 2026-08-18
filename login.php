<?php
// ============================================================
// login.php — User Login
// ============================================================

$page_title = 'Login';
$base_url   = '';
require 'includes/db.php';
require 'includes/header.php';
require 'includes/navbar.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        // Fetch user by email using prepared statement
        $stmt = $conn->prepare("SELECT id, full_name, password, is_active FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (!$user['is_active']) {
                $error = 'Your account has been suspended. Please contact the admin.';
            } elseif (password_verify($password, $user['password'])) {
                // ✅ Password correct — start session
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email']= $email;

                // Redirect to intended page or dashboard
                $redirect = $_SESSION['intended'] ?? 'dashboard.php';
                unset($_SESSION['intended']);
                header('Location: ' . $redirect);
                exit;
            } else {
                $error = 'Incorrect password. Please try again.';
            }
        } else {
            $error = 'No account found with that email address.';
        }
        $stmt->close();
    }
}
?>

<main class="section-pad">
<div class="container">
<div class="row justify-content-center">
<div class="col-lg-5 col-md-7">

    <?php if ($error): ?>
        <div class="alert alert-danger alert-auto-dismiss" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['registered'])): ?>
        <div class="alert alert-success alert-auto-dismiss" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            Registration successful! Please log in.
        </div>
    <?php endif; ?>

    <div class="form-card">
        <div class="text-center mb-4">
            <div class="brand-icon mx-auto mb-3" style="width:56px;height:56px;font-size:1.5rem;">
                <i class="bi bi-box-arrow-in-right"></i>
            </div>
            <h1 class="form-card-title h3">Welcome Back</h1>
            <p class="form-card-subtitle">Login to your campus portal account</p>
        </div>

        <form method="POST" action="login.php" novalidate>
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-control"
                       placeholder="your@college.edu"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" id="password" name="password"
                           class="form-control" placeholder="Your password" required
                           style="border-right:none;">
                    <button type="button" class="btn btn-secondary"
                            style="border-radius:0 10px 10px 0;border:1px solid var(--border);background:var(--surface);"
                            onclick="togglePassword()">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" id="btn_login" class="btn btn-primary-custom w-100 py-2">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login
            </button>

            <div class="divider-text mt-3">OR</div>

            <p class="text-center text-muted small mb-0">
                New to the portal?
                <a href="register.php" class="text-primary-custom fw-semibold">Create an account</a>
            </p>

            <hr class="my-3" style="border-color:var(--border)">

            <p class="text-center text-muted small mb-0">
                Are you an admin?
                <a href="admin/admin_login.php" class="text-primary-custom fw-semibold">Admin Login</a>
            </p>
        </form>
    </div>

    <!-- Demo credentials card -->
    <div class="form-card mt-3 p-3">
        <p class="text-muted small mb-2 fw-semibold"><i class="bi bi-info-circle me-1"></i>Demo Credentials:</p>
        <p class="small mb-1 text-muted">Email: <code class="text-info">rahul@college.edu</code></p>
        <p class="small mb-0 text-muted">Password: <code class="text-info">Student@123</code></p>
    </div>
</div>
</div>
</div>
</main>

<script>
function togglePassword() {
    var pwd  = document.getElementById('password');
    var icon = document.getElementById('eyeIcon');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        pwd.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>

<?php require 'includes/footer.php'; ?>
