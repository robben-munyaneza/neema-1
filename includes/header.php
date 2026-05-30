<?php
// includes/header.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';

$current_user = get_current_user_details();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Name-e-Shopping | Premium E-Commerce Platform</title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Premium Custom Stylesheet -->
    <link href="/neema/assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>
<body>

    <!-- Session Flash Message for JS Toast -->
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div id="session-flash-data" 
             style="display: none;" 
             data-message="<?php echo htmlspecialchars($_SESSION['flash_message']); ?>" 
             data-type="<?php echo htmlspecialchars($_SESSION['flash_type'] ?? 'success'); ?>">
        </div>
        <?php 
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        ?>
    <?php endif; ?>

    <!-- Navigation Header -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-premium sticky-top">
        <div class="container">
            <a class="navbar-brand navbar-brand-premium" href="/neema/index.php">
                <i class="bi bi-grid-3x3-gap-fill me-2"></i>Name-e-Shopping
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link nav-link-premium <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="/neema/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-premium <?php echo basename($_SERVER['PHP_SELF']) == 'shop.php' ? 'active' : ''; ?>" href="/neema/shop.php">Shop Catalogue</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-premium <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>" href="/neema/about.php">Our Story</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-premium <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" href="/neema/contact.php">Contact Us</a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center gap-3">
                    <!-- Shopping Cart Icon -->
                    <a href="/neema/cart.php" class="btn btn-premium-secondary position-relative px-3 py-2" title="View Cart">
                        <i class="bi bi-cart3"></i>
                        <?php 
                        $cnt = cart_count();
                        if ($cnt > 0): 
                        ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.75rem;">
                                <?php echo $cnt; ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- User Actions -->
                    <?php if ($current_user): ?>
                        <div class="dropdown">
                            <button class="btn btn-premium dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($current_user['name']); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark border-secondary bg-dark shadow-lg" aria-labelledby="userMenu">
                                <li>
                                    <h6 class="dropdown-header text-muted">Role: <?php echo ucfirst($current_user['role']); ?></h6>
                                </li>
                                <li><hr class="dropdown-divider border-secondary"></li>
                                <?php if ($current_user['role'] === 'admin'): ?>
                                    <li><a class="dropdown-item" href="/neema/admin/dashboard.php"><i class="bi bi-speedometer2 me-2 text-warning"></i>Admin Panel</a></li>
                                <?php elseif ($current_user['role'] === 'seller'): ?>
                                    <li><a class="dropdown-item" href="/neema/seller/dashboard.php"><i class="bi bi-shop me-2 text-info"></i>Seller Dashboard</a></li>
                                    <li><a class="dropdown-item" href="/neema/seller/orders.php"><i class="bi bi-receipt me-2 text-info"></i>Seller Orders</a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item" href="/neema/customer/dashboard.php"><i class="bi bi-person-bounding-box me-2 text-success"></i>My Account</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider border-secondary"></li>
                                <li><a class="dropdown-item text-danger" href="/neema/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="/neema/login.php" class="btn btn-premium-secondary"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                        <a href="/neema/register.php" class="btn btn-premium"><i class="bi bi-person-plus"></i> Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <main class="flex-grow-1">
