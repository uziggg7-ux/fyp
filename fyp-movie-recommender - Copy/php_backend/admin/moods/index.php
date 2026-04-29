<?php
// admin/moods/index.php
$base_url = '../';
require_once '../includes/admin_header.php';

// Fetch moods from database
try {
    $stmt = $pdo->query("SELECT * FROM moods ORDER BY name ASC");
    $moods = $stmt->fetchAll();
} catch (Exception $e) {
    $moods = [];
    $error = $e->getMessage();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="section-title mb-0">Mood Management</h2>
    <a href="add_edit.php" class="btn btn-premium">
        <i class="bi bi-plus-lg me-2"></i> Add New Mood
    </a>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="premium-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Mood Name</th>
                    <th>Description</th>
                    <th>Associated Genres</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($moods)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No moods found. Click "Add New Mood" to get started.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($moods as $mood): ?>
                        <tr>
                            <td>#<?php echo $mood['id']; ?></td>
                            <td><span class="badge bg-danger"><?php echo htmlspecialchars($mood['name']); ?></span></td>
                            <td><?php echo htmlspecialchars($mood['description']); ?></td>
                            <td><?php echo htmlspecialchars($mood['associated_genres']); ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="add_edit.php?id=<?php echo $mood['id']; ?>" class="btn btn-sm btn-outline-premium">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-secondary delete-mood" data-id="<?php echo $mood['id']; ?>">
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
document.querySelectorAll('.delete-mood').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (confirm('Are you sure you want to delete this mood? This will also remove all movie mappings for this mood.')) {
            const id = this.getAttribute('data-id');
            const response = await fetch('../api/moods/delete.php', {
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
