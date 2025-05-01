/**
 * Main JavaScript file for the Marble Slab Store website
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize Bootstrap popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Handle add to cart button clicks
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    if (addToCartButtons.length > 0) {
        addToCartButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.getAttribute('data-product-id');
                const quantity = document.querySelector('input[name="quantity"]') 
                    ? document.querySelector('input[name="quantity"]').value 
                    : 1;
                
                addToCart(productId, quantity);
            });
        });
    }
    
    // Handle quantity input
    const quantityInputs = document.querySelectorAll('.quantity-input');
    if (quantityInputs.length > 0) {
        quantityInputs.forEach(input => {
            input.addEventListener('change', function() {
                const min = parseFloat(this.getAttribute('min')) || 0;
                if (parseFloat(this.value) < min) {
                    this.value = min;
                }
                
                // If this is on the cart page, update cart
                if (window.location.pathname.includes('cart.php')) {
                    const productId = this.getAttribute('data-product-id');
                    updateCartItem(productId, this.value);
                }
            });
        });
    }
    
    // Quantity buttons
    const quantityBtns = document.querySelectorAll('.quantity-btn');
    if (quantityBtns.length > 0) {
        quantityBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.closest('.quantity-control').querySelector('.quantity-input');
                let value = parseFloat(input.value) || 0;
                const min = parseFloat(input.getAttribute('min')) || 0;
                const step = parseFloat(input.getAttribute('step')) || 1;
                
                if (this.getAttribute('data-action') === 'increase') {
                    value += step;
                } else {
                    value = Math.max(min, value - step);
                }
                
                input.value = value;
                
                // If this is on the cart page, update cart
                if (window.location.pathname.includes('cart.php')) {
                    const productId = input.getAttribute('data-product-id');
                    updateCartItem(productId, value);
                }
            });
        });
    }
    
    // Remove items from cart
    const removeButtons = document.querySelectorAll('.remove-item');
    if (removeButtons.length > 0) {
        removeButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to remove this item from your cart?')) {
                    const productId = this.getAttribute('data-product-id');
                    removeFromCart(productId);
                }
            });
        });
    }
    
    // Product gallery image click
    const galleryThumbs = document.querySelectorAll('.gallery-thumbs .thumbnail');
    if (galleryThumbs.length > 0) {
        const mainImage = document.getElementById('main-product-image');
        
        galleryThumbs.forEach(thumb => {
            thumb.addEventListener('click', function() {
                const imgSrc = this.getAttribute('data-src');
                mainImage.src = imgSrc;
                
                // Remove active class from all thumbnails
                galleryThumbs.forEach(t => t.classList.remove('active'));
                // Add active class to clicked thumbnail
                this.classList.add('active');
                
                // Update lightbox href if it exists
                if (mainImage.parentElement.tagName === 'A') {
                    mainImage.parentElement.setAttribute('href', imgSrc);
                }
            });
        });
    }
    
    // Add to cart via AJAX
    function addToCart(productId, quantity) {
        fetch('/cart-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=add&product_id=' + encodeURIComponent(productId) + '&quantity=' + encodeURIComponent(quantity)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update cart count
                updateCartCount(data.cart_count);
                
                // Show success message
                showMessage('Product added to cart successfully!', 'success');
                
                // Optionally show mini cart or redirect to cart page
                if (confirm('Product added to cart. View cart now?')) {
                    window.location.href = 'cart.php';
                }
            } else {
                showMessage('Error: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred. Please try again.', 'danger');
        });
    }
    
    // Update cart item quantity
    function updateCartItem(productId, quantity) {
        fetch('/cart-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=update&product_id=' + encodeURIComponent(productId) + '&quantity=' + encodeURIComponent(quantity)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the page to update totals
                window.location.reload();
            } else {
                showMessage('Error: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred. Please try again.', 'danger');
        });
    }
    
    // Remove item from cart
    function removeFromCart(productId) {
        fetch('/cart-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=remove&product_id=' + encodeURIComponent(productId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the page to update the cart
                window.location.reload();
            } else {
                showMessage('Error: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred. Please try again.', 'danger');
        });
    }
    
    // Update cart count in header
    function updateCartCount(count) {
        const cartCountEl = document.querySelector('.btn-primary .badge');
        if (cartCountEl) {
            cartCountEl.textContent = count;
            if (count > 0) {
                cartCountEl.classList.remove('d-none');
            } else {
                cartCountEl.classList.add('d-none');
            }
        }
    }
    
    // Function to show messages
    function showMessage(message, type = 'info') {
        // Create alert element
        const alertEl = document.createElement('div');
        alertEl.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertEl.setAttribute('role', 'alert');
        alertEl.style.top = '20px';
        alertEl.style.right = '20px';
        alertEl.style.zIndex = '9999';
        
        // Add message
        alertEl.innerHTML = message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        
        // Add to body
        document.body.appendChild(alertEl);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alertEl);
            bsAlert.close();
        }, 5000);
    }
    
    // Handle form submissions for validation
    const forms = document.querySelectorAll('.needs-validation');
    if (forms.length > 0) {
        forms.forEach(form => {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
        });
    }
    
    // Filter toggle for mobile
    const filterToggle = document.getElementById('filter-toggle');
    if (filterToggle) {
        filterToggle.addEventListener('click', function() {
            const filterSidebar = document.querySelector('.filter-sidebar');
            filterSidebar.classList.toggle('show-filters');
        });
    }
    
    // Price range slider on products page
    const priceRange = document.getElementById('price-range');
    if (priceRange) {
        const minPriceInput = document.getElementById('min-price');
        const maxPriceInput = document.getElementById('max-price');
        
        if (minPriceInput && maxPriceInput) {
            // Update slider when inputs change
            function updateSlider() {
                const minPrice = parseFloat(minPriceInput.value);
                const maxPrice = parseFloat(maxPriceInput.value);
                const totalRange = parseFloat(maxPriceInput.max) - parseFloat(minPriceInput.min);
                
                // Update slider position
                priceRange.value = Math.round(((maxPrice - parseFloat(minPriceInput.min)) / totalRange) * 100);
            }
            
            minPriceInput.addEventListener('change', updateSlider);
            maxPriceInput.addEventListener('change', updateSlider);
            
            // Update max price when slider changes
            priceRange.addEventListener('input', function() {
                const percentage = this.value / 100;
                const range = parseFloat(maxPriceInput.max) - parseFloat(minPriceInput.min);
                const value = parseFloat(minPriceInput.min) + (range * percentage);
                
                maxPriceInput.value = Math.round(value);
            });
            
            // Initialize slider
            updateSlider();
        }
    }
    
    // Add to wishlist
    const wishlistButtons = document.querySelectorAll('.add-to-wishlist');
    if (wishlistButtons.length > 0) {
        wishlistButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.getAttribute('data-product-id');
                
                fetch('/wishlist-handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=add&product_id=' + encodeURIComponent(productId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('Product added to wishlist!', 'success');
                        this.innerHTML = '<i class="fas fa-heart"></i>'; // Change to filled heart
                    } else if (data.redirect) {
                        window.location.href = data.redirect; // Redirect to login
                    } else {
                        showMessage('Error: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('An error occurred. Please try again.', 'danger');
                });
            });
        });
    }
    
    // Lazy loading for images
    if ('loading' in HTMLImageElement.prototype) {
        const lazyImages = document.querySelectorAll('img.lazy');
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
            img.classList.remove('lazy');
        });
    } else {
        // Fallback for browsers that don't support native lazy loading
        const lazyLoadImages = function() {
            const lazyImages = document.querySelectorAll('img.lazy');
            const scrollTop = window.pageYOffset;
            
            lazyImages.forEach(img => {
                if(img.offsetTop < window.innerHeight + scrollTop) {
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                }
            });
            
            if(lazyImages.length == 0) { 
                document.removeEventListener('scroll', lazyLoadImages);
                window.removeEventListener('resize', lazyLoadImages);
                window.removeEventListener('orientationChange', lazyLoadImages);
            }
        };
        
        document.addEventListener('scroll', lazyLoadImages);
        window.addEventListener('resize', lazyLoadImages);
        window.addEventListener('orientationChange', lazyLoadImages);
    }
    
    // Handle installation alerts dismissal
    const installAlerts = document.querySelectorAll('.install-alert .btn-close');
    if (installAlerts.length > 0) {
        installAlerts.forEach(button => {
            button.addEventListener('click', function() {
                // Set a cookie to remember the dismissal
                document.cookie = "install_alert_dismissed=1; path=/; max-age=86400"; // 1 day
            });
        });
    }
    
    // Sort products
    const sortSelect = document.getElementById('sort-select');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const [sort, dir] = this.value.split('-');
            
            // Get current URL and parameters
            const url = new URL(window.location.href);
            const params = url.searchParams;
            
            // Update or add sort parameters
            params.set('sort', sort);
            params.set('dir', dir);
            
            // Redirect to the new URL
            window.location.href = url.toString();
        });
    }
});