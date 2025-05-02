<?php
require_once __DIR__ . '/admin_init.php';
require_once __DIR__ . '/admin_messages.php';

// Get category data if editing
$id = $_GET['id'] ?? null;
$category = [
    'name' => '', 'slug' => '', 'parent_id' => 0, 'description' => '',
    'meta_title' => '', 'meta_description' => '', 'sort_order' => 0
];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$category) {
        setMessage('Category not found', MSG_ERROR);
        header('Location: categories.php');
        exit;
    }
}

// Get all categories for parent selection
$stmt = $pdo->prepare("SELECT id, name, parent_id FROM categories WHERE id != ? ORDER BY name");
$stmt->execute([$id ?? 0]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build category tree for display
function buildSelectOptions($categories, $parent_id = 0, $prefix = '') {
    $options = [];
    foreach ($categories as $cat) {
        if ($cat['parent_id'] == $parent_id) {
            $options[] = [
                'id' => $cat['id'],
                'name' => $prefix . $cat['name']
            ];
            $options = array_merge($options, buildSelectOptions($categories, $cat['id'], $prefix . '── '));
        }
    }
    return $options;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $parent_id = (int)($_POST['parent_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    
    $errors = [];
    
    // Validate input
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    // Generate slug if empty
    if (empty($slug)) {
        $slug = createSafeFilename($name);
    }
    
    // Check for circular reference
    if ($id && $parent_id) {
        $current = $parent_id;
        while ($current) {
            if ($current == $id) {
                $errors[] = 'Invalid parent category - would create circular reference';
                break;
            }
            $stmt = $pdo->prepare("SELECT parent_id FROM categories WHERE id = ?");
            $stmt->execute([$current]);
            $current = $stmt->fetchColumn();
        }
    }
    
    // Handle image upload
    $image_path = $category['image_path'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        $upload_errors = validateFileUpload($_FILES['image']);
        if (empty($upload_errors)) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('category_') . '.' . $ext;
            $upload_path = CATEGORY_IMAGES_PATH . '/' . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image if exists
                if ($image_path && file_exists('../' . $image_path)) {
                    unlink('../' . $image_path);
                }
                $image_path = 'uploads/categories/' . $filename;
            } else {
                $errors[] = 'Failed to upload image';
            }
        } else {
            $errors = array_merge($errors, $upload_errors);
        }
    }
    
    if (empty($errors)) {
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE categories SET 
                    name = ?, slug = ?, parent_id = ?, description = ?,
                    meta_title = ?, meta_description = ?, sort_order = ?,
                    image_path = ?, updated_at = NOW()
                    WHERE id = ?");
                $stmt->execute([
                    $name, $slug, $parent_id, $description,
                    $meta_title, $meta_description, $sort_order,
                    $image_path, $id
                ]);
                setMessage('Category updated successfully');
            } else {
                $stmt = $pdo->prepare("INSERT INTO categories 
                    (name, slug, parent_id, description, meta_title, 
                     meta_description, sort_order, image_path, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                $stmt->execute([
                    $name, $slug, $parent_id, $description,
                    $meta_title, $meta_description, $sort_order, $image_path
                ]);
                setMessage('Category created successfully');
            }
            
            header('Location: categories.php');
            exit;
            
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                $errors[] = 'A category with this slug already exists';
            } else {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

$category_options = buildSelectOptions($categories);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $id ? 'Edit' : 'Add' ?> Category</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f8; margin: 0; }
        .container { max-width: 800px; margin: 40px auto; background: white; border-radius: 10px; box-shadow: 0 2px 16px #d0d7de; padding: 32px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: 500; }
        input:not([type="file"]), select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        textarea { min-height: 100px; }
        .preview-image { max-width: 200px; margin: 10px 0; }
        .btn { padding: 10px 20px; border-radius: 4px; border: none; cursor: pointer; font-size: 14px; font-weight: 500; }
        .btn-primary { background: #0078d4; color: white; }
        .btn-secondary { background: #f0f0f0; color: #333; }
        .action-buttons { display: flex; gap: 10px; margin-top: 30px; }
        .error-list { color: #d32f2f; margin-bottom: 20px; }
    </style>
</head>
<body>
<?php include 'admin_nav.php'; ?>
<?php echo getMessageStyles(); ?>

<div class="container">
    <h1><?= $id ? 'Edit' : 'Add' ?> Category</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="error-list">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php echo displayMessages(); ?>
    
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($category['name'] ?? '') ?>" required>
        </div>
        
        <div class="form-group">
            <label for="slug">Slug</label>
            <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($category['slug'] ?? '') ?>">
            <small>Leave empty to auto-generate from name</small>
        </div>
        
        <div class="form-group">
            <label for="parent_id">Parent Category</label>
            <select id="parent_id" name="parent_id">
                <option value="0">None (Top Level)</option>
                <?php foreach ($category_options as $option): ?>
                    <?php if ($option['id'] != $id): ?>
                        <option value="<?= $option['id'] ?>" 
                                <?= ($category['parent_id'] ?? 0) == $option['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($option['name']) ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="meta_title">Meta Title</label>
            <input type="text" id="meta_title" name="meta_title" 
                   value="<?= htmlspecialchars($category['meta_title'] ?? '') ?>">
        </div>
        
        <div class="form-group">
            <label for="meta_description">Meta Description</label>
            <textarea id="meta_description" name="meta_description"><?= htmlspecialchars($category['meta_description'] ?? '') ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="sort_order">Sort Order</label>
            <input type="number" id="sort_order" name="sort_order" 
                   value="<?= htmlspecialchars($category['sort_order'] ?? '0') ?>">
        </div>
        
        <div class="form-group">
            <label for="image">Category Image</label>
            <?php if (!empty($category['image_path'])): ?>
                <div>
                    <img src="../<?= htmlspecialchars($category['image_path']) ?>" 
                         alt="Current category image" class="preview-image">
                </div>
            <?php endif; ?>
            <input type="file" id="image" name="image" accept="image/*">
        </div>
        
        <div class="action-buttons">
            <button type="submit" class="btn btn-primary">Save Category</button>
            <a href="categories.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
document.getElementById('name').addEventListener('blur', function() {
    const slugInput = document.getElementById('slug');
    if (slugInput.value === '') {
        // Convert to lowercase, replace non-alphanumeric with hyphen
        let slug = this.value.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        slugInput.value = slug;
    }
});
</script>
</body>
</html>
