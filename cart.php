<?php
// cart.php
require_once 'includes/header.php';

// Handle POST actions for cart management (Update / Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $prod_id = (int)$_POST['product_id'];
        
        if ($_POST['action'] === 'update') {
            $qty = (int)$_POST['quantity'];
            if ($qty <= 0) {
                cart_remove($prod_id);
                $_SESSION['flash_message'] = "Item removed from cart.";
                $_SESSION['flash_type'] = "info";
            } else {
                $_SESSION['cart'][$prod_id] = $qty;
                $_SESSION['flash_message'] = "Cart quantities updated.";
                $_SESSION['flash_type'] = "success";
            }
        } elseif ($_POST['action'] === 'remove') {
            cart_remove($prod_id);
            $_SESSION['flash_message'] = "Item removed from cart.";
            $_SESSION['flash_type'] = "info";
        }
        
        header("Location: cart.php");
        exit;
    }
}

// Fetch details for all items in the cart
$cart_items = [];
$total_cost = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    $stmt = $conn->prepare("SELECT p.*, s.name as seller_name FROM products p LEFT JOIN users s ON p.seller_id = s.id WHERE p.id IN ($placeholders)");
    
    // Bind dynamic number of arguments
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
}
?>

<div class="container py-5 mt-4">
    <h2 class="text-white mb-4"><i class="bi bi-cart3 text-gradient-primary me-2"></i>Your Shopping Cart</h2>
    
    <?php if (empty($cart_items)): ?>
        <!-- Empty Cart Visual -->
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 col-sm-10 text-center py-5">
                <div class="card-glass p-5">
                    <i class="bi bi-cart-x text-secondary" style="font-size: 4rem;"></i>
                    <h3 class="text-white mt-4 font-heading">Cart is Empty</h3>
                    <p class="text-secondary mb-4">Browse our premium marketplace catalogues to populate your shopping order bag.</p>
                    <a href="shop.php" class="btn btn-premium btn-lg">Explore Shop Catalogue</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row gy-4">
            <!-- Items Column -->
            <div class="col-lg-8">
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="card-glass p-3">
                            <div class="row align-items-center gy-3">
                                <!-- Photo -->
                                <div class="col-md-2 col-4">
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-fluid rounded" style="max-height: 80px; object-fit: contain;">
                                </div>
                                <!-- Descriptor -->
                                <div class="col-md-4 col-8">
                                    <h6 class="text-white font-heading mb-1 text-truncate" title="<?php echo htmlspecialchars($item['name']); ?>"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <div class="text-secondary small">Seller: <?php echo htmlspecialchars($item['seller_name']); ?></div>
                                    <span class="badge <?php echo $item['is_digital'] ? 'bg-info' : 'bg-primary'; ?> mt-1" style="font-size: 0.7rem;">
                                        <?php echo $item['is_digital'] ? 'Digital Download' : 'Physical Logistics'; ?>
                                    </span>
                                </div>
                                <!-- Price & Action controls -->
                                <div class="col-md-6 col-12 d-flex align-items-center justify-content-between">
                                    <div class="text-white fw-bold me-3"><?php echo format_price($item['price']); ?> each</div>
                                    
                                    <!-- Qty Control -->
                                    <form action="cart.php" method="POST" class="d-flex align-items-center gap-1">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <input type="number" name="quantity" class="form-control form-glass-input text-center px-1" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" style="width: 60px;" onchange="this.form.submit()">
                                    </form>
                                    
                                    <!-- Subtotal -->
                                    <div class="text-gradient-primary fw-bold font-heading mx-3"><?php echo format_price($item['subtotal']); ?></div>
                                    
                                    <!-- Delete Item -->
                                    <form action="cart.php" method="POST">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm border-0"><i class="bi bi-trash3-fill"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Summary Column -->
            <div class="col-lg-4">
                <div class="cart-summary-card">
                    <h4 class="text-white font-heading border-bottom border-secondary pb-3 mb-4">Summary Bill</h4>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3 text-secondary">
                        <span>Items Subtotal</span>
                        <span class="text-white fw-bold"><?php echo format_price($total_cost); ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-4 text-secondary">
                        <span>Shipping/Downloads</span>
                        <span class="text-success small fw-bold">FREE GUARANTEE</span>
                    </div>
                    
                    <hr class="border-secondary mb-4">
                    
                    <div class="d-flex justify-content-between align-items-center mb-5 font-heading">
                        <span class="fs-5 text-white">Final Total Price</span>
                        <span class="fs-3 fw-bold text-gradient-primary"><?php echo format_price($total_cost); ?></span>
                    </div>
                    
                    <div class="d-flex flex-column gap-3">
                        <a href="checkout.php" class="btn btn-premium w-100 py-3 text-center"><i class="bi bi-credit-card-fill"></i> Proceed to Checkout</a>
                        <a href="shop.php" class="btn btn-premium-secondary w-100 py-3 text-center"><i class="bi bi-arrow-left"></i> Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
