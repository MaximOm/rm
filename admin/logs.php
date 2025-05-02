<?php
require_once __DIR__ . '/admin_init.php';
require_once __DIR__ . '/admin_preferences.php';
require_once __DIR__ . '/admin_logs.php';

// Initialize logger
$logger = AdminLogger::getInstance($pdo);

// Get search parameters
$searchQuery = $_GET['search'] ?? '';
$entityType = $_GET['type'] ?? '';
$userId = $_GET['user_id'] ?? '';
$page = max(1, $_GET['page'] ?? 1);
$perPage = 50;

// Fetch logs based on filters
if ($searchQuery) {
    $logs = $logger->searchLogs($searchQuery, $perPage);
} elseif ($entityType) {
    $logs = $logger->getLogsByEntityType($entityType, $perPage);
} elseif ($userId) {
    $logs = $logger->getLogsByUser($userId, $perPage);
} else {
    $logs = $logger->getRecentLogs($perPage);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Activity Logs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f8; margin: 0; }
        .container { max-width: 1200px; margin: 40px auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .search-bar { 
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .search-form {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        .search-group {
            flex: 1;
            min-width: 200px;
        }
        .search-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .search-group input,
        .search-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .search-actions {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            border: none;
            font-weight: 500;
            cursor: pointer;
        }
        .btn-primary {
            background: #0078d4;
            color: white;
        }
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }
        .export-btn {
            margin-left: auto;
        }
        @media (max-width: 768px) {
            .container { padding: 16px; }
            .search-form { flex-direction: column; }
            .search-group { min-width: 100%; }
            .search-actions { width: 100%; justify-content: space-between; }
        }
    </style>
    <?php echo $logger->getLogStyles(); ?>
</head>
<body>
<?php include 'admin_nav.php'; ?>

<div class="container">
    <div class="header">
        <h1>Activity Logs</h1>
        <a href="export_logs.php" class="btn btn-secondary export-btn">Export Logs</a>
    </div>
    
    <div class="search-bar">
        <form method="get" class="search-form">
            <div class="search-group">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" 
                       value="<?= htmlspecialchars($searchQuery) ?>" 
                       placeholder="Search logs...">
            </div>
            
            <div class="search-group">
                <label for="type">Filter by Type</label>
                <select id="type" name="type">
                    <option value="">All Types</option>
                    <option value="product" <?= $entityType === 'product' ? 'selected' : '' ?>>Products</option>
                    <option value="category" <?= $entityType === 'category' ? 'selected' : '' ?>>Categories</option>
                    <option value="user" <?= $entityType === 'user' ? 'selected' : '' ?>>Users</option>
                </select>
            </div>
            
            <div class="search-actions">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="logs.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
    
    <div class="logs-container">
        <?php 
        if (empty($logs)): ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                No log entries found.
            </div>
        <?php else:
            foreach ($logs as $log) {
                echo $logger->formatLogEntry($log);
            }
        endif; ?>
    </div>
</div>
</body>
</html>