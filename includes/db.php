<?php
/**
 * Database connection using PDO
 * 
 * Provides secure database connection for the application
 */

// Prevent direct access
if (!defined(constant_name: 'APP_INITIALIZED') && !defined('ADMIN_INITIALIZED')) {
    die('Direct access to this file is not allowed.');
}

require_once __DIR__ . '/config.php';

try {
    // Create PDO connection
    $dsn = 'mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
    
    // Function to get database connection (for backward compatibility)
    function getDbConnection() {
        global $pdo;
        return $pdo;
    }
    
} catch (PDOException $e) {
    // Log error
    error_log("Database connection failed: " . $e->getMessage());
    
    // Display error message in development, generic message in production
    if (defined('DEV_MODE') && DEV_MODE) {
        die("Connection failed: " . $e->getMessage());
    } else {
        die("A database error occurred. Please try again later.");
    }
}
