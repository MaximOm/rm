<footer class="site-footer pt-5 bg-dark text-white">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <h4 class="h5 mb-3">About Us</h4>
                <p class="small text-muted">
                    <?php echo get_config('site_description', 'We offer premium marble slabs for your home or business. Our high-quality marble comes from quarries around the world.'); ?>
                </p>
                <h4 class="h5 mt-4 mb-3">Follow Us</h4>
                <div class="social-links">
                    <?php if($social_facebook = get_config('social_facebook', '')): ?>
                    <a href="<?php echo $social_facebook; ?>" class="text-white me-2" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <?php endif; ?>
                    
                    <?php if($social_instagram = get_config('social_instagram', '')): ?>
                    <a href="<?php echo $social_instagram; ?>" class="text-white me-2" target="_blank"><i class="fab fa-instagram"></i></a>
                    <?php endif; ?>
                    
                    <?php if($social_pinterest = get_config('social_pinterest', '')): ?>
                    <a href="<?php echo $social_pinterest; ?>" class="text-white me-2" target="_blank"><i class="fab fa-pinterest-p"></i></a>
                    <?php endif; ?>
                    
                    <?php if($social_linkedin = get_config('social_linkedin', '')): ?>
                    <a href="<?php echo $social_linkedin; ?>" class="text-white" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <h4 class="h5 mb-3">Quick Links</h4>
                <ul class="list-unstyled footer-links">
                    <li><a href="/" class="text-white text-decoration-none">Home</a></li>
                    <li><a href="/products" class="text-white text-decoration-none">Products</a></li>
                    <li><a href="/quarry" class="text-white text-decoration-none">Our Quarry</a></li>
                    <li><a href="/mission" class="text-white text-decoration-none">Our Mission</a></li>
                    <li><a href="/contacts" class="text-white text-decoration-none">Contact Us</a></li>
                    <?php if(isset($_SESSION['user_id']) && $_SESSION['user_role'] == 'admin'): ?>
                    <li><a href="/admin" class="text-white text-decoration-none">Admin Panel</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <h4 class="h5 mb-3">Product Categories</h4>
                <ul class="list-unstyled footer-links">
                    <li><a href="/products" class="text-white text-decoration-none">All Products</a></li>
                    <li><a href="/categories" class="text-white text-decoration-none">Browse Categories</a></li>
                    <?php
                    // Get main categories for footer
                    $footer_categories = get_categories();
                    foreach ($footer_categories as $category):
                    ?>
                        <li>
                            <a href="/products?category=<?php echo $category['id']; ?>" class="text-white text-decoration-none">
                                <?php echo $category['name']; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <h4 class="h5 mb-3">Contact Information</h4>
                <ul class="list-unstyled footer-contact">
                    <li class="d-flex mb-2">
                        <i class="fas fa-map-marker-alt me-2 mt-1"></i>
                        <span><?php echo get_config('site_address', '123 Marble Way, Stone City<br>Quarry State, 12345'); ?></span>
                    </li>
                    <li class="d-flex mb-2">
                        <i class="fas fa-phone-alt me-2 mt-1"></i>
                        <span><?php echo get_config('site_phone', '(123) 456-7890'); ?></span>
                    </li>
                    <li class="d-flex mb-2">
                        <i class="fas fa-envelope me-2 mt-1"></i>
                        <span><a href="mailto:<?php echo get_config('site_email', 'info@marbleslabs.com'); ?>" class="text-white text-decoration-none"><?php echo get_config('site_email', 'info@marbleslabs.com'); ?></a></span>
                    </li>
                    <li class="d-flex mb-2">
                        <i class="fas fa-clock me-2 mt-1"></i>
                        <span><?php echo get_config('business_hours', 'Monday-Friday: 9am-5pm<br>Saturday: 10am-2pm'); ?></span>
                    </li>
                </ul>
                
                <h4 class="h5 mt-4 mb-3">Newsletter</h4>
                <p class="small">Subscribe to receive updates on new products and special offers.</p>
                <form action="/api/subscribe" method="POST" class="newsletter-form mt-3">
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" placeholder="Your Email" required name="email">
                        <button class="btn btn-primary" type="submit">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
        
        <hr class="mt-4 mb-3 border-secondary">
        
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                <p class="mb-0 small">&copy; <?php echo date('Y'); ?> <?php echo get_config('site_title', 'Marble Slabs'); ?>. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-center text-md-end small">
                <a href="/privacy-policy" class="text-white text-decoration-none me-3">Privacy Policy</a>
                <a href="/terms-of-service" class="text-white text-decoration-none">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>
