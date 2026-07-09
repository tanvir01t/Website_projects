/* ============================================================
   HushMan — Mobile UX JavaScript
   - Shop filter sidebar toggle
   - Admin mobile sidebar
   - Touch product actions
   - Quantity input helpers
   ============================================================ */
(function () {
  'use strict';

  /* ── Shop Page: Collapsible Filter Sidebar ─────────────── */
  function initShopFilter() {
    var btn     = document.getElementById('filterToggleBtn');
    var sidebar = document.getElementById('shopFilterSidebar');
    var icon    = document.getElementById('filterToggleIcon');
    var closeBtn= document.getElementById('closeSidebarBtn');

    if (!btn || !sidebar) return;

    function isMobile() { return window.innerWidth < 992; }

    function hide() {
      sidebar.style.display = 'none';
      if (icon) icon.className = 'bi bi-chevron-down';
    }
    function show() {
      sidebar.style.display = 'block';
      if (icon) icon.className = 'bi bi-chevron-up';
    }

    // Hide on mobile by default
    if (isMobile()) hide();

    btn.addEventListener('click', function () {
      if (sidebar.style.display === 'none') show(); else hide();
    });

    if (closeBtn) closeBtn.addEventListener('click', hide);

    window.addEventListener('resize', function () {
      if (!isMobile()) show(); else hide();
    });
  }

  /* ── Touch Devices: Always show product action bar ─────── */
  function initTouchActions() {
    if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
      document.querySelectorAll('.product-actions').forEach(function (el) {
        el.style.bottom = '0';
      });
    }
  }

  /* ── Admin: Mobile sidebar with overlay ────────────────── */
  function initAdminSidebar() {
    var sidebar = document.querySelector('.admin-sidebar');
    var topbar  = document.querySelector('.admin-topbar');
    if (!sidebar || !topbar) return;

    // Inject hamburger button
    var menuBtn = document.createElement('button');
    menuBtn.innerHTML = '<i class="bi bi-list" style="font-size:22px"></i>';
    menuBtn.setAttribute('type', 'button');
    menuBtn.className = 'admin-mobile-toggle d-lg-none';
    menuBtn.style.cssText = 'background:none;border:none;padding:4px;cursor:pointer;color:var(--black);margin-right:12px;';
    topbar.insertBefore(menuBtn, topbar.firstChild);

    var overlay = null;

    function openSidebar() {
      sidebar.classList.add('open');
      overlay = document.createElement('div');
      overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99;';
      document.body.appendChild(overlay);
      overlay.addEventListener('click', closeSidebar);
    }
    function closeSidebar() {
      sidebar.classList.remove('open');
      if (overlay && overlay.parentNode) { overlay.parentNode.removeChild(overlay); overlay = null; }
    }

    menuBtn.addEventListener('click', function () {
      if (sidebar.classList.contains('open')) closeSidebar(); else openSidebar();
    });

    window.addEventListener('resize', function () {
      if (window.innerWidth >= 992) closeSidebar();
    });
  }

  /* ── Qty inputs: mobile number keyboard ────────────────── */
  function initQtyInputs() {
    document.querySelectorAll('.qty-val').forEach(function (el) {
      el.setAttribute('inputmode', 'numeric');
      el.setAttribute('pattern', '[0-9]*');
    });
  }

  /* ── Init ──────────────────────────────────────────────── */
  document.addEventListener('DOMContentLoaded', function () {
    initShopFilter();
    initTouchActions();
    initAdminSidebar();
    initQtyInputs();
  });

})();
