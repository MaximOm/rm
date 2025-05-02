<?php
require_once __DIR__ . '/admin_init.php';
require_once '../includes/db.php';

// Fetch categories for dropdown
$catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

$id = $_GET['id'] ?? null;
$product = [
    'name' => '', 'slug' => '', 'category_id' => '', 'price' => '', 'stock' => '',
    'description' => '', 'technical_specs' => '', 'care_instructions' => '',
    'installation_recommendations' => '', 'sku' => '', 'images' => []
];
$editing = false;
$images = [];

if ($id) {
    // Fetch product data
    $stmt = $pdo->prepare("SELECT p.*, GROUP_CONCAT(pi.image_path) as images, 
                          GROUP_CONCAT(pi.is_primary) as image_types 
                          FROM products p 
                          LEFT JOIN product_images pi ON p.id = pi.product_id 
                          WHERE p.id = ? 
                          GROUP BY p.id");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product) {
        $editing = true;
        
        // Parse images
        $product['images'] = $product['images'] ? explode(',', $product['images']) : [];
        $product['image_types'] = $product['image_types'] ? explode(',', $product['image_types']) : [];
    }
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process product information
    if (isset($_POST['save_product'])) {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $category_id = $_POST['category_id'] ?? null;
        $price = $_POST['price'] ?? 0;
        $stock = $_POST['stock'] ?? 0;
        $description = trim($_POST['description'] ?? '');
        $technical_specs = trim($_POST['technical_specs'] ?? '');
        $care_instructions = trim($_POST['care_instructions'] ?? '');
        $installation_recommendations = trim($_POST['installation_recommendations'] ?? '');
        $sku = trim($_POST['sku'] ?? '');
        
        // Generate slug if empty
        if (empty($slug) && !empty($name)) {
            $slug = createSlug($name);
        }
        
        // Validate input
        if ($name === '' || !$category_id || $sku === '') {
            $error = 'Name, category, and SKU are required.';
        } else {
            // Check for duplicate slug
            $slugCheckSql = $editing ? 
                "SELECT id FROM products WHERE slug = ? AND id != ?" : 
                "SELECT id FROM products WHERE slug = ?";
            $slugParams = $editing ? [$slug, $id] : [$slug];
            
            $slugStmt = $pdo->prepare($slugCheckSql);
            $slugStmt->execute($slugParams);
            if ($slugStmt->rowCount() > 0) {
                $error = "A product with this slug already exists. Please choose a different slug.";
            } else {
                // Update or insert product
                if ($editing) {
                    $stmt = $pdo->prepare("UPDATE products SET 
                        name=?, slug=?, category_id=?, price=?, stock=?, 
                        description=?, technical_specs=?, care_instructions=?, 
                        installation_recommendations=?, sku=?, updated_at=NOW() 
                        WHERE id=?");
                    $stmt->execute([
                        $name, $slug, $category_id, $price, $stock, 
                        $description, $technical_specs, $care_instructions, 
                        $installation_recommendations, $sku, $id
                    ]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO products 
                        (name, slug, category_id, price, stock, description, 
                        technical_specs, care_instructions, installation_recommendations, 
                        sku, created_at, updated_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                    $stmt->execute([
                        $name, $slug, $category_id, $price, $stock, 
                        $description, $technical_specs, $care_instructions, 
                        $installation_recommendations, $sku
                    ]);
                    $id = $pdo->lastInsertId();
                }
                
                $success = "Product saved successfully.";
                
                // Redirect only if we're done with everything
                if (!isset($_FILES['new_images']) || $_FILES['new_images']['error'][0] == UPLOAD_ERR_NO_FILE) {
                    header('Location: products.php?success=' . urlencode($success));
                    exit;
                }
            }
        }
    }
    
    // Process image uploads
    if (isset($_FILES['new_images']) && $id && $_FILES['new_images']['error'][0] != UPLOAD_ERR_NO_FILE) {
        $uploadDir = '../uploads/products/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Get current maximum sort order
        $sortStmt = $pdo->prepare("SELECT MAX(sort_order) as max_order FROM product_images WHERE product_id = ?");
        $sortStmt->execute([$id]);
        $maxOrder = $ortStmt->fetch(PDO::FETCH_ASSOC)['max_order'] ?? 0;
        
        // Process each uploaded file
        for ($i = 0; $i < count($_FILES['new_images']['name']); $i++) {
            if ($_FILES['new_images']['error'][$i] == UPLOAD_ERR_OK) {
                $tmpName = $_FILES['new_images']['tmp_name'][$i];
                $originalName = $_FILES['new_images']['name'][$i];
                $fileExt = pathinfo($originalName, PATHINFO_EXTENSION);
                $uniqueName = uniqid('product_') . '.' . $fileExt;
                $targetFile = $uploadDir . $uniqueName;
                
                // Move uploaded file
                if (move_uploaded_file($tmpName, $targetFile)) {
                    // Save file info to database
                    $isPrimary = isset($_POST['is_primary']) && $_POST['is_primary'] == $i ? 1 : 0;
                    
                    // If this is set as primary, update all other images to non-primary
                    if ($isPrimary) {
                        $updateStmt = $pdo->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
                        $updateStmt->execute([$id]);
                    }
                    
                    // Insert new image
                    $maxOrder++;
                    $imgStmt = $pdo->prepare("INSERT INTO product_images 
                        (product_id, image_path, is_primary, sort_order, created_at) 
                        VALUES (?, ?, ?, ?, NOW())");
                    $imgStmt->execute([$id, 'uploads/products/' . $uniqueName, $isPrimary, $maxOrder]);
                } else {
                    $error .= "Failed to upload file: " . $originalName . ". ";
                }
            }
        }
        
        // Refresh images list
        $imgStmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC");
        $imgStmt->execute([$id]);
        $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $success = "Product and images saved successfully.";
    }
    
    // Handle image reordering and primary selection
    if (isset($_POST['update_images']) && $id) {
        foreach ($_POST['image_order'] as $imageId => $order) {
            $isPrimary = isset($_POST['primary_image']) && $_POST['primary_image'] == $imageId ? 1 : 0;
            
            $updateStmt = $pdo->prepare("UPDATE product_images SET sort_order = ?, is_primary = ? WHERE id = ? AND product_id = ?");
            $updateStmt->execute([$order, $isPrimary, $imageId, $id]);
        }
        
        // Refresh images list
        $imgStmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC");
        $imgStmt->execute([$id]);
        $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $success = "Image order updated successfully.";
    }
    
    // Handle image deletion
    if (isset($_POST['delete_image']) && $id) {
        $imageId = $_POST['delete_image'];
        
        // Get image path before deletion
        $pathStmt = $pdo->prepare("SELECT image_path FROM product_images WHERE id = ? AND product_id = ?");
        $pathStmt->execute([$imageId, $id]);
        $imagePath = $pathStmt->fetch(PDO::FETCH_COLUMN);
        
        // Delete from database
        $deleteStmt = $pdo->prepare("DELETE FROM product_images WHERE id = ? AND product_id = ?");
        $deleteStmt->execute([$imageId, $id]);
        
        // Delete file from filesystem if it exists
        if ($imagePath && file_exists('../' . $imagePath)) {
            unlink('../' . $imagePath);
        }
        
        // Refresh images list
        $imgStmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC");
        $imgStmt->execute([$id]);
        $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $success = "Image deleted successfully.";
    }
}

// Helper function to create slug
function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $editing ? 'Edit' : 'Add' ?> Product</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 0; }
        .container { max-width: 800px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 16px #d0d7de; padding: 32px; }
        .tabs { display: flex; margin-bottom: 20px; border-bottom: 1px solid #e1e4e8; }
        .tab { padding: 10px 20px; cursor: pointer; border-bottom: 2px solid transparent; }
        .tab.active { border-bottom-color: #0078d4; color: #0078d4; font-weight: 500; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        h1 { margin-top: 0; font-size: 1.8rem; color: #222; }
        h2 { font-size: 1.4rem; color: #444; margin-top: 30px; }
        form { display: flex; flex-direction: column; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: 500; }
        input, select, textarea { padding: 10px; border: 1px solid #cfd8dc; border-radius: 5px; width: 100%; font-size: 1rem; box-sizing: border-box; }
        textarea { height: 100px; resize: vertical; }
        .button { background: #0078d4; color: #fff; padding: 12px 20px; border: none; border-radius: 6px; font-size: 1rem; font-weight: 600; cursor: pointer; text-align: center; }
        .button:hover { background: #005fa3; }
        .button.secondary { background: #f0f0f0; color: #333; border: 1px solid #ddd; }
        .button.secondary:hover { background: #e0e0e0; }
        .button.danger { background: #d32f2f; color: white; }
        .button.danger:hover { background: #b71c1c; }
        .action-buttons { display: flex; gap: 10px; margin-top: 20px; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .alert-success { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .alert-danger { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .image-gallery { display: flex; flex-wrap: wrap; gap: 15px; margin-top: 20px; }
        .image-item { width: 150px; position: relative; border: 1px solid #ddd; padding: 10px; border-radius: 5px; }
        .image-item img { width: 100%; height: 100px; object-fit: cover; display: block; margin-bottom: 10px; }
        .image-controls { display: flex; flex-direction: column; gap: 5px; }
        .primary-badge { position: absolute; top: -10px; right: -10px; background: #4caf50; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 12px; }
        .field-row { display: flex; gap: 20px; }
        .field-column { flex: 1; }
        .upload-container { margin-top: 20px; border: 2px dashed #ccc; padding: 20px; text-align: center; border-radius: 5px; }
        .file-input-label { display: inline-block; padding: 10px 20px; background: #f0f0f0; border-radius: 5px; cursor: pointer; }
        input[type="file"] { display: none; }
        .hint { color: #666; font-size: 0.85rem; margin-top: 5px; }
    </style>
</head>
<body>
<?php include 'admin_nav.php'; ?>
<div class="container">
    <h1><?= $editing ? 'Edit' : 'Add' ?> Product</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <div class="tabs">
        <div class="tab active" data-tab="basic-info">Basic Info</div>
        <div class="tab" data-tab="details">Details</div>
        <?php if ($editing): ?>
            <div class="tab" data-tab="images">Images</div>
        <?php endif; ?>
    </div>
    
    <form method="post" enctype="multipart/form-data">
        <!-- Basic Info Tab -->
        <div class="tab-content active" id="basic-info">
            <div class="field-row">
                <div class="field-column">
                    <div class="form-group">
                        <label for="name">Name *</label>
                        <input id="name" name="name" value="<?= htmlspecialchars($product['name'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="field-column">
                    <div class="form-group">
                        <label for="slug">Slug</label>
                        <input id="slug" name="slug" value="<?= htmlspecialchars($product['slug'] ?? '') ?>">
                        <div class="hint">Leave empty to auto-generate from name. Used in URLs.</div>
                    </div>
                </div>
            </div>
            
            <div class="field-row">
                <div class="field-column">
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category_id" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="field-column">
                    <div class="form-group">
                        <label for="price">Price</label>
                        <input id="price" name="price" type="number" step="0.01" value="<?= htmlspecialchars($product['price'] ?? '0.00') ?>">
                    </div>
                </div>
                <div class="field-column">
                    <div class="form-group">
                        <label for="stock">Stock</label>
                        <input id="stock" name="stock" type="number" value="<?= htmlspecialchars($product['stock'] ?? '0') ?>">
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="sku">SKU *</label>
                <input id="sku" name="sku" value="<?= htmlspecialchars($product['sku'] ?? '') ?>" required>
            </div>
            
            <?php if (!$editing): ?>
                <div class="upload-container">
                    <p>You can add images after saving the product first.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Details Tab -->
        <div class="tab-content" id="details">
            <div class="form-group">
                <label for="technical_specs">Technical Specifications</label>
                <textarea id="technical_specs" name="technical_specs"><?= htmlspecialchars($product['technical_specs'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="care_instructions">Care Instructions</label>
                <textarea id="care_instructions" name="care_instructions"><?= htmlspecialchars($product['care_instructions'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="installation_recommendations">Installation Recommendations</label>
                <textarea id="installation_recommendations" name="installation_recommendations"><?= htmlspecialchars($product['installation_recommendations'] ?? '') ?></textarea>
            </div>
        </div>
        
        <!-- Images Tab (Only for editing) -->
        <?php if ($editing): ?>
            <div class="tab-content" id="images">
                <h2>Current Images</h2>
                
                <?php if (empty($images)): ?>
                    <p>No images have been added to this product yet.</p>
                <?php else: ?>
                    <div class="image-gallery">
                        <?php foreach ($images as $image): ?>
                            <div class="image-item">
                                <?php if ($image['is_primary']): ?>
                                    <div class="primary-badge" title="Primary Image">P</div>
                                <?php endif; ?>
                                
                                <img src="../<?= htmlspecialchars($image['image_path']) ?>" alt="Product Image">
                                
                                <div class="image-controls">
                                    <label>
                                        <input type="radio" name="primary_image" value="<?= $image['id'] ?>" <?= $image['is_primary'] ? 'checked' : '' ?>>
                                        Primary
                                    </label>
                                    
                                    <label>
                                        <input type="number" name="image_order[<?= $image['id'] ?>]" value="<?= $image['sort_order'] ?>" min="1" style="width: 60px;">
                                        Order
                                    </label>
                                    
                                    <button type="submit" name="delete_image" value="<?= $image['id'] ?>" class="button danger" style="font-size: 0.8rem; padding: 5px;" onclick="return confirm('Are you sure you want to delete this image?')">Delete</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="action-buttons" style="margin-top: 20px;">
                        <button type="submit" name="update_images" class="button">Update Image Settings</button>
                    </div>
                <?php endif; ?>
                
                <h2>Upload New Images</h2>
                <div class="upload-container">
                    <label class="file-input-label">
                        <input type="file" name="new_images[]" multiple accept="image/*" id="new_images">
                        Select Files
                    </label>
                    <div id="file-names" style="margin-top: 10px;"></div>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="button">Upload Images</button>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="action-buttons">
            <button type="submit" name="save_product" class="button">Save Product</button>
            <a href="products.php" class="button secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
    // Tab navigation
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Hide all tab content
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                
                // Show corresponding tab content
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Auto-generate slug from name
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');
        
        if (nameInput && slugInput) {
            nameInput.addEventListener('blur', function() {
                if (slugInput.value === '') {
                    // Convert to lowercase, replace non-alphanumeric with hyphen, remove duplicate hyphens
                    const slug = this.value.toLowerCase()
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/-+/g, '-')
                        .replace(/^-|-$/g, '');
                    
                    slugInput.value = slug;
                }
            });
        }
        
        // Show selected file names
        const fileInput = document.getElementById('new_images');
        const fileNames = document.getElementById('file-names');
        
        if (fileInput && fileNames) {
            fileInput.addEventListener('change', function() {
                let names = '';
                for (let i = 0; i < this.files.length; i++) {
                    names += `<div>${this.files[i].name}</div>`;
                }
                fileNames.innerHTML = names;
            });
        }
    });
</script>
</body>
</html>
