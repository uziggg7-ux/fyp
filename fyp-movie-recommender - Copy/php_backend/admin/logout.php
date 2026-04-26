<?php
/**
 * logout.php - Admin Logout Logic
 */
session_start();

// Check if admin was logged in to log the logout
if (isset($_SESSION['admin_id'])) {
    require_once '../includes/config.php';
    require_once '../database/connection.php';

    try {
        $log = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, target) VALUES (?, 'Logout', 'System')");
        $log->execute([$_SESSION['admin_id']]);
    } catch (Exception $e) {
        // Silently fail if log fails
    }
}

// Unset only admin related sessions
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);

// Redirect to admin login
header("Location: index.php");
exit();
?>
