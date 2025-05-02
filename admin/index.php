<?php
define('APP_INITIALIZED', true);
require_once __DIR__ . '/admin_init.php';
require_once __DIR__ . '/admin_preferences.php';
require_once __DIR__ . '/admin_logs.php';

// Get quick statistics
$statsStmt = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM products) as total_products,
        (SELECT COUNT(*) FROM categories) as total_categories,
        (SELECT COUNT(*) FROM admin_logs WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)) as today_actions,
        (SELECT COUNT(*) FROM admin_logs WHERE action = 'ERROR' AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)) as today_errors
");
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Get recent activity
$recentLogs = $logger->getRecentLogs(10);

// Get low stock products (less than 5 items)
$lowStockStmt = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.stock < 5 
    ORDER BY p.stock ASC 
    LIMIT 5
");
$lowStockProducts = $lowStockStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f8; margin: 0; }
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 600;
            margin: 10px 0;
            color: #0078d4;
        }
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
        }
        .dashboard-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0;
        }
        .view-all {
            color: #0078d4;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .low-stock-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .low-stock-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        .low-stock-item:last-child {
            border-bottom: none;
        }
        .product-info {
            flex: 1;
        }
        .product-name {
            font-weight: 500;
            margin-bottom: 4px;
        }
        .product-category {
            font-size: 0.9rem;
            color: #666;
        }
        .stock-count {
            background: #fff2f2;
            color: #d32f2f;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 40px;
        }
        .quick-action {
            background: #0078d4;
            color: white;
            text-decoration: none;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            transition: all 0.2s;
        }
        .quick-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        @media (max-width: 768px) {
            .container { padding: 20px; }
            .dashboard-grid { grid-template-columns: 1fr; }
        }
    </style>
    <?php echo $logger->getLogStyles(); ?>
</head>
<body>
<?php include 'admin_nav.php'; ?>

<div class="container">
    <h1>Dashboard</h1>
    
    <div class="quick-actions">
        <a href="product_edit.php" class="quick-action">Add Product</a>
        <a href="category_edit.php" class="quick-action">Add Category</a>
        <a href="products.php" class="quick-action">Manage Products</a>
        <a href="categories.php" class="quick-action">Manage Categories</a>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Products</div>
            <div class="stat-value"><?= number_format($stats['total_products']) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Categories</div>
            <div class="stat-value"><?= number_format($stats['total_categories']) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Actions Today</div>
            <div class="stat-value"><?= number_format($stats['today_actions']) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Errors Today</div>
            <div class="stat-value"><?= number_format($stats['today_errors']) ?></div>
        </div>
    </div>
    
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <div class="card-header">
                <h2 class="card-title">Recent Activity</h2>
                <a href="logs.php" class="view-all">View All</a>
            </div>
            <?php 
            if (empty($recentLogs)): ?>
                <div style="text-align: center; padding: 20px; color: #666;">
                    No recent activity.
                </div>
            <?php else:
                foreach ($recentLogs as $log) {
                    echo $logger->formatLogEntry($log);
                }
            endif; ?>
        </div>
        
        <div class="dashboard-card">
            <div class="card-header">
                <h2 class="card-title">Low Stock Alert</h2>
                <a href="products.php?filter=low_stock" class="view-all">View All</a>
            </div>
            <?php if (empty($lowStockProducts)): ?>
                <div style="text-align: center; padding: 20px; color: #666;">
                    No products with low stock.
                </div>
            <?php else: ?>
                <ul class="low-stock-list">
                    <?php foreach ($lowStockProducts as $product): ?>
                        <li class="low-stock-item">
                            <div class="product-info">
                                <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
                                <div class="product-category"><?= htmlspecialchars($product['category_name']) ?></div>
                            </div>
                            <span class="stock-count"><?= $product['stock'] ?> left</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
