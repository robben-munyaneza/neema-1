<?php
// seller/orders.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

restrict_to_roles(['seller'], '../login.php');

$current_user = get_current_user_details();

// Handle Order Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    
    $allowed_statuses = ['pending', 'processing', 'completed', 'cancelled'];
    if (in_array($new_status, $allowed_statuses)) {
        // Enforce that the order actually contains items belonging to this seller before updating
        $stmt_check = $conn->prepare("SELECT COUNT(*) as count 
                                      FROM order_items oi 
                                      JOIN products p ON oi.product_id = p.id 
                                      WHERE oi.order_id = ? AND p.seller_id = ?");
        $stmt_check->bind_param("ii", $order_id, $current_user['id']);
        $stmt_check->execute();
        $check_res = $stmt_check->get_result()->fetch_assoc();
        $stmt_check->close();
        
        if ($check_res['count'] > 0) {
            $stmt_up = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt_up->bind_param("si", $new_status, $order_id);
            if ($stmt_up->execute()) {
                $_SESSION['flash_message'] = "Order #$order_id status updated to $new_status!";
                $_SESSION['flash_type'] = "success";
            }
            $stmt_up->close();
        }
    }
    header("Location: orders.php");
    exit;
}

// Fetch all order items corresponding to this seller
$stmt_orders = $conn->prepare("SELECT o.id as order_id, o.created_at, o.status as order_status, o.shipping_address, o.payment_method,
                                      u.name as customer_name, u.email as customer_email,
                                      oi.price, oi.quantity, p.name as product_name, p.is_digital
                               FROM order_items oi
                               JOIN orders o ON oi.order_id = o.id
                               JOIN products p ON oi.product_id = p.id
                               JOIN users u ON o.customer_id = u.id
                               WHERE p.seller_id = ?
                               ORDER BY o.id DESC");
$stmt_orders->bind_param("i", $current_user['id']);
$stmt_orders->execute();
$res = $stmt_orders->get_result();

$seller_orders = [];
while ($row = $res->fetch_assoc()) {
    $order_id = $row['order_id'];
    if (!isset($seller_orders[$order_id])) {
        $seller_orders[$order_id] = [
            'order_id' => $order_id,
            'created_at' => $row['created_at'],
            'order_status' => $row['order_status'],
            'shipping_address' => $row['shipping_address'],
            'payment_method' => $row['payment_method'],
            'customer_name' => $row['customer_name'],
            'customer_email' => $row['customer_email'],
            'items' => []
        ];
    }
    $seller_orders[$order_id]['items'][] = [
        'product_name' => $row['product_name'],
        'price' => $row['price'],
        'quantity' => $row['quantity'],
        'is_digital' => $row['is_digital']
    ];
}
$stmt_orders->close();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5 mt-4">
    <div class="row gy-4">
        <!-- Sidebar Profile -->
        <div class="col-lg-3">
            <div class="card-glass p-4 sticky-top" style="top: 100px; z-index: 10;">
                <div class="text-center mb-4 border-bottom border-secondary pb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-dark rounded-circle border border-secondary mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-shop text-gradient-primary fs-1"></i>
                    </div>
                    <h4 class="text-white font-heading mb-1"><?php echo htmlspecialchars($current_user['name']); ?></h4>
                    <span class="badge bg-info py-1 px-3">Seller Center</span>
                </div>
                
                <div class="d-flex flex-column gap-2 pt-3 border-top border-secondary">
                    <a href="dashboard.php" class="dashboard-nav-link"><i class="bi bi-box-seam"></i> Products Inventory</a>
                    <a href="orders.php" class="dashboard-nav-link active"><i class="bi bi-receipt"></i> Incoming Orders</a>
                    <a href="../logout.php" class="btn btn-outline-danger btn-sm w-100 mt-2"><i class="bi bi-box-arrow-right"></i> Sign Out</a>
                </div>
            </div>
        </div>
        
        <!-- Orders list -->
        <div class="col-lg-9">
            <h2 class="text-white mb-4"><i class="bi bi-receipt-cutoff text-gradient-primary me-2"></i>Incoming Client Orders</h2>
            
            <?php if (!empty($seller_orders)): ?>
                <div class="d-flex flex-column gap-4">
                    <?php foreach ($seller_orders as $ord): ?>
                        <div class="card-glass p-4">
                            <!-- Order details heading -->
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom border-secondary">
                                <div>
                                    <h5 class="text-white font-heading mb-1">Order ID: #<?php echo $ord['order_id']; ?></h5>
                                    <span class="text-secondary small">Customer: <strong><?php echo htmlspecialchars($ord['customer_name']); ?></strong> (<?php echo htmlspecialchars($ord['customer_email']); ?>)</span>
                                </div>
                                <div class="text-end">
                                    <span class="badge <?php 
                                        if ($ord['order_status'] === 'pending') echo 'bg-warning text-dark';
                                        elseif ($ord['order_status'] === 'processing') echo 'bg-info';
                                        elseif ($ord['order_status'] === 'completed') echo 'bg-success';
                                        else echo 'bg-danger';
                                    ?> py-1 px-3 mb-2 d-inline-block">
                                        <?php echo strtoupper($ord['order_status']); ?>
                                    </span>
                                    <div class="small text-secondary">Ordered: <?php echo date("M d, Y H:i", strtotime($ord['created_at'])); ?></div>
                                </div>
                            </div>
                            
                            <!-- Items List -->
                            <h6 class="text-white font-heading mb-2"><i class="bi bi-tag text-info me-1"></i> Ordered Items (My Store)</h6>
                            <div class="table-responsive mb-3">
                                <table class="table table-dark table-premium mb-0 small">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th class="text-center">Price</th>
                                            <th class="text-center">Qty Ordered</th>
                                            <th class="text-end">Line Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $seller_order_total = 0;
                                        foreach ($ord['items'] as $item): 
                                            $item_total = $item['price'] * $item['quantity'];
                                            $seller_order_total += $item_total;
                                        ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                                    <?php if ($item['is_digital']): ?>
                                                        <span class="badge bg-info ms-1" style="font-size:0.6rem;">Digital</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center"><?php echo format_price($item['price']); ?></td>
                                                <td class="text-center"><?php echo $item['quantity']; ?></td>
                                                <td class="text-end"><?php echo format_price($item_total); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr class="fw-bold border-top border-secondary">
                                            <td colspan="3" class="text-end text-white">Order Share Total:</td>
                                            <td class="text-end text-gradient-primary fs-6"><?php echo format_price($seller_order_total); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Logistics and Status Controls -->
                            <div class="row align-items-center gy-3 mt-1">
                                <div class="col-md-7 text-secondary small">
                                    <strong>Shipping Coordinates:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($ord['shipping_address'])); ?>
                                </div>
                                <div class="col-md-5">
                                    <!-- Status update form -->
                                    <form action="orders.php" method="POST" class="d-flex align-items-center gap-2">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="order_id" value="<?php echo $ord['order_id']; ?>">
                                        
                                        <select name="status" class="form-select form-glass-input form-select-sm" style="font-size: 0.8rem;">
                                            <option value="pending" <?php echo $ord['order_status'] == 'pending' ? 'selected' : ''; ?>>Pending Approval</option>
                                            <option value="processing" <?php echo $ord['order_status'] == 'processing' ? 'selected' : ''; ?>>Processing logistics</option>
                                            <option value="completed" <?php echo $ord['order_status'] == 'completed' ? 'selected' : ''; ?>>Completed / Delivered</option>
                                            <option value="cancelled" <?php echo $ord['order_status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled / Reject</option>
                                        </select>
                                        <button type="submit" class="btn btn-premium btn-sm"><i class="bi bi-save"></i> Save</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card-glass p-5 text-center">
                    <i class="bi bi-file-earmark-bar-graph text-secondary" style="font-size: 3rem;"></i>
                    <h5 class="text-white mt-3">No Orders Logged</h5>
                    <p class="text-secondary mb-0 font-heading">You haven't received any orders yet. Ensure your products are premium and locations are coordinate verified!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
