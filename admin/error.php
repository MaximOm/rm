<?php
require_once __DIR__ . '/admin_init.php';
require_once __DIR__ . '/admin_logs.php';

$error = $_SESSION['admin_error'] ?? null;
unset($_SESSION['admin_error']);

if (!$error) {
    header('Location: index.php');
    exit;
}

// Log the error if it hasn't been logged yet
if (!isset($error['logged']) || !$error['logged']) {
    $logger->logError('system', null, $error['message'], $error['trace'] ?? null);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Error Occurred</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background: #f4f6f8; 
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .container { 
            max-width: 800px; 
            margin: 40px auto;
            padding: 0 20px;
            width: 100%;
            box-sizing: border-box;
        }
        .error-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .error-icon {
            color: #d32f2f;
            font-size: 48px;
            text-align: center;
            margin-bottom: 20px;
        }
        .error-title {
            color: #d32f2f;
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }
        .error-message {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            line-height: 1.5;
        }
        .error-details {
            background: #f8f8f8;
            padding: 20px;
            border-radius: 4px;
            font-family: monospace;
            white-space: pre-wrap;
            word-break: break-all;
            margin-top: 20px;
            max-height: 300px;
            overflow-y: auto;
        }
        .error-actions {
            text-align: center;
            margin-top: 30px;
        }
        .btn {
            background: #0078d4;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            display: inline-block;
            margin: 0 10px;
        }
        .btn:hover {
            background: #006cbd;
        }
        .btn.secondary {
            background: #666;
        }
        .btn.secondary:hover {
            background: #555;
        }
        @media (max-width: 768px) {
            .container { padding: 20px; }
        }
    </style>
</head>
<body>
<?php include 'admin_nav.php'; ?>

<div class="container">
    <div class="error-card">
        <div class="error-icon">⚠️</div>
        <h1 class="error-title">An Error Occurred</h1>
        
        <div class="error-message">
            <?= htmlspecialchars($error['message']) ?>
        </div>
        
        <?php if (isset($error['trace']) && $_SESSION['admin_user']['is_admin']): ?>
            <div class="error-details">
                <?= htmlspecialchars($error['trace']) ?>
            </div>
        <?php endif; ?>
        
        <div class="error-actions">
            <a href="javascript:history.back()" class="btn secondary">Go Back</a>
            <a href="index.php" class="btn">Go to Dashboard</a>
        </div>
    </div>
</div>

<?php if ($_SESSION['admin_user']['is_admin']): ?>
    <div class="container">
        <div class="error-card">
            <h2>Recent System Errors</h2>
            <?php
            $recentErrors = $logger->getRecentErrors(5);
            if (empty($recentErrors)): ?>
                <div style="text-align: center; padding: 20px; color: #666;">
                    No recent system errors.
                </div>
            <?php else:
                foreach ($recentErrors as $errorLog) {
                    echo $logger->formatLogEntry($errorLog);
                }
            endif;
            ?>
            
            <div style="text-align: right; margin-top: 20px;">
                <a href="logs.php?action=ERROR" class="btn secondary">View All Errors</a>
            </div>
        </div>
    </div>
<?php endif; ?>

</body>
</html>