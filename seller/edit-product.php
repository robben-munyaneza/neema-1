<?php
// seller/edit-product.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

restrict_to_roles(['seller'], '../login.php');

$current_user = get_current_user_details();
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch Product Details to ensure ownership
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
$stmt->bind_param("ii", $product_id, $current_user['id']);
$stmt->execute();
$prod_res = $stmt->get_result();

if ($prod_res->num_rows === 0) {
    $_SESSION['flash_message'] = "Product listing not found or unauthorized.";
    $_SESSION['flash_type'] = "danger";
    header("Location: dashboard.php");
    exit;
}

$prod = $prod_res->fetch_assoc();
$stmt->close();

$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $stock = (int)$_POST['stock'];
    
    if (empty($name) || empty($description) || $price <= 0 || $category_id <= 0) {
        $error_msg = "Please fill in all standard product parameters.";
    } else {
        $image_path = $prod['image_url'];
        $file_path = $prod['file_url'];
        
        // Optional Image Update
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $img_tmp = $_FILES['product_image']['tmp_name'];
            $img_name = $_FILES['product_image']['name'];
            $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
            
            $upload_dir = __DIR__ . '/../assets/uploads/';
            $new_img_name = 'prod_' . uniqid() . '.' . $img_ext;
            
            if (move_uploaded_file($img_tmp, $upload_dir . $new_img_name)) {
                $image_path = 'assets/uploads/' . $new_img_name;
            }
        }
        
        // Optional Digital Resource Update
        if ($prod['is_digital'] && isset($_FILES['digital_file']) && $_FILES['digital_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['digital_file']['tmp_name'];
            $file_orig_name = $_FILES['digital_file']['name'];
            $file_ext = strtolower(pathinfo($file_orig_name, PATHINFO_EXTENSION));
            
            $upload_dir = __DIR__ . '/../assets/uploads/';
            $new_file_name = 'digital_' . uniqid() . '.' . $file_ext;
            
            if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                $file_path = 'assets/uploads/' . $new_file_name;
            }
        }
        
        // Database Update
        $stmt_up = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, image_url = ?, file_url = ?, stock = ? WHERE id = ? AND seller_id = ?");
        $stmt_up->bind_param("ssdissiii", $name, $description, $price, $category_id, $image_path, $file_path, $stock, $product_id, $current_user['id']);
        
        if ($stmt_up->execute()) {
            $_SESSION['flash_message'] = "Product updated successfully!";
            $_SESSION['flash_type'] = "success";
            header("Location: dashboard.php");
            exit;
        } else {
            $error_msg = "Database update error: " . $conn->error;
        }
        $stmt_up->close();
    }
}

$cats_res = $conn->query("SELECT * FROM categories ORDER BY name ASC");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5 mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card-glass p-5 animate-fade-in">
                <h2 class="text-white mb-4 font-heading"><i class="bi bi-pencil-square text-gradient-primary me-2"></i>Edit Product</h2>
                
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger bg-danger-subtle border-danger text-danger-emphasis rounded-3 px-3 py-2 small mb-3">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error_msg); ?>
                    </div>
                <?php endif; ?>

                <form action="edit-product.php?id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data" class="d-flex flex-column gap-3">
                    <div>
                        <label class="form-glass-label" for="prod_name">Product Name / Title</label>
                        <input type="text" name="name" id="prod_name" class="form-control form-glass-input" value="<?php echo htmlspecialchars($prod['name']); ?>" required>
                    </div>
                    
                    <div>
                        <label class="form-glass-label" for="category_id">Category</label>
                        <select name="category_id" id="category_id" class="form-select form-glass-input" required>
                            <?php if ($cats_res && $cats_res->num_rows > 0): ?>
                                <?php while ($cat = $cats_res->fetch_assoc()): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $prod['category_id'] == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-glass-label" for="prod_price">Price (USD)</label>
                            <input type="number" step="0.01" name="price" id="prod_price" class="form-control form-glass-input" value="<?php echo htmlspecialchars($prod['price']); ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-glass-label" for="prod_stock">Stock</label>
                            <input type="number" name="stock" id="prod_stock" class="form-control form-glass-input" value="<?php echo htmlspecialchars($prod['stock']); ?>" <?php echo $prod['is_digital'] ? 'readonly' : ''; ?>>
                        </div>
                    </div>

                    <div>
                        <label class="form-glass-label" for="prod_desc">Product Description</label>
                        <textarea name="description" id="prod_desc" rows="4" class="form-control form-glass-input" required><?php echo htmlspecialchars($prod['description']); ?></textarea>
                    </div>

                    <div>
                        <label class="form-glass-label d-block">Presentation Graphic</label>
                        <img src="../<?php echo htmlspecialchars($prod['image_url']); ?>" class="img-fluid rounded mb-2 d-block border border-secondary" style="max-height: 100px; object-fit: contain;">
                        <input type="file" name="product_image" id="product_image" class="form-control form-glass-input">
                        <span class="text-secondary small d-block mt-1">Leave empty to keep existing image file.</span>
                    </div>

                    <?php if ($prod['is_digital']): ?>
                        <div class="p-3 border border-info rounded-3 mt-2" style="background: rgba(14,165,233,0.05);">
                            <h6 class="text-info font-heading"><i class="bi bi-cloud-download-fill me-1"></i>Digital Resource Management</h6>
                            <div class="text-secondary small mb-2">Current File: <?php echo htmlspecialchars(basename($prod['file_url'])); ?></div>
                            <input type="file" name="digital_file" id="digital_file" class="form-control form-glass-input">
                            <span class="text-secondary small d-block mt-1">Upload single zip/pdf/svg file to update the resource. Leave blank to keep current.</span>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex gap-3 mt-3">
                        <button type="submit" class="btn btn-premium w-100 py-3"><i class="bi bi-check-circle-fill"></i> Save Updates</button>
                        <a href="dashboard.php" class="btn btn-premium-secondary w-100 py-3 text-center">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
