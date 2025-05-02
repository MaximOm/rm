<?php
define('APP_INITIALIZED', true);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/db.php';

// Get SKU from query
$sku = isset($_GET['sku']) ? $_GET['sku'] : null;
if (!$sku) {
    header('Location: products.php');
    exit;
}

// Fetch the slab by slug from DB, joining category name and main image
$stmt = $pdo->prepare('SELECT p.*, c.name AS category_name, pi.image AS main_image FROM products p JOIN categories c ON p.category_id = c.id LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1 WHERE p.slug = ? LIMIT 1');
$stmt->execute([$sku]);
$slab = $stmt->fetch();

if (!$slab) {
    include '404.php';
    exit;
}

include '_header.php';
?>
<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-md-6">
            <div class="product-img-container mb-3" style="max-height:400px; overflow:hidden;">
                <img src="<?php echo htmlspecialchars($slab['main_image']); ?>" alt="<?php echo htmlspecialchars($slab['name']); ?>" class="img-fluid rounded shadow" style="object-fit:cover; width:100%; max-height:400px;">
            </div>
        </div>
        <div class="col-md-6">
            <h1 class="mb-3"><?php echo htmlspecialchars($slab['name']); ?></h1>
            <p><strong>SKU:</strong> <?php echo htmlspecialchars($slab['slug']); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($slab['category_name']); ?></p>
            <?php if (!empty($slab['block_number'])): ?>
                <p><strong>Block Number:</strong> <?php echo htmlspecialchars($slab['block_number']); ?></p>
            <?php endif; ?>
            <?php if (!empty($slab['description'])): ?>
                <p><?php echo htmlspecialchars($slab['description']); ?></p>
            <?php endif; ?>
            <a href="/products?block=<?php echo urlencode($slab['category_name']); ?>" class="btn btn-outline-secondary mt-3">Back to Category</a>
        </div>
    </div>
</div>
<?php include '_footer.php'; ?>
