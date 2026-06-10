<?php
// login.php
require_once 'includes/header.php';

// Redirect if already logged in
if (is_logged_in()) {
    $user = get_current_user_details();
    if ($user['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } elseif ($user['role'] === 'seller') {
        header("Location: seller/dashboard.php");
    } else {
        header("Location: customer/dashboard.php");
    }
    exit;
}

$email = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error_msg = "Please fill in all input fields.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Set Sessions
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_status'] = $user['status'];
                $_SESSION['seller_location'] = $user['seller_location'];
                
                $_SESSION['flash_message'] = "Welcome back, {$user['name']}!";
                $_SESSION['flash_type'] = "success";
                
                // Route to appropriate directory
                if ($user['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } elseif ($user['role'] === 'seller') {
                    header("Location: seller/dashboard.php");
                } else {
                    header("Location: customer/dashboard.php");
                }
                exit;
            } else {
                $error_msg = "Invalid password. Please try again.";
            }
        } else {
            $error_msg = "No registered user found with that email.";
        }
        $stmt->close();
    }
}
?>

<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-8 col-sm-10">
            <!-- Glassmorphic Login Card -->
            <div class="card-glass p-5 animate-fade-in">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-dark rounded-circle border border-secondary mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-shield-lock-fill text-gradient-primary fs-2"></i>
                    </div>
                    <h2 class="text-white font-heading mb-1">Access Portal</h2>
                    <p class="text-secondary small">Secure credential gates for NEEMA</p>
                </div>
                
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger bg-danger-subtle border-danger text-danger-emphasis rounded-3 px-3 py-2 small mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error_msg); ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" class="d-flex flex-column gap-3">
                    <div>
                        <label class="form-glass-label" for="email">E-mail Address</label>
                        <input type="email" name="email" id="email" class="form-control form-glass-input" placeholder="name@domain.com" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-glass-label mb-0" for="password">Security Password</label>
                        </div>
                        <input type="password" name="password" id="password" class="form-control form-glass-input" placeholder="••••••••" required>
                    </div>
                    
                    <button type="submit" class="btn btn-premium py-3 mt-2">Verify & Login <i class="bi bi-box-arrow-in-right"></i></button>
                </form>
                
                <div class="text-center mt-4">
                    <p class="text-secondary small mb-0">Don't have an account? <a href="register.php" class="text-gradient-primary fw-bold text-decoration-none">Sign Up Here</a></p>
                </div>
                
                <!-- Quick developer access shortcuts -->
                <div class="mt-4 pt-4 border-top border-secondary">
                    <h6 class="text-white font-heading text-center mb-3">Quick Demo Portals (CAT 2 Testing)</h6>
                    <div class="d-flex flex-column gap-2">
                        <button type="button" class="btn btn-sm btn-outline-warning w-100" onclick="fillCreds('admin@shopping.com', 'admin123')">
                            <i class="bi bi-person-fill-gear me-1"></i> Admin Portal (admin@shopping.com / admin123)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info w-100" onclick="fillCreds('seller@shopping.com', 'seller123')">
                            <i class="bi bi-shop me-1"></i> Seller Portal (seller@shopping.com / seller123)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success w-100" onclick="fillCreds('customer@shopping.com', 'customer123')">
                            <i class="bi bi-person me-1"></i> Customer Portal (customer@shopping.com / customer123)
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function fillCreds(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
}
</script>

<?php require_once 'includes/footer.php'; ?>
