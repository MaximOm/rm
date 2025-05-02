<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set proper error reporting for admin area
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/admin_logs.php';

// Initialize logger
$logger = AdminLogger::getInstance($pdo);

// Check session timeout (30 minutes)
if (isset($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity'] > 1800)) {
    // Session expired
    $logger->log('LOGOUT', 'session', null, 'Session expired');
    session_unset();
    session_destroy();
    header('Location: login.php?error=' . urlencode('Session expired. Please login again.'));
    exit;
}

// Update last activity time
$_SESSION['admin_last_activity'] = time();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Set error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    global $logger;
    
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return false;
    }

    $error_type = match($errno) {
        E_ERROR => 'Fatal Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict Notice',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated',
        default => 'Unknown Error'
    };

    $message = "$error_type: $errstr in $errfile on line $errline";
    error_log($message);
    
    // Log critical errors
    if (in_array($errno, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        $logger->log('ERROR', 'system', null, $message);
    }

    if ($errno == E_USER_ERROR) {
        exit(1);
    }

    return true;
}
set_error_handler("customErrorHandler");

// Define upload paths with proper permissions check
define('UPLOAD_BASE_PATH', __DIR__ . '/../uploads');
define('PRODUCT_IMAGES_PATH', UPLOAD_BASE_PATH . '/products');
define('CATEGORY_IMAGES_PATH', UPLOAD_BASE_PATH . '/categories');

// Create upload directories if they don't exist
$directories = [UPLOAD_BASE_PATH, PRODUCT_IMAGES_PATH, CATEGORY_IMAGES_PATH];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (!mkdir($dir, 0755, true)) {
            $error = "Failed to create directory: $dir";
            error_log($error);
            $logger->log('ERROR', 'system', null, $error);
            throw new Exception("Failed to create required directories. Please check permissions.");
        }
    } elseif (!is_writable($dir)) {
        $error = "Directory not writable: $dir";
        error_log($error);
        $logger->log('ERROR', 'system', null, $error);
        throw new Exception("Upload directory is not writable. Please check permissions.");
    }
}

// Function to safely delete directory and its contents
function rrmdir($dir) {
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                if (is_dir("$dir/$file")) {
                    rrmdir("$dir/$file");
                } else {
                    unlink("$dir/$file");
                }
            }
        }
        rmdir($dir);
        return true;
    }
    return false;
}

// Function to create safe filename
function createSafeFilename($original) {
    // Remove any character that isn't a letter, number, dot, or hyphen
    $safe = preg_replace('/[^a-zA-Z0-9.-]/', '-', $original);
    // Remove any multiple hyphens
    $safe = preg_replace('/-+/', '-', $safe);
    // Remove leading/trailing hyphens
    $safe = trim($safe, '-');
    return $safe;
}

// Function to validate file upload
function validateFileUpload($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/webp']) {
    $errors = [];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = match($file['error']) {
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
            default => 'Unknown upload error'
        };
        return $errors;
    }
    
    // Check file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        $errors[] = 'File size must be less than 5MB';
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        $errors[] = 'Invalid file type. Allowed types: ' . implode(', ', array_map(fn($type) => strtoupper(substr($type, 6)), $allowedTypes));
    }
    
    return $errors;
}