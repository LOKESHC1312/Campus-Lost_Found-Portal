<?php
// ============================================================
// admin/admin_sidebar.php — Reusable Admin Sidebar
// Included by all admin pages
// ============================================================
$current_admin_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar" style="width:230px;flex-shrink:0;position:sticky;top:0;height:100vh;overflow-y:auto;">
    <!-- Brand -->
    <div class="px-4 py-3 mb-2 border-bottom" style="border-color:var(--border)!important;">
        <div class="d-flex align-items-center gap-2">
            <div style="width:32px;height:32px;background:linear-gradient(135deg,#ef4444,#b91c1c);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.9rem;">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <div>
                <div class="fw-700 text-white" style="font-size:.9rem;">Admin Panel</div>
                <div class="text-muted" style="font-size:.7rem;">Campus L&F</div>
            </div>
        </div>
    </div>

    <nav class="mt-2">
        <a href="admin_dashboard.php"
           class="sidebar-link <?= $current_admin_page==='admin_dashboard.php'?'active':'' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="admin_posts.php"
           class="sidebar-link <?= $current_admin_page==='admin_posts.php'?'active':'' ?>">
            <i class="bi bi-collection"></i> All Posts
        </a>
        <a href="admin_users.php"
           class="sidebar-link <?= $current_admin_page==='admin_users.php'?'active':'' ?>">
            <i class="bi bi-people"></i> Manage Users
        </a>

        <div class="px-4 pt-3 pb-1">
            <span class="text-muted" style="font-size:.65rem;text-transform:uppercase;letter-spacing:1px;font-weight:700;">Student Portal</span>
        </div>
        <a href="../index.php" class="sidebar-link" target="_blank">
            <i class="bi bi-box-arrow-up-right"></i> View Site
        </a>

        <div class="mt-auto px-4 pt-4">
            <a href="admin_logout.php" class="btn btn-danger btn-sm w-100">
                <i class="bi bi-box-arrow-right me-1"></i>Logout
            </a>
        </div>
    </nav>
</aside>
