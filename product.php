
<?php
session_start();
require_once 'includes/functions.php';

// Get product ID or slug from URL
$id_or_slug = isset($_GET['id']) ? sanitize($conn, $_GET['id']) : null;

if (empty($id_or_slug)) {
    // Redirect to products page if no ID provided
    redirect('products.php');
    exit;
}

// Get product details
$product = get_product($id_or_slug);

if (!$product) {
    // Product not found
    header("HTTP/1.0 404 Not Found");
    include('404.php');
    exit;
}

// Get related products
$related_products = get_related_products($product['id'], 4);

// Build breadcrumb path
$breadcrumbs = [
    ['title' => 'Home', 'url' => 'index.php'],
    ['title' => 'Products', 'url' => 'products.php']
];

// Add category path to breadcrumbs if available
if (!empty($product['categories'])) {
    $main_category = $product['categories'][0];
    $cat_path = get_category_path($main_category['id']);
    
    foreach ($cat_path as $cat) {
        $breadcrumbs[] = [
            'title' => $cat['name'],
            'url' => 'products.php?category=' . $cat['id']
        ];
    }
}

// Add product name to breadcrumbs
$breadcrumbs[] = ['title' => $product['name'], 'url' => ''];

// Get page meta
$page_title = $product['name'] . ' - ' . get_config('site_title');
$page_description = $product['short_description'];

// Convert unit type to display format
$unit_display = ($product['unit_type'] == 'square_foot') ? 'sq ft' : 'sq m';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_description; ?>">
    
    <!-- Open Graph tags for social sharing -->
    <meta property="og:title" content="<?php echo $product['name']; ?>">
    <meta property="og:description" content="<?php echo $product['short_description']; ?>">
    <?php if (!empty($product['images'][0]['url'])): ?>
    <meta property="og:image" content="<?php echo $product['images'][0]['url']; ?>">
    <?php endif; ?>
    <meta property="og:url" content="<?php echo get_config('base_url') . '/product.php?id=' . $product['slug']; ?>">
    <meta property="og:type" content="product">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Image Zoom CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/img/favicon.ico" type="image/x-icon">
</head>
<body>
    <!-- Header -->
    <?php include 'section/header.php'; ?>
    
    <!-- Breadcrumb -->
    <div class="container py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                    <?php if ($index == count($breadcrumbs) - 1): ?>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo $crumb['title']; ?></li>
                    <?php else: ?>
                        <li class="breadcrumb-item"><a href="<?php echo $crumb['url']; ?>"><?php echo $crumb['title']; ?></a></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
    </div>
    
    <!-- Product Detail Section -->
    <section class="product-detail py-5">
        <div class="container">
            <div class="row">
                <!-- Product Gallery -->
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div class="product-gallery">
                        <?php if (count($product['images']) > 0): ?>
                            <!-- Main Image with Zoom -->
                            <div class="main-image mb-3">
                                <a href="<?php echo $product['images'][0]['url']; ?>" data-lightbox="product-gallery" data-title="<?php echo $product['name']; ?>">
                                    <img id="main-product-image" src="<?php echo $product['images'][0]['url']; ?>" alt="<?php echo $product['name']; ?>" class="img-fluid rounded">
                                </a>
                            </div>
                            
                            <!-- Thumbnails -->
                            <?php if (count($product['images']) > 1): ?>
                                <div class="gallery-thumbs row g-2">
                                    <?php foreach ($product['images'] as $index => $image): ?>
                                        <div class="col-3">
                                            <div class="thumbnail<?php echo ($index === 0) ? ' active' : ''; ?>" 
                                                 data-image="<?php echo $image['url']; ?>"
                                                 data-index="<?php echo $index; ?>">
                                                <img src="<?php echo $image['url']; ?>" 
                                                     alt="<?php echo $product['name'] . ' - ' . ($index + 1); ?>" 
                                                     class="img-fluid rounded">
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="no-image-placeholder rounded d-flex align-items-center justify-content-center bg-light" style="height: 400px;">
                                <div class="text-center text-muted">
                                    <i class="fas fa-image fa-3x mb-3"></i>
                                    <p>No images available</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Product Information -->
                <div class="col-lg-6">
                    <div class="product-info">
                        <h1 class="product-title mb-2"><?php echo $product['name']; ?></h1>
                        
                        <div class="product-category mb-3">
                            <?php if (!empty($product['categories'])): ?>
                                <?php foreach($product['categories'] as $index => $category): ?>
                                    <a href="products.php?category=<?php echo $category['id']; ?>" class="badge bg-secondary text-decoration-none">
                                        <?php echo $category['name']; ?>
                                    </a>
                                    <?php echo ($index < count($product['categories']) - 1) ? ' ' : ''; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-origin mb-3">
                            <strong>Origin:</strong> <?php echo $product['origin_country']; ?>
                        </div>
                        
                        <div class="product-sku mb-3">
                            <strong>SKU:</strong> <?php echo $product['sku']; ?>
                        </div>
                        
                        <div class="product-price mb-4">
                            <h3>$<?php echo number_format($product['price_per_unit'], 2); ?> <small>per <?php echo $unit_display; ?></small></h3>
                        </div>
                        
                        <?php if (!empty($product['short_description'])): ?>
                            <div class="product-short-description mb-4">
                                <p><?php echo $product['short_description']; ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Add to Cart Form -->
                        <div class="add-to-cart-form mb-4">
                            <form action="cart-handler.php" method="POST" id="add-to-cart-form">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                
                                <div class="row g-3 align-items-center">
                                    <div class="col-sm-4">
                                        <div class="input-group">
                                            <span class="input-group-text">Quantity (<?php echo $unit_display; ?>)</span>
                                            <input type="number" name="quantity" id="quantity" class="form-control" 
                                                   min="1" step="0.5" value="1">
                                        </div>
                                    </div>
                                    <div class="col-sm-8">
                                        <div class="d-grid gap-2 d-sm-block">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                                            </button>
                                            <button type="button" class="btn btn-outline-primary btn-lg add-to-wishlist"
                                                    data-product-id="<?php echo $product['id']; ?>">
                                                <i class="far fa-heart"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Availability Status -->
                        <div class="product-availability mb-4">
                            <span class="badge <?php echo ($product['status'] == 'active') ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo ($product['status'] == 'active') ? 'In Stock' : 'Out of Stock'; ?>
                            </span>
                        </div>
                        
                        <!-- Contact/Inquiry Button -->
                        <div class="product-inquiry mb-4">
                            <a href="contacts.php?product=<?php echo $product['id']; ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-envelope me-2"></i> Inquire About This Product
                            </a>
                        </div>
                        
                        <!-- Social Sharing -->
                        <div class="social-sharing mb-4">
                            <p class="mb-2">Share this product:</p>
                            <a href="https://facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_config('base_url') . '/product.php?id=' . $product['slug']); ?>" 
                               target="_blank" class="btn btn-sm btn-outline-primary me-2">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_config('base_url') . '/product.php?id=' . $product['slug']); ?>&text=<?php echo urlencode($product['name']); ?>" 
                               target="_blank" class="btn btn-sm btn-outline-info me-2">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode(get_config('base_url') . '/product.php?id=' . $product['slug']); ?>&media=<?php echo urlencode(!empty($product['images'][0]['url']) ? $product['images'][0]['url'] : ''); ?>&description=<?php echo urlencode($product['short_description']); ?>" 
                               target="_blank" class="btn btn-sm btn-outline-danger me-2">
                                <i class="fab fa-pinterest"></i>
                            </a>
                            <a href="mailto:?subject=<?php echo urlencode($product['name']); ?>&body=<?php echo urlencode('Check out this product: ' . get_config('base_url') . '/product.php?id=' . $product['slug']); ?>" 
                               class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-envelope"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Product Details Tabs -->
            <div class="product-details-tabs mt-5">
                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="description-tab" data-bs-toggle="tab" 
                                data-bs-target="#description" type="button" role="tab" 
                                aria-controls="description" aria-selected="true">Description</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="specifications-tab" data-bs-toggle="tab" 
                                data-bs-target="#specifications" type="button" role="tab" 
                                aria-controls="specifications" aria-selected="false">Specifications</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="maintenance-tab" data-bs-toggle="tab" 
                                data-bs-target="#maintenance" type="button" role="tab" 
                                aria-controls="maintenance" aria-selected="false">Maintenance</button>
                    </li>
                </ul>
                
                <div class="tab-content p-4 border border-top-0 rounded-bottom" id="productTabsContent">
                    <!-- Description Tab -->
                    <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                        <?php if (!empty($product['description'])): ?>
                            <?php echo $product['description']; ?>
                        <?php else: ?>
                            <p class="text-muted">No detailed description available for this product.</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Specifications Tab -->
                    <div class="tab-pane fade" id="specifications" role="tabpanel" aria-labelledby="specifications-tab">
                        <?php if (!empty($product['specifications'])): ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <h4 class="h5 mb-3">Dimensions</h4>
                                    <table class="table table-bordered">
                                        <tbody>
                                            <?php if(!empty($product['specifications']['length'])): ?>
                                                <tr>
                                                    <th>Length</th>
                                                    <td><?php echo $product['specifications']['length']; ?> cm</td>
                                                </tr>
                                            <?php endif; ?>
                                            
                                            <?php if(!empty($product['specifications']['width'])): ?>
                                                <tr>
                                                    <th>Width</th>
                                                    <td><?php echo $product['specifications']['width']; ?> cm</td>
                                                </tr>
                                            <?php endif; ?>
                                            
                                            <?php if(!empty($product['specifications']['thickness'])): ?>
                                                <tr>
                                                    <th>Thickness</th>
                                                    <td><?php echo $product['specifications']['thickness']; ?> cm</td>
                                                </tr>
                                            <?php endif; ?>
                                            
                                            <?php if(!empty($product['specifications']['weight'])): ?>
                                                <tr>
                                                    <th>Weight</th>
                                                    <td><?php echo $product['specifications']['weight']; ?> kg</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="col-md-6">
                                    <h4 class="h5 mb-3">Technical Properties</h4>
                                    <table class="table table-bordered">
                                        <tbody>
                                            <?php if(!empty($product['specifications']['water_absorption'])): ?>
                                                <tr>
                                                    <th>Water Absorption</th>
                                                    <td><?php echo $product['specifications']['water_absorption']; ?>%</td>
                                                </tr>
                                            <?php endif; ?>
                                            
                                            <?php if(!empty($product['specifications']['density'])): ?>
                                                <tr>
                                                    <th>Density</th>
                                                    <td><?php echo $product['specifications']['density']; ?> g/cm³</td>
                                                </tr>
                                            <?php endif; ?>
                                            
                                            <?php 
                                            // Display technical details from JSON
                                            if(!empty($product['specifications']['technical_details']) && is_array($product['specifications']['technical_details'])):
                                                foreach($product['specifications']['technical_details'] as $key => $value):
                                                    if(is_array($value)):
                                                        continue; // Skip arrays for simple display
                                                    endif;
                                            ?>
                                                <tr>
                                                    <th><?php echo ucfirst(str_replace('_', ' ', $key)); ?></th>
                                                    <td><?php echo $value; ?></td>
                                                </tr>
                                            <?php 
                                                endforeach;
                                            endif; 
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Display applications if available -->
                                <?php 
                                if(!empty($product['specifications']['technical_details']['applications']) && 
                                   is_array($product['specifications']['technical_details']['applications'])): 
                                ?>
                                    <div class="col-12 mt-4">
                                        <h4 class="h5 mb-3">Recommended Applications</h4>
                                        <ul class="list-group list-group-horizontal-md flex-wrap">
                                            <?php foreach($product['specifications']['technical_details']['applications'] as $application): ?>
                                                <li class="list-group-item flex-fill text-center">
                                                    <i class="fas fa-check-circle text-success me-2"></i> 
                                                    <?php echo ucfirst($application); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Display finish options if available -->
                                <?php 
                                if(!empty($product['specifications']['technical_details']['finish_options']) && 
                                   is_array($product['specifications']['technical_details']['finish_options'])): 
                                ?>
                                    <div class="col-12 mt-4">
                                        <h4 class="h5 mb-3">Available Finishes</h4>
                                        <ul class="list-group list-group-horizontal-md flex-wrap">
                                            <?php foreach($product['specifications']['technical_details']['finish_options'] as $finish): ?>
                                                <li class="list-group-item flex-fill text-center">
                                                    <?php echo ucfirst($finish); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Specifications not available for this product.</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Maintenance Tab -->
                    <div class="tab-pane fade" id="maintenance" role="tabpanel" aria-labelledby="maintenance-tab">
                        <?php if (!empty($product['maintenance_info'])): ?>
                            <?php echo $product['maintenance_info']; ?>
                        <?php else: ?>
                            <div class="general-maintenance">
                                <h4 class="h5 mb-3">General Marble Maintenance Tips</h4>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">Clean regularly with a pH-neutral cleaner specifically designed for natural stone.</li>
                                    <li class="list-group-item">Avoid acidic cleaners (including vinegar) as they can etch marble surfaces.</li>
                                    <li class="list-group-item">Seal the marble every 6-12 months with a high-quality stone sealer.</li>
                                    <li class="list-group-item">Wipe up spills immediately, especially acidic substances like wine, coffee, or citrus juices.</li>
                                    <li class="list-group-item">Use coasters, trivets, and cutting boards to protect the surface from scratches and heat damage.</li>
                                    <li class="list-group-item">For stubborn stains, use a poultice specifically designed for marble.</li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Related Products -->
            <?php if (count($related_products) > 0): ?>
                <div class="related-products mt-5">
                    <h3 class="section-title h4 mb-4">Related Products</h3>
                    
                    <div class="row row-cols-2 row-cols-md-4 g-4">
                        <?php foreach ($related_products as $related): ?>
                            <div class="col">
                                <div class="product-card h-100">
                                    <div class="product-image">
                                        <a href="product.php?id=<?php echo $related['slug']; ?>">
                                            <img src="<?php echo $related['image_url']; ?>" 
                                                 alt="<?php echo $related['name']; ?>"
                                                 class="img-fluid">
                                        </a>
                                    </div>
                                    <div class="product-info p-3">
                                        <h3 class="product-title h6">
                                            <a href="product.php?id=<?php echo $related['slug']; ?>">
                                                <?php echo $related['name']; ?>
                                            </a>
                                        </h3>
                                        <div class="product-price">
                                            $<?php echo number_format($related['price_per_unit'], 2); ?>
                                            <small>per <?php echo ($related['unit_type'] == 'square_foot') ? 'sq ft' : 'sq m'; ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Footer -->
    <?php include 'section/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Image Zoom JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gallery thumbnail functionality
            const thumbnails = document.querySelectorAll('.gallery-thumbs .thumbnail');
            const mainImage = document.getElementById('main-product-image');
            
            if (thumbnails.length > 0 && mainImage) {
                thumbnails.forEach(thumbnail => {
                    thumbnail.addEventListener('click', function() {
                        // Update main image
                        mainImage.src = this.getAttribute('data-image');
                        
                        // Update lightbox href
                        mainImage.parentElement.href = this.getAttribute('data-image');
                        
                        // Update active state
                        thumbnails.forEach(t => t.classList.remove('active'));
                        this.classList.add('active');
                    });
                });
            }
            
            // Add to wishlist functionality
            const wishlistBtn = document.querySelector('.add-to-wishlist');
            if (wishlistBtn) {
                wishlistBtn.addEventListener('click', function() {
                    const productId = this.getAttribute('data-product-id');
                    
                    fetch('wishlist-handler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=add&product_id=${productId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Product added to wishlist!');
                            this.innerHTML = '<i class="fas fa-heart"></i>'; // Solid heart icon
                        } else if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            }
            
            // Form submission handling
            const addToCartForm = document.getElementById('add-to-cart-form');
            if (addToCartForm) {
                addToCartForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    
                    fetch('cart-handler.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Product added to cart!');
                            // Optionally update cart count
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            }
        });
    </script>
</body>
</html>
