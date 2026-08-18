<?php
// ============================================================
// dashboard.php — User Dashboard
// Shows the user's own posts and quick actions
// ============================================================

$page_title = 'My Dashboard';
$base_url   = '';
require 'includes/db.php';
require 'includes/header.php';

// --- Auth guard: redirect to login if not logged in ---
if (!isset($_SESSION['user_id'])) {
    $_SESSION['intended'] = 'dashboard.php';
    header('Location: login.php');
    exit;
}

require 'includes/navbar.php';

$uid = $_SESSION['user_id'];

// --- Fetch user info ---
$u_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$u_stmt->bind_param('i', $uid);
$u_stmt->execute();
$user = $u_stmt->get_result()->fetch_assoc();
$u_stmt->close();

// --- Count user's own items ---
$cnt_stmt = $conn->prepare(
    "SELECT
        SUM(type='lost')             AS my_lost,
        SUM(type='found')            AS my_found,
        SUM(status='returned')       AS my_returned,
        SUM(status='active')         AS my_active
     FROM items WHERE user_id = ?"
);
$cnt_stmt->bind_param('i', $uid);
$cnt_stmt->execute();
$counts = $cnt_stmt->get_result()->fetch_assoc();
$cnt_stmt->close();

// --- Fetch user's items ---
$items_stmt = $conn->prepare(
    "SELECT i.*, c.name AS category_name, c.icon AS category_icon
     FROM items i
     JOIN categories c ON i.category_id = c.id
     WHERE i.user_id = ?
     ORDER BY i.created_at DESC"
);
$items_stmt->bind_param('i', $uid);
$items_stmt->execute();
$my_items = $items_stmt->get_result();
$items_stmt->close();

// --- Flash message (from delete / edit) ---
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
?>

<main class="section-pad">
<div class="container">

    <!-- Flash message -->
    <?php if ($flash): ?>
    <div class="alert alert-success alert-auto-dismiss mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($flash) ?>
    </div>
    <?php endif; ?>

    <!-- Welcome header -->
    <div class="d-flex justify-content-between align-items-start mb-5 flex-wrap gap-3">
        <div>
            <h1 class="section-title mb-1">
                Hi, <?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?>! 👋
            </h1>
            <p class="section-subtitle mb-0">
                <i class="bi bi-mortarboard me-1"></i><?= htmlspecialchars($user['department'] ?: 'Student') ?>
                <?php if ($user['roll_no']): ?>
                — <?= htmlspecialchars($user['roll_no']) ?>
                <?php endif; ?>
            </p>
        </div>
        <a href="add_item.php" class="btn btn-primary-custom">
            <i class="bi bi-plus-lg me-2"></i>Report New Item
        </a>
    </div>

    <!-- Stats cards -->
    <div class="row g-4 mb-5">
        <div class="col-sm-6 col-lg-3">
            <div class="dashboard-card">
                <div class="dashboard-card-icon icon-primary">
                    <i class="bi bi-collection"></i>
                </div>
                <div>
                    <div class="dashboard-card-value"><?= ($counts['my_lost'] + $counts['my_found']) ?: 0 ?></div>
                    <div class="dashboard-card-label">Total Posts</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="dashboard-card">
                <div class="dashboard-card-icon icon-lost">
                    <i class="bi bi-question-circle"></i>
                </div>
                <div>
                    <div class="dashboard-card-value"><?= $counts['my_lost'] ?: 0 ?></div>
                    <div class="dashboard-card-label">Lost Items</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="dashboard-card">
                <div class="dashboard-card-icon icon-found">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <div class="dashboard-card-value"><?= $counts['my_found'] ?: 0 ?></div>
                    <div class="dashboard-card-label">Found Items</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="dashboard-card">
                <div class="dashboard-card-icon icon-success">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
                <div>
                    <div class="dashboard-card-value"><?= $counts['my_returned'] ?: 0 ?></div>
                    <div class="dashboard-card-label">Returned</div>
                </div>
            </div>
        </div>
    </div>

    <!-- My Posts table -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 fw-700 mb-0">My Posts</h2>
        <a href="add_item.php" class="btn btn-sm btn-outline-custom">
            <i class="bi bi-plus-circle me-1"></i>Add New
        </a>
    </div>

    <?php if ($my_items->num_rows > 0): ?>
    <div class="table-dark-custom">
        <table class="table table-borderless mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php $n = 1; while ($item = $my_items->fetch_assoc()): ?>
                <tr>
                    <td class="text-muted"><?= $n++ ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <?php if ($item['image']): ?>
                            <img src="uploads/<?= htmlspecialchars($item['image']) ?>"
                                 style="width:38px;height:38px;object-fit:cover;border-radius:8px;">
                            <?php else: ?>
                            <div style="width:38px;height:38px;border-radius:8px;background:var(--surface);display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
                                <i class="<?= htmlspecialchars($item['category_icon']) ?> text-muted"></i>
                            </div>
                            <?php endif; ?>
                            <span class="fw-500"><?= htmlspecialchars($item['item_name']) ?></span>
                        </div>
                    </td>
                    <td>
                        <span class="badge <?= $item['type']==='lost' ? 'bg-danger' : 'bg-success' ?>">
                            <?= ucfirst($item['type']) ?>
                        </span>
                    </td>
                    <td class="text-muted"><?= htmlspecialchars($item['category_name']) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($item['location'] ?: '—') ?></td>
                    <td class="text-muted">
                        <?= $item['date_lost'] ? date('d M Y', strtotime($item['date_lost'])) : '—' ?>
                    </td>
                    <td>
                        <span class="status-badge <?= $item['status'] ?> position-static d-inline-block">
                            <?= ucfirst($item['status']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="item_details.php?id=<?= $item['id'] ?>"
                               class="btn btn-sm btn-secondary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="edit_item.php?id=<?= $item['id'] ?>"
                               class="btn btn-sm btn-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="delete_item.php?id=<?= $item['id'] ?>"
                               class="btn btn-sm btn-danger btn-confirm-delete" title="Delete">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="form-card">
        <div class="empty-state py-4">
            <i class="bi bi-inbox d-block"></i>
            <h4>No posts yet</h4>
            <p class="small">You haven't reported any lost or found items yet.</p>
            <a href="add_item.php" class="btn btn-primary-custom mt-2">Report Your First Item</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Profile summary card -->
    <div class="form-card mt-5">
        <h3 class="h5 fw-700 mb-4">Profile Information</h3>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="detail-info-card">
                    <i class="bi bi-person-circle"></i>
                    <div>
                        <div class="detail-info-label">Full Name</div>
                        <div class="detail-info-value"><?= htmlspecialchars($user['full_name']) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="detail-info-card">
                    <i class="bi bi-envelope"></i>
                    <div>
                        <div class="detail-info-label">Email</div>
                        <div class="detail-info-value"><?= htmlspecialchars($user['email']) ?></div>
                    </div>
                </div>
            </div>
            <?php if ($user['phone']): ?>
            <div class="col-md-6">
                <div class="detail-info-card">
                    <i class="bi bi-phone"></i>
                    <div>
                        <div class="detail-info-label">Phone</div>
                        <div class="detail-info-value"><?= htmlspecialchars($user['phone']) ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($user['roll_no']): ?>
            <div class="col-md-6">
                <div class="detail-info-card">
                    <i class="bi bi-person-badge"></i>
                    <div>
                        <div class="detail-info-label">Roll Number</div>
                        <div class="detail-info-value"><?= htmlspecialchars($user['roll_no']) ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>
</main>

<?php require 'includes/footer.php'; ?>
