<?php
// admin/users/logs.php
$base_url = '../';
require_once '../includes/admin_header.php';

try {
    // We'll show both user_logs (new) and user_mood_history (legacy activity)
    $stmt = $pdo->query("SELECT h.mood, h.input_type, h.detected_at as created_at, u.username, 'Mood Scan' as action
                         FROM user_mood_history h
                         JOIN users u ON h.user_id = u.user_id
                         ORDER BY h.detected_at DESC LIMIT 100");
    $logs = $stmt->fetchAll();
} catch (Exception $e) {
    $logs = [];
    $error = $e->getMessage();
}
?>

<div class="mb-4">
    <a href="index.php" class="text-muted text-decoration-none"><i class="bi bi-arrow-left me-1"></i> Back to Users</a>
    <h2 class="section-title mt-3">Activity Logs</h2>
</div>

<div class="premium-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Action</th>
                    <th>Details</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">No activity found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="fw-bold"><?php echo htmlspecialchars($log['username']); ?></td>
                            <td><span class="badge bg-primary"><?php echo $log['action']; ?></span></td>
                            <td>
                                Detected Mood: <span class="text-danger"><?php echo $log['mood']; ?></span>
                                via <span class="text-muted"><?php echo $log['input_type']; ?></span>
                            </td>
                            <td class="text-muted small"><?php echo $log['created_at']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
