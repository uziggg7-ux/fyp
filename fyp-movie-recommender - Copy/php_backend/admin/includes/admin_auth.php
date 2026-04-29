<?php
// admin/includes/admin_auth.php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php"); // Adjust path if necessary
    exit();
}
?>