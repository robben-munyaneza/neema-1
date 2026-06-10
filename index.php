<?php
// index.php
require_once 'includes/header.php';

// Fetch all categories
$cat_res = $conn->query("SELECT * FROM categories LIMIT 6");

// Fetch featured products
$prod_res = $conn->query("SELECT p.*, c.name as category_name, s.name as seller_name, s.seller_location 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          LEFT JOIN users s ON p.seller_id = s.id 
                          ORDER BY p.id DESC LIMIT 4");
?>

<!-- ═══════════════════════════════════════════════
     HERO SECTION
═══════════════════════════════════════════════ -->
<section class="hero-section-v2">
    <div class="hero-bg-image" style="background-image: url('/neema/assets/uploads/hero_banner.png');"></div>
    <div class="hero-overlay"></div>
    <div class="container position-relative z-2 py-5">
        <div class="row align-items-center min-vh-80 py-5">
            <div class="col-lg-7 animate-fade-in">
                <div class="hero-tag">
                    <span class="hero-tag-dot"></span>
                    Rwanda's #1 Marketplace
                </div>
                <h1 class="hero-title-v2">
                    Shop Everything<br>
                    <span class="muted">You Need.</span>
                </h1>
                <p class="hero-desc">From fresh Rwandan coffee to cutting-edge electronics — discover curated products from Rwanda's most trusted verified sellers.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="shop.php" class="btn-hero-primary">
                        <i class="bi bi-bag-heart"></i>Shop Now
                    </a>
                    <a href="register.php" class="btn-hero-secondary">
                        <i class="bi bi-shop"></i>Start Selling
                    </a>
                </div>
                <div class="hero-stats">
                    <div><span class="hero-stat-num">10K+</span><span class="hero-stat-label">Customers</span></div>
                    <div><span class="hero-stat-num">500+</span><span class="hero-stat-label">Verified Sellers</span></div>
                    <div><span class="hero-stat-num">99.8%</span><span class="hero-stat-label">Satisfaction</span></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Scroll Indicator -->
    <div class="hero-scroll-hint">
        <i class="bi bi-chevron-down"></i>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     TRUST STRIP
═══════════════════════════════════════════════ -->
    <div class="trust-strip py-3">
    <div class="container">
        <div class="row g-0 align-items-center">
            <div class="col-6 col-md-3"><div class="trust-item"><i class="bi bi-shield-check trust-icon"></i><span class="trust-label">Verified Sellers</span></div></div>
            <div class="col-6 col-md-3"><div class="trust-item"><i class="bi bi-truck trust-icon"></i><span class="trust-label">Fast Delivery</span></div></div>
            <div class="col-6 col-md-3"><div class="trust-item"><i class="bi bi-arrow-return-left trust-icon"></i><span class="trust-label">Easy Returns</span></div></div>
            <div class="col-6 col-md-3"><div class="trust-item"><i class="bi bi-lock trust-icon"></i><span class="trust-label">Secure Payments</span></div></div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════
     CATEGORY BROWSER
═══════════════════════════════════════════════ -->
<section class="py-5 container">
    <div class="section-header mb-5">
        <span class="section-tag">Browse by Category</span>
        <h2 class="section-title">What are you looking for?</h2>
    </div>

    <div class="row g-3">
        <?php if ($cat_res && $cat_res->num_rows > 0): ?>
            <?php while ($cat = $cat_res->fetch_assoc()): ?>
                <div class="col-lg-3 col-md-6 col-6">
                    <a href="shop.php?category=<?php echo $cat['id']; ?>" class="category-card text-decoration-none d-block">
                        <div class="category-icon-wrap">
                            <i class="bi <?php echo htmlspecialchars($cat['icon']); ?>"></i>
                        </div>
                        <span class="category-name"><?php echo htmlspecialchars($cat['name']); ?></span>
                        <i class="bi bi-arrow-right category-arrow"></i>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     FEATURED PRODUCTS
═══════════════════════════════════════════════ -->
<section class="py-5" style="background: rgba(255,255,255,0.02); border-top: 1px solid rgba(255,255,255,0.05);">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div class="section-header mb-0">
                <span class="section-tag">Featured</span>
                <h2 class="section-title mb-0">New Arrivals</h2>
            </div>
            <a href="shop.php" class="btn btn-premium-secondary btn-sm">View All <i class="bi bi-arrow-right ms-1"></i></a>
        </div>

        <div class="row g-4">
            <?php if ($prod_res && $prod_res->num_rows > 0): ?>
                <?php while ($prod = $prod_res->fetch_assoc()): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="product-card">
                            <div class="product-card-image">
                                <img src="<?php echo htmlspecialchars($prod['image_url']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>">
                                <span class="product-type-badge"><?php echo $prod['is_digital'] ? 'Digital' : 'Physical'; ?></span>
                                <a href="product-details.php?id=<?php echo $prod['id']; ?>" class="product-overlay-btn">
                                    <i class="bi bi-eye me-1"></i> Quick View
                                </a>
                            </div>
                            <div class="product-card-body">
                                <span class="product-category"><?php echo htmlspecialchars($prod['category_name'] ?? 'Other'); ?></span>
                                <h5 class="product-name"><?php echo htmlspecialchars($prod['name']); ?></h5>
                                <p class="product-desc"><?php echo htmlspecialchars($prod['description']); ?></p>
                                <div class="product-footer">
                                    <span class="product-price"><?php echo format_price($prod['price']); ?></span>
                                    <a href="product-details.php?id=<?php echo $prod['id']; ?>" class="btn btn-premium btn-sm">
                                        Buy <i class="bi bi-cart-plus ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-secondary">No products currently available.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     HOW IT WORKS
═══════════════════════════════════════════════ -->
<section class="py-5 container">
    <div class="section-header mb-5 text-center">
        <span class="section-tag">Simple Process</span>
        <h2 class="section-title">How NEEMA Works</h2>
    </div>
    <div class="row g-4">
        <div class="col-md-4 text-center">
            <div class="how-step">
                <div class="how-step-number">01</div>
                <div class="how-step-icon"><i class="bi bi-search"></i></div>
                <h5 class="text-white fw-bold mt-3 mb-2">Discover Products</h5>
                <p class="text-secondary small">Browse hundreds of curated products across food, fashion, electronics, and books from verified local sellers.</p>
            </div>
        </div>
        <div class="col-md-4 text-center">
            <div class="how-step">
                <div class="how-step-number">02</div>
                <div class="how-step-icon"><i class="bi bi-cart-check"></i></div>
                <h5 class="text-white fw-bold mt-3 mb-2">Add to Cart & Pay</h5>
                <p class="text-secondary small">Securely add items to your cart and complete checkout with our fast and safe payment system in FRW.</p>
            </div>
        </div>
        <div class="col-md-4 text-center">
            <div class="how-step">
                <div class="how-step-number">03</div>
                <div class="how-step-icon"><i class="bi bi-box-seam"></i></div>
                <h5 class="text-white fw-bold mt-3 mb-2">Receive Your Order</h5>
                <p class="text-secondary small">Get your products delivered to your doorstep or download digital products instantly after confirmation.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     STATS SECTION
═══════════════════════════════════════════════ -->
<section class="stats-section-v2 py-5">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-6 col-md-3">
                <div class="stat-card-v2">
                    <h3 class="stat-number">10K+</h3>
                    <p class="stat-label">Happy Customers</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card-v2">
                    <h3 class="stat-number">500+</h3>
                    <p class="stat-label">Verified Sellers</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card-v2">
                    <h3 class="stat-number">99.8%</h3>
                    <p class="stat-label">Satisfaction Rate</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card-v2">
                    <h3 class="stat-number">24/7</h3>
                    <p class="stat-label">Customer Support</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     SELLER CTA
═══════════════════════════════════════════════ -->
<section class="py-5 container">
    <div class="seller-cta-card">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <span class="section-tag mb-3 d-block">For Entrepreneurs</span>
                <h2 class="text-white fw-black mb-3" style="font-size: 2rem;">Start Selling on NEEMA Today</h2>
                <p class="text-secondary mb-0">Join hundreds of verified sellers already earning on Rwanda's most trusted e-commerce platform. List your products in minutes.</p>
            </div>
            <div class="col-lg-5 text-lg-end mt-4 mt-lg-0">
                <a href="register.php" class="btn btn-premium btn-lg me-3"><i class="bi bi-person-plus me-2"></i>Open Seller Account</a>
                <a href="about.php" class="btn btn-hero-secondary">Learn More</a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
