<?php
define('BASE_PATH', __DIR__);

$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$query = parse_url($request, PHP_URL_QUERY);

// Remove trailing slashes and get the last segment
$path = rtrim($path, '/');
$segments = explode('/', $path);

// Keep original query parameters
if ($query) {
    $_SERVER['QUERY_STRING'] = $query;
    parse_str($query, $_GET);
}

// Handle API routes
if (strpos($path, '/api/') === 0) {
    $apiPath = substr($path, 5); // Remove /api/ prefix
    switch ($apiPath) {
        case 'callback':
            require __DIR__ . '/send_callback.php';
            break;
        case 'send-email':
            require __DIR__ . '/send_email.php';
            break;
        case 'subscribe':
            require __DIR__ . '/subscribe.php';
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'API endpoint not found']);
            exit;
    }
    exit;
}

// Handle main routes
switch ($path) {
    case '':
    case '/':
        require __DIR__ . '/index.php';
        break;
    case '/about':
        require __DIR__ . '/about.php';
        break;
    case '/products':
        require __DIR__ . '/products.php';
        break;
    case '/categories':
        require __DIR__ . '/categories.php';
        break;
    case '/product':
        require __DIR__ . '/product.php';
        break;
    case '/quarry':
        require __DIR__ . '/quarry.php';
        break;
    case '/mission':
        require __DIR__ . '/mission.php';
        break;
    case '/contacts':
        require __DIR__ . '/contacts.php';
        break;
    case '/cart':
        require __DIR__ . '/cart.php';
        break;
    case '/wishlist':
        require __DIR__ . '/wishlist.php';
        break;
    case '/login':
        require __DIR__ . '/login.php';
        break;
    case '/register':
        require __DIR__ . '/register.php';
        break;
    case '/account':
        require __DIR__ . '/account.php';
        break;
    case '/logout':
        require __DIR__ . '/logout.php';
        break;
    case '/privacy-policy':
        require __DIR__ . '/privacy-policy.php';
        break;
    case '/terms-of-service':
        require __DIR__ . '/terms-of-service.php';
        break;
    default:
        // Check if it's a .php file access attempt
        if (preg_match('/\.php$/', end($segments))) {
            // Redirect to clean URL if possible
            $clean_url = str_replace('.php', '', $path);
            header('Location: ' . $clean_url);
            exit;
        }
        http_response_code(404);
        require __DIR__ . '/404.php';
        break;
}