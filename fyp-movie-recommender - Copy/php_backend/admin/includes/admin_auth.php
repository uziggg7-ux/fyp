<?php
/**
 * admin_auth.php - Admin Authentication Guard
 *
 * Included at the top of every admin page to ensure session security.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Check for authenticated Admin session
if (isset($_SESSION['admin_id'])) {
    // Admin is logged in, proceed
    return;
}

// 2. Identify and handle Unauthenticated users (Guests vs Regular Users)
if (isset($_SESSION['user_id'])) {
    /**
     * Case: Regular User attempt
     * They are logged in as a user, but NOT an admin.
     * Block access and redirect to user dashboard.
     */
    header("Location: ../dashboard.php");
    exit();
} else {
    /**
     * Case: Guest attempt
     * No session found at all.
     * Redirect to admin login page.
     */

    // AJAX Check
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized admin access.']);
        exit();
    }

    // Standard Redirect
    header("Location: index.php");
    exit();
}
?>
