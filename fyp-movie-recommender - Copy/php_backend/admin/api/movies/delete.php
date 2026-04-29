<?php
// admin/api/movies/delete.php
require_once '../../includes/admin_auth.php';
require_once '../../../../php_backend/database/connection.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID missing']);
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM cached_movies WHERE tmdb_id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>