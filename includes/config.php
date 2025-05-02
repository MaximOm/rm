<?php
/**
 * Configuration File
 * 
 * Contains application settings and database credentials
 */

// Prevent direct access to this file
if (!defined('APP_INITIALIZED')) {
    die('Direct access to this file is not allowed.');
}

// Main configuration array
$config = [
    // Database Configuration
    'database' => [
        'server' => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'name' => 'rm',
        'port' => 3306
    ],
    
    // Website Configuration
    'site' => [
        'name' => 'RM',
        'url' => 'http://rm/',
        'admin_email' => 'admin@example.com',
        'version' => '1.0.0',
        'debug' => true  // Set to false in production
    ],
    
    // File Paths
    'paths' => [
        'root' => $_SERVER['DOCUMENT_ROOT'],
        'assets' => $_SERVER['DOCUMENT_ROOT'] . '/assets',
        'uploads' => $_SERVER['DOCUMENT_ROOT'] . '/assets/img',
        'products_upload' => $_SERVER['DOCUMENT_ROOT'] . '/assets/img/products',
        'categories_upload' => $_SERVER['DOCUMENT_ROOT'] . '/assets/img/categories',
        'product_images' => $_SERVER['DOCUMENT_ROOT'] . '/assets/img/products/images'
    ],
    
    // File URLs
    'urls' => [
        'assets' => '/assets',
        'products_upload' => '/assets/img/products',
        'categories_upload' => '/assets/img/categories'
    ],
    
    // Admin Configuration
    'admin' => [
        'username' => 'admin',
        'password' => 'admin123' // You should change this in production
    ],
    
    // Display Settings
    'display' => [
        'items_per_page' => 12,
        'featured_products_count' => 3
    ],
    
    // Session Settings
    'session' => [
        'timeout' => 3600 // 1 hour
    ],
    
    // Email Configuration
    'email' => [
        'smtp_enabled' => false,
        'smtp_host' => '',
        'smtp_port' => 587,
        'smtp_username' => '',
        'smtp_password' => '',
        'smtp_encryption' => 'tls'
    ],
    
    // Image settings
    'images' => [
        'large_width' => 1200,
        'thumbnail_width' => 300,
        'gallery_width' => 800,
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif'],
        'max_upload_size' => 10 * 1024 * 1024 // 10MB
    ],
    
    // Date/Time settings
    'datetime' => [
        'date_format' => 'M d, Y',
        'timezone' => 'America/New_York'
    ]
];

// Set timezone
date_default_timezone_set($config['datetime']['timezone']);

// For backward compatibility - define constants
// Database Configuration
define('DB_SERVER', $config['database']['server']);
define('DB_USERNAME', $config['database']['username']);
define('DB_PASSWORD', $config['database']['password']);
define('DB_NAME', $config['database']['name']);
define('DB_PORT', $config['database']['port']);

// Website Configuration
define('SITE_NAME', $config['site']['name']);
define('SITE_URL', $config['site']['url']);
define('ADMIN_EMAIL', $config['site']['admin_email']);
define('APP_DEBUG', $config['site']['debug']);

// File Paths
define('ROOT_PATH', $config['paths']['root']);
define('ASSETS_PATH', $config['paths']['assets']);
define('UPLOADS_PATH', $config['paths']['uploads']);
define('PRODUCTS_UPLOAD_PATH', $config['paths']['products_upload']);
define('CATEGORIES_UPLOAD_PATH', $config['paths']['categories_upload']);

// File URLs
define('ASSETS_URL', $config['urls']['assets']);
define('PRODUCTS_UPLOAD_URL', $config['urls']['products_upload']);
define('CATEGORIES_UPLOAD_URL', $config['urls']['categories_upload']);

// Admin Configuration
define('ADMIN_USERNAME', $config['admin']['username']);
define('ADMIN_PASSWORD', $config['admin']['password']);

// Other Constants
define('ITEMS_PER_PAGE', $config['display']['items_per_page']);
define('FEATURED_PRODUCTS_COUNT', $config['display']['featured_products_count']);

// Session Timeout
define('SESSION_TIMEOUT', $config['session']['timeout']);

// Email Configuration
define('SMTP_ENABLED', $config['email']['smtp_enabled']);
define('SMTP_HOST', $config['email']['smtp_host']);
define('SMTP_PORT', $config['email']['smtp_port']);
define('SMTP_USERNAME', $config['email']['smtp_username']);
define('SMTP_PASSWORD', $config['email']['smtp_password']);
define('SMTP_ENCRYPTION', $config['email']['smtp_encryption']);

// Version
define('APP_VERSION', $config['site']['version']);

/**
 * Helper Functions for Configuration
 */

// Function to get configuration value
function get_config($key, $default = null) {
    global $config;
    
    // Handle nested keys like 'site.name'
    if (strpos($key, '.') !== false) {
        $keys = explode('.', $key);
        $value = $config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
    
    return isset($config[$key]) ? $config[$key] : $default;
}

// Function to create appropriate URL
function url($path = '') {
    return get_config('site.url') . '/' . ltrim($path, '/');
}

// Function to get the current page name
function current_page() {
    $page = basename($_SERVER['PHP_SELF']);
    return $page;
}

// Function to redirect
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

// Simple session-based flash messages
function set_message($message, $type = 'info') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

function get_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

// Simple authentication check
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

// Debug function - only works when debug mode is enabled
function debug($data) {
    if (APP_DEBUG) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}
