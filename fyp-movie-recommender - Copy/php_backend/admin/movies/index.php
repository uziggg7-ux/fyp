<?php
// admin/movies/index.php
$base_url = '../';
require_once '../includes/admin_header.php';

$search = $_GET['search'] ?? '';

try {
    $query = "SELECT m.*, GROUP_CONCAT(mo.name SEPARATOR ', ') as moods
              FROM cached_movies m
              LEFT JOIN movie_mood_mapping map ON m.tmdb_id = map.movie_id
              LEFT JOIN moods mo ON map.mood_id = mo.id";

    if ($search) {
        $query .= " WHERE m.title LIKE :search";
    }

    $query .= " GROUP BY m.tmdb_id ORDER BY m.cached_at DESC LIMIT 50";

    $stmt = $pdo->prepare($query);
    if ($search) {
        $stmt->execute(['search' => "%$search%"]);
    } else {
        $stmt->execute();
    }
    $movies = $stmt->fetchAll();
} catch (Exception $e) {
    $movies = [];
    $error = $e->getMessage();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="section-title mb-0">Movie Management</h2>
    <div class="d-flex gap-2">
        <form class="d-flex" method="GET">
            <input type="text" name="search" class="form-control bg-dark text-white border-secondary me-2"
                   placeholder="Search movies..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-outline-premium">Search</button>
        </form>
        <a href="add_edit.php" class="btn btn-premium">
            <i class="bi bi-plus-lg me-2"></i> Add Movie
        </a>
    </div>
</div>

<div class="premium-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Poster</th>
                    <th>Title & ID</th>
                    <th>Year/Rating</th>
                    <th>Mood Tags</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($movies)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No movies found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($movies as $movie): ?>
                        <tr>
                            <td style="width: 80px;">
                                <img src="<?php echo htmlspecialchars($movie['poster_path']); ?>"
                                     alt="Poster" class="img-fluid rounded" style="max-height: 80px;">
                            </td>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($movie['title']); ?></div>
                                <div class="small text-muted">ID: #<?php echo $movie['tmdb_id']; ?></div>
                            </td>
                            <td>
                                <div><?php echo $movie['release_year'] ?? 'N/A'; ?></div>
                                <div class="text-warning"><i class="bi bi-star-fill me-1"></i> <?php echo number_format($movie['vote_average'], 1); ?></div>
                            </td>
                            <td>
                                <?php
                                    $tags = explode(', ', $movie['moods']);
                                    foreach ($tags as $tag) {
                                        if ($tag) echo '<span class="badge bg-dark border border-secondary me-1">' . htmlspecialchars($tag) . '</span>';
                                    }
                                    if (empty($movie['moods'])) echo '<span class="text-muted small">No moods assigned</span>';
                                ?>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="add_edit.php?id=<?php echo $movie['tmdb_id']; ?>" class="btn btn-sm btn-outline-premium">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-secondary delete-movie" data-id="<?php echo $movie['tmdb_id']; ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.querySelectorAll('.delete-movie').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (confirm('Are you sure you want to delete this movie from the local cache?')) {
            const id = this.getAttribute('data-id');
            const response = await fetch('../api/movies/delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            const result = await response.json();
            if (result.success) {
                location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        }
    });
});
</script>

<?php require_once '../includes/admin_footer.php'; ?>
