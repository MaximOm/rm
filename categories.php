<?php
// Define APP_INITIALIZED to access config
define('APP_INITIALIZED', true);

// Include initialization file
require_once 'includes/init.php';

// Get all categories
function getAllCategories() {
    $conn = getDbConnection();
    if (!$conn) {
        return [];
    }
    
    $sql = "SELECT * FROM categories ORDER BY title";
    $result = $conn->query($sql);
    
    $categories = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    
    $conn->close();
    return $categories;
}

$categories = getAllCategories();

// Include header
include '_header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-md-12">
            <h1 class="text-center mb-5">Our Marble Categories</h1>
            <p class="text-center mb-5">Explore our extensive selection of exquisite Riphean Marble products, categorized for your convenience.</p>
        </div>
    </div>
    
    <div class="row">
        <?php foreach ($categories as $category): ?>
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="category-img-container" style="height: 250px; overflow: hidden;">
                    <?php if (!empty($category['image'])): ?>
                        <img src="<?php echo htmlspecialchars($category['image']); ?>" alt="<?php echo htmlspecialchars($category['title']); ?>" class="card-img-top" style="object-fit: cover; height: 100%; width: 100%;">
                    <?php else: ?>
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 100%;">
                            <span class="text-muted">No image available</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($category['title']); ?></h5>
                    <p class="card-text"><?php echo htmlspecialchars($category['description']); ?></p>
                    <a href="/products?category=<?php echo $category['id']; ?>" class="btn btn-primary">View Products</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($categories)): ?>
        <div class="col-12 text-center">
            <p>No categories found. Please check back later.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '_footer.php'; ?>
