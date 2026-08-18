<?php
// ============================================================
// edit_item.php — Edit an Existing Item (Owner Only)
// ============================================================

$page_title = 'Edit Item';
$base_url   = '';
require 'includes/db.php';
require 'includes/header.php';

// Auth guard
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'includes/navbar.php';

$id  = (int)($_GET['id'] ?? 0);
$uid = $_SESSION['user_id'];

if ($id < 1) {
    header('Location: dashboard.php');
    exit;
}

// Fetch item — must belong to current user
$fetch = $conn->prepare("SELECT * FROM items WHERE id = ? AND user_id = ?");
$fetch->bind_param('ii', $id, $uid);
$fetch->execute();
$result = $fetch->get_result();

if ($result->num_rows === 0) {
    // Item doesn't exist or doesn't belong to this user
    header('Location: dashboard.php');
    exit;
}
$item = $result->fetch_assoc();
$fetch->close();

// Load categories
$cats = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type       = $_POST['type']         ?? '';
    $item_name  = trim($_POST['item_name']   ?? '');
    $cat_id     = (int)($_POST['category_id']?? 0);
    $description= trim($_POST['description'] ?? '');
    $location   = trim($_POST['location']    ?? '');
    $date_lost  = $_POST['date_lost']        ?? '';
    $contact    = trim($_POST['contact']     ?? '');
    $status     = $_POST['status']           ?? 'active';

    // Validate
    if (!in_array($type, ['lost','found']))         $errors[] = 'Please select Lost or Found.';
    if (empty($item_name))                           $errors[] = 'Item name is required.';
    if ($cat_id < 1)                                 $errors[] = 'Please select a category.';
    if (empty($contact))                             $errors[] = 'Contact information is required.';
    if (!in_array($status, ['active','returned']))   $errors[] = 'Invalid status.';

    // Handle new image upload (optional)
    $image_name = $item['image']; // keep old image by default
    if (!empty($_FILES['image']['name'])) {
        $allowed_types = ['image/jpeg','image/png','image/gif','image/webp'];
        $max_size      = 2 * 1024 * 1024;

        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors[] = 'Only JPG, PNG, GIF, and WebP images are allowed.';
        } elseif ($_FILES['image']['size'] > $max_size) {
            $errors[] = 'Image must be smaller than 2 MB.';
        } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Image upload failed.';
        } else {
            $ext        = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_name   = 'item_' . time() . '_' . rand(100,999) . '.' . strtolower($ext);
            $upload_dir = __DIR__ . '/uploads/';

            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_name)) {
                // Delete old image file if it exists
                if ($item['image'] && file_exists($upload_dir . $item['image'])) {
                    unlink($upload_dir . $item['image']);
                }
                $image_name = $new_name;
            } else {
                $errors[] = 'Could not save the image.';
            }
        }
    }

    // Update record
    if (empty($errors)) {
        $upd = $conn->prepare(
            "UPDATE items SET type=?, item_name=?, category_id=?, description=?,
                              location=?, date_lost=?, contact=?, image=?, status=?
             WHERE id=? AND user_id=?"
        );
        $upd->bind_param('ssisssssii',
            $type, $item_name, $cat_id, $description,
            $location, $date_lost, $contact, $image_name, $status,
            $id, $uid
        );

        if ($upd->execute()) {
            $_SESSION['flash'] = 'Item updated successfully!';
            header('Location: item_details.php?id=' . $id);
            exit;
        } else {
            $errors[] = 'Update failed. Please try again.';
        }
        $upd->close();
    }
} else {
    // Pre-fill from DB
    $_POST = $item;
}
?>

<main class="section-pad">
<div class="container">
<div class="row justify-content-center">
<div class="col-lg-8">

    <?php if ($errors): ?>
    <div class="alert alert-danger mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="form-card">
        <div class="mb-4 d-flex justify-content-between align-items-start">
            <div>
                <h1 class="form-card-title">Edit Item</h1>
                <p class="form-card-subtitle">Update the details of your post</p>
            </div>
            <a href="item_details.php?id=<?= $id ?>" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Cancel
            </a>
        </div>

        <form method="POST" action="edit_item.php?id=<?= $id ?>" enctype="multipart/form-data" novalidate>

            <!-- Type toggle -->
            <div class="mb-4">
                <label class="form-label">Item Type *</label>
                <div class="d-flex gap-3">
                    <div class="flex-fill">
                        <input type="radio" class="btn-check" name="type" id="typeLost"
                               value="lost" <?= ($_POST['type']==='lost') ? 'checked':'' ?>>
                        <label class="btn w-100 py-2" for="typeLost"
                               style="border:2px solid var(--border);border-radius:var(--radius);color:var(--text-muted);">
                            <i class="bi bi-question-circle-fill me-1 text-danger"></i>Lost
                        </label>
                    </div>
                    <div class="flex-fill">
                        <input type="radio" class="btn-check" name="type" id="typeFound"
                               value="found" <?= ($_POST['type']==='found') ? 'checked':'' ?>>
                        <label class="btn w-100 py-2" for="typeFound"
                               style="border:2px solid var(--border);border-radius:var(--radius);color:var(--text-muted);">
                            <i class="bi bi-check-circle-fill me-1 text-success"></i>Found
                        </label>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="item_name" class="form-label">Item Name *</label>
                    <input type="text" id="item_name" name="item_name" class="form-control"
                           value="<?= htmlspecialchars($_POST['item_name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="category_id" class="form-label">Category *</label>
                    <select id="category_id" name="category_id" class="form-select" required>
                        <option value="">Select a category</option>
                        <?php foreach ($cats as $cat): ?>
                        <option value="<?= $cat['id'] ?>"
                            <?= (($_POST['category_id'] ?? 0) == $cat['id']) ? 'selected':'' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" id="location" name="location" class="form-control"
                           value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="date_lost" class="form-label">Date Lost / Found</label>
                    <input type="date" id="date_lost" name="date_lost" class="form-control"
                           max="<?= date('Y-m-d') ?>"
                           value="<?= htmlspecialchars($_POST['date_lost'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label for="contact" class="form-label">Contact Information *</label>
                    <input type="text" id="contact" name="contact" class="form-control"
                           value="<?= htmlspecialchars($_POST['contact'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="active"   <?= ($_POST['status']==='active')   ? 'selected':'' ?>>Active</option>
                        <option value="returned" <?= ($_POST['status']==='returned') ? 'selected':'' ?>>Returned</option>
                    </select>
                </div>

                <!-- Current image -->
                <?php if ($item['image']): ?>
                <div class="col-12">
                    <label class="form-label">Current Image</label>
                    <div class="d-flex align-items-center gap-3">
                        <img src="uploads/<?= htmlspecialchars($item['image']) ?>"
                             style="height:80px;border-radius:var(--radius);object-fit:cover;">
                        <span class="text-muted small">Upload a new image below to replace this one.</span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- New image upload -->
                <div class="col-12">
                    <label class="form-label">Replace Photo (Optional)</label>
                    <div class="upload-area" onclick="document.getElementById('image').click()">
                        <i class="bi bi-cloud-arrow-up upload-icon d-block"></i>
                        <div class="upload-text">Click to upload a new photo</div>
                        <img id="imagePreview" class="upload-preview d-none" src="" alt="Preview">
                    </div>
                    <input type="file" id="image" name="image" accept="image/*"
                           class="d-none" onchange="previewImage(this)">
                </div>

                <div class="col-12 mt-2">
                    <button type="submit" class="btn btn-primary-custom px-5 py-2">
                        <i class="bi bi-save me-2"></i>Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>
</div>
</div>
</main>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById('imagePreview');
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
// Style radio buttons on load
document.querySelectorAll('.btn-check').forEach(function(chk) {
    chk.addEventListener('change', function() {
        document.querySelectorAll('.btn-check + label').forEach(l => {
            l.style.borderColor = 'var(--border)';
            l.style.color = 'var(--text-muted)';
            l.style.background = 'transparent';
        });
        this.nextElementSibling.style.borderColor = 'var(--primary)';
        this.nextElementSibling.style.color = 'var(--text-primary)';
        this.nextElementSibling.style.background = 'rgba(79,70,229,0.1)';
    });
    if (chk.checked) chk.dispatchEvent(new Event('change'));
});
</script>

<?php require 'includes/footer.php'; ?>
