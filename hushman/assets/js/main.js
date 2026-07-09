// ============================================================
// HushMan — Main JavaScript
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

  // ── Navbar Scroll Effect ───────────────────────────────────
  const nav = document.getElementById('mainNav');
  if (nav) {
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 50);
    });
  }

  // ── Back to Top ────────────────────────────────────────────
  const btn = document.getElementById('backToTop');
  if (btn) {
    window.addEventListener('scroll', () => btn.classList.toggle('show', window.scrollY > 400));
    btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  // ── Toast Notification ─────────────────────────────────────
  window.showToast = function (message, type = 'success') {
    let container = document.getElementById('toastContainer');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toastContainer';
      container.style.cssText = 'position:fixed;top:80px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:8px;';
      document.body.appendChild(container);
    }
    const colors = { success: '#198754', error: '#dc3545', info: '#0dcaf0', warning: '#ffc107' };
    const toast = document.createElement('div');
    toast.style.cssText = `
      background:${colors[type] || colors.success};color:#fff;
      padding:12px 20px;border-radius:6px;font-size:14px;
      box-shadow:0 4px 12px rgba(0,0,0,.15);
      animation:fadeInUp .3s ease;max-width:320px;
      display:flex;align-items:center;gap:8px;
    `;
    const icons = { success: '✓', error: '✕', info: 'ℹ', warning: '⚠' };
    toast.innerHTML = `<span style="font-weight:700">${icons[type] || '✓'}</span> ${message}`;
    container.appendChild(toast);
    setTimeout(() => { toast.style.animation = 'fadeOut .3s ease forwards'; setTimeout(() => toast.remove(), 300); }, 3000);
  };

  // ── Add to Cart (AJAX) ─────────────────────────────────────
  document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
    btn.addEventListener('click', async function (e) {
      e.preventDefault();
      const productId = this.dataset.productId;
      const size = document.querySelector('.size-btn.active')?.dataset.size || '';
      const color = document.querySelector('.color-dot.active')?.dataset.color || '';
      const qty = document.querySelector('.qty-val')?.value || 1;

      if (!size && document.querySelector('.size-btn')) {
        showToast('Please select a size', 'warning'); return;
      }

      const formData = new FormData();
      formData.append('product_id', productId);
      formData.append('size', size);
      formData.append('color', color);
      formData.append('quantity', qty);
      formData.append('action', 'add');

      try {
        const res = await fetch(siteUrl + '/pages/cart-action.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
          showToast('Added to cart!', 'success');
          document.querySelectorAll('.cart-badge').forEach(b => b.textContent = data.cart_count);
        } else {
          showToast(data.message || 'Error adding to cart', 'error');
        }
      } catch { showToast('Network error, please try again', 'error'); }
    });
  });

  // ── Size Selection ─────────────────────────────────────────
  document.querySelectorAll('.size-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
    });
  });

  // ── Color Selection ────────────────────────────────────────
  document.querySelectorAll('.color-dot').forEach(dot => {
    dot.addEventListener('click', function () {
      document.querySelectorAll('.color-dot').forEach(d => d.classList.remove('active'));
      this.classList.add('active');
    });
  });

  // ── Quantity Buttons ───────────────────────────────────────
  document.querySelectorAll('.qty-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const valEl = this.closest('.qty-group').querySelector('.qty-val');
      let val = parseInt(valEl.value) || 1;
      if (this.dataset.action === 'inc') val = Math.min(val + 1, 99);
      if (this.dataset.action === 'dec') val = Math.max(val - 1, 1);
      valEl.value = val;
    });
  });

  // ── Cart Item Qty Update ────────────────────────────────────
  document.querySelectorAll('.cart-qty-input').forEach(input => {
    input.addEventListener('change', async function () {
      const key = this.dataset.key;
      const qty = this.value;
      const formData = new FormData();
      formData.append('action', 'update');
      formData.append('key', key);
      formData.append('quantity', qty);
      await fetch(siteUrl + '/pages/cart-action.php', { method: 'POST', body: formData });
      location.reload();
    });
  });

  // ── Remove Cart Item ───────────────────────────────────────
  document.querySelectorAll('.remove-cart-item').forEach(btn => {
    btn.addEventListener('click', async function () {
      const key = this.dataset.key;
      const formData = new FormData();
      formData.append('action', 'remove');
      formData.append('key', key);
      await fetch(siteUrl + '/pages/cart-action.php', { method: 'POST', body: formData });
      this.closest('.cart-item').remove();
      showToast('Item removed', 'info');
      setTimeout(() => location.reload(), 300);
    });
  });

  // ── Coupon Apply ───────────────────────────────────────────
  const couponBtn = document.getElementById('applyCoupon');
  if (couponBtn) {
    couponBtn.addEventListener('click', async function () {
      const code = document.getElementById('couponCode').value.trim();
      if (!code) return;
      const formData = new FormData();
      formData.append('coupon', code);
      const res = await fetch(siteUrl + '/pages/apply-coupon.php', { method: 'POST', body: formData });
      const data = await res.json();
      if (data.success) {
        showToast('Coupon applied: ' + data.message, 'success');
        document.getElementById('couponResult').innerHTML = `<span class="text-success small"><i class="bi bi-check-circle me-1"></i>Saved ${data.discount}</span>`;
        setTimeout(() => location.reload(), 1000);
      } else {
        showToast(data.message, 'error');
      }
    });
  }

  // ── Product Image Gallery ──────────────────────────────────
  document.querySelectorAll('.product-thumb').forEach(thumb => {
    thumb.addEventListener('click', function () {
      const main = document.getElementById('mainProductImg');
      if (main) { main.src = this.src; main.parentNode.style.backgroundImage = `url(${this.src})`; }
      document.querySelectorAll('.product-thumb').forEach(t => t.classList.remove('active'));
      this.classList.add('active');
    });
  });

  // ── Admin Sidebar Toggle (Mobile) ──────────────────────────
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('adminSidebar');
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('open'));
  }

  // ── Confirm Delete ─────────────────────────────────────────
  document.querySelectorAll('.confirm-delete').forEach(el => {
    el.addEventListener('click', function (e) {
      if (!confirm('Are you sure you want to delete this? This action cannot be undone.')) {
        e.preventDefault();
      }
    });
  });

  // ── Image Preview (Admin) ──────────────────────────────────
  const imageInput = document.getElementById('productImage');
  if (imageInput) {
    imageInput.addEventListener('change', function () {
      const preview = document.getElementById('imagePreview');
      if (preview && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(this.files[0]);
      }
    });
  }

  // ── Auto-dismiss alerts ────────────────────────────────────
  document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => alert.classList.add('fade'), 4000);
    setTimeout(() => alert.remove(), 4500);
  });
});

// Inject CSS animation keyframe for toast fadeOut
const style = document.createElement('style');
style.textContent = `@keyframes fadeOut { to { opacity:0; transform:translateX(20px); } }`;
document.head.appendChild(style);
