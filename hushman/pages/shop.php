<?php
// ============================================================
// pages/shop.php — Shop / Product Listing Page
// ============================================================
require_once __DIR__ . '/../includes/functions.php';

$categorySlug = isset($_GET['category']) ? trim($_GET['category']) : null;
$search       = isset($_GET['search'])   ? trim($_GET['search'])   : null;
$saleOnly     = isset($_GET['sale'])     && $_GET['sale'];
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 12;

// If sale filter, override search with discount products
$db = getDB();
$params = [];
$where  = "WHERE p.is_active = 1";
if ($categorySlug) { $where .= " AND c.slug = ?"; $params[] = $categorySlug; }
if ($search)       { $where .= " AND (p.name LIKE ? OR p.description LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($saleOnly)     { $where .= " AND p.discount_percent > 0"; }

$countStmt = $db->prepare("SELECT COUNT(*) FROM products p JOIN categories c ON p.category_id = c.id $where");
$countStmt->execute($params);
$total      = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);
$offset     = ($page - 1) * $perPage;

$qParams = array_merge($params, [$perPage, $offset]);
$stmt = $db->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug,
           (p.price - (p.price * p.discount_percent / 100)) as sale_price
    FROM products p JOIN categories c ON p.category_id = c.id
    $where ORDER BY p.created_at DESC LIMIT ? OFFSET ?
");
$stmt->execute($qParams);
$products   = $stmt->fetchAll();
$categories = getCategories();

// Active category info
$activeCat = null;
if ($categorySlug) {
    foreach($categories as $c) { if($c['slug'] === $categorySlug) { $activeCat = $c; break; } }
}
$pageTitle = $activeCat ? $activeCat['name'] : ($search ? "Search: $search" : 'Shop All');
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<!-- Breadcrumb -->
<div class="page-breadcrumb">
  <div class="container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= SITE_URL ?>">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/pages/shop.php">Shop</a></li>
      <?php if($activeCat): ?><li class="breadcrumb-item active"><?= sanitize($activeCat['name']) ?></li>
      <?php elseif($search): ?><li class="breadcrumb-item active">Search: <?= sanitize($search) ?></li>
      <?php endif; ?>
    </ol></nav>
  </div>
</div>

<div class="container my-5">
  <div class="row">

    <!-- Sidebar Filters -->
    <div class="col-lg-3 mb-3 mb-lg-0">
      <!-- Mobile: Filter Toggle Button -->
      <button id="filterToggleBtn" type="button" class="d-flex d-lg-none w-100 align-items-center justify-content-between mb-2"
        style="background:var(--ivory);border:1.5px solid var(--border);border-radius:var(--radius);padding:11px 16px;font-size:13px;font-weight:600;cursor:pointer;">
        <span><i class="bi bi-funnel-fill me-2" style="color:var(--gold)"></i>Filter &amp; Search</span>
        <i class="bi bi-chevron-down" id="filterIcon"></i>
      </button>
      <div class="card-plain p-4" id="shopSidebar">
        <h6 class="fw-700 mb-3 text-uppercase" style="font-size:11px;letter-spacing:.15em;color:var(--muted)">Categories</h6>
        <div class="list-group list-group-flush">
          <a href="<?= SITE_URL ?>/pages/shop.php" class="list-group-item list-group-item-action d-flex justify-content-between <?= !$categorySlug && !$saleOnly ? 'active' : '' ?>" style="font-size:14px;">
            All Products <span class="badge bg-secondary"><?= array_sum(array_column($categories, 'product_count')) ?></span>
          </a>
          <?php foreach($categories as $cat): ?>
          <a href="<?= SITE_URL ?>/pages/shop.php?category=<?= $cat['slug'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between <?= $categorySlug === $cat['slug'] ? 'active' : '' ?>" style="font-size:14px;">
            <?= sanitize($cat['name']) ?> <span class="badge bg-secondary"><?= $cat['product_count'] ?></span>
          </a>
          <?php endforeach; ?>
        </div>
        <hr>
        <h6 class="fw-700 mb-3 text-uppercase" style="font-size:11px;letter-spacing:.15em;color:var(--muted)">Offers</h6>
        <a href="<?= SITE_URL ?>/pages/shop.php?sale=1" class="list-group-item list-group-item-action <?= $saleOnly ? 'active' : '' ?>" style="font-size:14px;">
          <i class="bi bi-tag-fill text-danger me-2"></i>Sale Items
        </a>
        <hr>
        <!-- Search -->
        <h6 class="fw-700 mb-3 text-uppercase" style="font-size:11px;letter-spacing:.15em;color:var(--muted)">Search</h6>
        <form method="GET" action="<?= SITE_URL ?>/pages/shop.php">
          <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control" placeholder="Search..." value="<?= $search ? sanitize($search) : '' ?>">
            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
          </div>
        </form>
      </div>
    </div>

    <!-- Products Grid -->
    <div class="col-lg-9">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h1 style="font-family:var(--ff-display);font-size:clamp(20px,5vw,28px);font-weight:600;margin:0">
            <?= $activeCat ? sanitize($activeCat['name']) : ($saleOnly ? 'Sale Items' : ($search ? 'Search Results' : 'All Products')) ?>
          </h1>
          <p class="text-muted small mb-0"><?= $total ?> products found <?= $search ? 'for "' . sanitize($search) . '"' : '' ?></p>
        </div>
      </div>

      <?php if(empty($products)): ?>
      <div class="text-center py-5">
        <i class="bi bi-bag-x" style="font-size:56px;color:var(--border)"></i>
        <h4 class="mt-3" style="font-family:var(--ff-display)">No Products Found</h4>
        <p class="text-muted">Try a different search term or browse all categories.</p>
        <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary">View All Products</a>
      </div>
      <?php else: ?>

      <div class="row g-3">
        <?php foreach($products as $product): ?>
        <div class="col-6 col-md-4">
          <div class="product-card">
            <div class="product-img-wrap">
              <a href="<?= SITE_URL ?>/pages/product.php?slug=<?= $product['slug'] ?>">
                <img src="<?= productImageUrl($product['image']) ?>" alt="<?= sanitize($product['name']) ?>">
              </a>
              <?php if($product['discount_percent'] > 0): ?>
              <span class="product-badge badge-sale">-<?= (int)$product['discount_percent'] ?>%</span>
              <?php elseif($product['is_featured']): ?>
              <span class="product-badge">Featured</span>
              <?php endif; ?>
              <?php if($product['stock'] < 5 && $product['stock'] > 0): ?>
              <span class="product-badge" style="top:auto;bottom:12px;background:#dc3545">Only <?= $product['stock'] ?> left</span>
              <?php elseif($product['stock'] == 0): ?>
              <span class="product-badge" style="top:auto;bottom:12px;background:#6c757d">Out of Stock</span>
              <?php endif; ?>
              <div class="product-actions">
                <a href="<?= SITE_URL ?>/pages/product.php?slug=<?= $product['slug'] ?>" class="btn btn-outline-light btn-sm">View</a>
                <?php if($product['stock'] > 0): ?>
                <button class="btn btn-gold btn-sm add-to-cart-btn" data-product-id="<?= $product['id'] ?>">Add to Cart</button>
                <?php endif; ?>
              </div>
            </div>
            <div class="product-body">
              <div class="product-category"><?= sanitize($product['category_name']) ?></div>
              <div class="product-name"><a href="<?= SITE_URL ?>/pages/product.php?slug=<?= $product['slug'] ?>"><?= sanitize($product['name']) ?></a></div>
              <div class="product-price">
                <span class="price-sale"><?= formatPrice($product['sale_price']) ?></span>
                <?php if($product['discount_percent'] > 0): ?>
                <span class="price-original"><?= formatPrice($product['price']) ?></span>
                <span class="price-discount"><?= (int)$product['discount_percent'] ?>% off</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if($totalPages > 1): ?>
      <nav class="mt-5 d-flex justify-content-center">
        <ul class="pagination">
          <?php if($page > 1): ?>
          <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"><i class="bi bi-chevron-left"></i></a></li>
          <?php endif; ?>
          <?php for($i = 1; $i <= $totalPages; $i++): ?>
          <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
          </li>
          <?php endfor; ?>
          <?php if($page < $totalPages): ?>
          <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"><i class="bi bi-chevron-right"></i></a></li>
          <?php endif; ?>
        </ul>
      </nav>
      <?php endif; ?>
      <?php endif; ?>
    </div>

  </div>
</div>

<script>
const siteUrl = '<?= SITE_URL ?>';
// Mobile filter sidebar
(function(){
  var btn = document.getElementById('filterToggleBtn');
  var sb  = document.getElementById('shopSidebar');
  var ic  = document.getElementById('filterIcon');
  if(!btn || !sb) return;
  function mob(){ return window.innerWidth < 992; }
  function hide(){ sb.style.display='none'; if(ic) ic.className='bi bi-chevron-down'; }
  function show(){ sb.style.display='block'; if(ic) ic.className='bi bi-chevron-up'; }
  if(mob()) hide();
  btn.addEventListener('click', function(){ mob() && (sb.style.display==='none' ? show() : hide()); });
  window.addEventListener('resize', function(){ mob() ? hide() : show(); });
})();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
