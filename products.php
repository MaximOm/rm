<?php
define('APP_INITIALIZED', true);
defined('BASE_PATH') or define('BASE_PATH', __DIR__);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/db.php';

// Get all categories from DB
$categories = [];
$stmt = $pdo->query('SELECT name FROM categories');
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get selected category
$selectedCategory = isset($_GET['block']) ? $_GET['block'] : null;

// Fetch slabs from DB with category join and main image
$slabs = [];
if ($selectedCategory) {
    $stmt = $pdo->prepare('SELECT p.*, c.name AS category_name, pi.image AS main_image FROM products p JOIN categories c ON p.category_id = c.id LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1 WHERE c.name = ?');
    $stmt->execute([$selectedCategory]);
    $slabs = $stmt->fetchAll();
} else {
    $stmt = $pdo->query('SELECT p.*, c.name AS category_name, pi.image AS main_image FROM products p JOIN categories c ON p.category_id = c.id LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1');
    $slabs = $stmt->fetchAll();
}

// Group slabs by category_name
$slabsByCategory = [];
foreach ($slabs as $slab) {
    $category = $slab['category_name'] ?? '';
    if ($category) {
        $slabsByCategory[$category][] = $slab;
    }
}

include '_header.php';
?>
<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-md-12">
            <h1 class="text-center mb-4">Marble Slabs</h1>
            <p class="text-center mb-5">Browse by category and view available slabs.</p>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-12 text-center">
            <?php foreach ($categories as $category): ?>
                <a href="/products?block=<?php echo urlencode($category); ?>" class="btn btn-outline-primary m-1<?php if ($selectedCategory === $category) echo ' active'; ?>">
                    <?php echo htmlspecialchars(ucfirst($category)); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php if ($selectedCategory && isset($slabsByCategory[$selectedCategory])): ?>
        <div class="row">
            <div class="col-12 mb-3">
                <h2 class="text-center">Slabs in <?php echo htmlspecialchars(ucfirst($selectedCategory)); ?></h2>
            </div>
            <?php foreach ($slabsByCategory[$selectedCategory] as $slab): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-sm">
                        <a href="/product?sku=<?php echo urlencode($slab['slug']); ?>" style="text-decoration:none;color:inherit;">
                            <div class="product-img-container" style="height: 200px; overflow: hidden;">
                                <img src="<?php echo htmlspecialchars($slab['main_image'] ?? ''); ?>" alt="<?php echo htmlspecialchars($slab['name']); ?>" class="card-img-top" style="object-fit: cover; height: 100%; width: 100%;">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($slab['name']); ?></h5>
                                <p class="card-text small">SKU: <?php echo htmlspecialchars($slab['slug']); ?></p>
                                <p class="card-text small">Category: <?php echo htmlspecialchars($slab['category_name']); ?></p>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($slabsByCategory[$selectedCategory])): ?>
                <div class="col-12 text-center">
                    <p>No slabs found in this category.</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="/products" class="btn btn-outline-secondary">Back to All Categories</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-12 text-center">
                <p>Select a category to view its slabs.</p>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php include '_footer.php'; ?>