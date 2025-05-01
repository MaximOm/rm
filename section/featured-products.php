<?php
// Get featured products (first 3 products)
function getFeaturedProducts($limit = 3) {
    $conn = getDbConnection();
    $sql = "SELECT p.*, c.title as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.id DESC LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    $stmt->close();
    $conn->close();
    
    return $products;
}

$featured_products = getFeaturedProducts(3);
?>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="display-4">Featured Products</h2>
                <p class="lead">Discover our selection of exquisite Riphean Marble products</p>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($featured_products as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow">
                    <div style="height: 200px; overflow: hidden;">
                        <?php if (!empty($product['main_image'])): ?>
                            <img src="<?php echo htmlspecialchars($product['main_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['title']); ?>" 
                                 class="card-img-top" style="object-fit: cover; height: 100%; width: 100%;">
                        <?php else: ?>
                            <div class="bg-secondary d-flex align-items-center justify-content-center text-white" style="height: 100%;">
                                <span>No image</span>
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
                    </div>
                    <div class="card-footer bg-white border-top-0">
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-block">View Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($featured_products)): ?>
                <div class="col-12 text-center">
                    <p>No products available yet. Please check back later.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="categories.php" class="btn btn-primary">View All Products</a>
            </div>
        </div>
    </div>
</section>