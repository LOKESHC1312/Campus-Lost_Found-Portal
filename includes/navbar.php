<?php
// ============================================================
// includes/navbar.php
// Responsive Bootstrap navigation bar
// ============================================================

// Determine the base path so links work from any subfolder
// Pages in /admin/ need '../' prefix; root pages use ''
$nav_base = $base_url ?? '';
$current  = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top" id="mainNav">
    <div class="container">
        <!-- Brand / Logo -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $nav_base ?>index.php">
            <span class="brand-icon"><i class="bi bi-search-heart-fill"></i></span>
            <span class="brand-text">Campus<span class="brand-accent">L&amp;F</span></span>
        </a>

        <!-- Mobile toggle button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav"
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Nav links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $current === 'index.php' ? 'active' : '' ?>"
                       href="<?= $nav_base ?>index.php">
                        <i class="bi bi-house-door me-1"></i>Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current === 'items.php' ? 'active' : '' ?>"
                       href="<?= $nav_base ?>items.php">
                        <i class="bi bi-grid me-1"></i>Browse Items
                    </a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $current === 'add_item.php' ? 'active' : '' ?>"
                       href="<?= $nav_base ?>add_item.php">
                        <i class="bi bi-plus-circle me-1"></i>Report Item
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Right side -->
            <ul class="navbar-nav ms-auto align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                           href="#" id="userDropdown" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="nav-avatar">
                                <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
                            </div>
                            <?= htmlspecialchars($_SESSION['user_name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="<?= $nav_base ?>dashboard.php">
                                    <i class="bi bi-speedometer2 me-2"></i>My Dashboard
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= $nav_base ?>logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $current === 'login.php' ? 'active' : '' ?>"
                           href="<?= $nav_base ?>login.php">
                            <i class="bi bi-person me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-register" href="<?= $nav_base ?>register.php">
                            <i class="bi bi-person-plus me-1"></i>Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container -->
</nav>
