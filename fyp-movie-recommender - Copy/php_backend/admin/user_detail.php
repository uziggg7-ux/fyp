<?php
/**
 * user_detail.php - Single User Intelligence Profile
 */
require_once 'includes/admin_header.php';

$user_id = $_GET['id'] ?? null;

if (!$user_id) {
    header("Location: users.php");
    exit();
}

try {
    // 1. Fetch User Info
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        header("Location: users.php");
        exit();
    }

    // 2. Fetch Detailed Stats
    // Most detected mood
    $stmt = $pdo->prepare("SELECT mood, COUNT(*) as count FROM user_mood_history WHERE user_id = ? GROUP BY mood ORDER BY count DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $fav_mood = $stmt->fetchColumn() ?: 'N/A';

    // Preferred method
    $stmt = $pdo->prepare("SELECT input_type, COUNT(*) as count FROM user_mood_history WHERE user_id = ? GROUP BY input_type ORDER BY count DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $pref_method = $stmt->fetchColumn() ?: 'N/A';

    // 3. Fetch Full Mood History
    $stmt = $pdo->prepare("SELECT * FROM user_mood_history WHERE user_id = ? ORDER BY detected_at DESC");
    $stmt->execute([$user_id]);
    $history = $stmt->fetchAll();

    // 4. Fetch Favorites
    $stmt = $pdo->prepare("SELECT * FROM user_favorites WHERE user_id = ? ORDER BY saved_at DESC");
    $stmt->execute([$user_id]);
    $favorites = $stmt->fetchAll();

} catch (Exception $e) {
    die("Intelligence Retrieval Error: " . $e->getMessage());
}
?>

<link rel="stylesheet" href="assets/css/admin_tables.css">
<link rel="stylesheet" href="assets/css/admin_dashboard.css">

<div class="admin-page-content">

    <div class="mb-4">
        <a href="users.php" class="text-muted text-decoration-none small"><i class="bi bi-arrow-left me-1"></i> Back to User List</a>
        <h2 class="admin-page-title mt-2">Profile Analysis: <?php echo htmlspecialchars($user['username']); ?></h2>
    </div>

    <div class="row g-4">
        <!-- Sidebar: User Info -->
        <div class="col-lg-4" data-aos="fade-right">
            <div class="premium-card user-info-card text-center">
                <div class="user-avatar-large mx-auto">
                    <i class="bi bi-person-fill"></i>
                </div>
                <h4 class="text-white fw-bold mb-1"><?php echo htmlspecialchars($user['username']); ?></h4>
                <p class="text-muted small mb-4"><?php echo htmlspecialchars($user['email']); ?></p>

                <hr class="border-secondary opacity-10">

                <div class="row text-start mt-4">
                    <div class="col-6 mb-3">
                        <div class="text-muted small uppercase fw-bold" style="font-size: 0.6rem;">Most Common Mood</div>
                        <div class="text-danger fw-bold"><?php echo strtoupper($fav_mood); ?></div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="text-muted small uppercase fw-bold" style="font-size: 0.6rem;">Primary Method</div>
                        <div class="text-white fw-bold"><?php echo strtoupper($pref_method); ?></div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small uppercase fw-bold" style="font-size: 0.6rem;">Initialization Date</div>
                        <div class="text-white"><?php echo date('F d, Y', strtotime($user['created_at'])); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main: History and Favorites -->
        <div class="col-lg-8" data-aos="fade-left">
            <!-- Tabs Navigation -->
            <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active btn-outline-premium py-2 px-4 me-2" id="pills-history-tab" data-bs-toggle="pill" data-bs-target="#pills-history" type="button" role="tab">Mood History</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link btn-outline-premium py-2 px-4" id="pills-favs-tab" data-bs-toggle="pill" data-bs-target="#pills-favs" type="button" role="tab">Saved Favorites</button>
                </li>
            </ul>

            <div class="tab-content" id="pills-tabContent">
                <!-- Mood History Tab -->
                <div class="tab-pane fade show active" id="pills-history" role="tabpanel">
                    <div class="admin-table-container">
                        <div class="table-responsive">
                            <table class="table table-premium mb-0">
                                <thead>
                                    <tr>
                                        <th>Mood Result</th>
                                        <th>Detection Method</th>
                                        <th>Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($history)): ?>
                                        <tr><td colspan="3" class="text-center py-4">No scan history recorded.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($history as $h): ?>
                                            <tr>
                                                <td>
                                                    <span class="mood-badge mood-<?php echo strtolower($h['mood']); ?>">
                                                        <?php echo htmlspecialchars($h['mood']); ?>
                                                    </span>
                                                </td>
                                                <td><i class="bi bi-cpu me-2 small"></i> <?php echo strtoupper($h['input_type']); ?></td>
                                                <td class="small"><?php echo date('M d, Y H:i', strtotime($h['detected_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Favorites Tab -->
                <div class="tab-pane fade" id="pills-favs" role="tabpanel">
                    <div class="row g-3">
                        <?php if (empty($favorites)): ?>
                            <div class="col-12 text-center py-5">
                                <i class="bi bi-bookmark-x display-4 text-muted d-block mb-3"></i>
                                <p class="text-muted">No movies saved to favorites yet.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($favorites as $f): ?>
                                <div class="col-md-6 col-xl-4">
                                    <div class="premium-card p-2">
                                        <img src="<?php echo htmlspecialchars($f['movie_poster']); ?>" class="img-fluid rounded mb-2" alt="Poster" onerror="this.src='../assets/img/no_poster.jpg';">
                                        <div class="px-2 pb-2">
                                            <h6 class="text-white text-truncate mb-1" title="<?php echo htmlspecialchars($f['movie_title']); ?>">
                                                <?php echo htmlspecialchars($f['movie_title']); ?>
                                            </h6>
                                            <span class="badge bg-dark border border-secondary text-muted" style="font-size: 0.6rem;">Saved: <?php echo date('M d', strtotime($f['saved_at'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once 'includes/admin_footer.php'; ?>
