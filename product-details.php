<?php
// product-details.php
require_once 'includes/header.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch Product Details
$stmt = $conn->prepare("SELECT p.*, c.name as category_name, s.name as seller_name, s.seller_location 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        LEFT JOIN users s ON p.seller_id = s.id 
                        WHERE p.id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$prod_res = $stmt->get_result();

if ($prod_res->num_rows === 0) {
    $_SESSION['flash_message'] = "Product not found.";
    $_SESSION['flash_type'] = "danger";
    header("Location: shop.php");
    exit;
}

$prod = $prod_res->fetch_assoc();
$stmt->close();

// Handle "Add to Cart" form action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    if ($qty <= 0) $qty = 1;
    
    // Add to cart helper function from auth.php
    cart_add($product_id, $qty);
    
    $_SESSION['flash_message'] = "{$prod['name']} added to your cart!";
    $_SESSION['flash_type'] = "success";
    header("Location: cart.php");
    exit;
}

// Handle Quality Comment Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_comment') {
    $curr_user = get_current_user_details();
    if (!$curr_user) {
        $_SESSION['flash_message'] = "Please login as a customer to submit a review.";
        $_SESSION['flash_type'] = "warning";
        header("Location: login.php");
        exit;
    }
    
    if ($curr_user['role'] !== 'customer') {
        $_SESSION['flash_message'] = "Only customers are permitted to submit reviews.";
        $_SESSION['flash_type'] = "danger";
    } else {
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
        $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
        
        if ($rating < 1 || $rating > 5 || empty($comment)) {
            $_SESSION['flash_message'] = "Please provide both a star rating and a comment.";
            $_SESSION['flash_type'] = "warning";
        } else {
            $stmt_comm = $conn->prepare("INSERT INTO comments (product_id, customer_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt_comm->bind_param("iiis", $product_id, $curr_user['id'], $rating, $comment);
            if ($stmt_comm->execute()) {
                $_SESSION['flash_message'] = "Your review has been successfully posted!";
                $_SESSION['flash_type'] = "success";
            } else {
                $_SESSION['flash_message'] = "Failed to post review. Please try again.";
                $_SESSION['flash_type'] = "danger";
            }
            $stmt_comm->close();
        }
    }
    // Refresh page to show new review
    header("Location: product-details.php?id=" . $product_id);
    exit;
}

// Fetch all Quality Comments / Reviews
$comm_stmt = $conn->prepare("SELECT c.*, u.name as customer_name 
                             FROM comments c 
                             LEFT JOIN users u ON c.customer_id = u.id 
                             WHERE c.product_id = ? 
                             ORDER BY c.id DESC");
$comm_stmt->bind_param("i", $product_id);
$comm_stmt->execute();
$comments_res = $comm_stmt->get_result();
$comm_stmt->close();
?>

<div class="container py-5 mt-4">
    <!-- Product Profile Header -->
    <div class="row gy-5 mb-5 align-items-center">
        <!-- Visual Render Column -->
        <div class="col-lg-6">
            <div class="card-glass p-3 text-center" style="background: rgba(18, 20, 32, 0.4);">
                <img src="<?php echo htmlspecialchars($prod['image_url']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" class="img-fluid rounded-4 w-100" style="max-height: 400px; object-fit: contain;">
            </div>
        </div>
        
        <!-- Interactive Detail Control Column -->
        <div class="col-lg-6">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="badge-premium"><?php echo htmlspecialchars($prod['category_name'] ?? 'Generic'); ?></span>
                <span class="badge <?php echo $prod['is_digital'] ? 'bg-info' : 'bg-primary'; ?>">
                    <?php echo $prod['is_digital'] ? 'Digital Goods' : 'Physical Logistics'; ?>
                </span>
            </div>
            
            <h1 class="text-white mb-2 font-heading" style="font-size: 2.8rem;"><?php echo htmlspecialchars($prod['name']); ?></h1>
            
            <div class="d-flex align-items-center gap-4 mb-4">
                <div class="fs-2 fw-bold text-gradient-primary font-heading"><?php echo format_price($prod['price']); ?></div>
                <div class="text-secondary small">|</div>
                <div class="text-success small"><i class="bi bi-shield-check me-1"></i> In Stock (<?php echo $prod['stock']; ?> available)</div>
            </div>
            
            <p class="text-secondary mb-4" style="font-size: 1.1rem; line-height: 1.6;">
                <?php echo nl2br(htmlspecialchars($prod['description'])); ?>
            </p>
            
            <!-- Seller Detail Bar -->
            <div class="card-glass p-3 mb-4 d-flex justify-content-between align-items-center" style="background: rgba(255, 255, 255, 0.02);">
                <div>
                    <div class="text-secondary small">Seller Marketplace</div>
                    <div class="text-white font-heading fw-bold"><i class="bi bi-shop text-info me-1"></i> <?php echo htmlspecialchars($prod['seller_name']); ?></div>
                </div>
                <div class="text-end">
                    <div class="text-secondary small">Trading Coordinates</div>
                    <div class="text-white font-heading fw-bold"><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?php echo htmlspecialchars($prod['seller_location'] ?? 'Global'); ?></div>
                </div>
            </div>
            
            <!-- Add to Cart Widget -->
            <form action="product-details.php?id=<?php echo $product_id; ?>" method="POST" class="row g-3 align-items-center">
                <input type="hidden" name="action" value="add_to_cart">
                <div class="col-auto">
                    <label class="visually-hidden" for="quantity">Quantity</label>
                    <input type="number" name="quantity" id="quantity" class="form-control form-glass-input text-center" value="1" min="1" max="<?php echo $prod['stock']; ?>" style="width: 80px;">
                </div>
                <div class="col">
                    <button type="submit" class="btn btn-premium w-100 py-3"><i class="bi bi-cart-plus-fill"></i> Add To Shopping Cart</button>
                </div>
            </form>
        </div>
    </div>

    <hr class="border-secondary my-5">

    <!-- Quality reviews and Comments Section -->
    <div class="row gy-5">
        <!-- Product Reviews Grid -->
        <div class="col-lg-7">
            <h3 class="text-white mb-4"><i class="bi bi-chat-square-text text-gradient-primary me-2"></i>Customer Comments on Quality</h3>
            
            <?php if ($comments_res && $comments_res->num_rows > 0): ?>
                <div class="d-flex flex-column gap-3">
                    <?php while ($comm = $comments_res->fetch_assoc()): ?>
                        <div class="review-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-white fw-bold"><i class="bi bi-person me-1"></i> <?php echo htmlspecialchars($comm['customer_name'] ?? 'Verified Buyer'); ?></span>
                                <div class="star-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi <?php echo $i <= $comm['rating'] ? 'bi-star-fill' : 'bi-star'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="text-secondary small mb-1"><?php echo nl2br(htmlspecialchars($comm['comment'])); ?></p>
                            <div class="text-muted text-end" style="font-size: 0.75rem;"><?php echo date("F d, Y", strtotime($comm['created_at'])); ?></div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="card-glass p-5 text-center">
                    <i class="bi bi-chat-dots text-secondary" style="font-size: 3rem;"></i>
                    <h5 class="text-white mt-3">No Reviews Yet</h5>
                    <p class="text-secondary mb-0">Be the first to comment on the quality of this product!</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Post Review Form (Only for Customer role) -->
        <div class="col-lg-5">
            <div class="card-glass p-4">
                <h4 class="text-white mb-3">Review Product Quality</h4>
                
                <?php if ($current_user && $current_user['role'] === 'customer'): ?>
                    <form action="product-details.php?id=<?php echo $product_id; ?>" method="POST" class="d-flex flex-column gap-3">
                        <input type="hidden" name="action" value="submit_comment">
                        <input type="hidden" name="rating" id="rating-input" value="5">
                        
                        <div>
                            <label class="form-glass-label d-block">Overall Quality Grade</label>
                            <div class="star-picker fs-3 d-flex gap-2">
                                <i class="bi bi-star-fill star-rating cursor-pointer" data-rating="1" style="cursor:pointer;"></i>
                                <i class="bi bi-star-fill star-rating cursor-pointer" data-rating="2" style="cursor:pointer;"></i>
                                <i class="bi bi-star-fill star-rating cursor-pointer" data-rating="3" style="cursor:pointer;"></i>
                                <i class="bi bi-star-fill star-rating cursor-pointer" data-rating="4" style="cursor:pointer;"></i>
                                <i class="bi bi-star-fill star-rating cursor-pointer" data-rating="5" style="cursor:pointer;"></i>
                            </div>
                        </div>
                        
                        <div>
                            <label class="form-glass-label" for="comment">Detailed Quality Review</label>
                            <textarea name="comment" id="comment" rows="4" class="form-control form-glass-input" placeholder="Give details about materials, design, functionality..." required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-premium w-100">Submit Quality Comment <i class="bi bi-send-fill"></i></button>
                    </form>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-lock-fill text-muted" style="font-size: 2.5rem;"></i>
                        <h5 class="text-white mt-2">Locked Feature</h5>
                        <p class="text-secondary small mb-3">Only authenticated customers can post product quality reviews.</p>
                        <a href="login.php" class="btn btn-premium btn-sm">Login as Customer</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
