<?php
// config/db.php

$host = 'localhost';
$user = 'root';
$pass = ''; // Default XAMPP password is empty

// 1. Initial connection to MySQL (without database selected)
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Create database if not exists
$db_name = 'neema_db';
$sql_db = "CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!$conn->query($sql_db)) {
    die("Error creating database: " . $conn->error);
}

// 3. Select database
$conn->select_db($db_name);

// 4. Create Tables if they do not exist

// Users Table
$table_users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'seller', 'customer') NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
    seller_location VARCHAR(255) DEFAULT NULL,
    seller_documents VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;";
$conn->query($table_users);

// Categories Table
$table_categories = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    icon VARCHAR(100) DEFAULT 'bi-box'
) ENGINE=InnoDB;";
$conn->query($table_categories);

// Products Table
$table_products = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    category_id INT NULL,
    image_url VARCHAR(255) NOT NULL,
    file_url VARCHAR(255) DEFAULT NULL,
    stock INT DEFAULT 10,
    is_digital TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;";
$conn->query($table_products);

// Orders Table
$table_orders = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    payment_method VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;";
$conn->query($table_orders);

// Order Items Table
$table_order_items = "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;";
$conn->query($table_order_items);

// Comments Table
$table_comments = "CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    customer_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;";
$conn->query($table_comments);

// 5. Seed initial categories if empty
$check_cats = $conn->query("SELECT COUNT(*) as count FROM categories");
$row_cats = $check_cats->fetch_assoc();
if ($row_cats['count'] == 0) {
    $categories = [
        ['Electronics', 'bi-laptop'],
        ['Fashion & Apparel', 'bi-sunglasses'],
        ['Digital Products', 'bi-cloud-download'],
        ['Home & Living', 'bi-house-heart'],
        ['Books & Media', 'bi-book']
    ];
    $stmt = $conn->prepare("INSERT INTO categories (name, icon) VALUES (?, ?)");
    foreach ($categories as $cat) {
        $stmt->bind_param("ss", $cat[0], $cat[1]);
        $stmt->execute();
    }
    $stmt->close();
}

// 6. Seed initial Admin if empty
$check_admin = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
$row_admin = $check_admin->fetch_assoc();
if ($row_admin['count'] == 0) {
    $admin_name = 'System Admin';
    $admin_email = 'admin@shopping.com';
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $admin_role = 'admin';
    $admin_status = 'approved';
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $admin_name, $admin_email, $admin_password, $admin_role, $admin_status);
    $stmt->execute();
    $stmt->close();
}

// 7. Seed initial Seller if empty
$check_seller = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'seller'");
$row_seller = $check_seller->fetch_assoc();
if ($row_seller['count'] == 0) {
    $seller_name = 'Apex Digital Store';
    $seller_email = 'seller@shopping.com';
    $seller_password = password_hash('seller123', PASSWORD_DEFAULT);
    $seller_role = 'seller';
    $seller_status = 'approved';
    $seller_location = 'Kigali, Rwanda';
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, status, seller_location) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $seller_name, $seller_email, $seller_password, $seller_role, $seller_status, $seller_location);
    $stmt->execute();
    $seller_id = $conn->insert_id;
    $stmt->close();
    
    // Seed standard products for the seller
    $check_products = $conn->query("SELECT COUNT(*) as count FROM products");
    $row_products = $check_products->fetch_assoc();
    if ($row_products['count'] == 0) {
        $products = [
            [
                'seller_id' => $seller_id,
                'name' => 'Premium Cyberpunk Laptop Stand',
                'description' => 'A heavy-duty aluminum laptop stand featuring built-in RGB lighting, dynamic angle adjustments, and ergonomic cable routing holes. The perfect additions for power-users.',
                'price' => 79.99,
                'category_name' => 'Electronics',
                'image_url' => 'assets/uploads/laptop_stand.svg',
                'file_url' => NULL,
                'stock' => 15,
                'is_digital' => 0
            ],
            [
                'seller_id' => $seller_id,
                'name' => 'Futuristic Neon Hoodie',
                'description' => 'High-comfort water-resistant cyber hoodie with luminous glowing neon stripes along the sleeves. Standard streetwear sizing with breathable cotton-mesh fabric.',
                'price' => 120.00,
                'category_name' => 'Fashion & Apparel',
                'image_url' => 'assets/uploads/hoodie.svg',
                'file_url' => NULL,
                'stock' => 20,
                'is_digital' => 0
            ],
            [
                'seller_id' => $seller_id,
                'name' => 'Complete UI Icon Kit (Digital)',
                'description' => 'A pack of 500+ premium dynamic UI vector icons for professional developers. Includes raw SVG, Figma, and fully layered EPS resource formats for immediate download.',
                'price' => 24.50,
                'category_name' => 'Digital Products',
                'image_url' => 'assets/uploads/icon_kit.svg',
                'file_url' => 'assets/uploads/icon_kit_v1.zip',
                'stock' => 999,
                'is_digital' => 1
            ],
            [
                'seller_id' => $seller_id,
                'name' => 'Smart Ambient Mood Lamp',
                'description' => 'Wifi-enabled sleek ambient mood sphere with responsive sound-active music modes. Fits on bedside tables or desktops for high-end office visual comfort.',
                'price' => 45.00,
                'category_name' => 'Home & Living',
                'image_url' => 'assets/uploads/mood_lamp.svg',
                'file_url' => NULL,
                'stock' => 8,
                'is_digital' => 0
            ]
        ];

        foreach ($products as $prod) {
            // Get category id
            $cat_res = $conn->query("SELECT id FROM categories WHERE name = '{$prod['category_name']}'");
            $cat_row = $cat_res->fetch_assoc();
            $cat_id = $cat_row['id'];
            
            $stmt = $conn->prepare("INSERT INTO products (seller_id, name, description, price, category_id, image_url, file_url, stock, is_digital) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issdisssi", $prod['seller_id'], $prod['name'], $prod['description'], $prod['price'], $cat_id, $prod['image_url'], $prod['file_url'], $prod['stock'], $prod['is_digital']);
            $stmt->execute();
            $prod_id = $conn->insert_id;
            $stmt->close();
            
            // Seed a comment for each product
            $comment_cust_id = 1; // Temporary ID (we'll seed a customer next and update or just add a user)
        }
    }
}

// 8. Seed initial Customer if empty
$check_cust = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
$row_cust = $check_cust->fetch_assoc();
if ($row_cust['count'] == 0) {
    $cust_name = 'John Doe';
    $cust_email = 'customer@shopping.com';
    $cust_password = password_hash('customer123', PASSWORD_DEFAULT);
    $cust_role = 'customer';
    $cust_status = 'approved';
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $cust_name, $cust_email, $cust_password, $cust_role, $cust_status);
    $stmt->execute();
    $cust_id = $conn->insert_id;
    $stmt->close();
    
    // Add reviews for the seeded products using this customer's account
    $prod_res = $conn->query("SELECT id FROM products");
    if ($prod_res->num_rows > 0) {
        $stmt_comm = $conn->prepare("INSERT INTO comments (product_id, customer_id, rating, comment) VALUES (?, ?, ?, ?)");
        while ($p_row = $prod_res->fetch_assoc()) {
            $rating = 5;
            $comment_text = "Absolutely stunning product! Exceeded my expectations. The quality is second to none.";
            $stmt_comm->bind_param("iiis", $p_row['id'], $cust_id, $rating, $comment_text);
            $stmt_comm->execute();
        }
        $stmt_comm->close();
    }
}
?>
