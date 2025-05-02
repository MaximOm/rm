<?php
require_once __DIR__ . '/admin_init.php';

// Get success message if any
$success = $_GET['success'] ?? '';

// Function to build category tree
function buildCategoryTree($categories, $parentId = 0, $level = 0) {
    $tree = [];
    foreach ($categories as $category) {
        if ($category['parent_id'] == $parentId) {
            $category['level'] = $level;
            $category['children'] = buildCategoryTree($categories, $category['id'], $level + 1);
            $tree[] = $category;
        }
    }
    return $tree;
}

// Fetch all categories
$stmt = $pdo->query("SELECT c.*, 
                            (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count,
                            p.name as parent_name 
                     FROM categories c 
                     LEFT JOIN categories p ON c.parent_id = p.id 
                     ORDER BY c.sort_order, c.name");
$categories = $stmt->fetchAll();

// Build category tree
$categoryTree = buildCategoryTree($categories);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f8; margin: 0; }
        .container { max-width: 1000px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 16px #d0d7de; padding: 32px; }
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .category-grid { border: 1px solid #e1e4e8; border-radius: 6px; overflow: hidden; }
        .category-row { display: flex; padding: 16px; border-bottom: 1px solid #e1e4e8; align-items: center; }
        .category-row:last-child { border-bottom: none; }
        .category-row:hover { background: #f6f8fa; }
        .category-name { flex: 2; display: flex; align-items: center; }
        .level-indicator { color: #888; margin-right: 8px; }
        .category-image { width: 50px; height: 50px; margin-right: 16px; }
        .category-image img { width: 100%; height: 100%; object-fit: cover; border-radius: 4px; }
        .category-info { flex: 2; }
        .parent-category { font-size: 0.9em; color: #666; }
        .product-count { flex: 1; color: #666; }
        .category-actions { flex: 1; display: flex; gap: 8px; justify-content: flex-end; }
        .btn { padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 14px; }
        .btn-primary { background: #0078d4; color: white; }
        .btn-secondary { background: #f0f0f0; color: #333; }
        .btn-danger { background: #d32f2f; color: white; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .empty-state { text-align: center; padding: 40px; color: #666; }
    </style>
</head>
<body>
<?php include 'admin_nav.php'; ?>

<div class="container">
    <div class="header-actions">
        <h1>Categories</h1>
        <a href="category_edit.php" class="btn btn-primary">Add Category</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (empty($categories)): ?>
        <div class="empty-state">
            <p>No categories found. Click "Add Category" to create one.</p>
        </div>
    <?php else: ?>
        <div class="category-grid">
            <?php 
            function renderCategoryTree($categories, $level = 0) {
                foreach ($categories as $category): ?>
                    <div class="category-row">
                        <div class="category-name">
                            <span class="level-indicator"><?= str_repeat('─', $level) ?></span>
                            <?php if (!empty($category['image_path'])): ?>
                                <div class="category-image">
                                    <img src="../<?= htmlspecialchars($category['image_path']) ?>" 
                                         alt="<?= htmlspecialchars($category['name']) ?>">
                                </div>
                            <?php endif; ?>
                            <div class="category-info">
                                <strong><?= htmlspecialchars($category['name']) ?></strong>
                                <?php if ($category['parent_name']): ?>
                                    <div class="parent-category">
                                        Parent: <?= htmlspecialchars($category['parent_name']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="product-count">
                            <?= $category['product_count'] ?> products
                        </div>
                        <div class="category-actions">
                            <a href="category_edit.php?id=<?= $category['id'] ?>" 
                               class="btn btn-secondary">Edit</a>
                            <?php if ($category['product_count'] == 0): ?>
                                <a href="category_delete.php?id=<?= $category['id'] ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this category?')">
                                    Delete
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php 
                    if (!empty($category['children'])) {
                        renderCategoryTree($category['children'], $level + 1);
                    }
                endforeach;
            }
            
            renderCategoryTree($categoryTree);
            ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
