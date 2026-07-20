<?php
/**
 * includes/navbar.php
 * Top navbar: mobile menu toggle, live search, dark-mode toggle,
 * and the user avatar dropdown (Profile / Change Password / Logout).
 * Requires $_SESSION['user_name'] to be set (i.e. requireLogin() ran).
 */
$userName = $_SESSION['user_name'] ?? 'User';
$initials = strtoupper(substr($userName, 0, 1));

// Pull avatar path if we have a DB-backed user row available
$avatarPath = $avatarPath ?? null;
?>
<header class="navbar">
    <button class="sidebar-toggle-btn icon-btn" id="sidebarToggleBtn" onclick="toggleSidebar()" title="Toggle sidebar" aria-label="Toggle sidebar">
        <i class="fa-solid fa-bars"></i>
    </button>

    <div class="navbar-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="globalTaskSearch" placeholder="Search tasks..." autocomplete="off">
    </div>

    <div class="navbar-actions">
        <button class="icon-btn" id="themeToggleBtn" title="Toggle dark mode" aria-label="Toggle dark mode">
            <i class="fa-solid fa-moon"></i>
        </button>

        <div class="user-menu">
            <button class="user-avatar-btn" onclick="toggleUserDropdown()">
                <?php if ($avatarPath): ?>
                    <img src="<?= BASE_URL . '/' . e($avatarPath) ?>" class="user-avatar" alt="Avatar">
                <?php else: ?>
                    <span class="user-avatar"><?= e($initials) ?></span>
                <?php endif; ?>
                <span class="user-name-label"><?= e($userName) ?></span>
                <i class="fa-solid fa-chevron-down" style="font-size:0.7rem; color:var(--text-muted);"></i>
            </button>

            <div class="user-dropdown" id="userDropdown">
                <a href="<?= BASE_URL ?>/profile/index.php"><i class="fa-solid fa-user"></i> My Profile</a>
                <a href="<?= BASE_URL ?>/profile/index.php#password"><i class="fa-solid fa-key"></i> Change Password</a>
                <a href="<?= BASE_URL ?>/auth/logout.php" class="danger" onclick="return confirm('Log out of TaskFlow?');">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
                </a>
            </div>
        </div>
    </div>
</header>
