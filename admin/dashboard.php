<?php
// admin/dashboard.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

restrict_to_roles(['admin'], '../login.php');

$current_user = get_current_user_details();

// Handle Compliance Approvals/Rejections (if POST action)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'compliance_decide') {
    $seller_id = (int)$_POST['seller_id'];
    $decision = $_POST['decision']; // 'approved' or 'rejected'
    
    if (in_array($decision, ['approved', 'rejected'])) {
        $stmt_up = $conn->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'seller'");
        $stmt_up->bind_param("si", $decision, $seller_id);
        if ($stmt_up->execute()) {
            $_SESSION['flash_message'] = "Seller account #$seller_id has been $decision!";
            $_SESSION['flash_type'] = $decision === 'approved' ? 'success' : 'warning';
        }
        $stmt_up->close();
    }
    header("Location: dashboard.php");
    exit;
}

// Stats Calculations
$total_customers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")->fetch_assoc()['count'];
$total_sellers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'seller'")->fetch_assoc()['count'];
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];

// Fetch all Sellers for approval desk
$sellers_res = $conn->query("SELECT * FROM users WHERE role = 'seller' ORDER BY id DESC");

// Fetch products listed
$products_res = $conn->query("SELECT p.*, s.name as seller_name 
                              FROM products p 
                              LEFT JOIN users s ON p.seller_id = s.id 
                              ORDER BY p.id DESC LIMIT 10");

require_once __DIR__ . '/../includes/dashboard_header.php';
?>

        <!-- Main Content -->
        <div class="col-12">
            <h2 class="text-white mb-4"><i class="bi bi-speedometer2 text-white me-2"></i>Admin Control Center</h2>
            
            <!-- Row of Stat Widgets -->
    <div class="row g-4 mb-5">
        <div class="col-lg-3 col-6">
            <div class="stats-card text-center">
                <i class="bi bi-person-lines-fill text-success fs-3 mb-2 d-inline-block"></i>
                <h3 class="text-white font-heading" style="font-size: 2rem; font-weight:800;"><?php echo $total_customers; ?></h3>
                <p class="text-secondary small mb-0">Total Clients</p>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="stats-card text-center">
                <i class="bi bi-shop text-info fs-3 mb-2 d-inline-block"></i>
                <h3 class="text-white font-heading" style="font-size: 2rem; font-weight:800;"><?php echo $total_sellers; ?></h3>
                <p class="text-secondary small mb-0">Registered Sellers</p>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="stats-card text-center">
                <i class="bi bi-box-seam text-warning fs-3 mb-2 d-inline-block"></i>
                <h3 class="text-white font-heading" style="font-size: 2rem; font-weight:800;"><?php echo $total_products; ?></h3>
                <p class="text-secondary small mb-0">Products Published</p>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="stats-card text-center">
                <i class="bi bi-cart-check-fill text-danger fs-3 mb-2 d-inline-block"></i>
                <h3 class="text-white font-heading" style="font-size: 2rem; font-weight:800;"><?php echo $total_orders; ?></h3>
                <p class="text-secondary small mb-0">Transactions Logged</p>
            </div>
        </div>
    </div>

    <div class="row gy-5">
        <!-- Sellers Compliance Review Desk -->
        <div class="col-xl-12">
            <div class="card-glass p-4 h-100">
                <h4 class="text-white mb-4 font-heading"><i class="bi bi-patch-check-fill text-info me-2"></i>Sellers Compliance Desk</h4>
                
                <?php if ($sellers_res && $sellers_res->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-premium mb-0 small">
                            <thead>
                                <tr>
                                    <th>Seller Identity</th>
                                    <th>Coordinate Location</th>
                                    <th>Compliance Docs</th>
                                    <th>Status State</th>
                                    <th class="text-end">Verification Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($sel = $sellers_res->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-white"><?php echo htmlspecialchars($sel['name']); ?></strong><br>
                                            <span class="text-secondary small"><?php echo htmlspecialchars($sel['email']); ?></span>
                                        </td>
                                        <td><i class="bi bi-geo-alt text-danger me-1"></i> <?php echo htmlspecialchars($sel['seller_location'] ?? 'Not set'); ?></td>
                                        <td>
                                            <?php if ($sel['seller_documents']): ?>
                                                <a href="../<?php echo htmlspecialchars($sel['seller_documents']); ?>" class="text-info text-decoration-none fw-bold" target="_blank">
                                                    <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> View Submitted Compliance Doc
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">No uploads</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php 
                                                if ($sel['status'] === 'pending') echo 'bg-warning text-dark';
                                                elseif ($sel['status'] === 'approved') echo 'bg-success';
                                                else echo 'bg-danger';
                                            ?>">
                                                <?php echo strtoupper($sel['status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <?php if ($sel['status'] === 'pending' || $sel['status'] === 'rejected'): ?>
                                                <form action="dashboard.php" method="POST" class="d-inline-block">
                                                    <input type="hidden" name="action" value="compliance_decide">
                                                    <input type="hidden" name="seller_id" value="<?php echo $sel['id']; ?>">
                                                    <input type="hidden" name="decision" value="approved">
                                                    <button type="submit" class="btn btn-sm btn-success py-1 px-2 me-1" title="Approve Compliance"><i class="bi bi-check-circle-fill"></i> Approve</button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if ($sel['status'] === 'pending' || $sel['status'] === 'approved'): ?>
                                                <form action="dashboard.php" method="POST" class="d-inline-block">
                                                    <input type="hidden" name="action" value="compliance_decide">
                                                    <input type="hidden" name="seller_id" value="<?php echo $sel['id']; ?>">
                                                    <input type="hidden" name="decision" value="rejected">
                                                    <button type="submit" class="btn btn-sm btn-danger py-1 px-2" title="Reject Seller"><i class="bi bi-x-circle-fill"></i> Reject</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-secondary mb-0">No registered sellers logged in system database.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Latest Published listings sidebar -->
        <div class="col-xl-12 mt-4">
            <div class="card-glass p-4 h-100">
                <h4 class="text-white mb-4 font-heading"><i class="bi bi-box-seam text-warning me-2"></i>Global Marketplace listings</h4>
                
                <?php if ($products_res && $products_res->num_rows > 0): ?>
                    <div class="d-flex flex-column gap-3">
                        <?php while ($prod = $products_res->fetch_assoc()): ?>
                            <div class="d-flex align-items-center justify-content-between bg-dark p-2 rounded border border-secondary" style="background: rgba(0,0,0,0.15) !important;">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="../<?php echo htmlspecialchars($prod['image_url']); ?>" class="rounded" style="width: 35px; height: 35px; object-fit: cover;">
                                    <div>
                                        <h6 class="text-white small mb-0 font-heading text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($prod['name']); ?></h6>
                                        <span class="text-secondary small" style="font-size: 0.75rem;">Seller: <?php echo htmlspecialchars($prod['seller_name']); ?></span>
                                    </div>
                                </div>
                                <span class="badge bg-primary fs-6"><?php echo format_price($prod['price']); ?></span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-secondary mb-0">No products uploaded to catalog.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
        </div>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
