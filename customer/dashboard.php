<?php
// customer/dashboard.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Restrict access to Customer role
restrict_to_roles(['customer'], '../login.php');

$current_user = get_current_user_details();

// ==========================================
// DYNAMIC INVOICE DOWNLOAD HANDLER (.txt file)
// ==========================================
if (isset($_GET['download_invoice'])) {
    $order_id = (int)$_GET['download_invoice'];
    
    // Fetch order
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ?");
    $stmt->bind_param("ii", $order_id, $current_user['id']);
    $stmt->execute();
    $order_res = $stmt->get_result();
    
    if ($order_res->num_rows === 1) {
        $order = $order_res->fetch_assoc();
        $stmt->close();
        
        // Fetch order items
        $items_stmt = $conn->prepare("SELECT oi.*, p.name as product_name, p.is_digital 
                                      FROM order_items oi 
                                      LEFT JOIN products p ON oi.product_id = p.id 
                                      WHERE oi.order_id = ?");
        $items_stmt->bind_param("i", $order_id);
        $items_stmt->execute();
        $items_res = $items_stmt->get_result();
        
        // Generate Plain Text Invoice
        $invoice_text = "====================================================\n";
        $invoice_text .= "               NAME-E-SHOPPING INVOICE\n";
        $invoice_text .= "====================================================\n";
        $invoice_text .= "Order ID:      #" . $order['id'] . "\n";
        $invoice_text .= "Client Name:   " . $current_user['name'] . "\n";
        $invoice_text .= "Client Email:  " . $current_user['email'] . "\n";
        $invoice_text .= "Order Date:    " . date("F d, Y H:i:s", strtotime($order['created_at'])) . "\n";
        $invoice_text .= "Payment:       " . $order['payment_method'] . "\n";
        $invoice_text .= "Status:        " . strtoupper($order['status']) . "\n";
        $invoice_text .= "----------------------------------------------------\n";
        $invoice_text .= sprintf("%-30s %-8s %-5s %-10s\n", "Item", "Price", "Qty", "Subtotal");
        $invoice_text .= "----------------------------------------------------\n";
        
        while ($item = $items_res->fetch_assoc()) {
            $subtotal = $item['price'] * $item['quantity'];
            $invoice_text .= sprintf(
                "%-30.30s $%-7.2f %-5d $%-9.2f\n", 
                $item['product_name'], 
                $item['price'], 
                $item['quantity'], 
                $subtotal
            );
        }
        $items_stmt->close();
        
        $invoice_text .= "----------------------------------------------------\n";
        $invoice_text .= sprintf("%45s: $%-9.2f\n", "TOTAL DUE", $order['total_amount']);
        $invoice_text .= "====================================================\n";
        $invoice_text .= "Delivery Target Address:\n" . $order['shipping_address'] . "\n";
        $invoice_text .= "====================================================\n";
        $invoice_text .= "Thank you for shopping with Name-e-Shopping!\n";
        $invoice_text .= "For compliance support, reach contact@name-e-shopping.com\n";
        
        // Send file headers
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="invoice_order_' . $order_id . '.txt"');
        header('Content-Length: ' . strlen($invoice_text));
        echo $invoice_text;
        exit;
    }
    $stmt->close();
}

// Fetch Customer's Orders
$orders_query = "SELECT * FROM orders WHERE customer_id = ? ORDER BY id DESC";
$orders_stmt = $conn->prepare($orders_query);
$orders_stmt->bind_param("i", $current_user['id']);
$orders_stmt->execute();
$orders_res = $orders_stmt->get_result();
$orders_stmt->close();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5 mt-4">
    <div class="row gy-4">
        <!-- Profile Column -->
        <div class="col-lg-4">
            <div class="card-glass p-4 sticky-top" style="top: 100px; z-index: 10;">
                <div class="text-center mb-4 border-bottom border-secondary pb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-dark rounded-circle border border-secondary mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-person-bounding-box text-gradient-primary fs-1"></i>
                    </div>
                    <h4 class="text-white font-heading mb-1"><?php echo htmlspecialchars($current_user['name']); ?></h4>
                    <span class="badge bg-success py-1 px-3">Customer Account</span>
                </div>
                
                <div class="d-flex flex-column gap-3 small">
                    <div>
                        <span class="text-secondary d-block">E-mail Address</span>
                        <strong class="text-white"><?php echo htmlspecialchars($current_user['email']); ?></strong>
                    </div>
                    <div>
                        <span class="text-secondary d-block">Account Status</span>
                        <strong class="text-success"><i class="bi bi-patch-check-fill me-1"></i> Active & Verified</strong>
                    </div>
                    <div>
                        <span class="text-secondary d-block">Registered Since</span>
                        <strong class="text-white">May 2026</strong>
                    </div>
                </div>
                
                <div class="mt-4 pt-4 border-top border-secondary text-center">
                    <a href="../logout.php" class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-box-arrow-right me-1"></i> Sign Out</a>
                </div>
            </div>
        </div>
        
        <!-- Order History and Download Center -->
        <div class="col-lg-8">
            <h2 class="text-white mb-4"><i class="bi bi-grid-fill text-gradient-primary me-2"></i>My Dashboard</h2>
            
            <h4 class="text-white mb-3 font-heading"><i class="bi bi-receipt me-2 text-warning"></i>Order Checklist & Receipt Center</h4>
            
            <?php if ($orders_res && $orders_res->num_rows > 0): ?>
                <div class="d-flex flex-column gap-4">
                    <?php while ($order = $orders_res->fetch_assoc()): ?>
                        <div class="card-glass p-4">
                            <!-- Order Summary Row -->
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom border-secondary">
                                <div>
                                    <h6 class="text-white font-heading mb-1">Order #<?php echo $order['id']; ?></h6>
                                    <span class="text-secondary small">Placed: <?php echo date("M d, Y H:i", strtotime($order['created_at'])); ?></span>
                                </div>
                                <div class="text-end">
                                    <span class="badge <?php 
                                        if ($order['status'] === 'pending') echo 'bg-warning text-dark';
                                        elseif ($order['status'] === 'processing') echo 'bg-info';
                                        elseif ($order['status'] === 'completed') echo 'bg-success';
                                        else echo 'bg-danger';
                                    ?> py-1 px-3 mb-1 d-inline-block">
                                        <?php echo strtoupper($order['status']); ?>
                                    </span>
                                    <div class="text-gradient-primary fw-bold fs-5 font-heading"><?php echo format_price($order['total_amount']); ?></div>
                                </div>
                            </div>
                            
                            <!-- Items list in this Order -->
                            <?php
                            $items_stmt = $conn->prepare("SELECT oi.*, p.name as product_name, p.is_digital, p.file_url 
                                                          FROM order_items oi 
                                                          LEFT JOIN products p ON oi.product_id = p.id 
                                                          WHERE oi.order_id = ?");
                            $items_stmt->bind_param("i", $order['id']);
                            $items_stmt->execute();
                            $items_res = $items_stmt->get_result();
                            ?>
                            
                            <div class="table-responsive">
                                <table class="table table-dark table-premium mb-0 small">
                                    <thead>
                                        <tr>
                                            <th>Product Title</th>
                                            <th class="text-center">Price</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($item = $items_res->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                                    <?php if ($item['is_digital']): ?>
                                                        <span class="badge bg-info ms-1" style="font-size:0.65rem;">Digital</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center"><?php echo format_price($item['price']); ?></td>
                                                <td class="text-center"><?php echo $item['quantity']; ?></td>
                                                <td class="text-end">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <!-- Leave comment quality shortcut -->
                                                        <a href="../product-details.php?id=<?php echo $item['product_id']; ?>#comment" class="btn btn-sm btn-premium-secondary py-1 px-2" title="Comment on Quality">
                                                            <i class="bi bi-chat-square-text-fill"></i> Comment Quality
                                                        </a>
                                                        
                                                        <!-- If product is digital, provide dynamic download link -->
                                                        <?php if ($item['is_digital'] && !empty($item['file_url'])): ?>
                                                            <a href="../<?php echo htmlspecialchars($item['file_url']); ?>" class="btn btn-sm btn-premium py-1 px-2" download title="Download Product Files">
                                                                <i class="bi bi-cloud-download-fill"></i> Download
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php $items_stmt->close(); ?>
                            
                            <!-- Receipt downloader form link -->
                            <div class="mt-3 text-end">
                                <a href="dashboard.php?download_invoice=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-file-earmark-arrow-down"></i> Export Text Invoice</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="card-glass p-5 text-center">
                    <i class="bi bi-folder2-open text-secondary" style="font-size: 3rem;"></i>
                    <h5 class="text-white mt-3">No Orders Logged</h5>
                    <p class="text-secondary mb-3">You haven't ordered any physical or digital assets yet.</p>
                    <a href="../shop.php" class="btn btn-premium btn-sm">Start Shopping Catalog</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
