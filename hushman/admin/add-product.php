<?php
// ============================================================
// admin/add-product.php — Add New Product
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$db         = getDB();
$categories = getCategories();
$errors     = [];
$success    = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $categoryId  = (int)($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $price       = (float)($_POST['price'] ?? 0);
    $discount    = (float)($_POST['discount_percent'] ?? 0);
    $stock       = (int)($_POST['stock'] ?? 0);
    $sizes       = trim($_POST['sizes'] ?? '');
    $colors      = trim($_POST['colors'] ?? '');
    $isFeatured  = isset($_POST['is_featured']) ? 1 : 0;

    if (empty($name))        $errors[] = 'Product name is required.';
    if (!$categoryId)        $errors[] = 'Category is required.';
    if ($price <= 0)         $errors[] = 'Valid price is required.';
    if ($discount < 0 || $discount > 100) $errors[] = 'Discount must be 0–100.';

    // Slug generation
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    // Ensure unique slug
    $slugCheck = $db->prepare("SELECT id FROM products WHERE slug = ?");
    $slugCheck->execute([$slug]);
    if ($slugCheck->fetch()) {
        $slug .= '-' . time();
    }

    // Handle image upload
    $imageName = null;
    if (!empty($_FILES['image']['name'])) {
        $allowed  = ['jpg','jpeg','png','webp'];
        $ext      = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Image must be JPG, PNG, or WEBP format.';
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Image size must be under 5MB.';
        } else {
            $imageName = 'product-' . time() . '.' . $ext;
            $uploadDir = __DIR__ . '/../assets/images/products/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName)) {
                $errors[] = 'Failed to upload image.';
                $imageName = null;
            }
        }
    }

    if (empty($errors)) {
        $stmt = $db->prepare("
            INSERT INTO products (category_id, name, slug, description, price, discount_percent, stock, sizes, colors, image, is_featured)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$categoryId, $name, $slug, $description, $price, $discount, $stock, $sizes, $colors, $imageName, $isFeatured]);
        setFlash('success', 'Product "' . htmlspecialchars($name) . '" added successfully!');
        header('Location: products.php'); exit;
    }
}

$adminTitle = 'Add Product';
require_once __DIR__ . '/header.php';
?>

<div class="row justify-content-center">
<div class="col-lg-9">

<?php if(!empty($errors)): ?>
<div class="alert alert-danger mb-4">
    <?php foreach($errors as $e): ?><div><i class="bi bi-x-circle me-2"></i><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<div class="row g-4">

    <!-- Left: Main Details -->
    <div class="col-md-8">
        <div class="card-plain p-4 mb-4">
            <h6 class="fw-700 mb-3" style="font-size:13px;text-transform:uppercase;letter-spacing:.1em;color:var(--muted)">Product Information</h6>
            <div class="mb-3">
                <label class="form-label">Product Name *</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required placeholder="e.g. Classic Oxford Shirt">
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="5" placeholder="Detailed product description..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Price (৳) *</label>
                    <input type="number" name="price" class="form-control" step="0.01" min="0" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Discount (%)</label>
                    <input type="number" name="discount_percent" class="form-control" step="0.1" min="0" max="100" value="<?= htmlspecialchars($_POST['discount_percent'] ?? '0') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Stock Quantity *</label>
                    <input type="number" name="stock" class="form-control" min="0" value="<?= htmlspecialchars($_POST['stock'] ?? '0') ?>" required>
                </div>
            </div>
        </div>

        <div class="card-plain p-4">
            <h6 class="fw-700 mb-3" style="font-size:13px;text-transform:uppercase;letter-spacing:.1em;color:var(--muted)">Variants</h6>
            <div class="mb-3">
                <label class="form-label">Available Sizes <small class="text-muted">(comma-separated)</small></label>
                <input type="text" name="sizes" class="form-control" value="<?= htmlspecialchars($_POST['sizes'] ?? 'S,M,L,XL,XXL') ?>" placeholder="S,M,L,XL,XXL  or  28,30,32,34">
            </div>
            <div class="mb-3">
                <label class="form-label">Available Colors <small class="text-muted">(comma-separated)</small></label>
                <input type="text" name="colors" class="form-control" value="<?= htmlspecialchars($_POST['colors'] ?? '') ?>" placeholder="Black,White,Navy,Grey">
            </div>
        </div>
    </div>

    <!-- Right: Category, Image -->
    <div class="col-md-4">
        <div class="card-plain p-4 mb-4">
            <h6 class="fw-700 mb-3" style="font-size:13px;text-transform:uppercase;letter-spacing:.1em;color:var(--muted)">Category</h6>
            <select name="category_id" class="form-select" required>
                <option value="">Select Category</option>
                <?php foreach($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= (int)($_POST['category_id'] ?? 0) === $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <div class="mt-3">
                <div class="form-check">
                    <input type="checkbox" name="is_featured" class="form-check-input" id="isFeatured" <?= isset($_POST['is_featured']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="isFeatured">Mark as Featured</label>
                </div>
            </div>
        </div>

        <div class="card-plain p-4">
            <h6 class="fw-700 mb-3" style="font-size:13px;text-transform:uppercase;letter-spacing:.1em;color:var(--muted)">Product Image</h6>
            <div class="mb-3 text-center p-4 rounded" style="border:2px dashed var(--border);cursor:pointer" onclick="document.getElementById('productImage').click()">
                <img id="imagePreview" src="" style="max-height:160px;max-width:100%;border-radius:4px;display:none;margin-bottom:8px">
                <div id="uploadPlaceholder">
                    <i class="bi bi-cloud-upload fs-2 text-muted d-block mb-2"></i>
                    <span class="text-muted small">Click to upload image</span>
                </div>
                <input type="file" name="image" id="productImage" accept="image/*" style="display:none">
            </div>
            <div class="form-text">JPG, PNG or WEBP. Max 5MB. Recommended: 600×800px</div>
        </div>
    </div>

</div><!-- /.row -->

<div class="d-flex gap-3 mt-4">
    <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-circle me-2"></i>Add Product</button>
    <a href="products.php" class="btn btn-outline-dark btn-lg">Cancel</a>
</div>
</form>

</div>
</div>

<script>
document.getElementById('productImage').addEventListener('change', function() {
    const preview = document.getElementById('imagePreview');
    const placeholder = document.getElementById('uploadPlaceholder');
    if (this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        };
        reader.readAsDataURL(this.files[0]);
    }
});
</script>
<?php require_once __DIR__ . '/footer.php'; ?>
