<?php
require_once 'db_connection.php';
require_once 'config.php';

// Check if database is connected
function is_db_connected() {
    global $conn;
    return ($conn && $conn->ping());
}

// ======= CATEGORY FUNCTIONS =======

// Get all categories
function get_categories($parent_id = 0) {
    global $conn;
    
    // Check if database is connected
    if (!is_db_connected()) {
        // Return empty array if no connection
        return [];
    }
    
    $sql = "SELECT * FROM categories WHERE parent_id = ? ORDER BY sort_order, name";
    $result = query($conn, $sql, [$parent_id]);
    
    $categories = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    return $categories;
}

// Get category by ID
function get_category($id) {
    global $conn;
    
    // Check if database is connected
    if (!is_db_connected()) {
        return null;
    }
    
    $sql = "SELECT * FROM categories WHERE id = ?";
    $result = query($conn, $sql, [$id]);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

// Get category path (for breadcrumbs)
function get_category_path($category_id) {
    $path = [];
    $current = get_category($category_id);
    
    if ($current) {
        $path[] = $current;
        
        while ($current && $current['parent_id'] > 0) {
            $current = get_category($current['parent_id']);
            if ($current) {
                array_unshift($path, $current);
            }
        }
    }
    
    return $path;
}

// ======= PRODUCT FUNCTIONS =======

// Get products with optional filtering
function get_products($filters = [], $page = 1, $limit = null) {
    global $conn;
    
    // Check if database is connected
    if (!is_db_connected()) {
        // Return empty array if no connection
        return [];
    }
    
    if ($limit === null) {
        $limit = get_config('products_per_page');
    }
    
    $offset = ($page - 1) * $limit;
    
    $where_clauses = ["p.status = 'active'"];
    $params = [];
    
    // Handle category filter
    if (!empty($filters['category_id'])) {
        $where_clauses[] = "p.id IN (SELECT product_id FROM product_categories WHERE category_id = ?)";
        $params[] = $filters['category_id'];
    }
    
    // Handle origin filter
    if (!empty($filters['origin'])) {
        $where_clauses[] = "p.origin_country = ?";
        $params[] = $filters['origin'];
    }
    
    // Handle price range filter
    if (!empty($filters['min_price'])) {
        $where_clauses[] = "p.price_per_unit >= ?";
        $params[] = $filters['min_price'];
    }
    
    if (!empty($filters['max_price'])) {
        $where_clauses[] = "p.price_per_unit <= ?";
        $params[] = $filters['max_price'];
    }
    
    // Handle search query
    if (!empty($filters['search'])) {
        $where_clauses[] = "(p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)";
        $search_term = "%{$filters['search']}%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    $where_sql = implode(' AND ', $where_clauses);
    
    // Determine sort order
    $sort_field = !empty($filters['sort']) ? $filters['sort'] : 'name';
    $sort_dir = !empty($filters['dir']) ? $filters['dir'] : 'ASC';
    
    // Sanitize sort parameters
    $allowed_sort_fields = ['name', 'price_per_unit', 'created_at'];
    $allowed_sort_dirs = ['ASC', 'DESC'];
    
    if (!in_array($sort_field, $allowed_sort_fields)) {
        $sort_field = 'name';
    }
    
    if (!in_array(strtoupper($sort_dir), $allowed_sort_dirs)) {
        $sort_dir = 'ASC';
    }
    
    $sql = "SELECT p.*, 
                 (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS primary_image
            FROM products p
            WHERE $where_sql
            ORDER BY p.$sort_field $sort_dir
            LIMIT $offset, $limit";
    
    $result = query($conn, $sql, $params);
    
    $products = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Add image URL
            if (!empty($row['primary_image'])) {
                $row['image_url'] = get_config('upload_url') . $row['primary_image'];
            } else {
                $row['image_url'] = get_config('base_url') . '/assets/images/no-image.jpg';
            }
            
            $products[] = $row;
        }
    }
    
    return $products;
}

// Count total products with filters
function count_products($filters = []) {
    global $conn;
    
    // Check if database is connected
    if (!is_db_connected()) {
        return 0;
    }
    
    $where_clauses = ["p.status = 'active'"];
    $params = [];
    
    // Handle category filter
    if (!empty($filters['category_id'])) {
        $where_clauses[] = "p.id IN (SELECT product_id FROM product_categories WHERE category_id = ?)";
        $params[] = $filters['category_id'];
    }
    
    // Handle origin filter
    if (!empty($filters['origin'])) {
        $where_clauses[] = "p.origin_country = ?";
        $params[] = $filters['origin'];
    }
    
    // Handle price range filter
    if (!empty($filters['min_price'])) {
        $where_clauses[] = "p.price_per_unit >= ?";
        $params[] = $filters['min_price'];
    }
    
    if (!empty($filters['max_price'])) {
        $where_clauses[] = "p.price_per_unit <= ?";
        $params[] = $filters['max_price'];
    }
    
    // Handle search query
    if (!empty($filters['search'])) {
        $where_clauses[] = "(p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)";
        $search_term = "%{$filters['search']}%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    $where_sql = implode(' AND ', $where_clauses);
    
    $sql = "SELECT COUNT(*) as total FROM products p WHERE $where_sql";
    
    $result = query($conn, $sql, $params);
    
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total'];
    }
    
    return 0;
}

// Get product by ID or slug
function get_product($id_or_slug) {
    global $conn;
    
    // Check if database is connected
    if (!is_db_connected()) {
        return null;
    }
    
    $is_numeric = is_numeric($id_or_slug);
    $field = $is_numeric ? 'id' : 'slug';
    
    $sql = "SELECT p.* FROM products p WHERE p.$field = ?";
    $result = query($conn, $sql, [$id_or_slug]);
    
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Get product categories
        $sql = "SELECT c.* FROM categories c 
                JOIN product_categories pc ON c.id = pc.category_id 
                WHERE pc.product_id = ?";
        $result = query($conn, $sql, [$product['id']]);
        
        $product['categories'] = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $product['categories'][] = $row;
            }
        }
        
        // Get product images
        $sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC";
        $result = query($conn, $sql, [$product['id']]);
        
        $product['images'] = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $row['url'] = get_config('upload_url') . $row['image_path'];
                $product['images'][] = $row;
            }
        }
        
        // Get product specifications
        $sql = "SELECT * FROM product_specifications WHERE product_id = ?";
        $result = query($conn, $sql, [$product['id']]);
        
        if ($result && $result->num_rows > 0) {
            $product['specifications'] = $result->fetch_assoc();
            
            // Parse JSON technical details
            if (isset($product['specifications']['technical_details'])) {
                $product['specifications']['technical_details'] = json_decode(
                    $product['specifications']['technical_details'], 
                    true
                );
            }
        }
        
        return $product;
    }
    
    return null;
}

// Get related products
function get_related_products($product_id, $limit = 4) {
    global $conn;
    
    // Check if database is connected
    if (!is_db_connected()) {
        return [];
    }
    
    // Get categories of this product
    $sql = "SELECT category_id FROM product_categories WHERE product_id = ?";
    $result = query($conn, $sql, [$product_id]);
    
    if (!$result || $result->num_rows == 0) {
        return [];
    }
    
    $category_ids = [];
    while ($row = $result->fetch_assoc()) {
        $category_ids[] = $row['category_id'];
    }
    
    $category_list = implode(',', $category_ids);
    
    // Get products from the same categories
    $sql = "SELECT DISTINCT p.*, 
                 (SELECT image_path FROM product_images 
                  WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS primary_image 
            FROM products p
            JOIN product_categories pc ON p.id = pc.product_id
            WHERE p.id != ? AND pc.category_id IN ($category_list) AND p.status = 'active'
            ORDER BY RAND()
            LIMIT ?";
    
    $result = query($conn, $sql, [$product_id, $limit]);
    
    $products = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Add image URL
            if (!empty($row['primary_image'])) {
                $row['image_url'] = get_config('upload_url') . $row['primary_image'];
            } else {
                $row['image_url'] = get_config('base_url') . '/assets/images/no-image.jpg';
            }
            
            $products[] = $row;
        }
    }
    
    return $products;
}

// ======= USER FUNCTIONS =======

// Authenticate user
function authenticate_user($email, $password) {
    global $conn;
    
    // Check if database is connected
    if (!is_db_connected()) {
        return false;
    }
    
    $sql = "SELECT * FROM users WHERE email = ?";
    $result = query($conn, $sql, [$email]);
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Don't store password in session
            unset($user['password']);
            
            // Set session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            return $user;
        }
    }
    
    return false;
}

// Log out user
function logout_user() {
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session
    session_destroy();
}

// ======= IMAGE HANDLING FUNCTIONS =======

// Upload and process product image
function upload_product_image($file, $product_id) {
    global $conn;
    
    // Check if database is connected
    if (!is_db_connected()) {
        return [
            'success' => false,
            'message' => 'Database connection error'
        ];
    }
    
    // Validate file
    $allowed_extensions = get_config('allowed_extensions');
    $max_size = get_config('max_upload_size');
    
    $file_name = $file['name'];
    $file_size = $file['size'];
    $file_tmp = $file['tmp_name'];
    
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, $allowed_extensions)) {
        return [
            'success' => false,
            'message' => 'Invalid file extension. Allowed: ' . implode(', ', $allowed_extensions)
        ];
    }
    
    if ($file_size > $max_size) {
        return [
            'success' => false,
            'message' => 'File too large. Maximum size: ' . ($max_size / 1024 / 1024) . 'MB'
        ];
    }
    
    // Create directories if they don't exist
    $product_dir = get_config('upload_path') . 'products/' . $product_id . '/';
    if (!is_dir($product_dir)) {
        mkdir($product_dir, 0755, true);
    }
    
    // Generate unique filename
    $new_filename = uniqid() . '.' . $file_ext;
    $upload_path = $product_dir . $new_filename;
    
    // Move uploaded file
    if (move_uploaded_file($file_tmp, $upload_path)) {
        // Save to database
        $image_path = 'products/' . $product_id . '/' . $new_filename;
        
        // Check if this is the first image (make it primary)
        $sql = "SELECT COUNT(*) as count FROM product_images WHERE product_id = ?";
        $result = query($conn, $sql, [$product_id]);
        $row = $result->fetch_assoc();
        $is_primary = ($row['count'] == 0) ? 1 : 0;
        
        $sql = "INSERT INTO product_images (product_id, image_path, is_primary, sort_order) 
                VALUES (?, ?, ?, ?)";
        $result = query($conn, $sql, [$product_id, $image_path, $is_primary, 0]);
        
        if ($result) {
            return [
                'success' => true,
                'image_id' => $conn->insert_id,
                'image_path' => $image_path,
                'image_url' => get_config('upload_url') . $image_path
            ];
        } else {
            // Delete the file if database insert failed
            unlink($upload_path);
            return [
                'success' => false,
                'message' => 'Failed to save image to database'
            ];
        }
    } else {
        return [
            'success' => false,
            'message' => 'Failed to upload file'
        ];
    }
}

// Delete product image
function delete_product_image($image_id) {
    global $conn;
    
    // Check if database is connected
    if (!is_db_connected()) {
        return false;
    }
    
    // Get image info
    $sql = "SELECT * FROM product_images WHERE id = ?";
    $result = query($conn, $sql, [$image_id]);
    
    if ($result && $result->num_rows > 0) {
        $image = $result->fetch_assoc();
        $file_path = get_config('upload_path') . $image['image_path'];
        
        // Delete file if exists
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // Delete from database
        $sql = "DELETE FROM product_images WHERE id = ?";
        $result = query($conn, $sql, [$image_id]);
        
        // If this was the primary image, set a new one
        if ($image['is_primary']) {
            $sql = "UPDATE product_images SET is_primary = 1 
                    WHERE product_id = ? ORDER BY sort_order ASC LIMIT 1";
            query($conn, $sql, [$image['product_id']]);
        }
        
        return $result ? true : false;
    }
    
    return false;
}

// ======= ADMIN FUNCTIONS =======

// Add or update product
function save_product($data) {
    global $conn;
    
    // Check if database is connected
    if (!is_db_connected()) {
        return [
            'success' => false,
            'message' => 'Database connection error'
        ];
    }
    
    // Format slug from name if empty
    if (empty($data['slug'])) {
        $data['slug'] = create_slug($data['name']);
    }
    
    // Check if product exists (update or insert)
    $is_update = !empty($data['id']);
    
    if ($is_update) {
        $sql = "UPDATE products SET 
                name = ?, 
                slug = ?, 
                sku = ?, 
                description = ?, 
                short_description = ?, 
                origin_country = ?, 
                price_per_unit = ?, 
                unit_type = ?, 
                weight_per_unit = ?, 
                maintenance_info = ?, 
                status = ?, 
                updated_at = NOW() 
                WHERE id = ?";
        
        $params = [
            $data['name'],
            $data['slug'],
            $data['sku'],
            $data['description'],
            $data['short_description'],
            $data['origin_country'],
            $data['price_per_unit'],
            $data['unit_type'],
            $data['weight_per_unit'],
            $data['maintenance_info'],
            $data['status'],
            $data['id']
        ];
        
        $result = query($conn, $sql, $params);
        $product_id = $data['id'];
        
    } else {
        $sql = "INSERT INTO products (
                name, slug, sku, description, short_description, 
                origin_country, price_per_unit, unit_type, weight_per_unit, 
                maintenance_info, status, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $params = [
            $data['name'],
            $data['slug'],
            $data['sku'],
            $data['description'],
            $data['short_description'],
            $data['origin_country'],
            $data['price_per_unit'],
            $data['unit_type'],
            $data['weight_per_unit'],
            $data['maintenance_info'],
            $data['status']
        ];
        
        $result = query($conn, $sql, $params);
        $product_id = $conn->insert_id;
    }
    
    if (!$result) {
        return [
            'success' => false,
            'message' => 'Failed to save product: ' . $conn->error
        ];
    }
    
    // Save specifications
    if (!empty($data['specifications'])) {
        // Check if specifications exist for this product
        $sql = "SELECT id FROM product_specifications WHERE product_id = ?";
        $result = query($conn, $sql, [$product_id]);
        
        $specs = $data['specifications'];
        
        // JSON encode technical details if it's an array
        if (isset($specs['technical_details']) && is_array($specs['technical_details'])) {
            $specs['technical_details'] = json_encode($specs['technical_details']);
        }
        
        if ($result && $result->num_rows > 0) {
            // Update existing specifications
            $sql = "UPDATE product_specifications SET 
                    length = ?, 
                    width = ?, 
                    thickness = ?, 
                    weight = ?, 
                    water_absorption = ?, 
                    density = ?, 
                    technical_details = ? 
                    WHERE product_id = ?";
            
            $params = [
                $specs['length'],
                $specs['width'],
                $specs['thickness'],
                $specs['weight'],
                $specs['water_absorption'],
                $specs['density'],
                $specs['technical_details'],
                $product_id
            ];
            
            query($conn, $sql, $params);
            
        } else {
            // Insert new specifications
            $sql = "INSERT INTO product_specifications (
                    product_id, length, width, thickness, weight, 
                    water_absorption, density, technical_details
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $product_id,
                $specs['length'],
                $specs['width'],
                $specs['thickness'],
                $specs['weight'],
                $specs['water_absorption'],
                $specs['density'],
                $specs['technical_details']
            ];
            
            query($conn, $sql, $params);
        }
    }
    
    // Save categories if provided
    if (isset($data['categories']) && is_array($data['categories'])) {
        // First, remove all existing category associations
        $sql = "DELETE FROM product_categories WHERE product_id = ?";
        query($conn, $sql, [$product_id]);
        
        // Then add new ones
        foreach ($data['categories'] as $category_id) {
            $sql = "INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)";
            query($conn, $sql, [$product_id, $category_id]);
        }
    }
    
    return [
        'success' => true,
        'product_id' => $product_id,
        'is_new' => !$is_update
    ];
}

// Create URL-friendly slug
function create_slug($string) {
    // Replace non letter or digit with dash
    $string = preg_replace('~[^\pL\d]+~u', '-', $string);
    
    // Transliterate
    $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
    
    // Remove unwanted characters
    $string = preg_replace('~[^-\w]+~', '', $string);
    
    // Trim
    $string = trim($string, '-');
    
    // Remove duplicate dashes
    $string = preg_replace('~-+~', '-', $string);
    
    // Lowercase
    $string = strtolower($string);
    
    if (empty($string)) {
        return 'product-' . time();
    }
    
    return $string;
}

// ======= PAGINATION FUNCTIONS =======

// Generate pagination links
function pagination($total_items, $current_page, $per_page, $url_pattern) {
    $total_pages = ceil($total_items / $per_page);
    
    if ($total_pages <= 1) {
        return '';
    }
    
    $html = '<div class="pagination">';
    
    // Previous page link
    if ($current_page > 1) {
        $prev_url = str_replace('{page}', $current_page - 1, $url_pattern);
        $html .= '<a href="' . $prev_url . '" class="prev">&laquo; Previous</a>';
    } else {
        $html .= '<span class="prev disabled">&laquo; Previous</span>';
    }
    
    // Page numbers
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    
    if ($start > 1) {
        $html .= '<a href="' . str_replace('{page}', 1, $url_pattern) . '">1</a>';
        if ($start > 2) {
            $html .= '<span class="ellipsis">...</span>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $current_page) {
            $html .= '<span class="current">' . $i . '</span>';
        } else {
            $page_url = str_replace('{page}', $i, $url_pattern);
            $html .= '<a href="' . $page_url . '">' . $i . '</a>';
        }
    }
    
    if ($end < $total_pages) {
        if ($end < $total_pages - 1) {
            $html .= '<span class="ellipsis">...</span>';
        }
        $html .= '<a href="' . str_replace('{page}', $total_pages, $url_pattern) . '">' . $total_pages . '</a>';
    }
    
    // Next page link
    if ($current_page < $total_pages) {
        $next_url = str_replace('{page}', $current_page + 1, $url_pattern);
        $html .= '<a href="' . $next_url . '" class="next">Next &raquo;</a>';
    } else {
        $html .= '<span class="next disabled">Next &raquo;</span>';
    }
    
    $html .= '</div>';
    
    return $html;
}
?>
