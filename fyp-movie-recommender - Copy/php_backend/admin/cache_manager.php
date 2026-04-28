<?php
/**
 * cache_manager.php - Optimized Movie Cache Interface
 */
require_once 'includes/admin_header.php';

try {
    // 1. Stats Breakdown
    $total_cached = $pdo->query("SELECT COUNT(*) FROM cached_movies")->fetchColumn();
    $genre_stats = $pdo->query("SELECT genre_id, COUNT(*) as count FROM cached_movies GROUP BY genre_id ORDER BY count DESC")->fetchAll();
    $lang_stats = $pdo->query("SELECT original_language, COUNT(*) as count FROM cached_movies GROUP BY original_language")->fetchAll();

    // 2. Sample Data
    $cached_samples = $pdo->query("SELECT * FROM cached_movies ORDER BY cached_at DESC LIMIT 50")->fetchAll();

} catch (Exception $e) {
    die("Cache Retrieval Error: " . $e->getMessage());
}
?>

<link rel="stylesheet" href="assets/css/admin_tables.css">
<link rel="stylesheet" href="assets/css/admin_dashboard.css">

<div class="admin-page-content">

    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <span class="section-tag">Optimization Hub</span>
            <h2 class="admin-page-title">Cache Manager</h2>
        </div>
        <button class="btn btn-premium px-4" id="clearAllCacheBtn">
            <i class="bi bi-trash3 me-2"></i> WIPE FULL REPOSITORY
        </button>
    </div>

    <!-- Bento-Style Stats -->
    <div class="row g-4 mb-5">
        <div class="col-md-4" data-aos="zoom-in">
            <div class="bento-item h-100 d-flex flex-column justify-content-center text-center">
                <div class="stat-label mb-2">Total Stored Entities</div>
                <div class="stat-value text-danger" style="font-size: 2.5rem; font-weight: 900;"><?php echo number_format($total_cached); ?></div>
                <div class="text-muted small mt-2">Active movie entries in fallback pool.</div>
            </div>
        </div>
        <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
            <div class="bento-item h-100 d-flex flex-column justify-content-center text-center">
                <div class="stat-label mb-2">Genre Diversity</div>
                <div class="stat-value text-white" style="font-size: 2.5rem; font-weight: 900;"><?php echo count($genre_stats); ?></div>
                <div class="text-muted small mt-2">Unique categories represented.</div>
            </div>
        </div>
        <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
            <div class="bento-item h-100 d-flex flex-column justify-content-center text-center">
                <div class="stat-label mb-2">Neural Status</div>
                <div class="stat-value text-success" style="font-size: 1.8rem; font-weight: 900;">SYNCHRONIZED</div>
                <div class="text-muted small mt-2">Repository optimized for fast retrieval.</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Genre Breakdown -->
        <div class="col-lg-4" data-aos="fade-right">
            <div class="admin-table-container">
                <h5 class="text-white fw-bold mb-4 small uppercase letter-spacing-1">Repository Breakdown</h5>
                <div class="list-group list-group-flush bg-transparent">
                    <?php foreach ($genre_stats as $stat): ?>
                        <div class="list-group-item bg-transparent border-0 d-flex justify-content-between align-items-center px-0 py-3 mb-2" style="border-bottom: 1px solid rgba(255,255,255,0.03) !important;">
                            <div>
                                <span class="text-white small fw-bold">Genre ID: #<?php echo $stat['genre_id']; ?></span>
                                <div class="text-muted" style="font-size: 0.65rem;">Neural Category Mapping</div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-dark border border-secondary px-3 py-2 me-2"><?php echo $stat['count']; ?></span>
                                <button class="btn btn-sm btn-outline-danger border-0 clear-genre-btn p-0" data-id="<?php echo $stat['genre_id']; ?>">
                                    <i class="bi bi-x-circle fs-5"></i>
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
                <h5 class="text-white fw-bold mb-4 small uppercase letter-spacing-1">Live Repository Samples</h5>
                <div class="table-responsive">
                    <table class="table table-premium mb-0">
                        <thead>
                            <tr>
                                <th>Entity</th>
                                <th>Identity</th>
                                <th>Lang</th>
                                <th>Synced At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cached_samples as $item): ?>
                                <tr>
                                    <td style="width: 50px;">
                                        <img src="<?php echo htmlspecialchars($item['poster_path']); ?>" width="40" class="rounded shadow-sm" onerror="this.src='../assets/img/no_poster.jpg';">
                                    </td>
                                    <td>
                                        <div class="text-white fw-bold small"><?php echo htmlspecialchars($item['title']); ?></div>
                                        <div class="text-muted" style="font-size: 0.65rem;">GENRE_ID: <?php echo $item['genre_id']; ?></div>
                                    </td>
                                    <td><span class="badge bg-dark border border-secondary"><?php echo strtoupper($item['original_language']); ?></span></td>
                                    <td class="text-muted small"><?php echo date('M d, H:i', strtotime($item['cached_at'])); ?></td>
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
            if (confirm("SYSTEM_CRITICAL: Wipe entire repository? This operation is irreversible.")) {
                handleCacheClear();
            }
        });
    }

    // Clear by Genre
    document.querySelectorAll('.clear-genre-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const gid = this.getAttribute('data-id');
            if (confirm(`Purge all cached entities for Genre ID: ${gid}?`)) {
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
                alert("SY_FAULT: Operation failed.");
            }
        } catch (err) {
            console.error(err);
        }
    }
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>
