<?php
// ============================================================
// add_item.php — Report a Lost or Found Item
// Handles image upload and inserts record to DB
// ============================================================

$page_title = 'Report Item';
$base_url   = '';
require 'includes/db.php';
require 'includes/header.php';

// Auth guard
if (!isset($_SESSION['user_id'])) {
    $_SESSION['intended'] = 'add_item.php';
    header('Location: login.php');
    exit;
}

require 'includes/navbar.php';

// Load categories for dropdown
$cats = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Collect inputs ---
    $uid        = $_SESSION['user_id'];
    $type       = $_POST['type']        ?? '';
    $item_name  = trim($_POST['item_name']  ?? '');
    $cat_id     = (int)($_POST['category_id'] ?? 0);
    $description= trim($_POST['description'] ?? '');
    $location   = trim($_POST['location']   ?? '');
    $date_lost  = $_POST['date_lost']       ?? '';
    $contact    = trim($_POST['contact']    ?? '');

    // --- Validate ---
    if (!in_array($type, ['lost','found']))  $errors[] = 'Please select Lost or Found.';
    if (empty($item_name))                   $errors[] = 'Item name is required.';
    if ($cat_id < 1)                         $errors[] = 'Please select a category.';
    if (empty($contact))                     $errors[] = 'Contact information is required.';

    // --- Handle image upload ---
    $image_name = null;
    if (!empty($_FILES['image']['name'])) {
        $allowed_types = ['image/jpeg','image/png','image/gif','image/webp'];
        $max_size      = 2 * 1024 * 1024; // 2 MB

        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors[] = 'Only JPG, PNG, GIF, and WebP images are allowed.';
        } elseif ($_FILES['image']['size'] > $max_size) {
            $errors[] = 'Image must be smaller than 2 MB.';
        } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Image upload failed. Try again.';
        } else {
            // Generate unique filename
            $ext        = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = 'item_' . time() . '_' . rand(100,999) . '.' . strtolower($ext);
            $upload_dir = __DIR__ . '/uploads/';

            // Create uploads directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name)) {
                $errors[] = 'Could not save the image. Check folder permissions.';
                $image_name = null;
            }
        }
    }

    // --- Insert into DB ---
    if (empty($errors)) {
        $ins = $conn->prepare(
            "INSERT INTO items (user_id, category_id, type, item_name, description, location, date_lost, contact, image)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $ins->bind_param('iisssssss',
            $uid, $cat_id, $type, $item_name, $description, $location, $date_lost, $contact, $image_name
        );

        if ($ins->execute()) {
            $new_id = $ins->insert_id;
            $_SESSION['flash'] = 'Your item has been reported successfully!';
            header('Location: item_details.php?id=' . $new_id);
            exit;
        } else {
            $errors[] = 'Database error. Please try again.';
        }
        $ins->close();
    }
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
        <div class="mb-4">
            <h1 class="form-card-title">Report an Item</h1>
            <p class="form-card-subtitle">Fill in the details to post a lost or found item</p>
        </div>

        <form method="POST" action="add_item.php" enctype="multipart/form-data" novalidate>

            <!-- Lost / Found toggle -->
            <div class="mb-4">
                <label class="form-label">Item Type *</label>
                <div class="d-flex gap-3">
                    <div class="flex-fill">
                        <input type="radio" class="btn-check" name="type" id="typeLost"
                               value="lost" <?= (($_POST['type']??'') === 'lost' || !isset($_POST['type'])) ? 'checked' : '' ?>>
                        <label class="btn w-100 py-3" for="typeLost"
                               style="border:2px solid var(--border);border-radius:var(--radius);color:var(--text-muted);transition:all .2s;">
                            <i class="bi bi-question-circle-fill d-block fs-2 mb-1 text-danger"></i>
                            I <strong>Lost</strong> Something
                        </label>
                    </div>
                    <div class="flex-fill">
                        <input type="radio" class="btn-check" name="type" id="typeFound"
                               value="found" <?= (($_POST['type']??'') === 'found') ? 'checked' : '' ?>>
                        <label class="btn w-100 py-3" for="typeFound"
                               style="border:2px solid var(--border);border-radius:var(--radius);color:var(--text-muted);transition:all .2s;">
                            <i class="bi bi-check-circle-fill d-block fs-2 mb-1 text-success"></i>
                            I <strong>Found</strong> Something
                        </label>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <!-- Item name -->
                <div class="col-md-6">
                    <label for="item_name" class="form-label">Item Name *</label>
                    <input type="text" id="item_name" name="item_name" class="form-control"
                           placeholder="e.g. Black HP Laptop"
                           value="<?= htmlspecialchars($_POST['item_name'] ?? '') ?>" required>
                </div>

                <!-- Category -->
                <div class="col-md-6">
                    <label for="category_id" class="form-label">Category *</label>
                    <select id="category_id" name="category_id" class="form-select" required>
                        <option value="">Select a category</option>
                        <?php foreach ($cats as $cat): ?>
                        <option value="<?= $cat['id'] ?>"
                            <?= (($_POST['category_id'] ?? 0) == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Description -->
                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3"
                              placeholder="Describe the item in detail: color, brand, any special markings…"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <!-- Location -->
                <div class="col-md-6">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" id="location" name="location" class="form-control"
                           placeholder="e.g. Library 2nd Floor, Canteen…"
                           value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
                </div>

                <!-- Date -->
                <div class="col-md-6">
                    <label for="date_lost" class="form-label">Date Lost / Found</label>
                    <input type="date" id="date_lost" name="date_lost" class="form-control"
                           max="<?= date('Y-m-d') ?>"
                           value="<?= htmlspecialchars($_POST['date_lost'] ?? date('Y-m-d')) ?>">
                </div>

                <!-- Contact -->
                <div class="col-12">
                    <label for="contact" class="form-label">Contact Information *</label>
                    <input type="text" id="contact" name="contact" class="form-control"
                           placeholder="Phone number, email, or how someone can reach you"
                           value="<?= htmlspecialchars($_POST['contact'] ?? '') ?>" required>
                    <div class="form-text text-muted" style="font-size:.78rem;">
                        This will be shown to other students so they can contact you.
                    </div>
                </div>

                <!-- Image upload -->
                <div class="col-12">
                    <label class="form-label">Item Photo (Optional)</label>
                    <div class="upload-area" id="uploadArea" onclick="document.getElementById('image').click()">
                        <i class="bi bi-cloud-arrow-up upload-icon d-block"></i>
                        <div class="upload-text fw-semibold">Click to upload a photo</div>
                        <div class="upload-text mt-1">JPG, PNG, GIF, WebP — Max 2 MB</div>
                        <img id="imagePreview" class="upload-preview d-none" src="" alt="Preview">
                    </div>
                    <input type="file" id="image" name="image" accept="image/*"
                           class="d-none" onchange="previewImage(this)">
                </div>

                <!-- Submit -->
                <div class="col-12 mt-2">
                    <button type="submit" class="btn btn-primary-custom px-5 py-2">
                        <i class="bi bi-send me-2"></i>Submit Report
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </div>
        </form>
    </div>

</div>
</div>
</div>
</main>

<script>
// Image preview before upload
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById('imagePreview');
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            document.querySelector('.upload-icon').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Drag-and-drop support
var uploadArea = document.getElementById('uploadArea');
uploadArea.addEventListener('dragover', function(e) {
    e.preventDefault();
    uploadArea.classList.add('drag-over');
});
uploadArea.addEventListener('dragleave', function() {
    uploadArea.classList.remove('drag-over');
});
uploadArea.addEventListener('drop', function(e) {
    e.preventDefault();
    uploadArea.classList.remove('drag-over');
    var fileInput = document.getElementById('image');
    fileInput.files = e.dataTransfer.files;
    previewImage(fileInput);
});

// Style btn-check radio labels
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
