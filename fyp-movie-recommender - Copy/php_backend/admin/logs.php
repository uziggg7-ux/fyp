<?php
/**
 * logs.php - System Activity & Audit Trail
 */
require_once 'includes/admin_header.php';

try {
    // Fetch logs with admin usernames
    $query = "
        SELECT l.*, a.username
        FROM admin_logs l
        LEFT JOIN admins a ON l.admin_id = a.admin_id
        ORDER BY l.created_at DESC
    ";
    $logs = $pdo->query($query)->fetchAll();
} catch (Exception $e) {
    $logs = [];
}
?>

<link rel="stylesheet" href="assets/css/admin_tables.css">

<div class="admin-page-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="section-tag">Audit Trail</span>
            <h2 class="admin-page-title">System Activity Logs</h2>
        </div>
        <div>
            <input type="text" id="tableSearch" class="table-search-box" placeholder="Search logs by action, admin or IP...">
        </div>
    </div>

    <div class="admin-table-container shadow-sm" data-aos="fade-up">
        <div class="table-responsive">
            <table class="table table-premium mb-0">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Administrator</th>
                        <th>Action Protocol</th>
                        <th>Target Entity</th>
                        <th>Origin IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">No system activity logs found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="small text-white"><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></td>
                                <td>
                                    <span class="text-white fw-bold"><i class="bi bi-shield-check me-2 text-danger"></i><?php echo htmlspecialchars($log['username'] ?? 'SYSTEM'); ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-dark border border-secondary text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">
                                        <?php echo htmlspecialchars($log['action']); ?>
                                    </span>
                                </td>
                                <td class="small">
                                    <?php echo htmlspecialchars($log['target'] ?: 'N/A'); ?>
                                </td>
                                <td class="text-muted small">
                                    <?php echo htmlspecialchars($log['ip_address'] ?: 'Internal'); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="assets/js/admin_tables.js"></script>

<?php require_once 'includes/admin_footer.php'; ?>
