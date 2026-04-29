<?php
// admin/users/index.php
$base_url = '../';
require_once '../includes/admin_header.php';

// Handling Block/Unblock
if (isset($_GET['action']) && isset($_GET['uid'])) {
    $uid = $_GET['uid'];
    $is_banned = ($_GET['action'] === 'block') ? 1 : 0;
    $stmt = $pdo->prepare("UPDATE users SET is_banned = ? WHERE user_id = ?");
    $stmt->execute([$is_banned, $uid]);
    header("Location: index.php");
    exit();
}

try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (Exception $e) {
    $users = [];
    $error = $e->getMessage();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="section-title mb-0">User Management</h2>
    <a href="logs.php" class="btn btn-outline-premium">
        <i class="bi bi-journal-text me-2"></i> View System Logs
    </a>
</div>

<div class="premium-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Joined</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>#<?php echo $user['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <?php if ($user['is_banned']): ?>
                                <span class="badge bg-danger">Banned</span>
                            <?php else: ?>
                                <span class="badge bg-success">Active</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <?php if ($user['is_banned']): ?>
                                    <a href="?action=unblock&uid=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-outline-success">Unblock</a>
                                <?php else: ?>
                                    <a href="?action=block&uid=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-outline-danger">Block</a>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-outline-secondary delete-user" data-id="<?php echo $user['user_id']; ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.querySelectorAll('.delete-user').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (confirm('Are you sure you want to permanently delete this user?')) {
            const id = this.getAttribute('data-id');
            const response = await fetch('../api/users/delete.php', {
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
