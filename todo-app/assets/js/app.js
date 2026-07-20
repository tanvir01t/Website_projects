/**
 * assets/js/app.js
 * Shell-level behavior shared by every dashboard page:
 * sidebar toggle (mobile), dark mode, user dropdown, keyboard
 * shortcuts, and hiding the initial page loader.
 */

/* ---------------- Page loader ---------------- */
window.addEventListener('load', () => {
    const loader = document.getElementById('page-loader');
    if (loader) {
        loader.style.opacity = '0';
        loader.style.transition = 'opacity 0.25s ease';
        setTimeout(() => loader.remove(), 250);
    }
});

/* ---------------- Sidebar ---------------- */
const SIDEBAR_BREAKPOINT = 900;

function isMobileViewport() {
    return window.innerWidth <= SIDEBAR_BREAKPOINT;
}

// Mobile: slide the sidebar in as an overlay
function openSidebar() {
    document.getElementById('sidebar')?.classList.add('open');
    document.getElementById('sidebarOverlay')?.classList.add('show');
}

function closeSidebar() {
    document.getElementById('sidebar')?.classList.remove('open');
    document.getElementById('sidebarOverlay')?.classList.remove('show');
}

// Desktop: collapse the sidebar down to an icon-only rail, remembered across visits
function setSidebarCollapsed(collapsed) {
    document.body.classList.toggle('sidebar-collapsed', collapsed);
    localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0');
    const btn = document.getElementById('sidebarToggleBtn');
    if (btn) btn.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
}

// The single entry point the navbar button calls — routes to the right
// behavior depending on screen size, so it actually does something on PC.
function toggleSidebar() {
    if (isMobileViewport()) {
        const sidebar = document.getElementById('sidebar');
        sidebar?.classList.contains('open') ? closeSidebar() : openSidebar();
    } else {
        setSidebarCollapsed(!document.body.classList.contains('sidebar-collapsed'));
    }
}

// Restore the desktop collapse preference before paint (avoids a flash of
// the wrong sidebar width), and keep mobile/desktop states from bleeding
// into each other when the window is resized across the breakpoint.
(function () {
    if (!isMobileViewport() && localStorage.getItem('sidebarCollapsed') === '1') {
        document.body.classList.add('sidebar-collapsed');
    }
})();

window.addEventListener('resize', () => {
    if (!isMobileViewport()) {
        closeSidebar(); // clear any mobile overlay state left over from a narrower width
    }
});

/* ---------------- Dark mode toggle ---------------- */
function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    const icon = document.querySelector('#themeToggleBtn i');
    if (icon) {
        icon.classList.toggle('fa-moon', theme !== 'dark');
        icon.classList.toggle('fa-sun', theme === 'dark');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const saved = localStorage.getItem('theme') || 'light';
    applyTheme(saved);

    const themeBtn = document.getElementById('themeToggleBtn');
    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme');
            applyTheme(current === 'dark' ? 'light' : 'dark');
        });
    }

    // Close user dropdown when clicking outside it
    document.addEventListener('click', (e) => {
        const menu = document.querySelector('.user-menu');
        const dropdown = document.getElementById('userDropdown');
        if (dropdown && menu && !menu.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });

    // Global search redirects to the tasks page with a query param
    const searchInput = document.getElementById('globalTaskSearch');
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                window.location.href = (window.BASE_URL || '') + '/tasks/index.php?q=' + encodeURIComponent(searchInput.value.trim());
            }
        });
    }
});

function toggleUserDropdown() {
    document.getElementById('userDropdown')?.classList.toggle('show');
}

/* ---------------- Keyboard shortcuts ---------------- */
document.addEventListener('keydown', (e) => {
    const tag = (e.target.tagName || '').toLowerCase();
    const typing = tag === 'input' || tag === 'textarea';

    // "/" focuses the search box (like GitHub, Slack, etc.)
    if (e.key === '/' && !typing) {
        e.preventDefault();
        document.getElementById('globalTaskSearch')?.focus();
    }

    // "n" opens the "Add Task" modal if present on the page
    if (e.key === 'n' && !typing) {
        const addBtn = document.getElementById('openAddTaskBtn');
        if (addBtn) {
            e.preventDefault();
            addBtn.click();
        }
    }

    // Escape closes any open modal
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.show').forEach(m => m.classList.remove('show'));
        document.getElementById('userDropdown')?.classList.remove('show');
    }
});
