<?php
// api/feedback_api.php
session_start();
header('Content-Type: application/json');
require_once '../database/connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$movie_id = $data['movie_id'] ?? null;
$type = $data['type'] ?? null; // 'like' or 'dislike'

if (!$movie_id || !in_array($type, ['like', 'dislike'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

try {
    // Check if feedback exists
    $stmt = $pdo->prepare("SELECT id FROM movie_feedback WHERE user_id = ? AND movie_id = ?");
    $stmt->execute([$user_id, $movie_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $pdo->prepare("UPDATE movie_feedback SET feedback_type = ? WHERE id = ?");
        $stmt->execute([$type, $existing['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO movie_feedback (user_id, movie_id, feedback_type) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $movie_id, $type]);
    }

    echo json_encode(['success' => true, 'message' => 'Feedback saved']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>