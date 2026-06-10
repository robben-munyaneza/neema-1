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
    <title>NEEMA | Rwanda's Premier E-Commerce Platform</title>
    <meta name="description" content="NEEMA — Shop premium food, fashion, electronics, and books from verified sellers. Rwanda's most trusted marketplace.">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/neema/assets/uploads/favicon.png">
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
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

    <!-- Navigation Header — Floating Pill Navbar -->
    <div class="navbar-floating-wrapper">
        <nav class="navbar navbar-expand-lg navbar-dark navbar-pill">
            <div class="container-fluid px-4">
                <a class="navbar-brand navbar-brand-premium d-flex align-items-center gap-2" href="/neema/index.php">
                    <img src="/neema/assets/uploads/logo.png" alt="NEEMA Logo" style="height: 32px; border-radius: 6px;">
                    <span class="fw-black text-white" style="font-size:1.2rem; letter-spacing:2px;">NEEMA</span>
                </a>
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                    <i class="bi bi-list text-white fs-4"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarContent">
                    <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-1">
                        <li class="nav-item">
                            <a class="nav-link nav-pill-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="/neema/index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-pill-link <?php echo basename($_SERVER['PHP_SELF']) == 'shop.php' ? 'active' : ''; ?>" href="/neema/shop.php">Shop</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-pill-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>" href="/neema/about.php">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-pill-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" href="/neema/contact.php">Contact</a>
                        </li>
                    </ul>
                    
                    <div class="d-flex align-items-center gap-2">
                        <!-- Cart -->
                        <a href="/neema/cart.php" class="navbar-icon-btn position-relative" title="Cart">
                            <i class="bi bi-bag"></i>
                            <?php $cnt = cart_count(); if ($cnt > 0): ?>
                                <span class="cart-badge"><?php echo $cnt; ?></span>
                            <?php endif; ?>
                        </a>

                        <!-- User Actions -->
                        <?php if ($current_user): ?>
                            <div class="dropdown">
                                <button class="btn navbar-user-btn dropdown-toggle" data-bs-toggle="dropdown">
                                    <span class="user-avatar-sm"><?php echo strtoupper(substr($current_user['name'], 0, 1)); ?></span>
                                    <span class="d-none d-md-inline ms-2"><?php echo htmlspecialchars(explode(' ', $current_user['name'])[0]); ?></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark border-secondary bg-dark shadow-lg mt-2">
                                    <li><h6 class="dropdown-header text-muted small"><?php echo ucfirst($current_user['role']); ?> Account</h6></li>
                                    <li><hr class="dropdown-divider border-secondary my-1"></li>
                                    <?php if ($current_user['role'] === 'admin'): ?>
                                        <li><a class="dropdown-item" href="/neema/admin/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</a></li>
                                    <?php elseif ($current_user['role'] === 'seller'): ?>
                                        <li><a class="dropdown-item" href="/neema/seller/dashboard.php"><i class="bi bi-shop me-2"></i>Seller Dashboard</a></li>
                                        <li><a class="dropdown-item" href="/neema/seller/orders.php"><i class="bi bi-receipt me-2"></i>My Orders</a></li>
                                    <?php else: ?>
                                        <li><a class="dropdown-item" href="/neema/customer/dashboard.php"><i class="bi bi-person me-2"></i>My Account</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider border-secondary my-1"></li>
                                    <li><a class="dropdown-item text-danger" href="/neema/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <a href="/neema/login.php" class="navbar-icon-btn" title="Login"><i class="bi bi-person"></i></a>
                            <a href="/neema/register.php" class="btn btn-premium btn-sm px-4">Get Started</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </div>
    <main class="flex-grow-1" style="padding-top: 90px;">
