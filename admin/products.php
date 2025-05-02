<?php
define('APP_INITIALIZED', true);
require_once __DIR__ . '/admin_init.php';
require_once __DIR__ . '/admin_preferences.php';
require_once __DIR__ . '/admin_messages.php';

// Initialize preferences
$prefs = AdminPreferences::getInstance($pdo, $_SESSION['admin_id'] ?? 1);

// Handle preference updates
if (isset($_GET['view'])) {
    $prefs->setViewMode($_GET['view']);
}
if (isset($_GET['per_page'])) {
    $prefs->setItemsPerPage((int)$_GET['per_page']);
}

// Get current preferences
$viewMode = $prefs->getViewMode();
$itemsPerPage = $prefs->getItemsPerPage(12);
$page = max(1, $_GET['page'] ?? 1);
$offset = ($page - 1) * $itemsPerPage;

// Get total products count for pagination
$totalStmt = $pdo->query("SELECT COUNT(*) FROM products");
$totalProducts = $totalStmt->fetchColumn();
$totalPages = ceil($totalProducts / $itemsPerPage);

// Fetch categories for filter
$catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected category from GET
$selectedCategory = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Build query based on filters
if ($selectedCategory) {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, pi.image_path as primary_image 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1 
                          WHERE p.category_id = ? 
                          ORDER BY p.created_at DESC 
                          LIMIT ? OFFSET ?");
    $stmt->execute([$selectedCategory, $itemsPerPage, $offset]);
} else {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, pi.image_path as primary_image 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1 
                          ORDER BY p.created_at DESC 
                          LIMIT ? OFFSET ?");
    $stmt->execute([$itemsPerPage, $offset]);
}
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Store current page as last visited
$prefs->setLastVisitedPage($_SERVER['REQUEST_URI']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Products</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f8; margin: 0; }
        .container { max-width: 1200px; margin: 40px auto; }
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .toolbar { display: flex; gap: 20px; align-items: center; margin-bottom: 20px; }
        .view-controls { display: flex; gap: 10px; align-items: center; }
        .view-btn { padding: 8px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; background: white; }
        .view-btn.active { background: #0078d4; color: white; border-color: #0078d4; }
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .products-list { display: flex; flex-direction: column; gap: 10px; }
        .product-card { background: white; border-radius: 8px; overflow: hidden; transition: all 0.2s; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .product-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .product-grid-item { border: 1px solid #e0e4ea; }
        .product-list-item { display: flex; padding: 15px; align-items: center; }
        .product-image { height: 200px; background: #f5f5f5; position: relative; }
        .product-list-item .product-image { width: 100px; height: 100px; margin-right: 20px; }
        .product-image img { width: 100%; height: 100%; object-fit: cover; }
        .product-info { padding: 15px; }
        .product-list-item .product-info { flex: 1; padding: 0; }
        .product-name { font-size: 1.1rem; margin: 0 0 10px; font-weight: 600; }
        .product-meta { font-size: 0.9rem; color: #666; margin-bottom: 5px; }
        .product-actions { padding: 15px; border-top: 1px solid #eee; display: flex; gap: 10px; }
        .product-list-item .product-actions { padding: 0; border: none; margin-left: auto; }
        .btn { padding: 8px 16px; border-radius: 4px; text-decoration: none; font-weight: 500; }
        .btn-primary { background: #0078d4; color: white; }
        .btn-secondary { background: #f0f0f0; color: #333; }
        .btn-danger { background: #d32f2f; color: white; }
        .pagination { display: flex; justify-content: center; gap: 10px; margin-top: 30px; }
        .page-link { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #333; }
        .page-link.active { background: #0078d4; color: white; border-color: #0078d4; }
        .filter-bar { margin-bottom: 20px; padding: 15px; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        select { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        @media (max-width: 768px) {
            .container { padding: 16px; }
            .toolbar { flex-direction: column; align-items: stretch; }
            .view-controls { justify-content: center; }
            .products-grid { grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); }
            .product-list-item { flex-direction: column; }
            .product-list-item .product-image { width: 100%; margin-right: 0; margin-bottom: 15px; }
            .product-list-item .product-actions { margin-top: 15px; }
        }
    </style>
</head>
<body>
<?php include 'admin_nav.php'; ?>
<?php echo getMessageStyles(); ?>

<div class="container">
    <div class="header-actions">
        <h1>Products</h1>
        <a href="product_edit.php" class="btn btn-primary">Add Product</a>
    </div>

    <?php echo displayMessages(); ?>

    <div class="toolbar">
        <div class="filter-bar"></div>
            <form method="get" style="display:flex; gap:20px; align-items:center;">
                <div>
                    <label for="category">Category:</label>
                    <select name="category" id="category" onchange="this.form.submit()"></select>
                        <option value="0">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $selectedCategory == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div></div>
                    <label for="per_page">Items per page:</label>
                    <select name="per_page" id="per_page" onchange="this.form.submit()"></select>
                        <?php foreach ([12, 24, 48, 96] as $count): ?>
                            <option value="<?= $count ?>" <?= $itemsPerPage == $count ? 'selected' : '' ?>>
                                <?= $count ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
        
        <div class="view-controls"></div>
            <a href="?view=grid<?= $selectedCategory ? '&category='.$selectedCategory : '' ?>" 
               class="view-btn <?= $viewMode === 'grid' ? 'active' : '' ?>" 
               title="Grid View">
                📱 Grid
            </a>
            <a href="?view=list<?= $selectedCategory ? '&category='.$selectedCategory : '' ?>" 
               class="view-btn <?= $viewMode === 'list' ? 'active' : '' ?>" 
               title="List View">
                📋 List
            </a>
        </div>
    </div>

    <div class="<?= $viewMode === 'grid' ? 'products-grid' : 'products-list' ?>">
        <?php foreach ($products as $product): ?>
            <div class="product-card <?= $viewMode === 'grid' ? 'product-grid-item' : 'product-list-item' ?>"></div>
                <div class="product-image">
                    <?php if ($product['primary_image']): ?>
                        <img src="../<?= htmlspecialchars($product['primary_image']) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>">
                    <?php else: ?>
                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#666;"></div>
                            No Image
                        </div>
                    <?php endif; ?>
                </div>
                <div class="product-info"></div>
                    <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                    <div class="product-meta"></div>
                        <div>SKU: <?= htmlspecialchars($product['sku']) ?></div>
                        <div>Category: <?= htmlspecialchars($product['category_name']) ?></div>
                        <div>Stock: <?= htmlspecialchars($product['stock']) ?></div>
                    </div>
                </div>
                <div class="product-actions"></div>
                    <a href="product_edit.php?id=<?= $product['id'] ?>" class="btn btn-secondary">Edit</a>
                    <a href="product_delete.php?id=<?= $product['id'] ?>" 
                       class="btn btn-danger" 
                       onclick="return confirm('Are you sure you want to delete this product?')">
                        Delete
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($products)): ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                <p>No products found. Click "Add Product" to create one.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination"></div>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?><?= $selectedCategory ? '&category='.$selectedCategory : '' ?>" 
                   class="page-link <?= $page === $i ? 'active' : '' ?>"></a>
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
