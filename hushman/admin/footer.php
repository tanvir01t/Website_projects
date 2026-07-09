    </div><!-- /.admin-content -->
</div><!-- /.admin-main -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
<script>
// Sidebar toggle for mobile
const toggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('adminSidebar');
if (toggle && sidebar) {
    toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
}
// Auto-dismiss alerts
document.querySelectorAll('.alert').forEach(a => {
    setTimeout(() => a.remove(), 4000);
});
</script>

<div class="admin-mobile-overlay" id="adminOverlay"></div>
<script>
(function(){
  function isMob(){ return window.innerWidth < 992; }
  var sidebar = document.querySelector('.admin-sidebar');
  var topbar  = document.querySelector('.admin-topbar');
  var overlay = document.getElementById('adminOverlay');
  if(!sidebar || !topbar) return;

  // Create hamburger button
  var btn = document.createElement('button');
  btn.id = 'adminSidebarToggle';
  btn.setAttribute('type','button');
  btn.innerHTML = '<i class="bi bi-list" style="font-size:22px"></i>';
  btn.style.cssText = 'background:none;border:none;padding:0 8px 0 0;cursor:pointer;color:var(--black);';
  topbar.insertBefore(btn, topbar.firstChild);

  function open_(){
    sidebar.classList.add('open');
    if(overlay){ overlay.classList.add('show'); }
  }
  function close_(){
    sidebar.classList.remove('open');
    if(overlay){ overlay.classList.remove('show'); }
  }

  btn.addEventListener('click', function(){
    sidebar.classList.contains('open') ? close_() : open_();
  });
  if(overlay) overlay.addEventListener('click', close_);
  window.addEventListener('resize', function(){ if(!isMob()) close_(); });
})();
</script>
</body>
</html>
