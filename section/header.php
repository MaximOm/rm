<?php
// Get current page for highlighting active nav item
$current_page = basename($_SERVER['PHP_SELF']);

// Get cart count if available
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}
?>
<header class="site-header">
    <div class="top-bar bg-dark text-white py-2">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-2 mb-md-0">
                    <div class="contact-info small">
                        <span class="me-3"><i class="fas fa-phone-alt me-1"></i> (123) 456-7890</span>
                        <span><i class="fas fa-envelope me-1"></i> info@marbleslabs.com</span>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="user-nav small">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="account.php" class="text-white me-3">
                                <i class="fas fa-user me-1"></i> My Account
                            </a>
                            <a href="logout.php" class="text-white">
                                <i class="fas fa-sign-out-alt me-1"></i> Logout
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="text-white me-3">
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </a>
                            <a href="register.php" class="text-white">
                                <i class="fas fa-user-plus me-1"></i> Register
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="main-header py-3">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-3 col-md-4 col-6 mb-3 mb-md-0">
                    <div class="logo">
                        <a href="index.php">
                            <img src="images/logo.svg" alt="<?php echo get_config('site_title'); ?>" class="img-fluid" style="max-height: 60px;">
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 col-md-5 order-md-2 order-3 mt-3 mt-md-0">
                    <form action="products.php" method="GET" class="search-form">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search for marble slabs..." 
                                aria-label="Search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-lg-3 col-md-3 col-6 order-md-3 order-2 text-end">
                    <div class="header-actions">
                        <a href="wishlist.php" class="btn btn-outline-secondary me-2" title="Wishlist">
                            <i class="far fa-heart"></i>
                        </a>
                        <a href="cart.php" class="btn btn-outline-primary position-relative" title="Shopping Cart">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if ($cart_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $cart_count; ?>
                                <span class="visually-hidden">items in cart</span>
                            </span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                    data-bs-target="#navbarMain" aria-controls="navbarMain" 
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo ($current_page == 'products.php' || $current_page == 'categories.php') ? 'active' : ''; ?>" 
                           href="products.php" id="navbarDropdownProducts" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            Products
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownProducts">
                            <li><a class="dropdown-item" href="products.php">All Products</a></li>
                            <li><hr class="dropdown-divider"></li>
                            
                            <?php
                            // Get main categories for navigation
                            $nav_categories = get_categories();
                            foreach ($nav_categories as $category):
                            ?>
                                <li><a class="dropdown-item" href="products.php?category=<?php echo $category['id']; ?>"><?php echo $category['name']; ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'quarry.php') ? 'active' : ''; ?>" href="quarry.php">Our Quarry</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'mission.php') ? 'active' : ''; ?>" href="mission.php">Our Mission</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'contacts.php') ? 'active' : ''; ?>" href="contacts.php">Contact Us</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>