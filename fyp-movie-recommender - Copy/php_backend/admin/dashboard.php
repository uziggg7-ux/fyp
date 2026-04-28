<?php
/**
 * dashboard.php - Admin Bento Command Center
 */
require_once 'includes/admin_header.php';

// 1. Fetch Key Stats
try {
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $total_detections = $pdo->query("SELECT COUNT(*) FROM user_mood_history")->fetchColumn();
    $total_favorites = $pdo->query("SELECT COUNT(*) FROM user_favorites")->fetchColumn();
    $cache_count = $pdo->query("SELECT COUNT(*) FROM cached_movies")->fetchColumn();

    // 2. Fetch Mood Distribution
    $mood_data = $pdo->query("SELECT mood, COUNT(*) as count FROM user_mood_history GROUP BY mood")->fetchAll(PDO::FETCH_KEY_PAIR);

    // 3. Fetch User Registrations (30 Days)
    $user_growth = $pdo->query("
        SELECT DATE(created_at) as date, COUNT(*) as count
        FROM users
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    // 4. Fetch Detection Methods
    $method_data = $pdo->query("SELECT input_type, COUNT(*) as count FROM user_mood_history GROUP BY input_type")->fetchAll(PDO::FETCH_KEY_PAIR);

    // 5. Fetch Recent Activity
    $recent_activity = $pdo->query("
        SELECT h.*, u.username
        FROM user_mood_history h
        JOIN users u ON h.user_id = u.user_id
        ORDER BY h.detected_at DESC
        LIMIT 10
    ")->fetchAll();

} catch (Exception $e) {
    $total_users = $total_detections = $total_favorites = $cache_count = 0;
    $mood_data = $user_growth = $method_data = [];
    $recent_activity = [];
}

// Prepare data for JS
$mood_labels = array_keys($mood_data);
$mood_values = array_values($mood_data);
$growth_labels = array_keys($user_growth);
$growth_values = array_values($user_growth);
$method_labels = array_map('ucfirst', array_keys($method_data));
$method_values = array_values($method_data);
?>

<link rel="stylesheet" href="assets/css/admin_dashboard.css">

<div class="admin-page-content">

    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <span class="section-tag">Neural Interface</span>
            <h2 class="admin-page-title">Command Center</h2>
        </div>
        <div class="text-end d-none d-md-block">
            <div class="admin-profile-pill py-2 px-3">
                <span class="animate-flicker me-2" style="width: 8px; height: 8px; background: #00FF00; border-radius: 50%; display: inline-block;"></span>
                <span class="text-muted small fw-bold">AI_SYNC: ACTIVE</span>
            </div>
        </div>
    </div>

    <!-- Bento Grid -->
    <div class="bento-grid">

        <!-- Stat: Users -->
        <div class="bento-item bento-stat stat-card-bento" data-aos="zoom-in" data-aos-delay="100">
            <div class="icon-wrapper"><i class="bi bi-people"></i></div>
            <div class="stat-value"><?php echo number_format($total_users); ?></div>
            <div class="stat-label">Total Operatives</div>
        </div>

        <!-- Stat: Scans -->
        <div class="bento-item bento-stat stat-card-bento" data-aos="zoom-in" data-aos-delay="200">
            <div class="icon-wrapper"><i class="bi bi-activity"></i></div>
            <div class="stat-value"><?php echo number_format($total_detections); ?></div>
            <div class="stat-label">Neural Scans</div>
        </div>

        <!-- Stat: Favorites -->
        <div class="bento-item bento-stat stat-card-bento" data-aos="zoom-in" data-aos-delay="300">
            <div class="icon-wrapper"><i class="bi bi-heart"></i></div>
            <div class="stat-value"><?php echo number_format($total_favorites); ?></div>
            <div class="stat-label">System Favs</div>
        </div>

        <!-- Stat: Cache -->
        <div class="bento-item bento-stat stat-card-bento" data-aos="zoom-in" data-aos-delay="400">
            <div class="icon-wrapper"><i class="bi bi-database"></i></div>
            <div class="stat-value"><?php echo number_format($cache_count); ?></div>
            <div class="stat-label">Cached Entities</div>
        </div>

        <!-- Chart: User Growth -->
        <div class="bento-item bento-chart-large" data-aos="fade-up" data-aos-delay="500">
            <div class="chart-header">
                <h5><i class="bi bi-graph-up"></i> Population Growth</h5>
                <span class="text-muted small">Last 30 Days</span>
            </div>
            <div style="height: 300px;">
                <canvas id="userGrowthChart"></canvas>
            </div>
        </div>

        <!-- Recent Activity Feed -->
        <div class="bento-item bento-feed" data-aos="fade-up" data-aos-delay="600">
            <div class="chart-header">
                <h5><i class="bi bi-clock-history"></i> Real-time Scans</h5>
                <a href="logs.php" class="btn-outline-premium btn-sm py-1 px-3" style="font-size: 0.6rem;">VIEW ALL</a>
            </div>
            <div class="activity-feed-bento">
                <?php if (empty($recent_activity)): ?>
                    <div class="text-center py-5 text-muted small">No recent neural activity detected.</div>
                <?php else: ?>
                    <?php foreach ($recent_activity as $row): ?>
                        <div class="activity-item-bento">
                            <div class="d-flex align-items-center">
                                <div class="me-3" style="width: 35px; height: 35px; background: rgba(255,255,255,0.03); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-person small text-muted"></i>
                                </div>
                                <div>
                                    <div class="text-white fw-bold small"><?php echo htmlspecialchars($row['username']); ?></div>
                                    <div class="text-muted" style="font-size: 0.6rem;">via <?php echo strtoupper($row['input_type']); ?></div>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="mood-pill mood-<?php echo strtolower($row['mood']); ?>">
                                    <?php echo htmlspecialchars($row['mood']); ?>
                                </span>
                                <div class="text-muted mt-1" style="font-size: 0.55rem;"><?php echo date('H:i', strtotime($row['detected_at'])); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Chart: Mood Distribution -->
        <div class="bento-item bento-chart-medium" data-aos="fade-up" data-aos-delay="700">
            <div class="chart-header">
                <h5><i class="bi bi-bar-chart"></i> Emotional Resonance</h5>
            </div>
            <div style="height: 180px;">
                <canvas id="moodChart"></canvas>
            </div>
        </div>

    </div>
</div>

<!-- Pass PHP data to JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const moodChartLabels = <?php echo json_encode($mood_labels); ?>;
    const moodChartData = <?php echo json_encode($mood_values); ?>;
    const userChartLabels = <?php echo json_encode($growth_labels); ?>;
    const userChartData = <?php echo json_encode($growth_values); ?>;
    const methodChartLabels = <?php echo json_encode($method_labels); ?>;
    const methodChartData = <?php echo json_encode($method_values); ?>;
</script>
<script src="assets/js/admin_charts.js"></script>

<?php require_once 'includes/admin_footer.php'; ?>
