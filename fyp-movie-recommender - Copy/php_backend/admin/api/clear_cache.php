<?php
/**
 * api/clear_cache.php - Wipe movie cache
 */
require_once '../includes/admin_auth.php';
require_once '../../includes/config.php';
require_once '../../database/connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'INVALID_METHOD']);
    exit();
}

$genre_id = $_GET['genre_id'] ?? null;

try {
    if ($genre_id) {
        $stmt = $pdo->prepare("DELETE FROM cached_movies WHERE genre_id = ?");
        $stmt->execute([$genre_id]);
        $action = "Clear Cache (Genre: $genre_id)";
    } else {
        $pdo->exec("TRUNCATE TABLE cached_movies");
        $action = "Wipe Full Cache";
    }

    // Log the action
    $log = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, target) VALUES (?, ?, 'Database')");
    $log->execute([$_SESSION['admin_id'], $action]);

    echo json_encode(['success' => true, 'message' => 'CACHE_CLEARED']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
