<?php
// ============================================================
// logout.php — Destroy session and redirect
// ============================================================
session_start();
session_unset();    // Clear all session variables
session_destroy();  // Destroy the session
header('Location: login.php?logout=1');
exit;
?>
