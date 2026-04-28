<?php
/**
 * mood_analytics.php - Global Trends & Emotional Data
 */
require_once 'includes/admin_header.php';

try {
    // 1. Peak Mood Day (Most detections in a single day)
    $peak_day_query = $pdo->query("
        SELECT DATE(detected_at) as date, COUNT(*) as count
        FROM user_mood_history
        GROUP BY DATE(detected_at)
        ORDER BY count DESC LIMIT 1
    ")->fetch();

    // 2. Most Popular Mood (All time)
    $pop_mood = $pdo->query("SELECT mood, COUNT(*) as count FROM user_mood_history GROUP BY mood ORDER BY count DESC LIMIT 1")->fetch();

    // 3. Top 10 Most Saved Movies
    $top_movies = $pdo->query("
        SELECT movie_title, movie_poster, tmdb_movie_id, COUNT(*) as save_count
        FROM user_favorites
        GROUP BY tmdb_movie_id
        ORDER BY save_count DESC
        LIMIT 10
    ")->fetchAll();

    // 4. Method Efficiency (Avg confidence if available, else count)
    $methods = $pdo->query("SELECT input_type, COUNT(*) as count FROM user_mood_history GROUP BY input_type")->fetchAll();

} catch (Exception $e) {
    die("Analytics Failure: " . $e->getMessage());
}
?>

<link rel="stylesheet" href="assets/css/admin_dashboard.css">
<link rel="stylesheet" href="assets/css/admin_tables.css">

<div class="admin-page-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="section-tag">Global Intelligence</span>
            <h2 class="admin-page-title">Mood Analytics</h2>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-premium btn-sm active">30 Days</button>
            <button type="button" class="btn btn-outline-premium btn-sm">90 Days</button>
            <button type="button" class="btn btn-outline-premium btn-sm">All Time</button>
        </div>
    </div>

    <!-- Analytics Overview Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
            <div class="premium-card text-center">
                <div class="text-muted small uppercase fw-bold mb-2">Peak Activity Date</div>
                <h3 class="text-white fw-800 mb-0"><?php echo $peak_day_query ? date('M d, Y', strtotime($peak_day_query['date'])) : 'N/A'; ?></h3>
                <div class="text-danger small mt-1"><?php echo $peak_day_query ? $peak_day_query['count'] : 0; ?> Scans Performed</div>
            </div>
        </div>
        <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
            <div class="premium-card text-center">
                <div class="text-muted small uppercase fw-bold mb-2">Dominant Sentiment</div>
                <h3 class="text-white fw-800 mb-0"><?php echo $pop_mood ? strtoupper($pop_mood['mood']) : 'N/A'; ?></h3>
                <div class="text-danger small mt-1"><?php echo $pop_mood ? $pop_mood['count'] : 0; ?> Global Matches</div>
            </div>
        </div>
        <div class="col-md-4" data-aos="zoom-in" data-aos-delay="300">
            <div class="premium-card text-center">
                <div class="text-muted small uppercase fw-bold mb-2">Sync Consistency</div>
                <h3 class="text-white fw-800 mb-0">94.2%</h3>
                <div class="text-success small mt-1">API Reliability: HIGH</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Chart Area -->
        <div class="col-lg-8">
            <div class="chart-container" data-aos="fade-right">
                <div class="chart-title"><i class="bi bi-graph-up-arrow"></i> Mood Frequency Trends</div>
                <div style="height: 400px;">
                    <canvas id="moodFrequencyChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Movies Sidebar -->
        <div class="col-lg-4">
            <div class="admin-table-container" data-aos="fade-left">
                <h5 class="text-white fw-bold mb-4 small uppercase letter-spacing-1">Top Saved Movies</h5>
                <div class="top-movies-list">
                    <?php if (empty($top_movies)): ?>
                        <p class="text-muted small">No movies saved yet.</p>
                    <?php else: ?>
                        <?php foreach ($top_movies as $index => $movie): ?>
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3 position-relative">
                                    <span class="badge bg-danger position-absolute top-0 start-0" style="font-size: 0.5rem;"><?php echo $index + 1; ?></span>
                                    <img src="<?php echo htmlspecialchars($movie['movie_poster']); ?>" width="40" class="rounded" onerror="this.src='../assets/img/no_poster.jpg';">
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="text-white small fw-bold text-truncate"><?php echo htmlspecialchars($movie['movie_title']); ?></div>
                                    <div class="text-muted" style="font-size: 0.6rem;"><?php echo $movie['save_count']; ?> Operatives Saved</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Pass Initial Data for simple static view or use the API for dynamic -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('moodFrequencyChart');
    if (ctx) {
        // Fetch via API to test the logic
        fetch('api/get_chart_data.php?type=mood_distribution&days=30')
            .then(res => res.json())
            .then(json => {
                if(json.success) {
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: Object.keys(json.data),
                            datasets: [{
                                label: 'Occurrence',
                                data: Object.values(json.data),
                                borderColor: '#FF0000',
                                backgroundColor: 'rgba(255, 0, 0, 0.1)',
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { grid: { color: '#1a1a1a' }, ticks: { color: '#888' } },
                                x: { grid: { display: false }, ticks: { color: '#888' } }
                            }
                        }
                    });
                }
            });
    }
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>
