<?php
// contact.php
require_once 'includes/header.php';

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = true;
    $_SESSION['flash_message'] = "Your support request has been submitted successfully! We will contact you soon.";
    $_SESSION['flash_type'] = "success";
}
?>

<div class="container py-5 mt-4">
    <div class="text-center mb-5 animate-fade-in">
        <span class="text-gradient-primary fw-bold text-uppercase small" style="letter-spacing: 2px;">Support Desk</span>
        <h1 class="text-white font-heading mt-2">Get In Touch</h1>
        <p class="text-secondary col-lg-6 mx-auto mt-3">Need assistance with your seller compliance, shipping, or digital downloads? Drop us a secure message and our team will get right back to you.</p>
    </div>
    
    <div class="row gy-5">
        <!-- Details Column -->
        <div class="col-lg-5">
            <div class="card-glass p-5 h-100 d-flex flex-column justify-content-between">
                <div>
                    <h4 class="text-white mb-4 font-heading"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Corporate Headquarters</h4>
                    <p class="text-secondary small mb-4">Name-e-Shopping Tech City Plaza, Tower B, Kigali, Rwanda.</p>
                    
                    <h5 class="text-white mb-3 font-heading"><i class="bi bi-envelope-open-fill text-info me-2"></i>E-mail Queries</h5>
                    <p class="text-secondary small mb-4">support@name-e-shopping.com<br>info@name-e-shopping.com</p>

                    <h5 class="text-white mb-3 font-heading"><i class="bi bi-telephone-inbound-fill text-success me-2"></i>Direct Hotline</h5>
                    <p class="text-secondary small mb-0">+250 788 123 456<br>+250 722 987 654</p>
                </div>
                
                <div class="pt-4 border-top border-secondary mt-4">
                    <span class="text-secondary small">Supervised by Name-e-Shopping Administrative Registry.</span>
                </div>
            </div>
        </div>
        
        <!-- Form Column -->
        <div class="col-lg-7">
            <div class="card-glass p-5">
                <h4 class="text-white mb-4 font-heading"><i class="bi bi-chat-text-fill text-gradient-primary me-2"></i>Send Message</h4>
                
                <?php if ($success): ?>
                    <div class="alert alert-success bg-success-subtle border-success text-success-emphasis rounded-3 px-3 py-2 small mb-4">
                        <i class="bi bi-check-circle-fill me-2"></i>Message submitted! We will respond within 24 hours.
                    </div>
                <?php endif; ?>
                
                <form action="contact.php" method="POST" class="d-flex flex-column gap-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-glass-label" for="c_name">Your Name</label>
                            <input type="text" name="name" id="c_name" class="form-control form-glass-input" placeholder="e.g. Jane" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-glass-label" for="c_email">Your Email</label>
                            <input type="email" name="email" id="c_email" class="form-control form-glass-input" placeholder="name@domain.com" required>
                        </div>
                    </div>
                    
                    <div>
                        <label class="form-glass-label" for="c_subject">Subject Topic</label>
                        <input type="text" name="subject" id="c_subject" class="form-control form-glass-input" placeholder="e.g. Question about Seller Account Setup" required>
                    </div>

                    <div>
                        <label class="form-glass-label" for="c_message">Message Details</label>
                        <textarea name="message" id="c_message" rows="5" class="form-control form-glass-input" placeholder="Type details here..." required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-premium w-100 py-3 mt-2">Submit Support Request <i class="bi bi-send-fill"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
