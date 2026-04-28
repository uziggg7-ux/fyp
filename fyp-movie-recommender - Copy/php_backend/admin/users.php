<?php
/**
 * users.php - User Management Terminal
 */
require_once 'includes/admin_header.php';

// Fetch users with stats
try {
    $query = "
        SELECT u.*,
        (SELECT COUNT(*) FROM user_mood_history WHERE user_id = u.user_id) as total_scans,
        (SELECT COUNT(*) FROM user_favorites WHERE user_id = u.user_id) as total_favs
        FROM users u
        ORDER BY u.created_at DESC
    ";
    $users = $pdo->query($query)->fetchAll();
} catch (Exception $e) {
    $users = [];
}
?>

<link rel="stylesheet" href="assets/css/admin_tables.css">

<div class="admin-page-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="section-tag">User Database</span>
            <h2 class="admin-page-title">Registered Operatives</h2>
        </div>
        <div>
            <input type="text" id="tableSearch" class="table-search-box" placeholder="Search by name, email or ID...">
        </div>
    </div>

    <div class="admin-table-container shadow-sm" data-aos="fade-up">
        <div class="table-responsive">
            <table class="table table-premium mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Operative</th>
                        <th>Email Identity</th>
                        <th>Scans</th>
                        <th>Favs</th>
                        <th>Registered</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">No operatives found in the database.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="fw-bold">#<?php echo str_pad($user['user_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td>
                                    <div class="text-white fw-bold"><?php echo htmlspecialchars($user['username']); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge bg-dark border border-secondary"><?php echo $user['total_scans']; ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-dark border border-secondary"><?php echo $user['total_favs']; ?></span>
                                </td>
                                <td class="small"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td class="text-end">
                                    <a href="user_detail.php?id=<?php echo $user['user_id']; ?>" class="btn-action me-2" title="View Profile">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button class="btn-action btn-action-delete delete-user-btn"
                                            data-user-id="<?php echo $user['user_id']; ?>"
                                            data-user-name="<?php echo htmlspecialchars($user['username']); ?>"
                                            title="Purge User Data">
                                        <i class="bi bi-trash"></i>
                                    </button>
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
