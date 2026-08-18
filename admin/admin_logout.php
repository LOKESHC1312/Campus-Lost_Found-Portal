<?php
// ============================================================
// admin/admin_logout.php — Destroy admin session
// ============================================================
session_start();
unset($_SESSION['admin_id'], $_SESSION['admin_name']);
session_destroy();
header('Location: admin_login.php');
exit;
?>
