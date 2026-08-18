<?php
// ============================================================
// admin/admin_users.php — Manage Students (Admin)
// Admin can ban/unban users or delete accounts
// ============================================================

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$base_url = '../';
require '../includes/db.php';
$page_title = 'Manage Users';
require '../includes/header.php';

// --- Handle actions ---
if (isset($_GET['action'], $_GET['id'])) {
    $act = $_GET['action'];
    $uid = (int)$_GET['id'];
    $flash = '';

    if ($act === 'ban') {
        $stmt = $conn->prepare("UPDATE users SET is_active=0 WHERE id=?");
        $stmt->bind_param('i', $uid); $stmt->execute(); $stmt->close();
        $flash = 'User has been suspended.';
    } elseif ($act === 'unban') {
        $stmt = $conn->prepare("UPDATE users SET is_active=1 WHERE id=?");
        $stmt->bind_param('i', $uid); $stmt->execute(); $stmt->close();
        $flash = 'User account restored.';
    } elseif ($act === 'delete') {
        // CASCADE will delete their items too (via FK)
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param('i', $uid); $stmt->execute(); $stmt->close();
        $flash = 'User deleted permanently.';
    }
    $_SESSION['flash'] = $flash;
    header('Location: admin_users.php');
    exit;
}

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// --- Search ---
$search = trim($_GET['search'] ?? '');
$filter = $_GET['filter']      ?? ''; // 'active' | 'banned'

$where  = ['1=1'];
$params = [];
$types  = '';

if (!empty($search)) {
    $like    = '%' . $search . '%';
    $where[] = '(full_name LIKE ? OR email LIKE ? OR roll_no LIKE ?)';
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types  .= 'sss';
}
if ($filter === 'active') { $where[] = 'is_active=1'; }
if ($filter === 'banned') { $where[] = 'is_active=0'; }

$where_sql = 'WHERE ' . implode(' AND ', $where);

$sql  = "SELECT u.*,
                (SELECT COUNT(*) FROM items i WHERE i.user_id=u.id) AS total_posts
         FROM users u $where_sql ORDER BY u.created_at DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result();
$stmt->close();
?>

<div class="d-flex" style="min-height:100vh;">
    <?php require 'admin_sidebar.php'; ?>

    <main class="flex-grow-1 p-4" style="background:var(--dark-bg);">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-800 mb-0">Manage Users</h1>
                <p class="text-muted small mb-0">View and manage all registered students</p>
            </div>
        </div>

        <?php if ($flash): ?>
        <div class="alert alert-success alert-auto-dismiss mb-4">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($flash) ?>
        </div>
        <?php endif; ?>

        <!-- Filter bar -->
        <div class="filter-bar mb-4">
            <form method="GET" action="admin_users.php">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control"
                               placeholder="Name, email, roll number…"
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="filter" class="form-select" onchange="this.form.submit()">
                            <option value=""       <?= $filter===''       ?'selected':'' ?>>All Users</option>
                            <option value="active" <?= $filter==='active' ?'selected':'' ?>>Active</option>
                            <option value="banned" <?= $filter==='banned' ?'selected':'' ?>>Suspended</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary-custom flex-fill">Search</button>
                        <a href="admin_users.php" class="btn btn-secondary" title="Clear"><i class="bi bi-x-lg"></i></a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Users table -->
        <div class="table-dark-custom">
            <table class="table table-borderless mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Roll No</th>
                        <th>Department</th>
                        <th>Posts</th>
                        <th>Joined</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($users->num_rows === 0): ?>
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">No users found.</td>
                </tr>
                <?php endif; ?>
                <?php $n = 1; while ($u = $users->fetch_assoc()): ?>
                <tr>
                    <td class="text-muted"><?= $n++ ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="nav-avatar" style="width:32px;height:32px;">
                                <?= strtoupper(substr($u['full_name'],0,1)) ?>
                            </div>
                            <span class="fw-500 small"><?= htmlspecialchars($u['full_name']) ?></span>
                        </div>
                    </td>
                    <td class="text-muted small"><?= htmlspecialchars($u['email']) ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($u['roll_no'] ?: '—') ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($u['department'] ?: '—') ?></td>
                    <td>
                        <span class="badge bg-primary"><?= $u['total_posts'] ?></span>
                    </td>
                    <td class="text-muted small"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <?php if ($u['is_active']): ?>
                        <span class="badge bg-success">Active</span>
                        <?php else: ?>
                        <span class="badge bg-danger">Suspended</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <?php if ($u['is_active']): ?>
                            <a href="?action=ban&id=<?= $u['id'] ?>"
                               class="btn btn-warning btn-sm"
                               onclick="return confirm('Suspend this user?')">
                                <i class="bi bi-ban me-1"></i>Ban
                            </a>
                            <?php else: ?>
                            <a href="?action=unban&id=<?= $u['id'] ?>"
                               class="btn btn-success btn-sm">
                                <i class="bi bi-person-check me-1"></i>Restore
                            </a>
                            <?php endif; ?>
                            <a href="?action=delete&id=<?= $u['id'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Delete this user and ALL their posts permanently?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.alert-auto-dismiss').forEach(function(a) {
    setTimeout(() => new bootstrap.Alert(a).close(), 4000);
});
</script>
</body>
</html>
