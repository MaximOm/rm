<?php
/**
 * Application Configuration
 * 
 * Central configuration file for the application.
 * Contains database credentials, paths, and other settings.
 */

// Prevent direct access
if (!defined('APP_PATH')) {
    define('APP_PATH', dirname(__DIR__));
}

// Environment settings
$environment = getenv('APP_ENV') ?: 'development';

// Base configuration
$config = [
    // Application settings
    'app_name' => 'Royal Marble',
    'app_url' => 'http://localhost/rm',
    'admin_email' => 'admin@example.com',
    
    // Database settings - default development values
    'db_host' => 'localhost',
    'db_name' => 'rm_database',
    'db_user' => 'root',
    'db_pass' => '',
    
    // Path settings
    'upload_path' => APP_PATH . '/uploads',
    'max_upload_size' => 10 * 1024 * 1024, // 10MB
    
    // Email settings
    'mail_host' => 'smtp.example.com',
    'mail_port' => 587,
    'mail_username' => 'noreply@example.com',
    'mail_password' => 'your-password',
    'mail_encryption' => 'tls',
    'mail_from_name' => 'Royal Marble',
    
    // Security settings
    'session_lifetime' => 86400, // 24 hours
    'encryption_key' => 'change-this-to-a-random-string',
];

// Environment-specific configuration
if (file_exists(APP_PATH . '/config/environments/' . $environment . '.php')) {
    include APP_PATH . '/config/environments/' . $environment . '.php';
}

// Define constants for database connection
define('DB_HOST', $config['db_host']);
define('DB_NAME', $config['db_name']);
define('DB_USER', $config['db_user']);
define('DB_PASS', $config['db_pass']);

// Define other application constants
define('SITE_NAME', $config['app_name']);
define('SITE_URL', $config['app_url']);
define('ADMIN_EMAIL', $config['admin_email']);
define('UPLOAD_PATH', $config['upload_path']);
define('MAX_UPLOAD_SIZE', $config['max_upload_size']);