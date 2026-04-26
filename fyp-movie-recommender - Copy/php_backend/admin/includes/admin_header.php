<?php
/**
 * admin_header.php - Global Admin Layout (Sidebar & Topbar)
 */
require_once __DIR__ . '/admin_auth.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../database/connection.php';

// Helper to set active class on nav items
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoodAI Admin | Premium Command Center</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="assets/css/admin_style.css">
</head>
<body class="admin-body">

    <!-- Collapsing Sidebar -->
    <nav id="sidebar" class="admin-sidebar">
        <div class="sidebar-header">
            <h4 class="logo-text">MoodAI<span>.</span> <small>ADMIN</small></h4>
            <button type="button" id="sidebarCollapse" class="btn btn-link d-md-none text-white">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <ul class="list-unstyled components">
            <li class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php"><i class="bi bi-speedometer2"></i> <span>Dashboard</span></a>
            </li>
            <li class="<?php echo $current_page == 'users.php' || $current_page == 'user_detail.php' ? 'active' : ''; ?>">
                <a href="users.php"><i class="bi bi-people"></i> <span>Users</span></a>
            </li>
            <li class="<?php echo $current_page == 'mood_analytics.php' ? 'active' : ''; ?>">
                <a href="mood_analytics.php"><i class="bi bi-bar-chart"></i> <span>Mood Analytics</span></a>
            </li>
            <li class="<?php echo $current_page == 'cache_manager.php' ? 'active' : ''; ?>">
                <a href="cache_manager.php"><i class="bi bi-database"></i> <span>Cache Manager</span></a>
            </li>
            <li class="<?php echo $current_page == 'logs.php' ? 'active' : ''; ?>">
                <a href="logs.php"><i class="bi bi-journal-text"></i> <span>System Logs</span></a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <a href="logout.php" class="btn-logout-admin">
                <i class="bi bi-box-arrow-left"></i> <span>Sign Out</span>
            </a>
        </div>
    </nav>

    <!-- Page Content Holder -->
    <div id="content" class="admin-main-content">
        <!-- Topbar -->
        <nav class="navbar navbar-expand-lg admin-topbar sticky-top">
            <div class="container-fluid">
                <button type="button" id="sidebarToggle" class="btn btn-tech-toggle">
                    <i class="bi bi-list"></i>
                </button>

                <div class="ms-auto d-flex align-items-center">
                    <div class="admin-profile-pill">
                        <i class="bi bi-shield-lock me-2 text-danger"></i>
                        <span class="d-none d-sm-inline"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-4">
