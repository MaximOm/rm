<?php
// Define APP_INITIALIZED to access config
define('APP_INITIALIZED', true);

// Include initialization file
require_once 'includes/init.php';

// Get products by category
function getProductsByCategory($category_id = null) {
    $conn = getDbConnection();
    if (!$conn) {
        return [];
    }
    
    $sql = "SELECT p.*, c.title as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id";
    
    if ($category_id) {
        $stmt = $conn->prepare($sql . " WHERE p.category_id = ? ORDER BY p.title");
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql . " ORDER BY p.title");
    }
    
    $products = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    $conn->close();
    return $products;
}

// Get category info
function getCategoryInfo($category_id) {
    $conn = getDbConnection();
    if (!$conn) {
        return null;
    }
    
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    return $category;
}

// Get category parameter
$category_id = isset($_GET['category']) && is_numeric($_GET['category']) ? $_GET['category'] : null;
$category = $category_id ? getCategoryInfo($category_id) : null;
$products = getProductsByCategory($category_id);

// Include header
include '_header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-md-12">
            <?php if ($category): ?>
                <h1 class="text-center mb-4"><?php echo htmlspecialchars($category['title']); ?></h1>
                <?php if (!empty($category['description'])): ?>
                    <p class="text-center mb-5"><?php echo htmlspecialchars($category['description']); ?></p>
                <?php endif; ?>
            <?php else: ?>
                <h1 class="text-center mb-5">All Products</h1>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="row">
        <?php foreach ($products as $product): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="product-img-container" style="height: 250px; overflow: hidden;">
                    <?php if (!empty($product['main_image'])): ?>
                        <img src="<?php echo htmlspecialchars($product['main_image']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" class="card-img-top" style="object-fit: cover; height: 100%; width: 100%;">
                    <?php else: ?>
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 100%;">
                            <span class="text-muted">No image available</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                    <p class="card-text">
                        <?php 
                        if (!empty($product['short_description'])) {
                            echo htmlspecialchars($product['short_description']);
                        } else {
                            echo substr(htmlspecialchars($product['description']), 0, 100) . '...';
                        }
                        ?>
                    </p>
                    <p class="text-muted small">Category: <?php echo htmlspecialchars($product['category_name']); ?></p>
                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($products)): ?>
        <div class="col-12 text-center">
            <p>No products found in this category. Please check back later.</p>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($category_id): ?>
    <div class="row mt-4">
        <div class="col-12 text-center">
            <a href="categories.php" class="btn btn-outline-secondary">Back to Categories</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include '_footer.php'; ?>
