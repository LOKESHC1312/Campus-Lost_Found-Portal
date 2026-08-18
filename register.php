<?php
// ============================================================
// register.php — Student Registration
// ============================================================

$page_title = 'Register';
$base_url   = '';
require 'includes/db.php';
require 'includes/header.php';
require 'includes/navbar.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Collect & sanitize input ---
    $full_name  = trim($_POST['full_name']  ?? '');
    $email      = trim($_POST['email']      ?? '');
    $password   = $_POST['password']        ?? '';
    $confirm    = $_POST['confirm_password']?? '';
    $phone      = trim($_POST['phone']      ?? '');
    $roll_no    = trim($_POST['roll_no']    ?? '');
    $department = trim($_POST['department'] ?? '');

    // --- Validate ---
    if (empty($full_name))               $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 6)           $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm)          $errors[] = 'Passwords do not match.';

    // Check duplicate email (prepared statement)
    if (empty($errors)) {
        $chk = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $chk->bind_param('s', $email);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) {
            $errors[] = 'This email address is already registered.';
        }
        $chk->close();
    }

    // --- Insert user ---
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        $ins = $conn->prepare(
            "INSERT INTO users (full_name, email, password, phone, roll_no, department)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $ins->bind_param('ssssss', $full_name, $email, $hashed, $phone, $roll_no, $department);

        if ($ins->execute()) {
            $success = 'Registration successful! You can now <a href="login.php">login here</a>.';
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
        $ins->close();
    }
}
?>

<main class="section-pad">
<div class="container">
<div class="row justify-content-center">
<div class="col-lg-6 col-md-8">

    <?php if ($success): ?>
        <div class="alert alert-success alert-auto-dismiss" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= $success ?>
        </div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php foreach ($errors as $e): ?>
                <div><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <!-- Header -->
        <div class="text-center mb-4">
            <div class="brand-icon mx-auto mb-3" style="width:56px;height:56px;font-size:1.5rem;">
                <i class="bi bi-person-plus-fill"></i>
            </div>
            <h1 class="form-card-title h3">Create Account</h1>
            <p class="form-card-subtitle">Join the Campus Lost &amp; Found Portal</p>
        </div>

        <form method="POST" action="register.php" novalidate>
            <!-- Full name -->
            <div class="mb-3">
                <label for="full_name" class="form-label">Full Name *</label>
                <input type="text" id="full_name" name="full_name" class="form-control"
                       placeholder="e.g. Rahul Sharma"
                       value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label">College Email *</label>
                <input type="email" id="email" name="email" class="form-control"
                       placeholder="e.g. rahul@college.edu"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>

            <!-- Password row -->
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label for="password" class="form-label">Password *</label>
                    <input type="password" id="password" name="password"
                           class="form-control" placeholder="Min 6 chars" required>
                </div>
                <div class="col-6">
                    <label for="confirm_password" class="form-label">Confirm *</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                           class="form-control" placeholder="Repeat password" required>
                </div>
            </div>

            <!-- Phone & Roll No -->
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-control"
                           placeholder="10-digit number"
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
                <div class="col-6">
                    <label for="roll_no" class="form-label">Roll Number</label>
                    <input type="text" id="roll_no" name="roll_no" class="form-control"
                           placeholder="e.g. CS2021001"
                           value="<?= htmlspecialchars($_POST['roll_no'] ?? '') ?>">
                </div>
            </div>

            <!-- Department -->
            <div class="mb-4">
                <label for="department" class="form-label">Department</label>
                <select id="department" name="department" class="form-select">
                    <option value="">Select Department</option>
                    <?php
                    $depts = ['Computer Science','Electronics','Mechanical','Civil','Electrical','Chemical','Information Technology','Other'];
                    foreach ($depts as $d) {
                        $sel = (($_POST['department'] ?? '') === $d) ? 'selected' : '';
                        echo "<option value=\"$d\" $sel>$d</option>";
                    }
                    ?>
                </select>
            </div>

            <button type="submit" id="btn_register" class="btn btn-primary-custom w-100 py-2">
                <i class="bi bi-person-plus me-2"></i>Create Account
            </button>

            <div class="divider-text">OR</div>

            <p class="text-center text-muted small mb-0">
                Already have an account?
                <a href="login.php" class="text-primary-custom fw-semibold">Login here</a>
            </p>
        </form>
    </div>

</div>
</div>
</div>
</main>

<?php require 'includes/footer.php'; ?>
