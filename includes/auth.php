<?php
// includes/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if the user is logged in.
 *
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current logged in user details.
 *
 * @return array|null
 */
function get_current_user_details() {
    if (is_logged_in()) {
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role'],
            'status' => $_SESSION['user_status'] ?? 'approved',
            'location' => $_SESSION['seller_location'] ?? null
        ];
    }
    return null;
}

/**
 * Restrict page access to specific roles.
 *
 * @param array $allowed_roles
 * @param string $redirect_to Page to redirect to on failure
 */
function restrict_to_roles($allowed_roles, $redirect_to = '../login.php') {
    if (!is_logged_in()) {
        $_SESSION['flash_message'] = "Please login to access this page.";
        $_SESSION['flash_type'] = "warning";
        header("Location: " . $redirect_to);
        exit;
    }
    
    $user = get_current_user_details();
    if (!in_array($user['role'], $allowed_roles)) {
        $_SESSION['flash_message'] = "Unauthorized access level.";
        $_SESSION['flash_type'] = "danger";
        
        // Redirect to their respective dashboards
        if ($user['role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } elseif ($user['role'] === 'seller') {
            header("Location: ../seller/dashboard.php");
        } else {
            header("Location: ../customer/dashboard.php");
        }
        exit;
    }
    
    // If seller is pending approval, restrict access to certain actions
    if ($user['role'] === 'seller' && $user['status'] !== 'approved') {
        // Allow dashboard view but add restriction banner
        $_SESSION['seller_pending_warning'] = true;
    }
}

/**
 * Add an item to the shopping cart.
 */
function cart_add($product_id, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

/**
 * Remove an item from the cart.
 */
function cart_remove($product_id) {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

/**
 * Get total number of items in the cart.
 */
function cart_count() {
    if (!isset($_SESSION['cart'])) {
        return 0;
    }
    return array_sum($_SESSION['cart']);
}

/**
 * Format currency.
 */
function format_price($price) {
    return number_format($price, 0) . ' FRW';
}
?>
