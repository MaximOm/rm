<?php
/**
 * Application Initialization
 * 
 * This file initializes the application environment, loads configuration,
 * establishes database connections, and sets up essential components.
 */

// Define application path if not already defined
if (!defined('APP_PATH')) {
    define('APP_PATH', dirname(__DIR__));
}

// Define initialization constant to prevent direct access to config
if (!defined('APP_INITIALIZED')) {
    define('APP_INITIALIZED', true);
}

// Start or resume session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set error reporting based on environment
if (defined('APP_ENV') && APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Load configuration - updated path to match new structure
if (file_exists(APP_PATH . '/config/config.php')) {
    require_once APP_PATH . '/config/config.php';
} else {
    // Fallback to old location for backward compatibility during migration
    require_once APP_PATH . '/includes/config.php';
}

// Load database connection
require_once APP_PATH . '/includes/db_connection.php';

// Load database status checker
if (file_exists(APP_PATH . '/includes/db_status.php')) {
    require_once APP_PATH . '/includes/db_status.php';
}

// Define common template paths - ensure directories exist
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

// Load helper functions
require_once APP_PATH . '/includes/functions.php';

// Set up controllers directory path
define('CONTROLLER_PATH', APP_PATH . '/includes/controllers');
if (!is_dir(CONTROLLER_PATH)) {
    mkdir(CONTROLLER_PATH, 0755, true);
}

// Models directory
define('MODEL_PATH', APP_PATH . '/includes/models');
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
$db = Database::getInstance();

/**
 * Check if the database is set up, redirect to installer if not
 */
if (function_exists('isDatabaseSetup') && !isDatabaseSetup() && basename($_SERVER['PHP_SELF']) !== 'install.php') {
    redirect('/install.php', 'Please complete the installation first.', 'warning');
}

// Load any additional application components
// This can be extended as needed for plugins, modules, etc.
