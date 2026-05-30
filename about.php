<?php
// about.php
require_once 'includes/header.php';
?>

<div class="container py-5 mt-4">
    <!-- Showcase header -->
    <div class="text-center mb-5 animate-fade-in">
        <span class="text-gradient-primary fw-bold text-uppercase small" style="letter-spacing: 2px;">Our Legacy</span>
        <h1 class="text-white font-heading mt-2" style="font-size: 3rem;">Redefining E-Commerce Interactivity</h1>
        <p class="text-secondary col-lg-7 mx-auto mt-3">We believe in a tailored retail ecosystems where sellers trade coordinates with integrity, administrators supervise compliance, and clients leave verified reviews of quality.</p>
    </div>
    
    <!-- Hero Splitting row -->
    <div class="row align-items-center gy-5 mb-5 pt-3">
        <div class="col-lg-6">
            <div class="card-glass p-5">
                <h3 class="text-white mb-3 font-heading text-gradient-primary">A Three-Tier System Built for Trust</h3>
                <p class="text-secondary">Name-e-Shopping was designed to bridge trust gaps between consumers and digital merchants. By enforcing seller document verification and setting exact geolocation tags, we guarantee authentic products.</p>
                <div class="d-flex flex-column gap-3 mt-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="bg-dark rounded p-2 text-warning border border-secondary"><i class="bi bi-shield-fill-check"></i></div>
                        <div>
                            <h6 class="text-white mb-1">Admin Supervision</h6>
                            <p class="text-secondary small">Administrators review legal compliance credentials and ensure that sellers operate inside standard parameters.</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <div class="bg-dark rounded p-2 text-info border border-secondary"><i class="bi bi-shop"></i></div>
                        <div>
                            <h6 class="text-white mb-1">Seller Sovereignty</h6>
                            <p class="text-secondary small">Registered sellers upload gorgeous, responsive product portfolios, set locations, and manage instant digital inventories.</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <div class="bg-dark rounded p-2 text-success border border-secondary"><i class="bi bi-chat-square-heart-fill"></i></div>
                        <div>
                            <h6 class="text-white mb-1">Customer Transparency</h6>
                            <p class="text-secondary small">Customers download instant invoices, comment on product quality grades, and gain immediate digital download access.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 text-center">
            <div class="position-relative d-inline-block">
                <div style="position: absolute; top: 15%; left: 15%; width: 70%; height: 70%; background: var(--primary-gradient); filter: blur(60px); opacity: 0.25; border-radius: 50%; z-index: -1;"></div>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 350" width="100%" height="auto" style="max-width: 380px;">
                    <defs>
                        <linearGradient id="g1" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="#8b5cf6" />
                            <stop offset="100%" stop-color="#ec4899" />
                        </linearGradient>
                    </defs>
                    <!-- Visual geometric representation -->
                    <circle cx="200" cy="170" r="100" fill="none" stroke="url(#g1)" stroke-width="4" />
                    <circle cx="200" cy="170" r="80" fill="none" stroke="#0ea5e9" stroke-width="2" stroke-dasharray="10, 10" />
                    <!-- Visual Nodes -->
                    <circle cx="200" cy="70" r="20" fill="#a855f7" />
                    <text x="200" y="75" fill="#fff" font-family="sans-serif" font-weight="bold" font-size="12" text-anchor="middle">A</text>
                    
                    <circle cx="110" cy="220" r="20" fill="#0ea5e9" />
                    <text x="110" y="225" fill="#fff" font-family="sans-serif" font-weight="bold" font-size="12" text-anchor="middle">S</text>
                    
                    <circle cx="290" cy="220" r="20" fill="#10b981" />
                    <text x="290" y="225" fill="#fff" font-family="sans-serif" font-weight="bold" font-size="12" text-anchor="middle">C</text>
                </g>
                </svg>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
