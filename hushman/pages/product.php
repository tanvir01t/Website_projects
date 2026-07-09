<?php
// ============================================================
// pages/product.php — Product Detail Page
// ============================================================
require_once __DIR__ . '/../includes/functions.php';

$slug    = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$product = $slug ? getProductBySlug($slug) : null;

if (!$product) {
    header('Location: ' . SITE_URL . '/pages/shop.php');
    exit;
}

$reviews    = getProductReviews($product['id']);
$ratingData = getAverageRating($product['id']);
$sizes      = array_filter(array_map('trim', explode(',', $product['sizes'])));
$colors     = array_filter(array_map('trim', explode(',', $product['colors'] ?? '')));

// Related products (same category)
$db = getDB();
$stmt = $db->prepare("SELECT p.*, (p.price - (p.price * p.discount_percent / 100)) as sale_price FROM products p WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1 ORDER BY RAND() LIMIT 4");
$stmt->execute([$product['category_id'], $product['id']]);
$related = $stmt->fetchAll();

$pageTitle = $product['name'];
$pageDesc  = substr(strip_tags($product['description']), 0, 155);
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<!-- Breadcrumb -->
<div class="page-breadcrumb">
  <div class="container">
    <nav><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= SITE_URL ?>">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/pages/shop.php">Shop</a></li>
      <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/pages/shop.php?category=<?= $product['category_slug'] ?>"><?= sanitize($product['category_name']) ?></a></li>
      <li class="breadcrumb-item active"><?= sanitize($product['name']) ?></li>
    </ol></nav>
  </div>
</div>

<div class="container my-5">
  <div class="row g-3 g-lg-5">

    <!-- Product Images -->
    <div class="col-md-6">
      <img id="mainProductImg" class="product-detail-img-main mb-3"
           src="<?= productImageUrl($product['image']) ?>"
           alt="<?= sanitize($product['name']) ?>">
      <!-- Thumbnails -->
      <?php
      $imgs = array_filter([$product['image'], $product['image2'], $product['image3']]);
      if(count($imgs) > 1): ?>
      <div class="d-flex gap-2 mt-2">
        <?php foreach($imgs as $img): ?>
        <img src="<?= productImageUrl($img) ?>"
             class="product-thumb <?= $img === $product['image'] ? 'active' : '' ?>"
             style="width:60px;height:72px;object-fit:cover;border-radius:4px;cursor:pointer;border:2px solid <?= $img === $product['image'] ? 'var(--gold)' : 'var(--border)' ?>;transition:border-color .2s"
             alt="Product thumbnail">
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Product Details -->
    <div class="col-md-6">
      <span class="product-category mb-2 d-block"><?= sanitize($product['category_name']) ?></span>
      <h1 style="font-family:var(--ff-display);font-size:clamp(22px,5vw,34px);font-weight:700;line-height:1.2;margin-bottom:12px">
        <?= sanitize($product['name']) ?>
      </h1>

      <!-- Rating Summary -->
      <?php if($ratingData['count'] > 0): ?>
      <div class="d-flex align-items-center gap-2 mb-3">
        <div style="color:var(--gold)">
          <?php for($i=1;$i<=5;$i++): ?>
          <i class="bi bi-star<?= $i <= round($ratingData['avg']) ? '-fill' : '' ?>"></i>
          <?php endfor; ?>
        </div>
        <span class="text-muted small"><?= number_format($ratingData['avg'],1) ?> (<?= $ratingData['count'] ?> reviews)</span>
      </div>
      <?php endif; ?>

      <!-- Price -->
      <div class="d-flex align-items-center gap-3 mb-4">
        <span style="font-family:var(--ff-display);font-size:clamp(20px,5vw,32px);font-weight:700"><?= formatPrice($product['sale_price']) ?></span>
        <?php if($product['discount_percent'] > 0): ?>
        <span class="text-muted text-decoration-line-through fs-5"><?= formatPrice($product['price']) ?></span>
        <span class="badge" style="background:var(--danger);font-size:13px">Save <?= (int)$product['discount_percent'] ?>%</span>
        <?php endif; ?>
      </div>

      <p style="color:var(--mid);line-height:1.75;font-size:15px;margin-bottom:28px"><?= nl2br(sanitize($product['description'])) ?></p>

      <!-- Size Selection -->
      <?php if(!empty($sizes)): ?>
      <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <label class="form-label mb-0">Size</label>
          <a href="#" class="text-muted small">Size Guide <i class="bi bi-rulers ms-1"></i></a>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <?php foreach($sizes as $s): ?>
          <button class="size-btn" data-size="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></button>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Color Selection -->
      <?php if(!empty($colors)): ?>
      <div class="mb-4">
        <label class="form-label mb-2">Color: <span id="selectedColor" class="text-muted fw-normal"><?= htmlspecialchars($colors[0]) ?></span></label>
        <div class="d-flex gap-2 flex-wrap">
          <?php
          $colorMap = ['Black'=>'#1a1a1a','White'=>'#f8f5f0','Grey'=>'#9e9e9e','Navy'=>'#1b2a4a','Blue'=>'#1e6091','Sky Blue'=>'#87ceeb','Light Blue'=>'#add8e6','Dark Blue'=>'#1a237e','Red'=>'#c62828','Burgundy'=>'#880e4f','Olive'=>'#6d6b35','Khaki'=>'#c8b560','Beige'=>'#d4b896','Pink'=>'#f48fb1','Brown'=>'#795548','Tan'=>'#a0785a','Charcoal'=>'#434343','Green'=>'#2e7d32'];
          foreach($colors as $ci => $c):
            $hex = $colorMap[$c] ?? '#888';
          ?>
          <div class="color-dot <?= $ci===0?'active':'' ?>"
               data-color="<?= htmlspecialchars($c) ?>"
               title="<?= htmlspecialchars($c) ?>"
               style="background:<?= $hex ?>"
               onclick="document.getElementById('selectedColor').textContent='<?= htmlspecialchars($c) ?>'">
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Quantity + Add to Cart -->
      <div class="d-flex gap-3 align-items-center mb-4 flex-wrap">
        <div class="qty-group">
          <button class="qty-btn" data-action="dec">−</button>
          <input type="number" class="qty-val" value="1" min="1" max="<?= $product['stock'] ?>">
          <button class="qty-btn" data-action="inc">+</button>
        </div>
        <?php if($product['stock'] > 0): ?>
        <button class="btn btn-primary btn-lg flex-grow-1 add-to-cart-btn" data-product-id="<?= $product['id'] ?>">
          <i class="bi bi-bag-plus me-2"></i>Add to Cart
        </button>
        <?php else: ?>
        <button class="btn btn-secondary btn-lg flex-grow-1" disabled>Out of Stock</button>
        <?php endif; ?>
      </div>

      <!-- Stock -->
      <?php if($product['stock'] > 0 && $product['stock'] <= 10): ?>
      <p class="text-danger small mb-3"><i class="bi bi-exclamation-circle me-1"></i>Only <?= $product['stock'] ?> items left in stock!</p>
      <?php elseif($product['stock'] > 10): ?>
      <p class="text-success small mb-3"><i class="bi bi-check-circle me-1"></i>In Stock (<?= $product['stock'] ?> available)</p>
      <?php endif; ?>

      <!-- Trust Badges -->
      <div class="p-3 rounded" style="background:var(--ivory);border:1px solid var(--border)">
        <div class="row g-2 text-center">
          <div class="col-4"><i class="bi bi-shield-check text-success d-block mb-1"></i><span style="font-size:11px;color:var(--muted)">Authentic</span></div>
          <div class="col-4"><i class="bi bi-truck text-primary d-block mb-1"></i><span style="font-size:11px;color:var(--muted)">Fast Delivery</span></div>
          <div class="col-4"><i class="bi bi-arrow-counterclockwise text-warning d-block mb-1"></i><span style="font-size:11px;color:var(--muted)">Easy Return</span></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Reviews Section -->
  <div class="row mt-5 pt-4" style="border-top:1px solid var(--border)">
    <div class="col-12">
      <h3 style="font-family:var(--ff-display);font-size:28px;margin-bottom:28px">Customer Reviews</h3>
      <?php if(empty($reviews)): ?>
      <p class="text-muted">No reviews yet. Be the first to review this product!</p>
      <?php else: ?>
      <div class="row g-4">
        <?php foreach($reviews as $r): ?>
        <div class="col-md-6">
          <div class="review-card">
            <div class="review-stars"><?= str_repeat('<i class="bi bi-star-fill"></i>', $r['rating']) ?></div>
            <p class="review-text">"<?= sanitize($r['comment']) ?>"</p>
            <div class="d-flex align-items-center gap-3">
              <div class="reviewer-avatar"><?= strtoupper(substr($r['user_name'],0,1)) ?></div>
              <div>
                <div class="reviewer-name"><?= sanitize($r['user_name']) ?></div>
                <div class="reviewer-info"><?= formatDate($r['created_at']) ?></div>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Leave Review Form -->
      <?php if(isLoggedIn()): ?>
      <div class="mt-4 p-4 card-plain">
        <h5 style="font-family:var(--ff-display)">Write a Review</h5>
        <form method="POST" action="<?= SITE_URL ?>/pages/submit-review.php">
          <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
          <div class="mb-3">
            <label class="form-label">Rating</label>
            <div class="d-flex gap-2">
              <?php for($i=1;$i<=5;$i++): ?>
              <label style="cursor:pointer">
                <input type="radio" name="rating" value="<?= $i ?>" style="display:none" required>
                <i class="bi bi-star-fill" style="font-size:20px;color:var(--border);transition:color .2s" onmouseover="highlightStars(<?= $i ?>)" onclick="selectStar(<?= $i ?>)" id="star<?= $i ?>"></i>
              </label>
              <?php endfor; ?>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Comment</label>
            <textarea name="comment" class="form-control" rows="3" placeholder="Share your thoughts about this product..." required></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Submit Review</button>
        </form>
      </div>
      <?php else: ?>
      <div class="mt-3 p-3 bg-ivory rounded">
        <a href="<?= SITE_URL ?>/pages/login.php">Login</a> to leave a review.
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Related Products -->
  <?php if(!empty($related)): ?>
  <div class="row mt-5">
    <div class="col-12">
      <h3 style="font-family:var(--ff-display);font-size:28px;margin-bottom:28px">You May Also Like</h3>
      <div class="row g-3">
        <?php foreach($related as $rp): ?>
        <div class="col-6 col-md-3">
          <div class="product-card">
            <div class="product-img-wrap">
              <a href="<?= SITE_URL ?>/pages/product.php?slug=<?= $rp['slug'] ?>">
                <img src="<?= productImageUrl($rp['image']) ?>" alt="<?= sanitize($rp['name']) ?>">
              </a>
            </div>
            <div class="product-body">
              <div class="product-name"><a href="<?= SITE_URL ?>/pages/product.php?slug=<?= $rp['slug'] ?>"><?= sanitize($rp['name']) ?></a></div>
              <div class="product-price"><span class="price-sale"><?= formatPrice($rp['sale_price']) ?></span></div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
const siteUrl = '<?= SITE_URL ?>';
function highlightStars(n) {
  for(let i=1;i<=5;i++) document.getElementById('star'+i).style.color = i<=n ? 'var(--gold)' : 'var(--border)';
}
function selectStar(n) {
  document.querySelector('input[name="rating"][value="'+n+'"]').checked = true;
  for(let i=1;i<=5;i++) document.getElementById('star'+i).style.color = i<=n ? 'var(--gold)' : 'var(--border)';
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
