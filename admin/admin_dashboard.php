<?php
// ============================================================
// admin/admin_dashboard.php — Admin Overview Dashboard
// ============================================================

session_start();

// Admin auth guard
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$base_url = '../';
require '../includes/db.php';

$page_title = 'Admin Dashboard';
require '../includes/header.php';

// --- Stats ---
$total_users    = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$total_lost     = $conn->query("SELECT COUNT(*) FROM items WHERE type='lost'")->fetch_row()[0];
$total_found    = $conn->query("SELECT COUNT(*) FROM items WHERE type='found'")->fetch_row()[0];
$total_returned = $conn->query("SELECT COUNT(*) FROM items WHERE status='returned'")->fetch_row()[0];
$total_active   = $conn->query("SELECT COUNT(*) FROM items WHERE status='active'")->fetch_row()[0];
$total_removed  = $conn->query("SELECT COUNT(*) FROM items WHERE status='removed'")->fetch_row()[0];

// --- Recent 5 items ---
$recent = $conn->query(
    "SELECT i.*, c.name AS cat, u.full_name
     FROM items i
     JOIN categories c ON i.category_id=c.id
     JOIN users u      ON i.user_id=u.id
     ORDER BY i.created_at DESC LIMIT 5"
)->fetch_all(MYSQLI_ASSOC);

// --- Recent 5 users ---
$new_users = $conn->query(
    "SELECT * FROM users ORDER BY created_at DESC LIMIT 5"
)->fetch_all(MYSQLI_ASSOC);
?>

<!-- Admin layout: sidebar + main -->
<div class="d-flex" style="min-height:100vh;">

    <!-- Sidebar -->
    <?php require 'admin_sidebar.php'; ?>

    <!-- Main content -->
    <main class="flex-grow-1 p-4" style="background:var(--dark-bg);">

        <!-- Top bar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-800 mb-0">Dashboard</h1>
                <p class="text-muted small mb-0">Welcome back, <?= htmlspecialchars($_SESSION['admin_name']) ?>!</p>
            </div>
            <a href="admin_logout.php" class="btn btn-danger btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i>Logout
            </a>
        </div>

        <!-- Stats row -->
        <div class="row g-3 mb-5">
            <?php
            $stats = [
                ['label'=>'Total Users',    'value'=>$total_users,    'icon'=>'bi-people-fill',    'class'=>'icon-primary'],
                ['label'=>'Lost Items',     'value'=>$total_lost,     'icon'=>'bi-question-circle','class'=>'icon-lost'],
                ['label'=>'Found Items',    'value'=>$total_found,    'icon'=>'bi-check-circle',   'class'=>'icon-found'],
                ['label'=>'Returned',       'value'=>$total_returned, 'icon'=>'bi-arrow-repeat',   'class'=>'icon-success'],
                ['label'=>'Active Posts',   'value'=>$total_active,   'icon'=>'bi-collection',     'class'=>'icon-primary'],
                ['label'=>'Removed Posts',  'value'=>$total_removed,  'icon'=>'bi-trash',          'class'=>'icon-warning'],
            ];
            foreach ($stats as $s): ?>
            <div class="col-sm-6 col-lg-4">
                <div class="dashboard-card">
                    <div class="dashboard-card-icon <?= $s['class'] ?>">
                        <i class="<?= $s['icon'] ?>"></i>
                    </div>
                    <div>
                        <div class="dashboard-card-value"><?= $s['value'] ?></div>
                        <div class="dashboard-card-label"><?= $s['label'] ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="row g-4">
            <!-- Recent Items -->
            <div class="col-lg-7">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 fw-700 mb-0">Recent Items</h2>
                    <a href="admin_posts.php" class="btn btn-sm btn-outline-custom">View All</a>
                </div>
                <div class="table-dark-custom">
                    <table class="table table-borderless mb-0">
                        <thead>
                            <tr>
                                <th>Item</th><th>Type</th><th>Category</th><th>Posted By</th><th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recent as $item): ?>
                        <tr>
                            <td class="fw-500"><?= htmlspecialchars($item['item_name']) ?></td>
                            <td>
                                <span class="badge <?= $item['type']==='lost'?'bg-danger':'bg-success' ?>">
                                    <?= ucfirst($item['type']) ?>
                                </span>
                            </td>
                            <td class="text-muted"><?= htmlspecialchars($item['cat']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($item['full_name']) ?></td>
                            <td>
                                <span class="status-badge <?= $item['status'] ?> position-static d-inline-block">
                                    <?= ucfirst($item['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="col-lg-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 fw-700 mb-0">New Students</h2>
                    <a href="admin_users.php" class="btn btn-sm btn-outline-custom">View All</a>
                </div>
                <div class="table-dark-custom">
                    <table class="table table-borderless mb-0">
                        <thead>
                            <tr><th>Name</th><th>Dept</th><th>Joined</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($new_users as $u): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="nav-avatar" style="width:28px;height:28px;font-size:.75rem;">
                                        <?= strtoupper(substr($u['full_name'],0,1)) ?>
                                    </div>
                                    <span class="fw-500 small"><?= htmlspecialchars($u['full_name']) ?></span>
                                </div>
                            </td>
                            <td class="text-muted small"><?= htmlspecialchars($u['department'] ?: '—') ?></td>
                            <td class="text-muted small"><?= date('d M', strtotime($u['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
