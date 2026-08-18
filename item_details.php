<?php
// ============================================================
// item_details.php — Full Item Detail View
// ============================================================

$page_title = 'Item Details';
$base_url   = '';
require 'includes/db.php';
require 'includes/header.php';
require 'includes/navbar.php';

$id = (int)($_GET['id'] ?? 0);
if ($id < 1) {
    header('Location: items.php');
    exit;
}

// Fetch item with related data (prepared statement)
$stmt = $conn->prepare(
    "SELECT i.*, c.name AS cat_name, c.icon AS cat_icon,
            u.full_name, u.email AS poster_email, u.phone AS poster_phone,
            u.roll_no, u.department
     FROM items i
     JOIN categories c ON i.category_id = c.id
     JOIN users u      ON i.user_id     = u.id
     WHERE i.id = ? AND i.status != 'removed'"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: items.php');
    exit;
}
$item = $result->fetch_assoc();
$stmt->close();

$page_title = htmlspecialchars($item['item_name']) . ' — Details';

// Flash message
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// Is this the owner?
$is_owner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $item['user_id'];
?>

<main class="section-pad">
<div class="container">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="items.php">Items</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($item['item_name']) ?></li>
        </ol>
    </nav>

    <?php if ($flash): ?>
    <div class="alert alert-success alert-auto-dismiss mb-4">
        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($flash) ?>
    </div>
    <?php endif; ?>

    <div class="row g-5">

        <!-- LEFT: Image -->
        <div class="col-lg-5">
            <?php if ($item['image']): ?>
                <img src="uploads/<?= htmlspecialchars($item['image']) ?>"
                     class="detail-img w-100" alt="<?= htmlspecialchars($item['item_name']) ?>">
            <?php else: ?>
                <div class="detail-placeholder">
                    <i class="<?= htmlspecialchars($item['cat_icon']) ?>"></i>
                </div>
            <?php endif; ?>

            <!-- Owner actions -->
            <?php if ($is_owner): ?>
            <div class="form-card mt-3 p-3">
                <p class="small text-muted fw-semibold mb-2"><i class="bi bi-gear me-1"></i>Manage Your Post</p>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="edit_item.php?id=<?= $id ?>" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                    <a href="delete_item.php?id=<?= $id ?>" class="btn btn-danger btn-sm btn-confirm-delete">
                        <i class="bi bi-trash me-1"></i>Delete
                    </a>
                    <?php if ($item['status'] !== 'returned'): ?>
                    <a href="delete_item.php?id=<?= $id ?>&action=returned"
                       class="btn btn-success btn-sm btn-confirm-delete"
                       onclick="return confirm('Mark this item as returned?')">
                        <i class="bi bi-check2-all me-1"></i>Mark Returned
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT: Info -->
        <div class="col-lg-7">
            <!-- Type & Status badges -->
            <div class="d-flex gap-2 mb-3">
                <span class="type-badge <?= $item['type'] ?> position-static d-inline-flex" style="font-size:.85rem;padding:.35rem .9rem;">
                    <?= $item['type']==='lost'
                        ? '<i class="bi bi-question-circle me-1"></i>Lost'
                        : '<i class="bi bi-check-circle me-1"></i>Found' ?>
                </span>
                <span class="status-badge <?= $item['status'] ?> position-static d-inline-flex" style="font-size:.85rem;padding:.35rem .9rem;">
                    <?= ucfirst($item['status']) ?>
                </span>
            </div>

            <h1 class="fw-800 mb-1" style="font-size:1.8rem;"><?= htmlspecialchars($item['item_name']) ?></h1>
            <p class="text-muted small mb-4">
                Posted on <?= date('d F Y, g:i A', strtotime($item['created_at'])) ?>
            </p>

            <!-- Description -->
            <?php if ($item['description']): ?>
            <div class="mb-4">
                <h3 class="h6 text-muted text-uppercase" style="letter-spacing:.5px;font-size:.75rem;">Description</h3>
                <p class="mb-0"><?= nl2br(htmlspecialchars($item['description'])) ?></p>
            </div>
            <?php endif; ?>

            <!-- Details grid -->
            <div class="row g-2 mb-4">
                <div class="col-md-6">
                    <div class="detail-info-card">
                        <i class="<?= htmlspecialchars($item['cat_icon']) ?>"></i>
                        <div>
                            <div class="detail-info-label">Category</div>
                            <div class="detail-info-value"><?= htmlspecialchars($item['cat_name']) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-info-card">
                        <i class="bi bi-geo-alt-fill"></i>
                        <div>
                            <div class="detail-info-label">Location</div>
                            <div class="detail-info-value"><?= htmlspecialchars($item['location'] ?: 'Not specified') ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-info-card">
                        <i class="bi bi-calendar-event"></i>
                        <div>
                            <div class="detail-info-label">Date <?= ucfirst($item['type']) ?></div>
                            <div class="detail-info-value">
                                <?= $item['date_lost'] ? date('d F Y', strtotime($item['date_lost'])) : 'Not specified' ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-info-card">
                        <i class="bi bi-person-fill"></i>
                        <div>
                            <div class="detail-info-label">Posted By</div>
                            <div class="detail-info-value">
                                <?= htmlspecialchars($item['full_name']) ?>
                                <?php if ($item['department']): ?>
                                <span class="text-muted" style="font-size:.8rem;">(<?= htmlspecialchars($item['department']) ?>)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact box -->
            <div class="form-card p-4" style="border-color:var(--primary);background:rgba(79,70,229,0.06);">
                <h3 class="h6 fw-700 mb-3">
                    <i class="bi bi-telephone-fill me-2 text-primary-custom"></i>Contact Information
                </h3>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <p class="mb-2 fw-semibold"><?= htmlspecialchars($item['contact']) ?></p>
                    <?php if ($item['poster_email']): ?>
                    <a href="mailto:<?= htmlspecialchars($item['poster_email']) ?>"
                       class="btn btn-primary-custom btn-sm me-2">
                        <i class="bi bi-envelope me-1"></i>Send Email
                    </a>
                    <?php endif; ?>
                    <?php if ($item['poster_phone']): ?>
                    <a href="tel:<?= htmlspecialchars($item['poster_phone']) ?>"
                       class="btn btn-outline-custom btn-sm">
                        <i class="bi bi-telephone me-1"></i>Call
                    </a>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted small mb-2">
                        <i class="bi bi-lock me-1"></i>
                        Please <a href="login.php" class="text-primary-custom fw-semibold">login</a>
                        to view contact details and reach the poster.
                    </p>
                    <a href="login.php" class="btn btn-primary-custom btn-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Login to Contact
                    </a>
                <?php endif; ?>
            </div>

            <!-- Back button -->
            <div class="mt-4">
                <a href="items.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Items
                </a>
            </div>
        </div>

    </div>
</div>
</main>

<style>
.breadcrumb { background: transparent; padding: 0; }
.breadcrumb-item a { color: var(--primary-light); }
.breadcrumb-item.active { color: var(--text-muted); }
.breadcrumb-item + .breadcrumb-item::before { color: var(--text-muted); }
</style>

<?php require 'includes/footer.php'; ?>
