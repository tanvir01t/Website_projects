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

// Edit category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    $id   = (int)($_POST['edit_id'] ?? 0);
    $name = trim($_POST['edit_name'] ?? '');
    $desc = trim($_POST['edit_description'] ?? '');
    if (empty($name) || $id <= 0) {
        setFlash('error', 'Invalid data.');
    } else {
        $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));
        $imgName = null;
        $hasNewImage = !empty($_FILES['edit_image']['name']);
        if ($hasNewImage) {
            $ext = strtolower(pathinfo($_FILES['edit_image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $imgName = 'cat-' . time() . '.' . $ext;
                $dir = __DIR__ . '/../assets/images/categories/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                move_uploaded_file($_FILES['edit_image']['tmp_name'], $dir . $imgName);
            }
        }
        try {
            if ($hasNewImage && $imgName) {
                $db->prepare("UPDATE categories SET name=?, slug=?, description=?, image=? WHERE id=?")
                   ->execute([$name, $slug, $desc, $imgName, $id]);
            } else {
                $db->prepare("UPDATE categories SET name=?, slug=?, description=? WHERE id=?")
                   ->execute([$name, $slug, $desc, $id]);
            }
            setFlash('success', 'Category updated!');
        } catch (Exception $e) {
            setFlash('error', 'Slug already exists. Try a different name.');
        }
    }
    header('Location: categories.php'); exit;
}

// Toggle active/hidden
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $db->prepare("UPDATE categories SET is_active = 1 - is_active WHERE id = ?")
       ->execute([(int)$_GET['toggle']]);
    setFlash('success', 'Category status updated.');
    header('Location: categories.php'); exit;
}

// Delete (permanent) — blocked if category has active products
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND is_active = 1");
    $stmt->execute([$id]);
    $cnt = (int)$stmt->fetchColumn();

    if ($cnt > 0) {
        // Redirect back with blocked flag — JS will open warning modal
        header('Location: categories.php?blocked=' . $id . '&pcount=' . $cnt); exit;
    }

    // Safe: remove image then delete row
    $row = $db->prepare("SELECT image FROM categories WHERE id = ?");
    $row->execute([$id]);
    $existing = $row->fetch();
    if (!empty($existing['image'])) {
        $imgPath = __DIR__ . '/../assets/images/categories/' . $existing['image'];
        if (file_exists($imgPath)) unlink($imgPath);
    }
    $db->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
    setFlash('success', 'Category permanently deleted.');
    header('Location: categories.php'); exit;
}

$cats = $db->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id AND p.is_active=1 GROUP BY c.id ORDER BY c.name")->fetchAll();
$adminTitle = 'Categories';
require_once __DIR__ . '/header.php';
?>

<div class="row g-4">
    <!-- Category List -->
    <div class="col-lg-7">
        <div class="admin-table">
            <div class="p-3 fw-700" style="border-bottom:1px solid var(--border)">All Categories</div>
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Image</th><th>Name</th><th>Products</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach($cats as $cat): ?>
                <tr>
                    <td>
                        <img src="<?= SITE_URL ?>/assets/images/categories/<?= htmlspecialchars($cat['image'] ?? '') ?>"
                             style="width:44px;height:44px;object-fit:cover;border-radius:4px;background:var(--ivory)"
                             onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.jpg'">
                    </td>
                    <td>
                        <div class="fw-600"><?= htmlspecialchars($cat['name']) ?></div>
                        <small class="text-muted"><?= htmlspecialchars($cat['slug']) ?></small>
                        <?php if (!empty($cat['description'])): ?>
                        <div class="text-muted" style="font-size:11px;margin-top:2px;max-width:200px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis">
                            <?= htmlspecialchars($cat['description']) ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border"><?= $cat['product_count'] ?></span>
                    </td>
                    <td>
                        <a href="categories.php?toggle=<?= $cat['id'] ?>"
                           class="badge text-decoration-none <?= $cat['is_active'] ? 'bg-success' : 'bg-secondary' ?>"
                           style="cursor:pointer"
                           title="Click to <?= $cat['is_active'] ? 'hide' : 'activate' ?>">
                            <i class="bi bi-<?= $cat['is_active'] ? 'eye' : 'eye-slash' ?> me-1"></i>
                            <?= $cat['is_active'] ? 'Active' : 'Hidden' ?>
                        </a>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    title="Edit"
                                    onclick='openEditModal(<?= json_encode([
                                        "id"          => $cat["id"],
                                        "name"        => $cat["name"],
                                        "description" => $cat["description"] ?? "",
                                        "image"       => $cat["image"] ?? ""
                                    ]) ?>)'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <!-- Delete: pass product_count to JS for client-side check -->
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    title="Delete"
                                    onclick="confirmDelete(<?= $cat['id'] ?>, <?= (int)$cat['product_count'] ?>, '<?= htmlspecialchars(addslashes($cat['name'])) ?>')">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Category Form -->
    <div class="col-lg-5">
        <div class="card-plain p-4">
            <h6 class="fw-700 mb-3">Add New Category</h6>
            <?php if(!empty($errors)): ?>
            <div class="alert alert-danger mb-3">
                <?php foreach($errors as $e) echo '<div>' . htmlspecialchars($e) . '</div>'; ?>
            </div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Hoodies">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Short description for this category..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Category Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <button type="submit" name="add_category" class="btn btn-primary w-100">
                    <i class="bi bi-plus-circle me-1"></i> Add Category
                </button>
            </form>
        </div>
    </div>
</div>


<!-- ── Edit Category Modal ───────────────────────────────────── -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px;border:1px solid var(--border)">
            <div class="modal-header" style="border-bottom:1px solid var(--border)">
                <h6 class="modal-title fw-700"><i class="bi bi-pencil-square me-2"></i>Edit Category</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="edit_name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="edit_description" id="edit_description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Image</label>
                        <div class="mb-2">
                            <img id="edit_current_image" src="" alt="Current image"
                                 style="width:64px;height:64px;object-fit:cover;border-radius:6px;border:1px solid var(--border)"
                                 onerror="this.style.display='none'">
                        </div>
                        <label class="form-label">Replace Image <span class="text-muted">(optional)</span></label>
                        <input type="file" name="edit_image" class="form-control" accept="image/*">
                        <div class="form-text">Leave blank to keep the current image.</div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--border)">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_category" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ── Delete Warning Modal (has products) ───────────────────── -->
<div class="modal fade" id="deleteBlockedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px;border:1px solid var(--border)">
            <div class="modal-header" style="border-bottom:1px solid var(--border);background:#fff8f8">
                <h6 class="modal-title fw-700 text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Cannot Delete Category
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4">
                <div class="d-flex align-items-start gap-3">
                    <div style="width:42px;height:42px;min-width:42px;background:#fff0f0;border-radius:50%;display:flex;align-items:center;justify-content:center">
                        <i class="bi bi-box-seam text-danger fs-5"></i>
                    </div>
                    <div>
                        <p class="mb-1 fw-600" id="blockedCatName"></p>
                        <p class="mb-0 text-muted" style="font-size:14px" id="blockedMsg"></p>
                        <div class="alert alert-warning mt-3 mb-0 py-2 px-3" style="font-size:13px">
                            <i class="bi bi-info-circle me-1"></i>
                            To delete this category, first remove or move all its products from the <strong>Products</strong> page.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--border)">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <a href="products.php" class="btn btn-primary">
                    <i class="bi bi-box-seam me-1"></i> Go to Products
                </a>
            </div>
        </div>
    </div>
</div>


<!-- ── Confirm Delete Modal (no products — safe) ─────────────── -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px;border:1px solid var(--border)">
            <div class="modal-header" style="border-bottom:1px solid var(--border)">
                <h6 class="modal-title fw-700">
                    <i class="bi bi-trash3 me-2 text-danger"></i>Delete Category
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-1">Are you sure? The category <strong id="confirmCatName"></strong> will be permanently deleted.</p>
                <p class="text-muted mb-0" style="font-size:13px">This action cannot be undone.</p>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--border)">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">
                    <i class="bi bi-trash3 me-1"></i> Yes, Delete
                </a>
            </div>
        </div>
    </div>
</div>


<script>
// Open edit modal
function openEditModal(cat) {
    document.getElementById('edit_id').value          = cat.id;
    document.getElementById('edit_name').value        = cat.name;
    document.getElementById('edit_description').value = cat.description;
    const img = document.getElementById('edit_current_image');
    if (cat.image) {
        img.src = '<?= SITE_URL ?>/assets/images/categories/' + cat.image;
        img.style.display = '';
    } else {
        img.style.display = 'none';
    }
    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}

// Delete button handler
function confirmDelete(id, productCount, catName) {
    if (productCount > 0) {
        // Category has products — show warning modal
        document.getElementById('blockedCatName').textContent = '"' + catName + '"';
        document.getElementById('blockedMsg').textContent =
            'This category has ' + productCount + ' active product(s). Please delete or move them first before deleting this category.';
        new bootstrap.Modal(document.getElementById('deleteBlockedModal')).show();
    } else {
        // Safe — show confirmation modal
        document.getElementById('confirmCatName').textContent = '"' + catName + '"';
        document.getElementById('confirmDeleteBtn').href = 'categories.php?delete=' + id;
        new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
    }
}

// Auto-open blocked modal if redirected back with ?blocked=
<?php if (isset($_GET['blocked']) && is_numeric($_GET['blocked'])): ?>
window.addEventListener('DOMContentLoaded', function() {
    var pcount = <?= (int)($_GET['pcount'] ?? 0) ?>;
    var catName = '';
    <?php
        $bid = (int)$_GET['blocked'];
        $bcat = $db->prepare("SELECT name FROM categories WHERE id = ?");
        $bcat->execute([$bid]);
        $brow = $bcat->fetch();
        if ($brow) echo 'catName = ' . json_encode($brow['name']) . ';';
    ?>
    document.getElementById('blockedCatName').textContent = '"' + catName + '"';
    document.getElementById('blockedMsg').textContent =
        'This category has ' + pcount + ' active product(s). Please delete or move them first before deleting this category.';
    new bootstrap.Modal(document.getElementById('deleteBlockedModal')).show();
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
