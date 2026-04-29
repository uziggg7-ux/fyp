<?php
// admin/dashboard.php
$base_url = '';
require_once 'includes/admin_header.php';

// Fetch stats
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_movies = $pdo->query("SELECT COUNT(*) FROM cached_movies")->fetchColumn();
$total_moods = $pdo->query("SELECT COUNT(*) FROM moods")->fetchColumn();
$total_scans = $pdo->query("SELECT COUNT(*) FROM user_mood_history")->fetchColumn();

// Recent activity
$recent_scans = $pdo->query("SELECT h.*, u.username FROM user_mood_history h JOIN users u ON h.user_id = u.user_id ORDER BY h.detected_at DESC LIMIT 5")->fetchAll();
?>

<h2 class="section-title">Admin Dashboard</h2>

<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="premium-card text-center">
            <i class="bi bi-people text-danger mb-3 d-block" style="font-size: 2rem;"></i>
            <h3 class="fw-bold"><?php echo $total_users; ?></h3>
            <p class="text-muted small mb-0">Total Users</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="premium-card text-center">
            <i class="bi bi-film text-danger mb-3 d-block" style="font-size: 2rem;"></i>
            <h3 class="fw-bold"><?php echo $total_movies; ?></h3>
            <p class="text-muted small mb-0">Cached Movies</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="premium-card text-center">
            <i class="bi bi-emoji-smile text-danger mb-3 d-block" style="font-size: 2rem;"></i>
            <h3 class="fw-bold"><?php echo $total_moods; ?></h3>
            <p class="text-muted small mb-0">Mood Categories</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="premium-card text-center">
            <i class="bi bi-activity text-danger mb-3 d-block" style="font-size: 2rem;"></i>
            <h3 class="fw-bold"><?php echo $total_scans; ?></h3>
            <p class="text-muted small mb-0">Total Scans</p>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="premium-card">
            <h5 class="mb-4">Recent Mood Scans</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Mood</th>
                            <th>Method</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_scans as $scan): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($scan['username']); ?></td>
                                <td><span class="badge bg-danger"><?php echo $scan['mood']; ?></span></td>
                                <td><i class="bi bi-<?php echo $scan['input_type'] === 'face' ? 'webcam' : ($scan['input_type'] === 'voice' ? 'mic' : 'pencil-square'); ?> me-1"></i> <?php echo ucfirst($scan['input_type']); ?></td>
                                <td class="text-muted small"><?php echo date('H:i:s', strtotime($scan['detected_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-end mt-3">
                <a href="users/logs.php" class="text-danger small text-decoration-none">View All Logs <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="premium-card h-100">
            <h5 class="mb-4">System Overview</h5>
            <canvas id="moodChart" height="300"></canvas>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async function() {
    const response = await fetch('api/get_analytics.php');
    const result = await response.json();

    if (result.success) {
        const ctx = document.getElementById('moodChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: result.moods.labels,
                datasets: [{
                    data: result.moods.data,
                    backgroundColor: ['#FF0000', '#950101', '#3D0000', '#1a1a1a', '#444'],
                    borderWidth: 0
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#888', usePointStyle: true }
                    }
                }
            }
        });
    }
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>
