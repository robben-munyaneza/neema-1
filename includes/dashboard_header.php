<?php
// includes/dashboard_header.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';

$current_user = get_current_user_details();
if (!$current_user || !in_array($current_user['role'], ['admin', 'seller'])) {
    header("Location: /neema/login.php");
    exit;
}

$page_title = ucfirst($current_user['role']) . " Dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEEMA | <?php echo $page_title; ?></title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Premium Custom Stylesheet -->
    <link href="/neema/assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>
<body class="dashboard-body">

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

    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="dashboard-sidebar-wrapper">
            <div class="sidebar-logo p-4 border-bottom border-secondary d-flex align-items-center">
                <img src="/neema/assets/uploads/logo.png" alt="NEEMA Logo" style="height: 35px; margin-right: 12px; border-radius: 4px;">
                <h4 class="text-white mb-0 font-heading fw-bold">NEEMA</h4>
            </div>
            
            <div class="sidebar-nav p-3 flex-grow-1 overflow-auto">
                <span class="text-secondary small fw-bold ms-2 mb-2 d-block">MENU</span>
                <?php if ($current_user['role'] === 'admin'): ?>
                    <a href="/neema/admin/dashboard.php" class="dashboard-nav-link active"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
                    <a href="#" class="dashboard-nav-link"><i class="bi bi-people-fill"></i> User Management</a>
                    <a href="#" class="dashboard-nav-link"><i class="bi bi-shop-window"></i> Store Compliance</a>
                    <a href="#" class="dashboard-nav-link"><i class="bi bi-cart-check-fill"></i> Global Transactions</a>
                    <a href="#" class="dashboard-nav-link"><i class="bi bi-bar-chart-fill"></i> Platform Analytics</a>
                    <a href="#" class="dashboard-nav-link"><i class="bi bi-gear-fill"></i> System Settings</a>
                <?php else: ?>
                    <a href="/neema/seller/dashboard.php" class="dashboard-nav-link active"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
                    <a href="#" class="dashboard-nav-link"><i class="bi bi-box-seam"></i> Products</a>
                    <a href="#" class="dashboard-nav-link"><i class="bi bi-people"></i> Clients</a>
                    <a href="#" class="dashboard-nav-link"><i class="bi bi-receipt-cutoff"></i> Order Fulfillment</a>
                    <a href="#" class="dashboard-nav-link"><i class="bi bi-graph-up-arrow"></i> Reports</a>
                    <a href="#" class="dashboard-nav-link"><i class="bi bi-gear-fill"></i> Settings</a>
                <?php endif; ?>
            </div>
            
            <div class="sidebar-footer p-3 border-top border-secondary">
                <div class="d-flex align-items-center gap-2 mb-3 px-2">
                    <span class="d-inline-block rounded-circle bg-success" style="width: 10px; height: 10px; box-shadow: 0 0 8px #198754;"></span>
                    <span class="text-white small">Online</span>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="dashboard-main">
            <!-- Topbar -->
            <header class="dashboard-topbar">
                <div class="d-flex align-items-center">
                    <h5 class="text-white mb-0 me-4 font-heading fw-bold d-none d-md-block"><?php echo $page_title; ?></h5>
                    
                    <div class="search-bar-wrapper position-relative">
                        <i class="bi bi-search position-absolute top-50 translate-middle-y text-secondary ms-3"></i>
                        <input type="text" class="form-control form-glass-input ps-5" placeholder="Search..." style="width: 300px; border-radius: 8px;">
                    </div>
                </div>
                
                <div class="d-flex align-items-center gap-4">
                    <button class="btn btn-link text-white p-0 position-relative">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-dark rounded-circle">
                            <span class="visually-hidden">New alerts</span>
                        </span>
                    </button>
                    
                    <div class="dropdown">
                        <div class="d-flex align-items-center gap-2 cursor-pointer dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-weight: bold;">
                                <?php echo strtoupper(substr($current_user['name'], 0, 1)); ?>
                            </div>
                            <div class="d-none d-md-flex flex-column lh-1">
                                <span class="text-white small fw-bold"><?php echo htmlspecialchars($current_user['name']); ?></span>
                                <span class="text-secondary" style="font-size: 0.75rem;">NEEMA <?php echo strtoupper($current_user['role']); ?></span>
                            </div>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark border-secondary bg-dark shadow-lg">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/neema/index.php"><i class="bi bi-house me-2"></i>View Website</a></li>
                            <li><hr class="dropdown-divider border-secondary"></li>
                            <li><a class="dropdown-item text-danger" href="/neema/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
                        </ul>
                    </div>
                </div>
            </header>
            
            <!-- Dashboard Content Container -->
            <div class="dashboard-content p-4">
