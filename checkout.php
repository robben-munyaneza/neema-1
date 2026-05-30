<?php
// checkout.php
require_once 'includes/header.php';

// Restrict page to Customer role
restrict_to_roles(['customer'], 'login.php');

$current_user = get_current_user_details();

// Redirect to cart if empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['flash_message'] = "Your cart is empty. Please add items to checkout.";
    $_SESSION['flash_type'] = "warning";
    header("Location: shop.php");
    exit;
}

// Fetch Cart items to calculate final total
$cart_items = [];
$total_cost = 0;

$ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$stmt = $conn->prepare("SELECT p.*, s.name as seller_name FROM products p LEFT JOIN users s ON p.seller_id = s.id WHERE p.id IN ($placeholders)");
$types = str_repeat('i', count($ids));
$stmt->bind_param($types, ...$ids);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $qty = $_SESSION['cart'][$row['id']];
    $subtotal = $row['price'] * $qty;
    $total_cost += $subtotal;
    
    $row['quantity'] = $qty;
    $row['subtotal'] = $subtotal;
    $cart_items[] = $row;
}
$stmt->close();

$shipping_address = '';
$payment_method = 'Credit Card';
$error_msg = '';

// Handle Order Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = trim($_POST['shipping_address']);
    $payment_method = $_POST['payment_method'];
    
    if (empty($shipping_address) || empty($payment_method)) {
        $error_msg = "Please fill in all order shipping and payment fields.";
    } else {
        // Start transaction for database safety
        $conn->begin_transaction();
        
        try {
            // 1. Insert Order
            $stmt_order = $conn->prepare("INSERT INTO orders (customer_id, total_amount, status, shipping_address, payment_method) VALUES (?, ?, 'pending', ?, ?)");
            $stmt_order->bind_param("idss", $current_user['id'], $total_cost, $shipping_address, $payment_method);
            $stmt_order->execute();
            $order_id = $conn->insert_id;
            $stmt_order->close();
            
            // 2. Insert Order Items & Deduct Stock
            $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, price, quantity) VALUES (?, ?, ?, ?)");
            $stmt_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            
            foreach ($cart_items as $item) {
                // Insert item
                $stmt_item->bind_param("iidi", $order_id, $item['id'], $item['price'], $item['quantity']);
                $stmt_item->execute();
                
                // Deduct inventory (only for physical items, or digital if stock limited)
                if (!$item['is_digital']) {
                    $stmt_stock->bind_param("ii", $item['quantity'], $item['id']);
                    $stmt_stock->execute();
                }
            }
            $stmt_item->close();
            $stmt_stock->close();
            
            // Commit changes
            $conn->commit();
            
            // Clear shopping cart
            unset($_SESSION['cart']);
            
            $_SESSION['flash_message'] = "Order #$order_id placed successfully! Thank you.";
            $_SESSION['flash_type'] = "success";
            
            // Redirect to customer dashboard to track order
            header("Location: order-confirmation.php?order_id=" . $order_id);
            exit;
            
        } catch (Exception $e) {
            $conn->rollback();
            $error_msg = "Transaction failed: " . $e->getMessage();
        }
    }
}
?>

<div class="container py-5 mt-4">
    <h2 class="text-white mb-4"><i class="bi bi-credit-card-fill text-gradient-primary me-2"></i>Checkout Portal</h2>
    
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger bg-danger-subtle border-danger text-danger-emphasis rounded-3 px-3 py-2 small mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error_msg); ?>
        </div>
    <?php endif; ?>
    
    <div class="row gy-4">
        <!-- Billing Details Form -->
        <div class="col-lg-7">
            <div class="card-glass p-5">
                <h4 class="text-white mb-4 font-heading"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Shipping & Invoicing Specs</h4>
                
                <form action="checkout.php" method="POST" class="d-flex flex-column gap-4">
                    <div>
                        <label class="form-glass-label">Customer Legal Identity</label>
                        <input type="text" class="form-control form-glass-input" value="<?php echo htmlspecialchars($current_user['name']); ?> (<?php echo htmlspecialchars($current_user['email']); ?>)" readonly>
                        <span class="text-secondary small d-block mt-1">Receipt will be generated with these account details.</span>
                    </div>
                    
                    <div>
                        <label class="form-glass-label" for="shipping_address">Delivery Address / GPS Coordinates</label>
                        <textarea name="shipping_address" id="shipping_address" rows="4" class="form-control form-glass-input" placeholder="e.g. KN 12 Ave, Nyarugenge, Kigali or street, city details..." required><?php echo htmlspecialchars($shipping_address); ?></textarea>
                        <span class="text-secondary small d-block mt-1">Required for physical logistics. For digital downloads, enter billing state/country.</span>
                    </div>

                    <div>
                        <label class="form-glass-label d-block">Secure Payment Mechanism</label>
                        <div class="d-flex flex-column gap-2">
                            <div class="form-check p-3 card-glass" style="background: rgba(255,255,255,0.01);">
                                <input class="form-check-input ms-0 me-2" type="radio" name="payment_method" id="pay_card" value="Credit Card" <?php echo $payment_method === 'Credit Card' ? 'checked' : ''; ?>>
                                <label class="form-check-label text-white fw-bold cursor-pointer" for="pay_card">
                                    <i class="bi bi-credit-card me-1 text-info"></i> Visa / MasterCard Online Gateway
                                </label>
                            </div>
                            <div class="form-check p-3 card-glass" style="background: rgba(255,255,255,0.01);">
                                <input class="form-check-input ms-0 me-2" type="radio" name="payment_method" id="pay_momo" value="Mobile Money" <?php echo $payment_method === 'Mobile Money' ? 'checked' : ''; ?>>
                                <label class="form-check-label text-white fw-bold cursor-pointer" for="pay_momo">
                                    <i class="bi bi-phone-vibrate me-1 text-success"></i> Mobile Money API (Mtn / Airtel)
                                </label>
                            </div>
                            <div class="form-check p-3 card-glass" style="background: rgba(255,255,255,0.01);">
                                <input class="form-check-input ms-0 me-2" type="radio" name="payment_method" id="pay_paypal" value="PayPal" <?php echo $payment_method === 'PayPal' ? 'checked' : ''; ?>>
                                <label class="form-check-label text-white fw-bold cursor-pointer" for="pay_paypal">
                                    <i class="bi bi-paypal me-1 text-primary"></i> Secure PayPal System
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-premium w-100 py-3 mt-2"><i class="bi bi-shield-check"></i> Place Order / Complete Transaction</button>
                </form>
            </div>
        </div>
        
        <!-- Cart Summary Column -->
        <div class="col-lg-5">
            <div class="cart-summary-card">
                <h4 class="text-white font-heading border-bottom border-secondary pb-3 mb-4">Cart Checklist</h4>
                
                <div class="d-flex flex-column gap-3 mb-4" style="max-height: 250px; overflow-y: auto;">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="d-flex justify-content-between align-items-center bg-dark p-2 rounded border border-secondary" style="background: rgba(0,0,0,0.2) !important;">
                            <div>
                                <h6 class="text-white small mb-0 font-heading text-truncate" style="max-width: 200px;"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <span class="text-secondary small">Qty: <?php echo $item['quantity']; ?></span>
                            </div>
                            <span class="text-white small fw-bold"><?php echo format_price($item['subtotal']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3 text-secondary">
                    <span>Order Total</span>
                    <span class="text-white fw-bold"><?php echo format_price($total_cost); ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4 text-secondary">
                    <span>Logistics Fee</span>
                    <span class="text-success small fw-bold">FREE</span>
                </div>
                
                <hr class="border-secondary mb-4">
                
                <div class="d-flex justify-content-between align-items-center mb-4 font-heading">
                    <span class="fs-5 text-white">Final Bill</span>
                    <span class="fs-3 fw-bold text-gradient-primary"><?php echo format_price($total_cost); ?></span>
                </div>
                
                <div class="alert alert-info bg-info-subtle border-info text-info-emphasis rounded-3 px-3 py-2 small mb-0">
                    <i class="bi bi-info-circle-fill me-2"></i>Digital downloads will become instantly downloadable in your customer center.
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
