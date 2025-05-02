<?php
/**
 * Application Initialization
 * 
 * This file initializes the application environment, loads configuration,
 * establishes database connections, and sets up essential components.
 */

// Prevent direct access to this file
defined('SECURE_ACCESS') or define('SECURE_ACCESS', true);

// Define initialization constant to prevent direct access to included files
defined('APP_INITIALIZED') or define('APP_INITIALIZED', true);

// Define application path constants
define('APP_PATH', dirname(__DIR__));
define('INCLUDES_PATH', APP_PATH . '/includes');
define('CONFIG_PATH', APP_PATH . '/config');

// Start or resume session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set error reporting based on environment
if (defined('APP_ENV') && APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set default timezone
date_default_timezone_set('UTC');

// Load configuration - updated path to match new structure
if (file_exists(CONFIG_PATH . '/config.php')) {
    require_once CONFIG_PATH . '/config.php';
} else {
    // Fallback to old location for backward compatibility during migration
    require_once INCLUDES_PATH . '/config.php';
}

// Load helper functions
require_once INCLUDES_PATH . '/functions.php';

// Load database connection
require_once INCLUDES_PATH . '/db_connection.php';

// Load database status checker if it exists
if (file_exists(INCLUDES_PATH . '/db_status.php')) {
    require_once INCLUDES_PATH . '/db_status.php';
}

// Define common template paths
define('TEMPLATE_PATH', APP_PATH . '/templates');
define('LAYOUT_PATH', TEMPLATE_PATH . '/layouts');
define('COMPONENT_PATH', TEMPLATE_PATH . '/components');
define('PAGE_PATH', TEMPLATE_PATH . '/pages');

// Create template directories if they don't exist
$templateDirs = [TEMPLATE_PATH, LAYOUT_PATH, COMPONENT_PATH, PAGE_PATH];
foreach ($templateDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Set up controllers directory path
define('CONTROLLER_PATH', INCLUDES_PATH . '/controllers');
if (!is_dir(CONTROLLER_PATH)) {
    mkdir(CONTROLLER_PATH, 0755, true);
}

// Models directory
define('MODEL_PATH', INCLUDES_PATH . '/models');
if (!is_dir(MODEL_PATH)) {
    mkdir(MODEL_PATH, 0755, true);
}

// Autoload models
function autoloadModels($className) {
    $file = MODEL_PATH . '/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}

spl_autoload_register('autoloadModels');

/**
 * Render a template with variables
 * 
 * @param string $template Path to template file
 * @param array $variables Variables to extract into template scope
 * @return string Rendered template content
 */
function renderTemplate($template, $variables = []) {
    // Extract variables into the current scope
    extract($variables);
    
    // Start output buffering
    ob_start();
    
    // Include the template file
    include $template;
    
    // Get the contents of the buffer and clean it
    $content = ob_get_clean();
    
    return $content;
}

/**
 * Render a complete page with layout
 * 
 * @param string $template Main content template file
 * @param array $variables Variables for the template
 * @param string $layout Layout template file
 * @return void Outputs the complete page
 */
function renderPage($template, $variables = [], $layout = 'default') {
    // Get main content
    $content = renderTemplate($template, $variables);
    
    // Determine layout path
    $layoutPath = LAYOUT_PATH . '/' . $layout . '.php';
    if (!file_exists($layoutPath)) {
        $layoutPath = LAYOUT_PATH . '/default.php';
    }
    
    // Add content to variables for layout
    $variables['content'] = $content;
    
    // Render layout with content
    echo renderTemplate($layoutPath, $variables);
    exit;
}

/**
 * Redirect to another URL with optional flash message
 * 
 * @param string $url URL to redirect to
 * @param string $message Optional message to display
 * @param string $type Message type (success, error, warning, info)
 * @param int $statusCode HTTP status code
 */
function redirect($url, $message = '', $type = 'info', $statusCode = 302) {
    if (!empty($message)) {
        $_SESSION['flash_message'] = [
            'message' => $message,
            'type' => $type
        ];
    }
    
    header('Location: ' . $url, true, $statusCode);
    exit;
}

// Initialize database connection
if (class_exists('Database')) {
    $db = Database::getInstance();
} else {
    // Log the error but don't die - this allows the application to continue
    // even if database is not available (useful for installation pages)
    error_log('Database class not found. Some functionality may be limited.');
}

/**
 * Check if the database is set up, redirect to installer if not
 */
if (function_exists('isDatabaseSetup') && !isDatabaseSetup() && basename($_SERVER['PHP_SELF']) !== 'install.php') {
    redirect('/install.php', 'Please complete the installation first.', 'warning');
}

// Register shutdown function for cleanup tasks
register_shutdown_function(function() {
    // Perform any cleanup tasks here
    // For example: close database connections, log execution time, etc.
});
