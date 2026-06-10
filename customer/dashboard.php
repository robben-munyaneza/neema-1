<?php
// customer/dashboard.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

restrict_to_roles(['customer'], '../login.php');
$current_user = get_current_user_details();

// ── Invoice download handler ──────────────────────────────
if (isset($_GET['download_invoice'])) {
    $order_id = (int)$_GET['download_invoice'];
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ?");
    $stmt->bind_param("ii", $order_id, $current_user['id']);
    $stmt->execute();
    $order_res = $stmt->get_result();
    if ($order_res->num_rows === 1) {
        $order = $order_res->fetch_assoc();
        $stmt->close();
        $items_stmt = $conn->prepare("SELECT oi.*, p.name as product_name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $items_stmt->bind_param("i", $order_id);
        $items_stmt->execute();
        $items_res = $items_stmt->get_result();
        $inv  = "====================================================\n";
        $inv .= "               NEEMA INVOICE\n";
        $inv .= "====================================================\n";
        $inv .= "Order ID:      #" . $order['id'] . "\n";
        $inv .= "Client:        " . $current_user['name'] . "\n";
        $inv .= "Email:         " . $current_user['email'] . "\n";
        $inv .= "Date:          " . date("F d, Y H:i", strtotime($order['created_at'])) . "\n";
        $inv .= "Payment:       " . $order['payment_method'] . "\n";
        $inv .= "Status:        " . strtoupper($order['status']) . "\n";
        $inv .= "----------------------------------------------------\n";
        while ($item = $items_res->fetch_assoc()) {
            $sub = $item['price'] * $item['quantity'];
            $inv .= sprintf("%-30.30s %10s x%-3d %10s\n", $item['product_name'], number_format($item['price'],0).' FRW', $item['quantity'], number_format($sub,0).' FRW');
        }
        $items_stmt->close();
        $inv .= "----------------------------------------------------\n";
        $inv .= sprintf("%44s: %s\n", "TOTAL", number_format($order['total_amount'],0).' FRW');
        $inv .= "====================================================\n";
        $inv .= "Shipping: " . $order['shipping_address'] . "\n";
        $inv .= "Thank you for shopping with NEEMA!\n";
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="NEEMA_Invoice_' . $order_id . '.txt"');
        echo $inv; exit;
    }
    $stmt->close();
}

// ── Fetch orders ──────────────────────────────────────────
$orders_stmt = $conn->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY id DESC");
$orders_stmt->bind_param("i", $current_user['id']);
$orders_stmt->execute();
$orders_res = $orders_stmt->get_result();
$orders_stmt->close();

$total_orders   = $conn->query("SELECT COUNT(*) as c FROM orders WHERE customer_id = {$current_user['id']}")->fetch_assoc()['c'];
$completed_orders = $conn->query("SELECT COUNT(*) as c FROM orders WHERE customer_id = {$current_user['id']} AND status='completed'")->fetch_assoc()['c'];
$total_spent    = $conn->query("SELECT COALESCE(SUM(total_amount),0) as s FROM orders WHERE customer_id = {$current_user['id']}")->fetch_assoc()['s'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEEMA | My Account</title>
    <link rel="icon" type="image/png" href="/neema/assets/uploads/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/neema/assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>
<body class="dashboard-body">
<div class="dashboard-layout">

    <!-- ══════════════════════════════════
         SIDEBAR
    ══════════════════════════════════ -->
    <aside class="dashboard-sidebar-wrapper">
        <!-- Logo -->
        <div class="sidebar-logo px-4 d-flex align-items-center gap-3">
            <img src="/neema/assets/uploads/logo.png" alt="NEEMA" style="height:30px; border-radius:6px;">
            <span class="fw-black text-white" style="font-size:1.1rem; letter-spacing:2px;">NEEMA</span>
        </div>

        <!-- Nav -->
        <nav class="sidebar-nav">
            <p class="text-secondary px-2 mb-2" style="font-size:0.68rem; font-weight:700; letter-spacing:2px; text-transform:uppercase;">My Account</p>
            <a href="dashboard.php" class="dashboard-nav-link active"><i class="bi bi-grid-1x2"></i> Overview</a>
            <a href="../shop.php" class="dashboard-nav-link"><i class="bi bi-bag"></i> Shop Catalogue</a>
            <a href="../cart.php" class="dashboard-nav-link"><i class="bi bi-cart3"></i> My Cart</a>

            <p class="text-secondary px-2 mt-4 mb-2" style="font-size:0.68rem; font-weight:700; letter-spacing:2px; text-transform:uppercase;">Orders</p>
            <a href="#orders-section" class="dashboard-nav-link"><i class="bi bi-receipt"></i> Order History</a>
            <a href="#" class="dashboard-nav-link"><i class="bi bi-cloud-download"></i> Downloads</a>

            <p class="text-secondary px-2 mt-4 mb-2" style="font-size:0.68rem; font-weight:700; letter-spacing:2px; text-transform:uppercase;">Account</p>
            <a href="#" class="dashboard-nav-link"><i class="bi bi-person"></i> Profile Settings</a>
            <a href="#" class="dashboard-nav-link"><i class="bi bi-geo-alt"></i> Saved Addresses</a>
        </nav>

        <!-- Footer -->
        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-2 mb-3 px-2">
                <span class="d-inline-block rounded-circle bg-success" style="width:8px;height:8px;"></span>
                <span class="text-secondary small">Active Session</span>
            </div>
            <a href="../logout.php" class="dashboard-nav-link text-danger" style="color:#ef4444 !important;">
                <i class="bi bi-box-arrow-right"></i> Sign Out
            </a>
        </div>
    </aside>

    <!-- ══════════════════════════════════
         MAIN CONTENT
    ══════════════════════════════════ -->
    <main class="dashboard-main">

        <!-- Topbar -->
        <header class="dashboard-topbar">
            <div class="d-flex align-items-center gap-3">
                <h5 class="text-white mb-0 fw-bold d-none d-md-block" style="font-family:var(--font-heading);">My Dashboard</h5>
                <div class="position-relative d-none d-md-block">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y text-secondary ms-3" style="font-size:0.85rem;"></i>
                    <input type="text" class="form-control form-glass-input ps-5" placeholder="Search orders..." style="width:260px; border-radius:100px; height:38px; font-size:0.85rem;">
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Cart -->
                <a href="../cart.php" class="navbar-icon-btn" title="Cart">
                    <i class="bi bi-bag"></i>
                    <?php $cnt = cart_count(); if ($cnt > 0): ?>
                        <span class="cart-badge"><?php echo $cnt; ?></span>
                    <?php endif; ?>
                </a>
                <!-- Notifications -->
                <button class="navbar-icon-btn">
                    <i class="bi bi-bell"></i>
                </button>
                <!-- User -->
                <div class="dropdown">
                    <button class="navbar-user-btn dropdown-toggle" data-bs-toggle="dropdown">
                        <span class="user-avatar-sm"><?php echo strtoupper(substr($current_user['name'],0,1)); ?></span>
                        <span class="ms-2 d-none d-md-inline"><?php echo htmlspecialchars(explode(' ', $current_user['name'])[0]); ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark border-secondary bg-dark shadow-lg mt-2">
                        <li><h6 class="dropdown-header text-muted small">Customer Account</h6></li>
                        <li><hr class="dropdown-divider border-secondary my-1"></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="../shop.php"><i class="bi bi-bag me-2"></i>Browse Shop</a></li>
                        <li><hr class="dropdown-divider border-secondary my-1"></li>
                        <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="dashboard-content p-4">

            <!-- Welcome Banner -->
            <div class="customer-welcome-banner mb-5">
                <div>
                    <p class="text-secondary small mb-1">Welcome back,</p>
                    <h2 class="text-white fw-black mb-0" style="font-size:1.8rem; font-family:var(--font-heading);">
                        <?php echo htmlspecialchars($current_user['name']); ?> 👋
                    </h2>
                </div>
                <a href="../shop.php" class="btn-hero-primary" style="padding:0.7rem 1.6rem; font-size:0.88rem;">
                    <i class="bi bi-bag-heart"></i> Continue Shopping
                </a>
            </div>

            <!-- Stats Row -->
            <div class="row g-3 mb-5">
                <div class="col-md-4">
                    <div class="cust-stat-card">
                        <div class="cust-stat-icon"><i class="bi bi-receipt"></i></div>
                        <div>
                            <div class="cust-stat-num"><?php echo $total_orders; ?></div>
                            <div class="cust-stat-label">Total Orders</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="cust-stat-card">
                        <div class="cust-stat-icon"><i class="bi bi-check-circle"></i></div>
                        <div>
                            <div class="cust-stat-num"><?php echo $completed_orders; ?></div>
                            <div class="cust-stat-label">Completed</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="cust-stat-card">
                        <div class="cust-stat-icon"><i class="bi bi-wallet2"></i></div>
                        <div>
                            <div class="cust-stat-num"><?php echo number_format($total_spent, 0); ?></div>
                            <div class="cust-stat-label">Total Spent (FRW)</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order History -->
            <div id="orders-section">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <span class="section-tag">Purchase History</span>
                        <h3 class="section-title" style="font-size:1.4rem;">Order History</h3>
                    </div>
                    <a href="../shop.php" class="btn btn-premium-secondary btn-sm">
                        <i class="bi bi-bag me-1"></i> New Order
                    </a>
                </div>

                <?php if ($orders_res && $orders_res->num_rows > 0): ?>
                    <div class="d-flex flex-column gap-3">
                        <?php while ($order = $orders_res->fetch_assoc()): ?>
                            <div class="order-card">
                                <!-- Order header -->
                                <div class="order-card-header">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="order-icon-wrap">
                                            <i class="bi bi-bag-check"></i>
                                        </div>
                                        <div>
                                            <div class="text-white fw-bold" style="font-family:var(--font-heading);">Order #<?php echo $order['id']; ?></div>
                                            <div class="text-secondary small"><?php echo date("M d, Y · H:i", strtotime($order['created_at'])); ?></div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="order-status-badge order-status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                        <span class="text-white fw-bold" style="font-family:var(--font-heading); font-size:1.05rem;">
                                            <?php echo format_price($order['total_amount']); ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Order items -->
                                <?php
                                $items_stmt = $conn->prepare("SELECT oi.*, p.name as product_name, p.is_digital, p.file_url, p.image_url
                                                              FROM order_items oi
                                                              LEFT JOIN products p ON oi.product_id = p.id
                                                              WHERE oi.order_id = ?");
                                $items_stmt->bind_param("i", $order['id']);
                                $items_stmt->execute();
                                $items_res = $items_stmt->get_result();
                                ?>
                                <div class="order-items-list">
                                    <?php while ($item = $items_res->fetch_assoc()): ?>
                                        <div class="order-item-row">
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="../<?php echo htmlspecialchars($item['image_url'] ?? ''); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                                     class="order-item-thumb">
                                                <div>
                                                    <div class="text-white fw-semibold small"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                                    <div class="text-secondary" style="font-size:0.75rem;">
                                                        <?php echo format_price($item['price']); ?> × <?php echo $item['quantity']; ?>
                                                        <?php if ($item['is_digital']): ?>
                                                            <span class="ms-1 badge" style="background:rgba(6,182,212,0.15); color:#06b6d4; border:1px solid rgba(6,182,212,0.25); font-size:0.6rem; padding:2px 6px;">Digital</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <a href="../product-details.php?id=<?php echo $item['product_id']; ?>#comment" class="btn-order-action">
                                                    <i class="bi bi-chat-square"></i>
                                                </a>
                                                <?php if ($item['is_digital'] && !empty($item['file_url'])): ?>
                                                    <a href="../<?php echo htmlspecialchars($item['file_url']); ?>" class="btn-order-action btn-order-download" download>
                                                        <i class="bi bi-cloud-download"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                    <?php $items_stmt->close(); ?>
                                </div>

                                <!-- Order footer -->
                                <div class="order-card-footer">
                                    <div class="text-secondary small">
                                        <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($order['shipping_address']); ?>
                                    </div>
                                    <a href="dashboard.php?download_invoice=<?php echo $order['id']; ?>" class="btn-invoice">
                                        <i class="bi bi-file-earmark-arrow-down me-1"></i> Invoice
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                <?php else: ?>
                    <div class="empty-state-card">
                        <div class="empty-icon"><i class="bi bi-bag-x"></i></div>
                        <h5 class="text-white fw-bold mb-2">No Orders Yet</h5>
                        <p class="text-secondary small mb-4">You haven't placed any orders. Discover amazing products from verified sellers.</p>
                        <a href="../shop.php" class="btn-hero-primary" style="padding:0.7rem 1.8rem; font-size:0.88rem;">
                            <i class="bi bi-bag-heart"></i> Start Shopping
                        </a>
                    </div>
                <?php endif; ?>
            </div>

        </div><!-- end dashboard-content -->
    </main>
</div><!-- end dashboard-layout -->

<style>
/* ── Customer Dashboard Specific ── */
.customer-welcome-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 16px;
    padding: 1.75rem 2rem;
}

.cust-stat-card {
    display: flex;
    align-items: center;
    gap: 1.1rem;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 14px;
    padding: 1.4rem 1.5rem;
    transition: all 0.25s ease;
}
.cust-stat-card:hover { border-color: rgba(255,255,255,0.14); transform: translateY(-2px); }
.cust-stat-icon {
    width: 48px; height: 48px; flex-shrink: 0;
    border-radius: 12px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.08);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; color: rgba(255,255,255,0.7);
}
.cust-stat-num { font-size: 1.7rem; font-weight: 900; color: #fff; line-height: 1; font-family: var(--font-heading); letter-spacing: -1px; }
.cust-stat-label { font-size: 0.75rem; color: rgba(255,255,255,0.35); font-weight: 500; margin-top: 3px; }

/* Order Cards */
.order-card {
    background: rgba(255,255,255,0.025);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 16px;
    overflow: hidden;
    transition: border-color 0.25s ease;
}
.order-card:hover { border-color: rgba(255,255,255,0.13); }
.order-card-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}
.order-icon-wrap {
    width: 40px; height: 40px; border-radius: 10px;
    background: rgba(255,255,255,0.06);
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; color: rgba(255,255,255,0.6);
}
.order-status-badge {
    font-size: 0.7rem; font-weight: 700;
    letter-spacing: 0.8px; text-transform: uppercase;
    padding: 4px 12px; border-radius: 100px;
}
.order-status-pending   { background: rgba(245,158,11,0.15); color: #f59e0b; border: 1px solid rgba(245,158,11,0.25); }
.order-status-processing{ background: rgba(6,182,212,0.15);  color: #06b6d4; border: 1px solid rgba(6,182,212,0.25); }
.order-status-completed { background: rgba(34,197,94,0.15);  color: #22c55e; border: 1px solid rgba(34,197,94,0.25); }
.order-status-cancelled { background: rgba(239,68,68,0.15);  color: #ef4444; border: 1px solid rgba(239,68,68,0.25); }

.order-items-list { padding: 0 1.5rem; }
.order-item-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.9rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.04);
}
.order-item-row:last-child { border-bottom: none; }
.order-item-thumb {
    width: 44px; height: 44px;
    border-radius: 10px; object-fit: cover;
    background: #111;
    border: 1px solid rgba(255,255,255,0.07);
}
.btn-order-action {
    width: 34px; height: 34px; border-radius: 8px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,0.5); font-size: 0.9rem;
    text-decoration: none; transition: all 0.2s;
}
.btn-order-action:hover { background: rgba(255,255,255,0.1); color: #fff; }
.btn-order-download { color: #22c55e !important; border-color: rgba(34,197,94,0.25) !important; }
.btn-order-download:hover { background: rgba(34,197,94,0.1) !important; }

.order-card-footer {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.9rem 1.5rem;
    background: rgba(255,255,255,0.015);
    border-top: 1px solid rgba(255,255,255,0.05);
}
.btn-invoice {
    font-size: 0.78rem; font-weight: 600;
    color: rgba(255,255,255,0.5);
    text-decoration: none;
    padding: 0.35rem 0.9rem;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.1);
    background: rgba(255,255,255,0.04);
    transition: all 0.2s;
    white-space: nowrap;
}
.btn-invoice:hover { background: rgba(255,255,255,0.09); color: #fff; }

/* Empty state */
.empty-state-card {
    text-align: center;
    padding: 4rem 2rem;
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 20px;
}
.empty-icon {
    width: 72px; height: 72px; border-radius: 20px;
    background: rgba(255,255,255,0.05);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem; color: rgba(255,255,255,0.3);
    margin: 0 auto 1.5rem;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
