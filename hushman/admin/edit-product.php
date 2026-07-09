<?php
// ============================================================
// admin/edit-product.php — Edit Product
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$db         = getDB();
$productId  = (int)($_GET['id'] ?? 0);
$categories = getCategories();
$errors     = [];

$product = $db->prepare("SELECT * FROM products WHERE id = ?");
$product->execute([$productId]);
$product = $product->fetch();

if (!$product) {
    setFlash('error', 'Product not found.');
    header('Location: products.php'); exit;
}

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

    if (empty($name))  $errors[] = 'Product name is required.';
    if (!$categoryId)  $errors[] = 'Category is required.';
    if ($price <= 0)   $errors[] = 'Valid price is required.';

    $imageName = $product['image']; // keep existing by default

    // Handle new image upload
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg','jpeg','png','webp'];
        $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Image must be JPG, PNG or WEBP.';
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Image must be under 5MB.';
        } else {
            $newName   = 'product-' . time() . '.' . $ext;
            $uploadDir = __DIR__ . '/../assets/images/products/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newName)) {
                // Delete old image
                if ($product['image'] && file_exists($uploadDir . $product['image'])) {
                    @unlink($uploadDir . $product['image']);
                }
                $imageName = $newName;
            } else {
                $errors[] = 'Failed to upload image.';
            }
        }
    }

    if (empty($errors)) {
        $db->prepare("
            UPDATE products SET category_id=?, name=?, description=?, price=?, discount_percent=?,
            stock=?, sizes=?, colors=?, image=?, is_featured=?, updated_at=NOW()
            WHERE id=?
        ")->execute([$categoryId, $name, $description, $price, $discount, $stock, $sizes, $colors, $imageName, $isFeatured, $productId]);

        setFlash('success', 'Product updated successfully!');
        header('Location: products.php'); exit;
    }
}

$adminTitle = 'Edit Product';
require_once __DIR__ . '/header.php';
?>

<div class="row justify-content-center">
<div class="col-lg-9">
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="products.php" class="btn btn-outline-dark btn-sm"><i class="bi bi-arrow-left me-2"></i>Back to Products</a>
</div>

<?php if(!empty($errors)): ?>
<div class="alert alert-danger mb-4">
    <?php foreach($errors as $e): ?><div><i class="bi bi-x-circle me-2"></i><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<div class="row g-4">
    <div class="col-md-8">
        <div class="card-plain p-4 mb-4">
            <h6 class="fw-700 mb-3" style="font-size:13px;text-transform:uppercase;letter-spacing:.1em;color:var(--muted)">Product Information</h6>
            <div class="mb-3">
                <label class="form-label">Product Name *</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? $product['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($_POST['description'] ?? $product['description']) ?></textarea>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Price (৳) *</label>
                    <input type="number" name="price" class="form-control" step="0.01" value="<?= htmlspecialchars($_POST['price'] ?? $product['price']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Discount (%)</label>
                    <input type="number" name="discount_percent" class="form-control" step="0.1" min="0" max="100" value="<?= htmlspecialchars($_POST['discount_percent'] ?? $product['discount_percent']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Stock *</label>
                    <input type="number" name="stock" class="form-control" min="0" value="<?= htmlspecialchars($_POST['stock'] ?? $product['stock']) ?>" required>
                </div>
            </div>
        </div>
        <div class="card-plain p-4">
            <h6 class="fw-700 mb-3" style="font-size:13px;text-transform:uppercase;letter-spacing:.1em;color:var(--muted)">Variants</h6>
            <div class="mb-3">
                <label class="form-label">Available Sizes</label>
                <input type="text" name="sizes" class="form-control" value="<?= htmlspecialchars($_POST['sizes'] ?? $product['sizes']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Available Colors</label>
                <input type="text" name="colors" class="form-control" value="<?= htmlspecialchars($_POST['colors'] ?? $product['colors']) ?>">
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card-plain p-4 mb-4">
            <h6 class="fw-700 mb-3" style="font-size:13px;text-transform:uppercase;letter-spacing:.1em;color:var(--muted)">Category</h6>
            <select name="category_id" class="form-select" required>
                <?php foreach($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ((int)($_POST['category_id'] ?? $product['category_id'])) === $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <div class="mt-3">
                <div class="form-check">
                    <input type="checkbox" name="is_featured" class="form-check-input" id="isFeatured" <?= (isset($_POST['is_featured']) || $product['is_featured']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="isFeatured">Mark as Featured</label>
                </div>
            </div>
        </div>
        <div class="card-plain p-4">
            <h6 class="fw-700 mb-3" style="font-size:13px;text-transform:uppercase;letter-spacing:.1em;color:var(--muted)">Product Image</h6>
            <?php if($product['image']): ?>
            <div class="text-center mb-3">
                <img id="imagePreview" src="<?= SITE_URL ?>/assets/images/products/<?= htmlspecialchars($product['image']) ?>"
                     style="max-height:140px;max-width:100%;border-radius:6px;border:1px solid var(--border)"
                     onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.jpg'">
            </div>
            <?php else: ?>
            <img id="imagePreview" src="" style="display:none;max-height:140px;max-width:100%;border-radius:6px;margin-bottom:8px">
            <?php endif; ?>
            <label class="btn btn-outline-dark btn-sm w-100 mt-2">
                <i class="bi bi-cloud-upload me-2"></i>Change Image
                <input type="file" name="image" id="productImage" accept="image/*" style="display:none">
            </label>
            <div class="form-text mt-1">Leave blank to keep current image</div>
        </div>
    </div>
</div>

<div class="d-flex gap-3 mt-4">
    <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-circle me-2"></i>Update Product</button>
    <a href="products.php" class="btn btn-outline-dark btn-lg">Cancel</a>
</div>
</form>
</div>
</div>

<script>
document.getElementById('productImage').addEventListener('change', function() {
    const preview = document.getElementById('imagePreview');
    if (this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(this.files[0]);
    }
});
</script>
<?php require_once __DIR__ . '/footer.php'; ?>
