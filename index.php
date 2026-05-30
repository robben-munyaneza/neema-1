<?php
// index.php
require_once 'includes/header.php';

// Fetch all categories for navigation
$cat_res = $conn->query("SELECT * FROM categories LIMIT 5");

// Fetch featured products (limit 4)
$prod_res = $conn->query("SELECT p.*, c.name as category_name, s.name as seller_name, s.seller_location 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          LEFT JOIN users s ON p.seller_id = s.id 
                          ORDER BY p.id DESC LIMIT 4");
?>

<!-- Hero Showcase -->
<section class="hero-section text-white d-flex align-items-center">
    <div class="container">
        <div class="row align-items-center gy-5">
            <div class="col-lg-6 animate-fade-in">
                <span class="badge badge-premium mb-3"><i class="bi bi-fire text-warning me-1"></i> Evolution of Retail</span>
                <h1 class="hero-title mb-3">Elevate Your Digital & Physical Shopping</h1>
                <p class="hero-subtitle">Discover high-end electronics, elite apparel, smart home gadgets, and premium digital resources from verified independent sellers around the globe.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="shop.php" class="btn btn-premium btn-lg"><i class="bi bi-bag-heart"></i> Explore Catalogue</a>
                    <a href="register.php" class="btn btn-premium-secondary btn-lg"><i class="bi bi-shop"></i> Start Selling</a>
                </div>
            </div>
            <div class="col-lg-6 text-center animate-fade-in" style="animation-delay: 0.2s;">
                <div class="position-relative d-inline-block">
                    <!-- Custom Aesthetic Graphic -->
                    <div style="position: absolute; top: 10%; left: 10%; width: 80%; height: 80%; background: var(--primary-gradient); filter: blur(50px); opacity: 0.3; border-radius: 50%; z-index: -1;"></div>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 400" width="100%" height="auto" class="img-fluid" style="max-width: 450px;">
                        <defs>
                            <linearGradient id="orbGrad" cx="50%" cy="50%" r="50%">
                                <stop offset="0%" stop-color="#ec4899" />
                                <stop offset="100%" stop-color="#6366f1" />
                            </linearGradient>
                            <filter id="shadow">
                                <feDropShadow dx="0" dy="10" stdDeviation="10" flood-color="#a855f7" flood-opacity="0.5"/>
                            </filter>
                        </defs>
                        <!-- Grid Background -->
                        <path d="M 50,350 L 450,350 M 100,50 L 100,350 M 200,50 L 200,350 M 300,50 L 300,350 M 400,50 L 400,350" stroke="rgba(255,255,255,0.05)" stroke-width="1" />
                        <!-- Floating Device -->
                        <rect x="120" y="80" width="260" height="180" rx="20" fill="rgba(15,18,36,0.8)" stroke="#a855f7" stroke-width="3" filter="url(#shadow)"/>
                        <!-- Screen Details -->
                        <circle cx="150" cy="110" r="10" fill="#ec4899" />
                        <line x1="170" y1="110" x2="280" y2="110" stroke="#f3f4f6" stroke-width="4" stroke-linecap="round" />
                        <rect x="150" y="140" width="200" height="90" rx="10" fill="rgba(255,255,255,0.05)" stroke="rgba(255,255,255,0.1)" stroke-width="1" />
                        <!-- Cart Symbol -->
                        <path d="M 230 190 A 15 15 0 1 1 260 190 A 15 15 0 1 1 230 190" fill="none" stroke="#06b6d4" stroke-width="4" />
                        <path d="M 215 170 L 230 185 L 260 185 L 275 170" fill="none" stroke="#0ea5e9" stroke-width="3" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Showcase -->
<section class="py-5" style="background: rgba(255,255,255,0.01); border-y: 1px solid var(--border-glass);">
    <div class="container text-center">
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <div class="stats-card">
                    <h3 class="text-white mb-1 font-heading" style="font-size: 2.5rem; font-weight: 800;">10K+</h3>
                    <p class="text-secondary small mb-0">Premium Shipments</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stats-card">
                    <h3 class="text-white mb-1 font-heading" style="font-size: 2.5rem; font-weight: 800;">99.8%</h3>
                    <p class="text-secondary small mb-0">Quality Review Score</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stats-card">
                    <h3 class="text-white mb-1 font-heading" style="font-size: 2.5rem; font-weight: 800;">500+</h3>
                    <p class="text-secondary small mb-0">Verified Sellers</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stats-card">
                    <h3 class="text-white mb-1 font-heading" style="font-size: 2.5rem; font-weight: 800;">Instant</h3>
                    <p class="text-secondary small mb-0">Digital Product Hub</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Dynamic Categories Explorer -->
<section class="py-5 container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="text-gradient-primary fw-bold text-uppercase small" style="letter-spacing: 2px;">Curated Selections</span>
            <h2 class="text-white mt-1">Explore Niches</h2>
        </div>
        <a href="shop.php" class="btn btn-premium-secondary btn-sm">All Categories <i class="bi bi-arrow-right"></i></a>
    </div>
    
    <div class="row g-3">
        <?php if ($cat_res && $cat_res->num_rows > 0): ?>
            <?php while ($cat = $cat_res->fetch_assoc()): ?>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <a href="shop.php?category=<?php echo $cat['id']; ?>" class="text-decoration-none text-white">
                        <div class="card-glass p-4 text-center h-100 d-flex flex-column align-items-center justify-content-center">
                            <div class="rounded-circle bg-dark border border-secondary d-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px; font-size: 1.8rem; color: #a855f7;">
                                <i class="bi <?php echo htmlspecialchars($cat['icon']); ?>"></i>
                            </div>
                            <h5 class="mb-0 font-heading"><?php echo htmlspecialchars($cat['name']); ?></h5>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-secondary">No categories loaded.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Featured Catalogue Grid -->
<section class="py-5 bg-dark-section" style="background: rgba(0, 0, 0, 0.2);">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <span class="text-gradient-primary fw-bold text-uppercase small" style="letter-spacing: 2px;">Featured Marketplace</span>
                <h2 class="text-white mt-1">New Arrivals & Hot Releases</h2>
            </div>
            <a href="shop.php" class="btn btn-premium">See All Catalogues <i class="bi bi-grid"></i></a>
        </div>
        
        <div class="row g-4">
            <?php if ($prod_res && $prod_res->num_rows > 0): ?>
                <?php while ($prod = $prod_res->fetch_assoc()): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="card-glass d-flex flex-column h-100">
                            <!-- Image Container -->
                            <div class="position-relative overflow-hidden">
                                <img src="<?php echo htmlspecialchars($prod['image_url']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" class="card-glass-img w-100">
                                <span class="position-absolute top-3 end-3 badge <?php echo $prod['is_digital'] ? 'bg-info' : 'bg-primary'; ?>" style="top: 15px; right: 15px;">
                                    <?php echo $prod['is_digital'] ? 'Digital' : 'Physical'; ?>
                                </span>
                            </div>
                            <!-- Card Body -->
                            <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge-premium small py-1 px-2"><?php echo htmlspecialchars($prod['category_name'] ?? 'Other'); ?></span>
                                    <span class="star-rating"><i class="bi bi-star-fill"></i> 5.0</span>
                                </div>
                                <h5 class="card-title text-white font-heading mt-2"><?php echo htmlspecialchars($prod['name']); ?></h5>
                                <p class="text-secondary small mb-4 flex-grow-1 text-truncate-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?php echo htmlspecialchars($prod['description']); ?>
                                </p>
                                
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                                    <span class="text-secondary small"><?php echo htmlspecialchars($prod['seller_location'] ?? 'Unknown Location'); ?></span>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mt-auto border-top border-secondary pt-3">
                                    <span class="fs-4 fw-bold text-white"><?php echo format_price($prod['price']); ?></span>
                                    <a href="product-details.php?id=<?php echo $prod['id']; ?>" class="btn btn-premium btn-sm px-3">
                                        Details <i class="bi bi-chevron-right"></i>
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

<!-- Call to Action Banner -->
<section class="container py-5">
    <div class="card-glass p-5 text-center overflow-hidden position-relative" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(236, 72, 153, 0.1) 100%);">
        <div class="position-absolute top-0 start-0 w-100 h-100 bg-grid opacity-10"></div>
        <div class="row justify-content-center position-relative">
            <div class="col-lg-8">
                <h2 class="text-white mb-3">Become a Registered Seller on Name-e-Shopping</h2>
                <p class="text-secondary mb-4">Upload your product catalogs, register legal trading coordinates, declare locations, and securely sell to thousands of clients waiting to download and order products.</p>
                <a href="register.php" class="btn btn-premium btn-lg"><i class="bi bi-person-plus-fill"></i> Setup Seller Account</a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
