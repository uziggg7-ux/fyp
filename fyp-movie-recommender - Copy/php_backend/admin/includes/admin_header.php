<?php
// admin/includes/admin_header.php
require_once __DIR__ . '/admin_auth.php';
require_once __DIR__ . '/../../database/connection.php';

// Fetch settings if needed
$app_name = "MoodAI Admin";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $app_name; ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="<?php echo $base_url ?? '../'; ?>assets/css/admin_style.css">
</head>
<body class="bg-black text-white">

    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="admin-sidebar p-3 border-end border-dark">
            <h4 class="fw-bold mb-4 logo-text">MoodAI<span>.</span>Admin</h4>
            <ul class="nav flex-column gap-2">
                <li class="nav-item">
                    <a href="<?php echo $base_url ?? '../'; ?>dashboard.php" class="nav-link text-white">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo $base_url ?? '../'; ?>movies/index.php" class="nav-link text-white">
                        <i class="bi bi-film me-2"></i> Movie Management
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo $base_url ?? '../'; ?>moods/index.php" class="nav-link text-white">
                        <i class="bi bi-emoji-smile me-2"></i> Mood Management
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo $base_url ?? '../'; ?>users/index.php" class="nav-link text-white">
                        <i class="bi bi-people me-2"></i> User Management
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo $base_url ?? '../'; ?>analytics/index.php" class="nav-link text-white">
                        <i class="bi bi-bar-chart me-2"></i> Analytics
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo $base_url ?? '../'; ?>settings/index.php" class="nav-link text-white">
                        <i class="bi bi-gear me-2"></i> Settings
                    </a>
                </li>
                <li class="nav-item mt-5">
                    <a href="<?php echo $base_url ?? '../'; ?>logout.php" class="nav-link text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content Area -->
        <main class="flex-grow-1 p-4">
