<?php
// Set HTTP response code
http_response_code(404);

// Include required files
require_once 'includes/functions.php';

// Page title
$page_title = '404 - Page Not Found';
$page_description = 'The page you are looking for could not be found.';

// Include header
include 'section/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-6 mx-auto text-center">
            <div class="error-template">
                <h1 class="display-1 text-muted">404</h1>
                <h2>Page Not Found</h2>
                <div class="error-details mb-4">
                    Sorry, the page you requested could not be found.
                </div>
                <div class="error-actions">
                    <a href="index.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-home me-2"></i>Back to Homepage
                    </a>
                    <a href="products.php" class="btn btn-outline-secondary btn-lg ms-2">
                        <i class="fas fa-cube me-2"></i>View Products
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-light">
                    <h3 class="h5 mb-0">You might be looking for</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="h6">Popular Categories</h4>
                            <ul>
                                <?php
                                // Get some categories to display
                                $popular_cats = get_categories();
                                foreach(array_slice($popular_cats, 0, 5) as $cat) {
                                    echo '<li><a href="products.php?category=' . $cat['id'] . '">' . htmlspecialchars($cat['name']) . '</a></li>';
                                }
                                ?>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h4 class="h6">Important Pages</h4>
                            <ul>
                                <li><a href="index.php">Home</a></li>
                                <li><a href="products.php">All Products</a></li>
                                <li><a href="contacts.php">Contact Us</a></li>
                                <li><a href="about.php">About Us</a></li>
                                <li><a href="gallery.php">Gallery</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <form action="products.php" method="get" class="d-flex">
                        <input type="text" name="search" class="form-control" placeholder="Search products...">
                        <button type="submit" class="btn btn-primary ms-2">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'section/footer.php';
?>