<?php
session_start();

//  log all error on screen
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

define('BASE_PATH', __DIR__);
require_once 'includes/functions.php';

// Get featured/latest products for home page
$featured_products = get_products(['sort' => 'created_at', 'dir' => 'DESC'], 1, 8);

// Get main categories for display
$main_categories = get_categories();

$page_name = 'home';

$header = [
    'title' => 'Riphean Marble - Premium Natural Stone',
    'keywords' => 'marble, natural stone, premium marble, marble slabs, luxury stone',
    'meta-title' => 'Riphean Marble - Exquisite Natural Stone Collection',
    'description' => 'Discover our premium collection of natural marble and stone sourced from the finest quarries around the world.'
];
include '_header.php';

include '_index_header_body.php' ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-slider">
        <div class="hero-slide" style="background-image: url('images/slide/hero-1.jpg')">
            <div class="container">
                <div class="hero-content">
                    <h1>Exquisite Marble Slabs</h1>
                    <p>Discover the beauty of natural stone for your spaces</p>
                    <div class="d-flex gap-3">
                        <a href="/products" class="btn btn-primary btn-lg">View Our Catalog</a>
                        <a href="/categories" class="btn btn-outline-light">Browse Categories</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include 'index_on_scroll.php';

// Category Showcase
?>
<section class="categories-section py-5" data-aos="fade-up">
    <div class="container">
        <div class="section-title text-center mb-5">
            <h2 class="golden-text-bgr">Browse By Category</h2>
            <p>Explore our extensive collection of premium marble slabs</p>
        </div>
        
        <div class="row g-4">
            <?php foreach($main_categories as $category): ?>
            <div class="col-md-4">
                <a href="/categories?id=<?php echo $category['id']; ?>" class="category-card">
                    <div class="card h-100">
                        <?php if(!empty($category['image_path'])): ?>
                        <img src="<?php echo get_config('upload_url') . $category['image_path']; ?>" 
                             class="card-img-top" 
                             alt="<?php echo $category['name']; ?>">
                        <?php else: ?>
                        <div class="card-img-placeholder"></div>
                        <?php endif; ?>
                        <div class="card-body text-center">
                            <h3 class="card-title h5"><?php echo $category['name']; ?></h3>
                            <?php if(!empty($category['description'])): ?>
                            <p class="card-text small"><?php echo $category['description']; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
include 'section/presentation.php';
?>

<!-- Featured Products -->
<section class="featured-products py-5 bg-light" data-aos="fade-up">
    <div class="container">
        <div class="section-title text-center mb-5">
            <h2 class="golden-text-bgr">Featured Marble Slabs</h2>
            <p>Our selection of premium natural stone</p>
        </div>
        
        <div class="row g-4">
            <?php foreach($featured_products as $product): ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="product-card">
                    <div class="product-image">
                        <a href="/product?id=<?php echo $product['slug']; ?>">
                            <img src="<?php echo $product['image_url']; ?>" 
                                 alt="<?php echo $product['name']; ?>"
                                 class="img-fluid">
                        </a>
                    </div>
                    <div class="product-info p-3">
                        <h3 class="product-title h6">
                            <a href="/product?id=<?php echo $product['slug']; ?>">
                                <?php echo $product['name']; ?>
                            </a>
                        </h3>
                        <div class="product-origin small text-muted mb-2">
                            Origin: <?php echo $product['origin_country']; ?>
                        </div>
                        <div class="product-price">
                            $<?php echo number_format($product['price_per_unit'], 2); ?>
                            <small>per <?php echo ($product['unit_type'] == 'square_foot') ? 'sq ft' : 'sq m'; ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="/products" class="btn btn-primary">View All Products</a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section py-5" data-aos="fade-up">
    <div class="container">
        <div class="section-title text-center mb-5">
            <h2 class="golden-text-bgr">Why Choose Us</h2>
        </div>
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="feature-card text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-medal fa-2x text-primary"></i>
                    </div>
                    <h3 class="h5">Premium Selection</h3>
                    <p>Carefully curated collection of the finest marble varieties from around the world</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="feature-card text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-truck fa-2x text-primary"></i>
                    </div>
                    <h3 class="h5">Professional Delivery</h3>
                    <p>Safe and secure delivery service with specialized transportation for delicate stone</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="feature-card text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-tools fa-2x text-primary"></i>
                    </div>
                    <h3 class="h5">Expert Consultation</h3>
                    <p>Professional guidance to help you select the perfect marble for your project</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section py-5 bg-primary text-white" data-aos="fade-up">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <h2>Ready to transform your space?</h2>
                <p class="lead mb-0">Contact our experts for personalized assistance with your project</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="#contacts" class="btn btn-light">Contact Us</a>
            </div>
        </div>
    </div>
</section>


?>

    <section id="contacts" class="min-h-screen flex justify-center flex-col gap-0_5" data-aos="fade-up">
        <h2 class="golden-text-bgr">Contacts</h2>
        <div class="flex-row vert-top container" data-aos="fade-up" data-aos-delay="200">
            <div class="map w-50 " style="height:100%;min-height: 600px">
                <h3 class="mb-4 mt-2">Our Indian Office</h3>

                "RIPHEAN MARBLE PRIVATE LIMITED"<br>
                h.no.1633/9, ground floor Pundalik Nagar, Alto Porvorim Bardez North Goa GA, 403521 IN<br>
                Contact: +91 123 456 7890, india@ripheanmarble.com<br>
                Open Monday to Friday, 9:00 AM - 6:00 PM
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3959.5205177616995!2d73.83557347518916!3d15.517259553977942!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bbfc0740bf8a04b%3A0x27e2f05168a47606!2sGoa%20Swim%20Club!5e1!3m2!1sen!2sth!4v1735118168557!5m2!1sen!2sth"
                        width="600" height="650" style="margin-top:1.5em;width:100%;height:300px;border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <address class="w-50" data-aos="fade-up" data-aos-delay="300" style="padding-left: 30px">

                <?php include '_contact_form.php'; ?>
            </address>
        </div>
    </section>

<?php
include '_footer.php';
?>
