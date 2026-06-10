<?php
// shop.php
require_once 'includes/header.php';

// Get current query filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$cat_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$loc_filter = isset($_GET['location']) ? trim($_GET['location']) : '';
$type_filter = isset($_GET['type']) ? trim($_GET['type']) : '';

// Build Query
$query = "SELECT p.*, c.name as category_name, s.name as seller_name, s.seller_location 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          LEFT JOIN users s ON p.seller_id = s.id 
          WHERE 1=1";

$params = [];
$types = '';

if ($search !== '') {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $search_term = "%$search%";
    $params[] = &$search_term;
    $params[] = &$search_term;
    $types .= 'ss';
}

if ($cat_filter > 0) {
    $query .= " AND p.category_id = ?";
    $params[] = &$cat_filter;
    $types .= 'i';
}

if ($loc_filter !== '') {
    $query .= " AND s.seller_location = ?";
    $params[] = &$loc_filter;
    $types .= 's';
}

if ($type_filter !== '') {
    if ($type_filter === 'digital') {
        $query .= " AND p.is_digital = 1";
    } elseif ($type_filter === 'physical') {
        $query .= " AND p.is_digital = 0";
    }
}

$query .= " ORDER BY p.id DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$prod_res = $stmt->get_result();

// Get unique locations for the filter dropdown
$loc_res = $conn->query("SELECT DISTINCT seller_location FROM users WHERE role = 'seller' AND seller_location IS NOT NULL AND seller_location != ''");

// Get all categories
$all_cats = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>

<div class="container py-5 mt-4">
    <!-- Horizontal Filter Bar -->
    <div class="card-glass p-4 mb-5">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-4">
            <div>
                <h3 class="text-white mb-1"><i class="bi bi-shop me-2 text-gradient-primary"></i>Shop Catalogue</h3>
                <p class="text-secondary small mb-0">Showing <?php echo $prod_res->num_rows; ?> matches.</p>
            </div>
            
            <form action="shop.php" method="GET" class="d-flex flex-wrap align-items-end gap-3 flex-grow-1 justify-content-lg-end">
                <!-- Search Keyword -->
                <div style="min-width: 250px; flex: 1;">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control form-glass-input" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-premium px-3"><i class="bi bi-search"></i></button>
                    </div>
                </div>
                
                <!-- Category Selector -->
                <div style="min-width: 150px;">
                    <select name="category" class="form-select form-glass-input" onchange="this.form.submit()">
                        <option value="0">All Categories</option>
                        <?php if ($all_cats && $all_cats->num_rows > 0): ?>
                            <?php while ($cat = $all_cats->fetch_assoc()): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $cat_filter == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Location Selector -->
                <div style="min-width: 150px;">
                    <select name="location" class="form-select form-glass-input" onchange="this.form.submit()">
                        <option value="">All Locations</option>
                        <?php if ($loc_res && $loc_res->num_rows > 0): ?>
                            <?php while ($loc = $loc_res->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($loc['seller_location']); ?>" <?php echo $loc_filter == $loc['seller_location'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($loc['seller_location']); ?></option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Digital / Physical Selector -->
                <div style="min-width: 150px;">
                    <select name="type" class="form-select form-glass-input" onchange="this.form.submit()">
                        <option value="" <?php echo $type_filter == '' ? 'selected' : ''; ?>>All Types</option>
                        <option value="digital" <?php echo $type_filter == 'digital' ? 'selected' : ''; ?>>Digital</option>
                        <option value="physical" <?php echo $type_filter == 'physical' ? 'selected' : ''; ?>>Physical</option>
                    </select>
                </div>

                <?php if ($search !== '' || $cat_filter > 0 || $loc_filter !== '' || $type_filter !== ''): ?>
                    <a href="shop.php" class="btn btn-premium-secondary" style="height: 46px; display: flex; align-items: center;" title="Reset Filters"><i class="bi bi-x-circle"></i></a>
                <?php endif; ?>
            </form>
        </div>
    </div>
            
            <div class="row g-4">
                <?php if ($prod_res && $prod_res->num_rows > 0): ?>
                    <?php while ($prod = $prod_res->fetch_assoc()): ?>
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <div class="card-glass d-flex flex-column h-100">
                                <!-- Image Container -->
                                <div class="position-relative overflow-hidden">
                                    <img src="<?php echo htmlspecialchars($prod['image_url']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" class="card-glass-img w-100">
                                    <span class="position-absolute top-3 end-3 badge <?php echo $prod['is_digital'] ? 'bg-info' : 'bg-primary'; ?>" style="top: 15px; right: 15px;">
                                        <?php echo $prod['is_digital'] ? 'Digital' : 'Physical'; ?>
                                    </span>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge-premium small py-1 px-2"><?php echo htmlspecialchars($prod['category_name'] ?? 'Other'); ?></span>
                                        <span class="star-rating"><i class="bi bi-star-fill"></i> 5.0</span>
                                    </div>
                                    <h5 class="card-title text-white font-heading mt-2 text-truncate" title="<?php echo htmlspecialchars($prod['name']); ?>"><?php echo htmlspecialchars($prod['name']); ?></h5>
                                    
                                    <p class="text-secondary small mb-4 flex-grow-1 text-truncate-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?php echo htmlspecialchars($prod['description']); ?>
                                    </p>
                                    
                                    <!-- Seller details -->
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-shop text-info me-2"></i>
                                        <span class="text-secondary small me-3"><?php echo htmlspecialchars($prod['seller_name']); ?></span>
                                        <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                                        <span class="text-secondary small"><?php echo htmlspecialchars($prod['seller_location'] ?? 'Global'); ?></span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-auto border-top border-secondary pt-3">
                                        <span class="fs-4 fw-bold text-white"><?php echo format_price($prod['price']); ?></span>
                                        <a href="product-details.php?id=<?php echo $prod['id']; ?>" class="btn btn-premium btn-sm px-3">
                                            Buy <i class="bi bi-cart-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <div class="card-glass p-5">
                            <i class="bi bi-search-heart text-secondary" style="font-size: 3rem;"></i>
                            <h4 class="text-white mt-3">No Products Found</h4>
                            <p class="text-secondary">Try refining your filter queries or keywords in the sidebar.</p>
                            <a href="shop.php" class="btn btn-premium btn-sm mt-3">Reset All Filters</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
</div>

<?php require_once 'includes/footer.php'; ?>
