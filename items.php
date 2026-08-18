<?php
// ============================================================
// items.php — Browse All Items with Search & Filter
// ============================================================

$page_title = 'Browse Items';
$base_url   = '';
require 'includes/db.php';
require 'includes/header.php';
require 'includes/navbar.php';

// Load categories for filter sidebar
$cats = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// --- Build query with optional filters ---
$search   = trim($_GET['search']   ?? '');
$type     = $_GET['type']          ?? '';      // 'lost' or 'found'
$cat_id   = (int)($_GET['cat_id'] ?? 0);
$location = trim($_GET['location'] ?? '');
$status   = $_GET['status']        ?? 'active';

// Pagination
$per_page    = 9;
$page_num    = max(1, (int)($_GET['page'] ?? 1));
$offset      = ($page_num - 1) * $per_page;

// Build WHERE clause dynamically
$where  = ["i.status != 'removed'"];
$params = [];
$types  = '';

if ($status === 'active' || $status === 'returned') {
    $where[]  = "i.status = ?";
    $params[] = $status;
    $types   .= 's';
}
if ($type === 'lost' || $type === 'found') {
    $where[]  = "i.type = ?";
    $params[] = $type;
    $types   .= 's';
}
if ($cat_id > 0) {
    $where[]  = "i.category_id = ?";
    $params[] = $cat_id;
    $types   .= 'i';
}
if (!empty($search)) {
    $where[]  = "(i.item_name LIKE ? OR i.description LIKE ? OR i.location LIKE ?)";
    $like     = '%' . $search . '%';
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types   .= 'sss';
}
if (!empty($location)) {
    $where[]  = "i.location LIKE ?";
    $params[] = '%' . $location . '%';
    $types   .= 's';
}

$where_sql = 'WHERE ' . implode(' AND ', $where);

// --- Count total for pagination ---
$count_sql  = "SELECT COUNT(*) FROM items i $where_sql";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_count = $count_stmt->get_result()->fetch_row()[0];
$count_stmt->close();

$total_pages = max(1, (int)ceil($total_count / $per_page));
$page_num    = min($page_num, $total_pages);

// --- Fetch items ---
$sql = "SELECT i.*, c.name AS cat_name, c.icon AS cat_icon, u.full_name
        FROM items i
        JOIN categories c ON i.category_id = c.id
        JOIN users u      ON i.user_id     = u.id
        $where_sql
        ORDER BY i.created_at DESC
        LIMIT ? OFFSET ?";

$fetch_params = $params;
$fetch_types  = $types . 'ii';
$fetch_params[] = $per_page;
$fetch_params[] = $offset;

$stmt = $conn->prepare($sql);
if (!empty($fetch_params)) {
    $stmt->bind_param($fetch_types, ...$fetch_params);
}
$stmt->execute();
$items = $stmt->get_result();
$stmt->close();
?>

<main class="section-pad">
<div class="container">

    <!-- Page header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="section-title">Browse Items</h1>
            <p class="section-subtitle mb-0">
                <?= $total_count ?> item<?= $total_count !== 1 ? 's' : '' ?> found
                <?= !empty($search) ? ' for "' . htmlspecialchars($search) . '"' : '' ?>
            </p>
        </div>
        <?php if (isset($_SESSION['user_id'])): ?>
        <a href="add_item.php" class="btn btn-primary-custom">
            <i class="bi bi-plus-lg me-2"></i>Report Item
        </a>
        <?php endif; ?>
    </div>

    <!-- Filter bar -->
    <div class="filter-bar">
        <form method="GET" action="items.php" id="filterForm">
            <div class="row g-3 align-items-end">
                <!-- Search -->
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-surface border-custom text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control"
                               placeholder="Item name, location…"
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>

                <!-- Type filter -->
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="lost"  <?= $type==='lost'  ? 'selected':'' ?>>Lost</option>
                        <option value="found" <?= $type==='found' ? 'selected':'' ?>>Found</option>
                    </select>
                </div>

                <!-- Category filter -->
                <div class="col-md-2">
                    <label class="form-label">Category</label>
                    <select name="cat_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($cats as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $cat_id==$c['id'] ? 'selected':'' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status filter -->
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="active"   <?= $status==='active'   ? 'selected':'' ?>>Active</option>
                        <option value="returned" <?= $status==='returned' ? 'selected':'' ?>>Returned</option>
                        <option value=""         <?= $status===''         ? 'selected':'' ?>>All</option>
                    </select>
                </div>

                <!-- Search & Clear -->
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary-custom flex-fill">Search</button>
                    <a href="items.php" class="btn btn-secondary" title="Clear filters">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Category pills -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="items.php?<?= http_build_query(array_merge($_GET, ['cat_id'=>''])) ?>"
           class="cat-pill <?= $cat_id===0 ? 'active':'' ?>">
            <i class="bi bi-grid"></i> All
        </a>
        <?php foreach ($cats as $c): ?>
        <a href="items.php?<?= http_build_query(array_merge($_GET, ['cat_id'=>$c['id']])) ?>"
           class="cat-pill <?= $cat_id==$c['id'] ? 'active':'' ?>">
            <i class="<?= $c['icon'] ?>"></i> <?= htmlspecialchars($c['name']) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Items grid -->
    <?php if ($items->num_rows > 0): ?>
    <div class="row g-4">
        <?php while ($item = $items->fetch_assoc()): ?>
        <div class="col-sm-6 col-lg-4">
            <div class="item-card">
                <div class="item-card-img">
                    <?php if ($item['image']): ?>
                        <img src="uploads/<?= htmlspecialchars($item['image']) ?>"
                             alt="<?= htmlspecialchars($item['item_name']) ?>"
                             loading="lazy">
                    <?php else: ?>
                        <div class="item-card-img-placeholder">
                            <i class="<?= htmlspecialchars($item['cat_icon']) ?>"></i>
                        </div>
                    <?php endif; ?>
                    <span class="type-badge <?= $item['type'] ?>">
                        <?= $item['type']==='lost'
                            ? '<i class="bi bi-question-circle me-1"></i>Lost'
                            : '<i class="bi bi-check-circle me-1"></i>Found' ?>
                    </span>
                    <span class="status-badge <?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span>
                </div>

                <div class="item-card-body">
                    <div class="item-card-title" title="<?= htmlspecialchars($item['item_name']) ?>">
                        <?= htmlspecialchars($item['item_name']) ?>
                    </div>
                    <div class="item-meta">
                        <i class="<?= htmlspecialchars($item['cat_icon']) ?>"></i>
                        <?= htmlspecialchars($item['cat_name']) ?>
                    </div>
                    <div class="item-meta">
                        <i class="bi bi-geo-alt"></i>
                        <?= htmlspecialchars($item['location'] ?: 'Not specified') ?>
                    </div>
                    <div class="item-meta">
                        <i class="bi bi-calendar3"></i>
                        <?= $item['date_lost'] ? date('d M Y', strtotime($item['date_lost'])) : 'Date N/A' ?>
                    </div>
                    <div class="item-meta mt-auto pt-2">
                        <i class="bi bi-person"></i>
                        Posted by <?= htmlspecialchars($item['full_name']) ?>
                    </div>
                </div>

                <div class="item-card-footer">
                    <a href="item_details.php?id=<?= $item['id'] ?>"
                       class="btn btn-primary-custom btn-sm w-100">
                        <i class="bi bi-eye me-1"></i>View Details
                    </a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <nav class="mt-5 d-flex justify-content-center" aria-label="Page navigation">
        <ul class="pagination">
            <li class="page-item <?= $page_num<=1 ? 'disabled':'' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$page_num-1])) ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
            <?php for ($p=1; $p<=$total_pages; $p++): ?>
            <li class="page-item <?= $p===$page_num ? 'active':'' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>"><?= $p ?></a>
            </li>
            <?php endfor; ?>
            <li class="page-item <?= $page_num>=$total_pages ? 'disabled':'' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$page_num+1])) ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

    <?php else: ?>
    <div class="empty-state">
        <i class="bi bi-search d-block"></i>
        <h4>No Items Found</h4>
        <p class="small">
            <?= !empty($search) ? 'No results for "'.htmlspecialchars($search).'". Try a different search.' : 'No items match the selected filters.' ?>
        </p>
        <a href="items.php" class="btn btn-outline-custom mt-3">Clear Filters</a>
        <?php if (isset($_SESSION['user_id'])): ?>
        <a href="add_item.php" class="btn btn-primary-custom mt-3 ms-2">Report an Item</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>
</main>

<?php require 'includes/footer.php'; ?>
