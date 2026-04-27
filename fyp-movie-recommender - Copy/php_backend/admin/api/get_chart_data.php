<?php
/**
 * api/get_chart_data.php - JSON Data endpoint for Analytics
 */
require_once '../includes/admin_auth.php';
require_once '../../includes/config.php';
require_once '../../database/connection.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? 'mood_distribution';
$days = (int)($_GET['days'] ?? 30);

try {
    $data = [];

    if ($type === 'mood_distribution') {
        $stmt = $pdo->prepare("SELECT mood, COUNT(*) as count FROM user_mood_history WHERE detected_at >= DATE_SUB(NOW(), INTERVAL ? DAY) GROUP BY mood");
        $stmt->execute([$days]);
        $data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    } elseif ($type === 'user_growth') {
        $stmt = $pdo->prepare("SELECT DATE(created_at) as date, COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) GROUP BY DATE(created_at) ORDER BY date ASC");
        $stmt->execute([$days]);
        $data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    } elseif ($type === 'method_split') {
        $stmt = $pdo->prepare("SELECT input_type, COUNT(*) as count FROM user_mood_history WHERE detected_at >= DATE_SUB(NOW(), INTERVAL ? DAY) GROUP BY input_type");
        $stmt->execute([$days]);
        $data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
