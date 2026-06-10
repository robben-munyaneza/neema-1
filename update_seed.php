<?php
require_once __DIR__ . '/config/db.php';

// Empty products
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("TRUNCATE TABLE products");
$conn->query("TRUNCATE TABLE categories");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// Insert Categories
$categories = [
    ['Food Products', 'bi-cup-hot'],
    ['Fashion & Apparel', 'bi-sunglasses'],
    ['Electronics', 'bi-laptop'],
    ['Books & Media', 'bi-book']
];
$stmt = $conn->prepare("INSERT INTO categories (name, icon) VALUES (?, ?)");
foreach ($categories as $cat) {
    $stmt->bind_param("ss", $cat[0], $cat[1]);
    $stmt->execute();
}
$stmt->close();

$seller_res = $conn->query("SELECT id FROM users WHERE role = 'seller' LIMIT 1");
if ($seller_res->num_rows > 0) {
    $seller_id = $seller_res->fetch_assoc()['id'];
} else {
    $conn->query("INSERT INTO users (name, email, password, role, status, seller_location) VALUES ('Apex Digital Store', 'seller@shopping.com', 'pwd', 'seller', 'approved', 'Kigali, Rwanda')");
    $seller_id = $conn->insert_id;
}

$products = [
    [
        'seller_id' => $seller_id,
        'name' => 'Premium Rwandan Roasted Coffee',
        'description' => 'Rich, aromatic 100% Arabica beans sourced directly from the high-altitude volcanic hills of Rwanda. Carefully roasted to perfection for an exquisite, full-bodied morning cup.',
        'price' => 15000,
        'category_name' => 'Food Products',
        'image_url' => 'assets/uploads/food.png',
        'file_url' => NULL,
        'stock' => 50,
        'is_digital' => 0
    ],
    [
        'seller_id' => $seller_id,
        'name' => 'Minimalist Cyberpunk Jacket',
        'description' => 'A futuristic, sleek dark-wear jacket designed for the modern urban environment. Features water-resistant fabric, subtle structural lines, and a tailored professional fit.',
        'price' => 45000,
        'category_name' => 'Fashion & Apparel',
        'image_url' => 'assets/uploads/fashion.png',
        'file_url' => NULL,
        'stock' => 20,
        'is_digital' => 0
    ],
    [
        'seller_id' => $seller_id,
        'name' => 'NEEMA Stealth Smart Watch',
        'description' => 'An ultra-modern minimalist smart watch boasting an edge-to-edge dark AMOLED display. Features continuous health monitoring, seamless notifications, and a matte black aerospace-grade titanium body.',
        'price' => 85000,
        'category_name' => 'Electronics',
        'image_url' => 'assets/uploads/electronics.png',
        'file_url' => NULL,
        'stock' => 15,
        'is_digital' => 0
    ],
    [
        'seller_id' => $seller_id,
        'name' => 'The History of Rwanda (Hardcover)',
        'description' => 'A comprehensive, beautifully bound hardcover edition detailing the rich, resilient history and vibrant culture of Rwanda. Perfect for collectors, historians, and avid readers.',
        'price' => 25000,
        'category_name' => 'Books & Media',
        'image_url' => 'assets/uploads/books.png',
        'file_url' => NULL,
        'stock' => 30,
        'is_digital' => 0
    ]
];

foreach ($products as $prod) {
    $cat_res = $conn->query("SELECT id FROM categories WHERE name = '{$prod['category_name']}'");
    $cat_id = $cat_res->fetch_assoc()['id'];
    
    $stmt = $conn->prepare("INSERT INTO products (seller_id, name, description, price, category_id, image_url, file_url, stock, is_digital) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdisssi", $prod['seller_id'], $prod['name'], $prod['description'], $prod['price'], $cat_id, $prod['image_url'], $prod['file_url'], $prod['stock'], $prod['is_digital']);
    $stmt->execute();
    $stmt->close();
}

echo "Database seeded with new products!";
?>
