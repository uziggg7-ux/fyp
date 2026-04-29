<?php
// admin/movies/add_edit.php
$base_url = '../';
require_once '../includes/admin_header.php';

$id = $_GET['id'] ?? null;
$movie = [
    'tmdb_id' => '',
    'title' => '',
    'overview' => '',
    'poster_path' => '',
    'vote_average' => 0.0,
    'genre_id' => 0,
    'release_year' => '',
    'original_language' => 'en'
];
$selected_moods = [];

if ($id) {
    // Fetch movie
    $stmt = $pdo->prepare("SELECT * FROM cached_movies WHERE tmdb_id = ? LIMIT 1");
    $stmt->execute([$id]);
    $movie = $stmt->fetch();

    // Fetch mapped moods
    $stmt = $pdo->prepare("SELECT mood_id FROM movie_mood_mapping WHERE movie_id = ?");
    $stmt->execute([$id]);
    $selected_moods = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Fetch all available moods
$stmt = $pdo->query("SELECT id, name FROM moods ORDER BY name ASC");
$all_moods = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tmdb_id = $_POST['tmdb_id'];
    $title = $_POST['title'];
    $overview = $_POST['overview'];
    $poster_path = $_POST['poster_path'];
    $vote_average = $_POST['vote_average'];
    $release_year = $_POST['release_year'];
    $mood_ids = $_POST['moods'] ?? [];

    try {
        $pdo->beginTransaction();

        // 1. Insert/Update movie
        $stmt = $pdo->prepare("INSERT INTO cached_movies (tmdb_id, title, overview, poster_path, vote_average, release_year, genre_id, original_language)
                               VALUES (?, ?, ?, ?, ?, ?, ?, 'en')
                               ON DUPLICATE KEY UPDATE
                               title = VALUES(title),
                               overview = VALUES(overview),
                               poster_path = VALUES(poster_path),
                               vote_average = VALUES(vote_average),
                               release_year = VALUES(release_year)");
        $stmt->execute([$tmdb_id, $title, $overview, $poster_path, $vote_average, $release_year, 0]);

        // 2. Clear old mappings
        $stmt = $pdo->prepare("DELETE FROM movie_mood_mapping WHERE movie_id = ?");
        $stmt->execute([$tmdb_id]);

        // 3. Insert new mappings
        if (!empty($mood_ids)) {
            $stmt = $pdo->prepare("INSERT INTO movie_mood_mapping (movie_id, mood_id) VALUES (?, ?)");
            foreach ($mood_ids as $m_id) {
                $stmt->execute([$tmdb_id, $m_id]);
            }
        }

        $pdo->commit();
        header("Location: index.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}
?>

<div class="mb-4">
    <a href="index.php" class="text-muted text-decoration-none"><i class="bi bi-arrow-left me-1"></i> Back to Movies</a>
    <h2 class="section-title mt-3"><?php echo $id ? 'Edit Movie & Mappings' : 'Add New Movie'; ?></h2>
</div>

<form method="POST" class="row g-4">
    <div class="col-lg-8">
        <div class="premium-card">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label text-muted">TMDB ID</label>
                    <input type="number" name="tmdb_id" class="form-control bg-dark text-white border-secondary"
                           value="<?php echo htmlspecialchars($movie['tmdb_id']); ?>" required <?php echo $id ? 'readonly' : ''; ?>>
                </div>
                <div class="col-md-8">
                    <label class="form-label text-muted">Movie Title</label>
                    <input type="text" name="title" class="form-control bg-dark text-white border-secondary"
                           value="<?php echo htmlspecialchars($movie['title']); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted">Release Year</label>
                    <input type="number" name="release_year" class="form-control bg-dark text-white border-secondary"
                           value="<?php echo htmlspecialchars($movie['release_year']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted">Rating (0-10)</label>
                    <input type="number" step="0.1" name="vote_average" class="form-control bg-dark text-white border-secondary"
                           value="<?php echo htmlspecialchars($movie['vote_average']); ?>">
                </div>
                <div class="col-12">
                    <label class="form-label text-muted">Poster URL</label>
                    <input type="text" name="poster_path" class="form-control bg-dark text-white border-secondary"
                           value="<?php echo htmlspecialchars($movie['poster_path']); ?>">
                </div>
                <div class="col-12">
                    <label class="form-label text-muted">Description</label>
                    <textarea name="overview" class="form-control bg-dark text-white border-secondary"
                              rows="5"><?php echo htmlspecialchars($movie['overview']); ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="premium-card mb-4">
            <h5 class="mb-3 text-danger"><i class="bi bi-emoji-heart-eyes me-2"></i> Mood Mapping</h5>
            <p class="text-muted small">Select all moods that apply to this movie.</p>

            <div class="mood-selection-grid p-2 border border-secondary rounded bg-black" style="max-height: 300px; overflow-y: auto;">
                <?php foreach ($all_moods as $m): ?>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="moods[]" value="<?php echo $m['id']; ?>"
                               id="mood_<?php echo $m['id']; ?>" <?php echo in_array($m['id'], $selected_moods) ? 'checked' : ''; ?>>
                        <label class="form-check-label text-white" for="mood_<?php echo $m['id']; ?>">
                            <?php echo htmlspecialchars($m['name']); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-premium btn-lg">
                <i class="bi bi-save me-2"></i> Save Changes
            </button>
        </div>
    </div>
</form>

<?php require_once '../includes/admin_footer.php'; ?>
