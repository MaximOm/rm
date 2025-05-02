<?php
require_once __DIR__ . '/admin_init.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: categories.php');
    exit;
}

try {
    // Check if category exists and has no products
    $stmt = $pdo->prepare("SELECT c.*, 
                          (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count,
                          (SELECT COUNT(*) FROM categories WHERE parent_id = c.id) as child_count
                          FROM categories c WHERE c.id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch();

    if (!$category) {
        throw new Exception('Category not found');
    }

    if ($category['product_count'] > 0) {
        throw new Exception('Cannot delete category that has products');
    }

    if ($category['child_count'] > 0) {
        throw new Exception('Cannot delete category that has subcategories');
    }

    // Delete category image if exists
    if (!empty($category['image_path']) && file_exists('../' . $category['image_path'])) {
        unlink('../' . $category['image_path']);
    }

    // Delete the category
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: categories.php?success=' . urlencode('Category deleted successfully'));

} catch (Exception $e) {
    header('Location: categories.php?error=' . urlencode($e->getMessage()));
}
exit;
