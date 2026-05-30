<?php
// order-confirmation.php
require_once 'includes/header.php';

restrict_to_roles(['customer'], 'login.php');

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$current_user = get_current_user_details();

// Fetch order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ?");
$stmt->bind_param("ii", $order_id, $current_user['id']);
$stmt->execute();
$order_res = $stmt->get_result();

if ($order_res->num_rows === 0) {
    $_SESSION['flash_message'] = "Order record not found.";
    $_SESSION['flash_type'] = "danger";
    header("Location: index.php");
    exit;
}

$order = $order_res->fetch_assoc();
$stmt->close();

// Fetch order items
$items_stmt = $conn->prepare("SELECT oi.*, p.name as product_name, p.is_digital, p.file_url 
                              FROM order_items oi 
                              LEFT JOIN products p ON oi.product_id = p.id 
                              WHERE oi.order_id = ?");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_res = $items_stmt->get_result();
$order_items = [];
while($row = $items_res->fetch_assoc()) {
    $order_items[] = $row;
}
$items_stmt->close();
?>

<div class="container py-5 mt-4 text-center">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card-glass p-5 animate-fade-in">
                <!-- Checkmark Animation -->
                <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle rounded-circle border border-success mb-4" style="width: 90px; height: 90px; background: rgba(16, 185, 129, 0.15) !important;">
                    <i class="bi bi-patch-check-fill text-success fs-1 animate-fade-in" style="font-size: 3rem !important;"></i>
                </div>
                
                <h1 class="text-white font-heading mb-1">Transaction Successful!</h1>
                <p class="text-secondary">Order #<?php echo $order_id; ?> has been successfully initialized & recorded.</p>
                
                <div class="card-glass p-4 text-start my-4" style="background: rgba(0,0,0,0.15);">
                    <h5 class="text-white border-bottom border-secondary pb-2 mb-3">Order Receipt Detail</h5>
                    
                    <div class="row g-2 small text-secondary mb-3">
                        <div class="col-6"><strong>Client Name:</strong> <?php echo htmlspecialchars($current_user['name']); ?></div>
                        <div class="col-6"><strong>Order Date:</strong> <?php echo date("F d, Y H:i", strtotime($order['created_at'])); ?></div>
                        <div class="col-6"><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></div>
                        <div class="col-6"><strong>Status:</strong> <span class="badge bg-warning text-dark"><?php echo strtoupper($order['status']); ?></span></div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-dark table-premium mb-0 small">
                            <thead>
                                <tr>
                                    <th>Product Item</th>
                                    <th class="text-center">Price</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($item['product_name']); ?>
                                            <?php if ($item['is_digital']): ?>
                                                <span class="badge bg-info ms-1" style="font-size:0.6rem;">Digital</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?php echo format_price($item['price']); ?></td>
                                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                                        <td class="text-end"><?php echo format_price($item['price'] * $item['quantity']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="fw-bold border-top border-secondary">
                                    <td colspan="3" class="text-end text-white">Final Total:</td>
                                    <td class="text-end text-gradient-primary fs-5"><?php echo format_price($order['total_amount']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3 text-secondary small">
                        <strong>Delivery Target:</strong> <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="customer/dashboard.php" class="btn btn-premium px-4"><i class="bi bi-person-bounding-box"></i> Go to Dashboard / Downloads</a>
                    
                    <!-- Form to export Invoice as Text file download -->
                    <form action="customer/dashboard.php" method="GET" class="d-inline-block">
                        <input type="hidden" name="download_invoice" value="<?php echo $order_id; ?>">
                        <button type="submit" class="btn btn-premium-secondary px-4"><i class="bi bi-file-earmark-arrow-down-fill"></i> Download Invoice Receipt</button>
                    </form>
                    
                    <a href="shop.php" class="btn btn-outline-secondary text-white px-4">Return to Shop</a>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
