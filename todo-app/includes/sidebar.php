<?php
/**
 * includes/sidebar.php
 * Left navigation. Expects $activeNav to be set by the including
 * page (e.g. 'dashboard', 'tasks', 'profile') to highlight the
 * current section.
 */
$activeNav = $activeNav ?? '';
?>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<aside class="sidebar" id="sidebar">
    <div style="display:flex; align-items:center; justify-content:space-between;">
        <div class="sidebar-logo"><i class="fa-solid fa-list-check"></i> <span class="nav-label">TaskFlow</span></div>
        <button class="sidebar-close-btn" onclick="closeSidebar()" aria-label="Close menu">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <a href="<?= BASE_URL ?>/dashboard/index.php" class="<?= $activeNav === 'dashboard' ? 'active' : '' ?>" title="Dashboard">
            <i class="fa-solid fa-gauge-high"></i> <span class="nav-label">Dashboard</span>
        </a>
        <a href="<?= BASE_URL ?>/tasks/index.php" class="<?= $activeNav === 'tasks' ? 'active' : '' ?>" title="My Tasks">
            <i class="fa-solid fa-list-check"></i> <span class="nav-label">My Tasks</span>
        </a>
        <a href="<?= BASE_URL ?>/tasks/index.php?filter=today" class="<?= $activeNav === 'today' ? 'active' : '' ?>" title="Today">
            <i class="fa-solid fa-calendar-day"></i> <span class="nav-label">Today</span>
        </a>
        <a href="<?= BASE_URL ?>/tasks/index.php?filter=high" class="<?= $activeNav === 'priority' ? 'active' : '' ?>" title="High Priority">
            <i class="fa-solid fa-flag"></i> <span class="nav-label">High Priority</span>
        </a>
        <a href="<?= BASE_URL ?>/profile/index.php" class="<?= $activeNav === 'profile' ? 'active' : '' ?>" title="Profile">
            <i class="fa-solid fa-user"></i> <span class="nav-label">Profile</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="<?= BASE_URL ?>/auth/logout.php" onclick="return confirm('Log out of TaskFlow?');" title="Logout">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> <span class="nav-label">Logout</span>
        </a>
    </div>
</aside>
