<?php
/**
 * profile/index.php
 * Profile page: update name, upload avatar, change password.
 * Handles three separate POST actions distinguished by a hidden
 * "form_action" field: update_profile | change_password | upload_avatar
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$pdo    = getDB();
$userId = currentUserId();

$stmt = $pdo->prepare('SELECT full_name, email, avatar, created_at FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();
    $formAction = $_POST['form_action'] ?? '';

    // ---- Update profile info ----
    if ($formAction === 'update_profile') {
        $fullName = clean($_POST['full_name'] ?? '');
        if (mb_strlen($fullName) < 2) {
            $errors['full_name'] = 'Please enter a valid name.';
        } else {
            $upd = $pdo->prepare('UPDATE users SET full_name = ? WHERE id = ?');
            $upd->execute([$fullName, $userId]);
            $_SESSION['user_name'] = $fullName;
            flash('success', 'Profile updated successfully.');
            header('Location: ' . BASE_URL . '/profile/index.php');
            exit;
        }
    }

    // ---- Change password ----
    if ($formAction === 'change_password') {
        $current = (string)($_POST['current_password'] ?? '');
        $new     = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['confirm_new_password'] ?? '');

        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $hash = $stmt->fetchColumn();

        if (!password_verify($current, $hash)) {
            $errors['current_password'] = 'Current password is incorrect.';
        } elseif (mb_strlen($new) < 8) {
            $errors['new_password'] = 'New password must be at least 8 characters.';
        } elseif ($new !== $confirm) {
            $errors['confirm_new_password'] = 'Passwords do not match.';
        } else {
            $newHash = password_hash($new, PASSWORD_BCRYPT);
            $upd = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $upd->execute([$newHash, $userId]);
            logActivity($userId, null, 'login', 'Changed account password.');
            flash('success', 'Password changed successfully.');
            header('Location: ' . BASE_URL . '/profile/index.php');
            exit;
        }
    }

    // ---- Upload avatar ----
    if ($formAction === 'upload_avatar' && !empty($_FILES['avatar']['name'])) {
        $file = $_FILES['avatar'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['avatar'] = 'Upload failed. Please try again.';
        } elseif (!in_array($mimeType, $allowedTypes, true)) {
            $errors['avatar'] = 'Only JPG, PNG, or WEBP images are allowed.';
        } elseif ($file['size'] > $maxSize) {
            $errors['avatar'] = 'Image must be under 2MB.';
        } else {
            $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mimeType];
            $filename = 'user_' . $userId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $destination = __DIR__ . '/../assets/images/avatars/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $upd = $pdo->prepare('UPDATE users SET avatar = ? WHERE id = ?');
                $upd->execute(['assets/images/avatars/' . $filename, $userId]);
                flash('success', 'Profile picture updated.');
                header('Location: ' . BASE_URL . '/profile/index.php');
                exit;
            } else {
                $errors['avatar'] = 'Could not save the uploaded file.';
            }
        }
    }
}

$avatarPath = $user['avatar'] ?? null;
$pageTitle  = 'Profile';
$activeNav  = 'profile';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <?php require __DIR__ . '/../includes/navbar.php'; ?>
    <div class="page-content" style="max-width:640px;">

        <div class="page-header">
            <div>
                <h1>Profile Settings</h1>
                <p>Manage your account information and security.</p>
            </div>
        </div>

        <!-- Avatar upload -->
        <div class="card avatar-upload-row" style="padding:24px; margin-bottom:20px;">
            <?php if ($avatarPath): ?>
                <img src="<?= BASE_URL . '/' . e($avatarPath) ?>" style="width:76px; height:76px; border-radius:50%; object-fit:cover;" alt="Avatar">
            <?php else: ?>
                <div class="user-avatar" style="width:76px; height:76px; font-size:1.6rem;"><?= e(strtoupper(substr($user['full_name'], 0, 1))) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" style="flex:1;">
                <?= csrf_field() ?>
                <input type="hidden" name="form_action" value="upload_avatar">
                <label style="font-size:0.85rem; color:var(--text-muted); display:block; margin-bottom:8px;">Profile Picture (JPG, PNG, WEBP — max 2MB)</label>
                <div style="display:flex; gap:10px;">
                    <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" required
                           style="flex:1; font-size:0.85rem;">
                    <button type="submit" class="btn btn-outline btn-sm">Upload</button>
                </div>
                <?php if (isset($errors['avatar'])): ?>
                    <div class="field-error show" style="color:var(--color-error); font-size:0.78rem; margin-top:6px;"><?= e($errors['avatar']) ?></div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Profile info -->
        <div class="card" style="padding:24px; margin-bottom:20px;">
            <h3 style="margin-bottom:16px; font-size:1.02rem;">Account Information</h3>
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="form_action" value="update_profile">

                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-size:0.85rem; color:var(--text-muted); display:block; margin-bottom:5px;">Full Name</label>
                    <input type="text" name="full_name" value="<?= e($user['full_name']) ?>"
                           style="width:100%; padding:10px 12px; border-radius:var(--radius-md); border:1px solid var(--border-color); background:var(--bg-body); color:var(--text-main);">
                    <?php if (isset($errors['full_name'])): ?>
                        <div style="color:var(--color-error); font-size:0.78rem; margin-top:6px;"><?= e($errors['full_name']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group" style="margin-bottom:18px;">
                    <label style="font-size:0.85rem; color:var(--text-muted); display:block; margin-bottom:5px;">Email Address</label>
                    <input type="email" value="<?= e($user['email']) ?>" disabled
                           style="width:100%; padding:10px 12px; border-radius:var(--radius-md); border:1px solid var(--border-color); background:var(--bg-body); color:var(--text-muted);">
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:5px;">Email cannot be changed.</div>
                </div>

                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>

        <!-- Change password -->
        <div class="card" style="padding:24px;" id="password">
            <h3 style="margin-bottom:16px; font-size:1.02rem;">Change Password</h3>
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="form_action" value="change_password">

                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-size:0.85rem; color:var(--text-muted); display:block; margin-bottom:5px;">Current Password</label>
                    <input type="password" name="current_password" required
                           style="width:100%; padding:10px 12px; border-radius:var(--radius-md); border:1px solid var(--border-color); background:var(--bg-body); color:var(--text-main);">
                    <?php if (isset($errors['current_password'])): ?>
                        <div style="color:var(--color-error); font-size:0.78rem; margin-top:6px;"><?= e($errors['current_password']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-grid-2" style="margin-bottom:18px;">
                    <div class="form-group">
                        <label style="font-size:0.85rem; color:var(--text-muted); display:block; margin-bottom:5px;">New Password</label>
                        <input type="password" name="new_password" required
                               style="width:100%; padding:10px 12px; border-radius:var(--radius-md); border:1px solid var(--border-color); background:var(--bg-body); color:var(--text-main);">
                        <?php if (isset($errors['new_password'])): ?>
                            <div style="color:var(--color-error); font-size:0.78rem; margin-top:6px;"><?= e($errors['new_password']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label style="font-size:0.85rem; color:var(--text-muted); display:block; margin-bottom:5px;">Confirm New Password</label>
                        <input type="password" name="confirm_new_password" required
                               style="width:100%; padding:10px 12px; border-radius:var(--radius-md); border:1px solid var(--border-color); background:var(--bg-body); color:var(--text-main);">
                        <?php if (isset($errors['confirm_new_password'])): ?>
                            <div style="color:var(--color-error); font-size:0.78rem; margin-top:6px;"><?= e($errors['confirm_new_password']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </div>

    </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
