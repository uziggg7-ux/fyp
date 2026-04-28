<?php
/**
 * api/delete_user.php - AJAX endpoint to purge user
 */
require_once '../includes/admin_auth.php';
require_once '../../includes/config.php';
require_once '../../database/connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'INVALID_METHOD']);
    exit();
}

$user_id = $_GET['id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'MISSING_IDENTITY']);
    exit();
}

try {
    // Start Transaction
    $pdo->beginTransaction();

    // 1. Get username for logging
    $stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $username = $stmt->fetchColumn();

    if (!$username) {
        throw new Exception("USER_NOT_FOUND");
    }

    // 2. Delete the user (Cascade should handle mood_history and favorites if FKs are correct)
    // Even if cascade is set, we do it explicitly for safety or logging
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // 3. Log the Admin Action
    $log = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, target) VALUES (?, 'Purge User', ?)");
    $log->execute([$_SESSION['admin_id'], "User: $username (ID: $user_id)"]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'USER_PURGED']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
