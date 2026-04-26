<?php
/**
 * index.php - MoodAI Admin Login
 *
 * Separate authentication for administrative access.
 */
session_start();
require_once '../includes/config.php';
require_once '../database/connection.php';

// Redirect if already logged in as admin
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_message = "Identity and Secret Key required.";
    } else {
        try {
            // Fetch admin - User requested plain text for now, but we'll prepare the logic
            $stmt = $pdo->prepare("SELECT admin_id, username, password FROM admins WHERE email = :email AND is_active = 1");
            $stmt->execute(['email' => $email]);
            $admin = $stmt->fetch();

            // Simple plain text comparison as requested by user for phase 1/2
            if ($admin && $password === $admin['password']) {
                // Success: Initialize Session
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_username'] = $admin['username'];

                // Update last login
                $update = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE admin_id = ?");
                $update->execute([$admin['admin_id']]);

                // Log the action
                $log = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, target, ip_address) VALUES (?, 'Login', 'System', ?)");
                $log->execute([$admin['admin_id'], $_SERVER['REMOTE_ADDR']]);

                header("Location: dashboard.php");
                exit();
            } else {
                $error_message = "INVALID_CREDENTIALS: Access Denied.";
            }
        } catch (Exception $e) {
            $error_message = "SYSTEM_FAULT: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoodAI Admin | Secure Access</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin_style.css">
    <style>
        body {
            background-color: #050505;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .admin-login-card {
            width: 100%;
            max-width: 420px;
            padding: 40px;
            background: #111;
            border: 1px solid #1a1a1a;
            border-radius: 4px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .login-header i {
            font-size: 3rem;
            color: var(--accent-red);
            margin-bottom: 15px;
            display: block;
        }
        .btn-admin-login {
            background: var(--accent-red);
            color: #fff;
            border: none;
            width: 100%;
            padding: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        .btn-admin-login:hover {
            background: #d40000;
            box-shadow: 0 0 20px rgba(255, 0, 0, 0.2);
        }
    </style>
</head>
<body>

    <div class="admin-login-card" data-aos="zoom-in" data-aos-duration="800">
        <div class="login-header">
            <i class="bi bi-shield-lock-fill"></i>
            <h4 class="logo-text">MoodAI<span>.</span> <small>ADMIN</small></h4>
            <p class="text-muted small mt-2">Authenticated terminal access only.</p>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-danger py-2 small" role="alert" data-aos="shake">
                <i class="bi bi-exclamation-octagon-fill me-2"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form action="index.php" method="POST">
            <div class="mb-4">
                <label class="form-label-custom">Admin Identity</label>
                <div class="input-group-premium">
                    <span class="input-icon"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" name="email" placeholder="admin@moodai.com" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label-custom">Security Key</label>
                <div class="input-group-premium">
                    <span class="input-icon"><i class="bi bi-key"></i></span>
                    <input type="password" class="form-control" name="password" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-admin-login">
                Initialize Session
            </button>
        </form>

        <div class="mt-5 text-center">
            <a href="../index.php" class="text-muted text-decoration-none small">
                <i class="bi bi-arrow-left me-1"></i> Back to Frontend
            </a>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
</body>
</html>
