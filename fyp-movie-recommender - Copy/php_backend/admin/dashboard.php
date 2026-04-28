<?php
/**
 * dashboard.php - Admin Command Center
 */
require_once 'includes/admin_header.php';

// 1. Fetch Key Stats
try {
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $total_detections = $pdo->query("SELECT COUNT(*) FROM user_mood_history")->fetchColumn();
    $total_favorites = $pdo->query("SELECT COUNT(*) FROM user_favorites")->fetchColumn();
    $cache_count = $pdo->query("SELECT COUNT(*) FROM cached_movies")->fetchColumn();

    // 2. Fetch Mood Distribution (Bar Chart Data)
    $mood_data = $pdo->query("SELECT mood, COUNT(*) as count FROM user_mood_history GROUP BY mood")->fetchAll(PDO::FETCH_KEY_PAIR);

    // 3. Fetch User Registrations - Last 30 Days (Line Chart Data)
    $user_growth = $pdo->query("
        SELECT DATE(created_at) as date, COUNT(*) as count
        FROM users
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    // 4. Fetch Detection Methods (Doughnut Chart Data)
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
    // Basic error handling for stats
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

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="section-tag">System Overview</span>
            <h2 class="admin-page-title">Command Center</h2>
        </div>
        <div class="text-end">
            <span class="badge border border-danger text-danger px-3 py-2" style="font-size: 0.65rem; letter-spacing: 1px;">
                <i class="bi bi-broadcast me-2"></i> LIVE FEED ACTIVE
            </span>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-4 mb-5">
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
            <div class="premium-card stat-card">
                <div class="icon-wrapper"><i class="bi bi-people"></i></div>
                <div>
                    <div class="stat-label">Total Users</div>
                    <div class="stat-value"><?php echo number_format($total_users); ?></div>
                </div>
                <div class="stat-trend trend-up"><i class="bi bi-graph-up me-1"></i> System Growth</div>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
            <div class="premium-card stat-card">
                <div class="icon-wrapper"><i class="bi bi-activity"></i></div>
                <div>
                    <div class="stat-label">Mood Scans</div>
                    <div class="stat-value"><?php echo number_format($total_detections); ?></div>
                </div>
                <div class="stat-trend trend-up"><i class="bi bi-cpu me-1"></i> Neural Activity</div>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
            <div class="premium-card stat-card">
                <div class="icon-wrapper"><i class="bi bi-heart"></i></div>
                <div>
                    <div class="stat-label">Total Favorites</div>
                    <div class="stat-value"><?php echo number_format($total_favorites); ?></div>
                </div>
                <div class="stat-trend trend-up"><i class="bi bi-star me-1"></i> User Engagement</div>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
            <div class="premium-card stat-card">
                <div class="icon-wrapper"><i class="bi bi-database"></i></div>
                <div>
                    <div class="stat-label">Cached Movies</div>
                    <div class="stat-value"><?php echo number_format($cache_count); ?></div>
                </div>
                <div class="stat-trend text-muted"><i class="bi bi-hdd-network me-1"></i> Local Storage</div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-5">
        <!-- User Growth -->
        <div class="col-lg-8" data-aos="fade-right">
            <div class="chart-container">
                <div class="chart-title">
                    <i class="bi bi-graph-up"></i> User Registrations (30 Days)
                </div>
                <div style="height: 300px;">
                    <canvas id="userGrowthChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Detection Methods -->
        <div class="col-lg-4" data-aos="fade-left">
            <div class="chart-container">
                <div class="chart-title">
                    <i class="bi bi-pie-chart"></i> Detection Sources
                </div>
                <div style="height: 300px;">
                    <canvas id="methodChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Mood Distribution -->
        <div class="col-lg-12" data-aos="fade-up">
            <div class="chart-container">
                <div class="chart-title">
                    <i class="bi bi-bar-chart"></i> Emotional Breakdown (Global)
                </div>
                <div style="height: 250px;">
                    <canvas id="moodChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Table -->
    <div class="row" data-aos="fade-up">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="text-white fw-bold"><i class="bi bi-clock-history me-2 text-danger"></i> Recent Global Detections</h5>
                <a href="logs.php" class="text-muted small text-decoration-none">View All Logs <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="activity-feed">
                <?php if (empty($recent_activity)): ?>
                    <div class="premium-card text-center py-5">No recent activity detected.</div>
                <?php else: ?>
                    <?php foreach ($recent_activity as $row): ?>
                        <div class="activity-item d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="icon-wrapper me-3" style="width: 40px; height: 40px; background: rgba(255,255,255,0.05); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-person text-muted"></i>
                                </div>
                                <div>
                                    <div class="text-white fw-bold small"><?php echo htmlspecialchars($row['username']); ?></div>
                                    <div class="text-muted" style="font-size: 0.65rem;">User ID: #<?php echo $row['user_id']; ?> • via <?php echo strtoupper($row['input_type']); ?></div>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="mood-badge-refined mood-<?php echo strtolower($row['mood']); ?>">
                                    <?php echo htmlspecialchars($row['mood']); ?>
                                </span>
                                <div class="text-muted mt-1" style="font-size: 0.6rem;"><?php echo date('H:i', strtotime($row['detected_at'])); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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
