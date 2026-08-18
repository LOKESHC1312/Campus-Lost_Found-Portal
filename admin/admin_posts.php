<?php
// ============================================================
// admin/admin_posts.php — Manage All Items (Admin)
// Admin can view, change status, or remove any post
// ============================================================

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$base_url = '../';
require '../includes/db.php';
$page_title = 'Manage Posts';
require '../includes/header.php';

// --- Handle admin actions via GET ---
// ?action=remove&id=X  |  ?action=active&id=X  |  ?action=returned&id=X  |  ?action=delete&id=X
if (isset($_GET['action'], $_GET['id'])) {
    $act    = $_GET['action'];
    $pid    = (int)$_GET['id'];
    $flash  = '';

    if ($act === 'delete') {
        // Hard delete — also remove image file
        $row = $conn->query("SELECT image FROM items WHERE id=$pid")->fetch_assoc();
        $conn->query("DELETE FROM items WHERE id=$pid");
        if ($row && $row['image']) {
            $f = __DIR__ . '/../uploads/' . $row['image'];
            if (file_exists($f)) unlink($f);
        }
        $flash = 'Post deleted permanently.';
    } elseif (in_array($act, ['active','returned','removed'])) {
        $stmt = $conn->prepare("UPDATE items SET status=? WHERE id=?");
        $stmt->bind_param('si', $act, $pid);
        $stmt->execute();
        $stmt->close();
        $flash = "Post status changed to \"$act\".";
    }
    $_SESSION['flash'] = $flash;
    header('Location: admin_posts.php');
    exit;
}

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// --- Filters ---
$search  = trim($_GET['search'] ?? '');
$type    = $_GET['type']        ?? '';
$status  = $_GET['status']      ?? '';

$where  = ['1=1'];
$params = [];
$types  = '';

if ($type === 'lost' || $type === 'found') {
    $where[] = 'i.type=?'; $params[] = $type; $types .= 's';
}
if (in_array($status, ['active','returned','removed'])) {
    $where[] = 'i.status=?'; $params[] = $status; $types .= 's';
}
if (!empty($search)) {
    $like = '%' . $search . '%';
    $where[] = '(i.item_name LIKE ? OR u.full_name LIKE ? OR i.location LIKE ?)';
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= 'sss';
}

$where_sql = 'WHERE ' . implode(' AND ', $where);

$sql = "SELECT i.*, c.name AS cat, u.full_name, u.email
        FROM items i
        JOIN categories c ON i.category_id=c.id
        JOIN users u      ON i.user_id=u.id
        $where_sql
        ORDER BY i.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$posts = $stmt->get_result();
$stmt->close();
?>

<div class="d-flex" style="min-height:100vh;">
    <?php require 'admin_sidebar.php'; ?>

    <main class="flex-grow-1 p-4" style="background:var(--dark-bg);">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-800 mb-0">All Posts</h1>
                <p class="text-muted small mb-0">Manage every lost &amp; found item on the platform</p>
            </div>
        </div>

        <?php if ($flash): ?>
        <div class="alert alert-success alert-auto-dismiss mb-4">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($flash) ?>
        </div>
        <?php endif; ?>

        <!-- Filter -->
        <div class="filter-bar mb-4">
            <form method="GET" action="admin_posts.php">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control"
                               placeholder="Item name, poster name, location…"
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" onchange="this.form.submit()">
                            <option value="">All</option>
                            <option value="lost"  <?= $type==='lost'  ?'selected':'' ?>>Lost</option>
                            <option value="found" <?= $type==='found' ?'selected':'' ?>>Found</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">All</option>
                            <option value="active"   <?= $status==='active'   ?'selected':'' ?>>Active</option>
                            <option value="returned" <?= $status==='returned' ?'selected':'' ?>>Returned</option>
                            <option value="removed"  <?= $status==='removed'  ?'selected':'' ?>>Removed</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary-custom flex-fill">Search</button>
                        <a href="admin_posts.php" class="btn btn-secondary" title="Clear">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="table-dark-custom">
            <table class="table table-borderless mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Posted By</th>
                        <th>Location</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($posts->num_rows === 0): ?>
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">No posts found.</td>
                </tr>
                <?php endif; ?>
                <?php $n=1; while ($p = $posts->fetch_assoc()): ?>
                <tr>
                    <td class="text-muted"><?= $n++ ?></td>
                    <td>
                        <div class="fw-500"><?= htmlspecialchars($p['item_name']) ?></div>
                        <?php if ($p['image']): ?>
                        <img src="../uploads/<?= htmlspecialchars($p['image']) ?>"
                             style="width:36px;height:36px;object-fit:cover;border-radius:6px;margin-top:4px;">
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge <?= $p['type']==='lost'?'bg-danger':'bg-success' ?>">
                            <?= ucfirst($p['type']) ?>
                        </span>
                    </td>
                    <td class="text-muted small"><?= htmlspecialchars($p['cat']) ?></td>
                    <td>
                        <div class="small fw-500"><?= htmlspecialchars($p['full_name']) ?></div>
                        <div class="text-muted" style="font-size:.75rem;"><?= htmlspecialchars($p['email']) ?></div>
                    </td>
                    <td class="text-muted small"><?= htmlspecialchars($p['location'] ?: '—') ?></td>
                    <td class="text-muted small"><?= $p['date_lost'] ? date('d M Y', strtotime($p['date_lost'])) : '—' ?></td>
                    <td>
                        <span class="status-badge <?= $p['status'] ?> position-static d-inline-block">
                            <?= ucfirst($p['status']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <!-- View -->
                            <a href="../item_details.php?id=<?= $p['id'] ?>"
                               class="btn btn-secondary btn-sm" title="View" target="_blank">
                                <i class="bi bi-eye"></i>
                            </a>
                            <!-- Change Status -->
                            <?php if ($p['status'] !== 'active'): ?>
                            <a href="?action=active&id=<?= $p['id'] ?>"
                               class="btn btn-primary btn-sm" title="Set Active">
                                <i class="bi bi-check"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($p['status'] !== 'returned'): ?>
                            <a href="?action=returned&id=<?= $p['id'] ?>"
                               class="btn btn-success btn-sm" title="Mark Returned">
                                <i class="bi bi-check2-all"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($p['status'] !== 'removed'): ?>
                            <a href="?action=remove&id=<?= $p['id'] ?>"
                               class="btn btn-warning btn-sm btn-confirm-delete" title="Remove Post"
                               onclick="return confirm('Remove this post? (Will be hidden from students)')">
                                <i class="bi bi-ban"></i>
                            </a>
                            <?php endif; ?>
                            <!-- Hard Delete -->
                            <a href="?action=delete&id=<?= $p['id'] ?>"
                               class="btn btn-danger btn-sm" title="Delete Forever"
                               onclick="return confirm('Permanently delete this post? This cannot be undone.')">
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
