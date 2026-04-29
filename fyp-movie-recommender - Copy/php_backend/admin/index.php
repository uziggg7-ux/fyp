<?php
// admin/index.php
session_start();
require_once __DIR__ . '/../database/connection.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_username'] = $admin['username'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MoodAI Admin | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin_style.css">
</head>
<body class="bg-black d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="premium-card" style="width: 100%; max-width: 400px;">
        <h3 class="fw-bold mb-4 logo-text">MoodAI<span>.</span>Admin</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger small py-2"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label text-muted">Admin ID</label>
                <input type="text" name="username" class="form-control bg-dark text-white border-secondary" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted">Secure Key</label>
                <input type="password" name="password" class="form-control bg-dark text-white border-secondary" required>
            </div>
            <button type="submit" class="btn btn-premium w-100">Login to Control Panel</button>
        </form>
    </div>
</body>
</html>
