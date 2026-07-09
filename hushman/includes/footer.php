<!-- ============================================================
Footer
============================================================ -->
<footer class="site-footer mt-5">
    <div class="footer-top">
        <div class="container">
            <div class="row g-4">
                <!-- Brand -->
                <div class="col-lg-3 col-md-6">
                    <div class="footer-brand">HUSH<span>MAN</span></div>
                    <p class="footer-tagline">Elevating everyday men's fashion with premium quality and timeless style.</p>
                    <div class="social-links">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-twitter-x"></i></a>
                        <a href="#"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                <!-- Shop -->
                <div class="col-lg-2 col-md-3 col-6">
                    <h6 class="footer-heading">Shop</h6>
                    <ul class="footer-links">
                        <?php foreach(getCategories() as $cat): ?>
                        <li><a href="<?= SITE_URL ?>/pages/shop.php?category=<?= $cat['slug'] ?>"><?= sanitize($cat['name']) ?></a></li>
                        <?php endforeach; ?>
                        <li><a href="<?= SITE_URL ?>/pages/shop.php?sale=1">Sale Items</a></li>
                    </ul>
                </div>
                <!-- Account -->
                <div class="col-lg-2 col-md-3 col-6">
                    <h6 class="footer-heading">Account</h6>
                    <ul class="footer-links">
                        <li><a href="<?= SITE_URL ?>/pages/profile.php">My Profile</a></li>
                        <li><a href="<?= SITE_URL ?>/pages/orders.php">My Orders</a></li>
                        <li><a href="<?= SITE_URL ?>/pages/cart.php">My Cart</a></li>
                        <li><a href="<?= SITE_URL ?>/pages/login.php">Login</a></li>
                        <li><a href="<?= SITE_URL ?>/pages/register.php">Register</a></li>
                    </ul>
                </div>
                <!-- Contact -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="footer-heading">Contact Us</h6>
                    <ul class="footer-contact">
                        <li><i class="bi bi-geo-alt-fill"></i> House 12, Road 5, Dhanmondi, Dhaka-1205</li>
                        <li><i class="bi bi-telephone-fill"></i> <a href="tel:+8801711234567">+880 171 123 4567</a></li>
                        <li><i class="bi bi-envelope-fill"></i> <a href="mailto:info@HushMan.com">info@HushMan.com</a></li>
                        <li><i class="bi bi-clock-fill"></i> Sat–Thu: 10AM – 8PM</li>
                    </ul>
                </div>
                <!-- Newsletter -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="footer-heading">Newsletter</h6>
                    <p class="footer-tagline small">Get exclusive deals & style tips.</p>
                    <form class="newsletter-form" onsubmit="return false;">
                        <input type="email" placeholder="Your email" class="form-control form-control-sm mb-2">
                        <button class="btn btn-primary btn-sm w-100">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <span>&copy; <?= date('Y') ?> HushMan. All rights reserved.</span>
                </div>
                <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                    <img src="<?= SITE_URL ?>/assets/images/payment-methods.png" alt="Payment Methods" height="24" onerror="this.style.display='none'">
                    <span class="text-muted small ms-2">Visa · MasterCard · bKash · Nagad · COD</span>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top -->
<button class="back-to-top" id="backToTop"><i class="bi bi-arrow-up-short"></i></button>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Main JS -->
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
<!-- Mobile UX JS -->
<script src="<?= SITE_URL ?>/assets/js/mobile.js"></script>
</body>
</html>
