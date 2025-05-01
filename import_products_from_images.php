<?php
// import_products_from_images.php
// Run this script ONCE to import marble slab products from images/products/block1, block2, block3

$baseDir = __DIR__ . '/images/products';
$blocks = ['block1', 'block2', 'block3'];

$products = [];
$productIndex = [];
$totalImages = 0;
$folderStats = [];
$unrecognized = [];

foreach ($blocks as $block) {
    $blockDir = $baseDir . "/$block";
    if (!is_dir($blockDir)) continue;
    $files = scandir($blockDir);
    $folderImageCount = 0;
    $folderProductCount = 0;
    $folderUnrecognized = 0;
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $filePath = "$blockDir/$file";
        if (!is_file($filePath)) continue;
        $totalImages++;
        $folderImageCount++;
        // Check for extra image pattern: slab-block STACK block
        if (preg_match('/^(\d+)-(\d+)\s*STACK\s*(\d+)/i', $file, $matches)) {
            $slabNumber = $matches[1];
            $extraIdx = $matches[2];
            $blockNumber = $matches[3];
            $sku = 'b'.$blockNumber.'s'.$slabNumber;
            if (isset($productIndex[$sku])) {
                $idx = $productIndex[$sku];
                if (!isset($products[$idx]['extra_images'])) {
                    $products[$idx]['extra_images'] = [];
                }
                $products[$idx]['extra_images'][] = "images/products/$block/$file";
            } else {
                $unrecognized[] = "images/products/$block/$file";
                $folderUnrecognized++;
            }
        }
        // Main product image pattern: slab STACK block
        else if (preg_match('/^(\d+)\s*STACK\s*(\d+)/i', $file, $matches)) {
            $slabNumber = $matches[1];
            $blockNumber = $matches[2];
            $sku = 'b'.$blockNumber.'s'.$slabNumber;
            $products[] = [
                'name' => "Slab #$slabNumber",
                'sku' => $sku,
                'block' => $block,
                'block_number' => $blockNumber,
                'image' => "images/products/$block/$file",
            ];
            $productIndex[$sku] = count($products) - 1;
            $folderProductCount++;
        } else {
            $unrecognized[] = "images/products/$block/$file";
            $folderUnrecognized++;
        }
    }
    $folderStats[$block] = [
        'images' => $folderImageCount,
        'products' => $folderProductCount,
        'unrecognized' => $folderUnrecognized
    ];
}

// Output result as JSON for review (or insert into DB as needed)
$json = json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// Output statistics and JSON in textarea
header('Content-Type: text/html; charset=utf-8');
echo "<h2>Import Summary</h2>";
echo "<ul>";
echo "<li><strong>Total folders scanned:</strong> " . count($folderStats) . "</li>";
echo "<li><strong>Total images in all folders:</strong> " . $totalImages . "</li>";
echo "<li><strong>Total products recognized:</strong> " . count($products) . "</li>";
echo "<li><strong>Total unrecognized images:</strong> " . count($unrecognized) . "</li>";
echo "</ul>";
echo "<h3>Per-folder statistics:</h3>";
echo "<table border='1' cellpadding='4' style='border-collapse:collapse;'>";
echo "<tr><th>Folder</th><th>Images</th><th>Products</th><th>Unrecognized</th></tr>";
foreach ($folderStats as $folder => $stat) {
    echo "<tr><td>" . htmlspecialchars($folder) . "</td><td>" . $stat['images'] . "</td><td>" . $stat['products'] . "</td><td>" . $stat['unrecognized'] . "</td></tr>";
}
echo "</table>";
if (count($unrecognized) > 0) {
    echo "<h4>Unrecognized files:</h4><ul>";
    foreach ($unrecognized as $file) {
        echo "<li>" . htmlspecialchars($file) . "</li>";
    }
    echo "</ul>";
}
echo '<h3>JSON Output</h3>';
echo '<textarea style="width:100%;height:400px;">' . htmlspecialchars($json) . '</textarea>';