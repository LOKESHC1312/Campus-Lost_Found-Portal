<?php
// ============================================================
// index.php — Public Homepage
// Shows hero, stats, and recent items
// ============================================================

$page_title = 'Home';
$base_url   = '';
require 'includes/db.php';
require 'includes/header.php';
require 'includes/navbar.php';

// --- Fetch live stats ---
$total_lost    = $conn->query("SELECT COUNT(*) FROM items WHERE type='lost'  AND status='active'")->fetch_row()[0];
$total_found   = $conn->query("SELECT COUNT(*) FROM items WHERE type='found' AND status='active'")->fetch_row()[0];
$total_returned= $conn->query("SELECT COUNT(*) FROM items WHERE status='returned'")->fetch_row()[0];
$total_users   = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];

// --- Fetch 6 most recent active items ---
$recent_stmt = $conn->prepare("
    SELECT i.*, c.name AS category_name, c.icon AS category_icon, u.full_name
    FROM items i
    JOIN categories c ON i.category_id = c.id
    JOIN users u      ON i.user_id     = u.id
    WHERE i.status = 'active'
    ORDER BY i.created_at DESC
    LIMIT 6
");
$recent_stmt->execute();
$recent_items = $recent_stmt->get_result();
?>

<!-- ===== HERO ===== -->
<section class="hero-section">
    <div class="container position-relative" style="z-index:1;">
        <div class="row align-items-center gy-5">
            <div class="col-lg-7">
                <div class="hero-badge">
                    <i class="bi bi-mortarboard-fill"></i>
                    Campus Lost &amp; Found Portal
                </div>
                <h1 class="hero-title">
                    Lost Something?<br>
                    <span class="highlight">We'll Help You Find It.</span>
                </h1>
                <p class="hero-subtitle">
                    A dedicated platform for college students to report lost items, post found belongings,
                    and connect with each other to recover what matters most.
                </p>

                <!-- Search bar -->
                <form action="items.php" method="GET" class="hero-search mb-4">
                    <i class="bi bi-search text-muted ms-2 fs-5"></i>
                    <input type="text" name="search" class="form-control"
                           placeholder="Search for a lost item, book, phone, ID card…"
                           autocomplete="off">
                    <button type="submit" class="btn btn-primary-custom px-4 flex-shrink-0">
                        Search
                    </button>
                </form>

                <!-- CTA buttons -->
                <div class="d-flex flex-wrap gap-3">
                    <a href="items.php?type=lost" class="btn btn-primary-custom">
                        <i class="bi bi-search me-2"></i>Find Lost Items
                    </a>
                    <a href="items.php?type=found" class="btn btn-outline-custom">
                        <i class="bi bi-hand-thumbs-up me-2"></i>Found Items
                    </a>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="register.php" class="btn btn-outline-custom">
                        <i class="bi bi-person-plus me-2"></i>Register Free
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-5">
                <!-- Stats grid -->
                <div class="row g-3">
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-number" id="cntLost"><?= $total_lost ?></div>
                            <div class="stat-label"><i class="bi bi-question-circle me-1 text-danger"></i>Lost Items</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-number" id="cntFound"><?= $total_found ?></div>
                            <div class="stat-label"><i class="bi bi-check-circle me-1 text-success"></i>Found Items</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-number" id="cntReturned"><?= $total_returned ?></div>
                            <div class="stat-label"><i class="bi bi-arrow-repeat me-1 text-warning"></i>Returned</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-number" id="cntUsers"><?= $total_users ?></div>
                            <div class="stat-label"><i class="bi bi-people me-1 text-info"></i>Students</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== HOW IT WORKS ===== -->
<section class="section-pad bg-surface">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">How It Works</h2>
            <p class="section-subtitle">Three simple steps to recover or return an item</p>
        </div>
        <div class="row g-4 justify-content-center">
            <?php
            $steps = [
                ['icon'=>'bi-person-plus-fill','color'=>'var(--primary)','title'=>'1. Register &amp; Login','desc'=>'Create a free account with your college email and log in to access all features.'],
                ['icon'=>'bi-pencil-square',   'color'=>'var(--secondary)','title'=>'2. Report the Item','desc'=>'Post a lost or found item with a photo, description, and location details.'],
                ['icon'=>'bi-envelope-check',  'color'=>'var(--success)','title'=>'3. Connect &amp; Recover','desc'=>'Browse posts, search by category, and contact the poster to arrange return.'],
            ];
            foreach ($steps as $step): ?>
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                         style="width:72px;height:72px;border-radius:20px;background:<?= $step['color'] ?>22;font-size:1.8rem;color:<?= $step['color'] ?>;">
                        <i class="<?= $step['icon'] ?>"></i>
                    </div>
                    <h5 class="fw-700"><?= $step['title'] ?></h5>
                    <p class="text-muted small"><?= $step['desc'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== RECENT ITEMS ===== -->
<section class="section-pad">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="section-title">Recent Posts</h2>
                <p class="section-subtitle mb-0">Latest lost &amp; found reports on campus</p>
            </div>
            <a href="items.php" class="btn btn-outline-custom btn-sm d-none d-md-inline-flex">
                View All <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <?php if ($recent_items->num_rows > 0): ?>
        <div class="row g-4">
            <?php while ($item = $recent_items->fetch_assoc()): ?>
            <div class="col-sm-6 col-lg-4">
                <div class="item-card">
                    <!-- Image / placeholder -->
                    <div class="item-card-img">
                        <?php if ($item['image']): ?>
                            <img src="uploads/<?= htmlspecialchars($item['image']) ?>"
                                 alt="<?= htmlspecialchars($item['item_name']) ?>">
                        <?php else: ?>
                            <div class="item-card-img-placeholder">
                                <i class="<?= htmlspecialchars($item['category_icon']) ?>"></i>
                            </div>
                        <?php endif; ?>
                        <span class="type-badge <?= $item['type'] ?>">
                            <?= $item['type'] === 'lost'
                                ? '<i class="bi bi-question-circle me-1"></i>Lost'
                                : '<i class="bi bi-check-circle me-1"></i>Found' ?>
                        </span>
                        <span class="status-badge <?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span>
                    </div>

                    <div class="item-card-body">
                        <div class="item-card-title"><?= htmlspecialchars($item['item_name']) ?></div>
                        <div class="item-meta">
                            <i class="bi bi-tag"></i>
                            <?= htmlspecialchars($item['category_name']) ?>
                        </div>
                        <div class="item-meta">
                            <i class="bi bi-geo-alt"></i>
                            <?= htmlspecialchars($item['location'] ?: 'Location not specified') ?>
                        </div>
                        <div class="item-meta">
                            <i class="bi bi-calendar3"></i>
                            <?= $item['date_lost'] ? date('d M Y', strtotime($item['date_lost'])) : 'Date not specified' ?>
                        </div>
                    </div>

                    <div class="item-card-footer">
                        <a href="item_details.php?id=<?= $item['id'] ?>" class="btn btn-primary-custom btn-sm w-100">
                            <i class="bi bi-eye me-1"></i>View Details
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <div class="text-center mt-4 d-md-none">
            <a href="items.php" class="btn btn-outline-custom">View All Items</a>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-inbox d-block"></i>
            <h4>No items posted yet</h4>
            <p class="small">Be the first to report a lost or found item.</p>
            <a href="add_item.php" class="btn btn-primary-custom mt-2">Report an Item</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ===== CALL TO ACTION ===== -->
<?php if (!isset($_SESSION['user_id'])): ?>
<section class="section-pad bg-surface">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title mb-3">Ready to Help Your Campus?</h2>
            <p class="section-subtitle mb-4">
                Join hundreds of students already using the portal to recover lost items.
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="register.php" class="btn btn-primary-custom px-5">
                    <i class="bi bi-person-plus me-2"></i>Create Account
                </a>
                <a href="login.php" class="btn btn-outline-custom px-5">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
