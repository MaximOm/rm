<?php
define('APP_INITIALIZED', true);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$jsonFile = __DIR__ . '/db/products.json';
if (!file_exists($jsonFile)) {
    die("JSON file not found.\n");
}

$json = file_get_contents($jsonFile);
$products = json_decode($json, true);

if (!$products) {
    die("Failed to decode JSON.\n");
}

$categoryCache = [];

// Prepare statements
$categorySelect = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
$categoryInsert = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
$productInsert = $pdo->prepare("INSERT INTO products (name, slug, category_id) VALUES (?, ?, ?)");
$imageInsert = $pdo->prepare("INSERT INTO product_images (product_id, image, is_primary) VALUES (?, ?, 1)");

foreach ($products as $product) {
    $categoryName = isset($product['block']) ? $product['block'] : 'Uncategorized';

    // Check if category is cached
    if (isset($categoryCache[$categoryName])) {
        $categoryId = $categoryCache[$categoryName];
    } else {
        // Check if category exists
        $categorySelect->execute([$categoryName]);
        $cat = $categorySelect->fetch();
        if ($cat) {
            $categoryId = $cat['id'];
        } else {
            $categoryInsert->execute([$categoryName]);
            $categoryId = $pdo->lastInsertId();
        }
        $categoryCache[$categoryName] = $categoryId;
    }

    try {
        // Insert product (use 'sku' from JSON as 'slug')
        $productInsert->execute([
            $product['name'] ?? '',
            $product['sku'] ?? '',
            $categoryId
        ]);
        $productId = $pdo->lastInsertId();

        // Insert main image if exists
        if (!empty($product['image'])) {
            $imageInsert->execute([
                $productId,
                $product['image']
            ]);
        }

        // Insert extra images if exist
        if (!empty($product['extra_images']) && is_array($product['extra_images'])) {
            $extraImageInsert = $pdo->prepare("INSERT INTO product_images (product_id, image, is_primary) VALUES (?, ?, 0)");
            foreach ($product['extra_images'] as $extraImage) {
                if (!empty($extraImage)) {
                    $extraImageInsert->execute([
                        $productId,
                        $extraImage
                    ]);
                }
            }
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            // Duplicate slug, skip this product
            continue;
        } else {
            throw $e;
        }
    }
}

echo "Import complete.\n";