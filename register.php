<?php
// register.php
require_once 'includes/header.php';

// Redirect if already logged in
if (is_logged_in()) {
    header("Location: index.php");
    exit;
}

$name = '';
$email = '';
$role = 'customer';
$location = '';
$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role']; // 'customer' or 'seller'
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    
    // Server-side validation
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error_msg = "Please fill in all standard required fields.";
    } elseif ($role === 'seller' && empty($location)) {
        $error_msg = "Sellers must specify their trading location coordinates.";
    } else {
        // Check if email already exists
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows > 0) {
            $error_msg = "An account with this email address already exists.";
            $stmt_check->close();
        } else {
            $stmt_check->close();
            
            $doc_path = NULL;
            $status = 'approved'; // Customers are approved by default
            
            // Handle Seller Specific logic (Document Upload & Pending Status)
            if ($role === 'seller') {
                $status = 'pending'; // Sellers start as pending until admin reviews documents
                
                if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES['document']['tmp_name'];
                    $file_name = $_FILES['document']['name'];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    
                    // Create documents upload directory if not exists
                    $upload_dir = __DIR__ . '/assets/uploads/documents/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $new_file_name = 'doc_' . uniqid() . '.' . $file_ext;
                    $dest_path = $upload_dir . $new_file_name;
                    
                    if (move_uploaded_file($file_tmp, $dest_path)) {
                        $doc_path = 'assets/uploads/documents/' . $new_file_name;
                    } else {
                        $error_msg = "Failed to upload business/identity documents.";
                    }
                } else {
                    $error_msg = "Sellers must upload valid identification/business registry documents.";
                }
            }
            
            // Proceed if no errors occurred
            if (empty($error_msg)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt_insert = $conn->prepare("INSERT INTO users (name, email, password, role, status, seller_location, seller_documents) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt_insert->bind_param("sssssss", $name, $email, $hashed_password, $role, $status, $location, $doc_path);
                
                if ($stmt_insert->execute()) {
                    if ($role === 'seller') {
                        $success_msg = "Seller registration successful! Admin approval is pending review of your documents.";
                    } else {
                        $success_msg = "Registration successful! You can now log in.";
                    }
                    
                    // Reset fields
                    $name = '';
                    $email = '';
                    $role = 'customer';
                    $location = '';
                } else {
                    $error_msg = "System error during registration. Please try again later.";
                }
                $stmt_insert->close();
            }
        }
    }
}
?>

<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-9 col-sm-11">
            <div class="card-glass p-5 animate-fade-in">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-dark rounded-circle border border-secondary mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-person-plus-fill text-gradient-primary fs-2"></i>
                    </div>
                    <h2 class="text-white font-heading mb-1">Create Account</h2>
                    <p class="text-secondary small">Join Name-e-Shopping marketplace today</p>
                </div>
                
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger bg-danger-subtle border-danger text-danger-emphasis rounded-3 px-3 py-2 small mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error_msg); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success_msg)): ?>
                    <div class="alert alert-success bg-success-subtle border-success text-success-emphasis rounded-3 px-3 py-2 small mb-4">
                        <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success_msg); ?>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST" enctype="multipart/form-data" class="d-flex flex-column gap-3">
                    <!-- Role Switch Toggle -->
                    <div>
                        <label class="form-glass-label d-block">Account Profile Type</label>
                        <div class="d-flex gap-2">
                            <input type="radio" class="btn-check" name="role" id="role_customer" value="customer" <?php echo $role === 'customer' ? 'checked' : ''; ?> required>
                            <label class="btn btn-outline-success w-100 py-2" for="role_customer"><i class="bi bi-person me-1"></i> Customer</label>
                            
                            <input type="radio" class="btn-check" name="role" id="role_seller" value="seller" <?php echo $role === 'seller' ? 'checked' : ''; ?> required>
                            <label class="btn btn-outline-info w-100 py-2" for="role_seller"><i class="bi bi-shop me-1"></i> Seller</label>
                        </div>
                    </div>
                    
                    <!-- Standard Fields -->
                    <div>
                        <label class="form-glass-label" for="name">Full Name / Business Entity</label>
                        <input type="text" name="name" id="name" class="form-control form-glass-input" placeholder="e.g. John Doe" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>

                    <div>
                        <label class="form-glass-label" for="email">E-mail Address</label>
                        <input type="email" name="email" id="email" class="form-control form-glass-input" placeholder="e.g. email@domain.com" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>

                    <div>
                        <label class="form-glass-label" for="password">Create Security Password</label>
                        <input type="password" name="password" id="password" class="form-control form-glass-input" placeholder="Min 6 characters" required>
                    </div>

                    <!-- Seller Specific Fields (Conditional visibility via JS) -->
                    <div id="seller-fields" class="d-flex flex-column gap-3" style="display: none;">
                        <hr class="border-secondary my-2">
                        <h6 class="text-info font-heading"><i class="bi bi-patch-question-fill me-1"></i> Seller Compliance Setup</h6>
                        
                        <div>
                            <label class="form-glass-label" for="location">Operating Location / Coordinates</label>
                            <input type="text" name="location" id="location" class="form-control form-glass-input" placeholder="e.g. Kigali, Rwanda" value="<?php echo htmlspecialchars($location); ?>">
                        </div>
                        
                        <div>
                            <label class="form-glass-label" for="document">Compliance Upload (ID / Business Certificate)</label>
                            <input type="file" name="document" id="document" class="form-control form-glass-input">
                            <span class="text-secondary small d-block mt-1">Please upload single PDF, PNG or TXT verification files.</span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-premium py-3 mt-2">Initialize Registration <i class="bi bi-person-check-fill"></i></button>
                </form>
                
                <div class="text-center mt-4">
                    <p class="text-secondary small mb-0">Already registered? <a href="login.php" class="text-gradient-primary fw-bold text-decoration-none">Sign In Here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const roleCustomer = document.getElementById('role_customer');
    const roleSeller = document.getElementById('role_seller');
    const sellerFields = document.getElementById('seller-fields');
    const locationInput = document.getElementById('location');
    const documentInput = document.getElementById('document');

    function toggleSellerFields() {
        if (roleSeller.checked) {
            sellerFields.style.display = 'flex';
            locationInput.setAttribute('required', 'required');
            documentInput.setAttribute('required', 'required');
        } else {
            sellerFields.style.display = 'none';
            locationInput.removeAttribute('required');
            documentInput.removeAttribute('required');
        }
    }

    roleCustomer.addEventListener('change', toggleSellerFields);
    roleSeller.addEventListener('change', toggleSellerFields);
    
    // Init state
    toggleSellerFields();
});
</script>

<?php require_once 'includes/footer.php'; ?>
