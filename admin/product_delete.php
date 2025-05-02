<?php
require_once __DIR__ . '/admin_init.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: products.php');
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Get product details and images before deletion
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        throw new Exception('Product not found');
    }
    
    // Get all image paths
    $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
    $stmt->execute([$id]);
    $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Delete product images from filesystem
    foreach ($images as $image_path) {
        $full_path = '../' . $image_path;
        if (file_exists($full_path)) {
            unlink($full_path);
        }
    }
    
    // Delete image records
    $stmt = $pdo->prepare("DELETE FROM product_images WHERE product_id = ?");
    $stmt->execute([$id]);
    
    // Delete the product
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    
    $pdo->commit();
    
    // Clean up empty product image directories
    $upload_dir = '../uploads/products/';
    if (file_exists($upload_dir . $product['sku'])) {
        rmdir($upload_dir . $product['sku']);
    }
    
    header('Location: products.php?success=' . urlencode('Product deleted successfully'));
    
} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: products.php?error=' . urlencode($e->getMessage()));
}
exit;
