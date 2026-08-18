<?php
// ============================================================
// delete_item.php — Delete Item or Mark as Returned
// Only the owner can perform these actions
// ============================================================

// db.php starts session via header.php include chain;
// but since this page has no HTML output, we start it directly.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auth guard
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'includes/db.php';

$id     = (int)($_GET['id']     ?? 0);
$action = $_GET['action']       ?? 'delete'; // 'delete' or 'returned'
$uid    = $_SESSION['user_id'];

if ($id < 1) {
    header('Location: dashboard.php');
    exit;
}

// Verify ownership
$chk = $conn->prepare("SELECT id, image FROM items WHERE id = ? AND user_id = ?");
$chk->bind_param('ii', $id, $uid);
$chk->execute();
$chk_result = $chk->get_result();

if ($chk_result->num_rows === 0) {
    // Item not found or not owned by this user
    header('Location: dashboard.php');
    exit;
}
$item = $chk_result->fetch_assoc();
$chk->close();

if ($action === 'returned') {
    // Mark as returned (soft update)
    $upd = $conn->prepare("UPDATE items SET status='returned' WHERE id=? AND user_id=?");
    $upd->bind_param('ii', $id, $uid);
    $upd->execute();
    $upd->close();
    $_SESSION['flash'] = 'Item marked as returned!';
    header('Location: item_details.php?id=' . $id);
    exit;
}

// Hard delete
$del = $conn->prepare("DELETE FROM items WHERE id=? AND user_id=?");
$del->bind_param('ii', $id, $uid);

if ($del->execute()) {
    // Also delete the image file from the server
    if ($item['image']) {
        $img_path = __DIR__ . '/uploads/' . $item['image'];
        if (file_exists($img_path)) {
            unlink($img_path);
        }
    }
    $_SESSION['flash'] = 'Item deleted successfully.';
}
$del->close();

header('Location: dashboard.php');
exit;
?>
