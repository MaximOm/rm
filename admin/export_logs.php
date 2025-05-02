<?php
require_once __DIR__ . '/admin_init.php';
require_once __DIR__ . '/admin_logs.php';

// Get filter parameters
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$entityType = $_GET['type'] ?? '';
$action = $_GET['action'] ?? '';

// Build the query
$query = "
    SELECT 
        l.*,
        CONCAT(u.username) as username
    FROM admin_logs l
    LEFT JOIN users u ON l.user_id = u.id
    WHERE l.created_at BETWEEN :start_date AND DATE_ADD(:end_date, INTERVAL 1 DAY)
";

$params = [
    ':start_date' => $startDate,
    ':end_date' => $endDate
];

if ($entityType) {
    $query .= " AND l.entity_type = :entity_type";
    $params[':entity_type'] = $entityType;
}

if ($action) {
    $query .= " AND l.action = :action";
    $params[':action'] = $action;
}

$query .= " ORDER BY l.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If no format specified or viewing in browser, show the export form
if (!isset($_GET['format'])) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Export Activity Logs</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f8; margin: 0; }
            .container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
            .export-form {
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            .form-group {
                margin-bottom: 20px;
            }
            .form-group label {
                display: block;
                margin-bottom: 5px;
                font-weight: 500;
            }
            .form-group input,
            .form-group select {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-sizing: border-box;
            }
            .btn {
                background: #0078d4;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 1rem;
            }
            .btn:hover {
                background: #006cbd;
            }
            .preview {
                margin-top: 20px;
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            .preview-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
            }
            @media (max-width: 768px) {
                .container { padding: 20px; }
            }
        </style>
    </head>
    <body>
    <?php include 'admin_nav.php'; ?>

    <div class="container">
        <h1>Export Activity Logs</h1>
        
        <form method="get" class="export-form">
            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
            </div>
            
            <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
            </div>
            
            <div class="form-group">
                <label for="type">Entity Type</label>
                <select id="type" name="type">
                    <option value="">All Types</option>
                    <option value="product" <?= $entityType === 'product' ? 'selected' : '' ?>>Products</option>
                    <option value="category" <?= $entityType === 'category' ? 'selected' : '' ?>>Categories</option>
                    <option value="user" <?= $entityType === 'user' ? 'selected' : '' ?>>Users</option>
                    <option value="system" <?= $entityType === 'system' ? 'selected' : '' ?>>System</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="action">Action</label>
                <select id="action" name="action">
                    <option value="">All Actions</option>
                    <option value="CREATE" <?= $action === 'CREATE' ? 'selected' : '' ?>>Create</option>
                    <option value="UPDATE" <?= $action === 'UPDATE' ? 'selected' : '' ?>>Update</option>
                    <option value="DELETE" <?= $action === 'DELETE' ? 'selected' : '' ?>>Delete</option>
                    <option value="ERROR" <?= $action === 'ERROR' ? 'selected' : '' ?>>Error</option>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn">Apply Filters</button>
                <button type="submit" name="format" value="csv" class="btn">Export to CSV</button>
            </div>
        </form>
        
        <div class="preview">
            <div class="preview-header">
                <h2>Preview</h2>
                <span><?= count($logs) ?> records found</span>
            </div>
            <?php 
            if (empty($logs)): ?>
                <div style="text-align: center; padding: 20px; color: #666;">
                    No logs found matching your criteria.
                </div>
            <?php else:
                foreach (array_slice($logs, 0, 5) as $log) {
                    echo $logger->formatLogEntry($log);
                }
                if (count($logs) > 5): ?>
                    <div style="text-align: center; padding: 20px; color: #666;">
                        ... and <?= count($logs) - 5 ?> more records
                    </div>
                <?php endif;
            endif; ?>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// Export to CSV
if ($_GET['format'] === 'csv') {
    $filename = 'activity_logs_' . date('Y-m-d_His') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for proper Excel handling
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Write headers
    fputcsv($output, [
        'Date',
        'User',
        'Action',
        'Entity Type',
        'Entity ID',
        'Details',
        'IP Address',
        'User Agent'
    ]);
    
    // Write data
    foreach ($logs as $log) {
        fputcsv($output, [
            $log['created_at'],
            $log['username'] ?? 'System',
            $log['action'],
            $log['entity_type'],
            $log['entity_id'],
            $log['details'],
            $log['ip_address'],
            $log['user_agent']
        ]);
    }
    
    fclose($output);
    exit;
}