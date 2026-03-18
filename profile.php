<?php
include 'header.php';
if (!$is_logged_in) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

// Fetch TMDB key for admin
$tmdb_api_key = '';
if ($is_admin) {
    try {
        $stmt = $pdo->prepare("SELECT value FROM settings WHERE setting_key = 'tmdb_api_key'");
        $stmt->execute();
        $tmdb_api_key = $stmt->fetchColumn();
    } catch (Exception $e) {}
}
?>

<div class="profile-container">
    <h2>User Profile: <?php echo htmlspecialchars($username); ?></h2>

    <section class="profile-section">
        <h3>Account Options</h3>
        <button onclick="window.location.href='logout.php'">Logout</button>
    </section>

    <section class="profile-section">
        <h3>Backup & Restore</h3>
        <p>Export your favourites and watchlist to a JSON file, or import them back.</p>
        <button onclick="exportData()">Export My Data</button>
        <div style="margin-top: 10px;">
            <input type="file" id="importFile" accept=".json" />
            <button onclick="importData()">Import My Data</button>
        </div>
    </section>

    <?php if ($is_admin): ?>
    <section class="admin-section" id="adminControls">
        <hr>
        <h2>Admin Settings</h2>
        
        <div class="form-group">
            <label>Site Title:</label>
            <input type="text" id="adminSiteTitle" value="<?php echo htmlspecialchars($site_title); ?>">
        </div>

        <div class="form-group">
            <label>Theme:</label>
            <select id="adminTheme">
                <option value="default" <?php if ($theme == 'default') echo 'selected'; ?>>Default (Dark)</option>
                <option value="ocean" <?php if ($theme == 'ocean') echo 'selected'; ?>>Ocean Blue</option>
                <option value="forest" <?php if ($theme == 'forest') echo 'selected'; ?>>Forest Green</option>
            </select>
        </div>

        <div class="form-group">
            <label>TMDB API Key:</label>
            <input type="text" id="adminTmdbKey" value="<?php echo htmlspecialchars($tmdb_api_key); ?>">
        </div>

        <div class="form-group">
            <label>Plugins:</label>
            <label>
                <input type="checkbox" id="pluginWatchToggle" <?php if ($plugin_watch == '1') echo 'checked'; ?>>
                Enable "Watch" Feature
            </label>
        </div>

        <button id="saveAdminSettings">Save Site Settings</button>
        <p id="adminStatus"></p>
    </section>
    <script>
    document.getElementById('saveAdminSettings').addEventListener('click', () => {
        const title = document.getElementById('adminSiteTitle').value;
        const theme = document.getElementById('adminTheme').value;
        const tmdb = document.getElementById('adminTmdbKey').value;
        const watch = document.getElementById('pluginWatchToggle').checked ? '1' : '0';
        const status = document.getElementById('adminStatus');

        status.textContent = "Saving...";

        fetch('api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                site_title: title,
                theme: theme,
                tmdb_api_key: tmdb,
                plugin_watch: watch
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                status.textContent = "Settings saved! Refreshing...";
                setTimeout(() => location.reload(), 1000);
            } else {
                status.textContent = "Error: " + (data.error || "Unknown error");
            }
        });
    });
    </script>
    <?php endif; ?>
</div>

<script src="js/main.js"></script>
<?php include 'footer.php'; ?>
