<?php
// ============================================================
// admin/categories.php — Category Management
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();
$db = getDB();
$errors = [];

// Add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    if (empty($name)) {
        $errors[] = 'Category name is required.';
    } else {
        $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));
        // Handle image upload
        $imgName = null;
        if (!empty($_FILES['image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $imgName = 'cat-' . time() . '.' . $ext;
                $dir = __DIR__ . '/../assets/images/categories/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                move_uploaded_file($_FILES['image']['tmp_name'], $dir . $imgName);
            }
        }
        try {
            $db->prepare("INSERT INTO categories (name, slug, description, image) VALUES (?,?,?,?)")->execute([$name, $slug, $desc, $imgName]);
            setFlash('success', 'Category added!');
        } catch (Exception $e) {
            $errors[] = 'Slug already exists. Try a different name.';
        }
        if (empty($errors)) { header('Location: categories.php'); exit; }
    }
}

// Delete category
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $db->prepare("UPDATE categories SET is_active = 0 WHERE id = ?")->execute([(int)$_GET['delete']]);
    setFlash('success', 'Category removed.');
    header('Location: categories.php'); exit;
}

$cats = $db->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id AND p.is_active=1 GROUP BY c.id ORDER BY c.name")->fetchAll();
$adminTitle = 'Categories';
require_once __DIR__ . '/header.php';
?>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="admin-table">
            <div class="p-3 fw-700" style="border-bottom:1px solid var(--border)">All Categories</div>
            <table class="table table-hover mb-0">
                <thead><tr><th>Image</th><th>Name</th><th>Products</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach($cats as $cat): ?>
                <tr>
                    <td><img src="<?= SITE_URL ?>/assets/images/categories/<?= htmlspecialchars($cat['image'] ?? '') ?>" style="width:44px;height:44px;object-fit:cover;border-radius:4px;background:var(--ivory)" onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.jpg'"></td>
                    <td><div class="fw-600"><?= htmlspecialchars($cat['name']) ?></div><small class="text-muted"><?= htmlspecialchars($cat['slug']) ?></small></td>
                    <td><span class="badge bg-light text-dark border"><?= $cat['product_count'] ?></span></td>
                    <td><span class="badge <?= $cat['is_active'] ? 'bg-success' : 'bg-secondary' ?>"><?= $cat['is_active'] ? 'Active' : 'Hidden' ?></span></td>
                    <td><a href="categories.php?delete=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-danger confirm-delete"><i class="bi bi-trash3"></i></a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card-plain p-4">
            <h6 class="fw-700 mb-3">Add New Category</h6>
            <?php if(!empty($errors)): ?>
            <div class="alert alert-danger mb-3"><?php foreach($errors as $e) echo '<div>'.htmlspecialchars($e).'</div>'; ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required placeholder="e.g. Hoodies"></div>
                <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                <div class="mb-3"><label class="form-label">Category Image</label><input type="file" name="image" class="form-control" accept="image/*"></div>
                <button type="submit" name="add_category" class="btn btn-primary w-100">Add Category</button>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
