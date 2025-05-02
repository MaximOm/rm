<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$error = '';
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid request';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Check for too many failed attempts
        $attempts = $_SESSION['login_attempts'] ?? 0;
        $last_attempt = $_SESSION['last_attempt'] ?? 0;
        
        if ($attempts >= 5 && (time() - $last_attempt) < 900) { // 15 minutes lockout
            $error = 'Too many failed attempts. Please try again later.';
        } else {
            if ((time() - $last_attempt) >= 900) {
                $_SESSION['login_attempts'] = 0;
                $attempts = 0;
            }
            
            // Validate credentials (using constant-time comparison)
            if (hash_equals(ADMIN_USERNAME, $username) && 
                password_verify($password, ADMIN_PASSWORD_HASH)) {
                
                // Reset login attempts on successful login
                unset($_SESSION['login_attempts']);
                unset($_SESSION['last_attempt']);
                
                // Set admin session
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_last_activity'] = time();
                
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
                
                header('Location: index.php');
                exit;
            } else {
                // Increment failed attempts
                $_SESSION['login_attempts'] = $attempts + 1;
                $_SESSION['last_attempt'] = time();
                $error = 'Invalid username or password';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background: #f4f6f8; 
            margin: 0; 
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 16px #d0d7de;
            width: 100%;
            max-width: 400px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            background: #0078d4;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            width: 100%;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
        }
        .btn:hover {
            background: #006abe;
        }
        .alert {
            background: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #ffcdd2;
        }
        h1 {
            margin: 0 0 30px;
            text-align: center;
            color: #333;
        }
        .back-to-site {
            text-align: center;
            margin-top: 20px;
        }
        .back-to-site a {
            color: #666;
            text-decoration: none;
        }
        .back-to-site a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Admin Login</h1>
        
        <?php if ($error): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="post" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="back-to-site">
            <a href="/">← Back to Website</a>
        </div>
    </div>
</body>
</html>
