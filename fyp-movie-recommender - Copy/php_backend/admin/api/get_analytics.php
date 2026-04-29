<?php
// admin/api/get_analytics.php
require_once '../includes/admin_auth.php';
require_once '../../database/connection.php';

header('Content-Type: application/json');

try {
    // 1. Mood distribution
    $stmt = $pdo->query("SELECT mood, COUNT(*) as count FROM user_mood_history GROUP BY mood");
    $mood_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Ensure all common moods are present
    $labels = ['Happy', 'Sad', 'Angry', 'Relaxed', 'Neutral'];
    $counts = [];
    foreach ($labels as $l) {
        $counts[] = $mood_data[$l] ?? 0;
    }

    // 2. Activity trend (last 7 days)
    $trend_data = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_mood_history WHERE DATE(detected_at) = ?");
        $stmt->execute([$date]);
        $trend_data[$date] = $stmt->fetchColumn();
    }

    echo json_encode([
        'success' => true,
        'moods' => [
            'labels' => $labels,
            'data' => $counts
        ],
        'trend' => [
            'labels' => array_keys($trend_data),
            'data' => array_values($trend_data)
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>