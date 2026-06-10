<?php
// seller/dashboard.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Restrict access to Seller role
restrict_to_roles(['seller'], '../login.php');

$current_user = get_current_user_details();

// Handle file upload and product creation
$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_product') {
    // Check if seller is approved before allowing uploads
    if ($current_user['status'] !== 'approved') {
        $error_msg = "Compliance alert: You cannot upload products until the Admin approves your account compliance documents.";
    } else {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $category_id = (int)$_POST['category_id'];
        $stock = (int)$_POST['stock'];
        $is_digital = isset($_POST['is_digital']) ? 1 : 0;
        
        if (empty($name) || empty($description) || $price <= 0 || $category_id <= 0) {
            $error_msg = "Please fill in all product description fields and specify categories/prices.";
        } else {
            $image_path = '';
            $file_path = NULL;
            
            // Handle Product Image Upload
            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                $img_tmp = $_FILES['product_image']['tmp_name'];
                $img_name = $_FILES['product_image']['name'];
                $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
                
                $upload_dir = __DIR__ . '/../assets/uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $new_img_name = 'prod_' . uniqid() . '.' . $img_ext;
                $dest_path = $upload_dir . $new_img_name;
                
                if (move_uploaded_file($img_tmp, $dest_path)) {
                    $image_path = 'assets/uploads/' . $new_img_name;
                } else {
                    $error_msg = "Failed to upload product visual photo.";
                }
            } else {
                $error_msg = "A valid product presentation image file is required.";
            }
            
            // Handle Digital File Upload (if digital is checked)
            if (empty($error_msg) && $is_digital) {
                if (isset($_FILES['digital_file']) && $_FILES['digital_file']['error'] === UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES['digital_file']['tmp_name'];
                    $file_orig_name = $_FILES['digital_file']['name'];
                    $file_ext = strtolower(pathinfo($file_orig_name, PATHINFO_EXTENSION));
                    
                    $new_file_name = 'digital_' . uniqid() . '.' . $file_ext;
                    $dest_file_path = $upload_dir . $new_file_name;
                    
                    if (move_uploaded_file($file_tmp, $dest_file_path)) {
                        $file_path = 'assets/uploads/' . $new_file_name;
                        $stock = 9999; // Set unlimited virtual stock for digital products
                    } else {
                        $error_msg = "Failed to upload downloadable digital file resource.";
                    }
                } else {
                    $error_msg = "You checked 'Digital Product' but uploaded no downloadable asset files.";
                }
            }
            
            // Insert product if no upload errors
            if (empty($error_msg)) {
                $stmt = $conn->prepare("INSERT INTO products (seller_id, name, description, price, category_id, image_url, file_url, stock, is_digital) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issdisssi", $current_user['id'], $name, $description, $price, $category_id, $image_path, $file_path, $stock, $is_digital);
                
                if ($stmt->execute()) {
                    $success_msg = "Product successfully created and added to store!";
                } else {
                    $error_msg = "Database insert failed: " . $conn->error;
                }
                $stmt->close();
            }
        }
    }
}

// Handle Delete product request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_product') {
    $del_id = (int)$_POST['product_id'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
    $stmt->bind_param("ii", $del_id, $current_user['id']);
    if ($stmt->execute()) {
        $_SESSION['flash_message'] = "Product deleted successfully.";
        $_SESSION['flash_type'] = "info";
    } else {
        $_SESSION['flash_message'] = "Failed to delete product.";
        $_SESSION['flash_type'] = "danger";
    }
    $stmt->close();
    header("Location: dashboard.php");
    exit;
}

// Fetch categories for product form
$cats_res = $conn->query("SELECT * FROM categories ORDER BY name ASC");

// Fetch products owned by this Seller
$stmt_prods = $conn->prepare("SELECT p.*, c.name as category_name 
                              FROM products p 
                              LEFT JOIN categories c ON p.category_id = c.id 
                              WHERE p.seller_id = ? 
                              ORDER BY p.id DESC");
$stmt_prods->bind_param("i", $current_user['id']);
$stmt_prods->execute();
$seller_prods = $stmt_prods->get_result();
$stmt_prods->close();

require_once __DIR__ . '/../includes/dashboard_header.php';
?>

    <!-- Compliance Status Banner -->
    <?php if ($current_user['status'] !== 'approved'): ?>
        <div class="alert alert-warning bg-warning-subtle border-warning text-dark rounded-4 p-4 mb-5 animate-fade-in">
            <h5 class="font-heading mb-2"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Account Approval Pending</h5>
            <p class="mb-0 small">Your Compliance Documents are currently under administrative review. Once the Administrator verifies your legal compliance uploads, you will be authorized to publish product listings and receive orders. Your set trading location is: <strong><?php echo htmlspecialchars($current_user['location']); ?></strong>.</p>
        </div>
    <?php endif; ?>

        <!-- Inventory management & Form -->
        <div class="col-12">
            <h2 class="text-white mb-4"><i class="bi bi-shop-window text-gradient-primary me-2"></i>Inventory Control Manager</h2>
            
            <div class="row g-4">
                <!-- Current Products List -->
                <div class="col-xl-7">
                    <div class="card-glass p-4 h-100">
                        <h4 class="text-white mb-3 font-heading">Existing Catalogues</h4>
                        
                        <?php if ($seller_prods && $seller_prods->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-dark table-premium mb-0 small">
                                    <thead>
                                        <tr>
                                            <th>Product Details</th>
                                            <th class="text-center">Price</th>
                                            <th class="text-center">Stock</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($prod = $seller_prods->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <img src="../<?php echo htmlspecialchars($prod['image_url']); ?>" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                                        <div>
                                                            <div class="text-white fw-bold"><?php echo htmlspecialchars($prod['name']); ?></div>
                                                            <span class="badge <?php echo $prod['is_digital'] ? 'bg-info' : 'bg-primary'; ?>" style="font-size:0.6rem;">
                                                                <?php echo $prod['is_digital'] ? 'Digital' : 'Physical'; ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center"><?php echo format_price($prod['price']); ?></td>
                                                <td class="text-center"><?php echo $prod['is_digital'] ? '∞' : $prod['stock']; ?></td>
                                                <td class="text-end">
                                                    <div class="d-flex justify-content-end gap-1">
                                                        <a href="edit-product.php?id=<?php echo $prod['id']; ?>" class="btn btn-sm btn-outline-info border-0"><i class="bi bi-pencil-square"></i></a>
                                                        
                                                        <form action="dashboard.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this listing?');">
                                                            <input type="hidden" name="action" value="delete_product">
                                                            <input type="hidden" name="product_id" value="<?php echo $prod['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-trash3-fill"></i></button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-box-seam text-secondary" style="font-size: 3rem;"></i>
                                <h5 class="text-white mt-3">No Products Listed</h5>
                                <p class="text-secondary mb-0">Use the creation form on the right to upload your first listing!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Upload New Product Form -->
                <div class="col-xl-5">
                    <div class="card-glass p-4 h-100">
                        <h4 class="text-white mb-3 font-heading"><i class="bi bi-cloud-arrow-up-fill text-gradient-primary me-2"></i>Publish Listing</h4>
                        
                        <?php if (!empty($error_msg)): ?>
                            <div class="alert alert-danger bg-danger-subtle border-danger text-danger-emphasis rounded-3 px-3 py-2 small mb-3">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error_msg); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success_msg)): ?>
                            <div class="alert alert-success bg-success-subtle border-success text-success-emphasis rounded-3 px-3 py-2 small mb-3">
                                <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success_msg); ?>
                            </div>
                        <?php endif; ?>

                        <form action="dashboard.php" method="POST" enctype="multipart/form-data" class="d-flex flex-column gap-3">
                            <input type="hidden" name="action" value="create_product">
                            
                            <div>
                                <label class="form-glass-label" for="prod_name">Product Name / Title</label>
                                <input type="text" name="name" id="prod_name" class="form-control form-glass-input" placeholder="e.g. Cyberpunk Stand" required>
                            </div>
                            
                            <div>
                                <label class="form-glass-label" for="category_id">Category</label>
                                <select name="category_id" id="category_id" class="form-select form-glass-input" required>
                                    <option value="">Select Category</option>
                                    <?php if ($cats_res && $cats_res->num_rows > 0): ?>
                                        <?php while ($cat = $cats_res->fetch_assoc()): ?>
                                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-glass-label" for="prod_price">Price (USD)</label>
                                    <input type="number" step="0.01" name="price" id="prod_price" class="form-control form-glass-input" placeholder="29.99" required>
                                </div>
                                <div class="col-6" id="stock-field-container">
                                    <label class="form-glass-label" for="prod_stock">Initial Stock</label>
                                    <input type="number" name="stock" id="prod_stock" class="form-control form-glass-input" value="10">
                                </div>
                            </div>

                            <div>
                                <label class="form-glass-label" for="prod_desc">Product Description</label>
                                <textarea name="description" id="prod_desc" rows="3" class="form-control form-glass-input" placeholder="Full details on material, dimensions, components..." required></textarea>
                            </div>

                            <div>
                                <label class="form-glass-label" for="product_image">Product Presentation Photo</label>
                                <input type="file" name="product_image" id="product_image" class="form-control form-glass-input" required>
                            </div>

                            <!-- Digital Checkbox -->
                            <div class="form-check p-1">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="is_digital" id="is_digital">
                                <label class="form-check-label text-white fw-bold cursor-pointer" for="is_digital">
                                    This is a Downloadable Digital Product
                                </label>
                            </div>

                            <!-- Digital Resource File upload (Initially hidden) -->
                            <div id="digital-resource-field" style="display: none;">
                                <label class="form-glass-label" for="digital_file">Downloadable Digital Resource Asset (.zip, .pdf, .svg)</label>
                                <input type="file" name="digital_file" id="digital_file" class="form-control form-glass-input">
                                <span class="text-secondary small d-block mt-1">This file will be delivered dynamically to buying customers.</span>
                            </div>

                            <button type="submit" class="btn btn-premium w-100 py-3 mt-2"><i class="bi bi-plus-circle"></i> Create & List Product</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const isDigital = document.getElementById('is_digital');
    const digitalField = document.getElementById('digital-resource-field');
    const digitalFileInput = document.getElementById('digital_file');
    const stockField = document.getElementById('stock-field-container');
    const stockInput = document.getElementById('prod_stock');

    isDigital.addEventListener('change', () => {
        if (isDigital.checked) {
            digitalField.style.display = 'block';
            digitalFileInput.setAttribute('required', 'required');
            stockField.style.opacity = '0.5';
            stockInput.value = '9999';
            stockInput.setAttribute('readonly', 'readonly');
        } else {
            digitalField.style.display = 'none';
            digitalFileInput.removeAttribute('required');
            stockField.style.opacity = '1';
            stockInput.value = '10';
            stockInput.removeAttribute('readonly');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
