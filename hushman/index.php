<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'HushMan | Premium Men\'s Fashion';
$pageDesc  = 'Shop the finest men\'s fashion — T-shirts, Shirts, Pants, Jackets & Accessories.';

$featuredProducts = getFeaturedProducts(8);
$categories       = getCategories();

$testimonials = [
  ['name'=>'Rahim Ahmed','city'=>'Dhaka','rating'=>5,'text'=>'Amazing quality! The shirt I ordered fits perfectly and the fabric is premium. Will definitely shop again.','initial'=>'R'],
  ['name'=>'Tanvir Hasan','city'=>'Chittagong','rating'=>5,'text'=>'Fast delivery and great packaging. The chino pants look exactly like the photos — really impressed.','initial'=>'T'],
  ['name'=>'Farhan Kabir','city'=>'Sylhet','rating'=>5,'text'=>'Best men\'s fashion store online. Great prices for premium quality. My go-to for office wear.','initial'=>'F'],
];
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<!-- ═══════════════════════════════════════════════════════════
     HERO SECTION — Left Category Sidebar + Right Slider
     ═══════════════════════════════════════════════════════════ -->
<section class="hero-section-new">
  <div class="container-fluid px-0">
    <div class="hero-layout">

      <!-- LEFT: Category Sidebar (25%) -->
      <div class="hero-sidebar">
        <div class="sidebar-header">
          <i class="bi bi-grid-fill"></i> All Categories
        </div>
        <ul class="sidebar-cat-list">
          <?php foreach($categories as $cat): ?>
          <li class="sidebar-cat-item">
            <a href="<?= SITE_URL ?>/pages/shop.php?category=<?= $cat['slug'] ?>">
              <span class="sidebar-cat-icon">
                <?php
                  $icons = [
                    't-shirts'    => 'bi-circle-square',
                    'shirts'      => 'bi-person-lines-fill',
                    'pants'       => 'bi-aspect-ratio',
                    'jackets'     => 'bi-wind',
                    'accessories' => 'bi-watch',
                    'shoes'       => 'bi-bag',
                    'hoodies'     => 'bi-layers',
                    'polos'       => 'bi-grid-1x2',
                  ];
                  $icon = $icons[strtolower($cat['slug'])] ?? 'bi-tag';
                ?>
                <i class="bi <?= $icon ?>"></i>
              </span>
              <span class="sidebar-cat-name"><?= sanitize($cat['name']) ?></span>
              <span class="sidebar-cat-count"><?= $cat['product_count'] ?></span>
              <i class="bi bi-chevron-right sidebar-cat-arrow"></i>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
        <div class="sidebar-promo">
          <div class="sidebar-promo-inner">
            <div class="sidebar-promo-tag">Limited Offer</div>
            <div class="sidebar-promo-code">WELCOME20</div>
            <div class="sidebar-promo-desc">20% off first order above ৳1,000</div>
          </div>
        </div>
      </div>

      <!-- RIGHT: Hero Slider (75%) — নিজের ছবি দিন -->
      <div class="hero-slider-wrap">
        <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="4500">
          <div class="carousel-inner">

            <!-- Slide 1 — ছবি: assets/images/hero-slide-1.jpg -->
            <div class="carousel-item active">
              <div class="hero-slide" style="background-image:url('assets/images/pic3.jpg');">
                <div class="hero-slide-overlay"></div>
                <div class="hero-slide-content">
                  <span class="slide-badge animate-in">New Collection 2025</span>
                  <h1 class="slide-title animate-in animate-delay-1">Dress With<br><em>Intention.</em></h1>
                  <p class="slide-subtitle animate-in animate-delay-2">Premium men's fashion for the modern gentleman.</p>
                  <div class="slide-actions animate-in animate-delay-3">
                    <a href="<?= SITE_URL ?>/pages/shop.php" class="btn-slide-primary">Shop Now <i class="bi bi-arrow-right ms-2"></i></a>
                    <a href="#categories" class="btn-slide-secondary">Explore</a>
                  </div>
                </div>
              </div>
            </div>

            <!-- Slide 2 — ছবি: assets/images/hero-slide-2.jpg -->
            <div class="carousel-item">
              <div class="hero-slide" style="background-image:url('assets/images/pic1.jpg');">
                <div class="hero-slide-overlay"></div>
                <div class="hero-slide-content">
                  <span class="slide-badge animate-in">Exclusive Styles</span>
                  <h1 class="slide-title animate-in animate-delay-1">Premium<br><em>Craftsmanship.</em></h1>
                  <p class="slide-subtitle animate-in animate-delay-2">Carefully selected fabrics. Timeless silhouettes.</p>
                  <div class="slide-actions animate-in animate-delay-3">
                    <a href="<?= SITE_URL ?>/pages/shop.php?category=shirts" class="btn-slide-primary">Shop Shirts <i class="bi bi-arrow-right ms-2"></i></a>
                    <a href="#categories" class="btn-slide-secondary">View All</a>
                  </div>
                </div>
              </div>
            </div>

            <!-- Slide 3 — ছবি: assets/images/hero-slide-3.jpg -->
            <div class="carousel-item">
              <div class="hero-slide" style="background-image:url('assets/images/pic2.jpg');" >
                <div class="hero-slide-overlay"></div>
                <div class="hero-slide-content">
                  <span class="slide-badge animate-in">Weekend Deals</span>
                  <h1 class="slide-title animate-in animate-delay-1">Style Has<br><em>No Limits.</em></h1>
                  <p class="slide-subtitle animate-in animate-delay-2">Up to 40% off selected styles. This weekend only.</p>
                  <div class="slide-actions animate-in animate-delay-3">
                    <a href="<?= SITE_URL ?>/pages/shop.php" class="btn-slide-primary">See Deals <i class="bi bi-arrow-right ms-2"></i></a>
                    <a href="#categories" class="btn-slide-secondary">Browse More</a>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="hero-nav-btn"><i class="bi bi-chevron-left"></i></span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="hero-nav-btn"><i class="bi bi-chevron-right"></i></span>
          </button>

          <div class="carousel-indicators hero-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
          </div>
        </div>

        <div class="hero-stats-bar">
          <div class="hstat"><span class="hstat-num">500+</span><span class="hstat-lbl">Products</span></div>
          <div class="hstat-divider"></div>
          <div class="hstat"><span class="hstat-num">10K+</span><span class="hstat-lbl">Customers</span></div>
          <div class="hstat-divider"></div>
          <div class="hstat"><span class="hstat-num">4.9★</span><span class="hstat-lbl">Avg Rating</span></div>
          <div class="hstat-divider"></div>
          <div class="hstat"><span class="hstat-num">Free</span><span class="hstat-lbl">Over ৳2,000</span></div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ── Features Strip ────────────────────────────────────────── -->
<section class="features-strip">
  <div class="container">
    <div class="row g-2">
      <div class="col-6 col-md-3"><div class="feature-item">
        <span class="feature-icon"><i class="bi bi-truck"></i></span>
        <div class="feature-title">Free Delivery</div>
        <div class="feature-desc">On orders over ৳2,000</div>
      </div></div>
      <div class="col-6 col-md-3"><div class="feature-item">
        <span class="feature-icon"><i class="bi bi-shield-check"></i></span>
        <div class="feature-title">100% Authentic</div>
        <div class="feature-desc">Quality guaranteed</div>
      </div></div>
      <div class="col-6 col-md-3"><div class="feature-item">
        <span class="feature-icon"><i class="bi bi-arrow-counterclockwise"></i></span>
        <div class="feature-title">Easy Returns</div>
        <div class="feature-desc">7-day return policy</div>
      </div></div>
      <div class="col-6 col-md-3"><div class="feature-item">
        <span class="feature-icon"><i class="bi bi-headset"></i></span>
        <div class="feature-title">24/7 Support</div>
        <div class="feature-desc">Always here to help</div>
      </div></div>
    </div>
  </div>
</section>

<!-- ── Categories Section ─────────────────────────────────────── -->
<section class="section-pad" id="categories">
  <div class="container">
    <div class="text-center mb-5">
      <span class="section-label">Browse By</span>
      <h2 class="section-title">Shop Categories</h2>
      <p class="section-subtitle">Find exactly what you're looking for in our curated collections</p>
    </div>
    <div class="row g-3">
      <?php foreach(array_slice($categories, 0, 5) as $i => $cat): ?>
      <div class="col-6 col-md-4 col-lg-<?= $i < 2 ? '6' : '4' ?>">
        <a href="<?= SITE_URL ?>/pages/shop.php?category=<?= $cat['slug'] ?>" class="category-card d-block">
          <img src="<?= SITE_URL ?>/assets/images/categories/<?= htmlspecialchars($cat['image']) ?>"
               alt="<?= sanitize($cat['name']) ?>"
               onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder-cat.jpg'">
          <div class="category-card-overlay">
            <div>
              <div class="category-name"><?= sanitize($cat['name']) ?></div>
              <div class="category-count"><?= $cat['product_count'] ?> Products</div>
            </div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── Featured Products ──────────────────────────────────────── -->
<section class="section-pad bg-ivory">
  <div class="container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 mb-md-5 gap-2">
      <div>
        <span class="section-label">Handpicked For You</span>
        <h2 class="section-title mb-0">Featured Products</h2>
      </div>
      <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-outline-dark d-none d-md-inline-flex">View All <i class="bi bi-arrow-right ms-2"></i></a>
    </div>
    <div class="row g-3 g-md-4">
      <?php foreach($featuredProducts as $product): ?>
      <div class="col-6 col-md-4 col-lg-3">
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
            <div class="product-actions">
              <a href="<?= SITE_URL ?>/pages/product.php?slug=<?= $product['slug'] ?>" class="btn btn-outline-light btn-sm">View</a>
              <button class="btn btn-gold btn-sm add-to-cart-btn" data-product-id="<?= $product['id'] ?>">Add to Cart</button>
            </div>
          </div>
          <div class="product-body">
            <div class="product-category"><?= sanitize($product['category_name']) ?></div>
            <div class="product-name"><a href="<?= SITE_URL ?>/pages/product.php?slug=<?= $product['slug'] ?>"><?= sanitize($product['name']) ?></a></div>
            <div class="product-price">
              <span class="price-sale"><?= formatPrice($product['sale_price']) ?></span>
              <?php if($product['discount_percent'] > 0): ?>
              <span class="price-original"><?= formatPrice($product['price']) ?></span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4 d-md-none">
      <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-outline-dark">View All Products</a>
    </div>
  </div>
</section>

<!-- ── Promo Banner ───────────────────────────────────────────── -->
<section style="background:linear-gradient(135deg,#0d0d0d,#1a1a1a);padding:64px 0;">
  <div class="container text-center">
    <span class="section-label">Limited Time</span>
    <h2 class="section-title text-white mb-3">Use Code <span style="color:var(--gold)">WELCOME20</span> for 20% Off</h2>
    <p class="text-white-50 mb-4">On your first order above ৳1,000. Don't miss out!</p>
    <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-gold btn-lg">Shop The Sale</a>
  </div>
</section>

<!-- ── Testimonials ───────────────────────────────────────────── -->
<section class="section-pad">
  <div class="container">
    <div class="text-center mb-5">
      <span class="section-label">What People Say</span>
      <h2 class="section-title">Customer Reviews</h2>
    </div>
    <div class="row g-4">
      <?php foreach($testimonials as $t): ?>
      <div class="col-md-4">
        <div class="review-card">
          <div class="review-stars"><?= str_repeat('<i class="bi bi-star-fill"></i>', $t['rating']) ?></div>
          <p class="review-text">"<?= htmlspecialchars($t['text']) ?>"</p>
          <div class="d-flex align-items-center gap-3">
            <div class="reviewer-avatar"><?= $t['initial'] ?></div>
            <div>
              <div class="reviewer-name"><?= htmlspecialchars($t['name']) ?></div>
              <div class="reviewer-info"><i class="bi bi-geo-alt me-1"></i><?= $t['city'] ?></div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── FAQ ────────────────────────────────────────────────────── -->
<section class="section-pad bg-ivory" id="faq">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="text-center mb-5">
          <span class="section-label">Got Questions?</span>
          <h2 class="section-title">Frequently Asked Questions</h2>
        </div>
        <div class="accordion" id="faqAccordion">
          <?php
          $faqs = [
            ['How long does delivery take?','We deliver within 3–5 business days in Dhaka and 5–7 business days in other cities.'],
            ['Can I return or exchange an item?','Yes! We offer hassle-free 7-day returns and exchanges. Items must be unused and in original packaging.'],
            ['What sizes do you offer?','We carry sizes S, M, L, XL, and XXL. Pants are available from waist size 28 to 38 inches.'],
            ['How do I track my order?','Visit your Order History page or contact us with your order number to get status updates.'],
            ['What payment methods do you accept?','We accept Cash on Delivery (COD), bKash, Nagad, and major credit/debit cards.'],
            ['Do you offer discounts for bulk orders?','Yes, contact us at info@HushMan.com to discuss bulk pricing.'],
          ];
          foreach($faqs as $i => $faq): ?>
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $i ?>">
                <?= htmlspecialchars($faq[0]) ?>
              </button>
            </h2>
            <div id="faq<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#faqAccordion">
              <div class="accordion-body"><?= htmlspecialchars($faq[1]) ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── Contact ────────────────────────────────────────────────── -->
<section class="section-pad" id="contact">
  <div class="container">
    <div class="text-center mb-5">
      <span class="section-label">Get In Touch</span>
      <h2 class="section-title">Contact Us</h2>
      <p class="section-subtitle">We'd love to hear from you. Reach out through any channel.</p>
    </div>
    <div class="row g-4 justify-content-center">
      <div class="col-md-4">
        <div class="contact-card">
          <span class="contact-icon"><i class="bi bi-telephone-fill"></i></span>
          <h5 class="mb-1" style="font-family:var(--ff-display)">Call Us</h5>
          <p class="text-muted small mb-2">Sat–Thu: 10AM – 8PM</p>
          <a href="tel:+8801711234567" class="fw-600">+880 171 123 4567</a>
        </div>
      </div>
      <div class="col-md-4">
        <div class="contact-card">
          <span class="contact-icon"><i class="bi bi-envelope-fill"></i></span>
          <h5 class="mb-1" style="font-family:var(--ff-display)">Email Us</h5>
          <p class="text-muted small mb-2">We reply within 24 hours</p>
          <a href="mailto:info@HushMan.com" class="fw-600">info@HushMan.com</a>
        </div>
      </div>
      <div class="col-md-4">
        <div class="contact-card">
          <span class="contact-icon"><i class="bi bi-geo-alt-fill"></i></span>
          <h5 class="mb-1" style="font-family:var(--ff-display)">Visit Us</h5>
          <p class="text-muted small mb-2">Our showroom is open daily</p>
          <span class="fw-600">House 12, Road 5, Dhanmondi, Dhaka</span>
        </div>
      </div>
    </div>
  </div>
</section>

<script>const siteUrl = '<?= SITE_URL ?>';</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
