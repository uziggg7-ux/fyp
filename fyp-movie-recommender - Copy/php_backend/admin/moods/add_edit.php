<?php
// admin/moods/add_edit.php
$base_url = '../';
require_once '../includes/admin_header.php';

$id = $_GET['id'] ?? null;
$mood = [
    'name' => '',
    'description' => '',
    'associated_genres' => ''
];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM moods WHERE id = ?");
    $stmt->execute([$id]);
    $mood = $stmt->fetch();
    if (!$mood) {
        header("Location: index.php");
        exit();
    }
}

// Handling form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $associated_genres = $_POST['associated_genres'];

    try {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE moods SET name = ?, description = ?, associated_genres = ? WHERE id = ?");
            $stmt->execute([$name, $description, $associated_genres, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO moods (name, description, associated_genres) VALUES (?, ?, ?)");
            $stmt->execute([$name, $description, $associated_genres]);
        }
        header("Location: index.php");
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="mb-4">
    <a href="index.php" class="text-muted text-decoration-none"><i class="bi bi-arrow-left me-1"></i> Back to Moods</a>
    <h2 class="section-title mt-3"><?php echo $id ? 'Edit Mood' : 'Add New Mood'; ?></h2>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="premium-card">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label text-muted">Mood Name</label>
                    <input type="text" name="name" class="form-control bg-dark text-white border-secondary"
                           value="<?php echo htmlspecialchars($mood['name']); ?>" required placeholder="e.g. Happy">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Description</label>
                    <textarea name="description" class="form-control bg-dark text-white border-secondary"
                              rows="3" placeholder="Describe the mood..."><?php echo htmlspecialchars($mood['description']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Associated TMDB Genre IDs</label>
                    <input type="text" name="associated_genres" class="form-control bg-dark text-white border-secondary"
                           value="<?php echo htmlspecialchars($mood['associated_genres']); ?>" placeholder="e.g. 35, 12">
                    <div class="form-text text-muted">Comma-separated IDs. (Comedy: 35, Drama: 18, Action: 28)</div>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-premium">
                        <i class="bi bi-check-lg me-2"></i> <?php echo $id ? 'Update Mood' : 'Create Mood'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="premium-card h-100">
            <h5 class="mb-4"><i class="bi bi-info-circle me-2 text-danger"></i> TMDB Genre Reference</h5>
            <div class="row g-2">
                <div class="col-6"><span class="text-muted">Action:</span> 28</div>
                <div class="col-6"><span class="text-muted">Adventure:</span> 12</div>
                <div class="col-6"><span class="text-muted">Animation:</span> 16</div>
                <div class="col-6"><span class="text-muted">Comedy:</span> 35</div>
                <div class="col-6"><span class="text-muted">Crime:</span> 80</div>
                <div class="col-6"><span class="text-muted">Documentary:</span> 99</div>
                <div class="col-6"><span class="text-muted">Drama:</span> 18</div>
                <div class="col-6"><span class="text-muted">Family:</span> 10751</div>
                <div class="col-6"><span class="text-muted">Fantasy:</span> 14</div>
                <div class="col-6"><span class="text-muted">History:</span> 36</div>
                <div class="col-6"><span class="text-muted">Horror:</span> 27</div>
                <div class="col-6"><span class="text-muted">Music:</span> 10402</div>
                <div class="col-6"><span class="text-muted">Mystery:</span> 9648</div>
                <div class="col-6"><span class="text-muted">Romance:</span> 10749</div>
                <div class="col-6"><span class="text-muted">Sci-Fi:</span> 878</div>
                <div class="col-6"><span class="text-muted">Thriller:</span> 53</div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
