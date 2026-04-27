<?php
/**
 * cache_manager.php - Manage local movie data
 */
require_once 'includes/admin_header.php';

try {
    // 1. Stats: Total and Breakdown
    $total_cached = $pdo->query("SELECT COUNT(*) FROM cached_movies")->fetchColumn();

    $genre_stats = $pdo->query("
        SELECT genre_id, COUNT(*) as count
        FROM cached_movies
        GROUP BY genre_id
        ORDER BY count DESC
    ")->fetchAll();

    $lang_stats = $pdo->query("
        SELECT original_language, COUNT(*) as count
        FROM cached_movies
        GROUP BY original_language
    ")->fetchAll();

    // 2. Fetch Sample for table
    $cached_samples = $pdo->query("SELECT * FROM cached_movies ORDER BY cached_at DESC LIMIT 50")->fetchAll();

} catch (Exception $e) {
    die("Cache Retrieval Error: " . $e->getMessage());
}
?>

<link rel="stylesheet" href="assets/css/admin_tables.css">
<link rel="stylesheet" href="assets/css/admin_dashboard.css">

<div class="admin-page-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="section-tag">System Storage</span>
            <h2 class="admin-page-title">Cache Manager</h2>
        </div>
        <button class="btn btn-premium" id="clearAllCacheBtn">
            <i class="bi bi-trash3 me-2"></i> WIPE ENTIRE CACHE
        </button>
    </div>

    <!-- Cache Stats -->
    <div class="row g-4 mb-5">
        <div class="col-md-4" data-aos="fade-up">
            <div class="premium-card">
                <div class="stat-label">Total Cached Entities</div>
                <div class="stat-value text-danger"><?php echo number_format($total_cached); ?></div>
                <p class="text-muted small mt-2">Movies stored for offline fallback.</p>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="premium-card">
                <div class="stat-label">Genre Coverage</div>
                <div class="stat-value"><?php echo count($genre_stats); ?></div>
                <p class="text-muted small mt-2">Unique genres with cached results.</p>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="premium-card">
                <div class="stat-label">Storage Optimization</div>
                <div class="stat-value">OPTIMAL</div>
                <p class="text-muted small mt-2">Database indices are active.</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Genre Breakdown Sidebar -->
        <div class="col-lg-4" data-aos="fade-right">
            <div class="admin-table-container">
                <h5 class="text-white fw-bold mb-4 small uppercase">Genre Distribution</h5>
                <div class="list-group list-group-flush bg-transparent">
                    <?php foreach ($genre_stats as $stat): ?>
                        <div class="list-group-item bg-transparent border-secondary d-flex justify-content-between align-items-center px-0">
                            <div>
                                <span class="text-white small fw-bold">ID: <?php echo $stat['genre_id']; ?></span>
                                <div class="text-muted" style="font-size: 0.65rem;">System mapped movies</div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-dark border border-secondary me-2"><?php echo $stat['count']; ?></span>
                                <button class="btn btn-sm btn-outline-danger border-0 clear-genre-btn" data-id="<?php echo $stat['genre_id']; ?>" title="Clear this genre">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Sample Table -->
        <div class="col-lg-8" data-aos="fade-left">
            <div class="admin-table-container">
                <h5 class="text-white fw-bold mb-4 small uppercase">Recent Cached Entries (Last 50)</h5>
                <div class="table-responsive">
                    <table class="table table-premium mb-0" style="font-size: 0.75rem;">
                        <thead>
                            <tr>
                                <th>Poster</th>
                                <th>Title</th>
                                <th>Genre</th>
                                <th>Lang</th>
                                <th>Cached At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cached_samples as $item): ?>
                                <tr>
                                    <td><img src="<?php echo htmlspecialchars($item['poster_path']); ?>" width="30" class="rounded" onerror="this.src='../assets/img/no_poster.jpg';"></td>
                                    <td class="text-white fw-bold"><?php echo htmlspecialchars($item['title']); ?></td>
                                    <td>ID: <?php echo $item['genre_id']; ?></td>
                                    <td><span class="badge bg-secondary"><?php echo strtoupper($item['original_language']); ?></span></td>
                                    <td class="text-muted"><?php echo date('M d, H:i', strtotime($item['cached_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // Clear All Cache
    const clearBtn = document.getElementById('clearAllCacheBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            if (confirm("SYSTEM_WARNING: This will purge ALL cached movie results. The system will rely solely on live API calls until new data is cached. Proceed?")) {
                handleCacheClear();
            }
        });
    }

    // Clear by Genre
    document.querySelectorAll('.clear-genre-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const gid = this.getAttribute('data-id');
            if (confirm(`Purge all cached movies for Genre ID: ${gid}?`)) {
                handleCacheClear(gid);
            }
        });
    });

    async function handleCacheClear(genreId = null) {
        let url = 'api/clear_cache.php';
        if (genreId) url += '?genre_id=' + genreId;

        try {
            const response = await fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const result = await response.json();
            if (result.success) {
                location.reload();
            } else {
                alert("CACHE_OP_FAILED: " + result.message);
            }
        } catch (err) {
            console.error(err);
        }
    }
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>
