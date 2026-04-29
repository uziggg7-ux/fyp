<?php
// admin/settings/index.php
$base_url = '../';
require_once '../includes/admin_header.php';

// Handling Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['settings'] as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?)
                               ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute([$key, $value]);
    }
    $success = "Settings updated successfully.";
}

// Fetch settings
$stmt = $pdo->query("SELECT * FROM system_settings");
$settings_raw = $stmt->fetchAll();
$settings = [];
foreach ($settings_raw as $s) {
    $settings[$s['setting_key']] = $s['setting_value'];
}

// Defaults
$app_name = $settings['app_name'] ?? 'MoodAI';
$rec_count = $settings['rec_count'] ?? '10';
$rec_logic = $settings['rec_logic'] ?? 'top_rated';
$tmdb_api_key = $settings['tmdb_api_key'] ?? '';
?>

<h2 class="section-title">System Settings</h2>

<div class="row">
    <div class="col-lg-8">
        <div class="premium-card">
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Application Name</label>
                        <input type="text" name="settings[app_name]" class="form-control bg-dark text-white border-secondary"
                               value="<?php echo htmlspecialchars($app_name); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Recommendations to Show</label>
                        <select name="settings[rec_count]" class="form-select bg-dark text-white border-secondary">
                            <option value="5" <?php echo $rec_count == '5' ? 'selected' : ''; ?>>Top 5</option>
                            <option value="10" <?php echo $rec_count == '10' ? 'selected' : ''; ?>>Top 10</option>
                            <option value="20" <?php echo $rec_count == '20' ? 'selected' : ''; ?>>Top 20</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Recommendation Logic</label>
                        <select name="settings[rec_logic]" class="form-select bg-dark text-white border-secondary">
                            <option value="top_rated" <?php echo $rec_logic == 'top_rated' ? 'selected' : ''; ?>>Top Rated First</option>
                            <option value="popular" <?php echo $rec_logic == 'popular' ? 'selected' : ''; ?>>Most Popular First</option>
                            <option value="random" <?php echo $rec_logic == 'random' ? 'selected' : ''; ?>>Random Mix</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted">TMDB API Key</label>
                        <div class="input-group">
                            <span class="input-group-text bg-black border-secondary text-muted"><i class="bi bi-key"></i></span>
                            <input type="password" name="settings[tmdb_api_key]" class="form-control bg-dark text-white border-secondary"
                                   value="<?php echo htmlspecialchars($tmdb_api_key); ?>">
                        </div>
                        <div class="form-text text-muted">Used for real-time movie synchronization from The Movie Database.</div>
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-premium btn-lg px-5">
                            <i class="bi bi-cloud-check me-2"></i> Save Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="premium-card bg-danger bg-opacity-10 border-danger">
            <h5 class="text-danger mb-3"><i class="bi bi-shield-lock me-2"></i> Admin Security</h5>
            <p class="small text-muted">These settings affect the global behavior of the MoodAI recommendation engine. Ensure your API keys are kept secure.</p>
            <hr class="border-secondary opacity-25">
            <div class="d-grid gap-2">
                <button class="btn btn-sm btn-outline-danger">Change Admin Password</button>
                <button class="btn btn-sm btn-outline-secondary">Download System Audit</button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
